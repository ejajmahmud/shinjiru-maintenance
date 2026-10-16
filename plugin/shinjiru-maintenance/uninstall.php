<?php
/**
 * Uninstallation cleanup routine.
 *
 * @package Shinjiru\Maintenance
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$settings = get_option( 'shinjiru_maintenance_settings', [] );

// Only purge data if delete_on_uninstall is explicitly enabled.
if ( ! empty( $settings['delete_on_uninstall'] ) ) {
	delete_option( 'shinjiru_maintenance_settings' );
	delete_option( 'shinjiru_maintenance_version' );
	delete_transient( 'shinjiru_maintenance_bypass_tokens' );
}
