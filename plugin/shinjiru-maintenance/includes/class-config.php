<?php
/**
 * Configuration and Settings Repository.
 *
 * @package Shinjiru\Maintenance
 */

namespace Shinjiru\Maintenance;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Config {

	const OPTION_KEY = 'shinjiru_maintenance_settings';

	/**
	 * Retrieve default plugin options.
	 *
	 * @return array
	 */
	public static function get_defaults() {
		return [
			// Mode: 'off', 'locked', 'maintenance', 'coming_soon'
			'status'                => 'off',
			'page_title'            => __( 'Site Under Maintenance', 'shinjiru-maintenance' ),
			'headline'              => __( 'We will be back shortly.', 'shinjiru-maintenance' ),
			'description'           => __( 'We are performing scheduled maintenance and updates to improve your experience. Thank you for your patience!', 'shinjiru-maintenance' ),
			'footer_text'           => __( '&copy; ' . date( 'Y' ) . ' All rights reserved.', 'shinjiru-maintenance' ),
			'template'              => 'minimal',
			'logo_url'              => '',
			'logo_id'               => 0,
			'custom_css'            => '',
			
			// CTA Button
			'show_cta'              => false,
			'cta_text'              => __( 'Contact Support', 'shinjiru-maintenance' ),
			'cta_url'               => '',

			// Countdown
			'show_countdown'        => false,
			'countdown_target'      => '', // Format: Y-m-d H:i:s

			// Social Links
			'social_x'              => '',
			'social_linkedin'       => '',
			'social_facebook'       => '',
			'social_instagram'      => '',
			'social_github'         => '',

			// SEO & Headers
			'retry_after'           => 3600, // seconds
			'coming_soon_index'     => true, // allow indexing in coming soon

			// Access Controls
			'allowed_roles'          => [ 'administrator' ],
			'allow_admin'            => true, // Logged in WP users/admins bypass password protection
			'enable_password'        => false,
			'password_hash'          => '',
			'password_session_hours' => 24, // 24 hours session
			'bypass_token'           => wp_generate_password( 24, false ),
			'bypass_expires_hours'   => 168, // 7 days
			'ip_allowlist'           => '',

			// Schedule Controls
			'schedule_enabled'      => false,
			'schedule_start'        => '',
			'schedule_end'          => '',

			// Portability / Cleanup
			'delete_on_uninstall'   => false,
		];
	}

	/**
	 * Retrieve current plugin settings merged with defaults.
	 *
	 * @return array
	 */
	public static function get_settings() {
		$saved = get_option( self::OPTION_KEY, [] );
		if ( ! is_array( $saved ) ) {
			$saved = [];
		}
		return wp_parse_args( $saved, self::get_defaults() );
	}

	/**
	 * Update settings.
	 *
	 * @param array $settings
	 * @return bool
	 */
	public static function update_settings( array $settings ) {
		$sanitized = self::sanitize_settings( $settings );
		return update_option( self::OPTION_KEY, $sanitized );
	}

