<?php
/**
 * Test bootstrap (minimal stubs).
 *
 * @package Traditores_In_One_Year
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/tmp/wordpress/' );
}

$GLOBALS['trad_ioy_test_options'] = array();
$GLOBALS['trad_ioy_test_filters'] = array();

if ( ! function_exists( 'add_filter' ) ) {
	/**
	 * @param string   $hook          Hook name.
	 * @param callable $callback      Callback.
	 * @param int      $priority      Priority.
	 * @param int      $accepted_args Unused. Matches the WordPress signature.
	 * @return true
	 */
	function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		$GLOBALS['trad_ioy_test_filters'][ $hook ][ (int) $priority ][] = $callback;
		return true;
	}
}

if ( ! function_exists( 'apply_filters' ) ) {
	/**
	 * @param string $hook  Hook name.
	 * @param mixed  $value Value passed to callbacks.
	 * @return mixed
	 */
	function apply_filters( $hook, $value ) {
		if ( empty( $GLOBALS['trad_ioy_test_filters'][ $hook ] ) || ! is_array( $GLOBALS['trad_ioy_test_filters'][ $hook ] ) ) {
			return $value;
		}

		$priorities = $GLOBALS['trad_ioy_test_filters'][ $hook ];
		ksort( $priorities );
		foreach ( $priorities as $callbacks ) {
			foreach ( $callbacks as $callback ) {
				$value = call_user_func( $callback, $value );
			}
		}
		return $value;
	}
}

if ( ! function_exists( 'get_option' ) ) {
	/**
	 * @param string $option   Option name.
	 * @param mixed  $fallback Value when the option is not set.
	 * @return mixed
	 */
	function get_option( $option, $fallback = false ) {
		if ( array_key_exists( $option, $GLOBALS['trad_ioy_test_options'] ) ) {
			return $GLOBALS['trad_ioy_test_options'][ $option ];
		}
		return $fallback;
	}
}

if ( ! function_exists( 'update_option' ) ) {
	/**
	 * @param string $option   Option name.
	 * @param mixed  $value    New value.
	 * @param mixed  $autoload Unused. Matches the WordPress signature.
	 * @return true
	 */
	function update_option( $option, $value, $autoload = null ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		$GLOBALS['trad_ioy_test_options'][ $option ] = $value;
		return true;
	}
}

if ( ! function_exists( 'add_option' ) ) {
	/**
	 * @param string $option     Option name.
	 * @param mixed  $value      Value.
	 * @param string $deprecated Unused. Matches the WordPress signature.
	 * @param mixed  $autoload   Unused. Matches the WordPress signature.
	 * @return bool
	 */
	function add_option( $option, $value, $deprecated = '', $autoload = 'yes' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		if ( array_key_exists( $option, $GLOBALS['trad_ioy_test_options'] ) ) {
			return false;
		}
		$GLOBALS['trad_ioy_test_options'][ $option ] = $value;
		return true;
	}
}

if ( ! function_exists( 'wp_parse_args' ) ) {
	/**
	 * @param array|object|string $args     Values that win.
	 * @param array               $defaults Fallback values.
	 * @return array
	 */
	function wp_parse_args( $args, $defaults = array() ) {
		if ( is_object( $args ) ) {
			$args = get_object_vars( $args );
		} elseif ( ! is_array( $args ) ) {
			$args = array();
		}
		if ( ! is_array( $defaults ) ) {
			$defaults = array();
		}
		return array_merge( $defaults, $args );
	}
}

define( 'TRAD_IOY_VERSION', '1.0.0' );
define( 'TRAD_IOY_PLUGIN_FILE', dirname( __DIR__ ) . '/trad-ioy.php' );
define( 'TRAD_IOY_PATH', dirname( __DIR__ ) . '/' );

require_once dirname( __DIR__ ) . '/inc/defaults.php';
require_once dirname( __DIR__ ) . '/inc/upgrade.php';
