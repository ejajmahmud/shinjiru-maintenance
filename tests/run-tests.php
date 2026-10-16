<?php
/**
 * Automated Unit & Logic Verification Suite for Shinjiru Maintenance.
 *
 * Runs without a live database by mocking core WordPress functions and verifying
 * access control, scheduling logic, security tokens, IP matching, and sanitization.
 */

define( 'ABSPATH', __DIR__ . '/../' );
define( 'SHINJIRU_MAINTENANCE_VERSION', '1.0.0' );
define( 'SHINJIRU_MAINTENANCE_PATH', dirname( __DIR__ ) . '/plugin/shinjiru-maintenance/' );
define( 'SHINJIRU_MAINTENANCE_URL', 'http://example.com/wp-content/plugins/shinjiru-maintenance/' );
define( 'SHINJIRU_MAINTENANCE_BASENAME', 'shinjiru-maintenance/shinjiru-maintenance.php' );
define( 'COOKIEPATH', '/' );
define( 'COOKIE_DOMAIN', '' );

// Mock WordPress Core Functions
$GLOBALS['mock_options'] = [];
$GLOBALS['mock_current_user'] = null;

function __( $text, $domain = 'default' ) { return $text; }
function esc_html__( $text, $domain = 'default' ) { return $text; }
function esc_attr__( $text, $domain = 'default' ) { return $text; }
function esc_html( $text ) { return htmlspecialchars( (string)$text, ENT_QUOTES, 'UTF-8' ); }
function esc_attr( $text ) { return htmlspecialchars( (string)$text, ENT_QUOTES, 'UTF-8' ); }
function esc_url( $url ) { return filter_var( $url, FILTER_SANITIZE_URL ); }
function esc_url_raw( $url ) { return filter_var( $url, FILTER_SANITIZE_URL ); }
function sanitize_text_field( $str ) { return strip_tags( trim( (string)$str ) ); }
function sanitize_textarea_field( $str ) { return strip_tags( trim( (string)$str ) ); }
function sanitize_key( $key ) { return preg_replace( '/[^a-z0-9_\-]/i', '', strtolower( (string)$key ) ); }
function wp_unslash( $val ) { return is_array( $val ) ? array_map( 'wp_unslash', $val ) : stripslashes( (string)$val ); }
function wp_strip_all_tags( $string, $remove_breaks = false ) {
	$string = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', (string)$string );
	$string = strip_tags( $string );
	return trim( $string );
}
function wp_kses_post( $str ) { return $str; }
function absint( $num ) { return abs( (int)$num ); }
function wp_parse_args( $args, $defaults = [] ) { return array_merge( $defaults, (array)$args ); }
function get_option( $name, $default = false ) { return isset( $GLOBALS['mock_options'][$name] ) ? $GLOBALS['mock_options'][$name] : $default; }
function update_option( $name, $value ) { $GLOBALS['mock_options'][$name] = $value; return true; }
function delete_option( $name ) { unset( $GLOBALS['mock_options'][$name] ); return true; }
function is_user_logged_in() { return ! empty( $GLOBALS['mock_current_user'] ); }
function wp_get_current_user() { return (object)$GLOBALS['mock_current_user']; }
function current_user_can( $cap ) {
	if ( empty( $GLOBALS['mock_current_user'] ) ) return false;
	return in_array( $cap, $GLOBALS['mock_current_user']['caps'], true );
}
function wp_salt( $scheme = 'auth' ) { return 'mock_salt_secret_key_1234567890_' . $scheme; }
function is_ssl() { return false; }
function wp_generate_password( $length = 12, $special_chars = true ) { return bin2hex( random_bytes( (int)($length / 2) ) ); }
function wp_hash_password( $pass ) { return password_hash( $pass, PASSWORD_BCRYPT ); }
function wp_check_password( $pass, $hash ) { return password_verify( $pass, $hash ); }
function wp_timezone() { return new DateTimeZone( 'UTC' ); }
function wp_timezone_string() { return 'UTC'; }
function current_datetime() { return new DateTimeImmutable( '2026-10-16 12:00:00', new DateTimeZone( 'UTC' ) ); }
function wp_roles() {
	return (object)[ 'roles' => [
		'administrator' => [ 'name' => 'Administrator' ],
		'editor'        => [ 'name' => 'Editor' ],
		'author'        => [ 'name' => 'Author' ],
		'subscriber'    => [ 'name' => 'Subscriber' ],
	] ];
}

// Require target plugin classes
require_once SHINJIRU_MAINTENANCE_PATH . 'includes/class-config.php';
require_once SHINJIRU_MAINTENANCE_PATH . 'includes/class-scheduler.php';
require_once SHINJIRU_MAINTENANCE_PATH . 'includes/class-access-manager.php';
require_once SHINJIRU_MAINTENANCE_PATH . 'includes/class-template-engine.php';

use Shinjiru\Maintenance\Config;
use Shinjiru\Maintenance\Scheduler;
use Shinjiru\Maintenance\Access_Manager;
use Shinjiru\Maintenance\Template_Engine;

// Test Execution Runner
$passed = 0;
$failed = 0;