	/**
	 * Sanitize raw settings array.
	 *
	 * @param array $input
	 * @return array
	 */
	public static function sanitize_settings( array $input ) {
		$defaults = self::get_defaults();
		$output = [];

		// Status
		$allowed_statuses = [ 'off', 'locked', 'maintenance', 'coming_soon' ];
		$output['status'] = ( isset( $input['status'] ) && in_array( $input['status'], $allowed_statuses, true ) ) ? $input['status'] : 'off';

		// Text fields
		$output['page_title']  = ! empty( $input['page_title'] ) ? sanitize_text_field( $input['page_title'] ) : $defaults['page_title'];
		$output['headline']    = ! empty( $input['headline'] ) ? sanitize_text_field( wp_strip_all_tags( $input['headline'] ) ) : $defaults['headline'];
		$output['description'] = ! empty( $input['description'] ) ? wp_kses_post( $input['description'] ) : $defaults['description'];
		$output['footer_text'] = ! empty( $input['footer_text'] ) ? wp_kses_post( $input['footer_text'] ) : $defaults['footer_text'];

		// Template selection
		$valid_templates = [ 'minimal', 'corporate', 'dark-tech', 'editorial', 'split', 'calm-gradient' ];
		$output['template'] = ( isset( $input['template'] ) && in_array( $input['template'], $valid_templates, true ) ) ? $input['template'] : 'minimal';

		// Media
		$output['logo_url'] = ! empty( $input['logo_url'] ) ? esc_url_raw( $input['logo_url'] ) : '';
		$output['logo_id']  = ! empty( $input['logo_id'] ) ? absint( $input['logo_id'] ) : 0;

		// Custom CSS
		$output['custom_css'] = ! empty( $input['custom_css'] ) ? wp_strip_all_tags( $input['custom_css'] ) : '';

		// CTA Button
		$output['show_cta'] = ! empty( $input['show_cta'] );
		$output['cta_text'] = ! empty( $input['cta_text'] ) ? sanitize_text_field( $input['cta_text'] ) : $defaults['cta_text'];
		$output['cta_url']  = ! empty( $input['cta_url'] ) ? esc_url_raw( $input['cta_url'] ) : '';

		// Countdown
		$output['show_countdown']   = ! empty( $input['show_countdown'] );
		$output['countdown_target'] = ! empty( $input['countdown_target'] ) ? sanitize_text_field( $input['countdown_target'] ) : '';

		// Social
		$output['social_x']         = ! empty( $input['social_x'] ) ? esc_url_raw( $input['social_x'] ) : '';
		$output['social_linkedin']  = ! empty( $input['social_linkedin'] ) ? esc_url_raw( $input['social_linkedin'] ) : '';
		$output['social_facebook']  = ! empty( $input['social_facebook'] ) ? esc_url_raw( $input['social_facebook'] ) : '';
		$output['social_instagram'] = ! empty( $input['social_instagram'] ) ? esc_url_raw( $input['social_instagram'] ) : '';
		$output['social_github']    = ! empty( $input['social_github'] ) ? esc_url_raw( $input['social_github'] ) : '';

		// SEO
		$output['retry_after']       = isset( $input['retry_after'] ) ? max( 60, min( 86400, absint( $input['retry_after'] ) ) ) : 3600;
		$output['coming_soon_index'] = ! empty( $input['coming_soon_index'] );

		// Roles
		$output['allowed_roles'] = [];
		if ( ! empty( $input['allowed_roles'] ) && is_array( $input['allowed_roles'] ) ) {
			$editable_roles = array_keys( wp_roles()->roles );
			foreach ( $input['allowed_roles'] as $role ) {
				if ( in_array( $role, $editable_roles, true ) ) {
					$output['allowed_roles'][] = sanitize_key( $role );
				}
			}
		}
		if ( empty( $output['allowed_roles'] ) ) {
			$output['allowed_roles'] = [ 'administrator' ];
		}

		// Admin bypass toggle & password
		$output['allow_admin'] = isset( $input['allow_admin'] ) ? ! empty( $input['allow_admin'] ) : true;
		$output['enable_password'] = ! empty( $input['enable_password'] );
		$output['password_session_hours'] = isset( $input['password_session_hours'] ) ? max( 1, min( 720, absint( $input['password_session_hours'] ) ) ) : 24;
		$current = self::get_settings();
		if ( ! empty( $input['new_password'] ) ) {
			$output['password_hash'] = wp_hash_password( trim( $input['new_password'] ) );
		} else {
			$output['password_hash'] = ! empty( $current['password_hash'] ) ? $current['password_hash'] : '';
		}

		// Bypass token
		if ( ! empty( $input['regenerate_token'] ) || empty( $current['bypass_token'] ) ) {
			$output['bypass_token'] = wp_generate_password( 24, false );
		} else {
			$output['bypass_token'] = ! empty( $current['bypass_token'] ) ? $current['bypass_token'] : wp_generate_password( 24, false );
		}
		$output['bypass_expires_hours'] = isset( $input['bypass_expires_hours'] ) ? max( 1, absint( $input['bypass_expires_hours'] ) ) : 168;

		// IP Allowlist
		$output['ip_allowlist'] = ! empty( $input['ip_allowlist'] ) ? sanitize_textarea_field( $input['ip_allowlist'] ) : '';

		// Schedule
		$output['schedule_enabled'] = ! empty( $input['schedule_enabled'] );
		$output['schedule_start']   = ! empty( $input['schedule_start'] ) ? sanitize_text_field( $input['schedule_start'] ) : '';
		$output['schedule_end']     = ! empty( $input['schedule_end'] ) ? sanitize_text_field( $input['schedule_end'] ) : '';

		// Portability
		$output['delete_on_uninstall'] = ! empty( $input['delete_on_uninstall'] );

		return $output;
	}
}
