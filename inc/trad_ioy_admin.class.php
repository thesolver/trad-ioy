<?php
/**
 * Created by JetBrains PhpStorm.
 * User: alan
 * Date: 12/20/12
 * Time: 5:50 PM
 * To change this template use File | Settings | File Templates.
 *
 */

class trad_ioy_admin extends trad_ioy {
	function __construct( $def_settings ) {
		parent::__construct( $def_settings );
		add_action( 'admin_menu', array( $this, 'tradmedia_admin_settings_menu' ) );
		add_action( 'admin_init', array( $this, 'trad_ioy_admin_init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'trad_ioy_load_admin_scripts' ) );
		add_action( 'add_meta_boxes', array( $this, 'trad_ioy_register_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'trad_ioy_save_meta_box_data' ) );
	}

	protected $options_page = false;

	public function tradmedia_admin_settings_menu() {
		$this->options_page = add_options_page( 'Traditores In-One-Year (Version ' . get_option( 'trad_ioy_version' ) . ') Settings',
			'Traditores IOY', 'manage_options', 'trad-ioy',
			array( $this, 'tradmedia_admin_settings_menu_body' ) );

		if ( $this->options_page ) {
			add_action( 'load-' . $this->options_page, array( $this, 'trad_ioy_help_tabs' ) );
		}
	}

	public function trad_ioy_help_tabs() {
		$screen = get_current_screen();
		$screen->add_help_tab( array(
			'id'       => 'trad-ioy-help-instructions',
			'title'    => 'Instructions',
			'callback' => array( $this, 'trad_ioy_help_instructions' )
		) );
		$screen->add_help_tab( array(
			'id'       => 'trad-ioy-help-shortcodes',
			'title'    => 'Shortcodes',
			'callback' => array( $this, 'trad_ioy_help_shortcodes' )
		) );
		$screen->add_help_tab( array(
			'id'       => 'trad-ioy-help-options',
			'title'    => 'Options',
			'callback' => array( $this, 'trad_ioy_help_options' )
		) );

		add_meta_box( 'trad_ioy_primary_settings', 'Primary Settings', array( $this, 'trad_ioy_primary_meta_box' ),
			$this->options_page, 'normal', 'core' );

		add_meta_box( 'trad_ioy_custom_css_settings', 'Custom CSS Settings', array( $this, 'trad_ioy_custom_css_meta_box' ),
			$this->options_page, 'normal', 'core' );

		add_meta_box( 'trad_ioy_js_css_settings', 'JavaScript and CSS Settings', array( $this, 'trad_ioy_js_css_meta_box' ),
			$this->options_page, 'normal', 'core' );

		add_meta_box( 'trad_ioy_caution_settings', '&quot;Be Especially Cautious&quot; Settings', array( $this, 'trad_ioy_caution_meta_box' ),
			$this->options_page, 'normal', 'core' );
	}

	public function trad_ioy_load_admin_scripts() {
		$screen = get_current_screen();
		if ( $screen->id == $this->options_page ) {
			wp_enqueue_script( 'common' );
			wp_enqueue_script( 'wp-lists' );
			wp_enqueue_script( 'postbox' );
		}
	}

	private function trad_ioy_load_help_file( $filename ) {
		include( plugin_dir_path( __FILE__ ) . '../help/' . $filename );
	}

	public function trad_ioy_help_instructions() {
		$this->trad_ioy_load_help_file( 'trad_ioy_help_instructions.html' );
	}

	public function trad_ioy_help_options() {
		$this->trad_ioy_load_help_file( 'trad_ioy_help_options.html' );
	}

	public function trad_ioy_help_shortcodes() {
		$this->trad_ioy_load_help_file( 'trad_ioy_help_shortcodes.html' );
	}

	public function trad_ioy_primary_meta_box() {
		$my_version = $this->get_setting( 'trad_ioy_bible_version' );
		?>
		<table class="form-table">
			<tr>
				<th scope="row">Bible Version</th>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><span>Bible Version</span></legend>
						<label for="trad_ioy_bible_version">Default Bible version?
							<select id="trad_ioy_bible_version" name="trad_ioy_bible_version">
								<?php foreach ( $this->def_settings['trad_ioy_avail_bible_versions'] as $key => $version ) { ?>
									<option value="<?php echo $key ?>" <?php selected( $key == $my_version ) ?>>
										<?php echo htmlentities( $version ) ?>
									</option>
								<?php } ?>
							</select></label><br />
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row">Page Behavior</th>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><span>Page Behavior</span></legend>
						<label for="trad_scroll"><input type="checkbox" id="trad_scroll" name="trad_scroll" <?php checked( $this->get_setting( 'trad_scroll' ) ) ?> /> Scroll to today&#39;s entry? (Can be overriden by shortcode.)</label><br />
						<label for="trad_copy_row"><input type="checkbox" id="trad_copy_row" name="trad_copy_row" <?php checked( $this->get_setting( 'trad_copy_row' ) ) ?> /> Copy current day&#39;s row to top of table?</label><br />
						<label for="trad_cookie"><input type="checkbox" id="trad_cookie" name="trad_cookie" <?php checked( $this->get_setting( 'trad_cookie' ) ) ?> /> Keep user&#39;s Bible preference in a cookie?</label><br />
					</fieldset>
				</td>
			</tr>
		</table>
		<input class="button-primary" type="submit" name="save" value="Save All Options" id="trad_submit_primary" />
	<?php
	}

	public function trad_ioy_js_css_meta_box() {
		?>
		<table class="form-table">
			<tr>
				<th scope="row">CSS and JavasScript</th>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><span>CSS and JavasScript</span></legend>
						<label for="trad_use_min_js"><input type="checkbox" id="trad_use_min_js" name="trad_use_min_js" <?php checked( $this->get_setting( 'trad_use_min_js' ) ) ?> /> Use minimized css and js files where possible?</label><br />
						<label for="trad_load_trad_ioy_css"><input type="checkbox" id="trad_load_trad_ioy_css" name="trad_load_trad_ioy_css" <?php checked( $this->get_setting( 'trad_load_trad_ioy_css' ) ) ?> /> Load Traditores In-One-Year css?</label><br />
						<label for="trad_load_cookie_js"><input type="checkbox" id="trad_load_cookie_js" name="trad_load_cookie_js" <?php checked( $this->get_setting( 'trad_load_cookie_js' ) ) ?> /> Load jQuery cookie extension?</label><br />
						<label for="trad_load_scrollto_js"><input type="checkbox" id="trad_load_scrollto_js" name="trad_load_scrollto_js" <?php checked( $this->get_setting( 'trad_load_scrollto_js' ) ) ?> /> Load jQuery scrollTo extension?</label>
					</fieldset>
				</td>
			</tr>
		</table>
		<input class="button-primary" type="submit" name="save" value="Save All Options" id="trad_submit_js_css" />
	<?php
	}

	public function trad_ioy_custom_css_meta_box() {
		?>
		<table class="form-table">
			<tr>
				<th scope="row">Header &lt;Style&gt;</th>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><span>Header &lt;tyle&gt;</span></legend>
						<label for="trad_ioy_local_css"><textarea id="trad_ioy_local_css" name="trad_ioy_local_css" rows="5" placeholder="Enter your custom css without the style tags here..."><?php echo esc_textarea( $this->get_setting( 'trad_ioy_local_css' ) ) ?></textarea>
						</label>

						<p class="description">
							<strong>WARNING:</strong> Whatever is entered here is sent in the &lt;head&gt; of the page&#39;s HTML without any changes (other than removing html tags and trimming whitespace off the front and back) &mdash; so be sure it is correct stylesheet mark-up.
						</p>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row">CSS File</th>
				<td>
					<fieldset>
						<label for="trad_ioy_local_css_file"><input type="text" id="trad_ioy_local_css_file" name="trad_ioy_local_css_file" value="<?php echo esc_html( $this->get_setting( 'trad_ioy_local_css_file' ) ) ?>" /></label>

						<p class="description">This needs to be a complete URL or a path that will resolve regardless of where it is called (eg. &quot;http://dir/dir/file.css&quot; or &quot;/dir/dir/file.css&quot;).
						</p>

						<p class="description">If you want
							<strong>only</strong> to use your custom CSS, then be sure to uncheck &quot;Load Pine Knoll Publications Media css?&quot; in JavaScript and CSS Settings. Custom CSS is loaded as late as possible to allow it to have the &quot;last word&quot; on formatting.
						</p>
					</fieldset>
				</td>
			</tr>
		</table>
		<input class="button-primary" type="submit" name="save" value="Save All Options" id="trad_submit_submit_custom_css" />
	<?php
	}

	public function trad_ioy_caution_meta_box() {
		?>
		<table class="form-table">
			<tr>
				<th scope="row">Delete Settings</th>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><span>Delete Settings</span></legend>
						<label for="trad_delete_settings"><strong><input type="checkbox" id="trad_delete_settings" name="trad_delete_settings" <?php checked( $this->get_setting( 'trad_delete_settings' ) ) ?> /> Delete settings on uninstall?</strong>
						</label>

						<p class="description">Please note, even if you have this unchecked, Wordpress will say it is going to delete data (but it won't).</p>
					</fieldset>
				</td>
			</tr>
		</table>
		<input class="button-primary" type="submit" name="save" value="Save All Options" id="trad_submit_cautious" />
	<?php
	}

	public function tradmedia_admin_settings_menu_body() {
		?>
		<div id="icon-options-general" class="icon32"><br /></div>
		<h2>Pine Knoll Publications Media (Version <?php echo $this->get_setting( 'trad_ioy_version' ) ?>) Settings</h2>
		<?php if ( isset( $_GET['trad-ioy-message'] ) && '1' == $_GET['trad-ioy-message'] ) { ?>
			<div id='trad-ioy-message' class='updated fade'><p><strong>Settings Saved</strong></p></div>
		<?php } ?>
		<div id="trad-ioy-settings-div">
			<form id="trad-ioy-settings-form" action="admin-post.php" method="post" onsubmit="jQuery('#trad-ioy-message').hide();">
				<input type="hidden" name="action" value="trad_save_settings" />
				<?php wp_nonce_field( 'tradioy', '_nonce_wp_tradioy' ) ?>
				<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
				<?php wp_nonce_field( 'meta-box-order', 'mata-box-order-nonce', false ); ?>
				<div id="trad_ioy_meta_body" class="metabox-holder">
					<div id="trad_ioy_meta_body_content">
						<?php do_meta_boxes( $this->options_page, 'normal', '' ); ?>
					</div>
				</div>

			</form>
		</div>

		<script type="text/javascript">
			//<! [CDATA[
			jQuery(document).ready(function ($) {
				// close postboxes that should be closed
				$('if-js-closed').removeClass('if-js-closed').addClass('closed');
				// set togglse on postboxes
				postboxes.add_postbox_toggles('<?php echo $this->options_page ?>');

			});
			//]]>
		</script>


	<?php
	}


	public function trad_ioy_admin_init() {
		global $trad_ioy_def_settings;
		if ( ( $option = get_option( 'trad_ioy_version' ) ) === false ) {
			add_option( 'trad_ioy_version', $trad_ioy_def_settings['trad_ioy_version'], '', false );
		} else {
			update_option( 'trad_ioy_version', $trad_ioy_def_settings['trad_ioy_version'] );
		}
		add_action( 'admin_post_trad_save_settings', array( $this, 'admin_post_save_options' ) );
		wp_enqueue_style( 'trad-ioy-admin', plugins_url( 'css/wp-admin.css', dirname( __FILE__ ) ) );
	}

	public function admin_post_save_options() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Not allowed!' );
		}

		check_admin_referer( 'tradioy', '_nonce_wp_tradioy' );

		$options = array();

		foreach ( $this->setting_loc as $key => $value ) {
			$type = $this->setting_type[$key];
			// there are some settings we don't allow to be set via the admin page
			if ( false === $type ) {
				continue; // foreach $this->setting_loc
			}

			$loc = $this->setting_loc[$key];
			if ( is_array( $loc ) ) {
				list( $pri, $sec ) = $loc;
				// need to make sure we have an array for any two-level options
				if ( ! isset( $options[$pri] ) ) {
					$options[$pri] = array();
				}
			} else {
				$pri = $loc;
				$sec = false;
			}

			if ( 'b' == $type ) {
				$opt_value = isset( $_POST[$key] ) ? true : false;
			} elseif ( isset( $_POST[$key] ) ) {
				if ( 's' == $type ) {
					$opt_value = wp_kses( trim( $_POST[$key] ), array(), array() );
					// don't let someone try to set an unknown Bible version...
					if ( 'key' == 'trad-ioy-bible-version' ) {
						if ( ! isset( $this->def_settings['trad_ioy_avail_bible_versions']['$opt_value'] ) ) {
							continue;
						}

					}
				} elseif ( 'u' == $type ) {
					$opt_value = esc_url_raw( trim( $_POST[$key] ), array( 'http', 'https' ) );
				} else {
					$opt_value = trim( $_POST[$key] );
				}
			} else {
				// This protects us from mistakenly deleting a setting because of a bad post...
				$opt_value = $this->get_setting( $key );
				// nothin' to see here, move along...
				continue; // foreach $this->setting_loc
			}

			if ( false === $sec ) {
				$options[$pri] = $opt_value;
			} else {
				$options[$pri][$sec] = $opt_value;
			}

		} // foreach $this->setting_loc

		foreach ( $options as $key => $value ) {
			update_option( $key, $value );
		}

		wp_redirect( add_query_arg( array( 'page'             => 'trad-ioy',
																			 'trad-ioy-message' => '1' ),
			admin_url( 'options-general.php' ) ) );
	}

	public function trad_ioy_register_meta_boxes() {
		$post_types = get_post_types( array(), 'objects' );
		foreach ( $post_types as $post_type ) {
			add_meta_box( 'trad_ioy_activate_shortcodes', 'Traditores IOY', array( $this, 'trad_ioy_shortcode_meta_box' ),
				$post_type->name, 'side' );
		}
	}

	public function trad_ioy_shortcode_meta_box( $post ) {
		?>
		<label for="trad_ioy_activate_shortcodes"><input type="checkbox" id="trad_ioy_activate_shortcodes" name="trad_ioy_activate_shortcodes" <?php
			checked( get_post_meta( $post->ID, 'trad_ioy_activate_shortcodes', true ) )
			?> /> Activate shorcodes? </label>
	<?php
	}

	public function trad_ioy_save_meta_box_data( $post_id = false, $post = false ) {
		if ( ! empty( $_POST['trad_ioy_activate_shortcodes'] ) ) {
			update_post_meta( $post_id, 'trad_ioy_activate_shortcodes',
				( $_POST['trad_ioy_activate_shortcodes'] ) ? true : false );
		} else {
			// This means we weren't turned on...so we are not on...
			update_post_meta( $post_id, 'trad_ioy_activate_shortcodes', false );
		}
	}

	// used to save all our default options at plugin activation (can be safely used during plugin update too)
	public function save_default_options( $autoload = false ) {
		$autoload = $autoload ? 'yes' : 'no';
		// go through all our possible options
		foreach ( $this->def_settings as $key => $value ) {
			// if it doesn't exist in a setting...add it...
			if ( ( $option = get_option( $key ) ) === false ) {
				add_option( $key, $value, '', $autoload );
			} else {
				// if the option is an array, then we need to make sure it has settings for all the sub settings...
				if ( is_array( $option ) ) {
					$need_to_update = false;
					foreach ( $this->def_settings[$key] as $option_key => $option_value ) {
						if ( ! isset( $option[$option_key] ) ) {
							$option[$option_key] = $option_value;
							$need_to_update      = true;
						}
					}
					if ( $need_to_update ) {
						update_option( $key, $option );
					}
				}
			}
		}
	}
}

?>