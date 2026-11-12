<?php
/**
 * Front-end functionality.
 *
 * @package Traditores_In_One_Year
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class.
 */
class Trad_Ioy {

	/** @var array<string, mixed> */
	protected $def_settings = array();

	/** @var array<string, mixed> */
	protected $settings = array();

	/** @var bool|null */
	protected $scroll_override = null;

	/** @var int */
	protected $select_count = 0;

	/** @var bool */
	protected $assets_enqueued = false;

	/** @var array<string, array{0: string, 1?: string}|string> */
	protected $setting_loc = array(
		'trad_load_trad_ioy_css'        => array( 'trad_ioy_gen_settings', 'load_trad_ioy_css' ),
		'trad_delete_settings'          => array( 'trad_ioy_gen_settings', 'delete_settings' ),
		'trad_scroll'                   => array( 'trad_ioy_gen_settings', 'scroll' ),
		'trad_copy_row'                 => array( 'trad_ioy_gen_settings', 'copy_row' ),
		'trad_cookie'                   => array( 'trad_ioy_gen_settings', 'cookie' ),
		'trad_ioy_bible_version'        => array( 'trad_ioy_gen_settings', 'bible_version' ),
		'trad_ioy_version'              => 'trad_ioy_version',
		'trad_ioy_local_css'            => 'trad_ioy_local_css',
		'trad_ioy_local_css_file'       => array( 'trad_ioy_gen_settings', 'local_css_file' ),
		'trad_ioy_avail_bible_versions' => 'trad_ioy_avail_bible_versions',
	);

	/** @var array<string, string|false> */
	protected $setting_type = array(
		'trad_load_trad_ioy_css'        => 'b',
		'trad_delete_settings'          => 'b',
		'trad_scroll'                   => 'b',
		'trad_copy_row'                 => 'b',
		'trad_cookie'                   => 'b',
		'trad_ioy_version'              => false,
		'trad_ioy_local_css'            => 's',
		'trad_ioy_bible_version'        => 's',
		'trad_ioy_local_css_file'       => 'u',
		'trad_ioy_avail_bible_versions' => 'a',
	);

