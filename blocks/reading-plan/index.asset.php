<?php
/**
 * Dependencies for the reading-plan block editor script.
 *
 * WordPress reads this when block.json points editorScript at file:./index.js.
 * wp-i18n in the list is what makes script translations load.
 *
 * @package Traditores_In_One_Year
 */

defined( 'ABSPATH' ) || exit;

return array(
	'dependencies' => array(
		'wp-blocks',
		'wp-element',
		'wp-block-editor',
		'wp-components',
		'wp-i18n',
	),
	'version'      => defined( 'TRAD_IOY_VERSION' ) ? TRAD_IOY_VERSION : '1.0.0',
);
