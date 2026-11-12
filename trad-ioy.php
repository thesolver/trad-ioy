<?php
/**
 * Plugin Name:       Traditores In-One-Year
 * Plugin URI:        https://github.com/thesolverus/trad-ioy
 * Description:       Insert a Bible reading plan into posts or pages with optional scroll-to-today and Bible version selection.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      8.0
 * Author:            Alan Fahrner
 * Author URI:        https://traditores.org
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       traditores-in-one-year
 *
 * @package Traditores_In_One_Year
 */

defined( 'ABSPATH' ) || exit;

define( 'TRAD_IOY_VERSION', '1.0.0' );
define( 'TRAD_IOY_PLUGIN_FILE', __FILE__ );
define( 'TRAD_IOY_PATH', plugin_dir_path( __FILE__ ) );

require_once TRAD_IOY_PATH . 'inc/defaults.php';
require_once TRAD_IOY_PATH . 'inc/upgrade.php';
require_once TRAD_IOY_PATH . 'inc/class-trad-ioy.php';
require_once TRAD_IOY_PATH . 'inc/privacy.php';

if ( is_admin() ) {
	require_once TRAD_IOY_PATH . 'inc/class-trad-ioy-admin.php';
}

/**
 * Bootstrap plugin.
 */
function trad_ioy_bootstrap() {
	$def_settings = trad_ioy_get_default_settings();

	trad_ioy_maybe_upgrade();

	if ( is_admin() ) {
		new Trad_Ioy_Admin( $def_settings );
	} else {
		new Trad_Ioy( $def_settings );
	}
}
add_action( 'plugins_loaded', 'trad_ioy_bootstrap', 5 );

register_activation_hook( __FILE__, 'trad_ioy_on_activate' );

/**
 * Activation handler (wp-admin and WP-CLI).
 */
function trad_ioy_on_activate() {
	trad_ioy_install_defaults();
	trad_ioy_maybe_upgrade();
}
