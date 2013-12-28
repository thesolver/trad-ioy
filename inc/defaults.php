<?php
/**
 * Created by JetBrains PhpStorm.
 * User: alan
 * Date: 12/19/12
 * Time: 12:51 PM
 * To change this template use File | Settings | File Templates.
 */

$trad_ioy_def_settings = array(
	'trad_ioy_gen_settings'         => array(
		'use_min_js'        => true,
		'load_cookie_js'    => true,
		'load_scrollto_js'  => true,
		'load_trad_ioy_css' => true,
		'delete_settings'   => false,
		'local_css_file'    => '',
		'bible_version'     => 'esv',
		'scroll'            => true,
		'copy_row'          => true,
		'cookie'            => true
	),
	'trad_ioy_local_css'            => '',
	'trad_ioy_version'              => '0.3',
	'trad_ioy_avail_bible_versions' => array( 'esv'     => 'English Standard (ESV)',
																						'niv'     => 'New International (NIV)', 'kjv' => 'King James (KJV)',
																						'nasb95'  => 'New American Standard (NASB)', 'lbla95' => 'La Biblia de las Americas',
																						'hscb'    => 'Holman Christian Standard (HCSB)', 'nsrv' => 'New Revised Standard (NRSV)',
																						'gnb'     => 'Good News Bible', 'nlt' => 'New Living Translation (NLT)',
																						'message' => 'The Message' )
);

$trad_ioy_post_def_settings = array(
	'trad_ioy_activate_shortcodes' => false
);

// better than trying to keep them manually in order...
asort( $trad_ioy_def_settings['trad_ioy_avail_bible_versions'] );
?>