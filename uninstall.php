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
 * Deletes plugin data for the current site when that site opted in.
 */
function trad_ioy_uninstall_current_site() {
	$gen = get_option( 'trad_ioy_gen_settings', array() );
	if ( ! is_array( $gen ) ) {
		return;
	}

	if ( empty( $gen['delete_settings'] ) ) {
		return;
	}

	foreach ( array_keys( trad_ioy_get_default_settings() ) as $key ) {
		delete_option( $key );
	}

	delete_option( 'trad_ioy_version' );
	delete_post_meta_by_key( 'trad_ioy_activate_shortcodes' );
}

/**
 * Deletes plugin data on every site that opted in.
 *
 * Network deletion includes this file once, so a multisite uninstall has to
 * walk the sites itself. Each site keeps its own delete_settings flag.
 */
function trad_ioy_uninstall() {
	if ( is_multisite() && function_exists( 'get_sites' ) && function_exists( 'switch_to_blog' ) ) {
		$offset = 0;
		$number = 100;
		do {
			$site_ids = get_sites(
				array(
					'fields' => 'ids',
					'number' => $number,
					'offset' => $offset,
				)
			);
			if ( ! is_array( $site_ids ) || array() === $site_ids ) {
				break;
			}
			foreach ( $site_ids as $site_id ) {
				switch_to_blog( (int) $site_id );
				trad_ioy_uninstall_current_site();
				restore_current_blog();
			}
			$count   = count( $site_ids );
			$offset += $number;
		} while ( $number === $count );
		return;
	}

	trad_ioy_uninstall_current_site();
}

trad_ioy_uninstall();
