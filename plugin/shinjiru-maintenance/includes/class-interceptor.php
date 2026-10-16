<?php
/**
 * Request Interceptor and Routing Gate.
 *
 * @package Shinjiru\Maintenance
 */

namespace Shinjiru\Maintenance;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Interceptor {

	/**
	 * Main intercept check hooked to template_redirect.
	 */
	public static function maybe_intercept() {
		// 1. Check if request should be strictly excluded
		if ( self::is_excluded_request() ) {
			return;
		}

		$settings = Config::get_settings();

		// 2. Check preview mode for administrators
		if ( isset( $_GET['shinjiru_preview'] ) && current_user_can( 'manage_options' ) ) {
			$preview_template = sanitize_key( $_GET['shinjiru_preview'] );
			Template_Engine::render( $preview_template, $settings, true );
			exit;
		}

		// 3. Determine effective status (manual status vs schedule)
		$effective_status = $settings['status'];
		if ( 'off' === $effective_status && ! empty( $settings['schedule_enabled'] ) ) {
			if ( Scheduler::is_schedule_active( $settings ) ) {
				$effective_status = 'maintenance';
			}
		}

		if ( 'off' === $effective_status ) {
			return;
		}

		// 4. Verify access permissions
		if ( Access_Manager::has_access( $settings ) ) {
			return;
		}

		// 5. Intercept and render maintenance/coming soon page
		self::send_headers( $effective_status, $settings );
		Template_Engine::render( $settings['template'], $settings, false, $effective_status );
		exit;
	}

	/**
	 * Send HTTP response headers according to mode.
	 *
	 * @param string $status
	 * @param array  $settings
	 */
	protected static function send_headers( $status, array $settings ) {
		// Prevent downstream caching of maintenance screens
		nocache_headers();
		header( 'Cache-Control: no-cache, no-store, must-revalidate, max-age=0' );
		header( 'Pragma: no-cache' );
		header( 'Expires: Wed, 11 Jan 1984 05:00:00 GMT' );

		if ( 'maintenance' === $status ) {
			// HTTP 503 Service Unavailable
			status_header( 503 );
			$retry_after = ! empty( $settings['retry_after'] ) ? (int) $settings['retry_after'] : 3600;
			header( 'Retry-After: ' . $retry_after );
			header( 'X-Robots-Tag: noindex, nofollow, noarchive', true );
		} elseif ( 'locked' === $status ) {
			// Password Locked Site Mode: HTTP 200 OK (or 401/403) with strict noindex
			status_header( 200 );
			header( 'X-Robots-Tag: noindex, nofollow, noarchive', true );
		} else {
			// Coming Soon mode: HTTP 200 OK
			status_header( 200 );
			if ( empty( $settings['coming_soon_index'] ) ) {
				header( 'X-Robots-Tag: noindex, nofollow', true );
			}
		}
	}

	/**
	 * Determine if request should bypass maintenance entirely.
	 *
	 * @return bool
	 */
	protected static function is_excluded_request() {
		// Admin screens
		if ( is_admin() ) {
			return true;
		}

		// WP-CLI
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return true;
		}

		// WP-Cron
		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			return true;
		}

		// Login and Signup pages
		global $pagenow;
		if ( in_array( $pagenow, [ 'wp-login.php', 'wp-signup.php', 'wp-activate.php' ], true ) ) {
			return true;
		}

		// REST API requests
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return true;
		}

		// XML-RPC
		if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
			return true;
		}

		// WooCommerce Webhooks and API endpoints
		if ( isset( $_GET['wc-api'] ) || isset( $_GET['wc-ajax'] ) ) {
			return true;
		}

		// Trackback
		if ( is_trackback() ) {
			return true;
		}

		return false;
	}
}
