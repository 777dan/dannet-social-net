<?php
/**
 * SettingsBase class file.
 *
 * @package cyr-to-lat
 */

namespace Cyr_To_Lat\Settings\Abstracts;

/**
 * Class SettingsBase
 *
 * This is an abstract class to create the settings page in any plugin.
 * It uses WordPress Settings API and general output of fields of any type.
 * Similar approach is used in many plugins, including WooCommerce.
 */
abstract class SettingsBase {

	/**
	 * Admin script handle.
	 */
	const HANDLE = 'ctl-settings-base';

	/**
	 * Form fields.
	 *
	 * @var array
	 */
	protected $form_fields;

	/**
	 * Plugin options.
	 *
	 * @var array
	 */
	protected $settings;

	/**
	 * Tabs of this settings page.
	 *
	 * @var array|null
	 */
	protected $tabs;

	/**
	 * Get screen id.
	 *
	 * @return string
	 */
	abstract public function screen_id();

	/**
	 * Get option group.
	 *
	 * @return string
	 */
	abstract protected function option_group();

	/**
	 * Get option page.
	 *
	 * @return string
	 */
	abstract protected function option_page();

	/**
	 * Get option name.
	 *
	 * @return string
	 */
	abstract protected function option_name();

	/**
	 * Get plugin base name.
	 *
	 * @return string
	 */
	abstract protected function plugin_basename();

	/**
	 * Get plugin url.
	 *
	 * @return string
	 */
	abstract protected function plugin_url();

	/**
	 * Get plugin version.
	 *
	 * @return string
	 */
	abstract protected function plugin_version();

	/**
	 * Get settings link label.
	 *
	 * @return string
	 */
	abstract protected function settings_link_label();

	/**
	 * Get settings link text.
	 *
	 * @return string
	 */
	abstract protected function settings_link_text();

	/**
	 * Init form fields.
	 */
	abstract protected function init_form_fields();

	/**
	 * Get page title.
	 *
	 * @return string
	 */
	abstract protected function page_title();

	/**
	 * Get menu title.
	 *
	 * @return string
	 */
	abstract protected function menu_title();

	/**
	 * Show setting page.
	 */
	abstract public function settings_page();

	/**
	 * Get section title.
	 *
	 * @return string
	 */
	abstract protected function section_title();

	/**
	 * Show section.
	 *
	 * @param array $arguments Arguments.
	 */
	abstract public function section_callback( $arguments );

	/**
	 * Enqueue scripts in admin.
	 */
	abstract public function admin_enqueue_scripts();

	/**
	 * Get text domain.
	 *
	 * @return string
	 */
	abstract protected function text_domain();

	/**
	 * SettingsBase constructor.
	 *
	 * @param array $tabs Tabs of this settings page.
	 */
	public function __construct( $tabs = [] ) {
		$this->tabs = $tabs;

		if ( ! $this->is_tab() ) {
			add_action( 'current_screen', [ $this, 'setup_tabs_section' ], 9 );
		}

		$this->init();
	}

	/**
	 * Init class.
	 */
	public function init() {
		$this->init_form_fields();
		$this->init_settings();

		if ( $this->is_tab_active( $this ) ) {
			$this->init_hooks();
		}
	}

	/**
	 * Init class hooks.
	 */
	protected function init_hooks() {
		add_action( 'plugins_loaded', [ $this, 'load_plugin_textdomain' ] );

		add_filter(
			'plugin_action_links_' . $this->plugin_basename(),
			[ $this, 'add_settings_link' ],
			10
		);

		add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
		add_action( 'current_screen', [ $this, 'setup_sections' ] );
		add_action( 'current_screen', [ $this, 'setup_fields' ] );

		add_filter( 'pre_update_option_' . $this->option_name(), [ $this, 'pre_update_option_filter' ], 10, 2 );

		add_action( 'admin_enqueue_scripts', [ $this, 'base_admin_enqueue_scripts' ] );
	}

