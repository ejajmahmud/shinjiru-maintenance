<?php
/**
 * Plugin Name:       Shinjiru Maintenance
 * Plugin URI:        https://wordpress.org/plugins/shinjiru-maintenance/
 * Description:       A professional, secure, and lightweight maintenance and coming soon plugin with designer templates, robust access controls, and schedule management.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Ejaj Mahmud
 * Author URI:        https://profiles.wordpress.org/ejajmahmud/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       shinjiru-maintenance
 * Domain Path:       /languages
 */

namespace Shinjiru\Maintenance;

// Prevent direct execution.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'SHINJIRU_MAINTENANCE_VERSION', '1.0.0' );
define( 'SHINJIRU_MAINTENANCE_FILE', __FILE__ );
define( 'SHINJIRU_MAINTENANCE_PATH', plugin_dir_path( __FILE__ ) );
define( 'SHINJIRU_MAINTENANCE_URL', plugin_dir_url( __FILE__ ) );
define( 'SHINJIRU_MAINTENANCE_BASENAME', plugin_basename( __FILE__ ) );

// Autoload internal classes.
spl_autoload_register( function ( $class ) {
	$prefix = 'Shinjiru\\Maintenance\\';
	$base_dir = SHINJIRU_MAINTENANCE_PATH . 'includes/';

	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	$relative_class = substr( $class, $len );
	$parts = explode( '\\', $relative_class );
	$file_name = 'class-' . strtolower( str_replace( '_', '-', array_pop( $parts ) ) ) . '.php';
	$file_path = $base_dir . ( ! empty( $parts ) ? strtolower( implode( '/', $parts ) ) . '/' : '' ) . $file_name;

	if ( file_exists( $file_path ) ) {
		require_once $file_path;
	}
} );

// Lifecycle hooks.
register_activation_hook( __FILE__, [ 'Shinjiru\\Maintenance\\Lifecycle', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'Shinjiru\\Maintenance\\Lifecycle', 'deactivate' ] );

// Initialize the plugin.
function shinjiru_maintenance_init() {
	load_plugin_textdomain( 'shinjiru-maintenance', false, dirname( SHINJIRU_MAINTENANCE_BASENAME ) . '/languages' );
	Plugin::instance();
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\\shinjiru_maintenance_init' );
