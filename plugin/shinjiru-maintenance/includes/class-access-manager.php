<?php
/**
 * Access and Bypass Verification Manager.
 *
 * @package Shinjiru\Maintenance
 */

namespace Shinjiru\Maintenance;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Access_Manager {

	const COOKIE_NAME = 'shinjiru_maint_access';

	/**
	 * Verify if current visitor has valid access to bypass the maintenance wall.
	 *
	 * @param array $settings
	 * @return bool
	 */
	public static function has_access( array $settings ) {
		// 1. Check logged-in user roles & capabilities
		if ( is_user_logged_in() ) {
			// If allow_admin is enabled, administrators and allowed roles bypass automatically
			$allow_admin = ! isset( $settings['allow_admin'] ) || ! empty( $settings['allow_admin'] );
			if ( $allow_admin ) {
				$allowed_roles = ! empty( $settings['allowed_roles'] ) ? (array) $settings['allowed_roles'] : [ 'administrator' ];
				$user = wp_get_current_user();
				if ( $user && ! empty( $user->roles ) ) {
					foreach ( $user->roles as $role ) {
						if ( in_array( $role, $allowed_roles, true ) ) {
							return true;
						}
					}
				}
				// Administrators with manage_options always retain bypass access
				if ( current_user_can( 'manage_options' ) ) {
					return true;
				}
			}
		}

		// 2. Check IP allowlist
		if ( ! empty( $settings['ip_allowlist'] ) ) {
			$visitor_ip = self::get_visitor_ip();
			if ( self::is_ip_allowed( $visitor_ip, $settings['ip_allowlist'] ) ) {
				return true;
			}
		}

		// 3. Check for Secret Bypass URL Parameter
		if ( ! empty( $settings['bypass_token'] ) && isset( $_GET['shinjiru_bypass'] ) ) {
			$passed_token = sanitize_text_field( wp_unslash( $_GET['shinjiru_bypass'] ) );
			if ( hash_equals( (string) $settings['bypass_token'], $passed_token ) ) {
				// Set authenticated bypass session cookie
				self::set_bypass_cookie( (int) $settings['bypass_expires_hours'] );
				return true;
			}
		}

		// 4. Check for active authenticated session cookie
		if ( self::verify_access_cookie() ) {
			return true;
		}

		// 5. Check if visitor is submitting valid password (applicable in 'locked' mode or when enable_password is on)
		$password_active = ( 'locked' === $settings['status'] ) || ! empty( $settings['enable_password'] );
		if ( $password_active && ! empty( $settings['password_hash'] ) && isset( $_POST['shinjiru_password'] ) ) {
			if ( isset( $_POST['shinjiru_pass_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['shinjiru_pass_nonce'] ) ), 'shinjiru_pass_action' ) ) {
				$submitted_password = isset( $_POST['shinjiru_password'] ) ? sanitize_text_field( wp_unslash( $_POST['shinjiru_password'] ) ) : '';
				if ( wp_check_password( $submitted_password, $settings['password_hash'] ) ) {
					$session_duration = ! empty( $settings['password_session_hours'] ) ? (int) $settings['password_session_hours'] : 24;
					self::set_bypass_cookie( $session_duration );
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Retrieve visitor IP safely.
	 *
	 * @return string
	 */
	public static function get_visitor_ip() {
		return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	}

	/**
	 * Match an IP against an allowlist supporting IPv4, IPv6, and CIDR blocks.
	 *
	 * @param string $ip
	 * @param string $allowlist_raw
	 * @return bool
	 */
	public static function is_ip_allowed( $ip, $allowlist_raw ) {
		if ( empty( $ip ) || empty( $allowlist_raw ) ) {
			return false;
		}

		$lines = preg_split( '/[\r\n,]+/', $allowlist_raw );
		foreach ( $lines as $entry ) {
			$entry = trim( $entry );
			if ( empty( $entry ) ) {
				continue;
			}

			// Direct exact match
			if ( $ip === $entry ) {
				return true;
			}

			// CIDR block evaluation
			if ( strpos( $entry, '/' ) !== false ) {
				if ( self::cidr_match( $ip, $entry ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check if IPv4 is within a CIDR range.
	 *
	 * @param string $ip
	 * @param string $range
	 * @return bool
	 */
	protected static function cidr_match( $ip, $range ) {
		list( $subnet, $bits ) = explode( '/', $range );
		$ip_long = ip2long( $ip );
		$subnet_long = ip2long( $subnet );
		if ( false === $ip_long || false === $subnet_long ) {
			return false;
		}
		$bits = (int) $bits;
		if ( $bits < 0 || $bits > 32 ) {
			return false;
		}
		$mask = -1 << ( 32 - $bits );
		$subnet_long &= $mask;
		return ( $ip_long & $mask ) === $subnet_long;
	}

	/**
	 * Set cryptographic bypass session cookie.
	 *
	 * @param int $hours
	 */
	public static function set_bypass_cookie( $hours = 24 ) {
		$expiry = time() + ( $hours * 3600 );
		$payload = $expiry . '|' . wp_salt( 'auth' );
		$signature = hash_hmac( 'sha256', $payload, wp_salt( 'secure_auth' ) );
		$cookie_value = $expiry . '|' . $signature;

		$secure = is_ssl();
		if ( ! headers_sent() ) {
			if ( PHP_VERSION_ID >= 70300 ) {
				setcookie( self::COOKIE_NAME, $cookie_value, [
					'expires'  => $expiry,
					'path'     => COOKIEPATH ?: '/',
					'domain'   => COOKIE_DOMAIN ?: '',
					'secure'   => $secure,
					'httponly' => true,
					'samesite' => 'Lax',
				] );
			} else {
				setcookie( self::COOKIE_NAME, $cookie_value, $expiry, ( COOKIEPATH ?: '/' ) . '; SameSite=Lax', COOKIE_DOMAIN ?: '', $secure, true );
			}
		}

		$_COOKIE[ self::COOKIE_NAME ] = $cookie_value;
	}

	/**
	 * Verify bypass cookie validity and HMAC signature.
	 *
	 * @return bool
	 */
	public static function verify_access_cookie() {
		if ( empty( $_COOKIE[ self::COOKIE_NAME ] ) ) {
			return false;
		}

		$raw = sanitize_text_field( wp_unslash( $_COOKIE[ self::COOKIE_NAME ] ) );
		$parts = explode( '|', $raw );
		if ( count( $parts ) !== 2 ) {
			return false;
		}

		list( $expiry, $signature ) = $parts;
		$expiry = (int) $expiry;
		if ( $expiry < time() ) {
			return false;
		}

		$expected_payload = $expiry . '|' . wp_salt( 'auth' );
		$expected_signature = hash_hmac( 'sha256', $expected_payload, wp_salt( 'secure_auth' ) );

		return hash_equals( $expected_signature, $signature );
	}
}
