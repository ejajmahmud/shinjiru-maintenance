<?php
/**
 * Main Plugin Orchestrator Singleton.
 *
 * @package Shinjiru\Maintenance
 */

namespace Shinjiru\Maintenance;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Plugin
	 */
	protected static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	protected function __construct() {
		// Initialize admin hooks
		if ( is_admin() ) {
			Admin::init();
		}

		// Register front-end interceptor on template_redirect
		add_action( 'template_redirect', [ 'Shinjiru\\Maintenance\\Interceptor', 'maybe_intercept' ], 1 );

		// Register settings link on plugins page
		add_filter( 'plugin_action_links_' . SHINJIRU_MAINTENANCE_BASENAME, [ $this, 'add_plugin_action_links' ] );
	}

	/**
	 * Add Settings shortcut link to plugins.php.
	 *
	 * @param array $links
	 * @return array
	 */
	public function add_plugin_action_links( array $links ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'options-general.php?page=' . Admin::MENU_SLUG ) ),
			esc_html__( 'Settings', 'shinjiru-maintenance' )
		);
		array_unshift( $links, $settings_link );
		return $links;
	}
}