	/**
	 * Get parent slug.
	 *
	 * @return string
	 */
	protected function parent_slug() {
		// By default, add menu pages to Options menu.
		return 'options-general.php';
	}

	/**
	 * Is this the main menu page.
	 *
	 * @return bool
	 */
	protected function is_main_menu_page() {
		// Main menu page should have empty string as parent slug.
		return ! $this->parent_slug();
	}

	/**
	 * Get tab name.
	 *
	 * @return string
	 */
	protected function tab_name() {
		return $this->get_class_name();
	}

	/**
	 * Get class name without namespace.
	 *
	 * @return string
	 */
	protected function get_class_name() {
		$path = explode( '\\', get_class( $this ) );

		return array_pop( $path );
	}

	/**
	 * Is this a tab.
	 *
	 * @return bool
	 */
	protected function is_tab() {
		// Tab has null in tabs property.
		return null === $this->tabs;
	}

	/**
	 * Add link to plugin setting page on plugins page.
	 *
	 * @param array $actions      An array of plugin action links.
	 *                            By default this can include 'activate', 'deactivate', and 'delete'.
	 *                            With Multisite active this can also include
	 *                            'network_active' and 'network_only' items.
	 *
	 * @return array|string[] Plugin links
	 */
	public function add_settings_link( array $actions ) {
		$new_actions = [
			'settings' =>
				'<a href="' . admin_url( 'options-general.php?page=' . $this->option_page() ) .
				'" aria-label="' . esc_attr( $this->settings_link_label() ) . '">' .
				esc_html( $this->settings_link_text() ) . '</a>',
		];

		return array_merge( $new_actions, $actions );
	}

	/**
	 * Initialise Settings.
	 *
	 * Store all settings in a single database entry
	 * and make sure the $settings array is either the default
	 * or the settings stored in the database.
	 */
	protected function init_settings() {
		$this->settings = get_option( $this->option_name(), null );

		$form_fields = $this->form_fields();

		if ( is_array( $this->settings ) ) {
			$this->settings = array_merge( wp_list_pluck( $form_fields, 'default' ), $this->settings );

			return;
		}

		// If there are no settings defined, use defaults.
		$this->settings = array_merge(
			array_fill_keys( array_keys( $form_fields ), '' ),
			wp_list_pluck( $form_fields, 'default' )
		);
	}

	/**
	 * Get the form fields after initialization.
	 *
	 * @return array of options
	 */
	protected function form_fields() {
		if ( empty( $this->form_fields ) ) {
			$this->init_form_fields();
		}

		return array_map( [ $this, 'set_defaults' ], $this->form_fields );
	}

	/**
	 * Set default required properties for each field.
	 *
	 * @param array $field Settings field.
	 *
	 * @return array
	 */
	protected function set_defaults( $field ) {
		if ( ! isset( $field['default'] ) ) {
			$field['default'] = '';
		}

		return $field;
	}

	/**
	 * Add settings page to the menu.
	 */
	public function add_settings_page() {
		if ( $this->is_main_menu_page() ) {
			add_menu_page(
				$this->page_title(),
				$this->menu_title(),
				'manage_options',
				$this->option_page(),
				[ $this, 'settings_base_page' ]
			);

			return;
		}

		add_submenu_page(
			$this->parent_slug(),
			$this->page_title(),
			$this->menu_title(),
			'manage_options',
			$this->option_page(),
			[ $this, 'settings_base_page' ]
		);
	}

	/**
	 * Invoke relevant settings_page() basing on tabs.
	 */
	public function settings_base_page() {
		$this->get_active_tab()->settings_page();
	}

	/**
	 * Invoke relevant admin_enqueue_scripts() basing on tabs.
	 */
	public function base_admin_enqueue_scripts() {
		global $cyr_to_lat_plugin;

		$min = $cyr_to_lat_plugin->min_suffix();

		$this->get_active_tab()->admin_enqueue_scripts();

		wp_enqueue_style(
			self::HANDLE,
			$this->plugin_url() . "/assets/css/settings-base$min.css",
			[],
			$this->plugin_version()
		);
	}

