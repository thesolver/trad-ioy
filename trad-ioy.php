<?php
/*
Plugin Name: Traditores In-One-Year
Plugin URI:
Description: A Bible reading plan plugin for Wordpress
Author: Alan Fahrner
Version: 0.2
Author URI: http://traditores.org
License: GPLv2 or later
*/

/*
 * TODO: Warning if jQuery already being loaded?  Skip jQuery altogether?
 * TODO: Document css and js keys for load, options in the database, and so on...
 * TODO: Reorganize and document methods
 * TODO: Help files...
 * TODO: Add comments in code with hints about why certain things were done...
 */

define( 'TRADIOYREALPATH', plugin_dir_path( __FILE__ ) );

include_once( plugin_dir_path( __FILE__ ) . 'inc/defaults.php' );
include_once plugin_dir_path( __FILE__ ) . 'inc/trad_ioy.class.php';

if ( is_admin() ) {
	include_once plugin_dir_path( __FILE__ ) . 'inc/trad_ioy_admin.class.php';
	$trad_ioy = new trad_ioy_admin( $trad_ioy_def_settings );
} else {
	$trad_ioy = new trad_ioy( $trad_ioy_def_settings );
}

// Wordpress discussions say this register_activation_hook must occur in the main plugin file...
register_activation_hook( __FILE__, array( $trad_ioy, 'save_default_options' ) );

?>