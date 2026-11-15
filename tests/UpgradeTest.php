<?php
/**
 * Upgrade helpers test.
 *
 * @package Traditores_In_One_Year
 */

use PHPUnit\Framework\TestCase;

/**
 * Upgrade test case.
 */
class UpgradeTest extends TestCase {

	/**
	 * Clears the in-memory options and filters.
	 */
	protected function setUp(): void {
		parent::setUp();
		$GLOBALS['trad_ioy_test_options'] = array();
		$GLOBALS['trad_ioy_test_filters'] = array();
	}

	public function test_default_bible_versions_include_hcsb_nrsv() {
		$versions = trad_ioy_default_bible_versions();
		$this->assertArrayHasKey( 'hcsb', $versions );
		$this->assertArrayHasKey( 'nrsv', $versions );
		$this->assertArrayNotHasKey( 'hscb', $versions );
		$this->assertArrayNotHasKey( 'nsrv', $versions );
	}

	public function test_non_array_bible_version_filter_is_ignored() {
		add_filter(
			'trad_ioy_bible_versions',
			static function () {
				return 'not-a-list';
			}
		);

		$versions = trad_ioy_default_bible_versions();
		$this->assertIsArray( $versions );
		$this->assertArrayHasKey( 'esv', $versions );
		$this->assertArrayHasKey( 'hcsb', $versions );
	}

	public function test_filter_drives_public_list_and_keeps_custom_codes() {
		add_filter(
			'trad_ioy_bible_versions',
			static function ( $versions ) {
				$versions['esv'] = 'ESV (filtered)';
				$versions['csb'] = 'Christian Standard (CSB)';
				unset( $versions['message'] );
				return $versions;
			}
		);

		$public = trad_ioy_merge_bible_versions(
			array(
				'esv'     => 'ESV (stored)',
				'message' => 'The Message',
				'hscb'    => 'Legacy HCSB',
				'custom'  => 'My Translation',
			)
		);

		$this->assertSame( 'ESV (filtered)', $public['esv'] );
		$this->assertArrayHasKey( 'csb', $public );
		$this->assertArrayHasKey( 'custom', $public );
		$this->assertArrayHasKey( 'hcsb', $public );
		$this->assertArrayNotHasKey( 'message', $public );
		$this->assertArrayNotHasKey( 'hscb', $public );
	}

	public function test_maybe_upgrade_maps_legacy_bible_codes() {
		$GLOBALS['trad_ioy_test_options'] = array(
			'trad_ioy_version'              => '0.3',
			'trad_ioy_gen_settings'         => array(
				'bible_version'     => 'hscb',
				'scroll'            => false,
				'cookie'            => false,
				'delete_settings'   => true,
				'load_trad_ioy_css' => true,
				'local_css_file'    => 'https://example.com/custom.css',
				'use_min_js'        => true,
				'load_jquery_js'    => true,
				'load_cookie_js'    => true,
				'load_scrollto_js'  => true,
			),
			'trad_ioy_avail_bible_versions' => array(
				'esv'  => 'English Standard (ESV)',
				'hscb' => 'Holman',
				'nsrv' => 'New Revised',
				'kjv'  => 'King James (KJV)',
			),
			'trad_ioy_local_css'            => '.x { content: "a\\b"; }',
		);

		trad_ioy_maybe_upgrade();

		$gen = get_option( 'trad_ioy_gen_settings' );
		$this->assertSame( 'hcsb', $gen['bible_version'] );
		$this->assertFalse( $gen['scroll'] );
		$this->assertFalse( $gen['cookie'] );
		$this->assertTrue( $gen['delete_settings'] );
		$this->assertTrue( $gen['copy_row'] );
		$this->assertSame( 'https://example.com/custom.css', $gen['local_css_file'] );
		$this->assertArrayNotHasKey( 'use_min_js', $gen );
		$this->assertArrayNotHasKey( 'load_jquery_js', $gen );
		$this->assertArrayNotHasKey( 'load_cookie_js', $gen );
		$this->assertArrayNotHasKey( 'load_scrollto_js', $gen );

		$versions = get_option( 'trad_ioy_avail_bible_versions' );
		$this->assertArrayHasKey( 'hcsb', $versions );
		$this->assertArrayHasKey( 'nrsv', $versions );
		$this->assertArrayHasKey( 'kjv', $versions );
		$this->assertArrayNotHasKey( 'hscb', $versions );
		$this->assertArrayNotHasKey( 'nsrv', $versions );
		$this->assertSame( '1.0.0', get_option( 'trad_ioy_version' ) );
		$this->assertSame( '.x { content: "a\\b"; }', get_option( 'trad_ioy_local_css' ) );

		$snapshot = $GLOBALS['trad_ioy_test_options'];
		trad_ioy_maybe_upgrade();
		$this->assertSame( $snapshot, $GLOBALS['trad_ioy_test_options'] );
	}

	public function test_maybe_upgrade_maps_nsrv_setting() {
		$GLOBALS['trad_ioy_test_options'] = array(
			'trad_ioy_version'      => '0.3',
			'trad_ioy_gen_settings' => array(
				'bible_version' => 'nsrv',
			),
		);

		trad_ioy_maybe_upgrade();

		$gen = get_option( 'trad_ioy_gen_settings' );
		$this->assertSame( 'nrsv', $gen['bible_version'] );
		$this->assertSame( '1.0.0', get_option( 'trad_ioy_version' ) );
	}
}
