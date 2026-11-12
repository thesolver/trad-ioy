<?php
/**
 * Admin settings and help.
 *
 * @package Traditores_In_One_Year
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin layer.
 */
class Trad_Ioy_Admin extends Trad_Ioy {

	/** @var string */
	private $options_page_hook = '';

	/**
	 * @param array<string, mixed> $def_settings Defaults.
	 */
	public function __construct( $def_settings ) {
		parent::__construct( $def_settings );

		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'admin_print_footer_scripts', array( $this, 'postbox_footer_script' ), 20 );
	}

	/**
	 * Adds options page under Settings.
	 */
	public function add_settings_page() {
		$this->options_page_hook = (string) add_options_page(
			__( 'Traditores In-One-Year', 'traditores-in-one-year' ),
			__( 'Traditores IOY', 'traditores-in-one-year' ),
			'manage_options',
			'trad-ioy',
			array( $this, 'render_settings_page' )
		);

		if ( $this->options_page_hook ) {
			add_action( 'load-' . $this->options_page_hook, array( $this, 'add_help_tabs' ) );
		}
	}

	/**
	 * Registers Settings API options.
	 */
	public function register_settings() {
		register_setting(
			'trad_ioy_settings',
			'trad_ioy_gen_settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_gen_settings' ),
				'default'           => $this->def_settings['trad_ioy_gen_settings'],
			)
		);

		register_setting(
			'trad_ioy_settings',
			'trad_ioy_local_css',
			array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_local_css' ),
				'default'           => '',
			)
		);

		register_setting(
			'trad_ioy_settings',
			'trad_ioy_avail_bible_versions',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_bible_versions' ),
				'default'           => trad_ioy_default_bible_versions(),
			)
		);

		add_settings_section(
			'trad_ioy_primary',
			__( 'Primary settings', 'traditores-in-one-year' ),
			'__return_false',
			'trad-ioy'
		);

		add_settings_field(
			'trad_ioy_bible_version',
			__( 'Default Bible version', 'traditores-in-one-year' ),
			array( $this, 'field_bible_version' ),
			'trad-ioy',
			'trad_ioy_primary'
		);

		add_settings_field(
			'trad_ioy_behavior',
			__( 'Page behavior', 'traditores-in-one-year' ),
			array( $this, 'field_behavior' ),
			'trad-ioy',
			'trad_ioy_primary'
		);

		add_settings_section(
			'trad_ioy_css',
			__( 'Custom CSS', 'traditores-in-one-year' ),
			'__return_false',
			'trad-ioy'
		);

		add_settings_field(
			'trad_ioy_local_css',
			__( 'Additional CSS', 'traditores-in-one-year' ),
			array( $this, 'field_local_css' ),
			'trad-ioy',
			'trad_ioy_css'
		);

		add_settings_field(
			'trad_ioy_local_css_file',
			__( 'External CSS file URL', 'traditores-in-one-year' ),
			array( $this, 'field_local_css_file' ),
			'trad-ioy',
			'trad_ioy_css'
		);

		add_settings_section(
			'trad_ioy_asset',
			__( 'Assets', 'traditores-in-one-year' ),
			'__return_false',
			'trad-ioy'
		);

		add_settings_field(
			'trad_load_trad_ioy_css',
			__( 'Plugin stylesheet', 'traditores-in-one-year' ),
			array( $this, 'field_load_css' ),
			'trad-ioy',
			'trad_ioy_asset'
		);

		add_settings_section(
			'trad_ioy_uninstall',
			__( 'Uninstall', 'traditores-in-one-year' ),
			array( $this, 'section_uninstall_description' ),
			'trad-ioy'
		);

		add_settings_field(
			'trad_delete_settings',
			__( 'Delete data on uninstall', 'traditores-in-one-year' ),
			array( $this, 'field_delete_settings' ),
			'trad-ioy',
			'trad_ioy_uninstall'
		);
	}

	/**
	 * @param array<string, mixed> $input Posted general settings.
	 * @return array<string, mixed>
	 */
	public function sanitize_gen_settings( $input ) {
		if ( ! is_array( $input ) ) {
			return $this->def_settings['trad_ioy_gen_settings'];
		}

		$allowed_versions = trad_ioy_default_bible_versions();
		$output           = wp_parse_args( $input, $this->def_settings['trad_ioy_gen_settings'] );

		$output['bible_version'] = isset( $input['bible_version'] )
			? sanitize_key( wp_unslash( $input['bible_version'] ) )
			: 'esv';
		if ( ! isset( $allowed_versions[ $output['bible_version'] ] ) ) {
			$output['bible_version'] = 'esv';
		}

		$flags = array( 'scroll', 'copy_row', 'cookie', 'load_trad_ioy_css', 'delete_settings' );
		foreach ( $flags as $flag ) {
			$output[ $flag ] = ! empty( $input[ $flag ] );
		}

		if ( isset( $input['local_css_file'] ) ) {
			$output['local_css_file'] = esc_url_raw( wp_unslash( trim( $input['local_css_file'] ) ), array( 'http', 'https' ) );
		}

		$this->settings['trad_ioy_gen_settings'] = $output;
		return $output;
	}

	/**
	 * @param string $input CSS string.
	 * @return string
	 */
	public function sanitize_local_css( $input ) {
		$input = is_string( $input ) ? wp_unslash( $input ) : '';
		if ( false !== stripos( $input, '</style>' ) ) {
			return '';
		}
		return wp_strip_all_tags( $input );
	}

	/**
	 * @param array<string, string> $input Versions list.
	 * @return array<string, string>
	 */
	public function sanitize_bible_versions( $input ) {
		if ( ! is_array( $input ) ) {
			return trad_ioy_default_bible_versions();
		}
		$clean = array();
		foreach ( $input as $key => $label ) {
			$key = sanitize_key( $key );
			if ( '' === $key ) {
				continue;
			}
			$clean[ $key ] = sanitize_text_field( wp_unslash( $label ) );
		}
		return $clean ? $clean : trad_ioy_default_bible_versions();
	}

	/**
	 * @param string $hook_suffix Current admin screen hook.
	 */
	public function enqueue_admin_assets( $hook_suffix ) {
		if ( $hook_suffix !== $this->options_page_hook ) {
			return;
		}
		wp_enqueue_style(
			'trad-ioy-admin',
			plugins_url( 'css/wp-admin.css', TRAD_IOY_PLUGIN_FILE ),
			array(),
			TRAD_IOY_VERSION
		);
		wp_enqueue_script( 'common' );
		wp_enqueue_script( 'wp-lists' );
		wp_enqueue_script( 'postbox' );
	}

	/**
	 * Postbox toggles without inline onsubmit.
	 */
	public function postbox_footer_script() {
		$screen = get_current_screen();
		if ( ! $screen || $screen->id !== $this->options_page_hook ) {
			return;
		}
		?>
		<script>
		jQuery( function ( $ ) {
			$( '.if-js-closed' ).removeClass( 'if-js-closed' ).addClass( 'closed' );
			if ( typeof postboxes !== 'undefined' ) {
				postboxes.add_postbox_toggles( <?php echo wp_json_encode( $this->options_page_hook ); ?> );
			}
		} );
		</script>
		<?php
	}

	/**
	 * Settings page output.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1>
			<?php
			echo esc_html(
				sprintf(
				/* translators: %s: plugin version number */
					__( 'Traditores In-One-Year (version %s)', 'traditores-in-one-year' ),
					TRAD_IOY_VERSION
				)
			);
			?>
			</h1>
			<?php settings_errors( 'trad_ioy_settings' ); ?>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'trad_ioy_settings' );
				do_settings_sections( 'trad-ioy' );
				submit_button( __( 'Save settings', 'traditores-in-one-year' ) );
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Uninstall section description.
	 */
	public function section_uninstall_description() {
		echo '<p>' . esc_html__(
			'When enabled, plugin options are removed if you delete the plugin from the Plugins screen. WordPress may still warn about data removal; the checkbox controls whether options are actually deleted. Multisite: configure each site separately.',
			'traditores-in-one-year'
		) . '</p>';
	}

	/**
	 * @return array<string, mixed>
	 */
	protected function get_gen_settings() {
		$gen = get_option( 'trad_ioy_gen_settings', array() );
		if ( ! is_array( $gen ) ) {
			$gen = array();
		}
		return wp_parse_args( $gen, $this->def_settings['trad_ioy_gen_settings'] );
	}

	public function field_bible_version() {
		$gen      = $this->get_gen_settings();
		$current  = isset( $gen['bible_version'] ) ? sanitize_key( $gen['bible_version'] ) : 'esv';
		$versions = trad_ioy_default_bible_versions();
		?>
		<select id="trad_ioy_bible_version" name="trad_ioy_gen_settings[bible_version]">
			<?php foreach ( $versions as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $current ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	public function field_behavior() {
		$gen = $this->get_gen_settings();
		?>
		<fieldset>
			<label for="trad_scroll">
				<input type="checkbox" id="trad_scroll" name="trad_ioy_gen_settings[scroll]" value="1" <?php checked( ! empty( $gen['scroll'] ) ); ?> />
				<?php esc_html_e( "Scroll to today's entry (overridable via shortcode or block)", 'traditores-in-one-year' ); ?>
			</label>
			<br />
			<label for="trad_copy_row">
				<input type="checkbox" id="trad_copy_row" name="trad_ioy_gen_settings[copy_row]" value="1" <?php checked( ! empty( $gen['copy_row'] ) ); ?> />
				<?php esc_html_e( "Copy today's row to the top of the table", 'traditores-in-one-year' ); ?>
			</label>
			<br />
			<label for="trad_cookie">
				<input type="checkbox" id="trad_cookie" name="trad_ioy_gen_settings[cookie]" value="1" <?php checked( ! empty( $gen['cookie'] ) ); ?> />
				<?php esc_html_e( "Remember the visitor's Bible version in a browser cookie", 'traditores-in-one-year' ); ?>
			</label>
		</fieldset>
		<?php
	}

	public function field_local_css() {
		$value = get_option( 'trad_ioy_local_css', '' );
		?>
		<textarea id="trad_ioy_local_css" name="trad_ioy_local_css" rows="5" class="large-text code"><?php echo esc_textarea( $value ); ?></textarea>
		<p class="description">
			<?php esc_html_e( 'Custom rules are added via wp_add_inline_style (no style tags).', 'traditores-in-one-year' ); ?>
		</p>
		<?php
	}

	public function field_local_css_file() {
		$gen = $this->get_gen_settings();
		?>
		<input type="url" class="regular-text" id="trad_ioy_local_css_file" name="trad_ioy_gen_settings[local_css_file]" value="<?php echo esc_attr( $gen['local_css_file'] ?? '' ); ?>" />
		<?php
	}

	public function field_load_css() {
		$gen = $this->get_gen_settings();
		?>
		<label for="trad_load_trad_ioy_css">
			<input type="checkbox" id="trad_load_trad_ioy_css" name="trad_ioy_gen_settings[load_trad_ioy_css]" value="1" <?php checked( ! empty( $gen['load_trad_ioy_css'] ) ); ?> />
			<?php esc_html_e( 'Load the plugin stylesheet on pages that display a reading plan', 'traditores-in-one-year' ); ?>
		</label>
		<?php
	}

	public function field_delete_settings() {
		$gen = $this->get_gen_settings();
		?>
		<label for="trad_delete_settings">
			<input type="checkbox" id="trad_delete_settings" name="trad_ioy_gen_settings[delete_settings]" value="1" <?php checked( ! empty( $gen['delete_settings'] ) ); ?> />
			<?php esc_html_e( 'Delete plugin settings when the plugin is uninstalled', 'traditores-in-one-year' ); ?>
		</label>
		<?php
	}

	/**
	 * Help tabs content.
	 */
	public function add_help_tabs() {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}

		$screen->add_help_tab(
			array(
				'id'      => 'trad-ioy-help-instructions',
				'title'   => __( 'Instructions', 'traditores-in-one-year' ),
				'content' => $this->help_instructions(),
			)
		);
		$screen->add_help_tab(
			array(
				'id'      => 'trad-ioy-help-shortcodes',
				'title'   => __( 'Shortcodes & block', 'traditores-in-one-year' ),
				'content' => $this->help_shortcodes(),
			)
		);
		$screen->add_help_tab(
			array(
				'id'      => 'trad-ioy-help-options',
				'title'   => __( 'Options', 'traditores-in-one-year' ),
				'content' => $this->help_options(),
			)
		);
	}

	/**
	 * @return string
	 */
	private function help_instructions() {
		ob_start();
		?>
		<p><?php esc_html_e( 'Add a reading plan with the block “Bible Reading Plan” or the shortcode [tradioy].', 'traditores-in-one-year' ); ?></p>
		<p>
			<?php
			echo wp_kses(
				__(
					'Day links open <a href="https://biblia.com/" rel="noopener noreferrer">Biblia.com</a> (Faithlife) in the visitor’s browser. See Faithlife <a href="https://faithlife.com/terms" rel="noopener noreferrer">terms</a> and <a href="https://www.logos.com/privacy" rel="noopener noreferrer">privacy policy</a>.',
					'traditores-in-one-year'
				),
				array(
					'a' => array(
						'href' => array(),
						'rel'  => array(),
					),
				)
			);
			?>
		</p>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * @return string
	 */
	private function help_shortcodes() {
		ob_start();
		?>
		<p><code>[tradioy]</code> — <?php esc_html_e( 'Full Bible plan (default).', 'traditores-in-one-year' ); ?></p>
		<p><code>[tradioy type="nt"]</code> — <?php esc_html_e( 'New Testament plan.', 'traditores-in-one-year' ); ?></p>
		<p><code>[tradioy scroll="1"]</code> — <?php esc_html_e( 'Force scroll to today.', 'traditores-in-one-year' ); ?></p>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * @return string
	 */
	private function help_options() {
		ob_start();
		?>
		<p><?php esc_html_e( 'Choose the default Bible version, scrolling, optional cookie, and custom CSS. Assets load only on pages that render the plan.', 'traditores-in-one-year' ); ?></p>
		<?php
		return (string) ob_get_clean();
	}
}
