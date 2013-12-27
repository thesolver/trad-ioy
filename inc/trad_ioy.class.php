<?php

/*
 * TODO: Add being able to select which Bible versions are availale
 * TODO: Don't copy row if is the first one...
 * TODO: Make using the cookies optional
 * TODO: Look at dynamically loading the required css and javascript files only if a page uses
 *       our shortcode: http://wordpress.stackexchange.com/questions/2302/loading-scripts-only-if-a-particular-shortcode-or-widget-is-present
 */

class trad_ioy {
	public function __construct( $def_settings ) {
		$this->def_settings = $def_settings;
		// well, trad_ioy_now let's actually do something!
		add_shortcode( 'tradioy', array( $this, 'tra_ioy_shortcode' ) );
		$this->settings = array(
			'trad_ioy_gen_settings'         => get_option( 'trad_ioy_gen_settings' ),
			'trad_ioy_version'              => get_option( 'trad_ioy_version' ),
			'trad_ioy_local_css'            => get_option( 'trad_ioy_local_css' ),
			'trad_ioy_avail_bible_versions' => get_option( 'trad_ioy_avail_bible_versions' )
		);
		if ( ! is_admin() ) {
			add_action( 'wp_head', array( $this, 'trad_ioy_shortcodes_activated' ), 0 );
		}
		add_action( 'wp_enqueue_scripts', array( $this, 'trad_load_css_and_jss' ) );
	}

	// when adding settings, do not set the top level to true/false since get_options returns false when it
	// can't find value...meaning you can't distinguish between missing and set to false
	protected $def_settings = array();
	protected $settings = array();
	protected $setting_loc = array(
		'trad_load_jquery_js'           => array( 'trad_ioy_gen_settings', 'load_jquery_js' ),
		'trad_load_cookie_js'           => array( 'trad_ioy_gen_settings', 'load_cookie_js' ),
		'trad_load_scrollto_js'         => array( 'trad_ioy_gen_settings', 'load_scrollto_js' ),
		'trad_load_trad_ioy_css'        => array( 'trad_ioy_gen_settings', 'load_trad_ioy_css' ),
		'trad_use_min_js'               => array( 'trad_ioy_gen_settings', 'use_min_js' ),
		'trad_delete_settings'          => array( 'trad_ioy_gen_settings', 'delete_settings' ),
		'trad_scroll'                   => array( 'trad_ioy_gen_settings', 'scroll' ),
		'trad_copy_row'                 => array( 'trad_ioy_gen_settings', 'copy_row' ),
		'trad_cookie'                   => array( 'trad_ioy_gen_settings', 'cookie' ),
		'trad_ioy_bible_version'        => array( 'trad_ioy_gen_settings', 'bible_version' ),
		'trad_ioy_version'              => 'trad_ioy_version',
		'trad_ioy_local_css'            => 'trad_ioy_local_css',
		'trad_ioy_local_css_file'       => array( 'trad_ioy_gen_settings', 'local_css_file' ),
		'trad_ioy_avail_bible_versions' => 'trad_ioy_avail_bible_versions'
	);

	// b = boolean, i = integer, false means don't let an option page change, h = html/css/js, s = stylesheet code, u = url, t = text, a = array
	protected $setting_type = array(
		'trad_load_jquery_js'           => 'b',
		'trad_load_cookie_js'           => 'b',
		'trad_load_scrollto_js'         => 'b',
		'trad_load_trad_ioy_css'        => 'b',
		'trad_use_min_js'               => 'b',
		'trad_delete_settings'          => 'b',
		'trad_scroll'                   => 'b',
		'trad_copy_row'                 => 'b',
		'trad_cookie'                   => 'b',
		'trad_ioy_version'              => false,
		'trad_ioy_local_css'            => 's',
		'trad_ioy_bible_version'        => 's',
		'trad_ioy_local_css_file'       => 'u',
		'trad_ioy_avail_bible_versions' => 'a'
	);

	protected $select_count = 0;

	/*
	* settings are either arrays or a single value
	* this returns a value based on the type of the setting, including removing tags/etc. to make it safe. It does
	* _not_ html encode (that is, it removes bad stuff...it does not prepare for HTML)
	*/
	protected function get_setting( $key ) {
		$location = $this->setting_loc[$key];
		$type     = $this->setting_type[$key];
		$value    = is_array( $location ) ? $this->settings[$location[0]][$location[1]] : $this->settings[$location];
		if ( 'b' == $type ) {
			$value = ( $value ) ? true : false;
		} elseif ( 's' == $type ) {
			$value = wp_kses( trim( $value ), array(), array() );
		} elseif ( 'u' == $type ) {
			$value = esc_url_raw( trim( $value ), array( 'http', 'https' ) );
		} elseif ( 't' == $type ) {
			$value = trim( $value );
		}
		return ( $value );
	}