	/**
	 * Setup settings sections.
	 */
	public function setup_sections() {
		if ( ! $this->is_options_screen() ) {
			return;
		}

		$tab = $this->get_active_tab();

		foreach ( $this->form_fields as $form_field ) {
			$title = isset( $form_field['title'] ) ? $form_field['title'] : '';
			add_settings_section(
				$form_field['section'],
				$title,
				[ $tab, 'section_callback' ],
				$tab->option_page()
			);
		}
	}

	/**
	 * Setup tabs section.
	 */
	public function setup_tabs_section() {
		if ( ! $this->is_options_screen() ) {
			return;
		}

		$tab = $this->get_active_tab();

		add_settings_section(
			'tabs_section',
			'',
			[ $this, 'tabs_callback' ],
			$tab->option_page()
		);
	}

	/**
	 * Show tabs.
	 */
	public function tabs_callback() {
		?>
		<div class="ctl-settings-tabs">
			<?php
			$this->tab_link( $this );

			foreach ( $this->tabs as $tab ) {
				$this->tab_link( $tab );
			}
			?>
		</div>
		<?php
	}

	/**
	 * Show tab link.
	 *
	 * @param SettingsBase $tab Tabs of the current settings page.
	 */
	private function tab_link( $tab ) {
		$url    = menu_page_url( $this->option_page(), false );
		$url    = add_query_arg( 'tab', strtolower( $tab->get_class_name() ), $url );
		$active = $this->is_tab_active( $tab ) ? ' active' : '';

		?>
		<a class="ctl-settings-tab<?php echo esc_attr( $active ); ?>" href="<?php echo esc_url( $url ); ?>">
			<?php echo esc_html( $tab->page_title() ); ?>
		</a>
		<?php
	}

	/**
	 * Check if tab is active.
	 *
	 * @param SettingsBase $tab Tab of the current settings page.
	 *
	 * @return bool
	 */
	protected function is_tab_active( $tab ) {
		$current_tab_name = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( null === $current_tab_name && ! $tab->is_tab() ) {
			return true;
		}

		return strtolower( $tab->get_class_name() ) === $current_tab_name;
	}

	/**
	 * Get tabs.
	 *
	 * @return array|null
	 */
	public function get_tabs() {
		return $this->tabs;
	}

	/**
	 * Get active tab.
	 *
	 * @return SettingsBase
	 */
	protected function get_active_tab() {
		if ( ! empty( $this->tabs ) ) {
			foreach ( $this->tabs as $tab ) {
				if ( $this->is_tab_active( $tab ) ) {
					return $tab;
				}
			}
		}

		return $this;
	}

	/**
	 * Setup settings fields.
	 */
	public function setup_fields() {
		if ( ! $this->is_options_screen() ) {
			return;
		}

		register_setting( $this->option_group(), $this->option_name() );

		foreach ( $this->form_fields as $key => $field ) {
			$field['field_id'] = $key;

			add_settings_field(
				$key,
				$field['label'],
				[ $this, 'field_callback' ],
				$this->option_page(),
				$field['section'],
				$field
			);
		}
	}

	/**
	 * Print text/password field.
	 *
	 * @param array $arguments Field arguments.
	 *
	 * @noinspection PhpUnusedPrivateMethodInspection
	 */
	private function print_text_field( array $arguments ) {
		$value = $this->get( $arguments['field_id'] );

		printf(
			'<input name="%1$s[%2$s]" id="%2$s" type="%3$s"' .
			' placeholder="%4$s" value="%5$s" class="regular-text" />',
			esc_html( $this->option_name() ),
			esc_attr( $arguments['field_id'] ),
			esc_attr( $arguments['type'] ),
			esc_attr( $arguments['placeholder'] ),
			esc_html( $value )
		);
	}

