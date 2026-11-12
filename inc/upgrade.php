<?php
/**
 * Install defaults and version migrations.
 *
 * @package Traditores_In_One_Year
 */

defined( 'ABSPATH' ) || exit;

/**
 * Runs on activation and when the stored plugin version is older than TRAD_IOY_VERSION.
 */
function trad_ioy_maybe_upgrade() {
	$def_settings = trad_ioy_get_default_settings();

	$stored = get_option( 'trad_ioy_version', '0' );
	if ( version_compare( (string) $stored, TRAD_IOY_VERSION, '>=' ) ) {
		trad_ioy_repair_empty_options( $def_settings );
		return;
	}

	trad_ioy_install_defaults( $def_settings );

	$gen = get_option( 'trad_ioy_gen_settings', array() );
	if ( ! is_array( $gen ) ) {
		$gen = array();
	}

	$bible_map = array(
		'hscb' => 'hcsb',
		'nsrv' => 'nrsv',
	);
	if ( isset( $gen['bible_version'] ) && isset( $bible_map[ $gen['bible_version'] ] ) ) {
		$gen['bible_version'] = $bible_map[ $gen['bible_version'] ];
	}

	$versions = get_option( 'trad_ioy_avail_bible_versions', array() );
	if ( is_array( $versions ) ) {
		foreach ( $bible_map as $old => $new ) {
			if ( isset( $versions[ $old ] ) ) {
				if ( ! isset( $versions[ $new ] ) ) {
					$versions[ $new ] = $versions[ $old ];
				}
				unset( $versions[ $old ] );
			}
		}
		update_option( 'trad_ioy_avail_bible_versions', $versions, true );
	}

	$obsolete = array( 'use_min_js', 'load_jquery_js', 'load_cookie_js', 'load_scrollto_js' );
	foreach ( $obsolete as $key ) {
		unset( $gen[ $key ] );
	}

	$gen = wp_parse_args( $gen, $def_settings['trad_ioy_gen_settings'] );
	update_option( 'trad_ioy_gen_settings', $gen, true );
	update_option( 'trad_ioy_version', TRAD_IOY_VERSION, true );
}

/**
 * Restores options that were saved as empty arrays.
 *
 * @param array<string, mixed> $def_settings Default settings.
 */
function trad_ioy_repair_empty_options( $def_settings ) {
	foreach ( $def_settings as $key => $value ) {
		$existing = get_option( $key, false );
		if ( is_array( $value ) && ( false === $existing || ( is_array( $existing ) && array() === $existing ) ) ) {
			update_option( $key, $value, true );
			continue;
		}
		if ( 'trad_ioy_gen_settings' === $key && is_array( $value ) && is_array( $existing ) ) {
			$merged = wp_parse_args( $existing, $value );
			if ( $merged !== $existing ) {
				update_option( $key, $merged, true );
			}
		}
	}
}

/**
 * Ensures all default options exist and merges missing keys.
 *
 * @param array<string, mixed>|null $def_settings Optional defaults (for tests).
 */
function trad_ioy_install_defaults( $def_settings = null ) {
	if ( null === $def_settings ) {
		$def_settings = trad_ioy_get_default_settings();
	}

	foreach ( $def_settings as $key => $value ) {
		$existing = get_option( $key, false );
		if ( false === $existing ) {
			add_option( $key, $value, '', true );
			continue;
		}
		if ( is_array( $value ) ) {
			if ( ! is_array( $existing ) ) {
				$existing = array();
			}
			$merged = wp_parse_args( $existing, $value );
			if ( $merged !== $existing || array() === $existing ) {
				update_option( $key, $merged, true );
			}
			continue;
		}
		if ( '' === $existing || null === $existing ) {
			update_option( $key, $value, true );
		}
	}
}