	public function trad_load_css_and_jss() {
		// used to make sure our copy of jquery is loaded when we want
		$jquery_handle = 'jquery';
		// if none of the pages we are dealing with have shortcodes activated...don't do it...
		if ( ! $this->trad_ioy_shortcodes_activated ) {
			return;
		}

		// this tells us if we want to load the .min versions of css and js when available
		$trad_use_min_js = $this->get_setting( 'trad_use_min_js' );

		// load our plugin-specific css
		if ( $this->get_setting( 'trad_load_trad_ioy_css' ) ) {
			wp_enqueue_style( 'trad-ioy', plugins_url(
				'css/stylesheet.css', dirname( __FILE__ ) ) );
		}

		// load our custom css
		if ( $this->get_setting( 'trad_ioy_local_css_file' ) ) {
			wp_enqueue_style( 'trad-ioy-custom', $this->get_setting( 'trad_ioy_local_css_file' ) );
		}

		if ( $this->get_setting( 'trad_ioy_local_css' ) ) {
			add_action( 'wp_head', array( $this, 'trad_ioy_add_custom_css' ) );
		}

		// load our local copy of the jQuery library if asked
		if ( $this->get_setting( 'trad_load_jquery_js' ) ) {
			// don't load the Wordpress jQuery
			wp_deregister_script('jquery');
			// keep track that we are using our jQuery (would have just reused 'jquery' as a handle, but it wouldn't work)
			$jquery_handle = 'trad-jquery';
			// would have put it in the footer, but it didn't work so well for the jQuery add-ons
			wp_enqueue_script( 'trad-jquery', plugins_url(
				$this->min_or_full( 'js/jquery/jquery-1.10.2', 'js', $trad_use_min_js ), dirname( __FILE__ ) ) );
			add_action( 'wp_head', array( $this, 'trad_jquery_no_conflict' ) );
		} else {
			// we need jQuery regardless of where we get it...
			wp_enqueue_script( 'jquery' );
		}

		if ( $this->get_setting( 'trad_load_cookie_js' ) ) {
			wp_enqueue_script( 'trad-cookie-js', plugins_url(
					$this->min_or_full( 'js/jquery/jquery.cookie', 'js', false ), dirname( __FILE__ ) ), array( $jquery_handle ),
				false, true );
		}

		if ( $this->get_setting( 'trad_load_scrollto_js' ) ) {
			wp_enqueue_script( 'trad-scrollto-js', plugins_url(
					$this->min_or_full( 'js/jquery/jquery.scrollTo-1.4.3.1', 'js', $trad_use_min_js ), dirname( __FILE__ ) ),
				array( $jquery_handle ), false, true );
		}

		// add our special javascipt
		add_action( 'wp_footer', array( $this, 'trad_ioy_js' ) );
	}

	// This is used so we only load all our css and js on pages where the shortcodes are active...
	public function trad_ioy_shortcodes_activated() {
		global $wp_query;
		if ( is_main_query() ) {
			$this->trad_ioy_shortcodes_activated = false;
			foreach ( $wp_query->posts as $post ) {
				if ( get_post_meta( $post->ID, 'trad_ioy_activate_shortcodes', true ) ) {
					$this->trad_ioy_shortcodes_activated = true;
					break;
				}
			}
		}
	}

	public function tra_ioy_shortcode( $atts, $content, $tag ) {
		$type   = $scroll = ''; // initialize
		$output = '';
		global $post;
		$this->trad_ioy_scroll_override = '';
		if ( ! get_post_meta( $post->ID, 'trad_ioy_activate_shortcodes', true ) ) {
			$output = '[' . $tag;
			foreach ( $atts as $key => $value ) {
				// however, I can't say that I trust them entirely :-) (so, esc_html)
				$output .= ' ' . esc_html( $key ) . "='" . esc_html( $value ) . "'";
			}
			$output .= ']';
		} else {
			extract( shortcode_atts( array(
				'type'   => 'all66',
				'scroll' => '',
			), $atts ) );

			switch ( $type ) {
				case 'all66':
					$output = file_get_contents( TRADIOYREALPATH . 'tmpl/bible-in-one-year.html' );
					$output = str_replace( '{trad-ioy-bible-version-select}', $this->trad_ioy_bible_select(), $output );
					if ( false === $output ) {
						$output = '';
					}
					break;
				case 'nt':
					$output = file_get_contents( TRADIOYREALPATH . 'tmpl/nt-in-one-year.html' );
					$output = str_replace( '{trad-ioy-bible-version-select}', $this->trad_ioy_bible_select(), $output );
					if ( false === $output ) {
						$output = '';
					}
					break;
				default:
					break;
			}
			if ( $scroll !== '' ) {
				if ( $scroll === 'y' || $scroll === 'Y' || $scroll === 1 || $scroll === '1' ) {
					$this->trad_ioy_scroll_override = true;
				} else {
					$this->trad_ioy_scroll_override = false;
				}
			}
		}

		return $output;
	}

	public function trad_ioy_add_custom_css() {
		?>
		<style type="text/css">
			/* Custom CSS added by Pine Knoll Media Wordpress Plugin (change in trad Media settings)*/
			<?php echo $this->get_setting( 'trad_ioy_local_css' ) ?>
		</style>
	<?php
	}

