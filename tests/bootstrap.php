<?php
/**
 * Test bootstrap (minimal stubs).
 *
 * @package Traditores_In_One_Year
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/tmp/wordpress/' );
}

if ( ! function_exists( 'apply_filters' ) ) {
	/**
	 * @param mixed $value Value.
	 * @return mixed
	 */
	function apply_filters( $hook, $value ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		return $value;
	}
}

define( 'TRAD_IOY_VERSION', '1.0.0' );
define( 'TRAD_IOY_PLUGIN_FILE', dirname( __DIR__ ) . '/trad-ioy.php' );
define( 'TRAD_IOY_PATH', dirname( __DIR__ ) . '/' );

require_once dirname( __DIR__ ) . '/inc/defaults.php';
require_once dirname( __DIR__ ) . '/inc/upgrade.php';