	/**
	 * Print number field.
	 *
	 * @param array $arguments Field arguments.
	 *
	 * @noinspection PhpUnusedPrivateMethodInspection
	 */
	private function print_number_field( array $arguments ) {
		$value = $this->get( $arguments['field_id'] );
		$min   = isset( $arguments['min'] ) ? $arguments['min'] : '';
		$max   = isset( $arguments['max'] ) ? $arguments['max'] : '';

		printf(
			'<input name="%1$s[%2$s]" id="%2$s" type="%3$s"' .
			' placeholder="%4$s" value="%5$s" class="regular-text" min="%6$s" max="%7$s" />',
			esc_html( $this->option_name() ),
			esc_attr( $arguments['field_id'] ),
			esc_attr( $arguments['type'] ),
			esc_attr( $arguments['placeholder'] ),
			esc_html( $value ),
			esc_attr( $min ),
			esc_attr( $max )
		);
	}

	/**
	 * Print textarea field.
	 *
	 * @param array $arguments Field arguments.
	 *
	 * @noinspection PhpUnusedPrivateMethodInspection
	 */
	private function print_text_area_field( array $arguments ) {
		$value = $this->get( $arguments['field_id'] );

		printf(
			'<textarea name="%1$s[%2$s]" id="%2$s" placeholder="%3$s" rows="5" cols="50">%4$s</textarea>',
			esc_html( $this->option_name() ),
			esc_attr( $arguments['field_id'] ),
			esc_attr( $arguments['placeholder'] ),
			wp_kses_post( $value )
		);
	}

	/**
	 * Print checkbox field.
	 *
	 * @param array $arguments Field arguments.
	 *
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @noinspection HtmlUnknownAttribute
	 */
	private function print_check_box_field( array $arguments ) {
		$value = (array) $this->get( $arguments['field_id'] );

		if ( empty( $arguments['options'] ) || ! is_array( $arguments['options'] ) ) {
			$arguments['options'] = [ 'yes' => '' ];
		}

		$options_markup = '';
		$iterator       = 0;
		foreach ( $arguments['options'] as $key => $label ) {
			$iterator ++;
			$checked = false;
			if ( is_array( $value ) && in_array( $key, $value, true ) ) {
				$checked = checked( $key, $key, false );
			}
			$options_markup .= sprintf(
				'<label for="%2$s_%7$s">' .
				'<input id="%2$s_%7$s" name="%1$s[%2$s][]" type="%3$s" value="%4$s" %5$s />' .
				' %6$s' .
				'</label>' .
				'<br/>',
				esc_html( $this->option_name() ),
				$arguments['field_id'],
				$arguments['type'],
				$key,
				$checked,
				$label,
				$iterator
			);
		}

		printf(
			'<fieldset>%s</fieldset>',
			wp_kses(
				$options_markup,
				[
					'label' => [
						'for' => [],
					],
					'input' => [
						'id'      => [],
						'name'    => [],
						'type'    => [],
						'value'   => [],
						'checked' => [],
					],
					'br'    => [],
				]
			)
		);
	}

	/**
	 * Print radio field.
	 *
	 * @param array $arguments Field arguments.
	 *
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @noinspection HtmlUnknownAttribute
	 */
	private function print_radio_field( array $arguments ) {
		$value = $this->get( $arguments['field_id'] );

		if ( empty( $arguments['options'] ) || ! is_array( $arguments['options'] ) ) {
			return;
		}

		$options_markup = '';
		$iterator       = 0;
		foreach ( $arguments['options'] as $key => $label ) {
			$iterator ++;
			$options_markup .= sprintf(
				'<label for="%2$s_%7$s">' .
				'<input id="%2$s_%7$s" name="%1$s[%2$s]" type="%3$s" value="%4$s" %5$s />' .
				' %6$s' .
				'</label>' .
				'<br/>',
				esc_html( $this->option_name() ),
				$arguments['field_id'],
				$arguments['type'],
				$key,
				checked( $value, $key, false ),
				$label,
				$iterator
			);
		}

		printf(
			'<fieldset>%s</fieldset>',
			wp_kses(
				$options_markup,
				[
					'label' => [
						'for' => [],
					],
					'input' => [
						'id'      => [],
						'name'    => [],
						'type'    => [],
						'value'   => [],
						'checked' => [],
					],
					'br'    => [],
				]
			)
		);
	}