function it( $description, $callback ) {
	global $passed, $failed;
	try {
		$result = $callback();
		if ( false !== $result ) {
			echo "[\033[32mPASS\033[0m] " . $description . PHP_EOL;
			$passed++;
		} else {
			echo "[\033[31mFAIL\033[0m] " . $description . PHP_EOL;
			$failed++;
		}
	} catch ( Exception $e ) {
		echo "[\033[31mFAIL\033[0m] " . $description . " - " . $e->getMessage() . PHP_EOL;
		$failed++;
	}
}

echo "=== Shinjiru Maintenance Test Suite ===" . PHP_EOL . PHP_EOL;

// 1. Config & Defaults
it( 'Loads valid default settings with status off', function() {
	$defaults = Config::get_defaults();
	return 'off' === $defaults['status'] && 'minimal' === $defaults['template'] && ! empty( $defaults['bypass_token'] );
} );

it( 'Sanitizes input cleanly and prevents invalid modes', function() {
	$dirty = [
		'status' => 'arbitrary_hack_mode',
		'headline' => '<script>alert("xss")</script>Hello World',
		'retry_after' => 20, // below min threshold of 60
	];
	$sanitized = Config::sanitize_settings( $dirty );
	if ( 'off' !== $sanitized['status'] || 'Hello World' !== $sanitized['headline'] || 60 !== $sanitized['retry_after'] ) {
		echo "DEBUG: status={$sanitized['status']}, headline={$sanitized['headline']}, retry_after={$sanitized['retry_after']}" . PHP_EOL;
		return false;
	}
	return true;
} );

// 2. IP Allowlist Verification
it( 'Allows exact IPv4 match', function() {
	$list = "127.0.0.1\n192.168.1.50\n10.0.0.1";
	return Access_Manager::is_ip_allowed( '192.168.1.50', $list );
} );

it( 'Rejects unauthorized IP address', function() {
	$list = "127.0.0.1\n192.168.1.50";
	return ! Access_Manager::is_ip_allowed( '192.168.1.99', $list );
} );

it( 'Correctly evaluates IPv4 CIDR blocks', function() {
	$list = "10.0.0.0/24\n172.16.0.0/16";
	$in_range = Access_Manager::is_ip_allowed( '10.0.0.45', $list );
	$out_of_range = Access_Manager::is_ip_allowed( '10.0.1.45', $list );
	return $in_range && ! $out_of_range;
} );

// 3. Timezone & Schedule Evaluations
it( 'Evaluates scheduled maintenance as active between start and end', function() {
	// current_datetime() is mocked to 2026-10-16 12:00:00
	$settings = [
		'schedule_enabled' => true,
		'schedule_start'   => '2026-10-16 10:00:00',
		'schedule_end'     => '2026-10-16 14:00:00',
	];
	return Scheduler::is_schedule_active( $settings );
} );

it( 'Evaluates scheduled maintenance as inactive before start time', function() {
	$settings = [
		'schedule_enabled' => true,
		'schedule_start'   => '2026-10-16 15:00:00',
		'schedule_end'     => '2026-10-16 18:00:00',
	];
	return ! Scheduler::is_schedule_active( $settings );
} );

it( 'Evaluates scheduled maintenance as inactive after end time', function() {
	$settings = [
		'schedule_enabled' => true,
		'schedule_start'   => '2026-10-16 08:00:00',
		'schedule_end'     => '2026-10-16 11:00:00',
	];
	return ! Scheduler::is_schedule_active( $settings );
} );

it( 'Safely handles reversed start and end dates without locking out', function() {
	$settings = [
		'schedule_enabled' => true,
		'schedule_start'   => '2026-10-16 14:00:00',
		'schedule_end'     => '2026-10-16 10:00:00',
	];
	// Should auto-swap start/end and evaluate correctly (12:00 is between 10:00 and 14:00)
	return Scheduler::is_schedule_active( $settings );
} );

// 4. Access Token & Cryptographic Cookie Verification
it( 'Generates and verifies HMAC-signed bypass session cookie', function() {
	Access_Manager::set_bypass_cookie( 24 );
	return Access_Manager::verify_access_cookie();
} );

it( 'Rejects tampered access session cookie', function() {
	Access_Manager::set_bypass_cookie( 24 );
	// Tamper with the signature
	$_COOKIE[ Access_Manager::COOKIE_NAME ] = $_COOKIE[ Access_Manager::COOKIE_NAME ] . '_tampered';
	return ! Access_Manager::verify_access_cookie();
} );

// 5. Template Registry Verification
it( 'Registers all 6 original templates cleanly', function() {
	$templates = Template_Engine::get_templates();
	$expected = [ 'minimal', 'corporate', 'dark-tech', 'editorial', 'split', 'calm-gradient' ];
	foreach ( $expected as $tpl_id ) {
		if ( ! isset( $templates[ $tpl_id ] ) ) {
			return false;
		}
		if ( ! file_exists( SHINJIRU_MAINTENANCE_PATH . 'templates/' . $tpl_id . '.php' ) ) {
			return false;
		}
	}
	return count( $templates ) === 6;
} );

echo PHP_EOL . "Summary: {$passed} passed, {$failed} failed." . PHP_EOL;

if ( $failed > 0 ) {
	exit( 1 );
}
exit( 0 );
