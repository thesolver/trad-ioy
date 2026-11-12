<?php
/**
 * Default option values for Traditores In-One-Year.
 *
 * @package Traditores_In_One_Year
 */

defined( 'ABSPATH' ) || exit;

/**
 * Filterable list of Biblia.com Bible version codes => labels.
 *
 * @return array<string, string>
 */
function trad_ioy_default_bible_versions() {
	$versions = array(
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

	/**
	 * Filters the Bible versions available in the reading plan UI.
	 *
	 * @param array<string, string> $versions Version code => label.
	 */
	$versions = apply_filters( 'trad_ioy_bible_versions', $versions );
	asort( $versions );
	return $versions;
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
