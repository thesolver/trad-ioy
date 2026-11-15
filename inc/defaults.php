<?php
/**
 * Default option values for Traditores In-One-Year.
 *
 * @package Traditores_In_One_Year
 */

defined( 'ABSPATH' ) || exit;

/**
 * Legacy Biblia codes stored by v0.3, mapped to the codes Biblia uses.
 *
 * @return array<string, string>
 */
function trad_ioy_legacy_bible_version_map() {
	return array(
		'hscb' => 'hcsb',
		'nsrv' => 'nrsv',
	);
}

/**
 * Lowercases a version code and applies the v0.3 spelling map.
 *
 * @param mixed $code Raw version code.
 * @return string
 */
function trad_ioy_normalize_bible_version_code( $code ) {
	$code = strtolower( (string) $code );
	$map  = trad_ioy_legacy_bible_version_map();
	if ( isset( $map[ $code ] ) ) {
		return $map[ $code ];
	}
	return $code;
}

/**
 * Built-in Biblia version codes, before the trad_ioy_bible_versions filter.
 *
 * @return array<string, string>
 */
function trad_ioy_builtin_bible_versions() {
	return array(
		'esv'     => 'English Standard (ESV)',
		'niv'     => 'New International (NIV)',
		'kjv'     => 'King James (KJV)',
		'nasb95'  => 'New American Standard (NASB)',
		'lbla95'  => 'La Biblia de las Americas',
		'hcsb'    => 'Holman Christian Standard (HCSB)',
		'nrsv'    => 'New Revised Standard (NRSV)',
		'gnb'     => 'Good News Bible',
		'nlt'     => 'New Living Translation (NLT)',
		'message' => 'The Message',
	);
}

/**
 * Keeps string code => label pairs and maps legacy codes.
 *
 * @param mixed $versions Raw list.
 * @return array<string, string>
 */
function trad_ioy_sanitize_bible_version_list( $versions ) {
	if ( ! is_array( $versions ) ) {
		return array();
	}

	$clean = array();
	foreach ( $versions as $code => $label ) {
		if ( is_int( $code ) ) {
			$code = (string) $code;
		}
		if ( ! is_string( $code ) ) {
			continue;
		}
		$code = trad_ioy_normalize_bible_version_code( $code );
		if ( ! preg_match( '/^[a-z0-9_-]+$/', $code ) ) {
			continue;
		}
		if ( is_array( $label ) || is_object( $label ) ) {
			continue;
		}
		$clean[ $code ] = (string) $label;
	}

	return $clean;
}

/**
 * Filterable list of Biblia.com Bible version codes => labels.
 *
 * A non-array filter return is ignored so a bad callback cannot take down the request.
 *
 * @return array<string, string>
 */
function trad_ioy_default_bible_versions() {
	$versions = trad_ioy_builtin_bible_versions();

	/**
	 * Filters the Bible versions available in the reading plan UI.
	 *
	 * Return the full list to show. Add, rename, or remove codes here.
	 * The front end reads this on each request, not a copy saved at upgrade time.
	 *
	 * @param array<string, string> $versions Version code => label.
	 */
	$filtered = apply_filters( 'trad_ioy_bible_versions', $versions );
	if ( ! is_array( $filtered ) ) {
		$filtered = $versions;
	}

	$clean = trad_ioy_sanitize_bible_version_list( $filtered );
	if ( array() === $clean ) {
		$clean = trad_ioy_sanitize_bible_version_list( $versions );
	}

	asort( $clean );
	return $clean;
}

/**
 * Bible versions for the public select and the script allow-list.
 *
 * The filtered list is the list visitors see, so removals and renames apply.
 * Codes stored in the option that are not part of the built-in set are kept,
 * which preserves a database-only translation without letting the saved copy
 * of the built-in list undo the filter.
 *
 * @param mixed $stored Stored trad_ioy_avail_bible_versions value.
 * @return array<string, string>
 */
function trad_ioy_merge_bible_versions( $stored ) {
	$builtin  = trad_ioy_builtin_bible_versions();
	$filtered = trad_ioy_default_bible_versions();
	$extra    = trad_ioy_sanitize_bible_version_list( $stored );

	foreach ( $extra as $code => $label ) {
		if ( isset( $filtered[ $code ] ) || isset( $builtin[ $code ] ) ) {
			continue;
		}
		$filtered[ $code ] = $label;
	}

	asort( $filtered );
	return $filtered;
}

/**
 * Default plugin options.
 *
 * @return array<string, mixed>
 */
function trad_ioy_get_default_settings() {
	return array(
		'trad_ioy_gen_settings'         => array(
			'load_trad_ioy_css' => true,
			'delete_settings'   => false,
			'local_css_file'    => '',
			'bible_version'     => 'esv',
			'scroll'            => true,
			'copy_row'          => true,
			'cookie'            => true,
		),
		'trad_ioy_local_css'            => '',
		'trad_ioy_version'              => defined( 'TRAD_IOY_VERSION' ) ? TRAD_IOY_VERSION : '1.0.0',
		'trad_ioy_avail_bible_versions' => trad_ioy_default_bible_versions(),
	);
}

/** @var array<string, mixed> $trad_ioy_def_settings */
$trad_ioy_def_settings = trad_ioy_get_default_settings();