	/**
	 * @param array<string, mixed> $def_settings Default settings array.
	 */
	public function __construct( $def_settings ) {
		$this->def_settings = $def_settings;
		$this->load_settings();

		add_action( 'init', array( $this, 'register_assets' ) );
		add_shortcode( 'tradioy', array( $this, 'shortcode' ) );
		add_action( 'init', array( $this, 'register_block' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_privacy_shortcode_assets' ), 5 );
	}

	/**
	 * Merges stored options with defaults.
	 */
	protected function load_settings() {
		$gen = get_option( 'trad_ioy_gen_settings', array() );
		if ( ! is_array( $gen ) ) {
			$gen = array();
		}
		$gen_defaults = $this->def_settings['trad_ioy_gen_settings'] ?? array();
		$gen          = wp_parse_args( $gen, $gen_defaults );

		$versions = get_option( 'trad_ioy_avail_bible_versions', array() );
		if ( ! is_array( $versions ) ) {
			$versions = $this->def_settings['trad_ioy_avail_bible_versions'] ?? trad_ioy_default_bible_versions();
		}

		$this->settings = array(
			'trad_ioy_gen_settings'         => $gen,
			'trad_ioy_version'              => get_option( 'trad_ioy_version', TRAD_IOY_VERSION ),
			'trad_ioy_local_css'            => get_option( 'trad_ioy_local_css', '' ),
			'trad_ioy_avail_bible_versions' => $versions,
		);
	}

	/**
	 * Registers scripts and styles.
	 */
	public function register_assets() {
		wp_register_style(
			'trad-ioy',
			plugins_url( 'css/stylesheet.css', TRAD_IOY_PLUGIN_FILE ),
			array(),
			TRAD_IOY_VERSION
		);

		wp_register_script(
			'trad-ioy',
			plugins_url( 'js/trad-ioy.js', TRAD_IOY_PLUGIN_FILE ),
			array(),
			TRAD_IOY_VERSION,
			true
		);
	}

	/**
	 * Enqueue when post content may contain shortcode (early hook).
	 */
	public function register_privacy_shortcode_assets() {
		if ( is_admin() ) {
			return;
		}
		if ( ! is_singular() ) {
			return;
		}
		$post = get_post();
		if ( ! $post instanceof WP_Post ) {
			return;
		}
		if ( has_shortcode( $post->post_content, 'tradioy' ) || has_block( 'traditores-in-one-year/reading-plan', $post ) ) {
			$this->enqueue_frontend_assets( false );
		}
	}

	/**
	 * Registers the dynamic block.
	 */
	public function register_block() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		register_block_type(
			TRAD_IOY_PATH . 'blocks/reading-plan',
			array(
				'render_callback' => array( $this, 'render_block' ),
			)
		);
	}

	/**
	 * Block render callback.
	 *
	 * @param array<string, mixed> $attributes Block attributes.
	 * @return string
	 */
	public function render_block( $attributes ) {
		$type   = isset( $attributes['planType'] ) ? (string) $attributes['planType'] : 'all66';
		$scroll = isset( $attributes['scrollToToday'] ) ? (bool) $attributes['scrollToToday'] : null;

		$atts = array( 'type' => $type );
		if ( null !== $scroll ) {
			$atts['scroll'] = $scroll ? '1' : '0';
		}

		return $this->render_reading_plan( $atts );
	}

	/**
	 * Shortcode handler.
	 *
	 * @param array<string, string>|string $atts    Attributes.
	 * @param string                       $content Enclosed content (unused).
	 * @param string                       $tag     Shortcode tag.
	 * @return string
	 */
	public function shortcode( $atts, $content = '', $tag = '' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		if ( ! is_array( $atts ) ) {
			$atts = array();
		}

		$atts = shortcode_atts(
			array(
				'type'   => 'all66',
				'scroll' => '',
			),
			$atts,
			'tradioy'
		);

		return $this->render_reading_plan( $atts );
	}

	/**
	 * Builds reading plan markup and enqueues assets.
	 *
	 * @param array<string, string> $atts Shortcode/block attributes.
	 * @return string
	 */
	public function render_reading_plan( $atts ) {
		$type = sanitize_key( $atts['type'] );
		if ( ! in_array( $type, array( 'all66', 'nt' ), true ) ) {
			if ( current_user_can( 'edit_posts' ) ) {
				return '<p class="trad-ioy-error">' . esc_html__( 'Unknown reading plan type.', 'traditores-in-one-year' ) . '</p>';
			}
			return '';
		}

		$this->scroll_override = null;
		$scroll_attr           = $atts['scroll'];
		if ( '' !== $scroll_attr ) {
			$scroll_on             = in_array( strtolower( (string) $scroll_attr ), array( 'y', '1', 'true', 'yes' ), true );
			$this->scroll_override = $scroll_on;
		}

		$this->enqueue_frontend_assets( $this->scroll_override );

		$file = ( 'nt' === $type ) ? 'nt-in-one-year.html' : 'bible-in-one-year.html';
		$path = TRAD_IOY_PATH . 'tmpl/' . $file;

		// Local template files ship with the plugin (not remote URLs).
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$template = file_get_contents( $path );
		if ( false === $template ) {
			return '';
		}

		$version  = $this->get_bible_version_code();
		$template = str_replace( '{version}', $version, $template );
		$template = str_replace( '{trad-ioy-bible-version-select}', $this->bible_version_select_markup(), $template );

		return $template;
	}

	/**
	 * @param bool|null $scroll_override Per-request scroll override.
	 */
	protected function enqueue_frontend_assets( $scroll_override ) {
		if ( $this->assets_enqueued ) {
			$this->attach_script_config( $scroll_override );
			return;
		}

		$inline_css = $this->get_setting( 'trad_ioy_local_css' );
		if ( $this->get_setting( 'trad_load_trad_ioy_css' ) || $inline_css ) {
			wp_enqueue_style( 'trad-ioy' );
		}

		$css_file = $this->get_setting( 'trad_ioy_local_css_file' );
		if ( $css_file ) {
			wp_enqueue_style( 'trad-ioy-custom', $css_file, array(), TRAD_IOY_VERSION );
		}

		if ( $inline_css ) {
			wp_enqueue_style( 'trad-ioy' );
			wp_add_inline_style( 'trad-ioy', wp_strip_all_tags( $inline_css ) );
		}

		wp_enqueue_script( 'trad-ioy' );
		$this->attach_script_config( $scroll_override );
		$this->assets_enqueued = true;
	}

	/**
	 * @param bool|null $scroll_override Scroll override.
	 */
	protected function attach_script_config( $scroll_override ) {
		$scroll = $this->get_setting( 'trad_scroll' );
		if ( null !== $scroll_override ) {
			$scroll = $scroll_override;
		}

		$version = $this->get_bible_version_code();
		$allowed = array_keys( $this->get_setting( 'trad_ioy_avail_bible_versions' ) );

		$config = array(
			'scroll'          => (bool) $scroll,
			'cookie'          => (bool) $this->get_setting( 'trad_cookie' ),
			'copyTodayRow'    => (bool) $this->get_setting( 'trad_copy_row' ),
			'bibleVersion'    => $version,
			'allowedVersions' => array_values( array_map( 'sanitize_key', $allowed ) ),
		);

		wp_add_inline_script(
			'trad-ioy',
			'var tradIoyConfig = ' . wp_json_encode( $config ) . ';',
			'before'
		);
	}

	/**
	 * Returns a validated default Bible version code.
	 *
	 * @return string
	 */
	protected function get_bible_version_code() {
		$code    = sanitize_key( (string) $this->get_setting( 'trad_ioy_bible_version' ) );
		$allowed = $this->get_setting( 'trad_ioy_avail_bible_versions' );
		if ( ! isset( $allowed[ $code ] ) ) {
			$code = 'esv';
		}
		return $code;
	}

	/**
	 * @param string $key Setting key.
	 * @return mixed
	 */
	protected function get_setting( $key ) {
		if ( ! isset( $this->setting_loc[ $key ], $this->setting_type[ $key ] ) ) {
			return '';
		}
		$location = $this->setting_loc[ $key ];
		$type     = $this->setting_type[ $key ];
		if ( false === $type ) {
			return '';
		}

		$value = is_array( $location )
			? ( $this->settings[ $location[0] ][ $location[1] ] ?? '' )
			: ( $this->settings[ $location ] ?? '' );

		if ( 'b' === $type ) {
			return (bool) $value;
		}
		if ( 's' === $type ) {
			return is_string( $value ) ? wp_kses( trim( $value ), array(), array() ) : '';
		}
		if ( 'u' === $type ) {
			return is_string( $value ) ? esc_url_raw( trim( $value ), array( 'http', 'https' ) ) : '';
		}
		if ( 'a' === $type ) {
			return is_array( $value ) ? $value : array();
		}
		return $value;
	}

	/**
	 * Bible version dropdown HTML.
	 *
	 * @return string
	 */
	protected function bible_version_select_markup() {
		++$this->select_count;
		$id      = 'trad-ioy-bible-version-' . $this->select_count;
		$current = $this->get_bible_version_code();

		$output  = '<label for="' . esc_attr( $id ) . '">';
		$output .= esc_html__( 'Bible version', 'traditores-in-one-year' );
		$output .= '<select id="' . esc_attr( $id ) . '" name="trad-ioy-bible-version" class="trad-ioy-bible-version" aria-label="' . esc_attr__( 'Bible version', 'traditores-in-one-year' ) . '">';
		foreach ( $this->get_setting( 'trad_ioy_avail_bible_versions' ) as $key => $label ) {
			$key     = sanitize_key( $key );
			$output .= '<option class="trad-ioy-bible-' . esc_attr( $key ) . '" value="' . esc_attr( $key ) . '"' .
				selected( $key, $current, false ) . '>' . esc_html( $label ) . '</option>';
		}
		$output .= '</select></label>';
		return $output;
	}
}
