<?php
/**
 * Settings Portability (Export & Import).
 *
 * @package Shinjiru\Maintenance
 */

namespace Shinjiru\Maintenance;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Portability {

	/**
	 * Export current settings as a clean JSON file (secrets excluded).
	 */
	public static function export_json() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'shinjiru-maintenance' ), 403 );
		}

		check_admin_referer( 'shinjiru_maint_export', 'shinjiru_export_nonce' );

		$settings = Config::get_settings();

		// Exclude sensitive secrets from portable export
		unset( $settings['password_hash'] );
		unset( $settings['bypass_token'] );

		$export_data = [
			'generator' => 'Shinjiru Maintenance',
			'version'   => SHINJIRU_MAINTENANCE_VERSION,
			'exported'  => gmdate( 'c' ),
			'settings'  => $settings,
		];

		$json = wp_json_encode( $export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
		$filename = 'shinjiru-maintenance-export-' . gmdate( 'Y-m-d-His' ) . '.json';

		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );
		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	/**
	 * Import settings from uploaded JSON file.
	 *
	 * @return array Status message and type
	 */
	public static function import_json() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return [ 'error', __( 'Unauthorized.', 'shinjiru-maintenance' ) ];
		}

		if ( ! isset( $_POST['shinjiru_import_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['shinjiru_import_nonce'] ) ), 'shinjiru_maint_import' ) ) {
			return [ 'error', __( 'Security verification failed. Please try again.', 'shinjiru-maintenance' ) ];
		}

		if ( empty( $_FILES['shinjiru_import_file']['tmp_name'] ) ) {
			return [ 'error', __( 'Please select a valid JSON export file to import.', 'shinjiru-maintenance' ) ];
		}

		$file = sanitize_text_field( wp_unslash( $_FILES['shinjiru_import_file']['tmp_name'] ) );
		$content = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if ( empty( $content ) ) {
			return [ 'error', __( 'The uploaded file is empty.', 'shinjiru-maintenance' ) ];
		}

		$data = json_decode( $content, true );
		if ( ! is_array( $data ) || empty( $data['settings'] ) || ! is_array( $data['settings'] ) ) {
			return [ 'error', __( 'Invalid file format. The file is not a valid Shinjiru Maintenance export.', 'shinjiru-maintenance' ) ];
		}

		// Preserve current secrets
		$current = Config::get_settings();
		$new_settings = $data['settings'];
		$new_settings['password_hash'] = ! empty( $current['password_hash'] ) ? $current['password_hash'] : '';
		$new_settings['bypass_token']  = ! empty( $current['bypass_token'] ) ? $current['bypass_token'] : wp_generate_password( 24, false );

		Config::update_settings( $new_settings );

		return [ 'success', __( 'Settings imported successfully!', 'shinjiru-maintenance' ) ];
	}
}
