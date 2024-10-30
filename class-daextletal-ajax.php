<?php
/**
 * Ajax actions.
 *
 * @package league-table-lite
 */

/**
 * This class should be used to include ajax actions.
 */
class Daextletal_Ajax {

	/**
	 * The singleton instance of this class.
	 *
	 * @var null
	 */
	protected static $instance = null;

	/**
	 * An instance of the shared class.
	 *
	 * @var null
	 */
	private $shared = null;

	/**
	 * Constructor.
	 */
	private function __construct() {

		// Assign an instance of the plugin info.
		$this->shared = Daextletal_Shared::get_instance();

		// Ajax requests for logged-in users --------------------------------------------------------------------------.
		add_action( 'wp_ajax_daextletal_save_data', array( $this, 'save_data' ) );
		add_action( 'wp_ajax_daextletal_retrieve_table_data', array( $this, 'retrieve_table_data' ) );
		add_action( 'wp_ajax_daextletal_add_remove_rows', array( $this, 'add_remove_rows' ) );
		add_action( 'wp_ajax_daextletal_add_remove_columns', array( $this, 'add_remove_columns' ) );
		add_action( 'wp_ajax_daextletal_retrieve_cell_properties', array( $this, 'retrieve_cell_properties' ) );
		add_action( 'wp_ajax_daextletal_update_reset_cell_properties', array( $this, 'update_reset_cell_properties' ) );
		add_action( 'wp_ajax_daextletal_insert_row_above', array( $this, 'insert_row_above' ) );
		add_action( 'wp_ajax_daextletal_insert_row_below', array( $this, 'insert_row_below' ) );
		add_action( 'wp_ajax_daextletal_insert_column_left', array( $this, 'insert_column_left' ) );
		add_action( 'wp_ajax_daextletal_insert_column_right', array( $this, 'insert_column_right' ) );
		add_action( 'wp_ajax_daextletal_remove_row', array( $this, 'remove_row' ) );
		add_action( 'wp_ajax_daextletal_remove_column', array( $this, 'remove_column' ) );
		add_action( 'wp_ajax_daextletal_reset_cell_properties', array( $this, 'reset_cell_properties' ) );
		add_action( 'wp_ajax_daextletal_get_cell_properties_index', array( $this, 'get_cell_properties_index' ) );
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
	 * Ajax handler used to save the data of the table
	 *
	 *  This method is called when in the "Tables" menu:
	 *
	 *  - The "Add Table" button is clicked
	 *  - The "Update Table" button is clicked
	 *
	 * @return void
	 */
	public function save_data() {

		// Preliminary operations.
		check_ajax_referer( 'daextletal', 'security' );
		$this->shared->check_tables_menu_capability();
		$this->shared->set_max_execution_time();
		$this->shared->raise_memory_limit();

		// Sanitization -----------------------------------------------------------------------------------------------.

		// Table data sanitized with a custom function.
		$table_data = isset( $_POST['table_data'] ) ? $this->shared->sanitize_table_data( wp_unslash( $_POST['table_data'] ) ) : null; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		// General.
		$table_id    = isset( $_POST['table_id'] ) ? intval( $_POST['table_id'], 10 ) : null;
		$name        = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : null;
		$description = isset( $_POST['description'] ) ? sanitize_text_field( wp_unslash( $_POST['description'] ) ) : null;
		$rows        = isset( $_POST['rows'] ) ? intval( $_POST['rows'], 10 ) : null;
		$columns     = isset( $_POST['columns'] ) ? intval( $_POST['columns'], 10 ) : null;

		// Sorting.
		$enable_sorting        = isset( $_POST['enable_sorting'] ) ? intval( $_POST['enable_sorting'], 10 ) : null;
		$enable_manual_sorting = isset( $_POST['enable_manual_sorting'] ) ? intval( $_POST['enable_manual_sorting'], 10 ) : null;
		$show_position         = isset( $_POST['show_position'] ) ? intval( $_POST['show_position'], 10 ) : null;
		$position_side         = isset( $_POST['position_side'] ) ? sanitize_key( $_POST['position_side'], 10 ) : null;
		$position_label        = isset( $_POST['position_label'] ) ? sanitize_text_field( wp_unslash( $_POST['position_label'] ) ) : null;
		$number_format         = isset( $_POST['number_format'] ) ? intval( $_POST['number_format'], 10 ) : null;
		$order_desc_asc        = isset( $_POST['order_desc_asc'] ) ? intval( $_POST['order_desc_asc'], 10 ) : null;
		$order_by              = isset( $_POST['order_by'] ) ? intval( $_POST['order_by'], 10 ) : null;
		$order_data_type       = isset( $_POST['order_data_type'] ) ? sanitize_key( $_POST['order_data_type'] ) : null;
		$order_date_format     = isset( $_POST['order_date_format'] ) ? sanitize_key( $_POST['order_date_format'] ) : null;

		// Style.
		$table_layout               = isset( $_POST['table_layout'] ) ? intval( $_POST['table_layout'], 10 ) : null;
		$table_width                = isset( $_POST['table_width'] ) ? intval( $_POST['table_width'], 10 ) : null;
		$table_width_value          = isset( $_POST['table_width_value'] ) ? intval( $_POST['table_width_value'], 10 ) : null;
		$table_minimum_width        = isset( $_POST['table_minimum_width'] ) ? intval( $_POST['table_minimum_width'], 10 ) : null;
		$column_width               = isset( $_POST['column_width'] ) ? intval( $_POST['column_width'], 10 ) : null;
		$column_width_value         = isset( $_POST['column_width_value'] ) ? sanitize_text_field( wp_unslash( $_POST['column_width_value'] ) ) : null;
		$enable_container           = isset( $_POST['enable_container'] ) ? intval( $_POST['enable_container'], 10 ) : null;
		$container_width            = isset( $_POST['container_width'] ) ? intval( $_POST['container_width'], 10 ) : null;
		$container_height           = isset( $_POST['container_height'] ) ? intval( $_POST['container_height'], 10 ) : null;
		$table_margin_top           = isset( $_POST['table_margin_top'] ) ? intval( $_POST['table_margin_top'], 10 ) : null;
		$table_margin_bottom        = isset( $_POST['table_margin_bottom'] ) ? intval( $_POST['table_margin_bottom'], 10 ) : null;
		$show_header                = isset( $_POST['show_header'] ) ? intval( $_POST['show_header'], 10 ) : null;
		$header_font_size           = isset( $_POST['header_font_size'] ) ? intval( $_POST['header_font_size'], 10 ) : null;
		$header_font_family         = isset( $_POST['header_font_family'] ) ? sanitize_text_field( wp_unslash( $_POST['header_font_family'] ) ) : null;
		$header_font_weight         = isset( $_POST['header_font_weight'] ) ? sanitize_text_field( wp_unslash( $_POST['header_font_weight'] ) ) : null;
		$header_font_style          = isset( $_POST['header_font_style'] ) ? sanitize_key( wp_unslash( $_POST['header_font_style'] ) ) : null;
		$header_position_alignment  = isset( $_POST['header_position_alignment'] ) ? sanitize_key( $_POST['header_position_alignment'] ) : null;
		$header_background_color    = isset( $_POST['header_background_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['header_background_color'] ) ) : null;
		$header_font_color          = isset( $_POST['header_font_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['header_font_color'] ) ) : null;
		$header_link_color          = isset( $_POST['header_link_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['header_link_color'] ) ) : null;
		$header_border_color        = isset( $_POST['header_border_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['header_border_color'] ) ) : null;
		$body_font_size             = isset( $_POST['body_font_size'] ) ? intval( $_POST['body_font_size'], 10 ) : null;
		$body_font_family           = isset( $_POST['body_font_family'] ) ? sanitize_text_field( wp_unslash( $_POST['body_font_family'] ) ) : null;
		$body_font_weight           = isset( $_POST['body_font_weight'] ) ? sanitize_text_field( wp_unslash( $_POST['body_font_weight'] ) ) : null;
		$body_font_style            = isset( $_POST['body_font_style'] ) ? sanitize_key( $_POST['body_font_style'] ) : null;
		$even_rows_background_color = isset( $_POST['even_rows_background_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['even_rows_background_color'] ) ) : null;
		$odd_rows_background_color  = isset( $_POST['odd_rows_background_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['odd_rows_background_color'] ) ) : null;
		$even_rows_font_color       = isset( $_POST['even_rows_font_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['even_rows_font_color'] ) ) : null;
		$even_rows_link_color       = isset( $_POST['even_rows_link_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['even_rows_link_color'] ) ) : null;
		$odd_rows_font_color        = isset( $_POST['odd_rows_font_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['odd_rows_font_color'] ) ) : null;
		$odd_rows_link_color        = isset( $_POST['odd_rows_link_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['odd_rows_link_color'] ) ) : null;
		$rows_border_color          = isset( $_POST['rows_border_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['rows_border_color'] ) ) : null;

		// Autoalignment.
		$autoalignment_priority                = isset( $_POST['autoalignment_priority'] ) ? sanitize_key( $_POST['autoalignment_priority'] ) : null;
		$autoalignment_affected_rows_left      = isset( $_POST['autoalignment_affected_rows_left'] ) ? sanitize_text_field( wp_unslash( $_POST['autoalignment_affected_rows_left'] ) ) : null;
		$autoalignment_affected_rows_center    = isset( $_POST['autoalignment_affected_rows_center'] ) ? sanitize_text_field( wp_unslash( $_POST['autoalignment_affected_rows_center'] ) ) : null;
		$autoalignment_affected_rows_right     = isset( $_POST['autoalignment_affected_rows_right'] ) ? sanitize_text_field( wp_unslash( $_POST['autoalignment_affected_rows_right'] ) ) : null;
		$autoalignment_affected_columns_left   = isset( $_POST['autoalignment_affected_columns_left'] ) ? sanitize_text_field( wp_unslash( $_POST['autoalignment_affected_columns_left'] ) ) : null;
		$autoalignment_affected_columns_center = isset( $_POST['autoalignment_affected_columns_center'] ) ? sanitize_text_field( wp_unslash( $_POST['autoalignment_affected_columns_center'] ) ) : null;
		$autoalignment_affected_columns_right  = isset( $_POST['autoalignment_affected_columns_right'] ) ? sanitize_text_field( wp_unslash( $_POST['autoalignment_affected_columns_right'] ) ) : null;

		// Responsive.
		$tablet_breakpoint       = isset( $_POST['tablet_breakpoint'] ) ? intval( $_POST['tablet_breakpoint'], 10 ) : null;
		$hide_tablet_list        = isset( $_POST['hide_tablet_list'] ) ? sanitize_text_field( wp_unslash( $_POST['hide_tablet_list'] ) ) : null;
		$tablet_header_font_size = isset( $_POST['tablet_header_font_size'] ) ? intval( $_POST['tablet_header_font_size'], 10 ) : null;
		$tablet_body_font_size   = isset( $_POST['tablet_body_font_size'] ) ? intval( $_POST['tablet_body_font_size'], 10 ) : null;
		$tablet_hide_images      = isset( $_POST['tablet_hide_images'] ) ? intval( $_POST['tablet_hide_images'], 10 ) : null;
		$phone_breakpoint        = isset( $_POST['phone_breakpoint'] ) ? intval( $_POST['phone_breakpoint'], 10 ) : null;
		$hide_phone_list         = isset( $_POST['hide_phone_list'] ) ? sanitize_text_field( wp_unslash( $_POST['hide_phone_list'] ) ) : null;
		$phone_header_font_size  = isset( $_POST['phone_header_font_size'] ) ? intval( $_POST['phone_header_font_size'], 10 ) : null;
		$phone_body_font_size    = isset( $_POST['phone_body_font_size'] ) ? intval( $_POST['phone_body_font_size'], 10 ) : null;
		$phone_hide_images       = isset( $_POST['phone_hide_images'] ) ? intval( $_POST['phone_hide_images'], 10 ) : null;

		// Advanced.
		$enable_cell_properties = isset( $_POST['enable_cell_properties'] ) ? intval( $_POST['enable_cell_properties'], 10 ) : null;

		// validate data ----------------------------------------------------------------------------------------------.
		$fields_with_errors_a = array();

		// Table Data.
		if ( ! is_array( $table_data ) ) {
			$fields_with_errors_a[] = 'data';
		}

		// Basic Info.
		if ( strlen( trim( stripslashes( $name ) ) ) < 1 || strlen( trim( stripslashes( $name ) ) ) > 255 ) {
			$fields_with_errors_a[] = 'name';
		}
		if ( strlen( trim( stripslashes( $description ) ) ) < 1 || strlen( trim( stripslashes( $description ) ) ) > 255 ) {
			$fields_with_errors_a[] = 'description';
		}
		if ( ! preg_match( $this->shared->digits_regex, $rows ) || intval( $rows, 10 ) < 1 || intval( $rows, 10 ) > 10000 ) {
			$fields_with_errors_a[] = 'rows';
		}
		if ( ! preg_match( $this->shared->digits_regex, $columns ) || intval( $columns, 10 ) < 1 || intval( $columns, 10 ) > 40 ) {
			$fields_with_errors_a[] = 'columns';
		}

		// Sorting Options.
		if ( strlen( trim( stripslashes( $position_label ) ) ) < 1 || strlen( trim( stripslashes( $position_label ) ) ) > 255 ) {
			$fields_with_errors_a[] = 'position_label';
		}

		// Style Options.
		if ( ! preg_match( $this->shared->digits_regex, $table_width_value ) || intval( $table_width_value, 10 ) < 1 || intval( $table_width_value, 10 ) > 999999 ) {
			$fields_with_errors_a[] = 'table_width_value';
		}
		if ( ! preg_match( $this->shared->digits_regex, $table_minimum_width ) || intval( $table_minimum_width, 10 ) < 0 || intval( $table_minimum_width, 10 ) > 999999 ) {
			$fields_with_errors_a[] = 'table_minimum_width';
		}
		if ( ( ! preg_match( $this->shared->list_of_comma_separated_numbers, $column_width_value ) && strlen( trim( stripslashes( $column_width_value ) ) ) > 0 ) || strlen( trim( stripslashes( $column_width_value ) ) ) > 2000 ) {
			$fields_with_errors_a[] = 'column_width_value';
		}
		if ( ! preg_match( $this->shared->digits_regex, $container_width ) || intval( $container_width, 10 ) < 0 || intval( $container_width, 10 ) > 999999 ) {
			$fields_with_errors_a[] = 'container_width';
		}
		if ( ! preg_match( $this->shared->digits_regex, $container_height ) || intval( $container_height, 10 ) < 0 || intval( $container_height, 10 ) > 999999 ) {
			$fields_with_errors_a[] = 'container_height';
		}
		if ( ! preg_match( $this->shared->digits_regex, $table_margin_top ) || intval( $table_margin_top, 10 ) < 0 || intval( $table_margin_top, 10 ) > 999999 ) {
			$fields_with_errors_a[] = 'table_margin_top';
		}
		if ( ! preg_match( $this->shared->digits_regex, $table_margin_bottom ) || intval( $table_margin_bottom, 10 ) < 0 || intval( $table_margin_bottom, 10 ) > 999999 ) {
			$fields_with_errors_a[] = 'table_margin_bottom';
		}
		if ( ! preg_match( $this->shared->digits_regex, $header_font_size ) || intval( $header_font_size, 10 ) < 1 || intval( $header_font_size, 10 ) > 999999 ) {
			$fields_with_errors_a[] = 'header_font_size';
		}
		if ( ! preg_match( $this->shared->font_family_regex, stripslashes( $header_font_family ) ) || strlen( trim( stripslashes( $header_font_family ) ) ) < 1 || strlen( trim( stripslashes( $header_font_family ) ) ) > 255 ) {
			$fields_with_errors_a[] = 'header_font_family';
		}
		if ( ! preg_match( $this->shared->hex_rgb_regex, $header_background_color ) ) {
			$fields_with_errors_a[] = 'header_background_color';
		}
		if ( ! preg_match( $this->shared->hex_rgb_regex, $header_font_color ) ) {
			$fields_with_errors_a[] = 'header_font_color';
		}
		if ( ! preg_match( $this->shared->hex_rgb_regex, $header_link_color ) ) {
			$fields_with_errors_a[] = 'header_link_color';
		}
		if ( ! preg_match( $this->shared->hex_rgb_regex, $header_border_color ) ) {
			$fields_with_errors_a[] = 'header_border_color';
		}
		if ( ! preg_match( $this->shared->digits_regex, $body_font_size ) || intval( $body_font_size, 10 ) < 1 || intval( $body_font_size, 10 ) > 999999 ) {
			$fields_with_errors_a[] = 'body_font_size';
		}
		if ( ! preg_match( $this->shared->font_family_regex, stripslashes( $body_font_family ) ) || strlen( trim( stripslashes( $body_font_family ) ) ) < 1 || strlen( trim( stripslashes( $body_font_family ) ) ) > 255 ) {
			$fields_with_errors_a[] = 'body_font_family';
		}
		if ( ! preg_match( $this->shared->hex_rgb_regex, $even_rows_background_color ) ) {
			$fields_with_errors_a[] = 'even_rows_background_color';
		}
		if ( ! preg_match( $this->shared->hex_rgb_regex, $odd_rows_background_color ) ) {
			$fields_with_errors_a[] = 'odd_rows_background_color';
		}
		if ( ! preg_match( $this->shared->hex_rgb_regex, $even_rows_font_color ) ) {
			$fields_with_errors_a[] = 'even_rows_font_color';
		}
		if ( ! preg_match( $this->shared->hex_rgb_regex, $odd_rows_font_color ) ) {
			$fields_with_errors_a[] = 'odd_rows_font_color';
		}
		if ( ! preg_match( $this->shared->hex_rgb_regex, $even_rows_link_color ) ) {
			$fields_with_errors_a[] = 'even_rows_link_color';
		}
		if ( ! preg_match( $this->shared->hex_rgb_regex, $odd_rows_link_color ) ) {
			$fields_with_errors_a[] = 'odd_rows_link_color';
		}
		if ( ! preg_match( $this->shared->hex_rgb_regex, $rows_border_color ) ) {
			$fields_with_errors_a[] = 'rows_border_color';
		}

		// Autoalignment Options.
		if ( ( ! preg_match( $this->shared->list_of_comma_separated_numbers, $autoalignment_affected_rows_left ) && strlen( trim( stripslashes( $autoalignment_affected_rows_left ) ) ) > 0 ) || strlen( trim( stripslashes( $autoalignment_affected_rows_left ) ) ) > 2000 ) {
			$fields_with_errors_a[] = 'autoalignment_affected_rows_left';
		}
		if ( ( ! preg_match( $this->shared->list_of_comma_separated_numbers, $autoalignment_affected_rows_center ) && strlen( trim( stripslashes( $autoalignment_affected_rows_center ) ) ) > 0 ) || strlen( trim( stripslashes( $autoalignment_affected_rows_center ) ) ) > 2000 ) {
			$fields_with_errors_a[] = 'autoalignment_affected_rows_center';
		}
		if ( ( ! preg_match( $this->shared->list_of_comma_separated_numbers, $autoalignment_affected_rows_right ) && strlen( trim( stripslashes( $autoalignment_affected_rows_right ) ) ) > 0 ) || strlen( trim( stripslashes( $autoalignment_affected_rows_right ) ) ) > 2000 ) {
			$fields_with_errors_a[] = 'autoalignment_affected_rows_right';
		}
		if ( ( ! preg_match( $this->shared->list_of_comma_separated_numbers, $autoalignment_affected_columns_left ) && strlen( trim( stripslashes( $autoalignment_affected_columns_left ) ) ) > 0 ) || strlen( trim( stripslashes( $autoalignment_affected_columns_left ) ) ) > 110 ) {
			$fields_with_errors_a[] = 'autoalignment_affected_columns_left';
		}
		if ( ( ! preg_match( $this->shared->list_of_comma_separated_numbers, $autoalignment_affected_columns_center ) && strlen( trim( stripslashes( $autoalignment_affected_columns_center ) ) ) > 0 ) || strlen( trim( stripslashes( $autoalignment_affected_columns_center ) ) ) > 110 ) {
			$fields_with_errors_a[] = 'autoalignment_affected_columns_center';
		}
		if ( ( ! preg_match( $this->shared->list_of_comma_separated_numbers, $autoalignment_affected_columns_right ) && strlen( trim( stripslashes( $autoalignment_affected_columns_right ) ) ) > 0 ) || strlen( trim( stripslashes( $autoalignment_affected_columns_right ) ) ) > 110 ) {
			$fields_with_errors_a[] = 'autoalignment_affected_columns_right';
		}

		// Responsive Options.
		if ( ! preg_match( $this->shared->digits_regex, $tablet_breakpoint ) || intval( $tablet_breakpoint, 10 ) < 1 || intval( $tablet_breakpoint, 10 ) > 999999 ) {
			$fields_with_errors_a[] = 'tablet_breakpoint';
		}
		if ( ( ! preg_match( $this->shared->list_of_comma_separated_numbers, $hide_tablet_list ) && strlen( trim( stripslashes( $hide_tablet_list ) ) ) > 0 ) || strlen( trim( stripslashes( $hide_tablet_list ) ) ) > 110 ) {
			$fields_with_errors_a[] = 'hide_tablet_list';
		}
		if ( ! preg_match( $this->shared->digits_regex, $tablet_header_font_size ) || intval( $tablet_header_font_size, 10 ) < 1 || intval( $tablet_header_font_size, 10 ) > 999999 ) {
			$fields_with_errors_a[] = 'tablet_header_font_size';
		}
		if ( ! preg_match( $this->shared->digits_regex, $tablet_body_font_size ) || intval( $tablet_body_font_size, 10 ) < 1 || intval( $tablet_body_font_size, 10 ) > 999999 ) {
			$fields_with_errors_a[] = 'tablet_body_font_size';
		}
		if ( ! preg_match( $this->shared->digits_regex, $phone_breakpoint ) || intval( $phone_breakpoint, 10 ) < 1 || intval( $phone_breakpoint, 10 ) > 999999 ) {
			$fields_with_errors_a[] = 'phone_breakpoint';
		}
		if ( ( ! preg_match( $this->shared->list_of_comma_separated_numbers, $hide_phone_list ) && strlen( trim( stripslashes( $hide_phone_list ) ) ) > 0 ) || strlen( trim( stripslashes( $hide_phone_list ) ) ) > 110 ) {
			$fields_with_errors_a[] = 'hide_phone_list';
		}
		if ( ! preg_match( $this->shared->digits_regex, $phone_header_font_size ) || intval( $phone_header_font_size, 10 ) < 1 || intval( $phone_header_font_size, 10 ) > 999999 ) {
			$fields_with_errors_a[] = 'phone_header_font_size';
		}
		if ( ! preg_match( $this->shared->digits_regex, $phone_body_font_size ) || intval( $phone_body_font_size, 10 ) < 1 || intval( $phone_body_font_size, 10 ) > 999999 ) {
			$fields_with_errors_a[] = 'phone_body_font_size';
		}

		// Return an error message if the submitted data are not valid.
		if ( count( $fields_with_errors_a ) > 0 ) {
			echo esc_html( 'Failed validation on the following fields: ' . implode( ', ', $fields_with_errors_a ) );
			die();
		}

		// UPDATE THE TABLE -------------------------------------------------------------------------------------------.

		// Save the table data in the 'table' db table.
		global $wpdb;
		$safe_sql = $wpdb->prepare(
			"UPDATE {$wpdb->prefix}daextletal_table SET

			name = %s,
			description = %s,
			`rows` = %d,
			columns = %d,
			
			/*sorting*/
            enable_sorting = %d,
            enable_manual_sorting = %d,
            show_position = %d,
            position_side = %s,
            position_label = %s,
            number_format = %d,
            order_desc_asc = %d,
            order_by = %d,
            order_data_type = %s,
            order_date_format = %s,
			
			/* style */
			table_layout = %d,
            table_width = %d,
            table_width_value = %d,
            table_minimum_width = %d,
            column_width = %d,
            column_width_value = %s,
            table_margin_top = %d,
            table_margin_bottom = %d,
            enable_container = %d,
            container_width = %d,
            container_height = %d,
            show_header = %d,
            header_font_size = %d,
            header_font_family = %s,
            header_font_weight = %s,
            header_font_style = %s,
            header_background_color = %s,
            header_font_color = %s,
            header_link_color = %s,
            header_border_color = %s,
            header_position_alignment = %s,
            body_font_size = %d,
            body_font_family = %s,
            body_font_weight = %s,
            body_font_style = %s,
            even_rows_background_color = %s,
            odd_rows_background_color = %s,
            even_rows_font_color = %s,
            even_rows_link_color = %s,
            odd_rows_font_color = %s,
            odd_rows_link_color = %s,
            rows_border_color = %s,
            
            /* autoalignment */
            autoalignment_priority = %s,
            autoalignment_affected_rows_left = %s,
            autoalignment_affected_rows_center = %s,
            autoalignment_affected_rows_right = %s,
            autoalignment_affected_columns_left = %s,
            autoalignment_affected_columns_center = %s,
            autoalignment_affected_columns_right = %s,
            
            /* responsive */
            tablet_breakpoint = %d,
            hide_tablet_list = %s,
            tablet_header_font_size = %d,
            tablet_body_font_size = %d,
            tablet_hide_images = %d,
            phone_breakpoint = %d,
            hide_phone_list = %s,
            phone_header_font_size = %d,
            phone_body_font_size = %d,
            phone_hide_images = %d,
            
            /* advanced */
            enable_cell_properties = %d,
			
            temporary = 0
            WHERE id = %d",
			$name,
			$description,
			$rows,
			$columns,
			// sorting.
			$enable_sorting,
			$enable_manual_sorting,
			$show_position,
			$position_side,
			$position_label,
			$number_format,
			$order_desc_asc,
			$order_by,
			$order_data_type,
			$order_date_format,
			// $style
			$table_layout,
			$table_width,
			$table_width_value,
			$table_minimum_width,
			$column_width,
			$column_width_value,
			$table_margin_top,
			$table_margin_bottom,
			$enable_container,
			$container_width,
			$container_height,
			$show_header,
			$header_font_size,
			$header_font_family,
			$header_font_weight,
			$header_font_style,
			$header_background_color,
			$header_font_color,
			$header_link_color,
			$header_border_color,
			$header_position_alignment,
			$body_font_size,
			$body_font_family,
			$body_font_weight,
			$body_font_style,
			$even_rows_background_color,
			$odd_rows_background_color,
			$even_rows_font_color,
			$even_rows_link_color,
			$odd_rows_font_color,
			$odd_rows_link_color,
			$rows_border_color,
			// autoalignment.
			$autoalignment_priority,
			$autoalignment_affected_rows_left,
			$autoalignment_affected_rows_center,
			$autoalignment_affected_rows_right,
			$autoalignment_affected_columns_left,
			$autoalignment_affected_columns_center,
			$autoalignment_affected_columns_right,
			// responsive.
			$tablet_breakpoint,
			$hide_tablet_list,
			$tablet_header_font_size,
			$tablet_body_font_size,
			$tablet_hide_images,
			$phone_breakpoint,
			$hide_phone_list,
			$phone_header_font_size,
			$phone_body_font_size,
			$phone_hide_images,
			$enable_cell_properties,
			$table_id
		);

		$wpdb->query( $safe_sql ); // phpcs:ignore

		$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . '_data';

		// Delete all the data of this table.
		$safe_sql = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}daextletal_data WHERE table_id = %d", $table_id );
		$wpdb->query( $safe_sql ); // phpcs:ignore

		// Add the new data.
		$values        = array();
		$place_holders = array();
		$query         = "INSERT INTO $table_name (table_id, row_index, content) VALUES ";
		foreach ( $table_data as $row_index => $row_data ) {

			$row_data_json = wp_json_encode( $row_data );

			array_push( $values, $table_id, $row_index, $row_data_json );
			$place_holders[] = "('%d', '%d', '%s')";

		}

		$query   .= implode( ', ', $place_holders );
		$safe_sql = $wpdb->prepare( "$query ", $values ); // phpcs:ignore
		$wpdb->query( $safe_sql ); // phpcs:ignore

		// Generate the response and terminate the script.
		echo 'success';
		die();
	}

	/**
	 * This method is called when in the "Tables" menu a table is edited and is used to return the data of a
	 *   specified table (data_content) in the json format and the indexes of the selected "order_by_*" options of the
	 *   table (order_by).
	 *
	 *   The returned data in the JSON format will be used to initialize the handsontable table. A json encoded string
	 * which includes the data of the table and the indexes of the selected.
	 *
	 * @return void
	 */
	public function retrieve_table_data() {

		// Preliminary operations.
		check_ajax_referer( 'daextletal', 'security' );
		$this->shared->check_tables_menu_capability();
		$this->shared->set_max_execution_time();
		$this->shared->raise_memory_limit();

		// Sanitization.
		$table_id = isset( $_POST['table_id'] ) ? intval( $_POST['table_id'], 10 ) : null;

		// Retrieve the table data ------------------------------------------------------------------------------------.

		// Retrieve the data.
		global $wpdb;
		$safe_sql = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}daextletal_data WHERE table_id = %d ORDER BY row_index ASC", $table_id );
		$data_a     = $wpdb->get_results( $safe_sql ); //phpcs:ignore

		// Load the data in an array.
		foreach ( $data_a as $key => $data ) {
			$data_content[] = json_decode( $data->content );
		}

		// Retrieve the selected "Order By *" field for all the five priorities ---------------------------------------.
		global $wpdb;
		$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . '_table';
		$safe_sql   = $wpdb->prepare( "SELECT order_by FROM {$wpdb->prefix}daextletal_table WHERE id = %d ", $table_id );
		$table_obj  = $wpdb->get_row( $safe_sql ); //phpcs:ignore

		$order_by = $table_obj->order_by;

		// Create an array with these two set of data (table data and list of modified cells).
		$answer                 = array();
		$answer['data_content'] = $data_content;
		$answer['order_by']     = $order_by;

		// Encode to json.
		echo wp_json_encode( $answer );

		// Terminate the script.
		die();
	}

	/**
	 * Ajax handler used to add and remove rows
	 *
	 * This method is called when in the "Tables" menu the value of the "Rows" field changes.
	 *
	 * @return void
	 */
	public function add_remove_rows() {

		// Preliminary operations.
		check_ajax_referer( 'daextletal', 'security' );
		$this->shared->check_tables_menu_capability();
		$this->shared->set_max_execution_time();
		$this->shared->raise_memory_limit();

		// Sanitization.
		$table_id                  = isset( $_POST['table_id'] ) ? intval( $_POST['table_id'], 10 ) : null;
		$current_number_of_rows    = isset( $_POST['current_number_of_rows'] ) ? intval( $_POST['current_number_of_rows'], 10 ) : null;
		$new_number_of_rows        = isset( $_POST['new_number_of_rows'] ) ? intval( $_POST['new_number_of_rows'], 10 ) : null;
		$current_number_of_columns = isset( $_POST['current_number_of_columns'] ) ? intval( $_POST['current_number_of_columns'], 10 ) : null;

		// Update the "data" db table ---------------------------------------------------------------------------------.

		if ( $new_number_of_rows > $current_number_of_rows ) {
			/*
			 * ---------------------------------------------------------------------------------------------------------
			 *
			 * Generate insert multirows query with placeholders:
			 *
			 *   INSERT INTO tbl_name
			 *      (col1,col2,col3)
			 *   VALUES
			 *      (1,2,3),
			 *      (4,5,6),
			 *      (7,8,9);
			 */

			// Create the first part of the insert multirows query.
			$values        = array();
			$place_holders = array();
			global $wpdb;
			$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . '_data';
			$query      = "INSERT INTO $table_name (table_id, row_index, content) VALUES ";

			// Add the rows -------------------------------------------------------------------------------------------.
			$row_difference = $new_number_of_rows - $current_number_of_rows;
			for ( $i = 1; $i <= $row_difference; $i++ ) {

				$row_index     = $current_number_of_rows + $i;
				$row_data      = array_fill( 0, $current_number_of_columns, 0 );
				$row_data_json = wp_json_encode( $row_data );

				// Prepare the values and the placeholders of the insert multirows query.
				array_push( $values, $table_id, $row_index, $row_data_json );
				$place_holders[] = "('%d', '%d', '%s')";

			}

			// Execute insert multirows query.
			$query   .= implode( ', ', $place_holders );
			$safe_sql = $wpdb->prepare( "$query ", $values ); //phpcs:ignore
			$wpdb->query( $safe_sql ); //phpcs:ignore

		} elseif ( $new_number_of_rows < $current_number_of_rows ) {

			$row_difference = $current_number_of_rows - $new_number_of_rows;

			/*
			 * ---------------------------------------------------------------------------------------------------------
			 *
			 * Generate delete multirows query with placeholders for the "data" table:
			 *
			 * DELETE FROM table WHERE (col1,col2) IN ((1,2),(3,4),(5,6))
			 */

			// Create the first part of the delete multirows query.
			global $wpdb;
			$table_name    = $wpdb->prefix . $this->shared->get( 'slug' ) . '_data';
			$query         = "DELETE FROM $table_name WHERE (table_id, row_index) IN ";
			$values        = array();
			$place_holders = array();

			// Add the values of the delete multirows query.
			for ( $i = 1; $i <= $row_difference; $i++ ) {
				array_push( $values, $table_id, $new_number_of_rows + $i );
				$place_holders[] = "('%d', '%d')";
			}

			// Execute the delete multirows query.
			$query   .= '(' . implode( ',', $place_holders ) . ')';
			$safe_sql = $wpdb->prepare( $query, $values ); //phpcs:ignore
			$wpdb->query( $safe_sql ); //phpcs:ignore

			/*
			 * ---------------------------------------------------------------------------------------------------------
			 *
			 * Generate delete multirows query with placeholders for the "cell" table:
			 *
			 * DELETE FROM table WHERE (col1,col2) IN ((1,2),(3,4),(5,6))
			 */

			// Create the first part of the delete multirows query.
			global $wpdb;
			$table_name    = $wpdb->prefix . $this->shared->get( 'slug' ) . '_cell';
			$query         = "DELETE FROM $table_name WHERE (table_id, row_index) IN ";
			$values        = array();
			$place_holders = array();

			// Add the values of the delete multirows query.
			for ( $i = 1; $i <= $row_difference; $i++ ) {
				array_push( $values, $table_id, $new_number_of_rows + $i );
				$place_holders[] = "('%d', '%d')";
			}

			// Execute the delete multirows query.
			$query   .= '(' . implode( ',', $place_holders ) . ')';
			$safe_sql = $wpdb->prepare( $query, $values ); //phpcs:ignore
			$wpdb->query( $safe_sql ); //phpcs:ignore

		}

		// Update the number of rows in the "table" db table ----------------------------------------------------------.
		$safe_sql = $wpdb->prepare( "UPDATE {$wpdb->prefix}daextletal_table SET `rows` = %d WHERE id = %d ", $new_number_of_rows, $table_id );
		$wpdb->query( $safe_sql ); //phpcs:ignore

		// Generate the response and terminate the script.
		echo 'success';
		die();
	}

	/**
	 * Ajax handler used to add and remove columns
	 *
	 * This method is called when in the "Tables" menu the value of the "Columns" field changes.
	 *
	 * @return void
	 */
	public function add_remove_columns() {

		// Preliminary operations.
		check_ajax_referer( 'daextletal', 'security' );
		$this->shared->check_tables_menu_capability();
		$this->shared->set_max_execution_time();
		$this->shared->raise_memory_limit();

		// Sanitization.
		$table_id              = isset( $_POST['table_id'] ) ? intval( $_POST['table_id'], 10 ) : null;
		$new_number_of_columns = isset( $_POST['new_number_of_columns'] ) ? intval( $_POST['new_number_of_columns'], 10 ) : null;

		// Update the "data" db table ---------------------------------------------------------------------------------.

		global $wpdb;
		$safe_sql = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}daextletal_data WHERE table_id = %d ORDER BY row_index ASC", $table_id );
		$results  = $wpdb->get_results( $safe_sql ); //phpcs:ignore

		// Parse through all the data with a foreach.
		foreach ( $results as $key => $result ) {

			$content_a = json_decode( $result->content );

			if ( $new_number_of_columns > count( $content_a ) ) {

				$content_a_count = count( $content_a );
				$difference      = $new_number_of_columns - $content_a_count;
				for ( $i = 1; $i <= $difference; $i++ ) {
					if ( 0 === $result->row_index ) {
						array_push( $content_a, 'Label ' . ( $i + $content_a_count ) );
					} else {
						array_push( $content_a, 0 );
					}
				}
			} elseif ( $new_number_of_columns < count( $content_a ) ) {

				$difference = count( $content_a ) - $new_number_of_columns;
				for ( $i = 1; $i <= $difference; $i++ ) {
					array_pop( $content_a );
				}
			}

			$content  = wp_json_encode( $content_a );
			$safe_sql = $wpdb->prepare( "UPDATE {$wpdb->prefix}daextletal_data SET content = %s WHERE id = %d ", $content, $result->id );
			$wpdb->query( $safe_sql ); //phpcs:ignore

		}

		// If the columns are removed delete the cell properties available in the "cell" db table ---------------------.

		// Get number of columns in table.
		$current_number_of_columns = $this->shared->get_columns_field( $table_id );
		if ( $new_number_of_columns < $current_number_of_columns ) {
			/*
			 * ---------------------------------------------------------------------------------------------------------
			 *
			 * Generate delete multirows query with placeholders for the "cell" table:
			 *
			 * DELETE FROM table WHERE (col1,col2) IN ((1,2),(3,4),(5,6))
			 */

			// Create the first part of the delete multirows query.
			global $wpdb;
			$table_name    = $wpdb->prefix . $this->shared->get( 'slug' ) . '_cell';
			$query         = "DELETE FROM $table_name WHERE (table_id, column_index) IN ";
			$values        = array();
			$place_holders = array();

			// Add the values of the delete multirows query.
			$column_difference = $current_number_of_columns - $new_number_of_columns;
			for ( $i = 1; $i <= $column_difference; $i++ ) {
				array_push( $values, $table_id, $new_number_of_columns - 1 + $i );
				$place_holders[] = "('%d', '%d')";
			}

			// Execute the delete multirows query.
			$query   .= '(' . implode( ',', $place_holders ) . ')';
			$safe_sql = $wpdb->prepare( $query, $values ); //phpcs:ignore
			$wpdb->query( $safe_sql ); //phpcs:ignore

		}

		// Update the number of columns in the "table" db table -------------------------------------------------------.
		$safe_sql = $wpdb->prepare( "UPDATE {$wpdb->prefix}daextletal_table SET columns = %d WHERE id = %d ", $new_number_of_columns, $table_id );
		$wpdb->query( $safe_sql ); //phpcs:ignore

		// Generate the response and terminate the script.
		echo 'success';
		die();
	}

	/**
	 * Ajax handler used to retrieve the cell properties of a cell as a JSON string.
	 *
	 * @return void
	 */
	public function retrieve_cell_properties() {

		// Preliminary operations.
		check_ajax_referer( 'daextletal', 'security' );
		$this->shared->check_tables_menu_capability();
		$this->shared->set_max_execution_time();
		$this->shared->raise_memory_limit();

		// Prepare data.
		$table_id = isset( $_POST['table_id'] ) ? intval( $_POST['table_id'], 10 ) : null;
		$row      = isset( $_POST['row'] ) ? intval( $_POST['row'], 10 ) : null;
		$column   = isset( $_POST['column'] ) ? intval( $_POST['column'], 10 ) : null;

		// If the data properties of the cell already exists retrieve the cell data properties.
		global $wpdb;
		$safe_sql = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}daextletal_cell WHERE table_id = %d AND row_index = %d AND column_index = %d", $table_id, $row, $column );
		$cell_obj = $wpdb->get_row( $safe_sql ); //phpcs:ignore

		if ( false === $cell_obj || null === $cell_obj ) {
			echo 'no properties';
			die();
		}

		// Generate the response and terminate the script.
		echo wp_json_encode( $this->shared->object_stripslashes( $cell_obj ) );
		die();
	}

	/**
	 * Ajax handler used to update or reset the cell properties.
	 *
	 * @return void
	 */
	public function update_reset_cell_properties() {

		// Preliminary operations.
		check_ajax_referer( 'daextletal', 'security' );
		$this->shared->check_tables_menu_capability();
		$this->shared->set_max_execution_time();
		$this->shared->raise_memory_limit();

		// Sanitization -----------------------------------------------------------------------------------------------.
		$task         = isset( $_POST['task'] ) ? sanitize_key( $_POST['task'] ) : null;
		$table_id     = isset( $_POST['table_id'] ) ? intval( $_POST['table_id'], 10 ) : null;
		$row_index    = isset( $_POST['row_index'] ) ? intval( $_POST['row_index'], 10 ) : null;
		$column_index = isset( $_POST['column_index'] ) ? intval( $_POST['column_index'], 10 ) : null;
		$link         = isset( $_POST['link'] ) ? esc_url_raw( wp_unslash( $_POST['link'] ) ) : null;
		$image_left   = isset( $_POST['image_left'] ) ? esc_url_raw( wp_unslash( $_POST['image_left'] ) ) : null;
		$image_right  = isset( $_POST['image_right'] ) ? esc_url_raw( wp_unslash( $_POST['image_right'] ) ) : null;

		switch ( $task ) {

			// Update cell properties ---------------------------------------------------------------------------------.
			case 'update-cell-properties':
				// Validation -----------------------------------------------------------------------------------------.

				// Init variables.
				$fields_with_errors_a = array();

				// Validate data --------------------------------------------------------------------------------------.

				if ( ( ! preg_match( $this->shared->url_regex, $link ) && strlen( trim( $link ) ) > 0 ) || strlen( trim( $link ) ) > 2083 ) {
					$fields_with_errors_a[] = 'link';
				}
				if ( ( ! preg_match( $this->shared->url_regex, $image_left ) && strlen( trim( $image_left ) ) > 0 ) || strlen( trim( $image_left ) ) > 2083 ) {
					$fields_with_errors_a[] = 'image_left';
				}
				if ( ( ! preg_match( $this->shared->url_regex, $image_right ) && strlen( trim( $image_right ) ) > 0 ) || strlen( trim( $image_right ) ) > 2083 ) {
					$fields_with_errors_a[] = 'image_right';
				}

				if ( count( $fields_with_errors_a ) > 0 ) {
					echo esc_html( 'Failed validation on the following fields: ' . implode( ', ', $fields_with_errors_a ) );
					die();
				}

				/**
				 * Prepare the $cell_info object that will be used by Daextletal_Shared::save_cell to save the cell
				 * properties.
				 */
				$cell_info               = new stdClass();
				$cell_info->table_id     = $table_id;
				$cell_info->row_index    = $row_index;
				$cell_info->column_index = $column_index;
				$cell_info->link         = $link;
				$cell_info->image_left   = $image_left;
				$cell_info->image_right  = $image_right;

				$this->shared->save_cell( $cell_info );
				break;

			// Delete cell properties ---------------------------------------------------------------------------------.
			case 'reset-cell-properties':
				global $wpdb;
				$safe_sql = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}daextletal_cell WHERE table_id = %d AND row_index = %d AND column_index = %d", $table_id, $row_index, $column_index );
				$result     = $wpdb->query( $safe_sql ); //phpcs:ignore

				if ( null === $result ) {
					echo 'Unable to delete the cell';
					die();
				}

				break;

		}

		// Generate the response and terminate the script.
		echo 'success';
		die();
	}

	/**
	 * Handles the AJAX request called in the JavaScript method used to handle clicks on the "Reset Cell Properties"
	 * context menu item.
	 */
	public function reset_cell_properties() {

		// Preliminary operations.
		check_ajax_referer( 'daextletal', 'security' );
		$this->shared->check_tables_menu_capability();
		$this->shared->set_max_execution_time();
		$this->shared->raise_memory_limit();

		// Sanitization.
		$table_id = isset( $_POST['table_id'] ) ? intval( $_POST['table_id'], 10 ) : null;
		$options  = isset( $_POST['options'] ) ? json_decode( sanitize_text_field( wp_unslash( $_POST['options'] ) ) ) : null;

		// Generate the cell indexes that should be removed.
		$cells_to_reset = array();
		for ( $i = $options->start->row; $i <= $options->end->row; $i++ ) {
			for ( $t = $options->start->col; $t <= $options->end->col; $t++ ) {
				$cells_to_reset[] = array( $i, $t );
			}
		}

		// Remove the cell properties.
		foreach ( $cells_to_reset as $key => $cell_to_reset ) {

			// Get the cell properties from the provided cells.
			global $wpdb;
			$safe_sql = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}daextletal_cell WHERE table_id = %d AND row_index = %d AND column_index = %d", $table_id, $cell_to_reset[0], $cell_to_reset[1] );
			$wpdb->query( $safe_sql ); //phpcs:ignore

		}

		// Encode to json and echo the response.
		echo wp_json_encode( 'done' );

		// Terminate the script.
		die();
	}


	/**
	 * The row index and column index of all the cells of the specificed table that has cell properties associated are
	 * echoed in json format.
	 *
	 * Handles the AJAX request called in the JavaScript method "refresh_cell_properties_highlight" and used to
	 * highlight the cells that have cell properties.
	 */
	public function get_cell_properties_index() {

		// Preliminary operations.
		check_ajax_referer( 'daextletal', 'security' );
		$this->shared->check_tables_menu_capability();
		$this->shared->set_max_execution_time();
		$this->shared->raise_memory_limit();

		// Sanitization.
		$table_id = isset( $_POST['table_id'] ) ? intval( $_POST['table_id'], 10 ) : null;

		global $wpdb;
		$safe_sql = $wpdb->prepare( "SELECT row_index, column_index FROM {$wpdb->prefix}daextletal_cell WHERE table_id = %d", $table_id );
		$cell_a     = $wpdb->get_results( $safe_sql ); //phpcs:ignore

		if ( null === $cell_a ) {
			echo 'no cell properties';
			die();
		}

		// Generate the response and terminate the script.
		echo wp_json_encode( $cell_a );
		die();
	}

	/**
	 * Handles the AJAX request called in the JavaScript method used to handle clicks on the "Insert Row Above" context
	 * menu item.
	 */
	public function insert_row_above() {

		// Preliminary operations.
		check_ajax_referer( 'daextletal', 'security' );
		$this->shared->check_tables_menu_capability();
		$this->shared->set_max_execution_time();
		$this->shared->raise_memory_limit();

		// Sanitization.
		$table_id  = isset( $_POST['table_id'] ) ? intval( $_POST['table_id'], 10 ) : null;
		$row_index = isset( $_POST['row'] ) ? intval( $_POST['row'], 10 ) : null;

		// Update the "table" menu ------------------------------------------------------------------------------------.
		global $wpdb;
		$safe_sql = $wpdb->prepare(
			"UPDATE {$wpdb->prefix}daextletal_table SET
            `rows` = `rows` + 1
            WHERE id = %d",
			$table_id
		);
		$wpdb->query( $safe_sql ); //phpcs:ignore

		// Count the number of subsequent rows.
		$safe_sql = $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->prefix}daextletal_data WHERE
			table_id = %d AND
	        row_index > %d",
			$table_id,
			$row_index
		);
		$count      = $wpdb->get_var( $safe_sql ); //phpcs:ignore

		// Update all the subsequent "row_index" in "data".
		for ( $i = $count + $row_index; $i >= $row_index; $i-- ) {

			// Update the row.
			$safe_sql = $wpdb->prepare(
				"UPDATE {$wpdb->prefix}daextletal_data SET
            row_index = row_index + 1
            WHERE table_id = %d AND row_index = %d",
				$table_id,
				$i
			);
			$wpdb->query( $safe_sql ); //phpcs:ignore

		}

		// Add the new row.
		$number_of_columns = $this->shared->get_columns_field( $table_id );
		$row_data          = array_fill( 0, $number_of_columns, '0' );
		$row_data_json     = wp_json_encode( $row_data );
		$safe_sql          = $wpdb->prepare(
			"INSERT INTO {$wpdb->prefix}daextletal_data SET
                            table_id = %d,
                            row_index = %d,
                            content = %s",
			$table_id,
			$row_index,
			$row_data_json
		);
		$wpdb->query( $safe_sql ); //phpcs:ignore

		// Update the "cell" menu -------------------------------------------------------------------------------------.

		// Update the row_index of the cell properties after the inserted row.
		$count = $this->shared->get_rows_field( $table_id );
		for ( $i = $count - 1; $i >= $row_index; $i-- ) {

			// Update the cell properties.
			$safe_sql = $wpdb->prepare(
				"UPDATE {$wpdb->prefix}daextletal_cell SET
            row_index = row_index + 1
            WHERE table_id = %d AND
            row_index = %d",
				$table_id,
				$i
			);
			$wpdb->query( $safe_sql ); //phpcs:ignore

		}

		// Generate the response and terminate the script.
		echo 'success';
		die();
	}

	/**
	 * Handles the AJAX request called in the JavaScript method used to handle clicks on the "Insert Row Below" context
	 * menu item.
	 */
	public function insert_row_below() {

		// Preliminary operations.
		check_ajax_referer( 'daextletal', 'security' );
		$this->shared->check_tables_menu_capability();
		$this->shared->set_max_execution_time();
		$this->shared->raise_memory_limit();

		// Sanitization.
		$table_id  = isset( $_POST['table_id'] ) ? intval( $_POST['table_id'], 10 ) : null;
		$row_index = isset( $_POST['row'] ) ? intval( $_POST['row'], 10 ) : null;

		// Update the "table" menu ------------------------------------------------------------------------------------.
		global $wpdb;
		$safe_sql = $wpdb->prepare(
			"UPDATE {$wpdb->prefix}daextletal_table SET
            `rows` = `rows` + 1
            WHERE id = %d",
			$table_id
		);
		$wpdb->query( $safe_sql ); //phpcs:ignore

		// Count the number of subsequent rows.
		$safe_sql = $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->prefix}daextletal_data WHERE
			table_id = %d AND
	        row_index > %d",
			$table_id,
			$row_index + 1
		);
		$count      = $wpdb->get_var( $safe_sql ); //phpcs:ignore

		// Update all the subsequent "row_index" in "data".
		for ( $i = $count + $row_index; $i >= $row_index; $i-- ) {

			// Update the row.
			$safe_sql = $wpdb->prepare(
				"UPDATE {$wpdb->prefix}daextletal_data SET
            row_index = row_index + 1
            WHERE table_id = %d AND row_index = %d",
				$table_id,
				$i + 1
			);
			$wpdb->query( $safe_sql ); //phpcs:ignore

		}

		// Add the new row.
		$number_of_columns = $this->shared->get_columns_field( $table_id );
		$row_data          = array_fill( 0, $number_of_columns, '0' );
		$row_data_json     = wp_json_encode( $row_data );
		$safe_sql          = $wpdb->prepare(
			"INSERT INTO {$wpdb->prefix}daextletal_data SET
                            table_id = %d,
                            row_index = %d,
                            content = %s",
			$table_id,
			$row_index + 1,
			$row_data_json
		);
		$wpdb->query( $safe_sql ); //phpcs:ignore

		// Update the "cell" menu -------------------------------------------------------------------------------------.

		// Update the row_index of the cell properties after the inserted row.
		$count = $this->shared->get_rows_field( $table_id );
		for ( $i = $count - 1; $i > $row_index; $i-- ) {

			// Update the cell properties.
			$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . '_cell';
			$safe_sql   = $wpdb->prepare(
				"UPDATE {$wpdb->prefix}daextletal_cell SET
            row_index = row_index + 1
            WHERE table_id = %d AND
            row_index = %d",
				$table_id,
				$i
			);
			$wpdb->query( $safe_sql ); //phpcs:ignore

		}

		// Generate the response and terminate the script.
		echo 'success';
		die();
	}

	/**
	 * Handles the AJAX request called in the JavaScript method used to handle clicks on the "Insert Column Left"
	 * context menu item.
	 */
	public function insert_column_left() {

		// Preliminary operations.
		check_ajax_referer( 'daextletal', 'security' );
		$this->shared->check_tables_menu_capability();
		$this->shared->set_max_execution_time();
		$this->shared->raise_memory_limit();

		// Sanitization.
		$table_id     = isset( $_POST['table_id'] ) ? intval( $_POST['table_id'], 10 ) : null;
		$column_index = isset( $_POST['column'] ) ? intval( $_POST['column'], 10 ) : null;

		// Update the "table" menu ------------------------------------------------------------------------------------.
		global $wpdb;
		$safe_sql = $wpdb->prepare(
			"UPDATE {$wpdb->prefix}daextletal_table SET
            columns = columns + 1
            WHERE id = %d",
			$table_id
		);
		$wpdb->query( $safe_sql ); //phpcs:ignore

		// Update the "data" database table ---------------------------------------------------------------------------.
		global $wpdb;
		$safe_sql = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}daextletal_data WHERE table_id = %d ORDER BY row_index ASC", $table_id );
		$results  = $wpdb->get_results( $safe_sql ); //phpcs:ignore

		// Parse through all the data with a foreach.
		foreach ( $results as $key => $result ) {

			$content_a = json_decode( $result->content );

			if ( 0 === $key ) {
				array_splice( $content_a, $column_index, 0, 'New Label' );
			} else {
				array_splice( $content_a, $column_index, 0, '0' );
			}

			$content  = wp_json_encode( $content_a );
			$safe_sql = $wpdb->prepare( "UPDATE {$wpdb->prefix}daextletal_data SET content = %s WHERE id = %d ", $content, $result->id );
			$wpdb->query( $safe_sql ); //phpcs:ignore

		}

		// Update the "cell" menu -------------------------------------------------------------------------------------.

		// Update the column_index of the cell properties after the inserted column.
		$count = $this->shared->get_columns_field( $table_id );
		for ( $i = $count - 2; $i >= $column_index; $i-- ) {

			// update the cell properties.
			$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . '_cell';
			$safe_sql   = $wpdb->prepare(
				"UPDATE {$wpdb->prefix}daextletal_cell SET
            column_index = column_index + 1
            WHERE table_id = %d AND
            column_index = %d",
				$table_id,
				$i
			);
			$wpdb->query( $safe_sql ); //phpcs:ignore

		}

		// Generate the response and terminate the script.
		echo 'success';
		die();
	}

	/**
	 * Handles the AJAX request called in the JavaScript method used to handle clicks on the "Insert Column Right"
	 * context menu item.
	 */
	public function insert_column_right() {

		// Preliminary operations.
		check_ajax_referer( 'daextletal', 'security' );
		$this->shared->check_tables_menu_capability();
		$this->shared->set_max_execution_time();
		$this->shared->raise_memory_limit();

		// Sanitization.
		$table_id     = isset( $_POST['table_id'] ) ? intval( $_POST['table_id'], 10 ) : null;
		$column_index = isset( $_POST['column'] ) ? intval( $_POST['column'], 10 ) : null;

		// Update the "table" menu ------------------------------------------------------------------------------------.
		global $wpdb;
		$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . '_table';
		$safe_sql   = $wpdb->prepare(
			"UPDATE {$wpdb->prefix}daextletal_table SET
            columns = columns + 1
            WHERE id = %d",
			$table_id
		);
		$wpdb->query( $safe_sql ); //phpcs:ignore

		// Update the "data" database table ---------------------------------------------------------------------------.
		global $wpdb;
		$safe_sql = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}daextletal_data WHERE table_id = %d ORDER BY row_index ASC", $table_id );
		$results    = $wpdb->get_results( $safe_sql ); //phpcs:ignore

		// Parse through all the data with a foreach.
		foreach ( $results as $key => $result ) {

			$content_a = json_decode( $result->content );

			if ( 0 === $key ) {
				array_splice( $content_a, $column_index + 1, 0, 'New Label' );
			} else {
				array_splice( $content_a, $column_index + 1, 0, '0' );
			}

			$content  = wp_json_encode( $content_a );
			$safe_sql = $wpdb->prepare( "UPDATE {$wpdb->prefix}daextletal_data SET content = %s WHERE id = %d ", $content, $result->id );
			$wpdb->query( $safe_sql ); //phpcs:ignore

		}

		// Update the "cell" menu -------------------------------------------------------------------------------------.

		// Update the column_index of the cell properties after the inserted column.
		$count = $this->shared->get_columns_field( $table_id );
		for ( $i = $count - 2; $i > $column_index; $i-- ) {

			// update the cell properties.
			$safe_sql = $wpdb->prepare(
				"UPDATE {$wpdb->prefix}daextletal_cell SET
            column_index = column_index + 1
            WHERE table_id = %d AND
            column_index = %d",
				$table_id,
				$i
			);
			$wpdb->query( $safe_sql ); //phpcs:ignore

		}

		// Generate the response and terminate the script.
		echo 'success';
		die();
	}

	/**
	 * Handles the AJAX request called in the JavaScript method used to handle clicks on the "Remove Row" context menu
	 * item.
	 */
	public function remove_row() {

		// Preliminary operations.
		check_ajax_referer( 'daextletal', 'security' );
		$this->shared->check_tables_menu_capability();
		$this->shared->set_max_execution_time();
		$this->shared->raise_memory_limit();

		// Sanitization.
		$table_id  = isset( $_POST['table_id'] ) ? intval( $_POST['table_id'], 10 ) : null;
		$row_index = isset( $_POST['row'] ) ? intval( $_POST['row'], 10 ) : null;

		// Update the "table" database table --------------------------------------------------------------------------.
		global $wpdb;
		$safe_sql = $wpdb->prepare(
			"UPDATE {$wpdb->prefix}daextletal_table SET
            `rows` = `rows` - 1
            WHERE id = %d",
			$table_id
		);
		$wpdb->query( $safe_sql ); //phpcs:ignore

		// Update the "data" database table ---------------------------------------------------------------------------.
		$safe_sql = $wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}daextletal_data WHERE table_id = %d AND row_index = %d",
			$table_id,
			$row_index
		);
		$wpdb->query( $safe_sql ); //phpcs:ignore

		/**
		 * Regenerate the "row_index" value for all the records where it's required.
		 *
		 * The procedure starts from the first index that require to be regenerated.
		 *
		 * 1 - Get the total number of records in "data"
		 * 2 - With for cycle from the first index to the last index regenerate the "row_index" field
		 */
		$safe_sql = $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->prefix}daextletal_data WHERE
			table_id = %d AND
	        row_index > %d",
			$table_id,
			$row_index
		);
		$count      = $wpdb->get_var( $safe_sql ); //phpcs:ignore

		for ( $i = $row_index; $i < $count + $row_index; $i++ ) {

			// update the row.
			$safe_sql = $wpdb->prepare(
				"UPDATE {$wpdb->prefix}daextletal_data SET
            row_index = row_index -1
            WHERE table_id = %d AND row_index =%d",
				$table_id,
				$i + 1
			);
			$wpdb->query( $safe_sql ); //phpcs:ignore

		}

		// Update the "cell" menu -------------------------------------------------------------------------------------.

		// Delete the cell properties associated with the delete row.
		$safe_sql = $wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}daextletal_cell WHERE table_id = %d AND row_index = %d",
			$table_id,
			$row_index
		);
		$wpdb->query( $safe_sql ); //phpcs:ignore

		// Update the row_index of the cell properties after the delete row.
		for ( $i = $row_index; $i < $count + $row_index; $i++ ) {

			// Update the cell properties.
			$safe_sql = $wpdb->prepare(
				"UPDATE {$wpdb->prefix}daextletal_cell SET
            row_index = row_index - 1
            WHERE table_id = %d AND
            row_index = %d",
				$table_id,
				$i + 1
			);
			$wpdb->query( $safe_sql ); //phpcs:ignore

		}

		// Generate the response and terminate the script.
		echo 'success';
		die();
	}

	/**
	 * Handles the AJAX request called in the JavaScript method used to handle clicks on the "Remove Column" context
	 * menu item.
	 */
	public function remove_column() {

		// Preliminary operations.
		check_ajax_referer( 'daextletal', 'security' );
		$this->shared->check_tables_menu_capability();
		$this->shared->set_max_execution_time();
		$this->shared->raise_memory_limit();

		// Sanitization.
		$table_id     = isset( $_POST['table_id'] ) ? intval( $_POST['table_id'], 10 ) : null;
		$column_index = isset( $_POST['column'] ) ? intval( $_POST['column'], 10 ) : null;

		// Update the "table" database table --------------------------------------------------------------------------.
		global $wpdb;
		$safe_sql = $wpdb->prepare(
			"UPDATE {$wpdb->prefix}daextletal_table SET
            columns = columns - 1
            WHERE id = %d",
			$table_id
		);
		$wpdb->query( $safe_sql ); //phpcs:ignore

		// Update the "data" database table ---------------------------------------------------------------------------.

		// Cycle through the records and remove the specified column.
		$safe_sql = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}daextletal_data WHERE table_id = %d ORDER BY row_index ASC", $table_id );
		$data_a     = $wpdb->get_results( $safe_sql ); //phpcs:ignore

		// Load the data in an array.
		foreach ( $data_a as $key => $data ) {

			$content_a = json_decode( $data->content );

			// Remove the specified row from the array.
			unset( $content_a[ $column_index ] );
			$content_a = array_values( $content_a );

			// Update the record of the database table.
			$safe_sql = $wpdb->prepare(
				"UPDATE {$wpdb->prefix}daextletal_data SET content = %s WHERE id = %d",
				wp_json_encode( $content_a ),
				$data->id
			);
			$wpdb->query( $safe_sql ); //phpcs:ignore

		}

		// Update the "cell" menu -------------------------------------------------------------------------------------.

		// Delete the cell properties associated with the delete column.
		$safe_sql = $wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}daextletal_cell WHERE table_id = %d AND column_index = %d",
			$table_id,
			$column_index
		);
		$wpdb->query( $safe_sql ); //phpcs:ignore

		// Get the value of the "columns" field because will be used as the counter in the next step.
		$count = $this->shared->get_columns_field( $table_id );

		// Update the column_index of the cell properties after the delete column.
		for ( $i = $column_index; $i < $count + $column_index - 1; $i++ ) {

			// Update the cell properties.
			$safe_sql = $wpdb->prepare(
				"UPDATE {$wpdb->prefix}daextletal_cell SET
            column_index = column_index - 1
            WHERE table_id = %d AND
            column_index = %d",
				$table_id,
				$i + 1
			);
			$wpdb->query( $safe_sql ); //phpcs:ignore

		}

		// Generate the response and terminate the script.
		echo 'success';
		die();
	}
}
