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

	public function test_default_bible_versions_include_hcsb_nrsv() {
		$versions = trad_ioy_default_bible_versions();
		$this->assertArrayHasKey( 'hcsb', $versions );
		$this->assertArrayHasKey( 'nrsv', $versions );
		$this->assertArrayNotHasKey( 'hscb', $versions );
		$this->assertArrayNotHasKey( 'nsrv', $versions );
	}
}
