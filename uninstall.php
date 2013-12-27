<?php
/**
 * simple data deletion script with protection against nefarious and unintended deletion
 */

if (false && !defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

include_once(plugin_dir_path(__FILE__) . 'inc/defaults.php');

trad_ioy_delete_options($trad_ioy_def_settings,$trad_ioy_post_def_settings);

// used to delete our options ad plugin removal
// please note...originally I attempted to pass $trad_ioy_def_settings as a global, but inside the function
// it would never work...
function trad_ioy_delete_options ($trad_ioy_def_settings,$trad_ioy_post_def_settings) {
	$gen_set = get_option('trad_ioy_gen_settings');
	$delete_settings = $gen_set['delete_settings'];

	if (true === $delete_settings) {
		foreach (array_keys($trad_ioy_def_settings) as $key) {
			if (get_option($key) !== false) {
				delete_option($key);
			}
			foreach (array_keys($trad_ioy_post_def_settings) as $key) {
				delete_post_meta_by_key($key);
			}
		}
	}
}
?>