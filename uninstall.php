<?php
/**
 * Uninstall handler.
 *
 * @package Traditores_In_One_Year
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

require_once plugin_dir_path( __FILE__ ) . 'inc/defaults.php';

if ( ! defined( 'TRAD_IOY_VERSION' ) ) {
	define( 'TRAD_IOY_VERSION', '1.0.0' );
}

/**
 * Deletes plugin data when the user opted in.
 */
function trad_ioy_uninstall() {
	$gen = get_option( 'trad_ioy_gen_settings', array() );
	if ( ! is_array( $gen ) ) {
		return;
	}

	$delete = ! empty( $gen['delete_settings'] );
	if ( ! $delete ) {
		return;
	}

	foreach ( array_keys( trad_ioy_get_default_settings() ) as $key ) {
		delete_option( $key );
	}

	delete_option( 'trad_ioy_version' );
	delete_post_meta_by_key( 'trad_ioy_activate_shortcodes' );
}

trad_ioy_uninstall();
