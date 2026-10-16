<?php
/**
 * Timezone-aware Scheduler.
 *
 * @package Shinjiru\Maintenance
 */

namespace Shinjiru\Maintenance;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Scheduler {

	/**
	 * Determine if maintenance is currently active based on schedule settings.
	 *
	 * @param array $settings
	 * @return bool
	 */
	public static function is_schedule_active( array $settings ) {
		if ( empty( $settings['schedule_enabled'] ) ) {
			return false;
		}

		$start_str = ! empty( $settings['schedule_start'] ) ? trim( $settings['schedule_start'] ) : '';
		$end_str   = ! empty( $settings['schedule_end'] ) ? trim( $settings['schedule_end'] ) : '';

		// If schedule is enabled but no dates are provided, return false.
		if ( empty( $start_str ) && empty( $end_str ) ) {
			return false;
		}

		$tz = function_exists( 'wp_timezone' ) ? wp_timezone() : new \DateTimeZone( get_option( 'timezone_string' ) ?: 'UTC' );
		$now = function_exists( 'current_datetime' ) ? current_datetime() : new \DateTimeImmutable( 'now', $tz );

		$start_dt = null;
		$end_dt   = null;

		if ( ! empty( $start_str ) ) {
			try {
				$start_dt = new \DateTimeImmutable( $start_str, $tz );
			} catch ( \Exception $e ) {
				$start_dt = null;
			}
		}

		if ( ! empty( $end_str ) ) {
			try {
				$end_dt = new \DateTimeImmutable( $end_str, $tz );
			} catch ( \Exception $e ) {
				$end_dt = null;
			}
		}

		// Handle reversed dates safely
		if ( $start_dt && $end_dt && $start_dt > $end_dt ) {
			// Swap to prevent invalid lockout
			$tmp = $start_dt;
			$start_dt = $end_dt;
			$end_dt = $tmp;
		}

		// Check start threshold
		if ( $start_dt && $now < $start_dt ) {
			return false; // hasn't started yet
		}

		// Check end threshold
		if ( $end_dt && $now >= $end_dt ) {
			return false; // already expired
		}

		return true;
	}
}