	/**
	 * Print select field.
	 *
	 * @param array $arguments Field arguments.
	 *
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @noinspection HtmlUnknownAttribute
	 */
	private function print_select_field( array $arguments ) {
		$value = $this->get( $arguments['field_id'] );

		if ( empty( $arguments['options'] ) || ! is_array( $arguments['options'] ) ) {
			return;
		}

		$options_markup = '';
		foreach ( $arguments['options'] as $key => $label ) {
			$options_markup .= sprintf(
				'<option value="%s" %s>%s</option>',
				$key,
				selected( $value, $key, false ),
				$label
			);
		}

		printf(
			'<select name="%1$s[%2$s]">%3$s</select>',
			esc_html( $this->option_name() ),
			esc_html( $arguments['field_id'] ),
			wp_kses(
				$options_markup,
				[
					'option' => [
						'value'    => [],
						'selected' => [],
					],
				]
			)
		);
	}

	/**
	 * Print multiple select field.
	 *
	 * @param array $arguments Field arguments.
	 *
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @noinspection HtmlUnknownAttribute
	 */
	private function print_multiple_select_field( array $arguments ) {
		$value = $this->get( $arguments['field_id'] );

		if ( empty( $arguments['options'] ) || ! is_array( $arguments['options'] ) ) {
			return;
		}

		$options_markup = '';
		foreach ( $arguments['options'] as $key => $label ) {
			$selected = '';
			if ( is_array( $value ) && in_array( $key, $value, true ) ) {
				$selected = selected( $key, $key, false );
			}
			$options_markup .= sprintf(
				'<option value="%s" %s>%s</option>',
				$key,
				$selected,
				$label
			);
		}

		printf(
			'<select multiple="multiple" name="%1$s[%2$s][]">%3$s</select>',
			esc_html( $this->option_name() ),
			esc_html( $arguments['field_id'] ),
			wp_kses(
				$options_markup,
				[
					'option' => [
						'value'    => [],
						'selected' => [],
					],
				]
			)
		);
	}

	/**
	 * Print table field.
	 *
	 * @param array $arguments Field arguments.
	 *
	 * @noinspection PhpUnusedPrivateMethodInspection
	 */
	private function print_table_field( array $arguments ) {
		$value = $this->get( $arguments['field_id'] );

		if ( ! is_array( $value ) ) {
			return;
		}

		$iterator = 0;
		foreach ( $value as $key => $cell_value ) {
			$id = $arguments['field_id'] . '-' . $iterator;

			echo '<div class="ctl-table-cell">';
			printf(
				'<label for="%1$s">%2$s</label>',
				esc_html( $id ),
				esc_html( $key )
			);
			printf(
				'<input name="%1$s[%2$s][%3$s]" id="%4$s" type="%5$s"' .
				' placeholder="%6$s" value="%7$s" class="regular-text" />',
				esc_html( $this->option_name() ),
				esc_attr( $arguments['field_id'] ),
				esc_attr( $key ),
				esc_attr( $id ),
				'text',
				esc_attr( $arguments['placeholder'] ),
				esc_html( $cell_value )
			);
			echo '</div>';

			$iterator ++;
		}
	}