	public function min_or_full( $filebasename, $ext, $min = false, $responsive = false ) {
		return ( $filebasename . ( $responsive ? "-responsive" : "" ) . ( $min ? ".min." : "." ) . $ext );
	}

	// if we load our own jQuery library then we at least want to make sure we don't overtake the $ variable...
	public function trad_jquery_no_conflict() {
		?>
		<script type="text/javascript">
			//<! [CDATA[
			jQuery.noConflict();
			//]]>
		</script>
	<?php
	}

	public function trad_ioy_bible_select() {
		$this->select_count ++;
		$output = '<label for="trad-ioy-bible-version-' . $this->select_count . '">Bible version?';
		$output .= '<select id="trad-ioy-bible-version-' . $this->select_count . '" name="trad-ioy-bible-version" class="trad-ioy-bible-version">';
		foreach ( $this->get_setting( 'trad_ioy_avail_bible_versions' ) as $key => $version ) {
			$output .= '<option class="trad-ioy-bible-' . $key . '" value="' . $key . '"' .
					selected( $key == $this->get_setting( 'trad_ioy_bible_version' ), true, false ) . '>' . htmlentities( $version ) .
					'</option>';
		}
		$output .= '</select></label>';
		return $output;
	}

	public function trad_ioy_js() {
		?>
		<!-- Javascript added by Traditores In-One-Year START -->
		<script type="text/javascript">
			var trad_ioy_scroll = <?php echo ($this->trad_ioy_scroll_override === false) ? 'false' :
          (($this->trad_ioy_scroll_override === true) ? 'true' : ($this->get_setting( 'trad_scroll' ) ? 'true' : 'false')); ?>;
			var trad_ioy_cookie = <?php echo $this->get_setting( 'trad_cookie' ) ? 'true' : 'false'; ?>;
			var trad_ioy_copy_today_row = <?php echo $this->get_setting( 'trad_copy_row' ) ? 'true' : 'false'; ?>;
			var trad_ioy_bible_version = '<?php echo $this->get_setting( 'trad_ioy_bible_version' )?>';
			var trad_ioy_prev_bible_version = trad_ioy_bible_version;
			// function from http://www.electrictoolbox.com/pad-number-zeroes-javascript-improved/
			function trad_ioy_num_pad(n, len) {
				s = n.toString();
				if (s.length < len) {
					s = ('0000000000' + s).slice(-len);
				}
				return s;
			}

			jQuery(document).ready(function ($) {
				if ($('table.trad-ioy-reading-plan').length > 0) {
					if (trad_ioy_cookie) {
						var trad_ioy_bible_version = $.cookie('trad_ioy_bible_version');
						if (null === trad_ioy_bible_version || $('.trad-ioy-bible-' + trad_ioy_bible_version).length == 0) {
							trad_ioy_bible_version = '<?php echo $this->get_setting( 'trad_ioy_bible_version' )?>';
							$.cookie('trad_ioy_bible_version', trad_ioy_bible_version, { expires: 365, path: '/' });
						} else {
							// this kicks in if they haven't chosen the esv...
							if (trad_ioy_bible_version != trad_ioy_prev_bible_version) {
								var regex = new RegExp('/' + trad_ioy_prev_bible_version + '/');
								$('a.trad-ioy-reflink').attr('href', function () {
									return this.href.replace(regex, '/' + trad_ioy_bible_version + '/');
								});
							}
						}
					}

					$('option.trad-ioy-bible-' + trad_ioy_bible_version).attr('selected', true);
					var trad_ioy_now = new Date();
					var trad_ioy_mmdd_now = trad_ioy_num_pad(trad_ioy_now.getMonth() + 1, 2) + trad_ioy_num_pad(trad_ioy_now.getDate(), 2);
					if (trad_ioy_scroll) {
						$('body').scrollTo('.' + trad_ioy_mmdd_now);
					}

					$('.' + trad_ioy_mmdd_now).addClass('trad-ioy-is-today');

					if (trad_ioy_copy_today_row) {
						$('tr.' + trad_ioy_mmdd_now).each(function () {
							$(this).clone().prependTo($(this).parent());
						});
					}

					$('select.trad-ioy-bible-version').change(function () {
						if ($(this).val() != trad_ioy_bible_version) {
							trad_ioy_prev_bible_version = trad_ioy_bible_version;
							trad_ioy_bible_version = $(this).val();
							if (trad_ioy_cookie) {
								$.cookie('trad_ioy_bible_version', trad_ioy_bible_version, { expires: 365, path: '/' });
							}
							var regex = new RegExp('/' + trad_ioy_prev_bible_version + '/');
							$('a.trad-ioy-reflink').attr('href', function () {
								return this.href.replace(regex, '/' + trad_ioy_bible_version + '/');
							});
						}
					});

				}

			});
		</script>
		<!-- Javascript added by Traditores In-One-Year STOP -->
	<?php
	}

}

?>