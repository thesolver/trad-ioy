<?php
/**
 * Privacy policy suggested text.
 *
 * @package Traditores_In_One_Year
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers privacy policy content for site owners.
 */
function trad_ioy_register_privacy_policy() {
	if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
		return;
	}

	$content = sprintf(
		/* translators: 1: cookie name */
		__( 'Traditores In-One-Year may store the visitor’s selected Bible translation in a browser cookie named %1$s for up to one year when that option is enabled. The plugin does not send personal data to Traditores servers.', 'traditores-in-one-year' ),
		'<code>trad_ioy_bible_version</code>'
	);

	$content .= ' ' . __(
		'Reading links point to Biblia.com (Faithlife). See Faithlife terms and privacy policy at faithlife.com/terms and logos.com/privacy.',
		'traditores-in-one-year'
	);

	wp_add_privacy_policy_content(
		__( 'Traditores In-One-Year', 'traditores-in-one-year' ),
		wp_kses_post( $content )
	);
}

add_action( 'admin_init', 'trad_ioy_register_privacy_policy' );
