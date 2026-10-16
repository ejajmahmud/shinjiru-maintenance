<?php
/**
 * Template Rendering Engine.
 *
 * @package Shinjiru\Maintenance
 */

namespace Shinjiru\Maintenance;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Template_Engine {

	/**
	 * Available templates list.
	 *
	 * @return array
	 */
	public static function get_templates() {
		return [
			'minimal' => [
				'id'          => 'minimal',
				'name'        => __( 'Minimal Professional', 'shinjiru-maintenance' ),
				'description' => __( 'Clean, monochromatic, distraction-free layout focused on clear typography.', 'shinjiru-maintenance' ),
			],
			'corporate' => [
				'id'          => 'corporate',
				'name'        => __( 'Corporate Enterprise', 'shinjiru-maintenance' ),
				'description' => __( 'Structured, trustworthy corporate aesthetic with status badge and formal styling.', 'shinjiru-maintenance' ),
			],
			'dark-tech' => [
				'id'          => 'dark-tech',
				'name'        => __( 'Dark Cyber Technology', 'shinjiru-maintenance' ),
				'description' => __( 'Sleek dark theme (#0b0f19) with glowing cyan accents and high-tech countdown cards.', 'shinjiru-maintenance' ),
			],
			'editorial' => [
				'id'          => 'editorial',
				'name'        => __( 'Modern Editorial', 'shinjiru-maintenance' ),
				'description' => __( 'Refined serif typography, classic margins, and elegant artistic dividers.', 'shinjiru-maintenance' ),
			],
			'split' => [
				'id'          => 'split',
				'name'        => __( 'Split Visual Hero', 'shinjiru-maintenance' ),
				'description' => __( 'Modern two-column layout featuring an architectural visual hero on one side and content on the other.', 'shinjiru-maintenance' ),
			],
			'calm-gradient' => [
				'id'          => 'calm-gradient',
				'name'        => __( 'Calm Aurora Gradient', 'shinjiru-maintenance' ),
				'description' => __( 'Soft multi-stop atmospheric gradient with frosted glassmorphic card elements.', 'shinjiru-maintenance' ),
			],
		];
	}

	/**
	 * Render the chosen template.
	 *
	 * @param string $template_id
	 * @param array  $settings
	 * @param bool   $is_preview
	 * @param string $status
	 */
	public static function render( $template_id, array $settings, $is_preview = false, $status = 'maintenance' ) {
		$templates = self::get_templates();
		if ( ! array_key_exists( $template_id, $templates ) ) {
			$template_id = 'minimal';
		}

		$file = SHINJIRU_MAINTENANCE_PATH . 'templates/' . $template_id . '.php';
		if ( ! file_exists( $file ) ) {
			$file = SHINJIRU_MAINTENANCE_PATH . 'templates/minimal.php';
		}

		// Prepare variables for the template view
		$title       = esc_html( $settings['page_title'] );
		$headline    = esc_html( $settings['headline'] );
		$description = wp_kses_post( $settings['description'] );
		$footer_text = wp_kses_post( $settings['footer_text'] );
		$default_logo = SHINJIRU_MAINTENANCE_URL . 'assets/images/shinjiru-logo.png';
		$logo_url    = ! empty( $settings['logo_url'] ) ? esc_url( $settings['logo_url'] ) : esc_url( $default_logo );
		$custom_css  = ! empty( $settings['custom_css'] ) ? $settings['custom_css'] : '';
		
		$show_cta    = ! empty( $settings['show_cta'] );
		$cta_text    = esc_html( $settings['cta_text'] );
		$cta_url     = esc_url( $settings['cta_url'] );

		$show_countdown   = ! empty( $settings['show_countdown'] );
		$countdown_target = esc_attr( $settings['countdown_target'] );

		// In 'locked' mode, password gate is always active so visitors can unlock the site
		$enable_password = ( 'locked' === $status || ( ! empty( $settings['enable_password'] ) && ! empty( $settings['password_hash'] ) ) ) && ! $is_preview;

		$socials = [
			'x'         => ! empty( $settings['social_x'] ) ? esc_url( $settings['social_x'] ) : '',
			'linkedin'  => ! empty( $settings['social_linkedin'] ) ? esc_url( $settings['social_linkedin'] ) : '',
			'facebook'  => ! empty( $settings['social_facebook'] ) ? esc_url( $settings['social_facebook'] ) : '',
			'instagram' => ! empty( $settings['social_instagram'] ) ? esc_url( $settings['social_instagram'] ) : '',
			'github'    => ! empty( $settings['social_github'] ) ? esc_url( $settings['social_github'] ) : '',
		];

		// Register actions to cleanly enqueue styles and scripts for the template
		add_action( 'wp_enqueue_scripts', function() use ( $custom_css ) {
			wp_enqueue_style(
				'shinjiru-maintenance-templates',
				SHINJIRU_MAINTENANCE_URL . 'assets/css/templates.css',
				[],
				SHINJIRU_MAINTENANCE_VERSION
			);

			if ( ! empty( $custom_css ) ) {
				wp_add_inline_style( 'shinjiru-maintenance-templates', wp_strip_all_tags( $custom_css ) );
			}

			wp_enqueue_script(
				'shinjiru-maintenance-countdown',
				SHINJIRU_MAINTENANCE_URL . 'assets/js/countdown.js',
				[],
				SHINJIRU_MAINTENANCE_VERSION,
				true
			);
		} );

		// Include the template
		include $file;
	}
}
