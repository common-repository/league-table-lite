<?php
/**
 * This class should be used to stores properties and methods shared by the
 * admin and public side of WordPress.
 *
 * @package league-table-lite
 */

/**
 * This class should be used to stores properties and methods shared by the
 * admin and public side of WordPress.
 */
class Daextletal_Shared {

	/**
	 * Regex WordPress capability patter.
	 *
	 * @var string
	 */
	public $regex_capability = '/^\s*[A-Za-z0-9_]+\s*$/';

	/**
	 * Regex list of comma separated numbers pattern.
	 *
	 * @var string
	 */
	public $list_of_comma_separated_numbers = '/^(\s*(\d+\s*,\s*)+\d+\s*|\s*\d+\s*)$/';

	/**
	 * Regex URL pattern.
	 *
	 * @var string
	 */
	public $url_regex = '/^https?:\/\/.+$/';

	/**
	 * Regex hex RGB pattern.
	 *
	 * @var string
	 */
	public $hex_rgb_regex = '/^#(?:[0-9a-fA-F]{3}){1,2}$/';

	/**
	 * Regex font family pattern.
	 *
	 * @var string
	 */
	public $font_family_regex = '/^([A-Za-z0-9-\'", ]*)$/';

	/**
	 * Regex digits pattern.
	 *
	 * @var string
	 */
	public $digits_regex = '/^\s*\d+\s*$/';

	/**
	 * Single instance of the class.
	 *
	 * @var string
	 */
	protected static $instance = null;

	/**
	 * The data of the class.
	 *
	 * @var array
	 */
	private $data = array();

	/**
	 * The constructor of the class.
	 */
	private function __construct() {

		// Set plugin textdomain.
		load_plugin_textdomain( 'league-table-lite', false, 'league-table-lite/lang/' );

		$this->data['slug'] = 'daextletal';
		$this->data['ver']  = '1.17';
		$this->data['dir']  = substr( plugin_dir_path( __FILE__ ), 0, -7 );
		$this->data['url']  = substr( plugin_dir_url( __FILE__ ), 0, -7 );

		// Here are stored the plugin option with the related default values.
		$this->data['options'] = array(

			// Database Version ---------------------------------------------------------------------------------------.
			$this->get( 'slug' ) . '_database_version'   => '0',

			// Options Version ----------------------------------------------------------------------------------------.
			$this->get( 'slug' ) . '_options_version'    => '0',

			// General ------------------------------------------------------------------------------------------------.
			$this->get( 'slug' ) . '_tables_menu_capability' => 'manage_options',
			$this->get( 'slug' ) . '_general_javascript_file_url' => $this->get( 'url' ) . 'public/assets/js/general.min.js',
			$this->get( 'slug' ) . '_general_stylesheet_file_url' => $this->get( 'url' ) . 'public/assets/css/general.min.css',
			$this->get( 'slug' ) . '_tablesorter_library_url' => $this->get( 'url' ) . 'public/assets/js/tablesorter/jquery.tablesorter-min.js',
			$this->get( 'slug' ) . '_load_google_font_1' => 'https://fonts.googleapis.com/css2?family=Open+Sans&display=swap',
			$this->get( 'slug' ) . '_load_google_font_2' => '',
			$this->get( 'slug' ) . '_max_execution_time' => '300',
			$this->get( 'slug' ) . '_limit_shortcode_parsing' => '1',
			$this->get( 'slug' ) . '_verify_single_shortcode' => '1',
			$this->get( 'slug' ) . '_widget_text_shortcode' => '0',

		);
	}

	/**
	 * Get the instance of the class.
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
	 * Retrieve data.
	 *
	 * @param string $index The id of the data to retrieve.
	 *
	 * @return mixed
	 */
	public function get( $index ) {
		return $this->data[ $index ];
	}

	/**
	 * Create a record of the data table filled with the provided data
	 *
	 * @param int    $table_id The id of the table.
	 * @param int    $row_index The index of the data structure row.
	 * @param string $row_data_json The data of a single data structure row in the json format.
	 *
	 * @return void
	 */
	public function data_insert_record( $table_id, $row_index, $row_data_json ) {

		// Save in the db table.
		global $wpdb;
		$safe_sql = $wpdb->prepare(
			"INSERT INTO {$wpdb->prefix}daextletal_data SET
            table_id = %d,
            row_index = %d,
            content = %s",
			$table_id,
			$row_index,
			$row_data_json
		);

		$query_result = $wpdb->query( $safe_sql ); // phpcs:ignore
	}

