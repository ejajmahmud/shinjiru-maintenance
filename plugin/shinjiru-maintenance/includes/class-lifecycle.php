<?php
/**
 * Plugin Lifecycle Handlers (Activation & Deactivation).
 *
 * @package Shinjiru\Maintenance
 */

namespace Shinjiru\Maintenance;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Lifecycle {

	/**
	 * Activation callback.
	 */
	public static function activate() {
		// Set default settings if not already present
		$existing = get_option( Config::OPTION_KEY, null );
		if ( null === $existing ) {
			add_option( Config::OPTION_KEY, Config::get_defaults() );
		}

		// Record current version
		update_option( 'shinjiru_maintenance_version', SHINJIRU_MAINTENANCE_VERSION );
	}

	/**
	 * Deactivation callback.
	 */
	public static function deactivate() {
		// Ensure any active bypass cookie is cleared for visitor safety
		if ( isset( $_COOKIE[ Access_Manager::COOKIE_NAME ] ) ) {
			setcookie( Access_Manager::COOKIE_NAME, '', time() - 3600, COOKIEPATH ?: '/', COOKIE_DOMAIN ?: '', is_ssl(), true );
		}
	}
}