	/**
	 * Output settings field.
	 *
	 * @param array $arguments Field arguments.
	 */
	public function field_callback( array $arguments ) {
		if ( ! isset( $arguments['field_id'] ) ) {
			return;
		}

		$types = [
			'text'     => 'print_text_field',
			'password' => 'print_text_field',
			'number'   => 'print_number_field',
			'textarea' => 'print_text_area_field',
			'checkbox' => 'print_check_box_field',
			'radio'    => 'print_radio_field',
			'select'   => 'print_select_field',
			'multiple' => 'print_multiple_select_field',
			'table'    => 'print_table_field',
		];

		$type = $arguments['type'];

		if ( ! array_key_exists( $type, $types ) ) {
			return;
		}

		// If there is help text.
		$helper = $arguments['helper'];
		if ( $helper ) {
			printf(
				'<span class="helper"><span class="helper-content">%s</span></span>',
				wp_kses_post( $helper )
			);
		}

		$this->{$types[ $type ]}( $arguments );

		// If there is supplemental text.
		$supplemental = $arguments['supplemental'];
		if ( $supplemental ) {
			printf( '<p class="description">%s</p>', wp_kses_post( $supplemental ) );
		}
	}

	/**
	 * Get plugin option.
	 *
	 * @param string $key         Setting name.
	 * @param mixed  $empty_value Empty value for this setting.
	 *
	 * @return string|array The value specified for the option or a default value for the option.
	 */
	public function get( $key, $empty_value = null ) {
		if ( empty( $this->settings ) ) {
			$this->init_settings();
		}

		// Get option default if unset.
		if ( ! isset( $this->settings[ $key ] ) ) {
			$form_fields            = $this->form_fields();
			$this->settings[ $key ] = isset( $form_fields[ $key ] ) ? $this->field_default( $form_fields[ $key ] ) : '';
		}

		if ( '' === $this->settings[ $key ] && ! is_null( $empty_value ) ) {
			$this->settings[ $key ] = $empty_value;
		}

		return $this->settings[ $key ];
	}

	/**
	 * Get a field default value. Defaults to '' if not set.
	 *
	 * @param array $field Setting field default value.
	 *
	 * @return string
	 */
	protected function field_default( array $field ) {
		return empty( $field['default'] ) ? '' : $field['default'];
	}

	/**
	 * Update plugin option.
	 *
	 * @param string $key   Setting name.
	 * @param mixed  $value Setting value.
	 */
	public function update_option( $key, $value ) {
		if ( empty( $this->settings ) ) {
			$this->init_settings();
		}

		$this->settings[ $key ] = $value;
		update_option( $this->option_name(), $this->settings );
	}

	/**
	 * Filter plugin option update.
	 *
	 * @param mixed $value     New option value.
	 * @param mixed $old_value Old option value.
	 *
	 * @return mixed
	 */
	public function pre_update_option_filter( $value, $old_value ) {
		if ( $value === $old_value ) {
			return $value;
		}

		// We save only one table, so merge with all existing tables.
		if ( is_array( $old_value ) && ( is_array( $value ) ) ) {
			$value = array_merge( $old_value, $value );
		}

		$form_fields = $this->form_fields();
		foreach ( $form_fields as $key => $form_field ) {
			if ( 'checkbox' === $form_field['type'] ) {
				$form_field_value = isset( $value[ $key ] ) ? $value[ $key ] : 'no';
				$form_field_value = '1' === $form_field_value || 'yes' === $form_field_value ? 'yes' : 'no';
				$value[ $key ]    = $form_field_value;
			}
		}

		return $value;
	}

	/**
	 * Load plugin text domain.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			$this->text_domain(),
			false,
			dirname( $this->plugin_basename() ) . '/languages/'
		);
	}

	/**
	 * Is current admin screen the plugin options screen.
	 *
	 * @return bool
	 */
	protected function is_options_screen() {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return false;
		}

		$current_screen = get_current_screen();

		$screen_id = $this->screen_id();
		if ( $this->is_main_menu_page() ) {
			$screen_id = str_replace( 'settings_page', 'toplevel_page', $screen_id );
		}

		return $current_screen && ( 'options' === $current_screen->id || $screen_id === $current_screen->id );
	}
}