	/**
	 * Applies stripslashes to all the properties of an object
	 *
	 * @param object $obj The object to be stripslashed.
	 *
	 * @return mixed
	 */
	public function object_stripslashes( $obj ) {

		$property_a = get_object_vars( $obj );

		foreach ( $property_a as $key => $value ) {

			$obj->{$key} = stripslashes( $value );

		}

		return $obj;
	}

	/**
	 * If the data of the cell exists updates the data based on the provided $cell_info Object.
	 *  If the data of the cell doesn't exist creates a new record based on the provided $cell_info Object.
	 *
	 * @param object $cell_info An object that contains the properties of the cell.
	 *
	 * @return bool|int|mysqli_result|null The result of the query
	 */
	public function save_cell( $cell_info ) {

		// Verifies if the cell already exists.
		global $wpdb;
		$safe_sql   = $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->prefix}daextletal_cell WHERE
        table_id = %d AND row_index = %d AND column_index = %d",
			$cell_info->table_id,
			$cell_info->row_index,
			$cell_info->column_index
		);
		$cell_count = $wpdb->get_var( $safe_sql ); // phpcs:ignore

		if ( $cell_count > 0 ) {

			// Update the cell data properties.
			global $wpdb;
			$table_name = $wpdb->prefix . $this->get( 'slug' ) . '_cell';
			$safe_sql   = $wpdb->prepare(
				"UPDATE {$wpdb->prefix}daextletal_cell SET
            link = %s,
            image_left = %s,
            image_right = %s
            WHERE table_id = %d AND row_index = %d AND column_index = %d",
				$cell_info->link,
				$cell_info->image_left,
				$cell_info->image_right,
				$cell_info->table_id,
				$cell_info->row_index,
				$cell_info->column_index
			);
			$result     = $wpdb->query( $safe_sql ); // phpcs:ignore

		} else {

			// If the properties of the cell don't exist create the cell data properties.
			global $wpdb;
			$table_name = $wpdb->prefix . $this->get( 'slug' ) . '_cell';
			$safe_sql   = $wpdb->prepare(
				"INSERT INTO {$wpdb->prefix}daextletal_cell SET
            link = %s,
            image_left = %s,
            image_right = %s,
            table_id = %d,
            row_index = %d,
            column_index = %d",
				$cell_info->link,
				$cell_info->image_left,
				$cell_info->image_right,
				$cell_info->table_id,
				$cell_info->row_index,
				$cell_info->column_index
			);
			$result     = $wpdb->query( $safe_sql ); // phpcs:ignore

		}

		return $result;
	}

	/**
	 * Returns the total number of non-temporary tables.
	 *
	 * @return string|null The number of non-temporary tables
	 */
	public function get_number_of_tables() {

		global $wpdb;
		$safe_sql         = "SELECT COUNT(*) FROM {$wpdb->prefix}daextletal_table WHERE temporary = 0";
		$number_of_tables = $wpdb->get_var( $safe_sql ); // phpcs:ignore

		return $number_of_tables;
	}

	/**
	 * Get the number of columns available in the table.
	 *
	 * @param int  $table_id The id of the table.
	 * @param bool $consider_position_column A boolean value which indicated if the position column, generated in the front-end via JavaScript,
	 *  should be considered in the calculation of the number of columns.
	 *
	 * @return int The number of columns available in the table.
	 */
	public function get_number_of_columns( $table_id, $consider_position_column ) {

		global $wpdb;
		$number_of_columns = 0;

		// Sum the number of columns found in the serialized field "content" of the data table to $number_of_columns.
		$safe_sql = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}daextletal_data WHERE table_id = %d ORDER BY row_index DESC", $table_id );
		$data_a     = $wpdb->get_results( $safe_sql ); // phpcs:ignore

		$row_data          = json_decode( $data_a[0]->content, true );
		$number_of_columns = $number_of_columns + count( $row_data );

		/**
		 * Add 1 to $number_of_columns if:
		 *
		 *  - The position column should be considered
		 *  - If the position column is enabled in this table
		 */
		$table_name = $wpdb->prefix . $this->get( 'slug' ) . '_table';
		$safe_sql   = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}daextletal_table WHERE id = %d", $table_id );
		$table_obj  = $wpdb->get_row( $safe_sql ); // phpcs:ignore

		if ( intval( $consider_position_column, 10 ) === 1 && intval( $table_obj->show_position, 10 ) === 1 ) {
			$number_of_columns = $number_of_columns++;
		}

		return $number_of_columns;
	}

	/**
	 * Returns the value of the "rows" field of the "table" db table
	 *
	 * @param int $table_id The table id.
	 * @return Int the value of the "rows" field
	 */
	public function get_rows_field( $table_id ) {

		global $wpdb;
		$safe_sql  = $wpdb->prepare( "SELECT `rows` FROM {$wpdb->prefix}daextletal_table WHERE id = %d", $table_id );
		$table_obj  = $wpdb->get_row( $safe_sql ); // phpcs:ignore

		return intval( $table_obj->rows, 10 );
	}

	/**
	 * Returns the value of the "columns" field of the "table" db table
	 *
	 * @param int $table_id The table id.
	 * @return Int the value of the "columns" field
	 */
	public function get_columns_field( $table_id ) {

		global $wpdb;
		$safe_sql  = $wpdb->prepare( "SELECT columns FROM {$wpdb->prefix}daextletal_table WHERE id = %d", $table_id );
		$table_obj  = $wpdb->get_row( $safe_sql ); // phpcs:ignore

		return intval( $table_obj->columns, 10 );
	}

	/**
	 * Check the tables menu capability.
	 */
	public function check_tables_menu_capability() {

		// Check the capability.
		if ( ! current_user_can( get_option( $this->get( 'slug' ) . '_tables_menu_capability' ) ) ) {
			echo 'Invalid Capability';
			die();
		}
	}

	/**
	 * Set the maximum execution time defined in the "Max Execution Time" option.
	 */
	public function set_max_execution_time() {
		set_time_limit( intval( get_option( $this->get( 'slug' ) . '_max_execution_time' ), 10 ) );
	}

	/**
	 * Raise the memory limit.
	 */
	public function raise_memory_limit() {
		wp_raise_memory_limit();
	}

	/**
	 * Sanitize the data of the table provided as an escaped json string.
	 *
	 * @param string $table_data The table data provided as an escaped json string.
	 *
	 * @return array|bool
	 */
	public function sanitize_table_data( $table_data ) {

		// Unescape and decode the table data provided in json format.
		$table_data = json_decode( stripslashes( $table_data ) );

		// Verify if data property of the returned object is an array.
		if ( ! isset( $table_data->data ) || ! is_array( $table_data->data ) ) {
			return false;
		}

		// Save the two-dimensional array that include the table data in the $table_data variable.
		$table_data = $table_data->data;

		foreach ( $table_data as $row_index => $row_data ) {

			// Verify if the table row data are provided as an array.
			if ( ! is_array( $row_data ) ) {
				return false;
			}

			// Sanitize all the cells data in the $row_data array.
			$table_data[ $row_index ] = array_map( 'sanitize_text_field', $row_data );

		}

		return $table_data;
	}

	/**
	 * Provided the db table name and the primary_key name, and the primary_key value of the record to duplicate, the method will duplicate the record in the
	 * database. Note that this method is generic and should work with any database table.
	 *
	 * @param string $table_name The database table name.
	 * @param string $primary_key_name The primary key name.
	 * @param string $primary_key_value The primary key value of the record to duplicate.
	 *
	 * @return array void The result of the query.
	 */
	public function duplicate_record( $table_name, $primary_key_name, $primary_key_value ) {

		global $wpdb;

		// Retrieve the record to duplicate.
		$safe_sql = $wpdb->prepare(
			'SELECT * FROM %i WHERE %i = %d', // phpcs:ignore
			$table_name,
			$primary_key_name,
			$primary_key_value
		);
		$record   = $wpdb->get_row( $safe_sql, ARRAY_A ); // phpcs:ignore

		// Remove the primary key from the record.
		unset( $record[ $primary_key_name ] );

		// Insert the record into the database.
		$query_result = $wpdb->insert( $table_name, $record ); // phpcs:ignore

		return array(
			'query_result'     => $query_result,
			'last_inserted_id' => $wpdb->insert_id,
		);
	}
}
