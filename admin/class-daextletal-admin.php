<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package league-table-lite
 */

/**
 * This class should be used to work with the administrative side of WordPress.
 */
class Daextletal_Admin {

	/**
	 * The instance of this class.
	 *
	 * @var self
	 */
	protected static $instance = null;

	/**
	 * The instance of the plugin info.
	 *
	 * @var Daextletal_Shared
	 */
	private $shared = null;

	/**
	 * The screen id for the tables menu.
	 *
	 * @var string
	 */
	private $screen_id_tables = null;

	/**
	 * The screen id for the help menu.
	 *
	 * @var string
	 */
	private $screen_id_help = null;

	/**
	 * The screen id for the pro version menu.
	 *
	 * @var string
	 */
	private $screen_id_pro_version = null;

	/**
	 * The screen id for the options menu.
	 *
	 * @var string
	 */
	private $screen_id_options = null;

	/**
	 * The constructor.
	 */
	private function __construct() {

		// Assign an instance of the plugin info.
		$this->shared = Daextletal_Shared::get_instance();

		// Load admin stylesheets and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the admin menu.
		add_action( 'admin_menu', array( $this, 'me_add_admin_menu' ) );

		// Load the options API registrations and callbacks.
		add_action( 'admin_init', array( $this, 'op_register_options' ) );

		// This hook is triggered during the creation of a new blog.
		add_action( 'wpmu_new_blog', array( $this, 'new_blog_create_options_and_tables' ), 10, 6 );

		// This hook is triggered during the deletion of a blog.
		add_action( 'delete_blog', array( $this, 'delete_blog_delete_options_and_tables' ), 10, 1 );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return self|null
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Enqueue admin-specific styles.
	 *
	 * @return void
	 */
	public function enqueue_admin_styles() {

		$screen = get_current_screen();

		// Menu tables.
		if ( $screen->id === $this->screen_id_tables ) {

			// jQuery UI Dialog.
			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-jquery-ui-dialog',
				$this->shared->get( 'url' ) . 'admin/assets/css/jquery-ui-dialog.css',
				array(),
				$this->shared->get( 'ver' )
			);
			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-jquery-ui-dialog-custom',
				$this->shared->get( 'url' ) . 'admin/assets/css/jquery-ui-dialog-custom.css',
				array(),
				$this->shared->get( 'ver' )
			);

			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework/menu.css', array(), $this->shared->get( 'ver' ) );
			wp_enqueue_style( $this->shared->get( 'slug' ) . '-menu-tables', $this->shared->get( 'url' ) . 'admin/assets/css/menu-tables.css', array(), $this->shared->get( 'ver' ) );
			wp_enqueue_style( $this->shared->get( 'slug' ) . '-jquery-ui-tooltip', $this->shared->get( 'url' ) . 'admin/assets/css/jquery-ui-tooltip.css', array(), $this->shared->get( 'ver' ) );
			wp_enqueue_style( $this->shared->get( 'slug' ) . '-handsontable-full', $this->shared->get( 'url' ) . 'admin/assets/inc/handsontable/handsontable.full.min.css', array(), $this->shared->get( 'ver' ) );

			// Select2.
			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-select2',
				$this->shared->get( 'url' ) . 'admin/assets/inc/select2/css/select2.min.css',
				array(),
				$this->shared->get( 'ver' )
			);
			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-select2-custom',
				$this->shared->get( 'url' ) . 'admin/assets/css/select2-custom.css',
				array(),
				$this->shared->get( 'ver' )
			);

		}

		// Menu help.
		if ( $screen->id === $this->screen_id_help ) {
			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-menu-help',
				$this->shared->get( 'url' ) . 'admin/assets/css/menu-help.css',
				array(),
				$this->shared->get( 'ver' )
			);
		}

		// Menu pro version.
		if ( $screen->id === $this->screen_id_pro_version ) {
			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-menu-pro-version',
				$this->shared->get( 'url' ) . 'admin/assets/css/menu-pro-version.css',
				array(),
				$this->shared->get( 'ver' )
			);
		}

		// Menu options.
		if ( $screen->id === $this->screen_id_options ) {
			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-options', $this->shared->get( 'url' ) . 'admin/assets/css/framework/options.css', array(), $this->shared->get( 'ver' ) );
			wp_enqueue_style( $this->shared->get( 'slug' ) . '-jquery-ui-tooltip', $this->shared->get( 'url' ) . 'admin/assets/css/jquery-ui-tooltip.css', array(), $this->shared->get( 'ver' ) );

			// Select2.
			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-select2',
				$this->shared->get( 'url' ) . 'admin/assets/inc/select2/css/select2.min.css',
				array(),
				$this->shared->get( 'ver' )
			);
			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-select2-custom',
				$this->shared->get( 'url' ) . 'admin/assets/css/select2-custom.css',
				array(),
				$this->shared->get( 'ver' )
			);

		}
	}

	/**
	 * Enqueue admin-specific javascript.
	 *
	 * @return void
	 */
	public function enqueue_admin_scripts() {

		$screen = get_current_screen();

		// Menu tables.
		if ( $screen->id === $this->screen_id_tables ) {

			wp_enqueue_script( 'jquery-ui-tooltip' );
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-jquery-ui-tooltip-init', $this->shared->get( 'url' ) . 'admin/assets/js/jquery-ui-tooltip-init.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-handsontable-full', $this->shared->get( 'url' ) . 'admin/assets/inc/handsontable/handsontable.full.min.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-tables-menu-utility', $this->shared->get( 'url' ) . 'admin/assets/js/tables/utility.js', array( 'jquery', 'jquery-ui-dialog' ), $this->shared->get( 'ver' ), true );
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-tables-menu-context-menu', $this->shared->get( 'url' ) . 'admin/assets/js/tables/context-menu.js', array( 'jquery', 'jquery-ui-dialog', 'daextletal-tables-menu-utility' ), $this->shared->get( 'ver' ), true );
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-init', $this->shared->get( 'url' ) . 'admin/assets/js/tables/init.js', array( 'jquery', 'jquery-ui-dialog', 'daextletal-tables-menu-utility' ), $this->shared->get( 'ver' ), true );
			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-select2',
				$this->shared->get( 'url' ) . 'admin/assets/inc/select2/js/select2.min.js',
				array( 'jquery' ),
				$this->shared->get( 'ver' ),
				true
			);
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-wp-color-picker-init', $this->shared->get( 'url' ) . 'admin/assets/js/wp-color-picker-init.js', array( 'jquery', 'wp-color-picker' ), $this->shared->get( 'ver' ), true );
			wp_enqueue_media();
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-media-uploader', $this->shared->get( 'url' ) . 'admin/assets/js/media-uploader.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

			// Pass the objectL10n object to this javascript file.
			wp_localize_script(
				$this->shared->get( 'slug' ) . '-tables-menu-utility',
				'objectL10n',
				array(
					'column'                               => wp_strip_all_tags( __( 'Column', 'league-table-lite' ) ),
					'name'                                 => wp_strip_all_tags( __( 'Name', 'league-table-lite' ) ),
					'description'                          => wp_strip_all_tags( __( 'Description', 'league-table-lite' ) ),
					'rows'                                 => wp_strip_all_tags( __( 'Rows', 'league-table-lite' ) ),
					'columns'                              => wp_strip_all_tags( __( 'Columns', 'league-table-lite' ) ),
					'position_label'                       => wp_strip_all_tags( __( 'Position Label', 'league-table-lite' ) ),
					'table_width_value'                    => wp_strip_all_tags( __( 'Table Width Value', 'league-table-lite' ) ),
					'table_minimum_width'                  => wp_strip_all_tags( __( 'Table Minimum Width', 'league-table-lite' ) ),
					'column_width_value'                   => wp_strip_all_tags( __( 'Column Width Value', 'league-table-lite' ) ),
					'container_width'                      => wp_strip_all_tags( __( 'Container Width', 'league-table-lite' ) ),
					'container_height'                     => wp_strip_all_tags( __( 'Container Height', 'league-table-lite' ) ),
					'table_margin_top'                     => wp_strip_all_tags( __( 'Table Margin Top', 'league-table-lite' ) ),
					'table_margin_bottom'                  => wp_strip_all_tags( __( 'Table Margin Bottom', 'league-table-lite' ) ),
					'header_font_size'                     => wp_strip_all_tags( __( 'Header Font Size', 'league-table-lite' ) ),
					'header_font_family'                   => wp_strip_all_tags( __( 'Header Font Family', 'league-table-lite' ) ),
					'header_background_color'              => wp_strip_all_tags( __( 'Header Background Color', 'league-table-lite' ) ),
					'header_font_color'                    => wp_strip_all_tags( __( 'Header Font Color', 'league-table-lite' ) ),
					'header_link_color'                    => wp_strip_all_tags( __( 'Header Link Color', 'league-table-lite' ) ),
					'header_border_color'                  => wp_strip_all_tags( __( 'Header Border Color', 'league-table-lite' ) ),
					'body_font_size'                       => wp_strip_all_tags( __( 'Body Font Size', 'league-table-lite' ) ),
					'body_font_family'                     => wp_strip_all_tags( __( 'Body Font Family', 'league-table-lite' ) ),
					'even_rows_background_color'           => wp_strip_all_tags( __( 'Even Rows Background Color', 'league-table-lite' ) ),
					'odd_rows_background_color'            => wp_strip_all_tags( __( 'Odd Rows Background Color', 'league-table-lite' ) ),
					'even_rows_font_color'                 => wp_strip_all_tags( __( 'Even Rows Font Color', 'league-table-lite' ) ),
					'odd_rows_font_color'                  => wp_strip_all_tags( __( 'Odd Rows Font Color', 'league-table-lite' ) ),
					'even_rows_link_color'                 => wp_strip_all_tags( __( 'Even Rows Link Color', 'league-table-lite' ) ),
					'odd_rows_link_color'                  => wp_strip_all_tags( __( 'Odd Rows Link Color', 'league-table-lite' ) ),
					'rows_border_color'                    => wp_strip_all_tags( __( 'Rows Border Color', 'league-table-lite' ) ),
					'autoalignment_affected_rows_left'     => wp_strip_all_tags( __( 'Affected Rows (Left)', 'league-table-lite' ) ),
					'autoalignment_affected_rows_center'   => wp_strip_all_tags( __( 'Affected Rows (Center)', 'league-table-lite' ) ),
					'autoalignment_affected_rows_right'    => wp_strip_all_tags( __( 'Affected Rows (Right)', 'league-table-lite' ) ),
					'autoalignment_affected_columns_left'  => wp_strip_all_tags( __( 'Affected Columns (Left)', 'league-table-lite' ) ),
					'autoalignment_affected_columns_center' => wp_strip_all_tags( __( 'Affected Columns (Center)', 'league-table-lite' ) ),
					'autoalignment_affected_columns_right' => wp_strip_all_tags( __( 'Affected Columns (Right)', 'league-table-lite' ) ),
					'tablet_breakpoint'                    => wp_strip_all_tags( __( 'Tablet Breakpoint', 'league-table-lite' ) ),
					'hide_tablet_list'                     => wp_strip_all_tags( __( 'Tablet Hide List', 'league-table-lite' ) ),
					'tablet_header_font_size'              => wp_strip_all_tags( __( 'Tablet Header Font Size', 'league-table-lite' ) ),
					'tablet_body_font_size'                => wp_strip_all_tags( __( 'Tablet Body Font Size', 'league-table-lite' ) ),
					'phone_breakpoint'                     => wp_strip_all_tags( __( 'Phone Breakpoint', 'league-table-lite' ) ),
					'hide_phone_list'                      => wp_strip_all_tags( __( 'Phone Hide List', 'league-table-lite' ) ),
					'phone_header_font_size'               => wp_strip_all_tags( __( 'Phone Header Font Size', 'league-table-lite' ) ),
					'phone_body_font_size'                 => wp_strip_all_tags( __( 'Phone Body Font Size', 'league-table-lite' ) ),
					'text_color'                           => wp_strip_all_tags( __( 'Text Color', 'league-table-lite' ) ),
					'background_color'                     => wp_strip_all_tags( __( 'Background Color', 'league-table-lite' ) ),
					'link'                                 => wp_strip_all_tags( __( 'Link', 'league-table-lite' ) ),
					'link_color'                           => wp_strip_all_tags( __( 'Link Color', 'league-table-lite' ) ),
					'image_left'                           => wp_strip_all_tags( __( 'Image Left', 'league-table-lite' ) ),
					'image_right'                          => wp_strip_all_tags( __( 'Image Right', 'league-table-lite' ) ),
					'update_cell_properties'               => wp_strip_all_tags( __( 'Update Cell Properties', 'league-table-lite' ) ),
					'add_cell_properties'                  => wp_strip_all_tags( __( 'Add Cell Properties', 'league-table-lite' ) ),
					'cell_properties_added_message'        => wp_strip_all_tags( __( 'The cell properties have been successfully added.', 'league-table-lite' ) ),
					'cell_properties_updated_message'      => wp_strip_all_tags( __( 'The cell properties have been successfully updated.', 'league-table-lite' ) ),
					'cell_properties_reset_message'        => wp_strip_all_tags( __( 'The cell properties have been successfully deleted.', 'league-table-lite' ) ),
					'cell_properties_error_partial_message' => wp_strip_all_tags( __( 'Please enter valid values in the following fields:', 'league-table-lite' ) ),
					'table_success'                        => wp_strip_all_tags( __( 'The table has been successfully updated.', 'league-table-lite' ) ),
					'table_error_partial_message'          => wp_strip_all_tags( __( 'Please enter valid values in the following fields:', 'league-table-lite' ) ),
					'insert_row_above'                     => wp_strip_all_tags( __( 'Insert Row Above', 'league-table-lite' ) ),
					'insert_row_below'                     => wp_strip_all_tags( __( 'Insert Row Below', 'league-table-lite' ) ),
					'insert_column_left'                   => wp_strip_all_tags( __( 'Insert Column Left', 'league-table-lite' ) ),
					'insert_column_right'                  => wp_strip_all_tags( __( 'Insert Column Right', 'league-table-lite' ) ),
					'remove_row'                           => wp_strip_all_tags( __( 'Remove Row', 'league-table-lite' ) ),
					'remove_column'                        => wp_strip_all_tags( __( 'Remove Column', 'league-table-lite' ) ),
					'copy_to_system_clipboard'             => wp_strip_all_tags( __( 'Copy to System Clipboard', 'league-table-lite' ) ),
					'cut_in_system_clipboard'              => wp_strip_all_tags( __( 'Cut in System Clipboard', 'league-table-lite' ) ),
					'paste_from_system_clipboard'          => wp_strip_all_tags( __( 'Paste from System Clipboard', 'league-table-lite' ) ),
					'copy_to_spreadsheet_clipboard'        => wp_strip_all_tags( __( 'Copy to Spreadsheet Clipboard', 'league-table-lite' ) ),
					'paste_spreadsheet_clipboard_cell_data' => wp_strip_all_tags( __( 'Paste Spreadsheet Clipboard Cell Data', 'league-table-lite' ) ),
					'paste_spreadsheet_clipboard_cell_properties' => wp_strip_all_tags( __( 'Paste Spreadsheet Clipboard Cell Properties', 'league-table-lite' ) ),
					'paste_spreadsheet_clipboard_cell_data_and_cell_properties' => wp_strip_all_tags( __( 'Paste Spreadsheet Clipboard Cell Data and Cell Properties', 'league-table-lite' ) ),
					'reset_data'                           => wp_strip_all_tags( __( 'Reset Data', 'league-table-lite' ) ),
					'reset_cell_properties'                => wp_strip_all_tags( __( 'Reset Cell Properties', 'league-table-lite' ) ),
					'reset_data_and_cell_properties'       => wp_strip_all_tags( __( 'Reset Data and Cell Properties', 'league-table-lite' ) ),
					'delete'                               => wp_strip_all_tags( __( 'Delete', 'league-table-lite' ) ),
					'cancel'                               => wp_strip_all_tags( __( 'Cancel', 'league-table-lite' ) ),
				)
			);

			// Store the JavaScript parameters in the window.DAEXTLETAL_PARAMETERS object.
			$initialization_script  = 'window.DAEXTLETAL_PARAMETERS = {';
			$initialization_script .= 'ajax_url: "' . admin_url( 'admin-ajax.php' ) . '",';
			$initialization_script .= 'nonce: "' . wp_create_nonce( 'daextletal' ) . '",';
			$initialization_script .= 'admin_url: "' . get_admin_url() . '"';
			$initialization_script .= '};';
			if ( false !== $initialization_script ) {
				wp_add_inline_script( $this->shared->get( 'slug' ) . '-tables-menu-utility', $initialization_script, 'before' );
			}
		}

		// Menu options.
		if ( $screen->id === $this->screen_id_options ) {

			// Select2.
			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-select2',
				$this->shared->get( 'url' ) . 'admin/assets/inc/select2/js/select2.min.js',
				'jquery',
				$this->shared->get( 'ver' ),
				true
			);

			wp_enqueue_script( 'jquery-ui-tooltip' );
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-jquery-ui-tooltip-init', $this->shared->get( 'url' ) . 'admin/assets/js/jquery-ui-tooltip-init.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu-options', $this->shared->get( 'url' ) . 'admin/assets/js/menu-options.js', array( 'jquery', $this->shared->get( 'slug' ) . '-select2' ), $this->shared->get( 'ver' ), true );

		}
	}

	/**
	 * Plugin activation.
	 *
	 * @param bool $networkwide Whether to activate the plugin for all sites in the network.
	 *
	 * @return void
	 */
	static public function ac_activate( $networkwide ) {

		/**
		 * Create options and tables for all the sites in the network.
		 */
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			/**
			 * If this is a "Network Activation" create the options and tables
			 * for each blog.
			 */
			if ( $networkwide ) {

				// Get the current blog id.
				global $wpdb;
				$current_blog = $wpdb->blogid;

				// create an array with all the blog ids.
				$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" ); // phpcs:ignore

				// Iterate through all the blogs.
				foreach ( $blogids as $blog_id ) {

					// Switch to the iterated blog.
					switch_to_blog( $blog_id );

					// Create options and tables for the iterated blog.
					self::ac_initialize_options();
					self::ac_create_database_tables();

				}

				// Switch to the current blog.
				switch_to_blog( $current_blog );

			} else {

				/**
				 * If this is not a "Network Activation" create options and
				 * tables only for the current blog.
				 */
				self::ac_initialize_options();
				self::ac_create_database_tables();

			}
		} else {

			/**
			 * If this is not a multisite installation create options and
			 * tables only for the current blog.
			 */
			self::ac_initialize_options();
			self::ac_create_database_tables();

		}
	}

	/**
	 * Create the options and tables for the newly created blog.
	 *
	 * @param int $blog_id The id of the new blog.
	 *
	 * @return void
	 */
	public function new_blog_create_options_and_tables( $blog_id ) {

		global $wpdb;

		/**
		 * If the plugin is "Network Active" create the options and tables for
		 * this new blog.
		 */
		if ( is_plugin_active_for_network( 'league-table/init.php' ) ) {

			// Get the id of the current blog.
			$current_blog = $wpdb->blogid;

			// Switch to the blog that is being activated.
			switch_to_blog( $blog_id );

			// Create options and database tables for the new blog.
			$this->ac_initialize_options();
			$this->ac_create_database_tables();

			// Switch to the current blog.
			switch_to_blog( $current_blog );

		}
	}

	/**
	 * Delete options and tables for the deleted blog.
	 *
	 * @param int $blog_id The id of the blog that is being deleted.
	 *
	 * @return void
	 */
	public function delete_blog_delete_options_and_tables( $blog_id ) {

		global $wpdb;

		// Get the id of the current blog.
		$current_blog = $wpdb->blogid;

		// Switch to the blog that is being activated.
		switch_to_blog( $blog_id );

		// Create options and database tables for the new blog.
		$this->un_delete_options();
		$this->un_delete_database_tables();

		// Switch to the current blog.
		switch_to_blog( $current_blog );
	}

	/**
	 * Initialize plugin options.
	 *
	 * @return void
	 */
	public static function ac_initialize_options() {

		if ( intval( get_option( 'daextletal_options_version' ), 10 ) < 1 ) {

			// Assign an instance of Daextletal_Shared.
			$shared = Daextletal_Shared::get_instance();

			foreach ( $shared->get( 'options' ) as $key => $value ) {
				add_option( $key, $value );
			}

			// Update options version.
			update_option( 'daextletal_options_version', '1' );

		}

	}

	/**
	 * Create the plugin database tables.
	 *
	 * @return void
	 */
	public static function ac_create_database_tables() {

		// Check database version and create the database.
		if ( intval( get_option( 'daextletal_database_version' ), 10 ) < 2 ) {

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			// Create *prefix*_table.
			global $wpdb;
			$sql = "CREATE TABLE {$wpdb->prefix}daextletal_table (
                  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                  temporary TINYINT(1) UNSIGNED DEFAULT 0,
                  name VARCHAR(255) DEFAULT 'Table Name',
                  description VARCHAR(255) DEFAULT 'Table Description',
                  `rows` INT UNSIGNED DEFAULT 10,
                  columns INT UNSIGNED DEFAULT 10,
                  show_position TINYINT(1) UNSIGNED DEFAULT 0,
                  position_side VARCHAR(5) DEFAULT 'left',
                  order_by INT UNSIGNED DEFAULT 1,
                  order_desc_asc TINYINT(1) UNSIGNED DEFAULT 0,
                  order_data_type VARCHAR(10) DEFAULT 'auto',
                  order_date_format VARCHAR(8) DEFAULT 'ddmmyyyy',
                  table_layout TINYINT(1) UNSIGNED DEFAULT 0,
                  table_width INT UNSIGNED DEFAULT 0,
                  table_width_value INT UNSIGNED DEFAULT 400,
                  table_minimum_width INT UNSIGNED DEFAULT 0,
                  column_width TINYINT(1) UNSIGNED DEFAULT 0,
                  column_width_value VARCHAR(2000) DEFAULT '100',
                  table_margin_top INT UNSIGNED DEFAULT 20,
                  table_margin_bottom INT UNSIGNED DEFAULT 20,
                  enable_container TINYINT(1) UNSIGNED DEFAULT 0,
                  container_width INT UNSIGNED DEFAULT 400,
                  container_height INT UNSIGNED DEFAULT 400,
                  header_background_color VARCHAR(7) DEFAULT '#C3512F',
                  header_font_color VARCHAR(7) DEFAULT '#FFFFFF',
                  header_link_color VARCHAR(7) DEFAULT '#FFFFFF',
                  even_rows_background_color VARCHAR(7) DEFAULT '#FFFFFF',
                  even_rows_font_color VARCHAR(7) DEFAULT '#666666',
                  even_rows_link_color VARCHAR(7) DEFAULT '#C3512F',
                  odd_rows_background_color VARCHAR(7) DEFAULT '#FCFCFC',
                  odd_rows_font_color VARCHAR(7) DEFAULT '#666666',
                  odd_rows_link_color VARCHAR(7) DEFAULT '#C3512F',
                  header_border_color VARCHAR(7) DEFAULT '#B34A2A',
                  header_position_alignment VARCHAR(6) DEFAULT 'center',
                  rows_border_color VARCHAR(7) DEFAULT '#E1E1E1',
                  phone_breakpoint INT UNSIGNED DEFAULT 479,
                  tablet_breakpoint INT UNSIGNED DEFAULT 989,
                  position_label VARCHAR(255) DEFAULT '#',
                  number_format TINYINT(1) UNSIGNED DEFAULT 0,
                  enable_sorting TINYINT(1) UNSIGNED DEFAULT 0,
                  enable_manual_sorting TINYINT(1) UNSIGNED DEFAULT 0,
                  show_header TINYINT(1) UNSIGNED DEFAULT 1,
                  header_font_size INT UNSIGNED DEFAULT 11,
                  header_font_family VARCHAR(255) DEFAULT '''Open Sans'', Helvetica, Arial, sans-serif',
                  header_font_weight VARCHAR(3) DEFAULT 400,
                  header_font_style VARCHAR(7) DEFAULT 'normal',
                  body_font_size INT UNSIGNED DEFAULT 11,
                  body_font_family VARCHAR(255) DEFAULT '''Open Sans'', Helvetica, Arial, sans-serif',
                  body_font_weight VARCHAR(3) DEFAULT 400,
                  body_font_style VARCHAR(7) DEFAULT  'normal',
                  autoalignment_priority VARCHAR(7) DEFAULT 'rows',
                  autoalignment_affected_rows_left VARCHAR(2000) DEFAULT '',
                  autoalignment_affected_rows_center VARCHAR(2000) DEFAULT '',
                  autoalignment_affected_rows_right VARCHAR(2000) DEFAULT '',
                  autoalignment_affected_columns_left VARCHAR(110) DEFAULT '',
                  autoalignment_affected_columns_center VARCHAR(110) DEFAULT '',
                  autoalignment_affected_columns_right VARCHAR(110) DEFAULT '',
                  hide_tablet_list VARCHAR(110) DEFAULT '',
                  hide_phone_list VARCHAR(110) DEFAULT '',
                  phone_header_font_size INT UNSIGNED DEFAULT 11,
                  phone_body_font_size INT UNSIGNED DEFAULT 11,
                  phone_hide_images TINYINT(1) UNSIGNED DEFAULT 0,
                  tablet_header_font_size INT UNSIGNED DEFAULT 11,
                  tablet_body_font_size INT UNSIGNED DEFAULT 11,
                  tablet_hide_images TINYINT(1) UNSIGNED DEFAULT 0,
                  enable_cell_properties TINYINT(1) UNSIGNED DEFAULT 1,
                  PRIMARY KEY (id)
            )
            COLLATE = utf8_general_ci
            ";

			dbDelta( $sql );

			// Create *prefix*_data.
			$sql = "CREATE TABLE {$wpdb->prefix}daextletal_data (
              id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
              table_id BIGINT UNSIGNED NOT NULL,
              row_index BIGINT UNSIGNED,
              content LONGTEXT,
              PRIMARY KEY (id)
            )
            COLLATE = utf8_general_ci
            ";

			dbDelta( $sql );

			// Create *prefix*_cell.
			$sql = "CREATE TABLE {$wpdb->prefix}daextletal_cell (
              id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
              table_id BIGINT UNSIGNED NOT NULL,
              row_index BIGINT UNSIGNED NOT NULL,
              column_index INT UNSIGNED NOT NULL,
              link VARCHAR(2083) DEFAULT '',
              image_left VARCHAR(2083) DEFAULT '',
              image_right VARCHAR(2083) DEFAULT '',
              PRIMARY KEY (id)
            )
            COLLATE = utf8_general_ci
            ";

			dbDelta( $sql );

			// Update database version.
			update_option( 'daextletal_database_version', '2' );

		}
	}

	/**
	 * Plugin delete.
	 *
	 * @return void
	 */
	public static function un_delete() {

		// Delete options and tables for all the sites in the network.
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			// Get the current blog id.
			global $wpdb;
			$current_blog = $wpdb->blogid;

			// Create an array with all the blog ids.
			$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" ); // phpcs:ignore

			// Iterate through all the blogs.
			foreach ( $blogids as $blog_id ) {

				// Switch to the iterated blog.
				switch_to_blog( $blog_id );

				// Create options and tables for the iterated blog.
				self::un_delete_options();
				self::un_delete_database_tables();

			}

			// Switch to the current blog.
			switch_to_blog( $current_blog );

		} else {

			// If this is not a multisite installation delete options and tables only for the current blog.
			self::un_delete_options();
			self::un_delete_database_tables();

		}
	}

	/**
	 * Delete plugin options.
	 *
	 * @return void
	 */
	public static function un_delete_options() {

		// Assign an instance of Daextletal_Shared.
		$shared = Daextletal_Shared::get_instance();

		foreach ( $shared->get( 'options' ) as $key => $value ) {
			delete_option( $key );
		}
	}

	/**
	 * Delete plugin database tables.
	 *
	 * @return void
	 */
	public static function un_delete_database_tables() {

		// Assign an instance of Daextletal_Shared.
		$shared = Daextletal_Shared::get_instance();

		global $wpdb;

		$table_name = $wpdb->prefix . $shared->get( 'slug' ) . '_table';
		$sql        = "DROP TABLE $table_name";
		$wpdb->query( $sql ); // phpcs:ignore

		$table_name = $wpdb->prefix . $shared->get( 'slug' ) . '_data';
		$sql        = "DROP TABLE $table_name";
		$wpdb->query( $sql ); // phpcs:ignore

		$table_name = $wpdb->prefix . $shared->get( 'slug' ) . '_cell';
		$sql        = "DROP TABLE $table_name";
		$wpdb->query( $sql ); // phpcs:ignore
	}

	/**
	 * Register the admin menu.
	 *
	 * @return void
	 */
	public function me_add_admin_menu() {

		add_menu_page(
			esc_html__( 'LT', 'league-table-lite' ),
			esc_html__( 'League Table', 'league-table-lite' ),
			get_option( $this->shared->get( 'slug' ) . '_tables_menu_capability' ),
			$this->shared->get( 'slug' ) . '-tables',
			array( $this, 'me_display_menu_tables' ),
			'dashicons-list-view'
		);

		$this->screen_id_tables = add_submenu_page(
			$this->shared->get( 'slug' ) . '-tables',
			esc_html__( 'LT - Tables', 'league-table-lite' ),
			esc_html__( 'Tables', 'league-table-lite' ),
			get_option( $this->shared->get( 'slug' ) . '_tables_menu_capability' ),
			$this->shared->get( 'slug' ) . '-tables',
			array( $this, 'me_display_menu_tables' )
		);

		$this->screen_id_help = add_submenu_page(
			$this->shared->get( 'slug' ) . '-tables',
			esc_html__( 'Help', 'league-table-lite' ),
			esc_html__( 'Help', 'league-table-lite' ),
			'manage_options',
			$this->shared->get( 'slug' ) . '-help',
			array( $this, 'me_display_menu_help' )
		);

		$this->screen_id_pro_version = add_submenu_page(
			$this->shared->get( 'slug' ) . '-tables',
			esc_html__( 'Pro Version', 'league-table-lite' ),
			esc_html__( 'Pro Version', 'league-table-lite' ),
			'manage_options',
			$this->shared->get( 'slug' ) . '-pro-version',
			array( $this, 'me_display_menu_pro_version' )
		);

		$this->screen_id_options = add_submenu_page(
			$this->shared->get( 'slug' ) . '-tables',
			esc_html__( 'LT - Options', 'league-table-lite' ),
			esc_html__( 'Options', 'league-table-lite' ),
			'manage_options',
			$this->shared->get( 'slug' ) . '-options',
			array( $this, 'me_display_menu_options' )
		);
	}

	/**
	 * Includes the tables view.
	 *
	 * @return void
	 */
	public function me_display_menu_tables() {
		include_once 'view/tables.php';
	}

	/**
	 * Includes the help view.
	 *
	 * @return void
	 */
	public function me_display_menu_help() {
		include_once 'view/help.php';
	}

	/**
	 * Includes the pro version view.
	 *
	 * @return void
	 */
	public function me_display_menu_pro_version() {
		include_once 'view/pro-version.php';
	}

	/**
	 * Includes the options view.
	 *
	 * @return void
	 */
	public function me_display_menu_options() {
		include_once 'view/options.php';
	}

	/**
	 * Register options.
	 *
	 * @return void
	 */
	public function op_register_options() {

		// Section general ----------------------------------------------------------.
		add_settings_section(
			'daextletal_general_settings_section',
			null,
			null,
			'daextletal_general_options'
		);

		add_settings_field(
			'tables_menu_capability',
			esc_html__( 'Tables Menu Capability', 'league-table-lite' ),
			array( $this, 'tables_menu_capability_callback' ),
			'daextletal_general_options',
			'daextletal_general_settings_section'
		);

		register_setting(
			'daextletal_general_options',
			'daextletal_tables_menu_capability',
			'sanitize_key'
		);

		add_settings_field(
			'general_javascript_file_url',
			esc_html__( 'General JavaScript File URL', 'league-table-lite' ),
			array( $this, 'general_javascript_file_url_callback' ),
			'daextletal_general_options',
			'daextletal_general_settings_section'
		);

		register_setting(
			'daextletal_general_options',
			'daextletal_general_javascript_file_url',
			'esc_url_raw'
		);

		add_settings_field(
			'general_styleshett_file_url',
			esc_html__( 'General Stylesheet File URL', 'league-table-lite' ),
			array( $this, 'general_stylesheet_file_url_callback' ),
			'daextletal_general_options',
			'daextletal_general_settings_section'
		);

		register_setting(
			'daextletal_general_options',
			'daextletal_general_stylesheet_file_url',
			'esc_url_raw'
		);

		add_settings_field(
			'tablesorter_library_url',
			esc_html__( 'Tablesorter Library URL', 'league-table-lite' ),
			array( $this, 'tablesorter_library_url_callback' ),
			'daextletal_general_options',
			'daextletal_general_settings_section'
		);

		register_setting(
			'daextletal_general_options',
			'daextletal_tablesorter_library_url',
			'esc_url_raw'
		);

		add_settings_field(
			'load_google_font_1',
			esc_html__( 'Load Google Font 1', 'league-table-lite' ),
			array( $this, 'load_google_font_1_callback' ),
			'daextletal_general_options',
			'daextletal_general_settings_section'
		);

		register_setting(
			'daextletal_general_options',
			'daextletal_load_google_font_1',
			array( $this, 'load_google_font_1_validation' )
		);

		add_settings_field(
			'load_google_font_2',
			esc_html__( 'Load Google Font 2', 'league-table-lite' ),
			array( $this, 'load_google_font_2_callback' ),
			'daextletal_general_options',
			'daextletal_general_settings_section'
		);

		register_setting(
			'daextletal_general_options',
			'daextletal_load_google_font_2',
			array( $this, 'load_google_font_2_validation' )
		);

		add_settings_field(
			'max_execution_time',
			esc_html__( 'Max Execution Time', 'league-table-lite' ),
			array( $this, 'max_execution_time_callback' ),
			'daextletal_general_options',
			'daextletal_general_settings_section'
		);

		register_setting(
			'daextletal_general_options',
			'daextletal_max_execution_time',
			array( $this, 'max_execution_time_validation' )
		);

		add_settings_field(
			'limit_shortcode_parsing',
			esc_html__( 'Limit Shortcode Parsing', 'league-table-lite' ),
			array( $this, 'limit_shortcode_parsing_callback' ),
			'daextletal_general_options',
			'daextletal_general_settings_section'
		);

		register_setting(
			'daextletal_general_options',
			'daextletal_limit_shortcode_parsing',
			array( $this, 'limit_shortcode_parsing_validation' )
		);

		add_settings_field(
			'verify_single_shortcode',
			esc_html__( 'Verify Single Shortcode', 'league-table-lite' ),
			array( $this, 'verify_single_shortcode_callback' ),
			'daextletal_general_options',
			'daextletal_general_settings_section'
		);

		register_setting(
			'daextletal_general_options',
			'daextletal_verify_single_shortcode',
			array( $this, 'verify_single_shortcode_validation' )
		);

		add_settings_field(
			'widget_text_shortcode',
			esc_html__( 'Shortcode in Text Widget', 'league-table-lite' ),
			array( $this, 'widget_text_shortcode_callback' ),
			'daextletal_general_options',
			'daextletal_general_settings_section'
		);

		register_setting(
			'daextletal_general_options',
			'daextletal_widget_text_shortcode',
			array( $this, 'widget_text_shortcode_validation' )
		);
	}

	// General --------------------------------------------------------------------------------------------------------.

	/**
	 * Tables Menu Capability option callback.
	 *
	 * @return void
	 */
	public function tables_menu_capability_callback() {

		echo '<input autocomplete="off" type="text" id="daextletal-tables-menu-capability" name="daextletal_tables_menu_capability" class="regular-text" value="' . esc_attr( get_option( 'daextletal_tables_menu_capability' ) ) . '" />';
		echo '<div class="help-icon" title="' . esc_attr__( 'The capability required to get access on the "Tables" menu.', 'league-table-lite' ) . '"></div>';
	}

	/**
	 * General JavaScript File URL option callback.
	 *
	 * @return void
	 */
	public function general_javascript_file_url_callback() {

		echo '<input autocomplete="off" type="text" id="daextletal-general-javascript-file-url" name="daextletal_general_javascript_file_url" class="regular-text" value="' . esc_attr( get_option( 'daextletal_general_javascript_file_url' ) ) . '" />';
		echo '<div class="help-icon" title="' . esc_attr__( 'The URL where the general JavaScript file is located.', 'league-table-lite' ) . '"></div>';
	}

	/**
	 * General Stylesheet File URL option callback.
	 *
	 * @return void
	 */
	public function general_stylesheet_file_url_callback() {

		echo '<input autocomplete="off" type="text" id="daextletal-general-stylesheet-file-url" name="daextletal_general_stylesheet_file_url" class="regular-text" value="' . esc_attr( get_option( 'daextletal_general_stylesheet_file_url' ) ) . '" />';
		echo '<div class="help-icon" title="' . esc_attr__( 'The URL where the general Stylesheet file is located.', 'league-table-lite' ) . '"></div>';
	}

	/**
	 * Tablesorter Library URL option callback.
	 *
	 * @return void
	 */
	public function tablesorter_library_url_callback() {

		echo '<input autocomplete="off" type="text" id="daextletal-tablesorter-library-url" name="daextletal_tablesorter_library_url" class="regular-text" value="' . esc_attr( get_option( 'daextletal_tablesorter_library_url' ) ) . '" />';
		echo '<div class="help-icon" title="' . esc_attr__( 'The URL where the Tablesorter library is located.', 'league-table-lite' ) . '"></div>';
	}

	/**
	 * Load Google Font 1 option callback.
	 *
	 * @return void
	 */
	public function load_google_font_1_callback() {

		echo '<input autocomplete="off" type="text" id="daextletal-load-google-font-1" name="daextletal_load_google_font_1" class="regular-text" value="' . esc_attr( get_option( 'daextletal_load_google_font_1' ) ) . '" />';
		echo '<div class="help-icon" title="' . esc_attr__( 'Enter the URL of a Google Font.', 'league-table-lite' ) . '"></div>';
	}

	/**
	 * Load Google Font 1 validation.
	 *
	 * @param string $input The option value.
	 *
	 * @return string
	 */
	public function load_google_font_1_validation( $input ) {

		if ( strlen( trim( $input ) ) > 0 ) {
			$output = esc_url_raw( $input );
		} else {
			$output = '';
		}

		return $output;
	}

	/**
	 * Load Google Font 2 option callback.
	 *
	 * @return void
	 */
	public function load_google_font_2_callback() {

		echo '<input autocomplete="off" type="text" id="daextletal-load-google-font-2" name="daextletal_load_google_font_2" class="regular-text" value="' . esc_attr( get_option( 'daextletal_load_google_font_2' ) ) . '" />';
		echo '<div class="help-icon" title="' . esc_attr__( 'Enter the URL of a Google Font.', 'league-table-lite' ) . '"></div>';
	}

	/**
	 * Load Google Font 2 validation.
	 *
	 * @param string $input The option value.
	 *
	 * @return string
	 */
	public function load_google_font_2_validation( $input ) {

		if ( strlen( trim( $input ) ) > 0 ) {
			$output = esc_url_raw( $input );
		} else {
			$output = '';
		}

		return $output;
	}

	/**
	 * Max Execution Time option callback.
	 *
	 * @return void
	 */
	public function max_execution_time_callback() {

		echo '<input autocomplete="off" type="text" id="daextletal-max-execution-time" name="daextletal_max_execution_time" class="regular-text" value="' . esc_attr( get_option( 'daextletal_max_execution_time' ) ) . '" />';
		echo '<div class="help-icon" title="' . esc_attr__( 'Please enter a number from 1 to 1000000. This value determines the maximum number of seconds allowed to execute the PHP scripts used by this plugin to alter or display the data of the tables.', 'league-table-lite' ) . '"></div>';
	}

	/**
	 * Max Execution Time validation.
	 *
	 * @param string $input The option value.
	 *
	 * @return int
	 */
	public function max_execution_time_validation( $input ) {

		if ( ! preg_match( $this->shared->digits_regex, $input ) || intval( $input, 10 ) < 1 || intval( $input, 10 ) > 1000000 ) {
			add_settings_error( 'daextletal_max_execution_time', 'daextletal_max_execution_time', esc_html__( 'Please enter a number from 1 to 1000000 in the "Max Execution Time Value" option.', 'league-table-lite' ) );
			$output = get_option( 'daextletal_max_execution_time' );
		} else {
			$output = $input;
		}

		return intval( $output, 10 );
	}

	/**
	 * Limit Shortcode Parsing option callback.
	 *
	 * @return void
	 */
	public function limit_shortcode_parsing_callback() {

		echo '<select id="daextletal-limit-shortcode-parsing" name="daextletal_limit_shortcode_parsing" class="daext-display-none">';
		echo '<option ' . selected( intval( get_option( 'daextletal_limit_shortcode_parsing' ) ), 0, false ) . ' value="0">' . esc_html__( 'No', 'league-table-lite' ) . '</option>';
		echo '<option ' . selected( intval( get_option( 'daextletal_limit_shortcode_parsing' ) ), 1, false ) . ' value="1">' . esc_html__( 'Yes', 'league-table-lite' ) . '</option>';
		echo '</select>';
		echo '<div class="help-icon" title="' . esc_attr__( 'With this option enabled the shortcodes generated with this plugin will be parsed only when the full content of single posts and pages is displayed.', 'league-table-lite' ) . '"></div>';
	}

	/**
	 * Limit Shortcode Parsing validation.
	 *
	 * @param string $input The option value.
	 *
	 * @return string
	 */
	public function limit_shortcode_parsing_validation( $input ) {

		return intval( $input, 10 ) === 1 ? '1' : '0';
	}

	/**
	 * Verify Single Shortcode option callback.
	 *
	 * @return void
	 */
	public function verify_single_shortcode_callback() {

		echo '<select id="daextletal-verify-single-shortcode" name="daextletal_verify_single_shortcode" class="daext-display-none">';
		echo '<option ' . selected( intval( get_option( 'daextletal_verify_single_shortcode' ) ), 0, false ) . ' value="0">' . esc_html__( 'No', 'league-table-lite' ) . '</option>';
		echo '<option ' . selected( intval( get_option( 'daextletal_verify_single_shortcode' ) ), 1, false ) . ' value="1">' . esc_html__( 'Yes', 'league-table-lite' ) . '</option>';
		echo '</select>';
		echo '<div class="help-icon" title="' . esc_attr__( 'With this option enabled the presence of a single application of the same shortcode is verified.', 'league-table-lite' ) . '"></div>';
	}

	/**
	 * Verify Single Shortcode validation.
	 *
	 * @param string $input The option value.
	 *
	 * @return string
	 */
	public function verify_single_shortcode_validation( $input ) {

		return intval( $input, 10 ) === 1 ? '1' : '0';
	}

	/**
	 * Widget Text Shortcode option callback.
	 *
	 * @return void
	 */
	public function widget_text_shortcode_callback() {

		echo '<select id="daextletal-widget-text-shortcode" name="daextletal_widget_text_shortcode" class="daext-display-none">';
		echo '<option ' . selected( intval( get_option( 'daextletal_widget_text_shortcode' ) ), 0, false ) . ' value="0">' . esc_html__( 'No', 'league-table-lite' ) . '</option>';
		echo '<option ' . selected( intval( get_option( 'daextletal_widget_text_shortcode' ) ), 1, false ) . ' value="1">' . esc_html__( 'Yes', 'league-table-lite' ) . '</option>';
		echo '</select>';
		echo '<div class="help-icon" title="' . esc_attr__( 'With this option enabled the shortcodes included inside text widgets will be parsed.', 'league-table-lite' ) . '"></div>';
	}

	/**
	 * Widget Text Shortcode validation.
	 *
	 * @param string $input The option value.
	 *
	 * @return string
	 */
	public function widget_text_shortcode_validation( $input ) {

		return intval( $input, 10 ) === 1 ? '1' : '0';
	}

	/**
	 * If the temporary tables are more than 100 clear the older (first inserted) temporary table.
	 *
	 *  This method is used to avoid un unlimited number of temporary table stored in the 'table', 'data' and 'cell' db
	 *  tables.
	 *
	 *  By deleting all the temporary tables (and not only the last one like this method does) wouldn't be possible to
	 *  work on multiple tabs on the 'Tables' menu without being unable to save the table associated with the first
	 *  opened tabs.
	 *
	 *  With this method a maximum of 100 tabs can be opened on the 'Table' menu to create tables at the same time. If
	 *  101 tabs are for example opened, in the first of these 101 tabs the data of the table will not be saved because
	 *  the temporary data are deleted.
	 *
	 * @return void
	 */
	public function delete_old_temporary_table() {

		// Get all the temporary tables as an array.
		global $wpdb;
		$safe_sql          = "SELECT * FROM {$wpdb->prefix}daextletal_table WHERE temporary = 1 ORDER BY id";
		$temporary_table_a = $wpdb->get_results( $safe_sql, ARRAY_A ); // phpcs:ignore

		// verify if the temporary tables are more than 100.
		if ( count( $temporary_table_a ) > 100 ) {

			// get the id of the older (first inserted) table.
			$older_id = $temporary_table_a[0]['id'];

			// delete the older (first inserted) temporary table.
			global $wpdb;
			$safe_sql = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}daextletal_table WHERE id = %d", $older_id );
			$result     = $wpdb->query( $safe_sql ); // phpcs:ignore

			// delete all the data associated with the older (first inserted) temporary table.
			$safe_sql = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}daextletal_data WHERE table_id = %d", $older_id );
			$result     = $wpdb->query( $safe_sql ); // phpcs:ignore

			// delete all the cells associated with the older (first inserted) temporary table.
			$safe_sql = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}daextletal_cell WHERE table_id = %d", $older_id );
			$result     = $wpdb->query( $safe_sql ); // phpcs:ignore

		}
	}

	/**
	 * Initialize the table data based on the defined table id, number of rows and number of columns
	 *
	 * @param int $table_id The table id.
	 * @param int $number_of_rows The number of rows.
	 * @param int $number_of_columns The number of columns.
	 */
	public function initialize_table_data( $table_id, $number_of_rows, $number_of_columns ) {

		for ( $row_index = 0; $row_index < $number_of_rows; $row_index++ ) {

			if ( 0 === $row_index ) {
				$row_data = array();
				for ( $i = 1; $i <= $number_of_columns; $i++ ) {
					$row_data[] = __( 'Label', 'league-table-lite' ) . ' ' . $i;
				}
				$row_data_json = wp_json_encode( $row_data );
				$this->shared->data_insert_record( $table_id, $row_index, $row_data_json );
			} else {
				$row_data      = array_fill( 0, $number_of_columns, 0 );
				$row_data_json = wp_json_encode( $row_data );
				$this->shared->data_insert_record( $table_id, $row_index, $row_data_json );
			}
		}
	}

	/**
	 * Echo all the dismissible notices based on the values of the $notices array.
	 *
	 * @param array $notices The array of with class and text of the dismissible notices.
	 */
	public function dismissible_notice( $notices ) {

		foreach ( $notices as $key => $notice ) {
			echo '<div class="' . esc_attr( $notice['class'] ) . ' settings-error notice is-dismissible below-h2"><p>' . esc_html( $notice['message'] ) . '</p></div>';
		}
	}
}
