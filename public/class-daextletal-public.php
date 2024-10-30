<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @package league-table-lite
 */

/**
 * This class should be used to work with the public side of WordPress.
 */
class Daextletal_Public {

	/**
	 * The instance of this class.
	 *
	 * @var null
	 */
	protected static $instance = null;

	/**
	 * The instance of the plugin info.
	 *
	 * @var Daextletal_Shared|string|null
	 */
	private $shared = null;

	/**
	 * Store all the tables included in the current post/page.
	 *
	 * @var array An array which includes all the tables included in the current post/page.
	 */
	private $tables = null;

	/**
	 * Store all the shortcode IDs used in the post/page.
	 *
	 * @var array An array which includes the id of the shortcode which has been used in the current post.
	 */
	private static $shortcode_id_a = array();

	/**
	 * Constructor.
	 */
	private function __construct() {

		// Assign an instance of the plugin info.
		$this->shared = Daextletal_Shared::get_instance();

		// ltl shortcode.
		add_shortcode( 'ltl', array( $this, 'display_league_table' ) );

		// Enable shortcodes in text widgets if the related option is enabled.
		if ( 1 === intval( get_option( $this->shared->get( 'slug' ) . '_widget_text_shortcode' ), 10 ) ) {
			add_filter( 'widget_text', 'do_shortcode' );
		}

		// Load public css.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );

		// Load public js.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue styles.
	 */
	public function enqueue_styles() {

		// Enqueue Google Font 1.
		if ( strlen( trim( get_option( $this->shared->get( 'slug' ) . '_load_google_font_1' ) ) ) > 0 ) {
			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-google-font-1',
				esc_url( get_option( $this->shared->get( 'slug' ) . '_load_google_font_1' ) ),
				false,
				$this->shared->get( 'ver' )
			);
		}

		// Enqueue Google Font 2.
		if ( strlen( trim( get_option( $this->shared->get( 'slug' ) . '_load_google_font_2' ) ) ) > 0 ) {
			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-google-font-2',
				esc_url( get_option( $this->shared->get( 'slug' ) . '_load_google_font_2' ) ),
				false,
				$this->shared->get( 'ver' )
			);
		}

		// Enqueue the main stylesheet.
		wp_enqueue_style( $this->shared->get( 'slug' ) . '-general', esc_url( get_option( $this->shared->get( 'slug' ) . '_general_stylesheet_file_url' ) ), array(), $this->shared->get( 'ver' ) );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->shared->get( 'slug' ) . '-tablesorter', esc_url( get_option( $this->shared->get( 'slug' ) . '_tablesorter_library_url' ) ), array( 'jquery' ), $this->shared->get( 'ver' ), true );
		wp_enqueue_script( $this->shared->get( 'slug' ) . '-general', esc_url( get_option( $this->shared->get( 'slug' ) . '_general_javascript_file_url' ) ), array( 'jquery' ), $this->shared->get( 'ver' ), true );
	}

	/**
	 * Create an instance of this class.
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
	 * [ltl] shortcode callback.
	 *
	 * @param array $atts The attributes of the shortcode.
	 *
	 * @return false|string|void
	 */
	public function display_league_table( $atts ) {

		/**
		 * Parse the shortcode only inside the full content of posts and pages if the "Limit Shortcode Parsing" option
		 * is enabled. Do not parse the shortcode inside feeds.
		 */
		if ( ! is_feed() && ( ( is_single() || is_page() ) || 0 === intval( get_option( $this->shared->get( 'slug' ) . '_limit_shortcode_parsing' ), 10 ) ) ) {

			// Get the table id.
			if ( isset( $atts['id'] ) ) {
				$table_id = intval( $atts['id'], 10 );
			} else {
				return '<p>' . esc_attr__( 'Please enter the identifier of the table.', 'league-table-lite' ) . '</p>';
			}

			// Get table object.
			global $wpdb;
			$safe_sql  = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}daextletal_table WHERE id = %d AND temporary = 0", $table_id );
			$table_obj  = $wpdb->get_row( $safe_sql ); // phpcs:ignore

			// Terminate if there is no table with the specified id.
			if ( null === $table_obj ) {
				return '<p>' . esc_attr__( 'There is no table associated with this shortcode.', 'league-table-lite' ) . '</p>';
			}

			// Terminate if this table id has already been used.
			if ( intval( get_option( $this->shared->get( 'slug' ) . '_verify_single_shortcode' ), 10 ) === 1 &&
				in_array( $table_id, self::$shortcode_id_a, true ) ) {
				return '<p>' . esc_attr__( "You can't use multiple times the same shortcode.", 'league-table-lite' ) . '</p>';
			}

			// Store the shortcode id.
			self::$shortcode_id_a[] = $table_id;

			// The tables property saves all the tables included in this post.
			$this->tables[] = $table_obj;

			// Init $table_cell_properties.
			$table_cells_properties = null;

			// Generate output ----------------------------------------------------------------------------------------.

			// Get table data.
			global $wpdb;
			$safe_sql = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}daextletal_data WHERE table_id = %d ORDER BY row_index ASC", $table_id );
			$results    = $wpdb->get_results( $safe_sql, ARRAY_A ); // phpcs:ignore

			if ( 1 === intval( $table_obj->enable_cell_properties, 10 ) ) {

				// Get the cell properties of all the cell of the table.
				global $wpdb;
				$safe_sql               = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}daextletal_cell WHERE table_id = %d", $table_id );
				$table_cells_properties = $wpdb->get_results( $safe_sql, ARRAY_A ); // phpcs:ignore

			}

			// Turn on output buffer.
			ob_start();

			?>

			<!-- Generate the "table" HTML element -------------------------------------------------------------------->
			<table id="daextletal-table-<?php echo esc_attr( $table_id ); ?>"
					class="daextletal-table" <?php $this->generate_table_data_attributes( $table_obj ); ?>>

				<?php

				// Generate the "colgroup" HTML element ---------------------------------------------------------------.
				if ( 1 === intval( $table_obj->column_width, 10 ) ) {

					// Extract the numeric values in column width value in an array.
					$column_width_value_a = explode( ',', preg_replace( '/\s+/', '', $table_obj->column_width_value ) );

					// If column width value doesn't include any numeric value do nothing.
					if ( count( $column_width_value_a ) > 0 ) {

						if ( count( $column_width_value_a ) === 1 ) {

							// If column width value is a single value apply the value to all the columns.
							$number_of_columns = $this->shared->get_number_of_columns( $table_id, true );
							echo '<colgroup>';
							for ( $i = 0; $i < $number_of_columns; $i++ ) {
								echo '<col style="width: ' . intval( $column_width_value_a[0], 10 ) . 'px;">';
							}
							echo '</colgroup>';

						} else {

							// If column width value are multiple values apply the various values to the columns.
							echo '<colgroup>';
							foreach ( $column_width_value_a as $key => $column_width ) {
								echo '<col style="width: ' . intval( $column_width, 10 ) . 'px;">';
							}
							echo '</colgroup>';

						}
					}
				}

				?>

				<!-- Generate the "thead" HTML element ---------------------------------------------------------------->
				<thead>
				<tr>
					<?php

					foreach ( $results as $key1 => $value ) {
						if ( $key1 > 0 ) {
							break;
						}
						$row_data = json_decode( $value['content'], true );

						foreach ( $row_data as $key2 => $cell_data ) {

							$this->echo_cell_data_th( $table_obj, $table_cells_properties, $cell_data, $key1, $key2 );

						}
					}

					?>
				</tr>
				</thead>

				<!-- Generate the "tbody" HTML element ---------------------------------------------------------------->
				<tbody>

				<?php

				foreach ( $results as $key1 => $value ) {
					if ( 0 === $key1 ) {
						continue;
					}
					$row_data = json_decode( $value['content'], true );
					echo '<tr>';
					foreach ( $row_data as $key2 => $cell_data ) {

						$this->echo_cell_data_td( $table_obj, $table_cells_properties, $cell_data, $key1, $key2 );

					}
					echo '</tr>';
				}

				?>


				</tbody>

			</table>

			<?php

			$out = ob_get_clean();

			// If the container is enabled include the table in a container.
			if ( 1 === intval( $table_obj->enable_container, 10 ) ) {
				$out = '<div class="daextletal-table-container">' . $out . '</div>';
			}

			return $out;

		}
	}

	/**
	 * Generates the data attributes placed in the starting "table" tag of the table.
	 *
	 * @param object $table_obj An object with all the properties of the table.
	 *
	 * @return void
	 */
	public function generate_table_data_attributes( $table_obj ) {

		// Sorting options.
		echo 'data-enable-sorting="' . intval( $table_obj->enable_sorting, 10 ) . '" ';
		echo 'data-enable-manual-sorting="' . intval( $table_obj->enable_manual_sorting, 10 ) . '" ';
		echo 'data-show-position="' . intval( $table_obj->show_position, 10 ) . '" ';
		echo 'data-position-side="' . esc_attr( stripslashes( $table_obj->position_side ) ) . '" ';
		echo 'data-position-label="' . esc_attr( stripslashes( $table_obj->position_label ) ) . '" ';
		echo 'data-number-format="' . intval( $table_obj->number_format, 10 ) . '" ';
		echo 'data-order-desc-asc="' . intval( $table_obj->order_desc_asc, 10 ) . '" ';
		echo 'data-order-by="' . intval( $table_obj->order_by, 10 ) . '" ';
		echo 'data-order-data-type="' . esc_attr( stripslashes( $table_obj->order_data_type ) ) . '" ';
		echo 'data-order-date-format="' . esc_attr( stripslashes( $table_obj->order_date_format ) ) . '" ';

		// Style options.
		echo 'data-table-layout="' . intval( $table_obj->table_layout, 10 ) . '" ';
		echo 'data-table-width="' . intval( $table_obj->table_width, 10 ) . '" ';
		echo 'data-table-width-value="' . intval( $table_obj->table_width_value, 10 ) . '" ';
		echo 'data-table-minimum-width="' . intval( $table_obj->table_minimum_width, 10 ) . '" ';
		echo 'data-table-margin-top="' . intval( $table_obj->table_margin_top, 10 ) . '" ';
		echo 'data-table-margin-bottom="' . intval( $table_obj->table_margin_bottom, 10 ) . '" ';
		echo 'data-enable-container="' . intval( $table_obj->enable_container, 10 ) . '" ';
		echo 'data-container-width="' . intval( $table_obj->container_width, 10 ) . '" ';
		echo 'data-container-height="' . intval( $table_obj->container_height, 10 ) . '" ';
		echo 'data-show-header="' . intval( $table_obj->show_header, 10 ) . '" ';
		echo 'data-header-font-size="' . intval( $table_obj->header_font_size, 10 ) . '" ';
		$this->echo_font_family_html_attribute( 'data-header-font-family', $table_obj->header_font_family );
		echo 'data-header-font-weight="' . esc_attr( stripslashes( $table_obj->header_font_weight ) ) . '" ';
		echo 'data-header-font-style="' . esc_attr( stripslashes( $table_obj->header_font_style ) ) . '" ';
		echo 'data-header-background-color="' . esc_attr( stripslashes( $table_obj->header_background_color ) ) . '" ';
		echo 'data-header-font-color="' . esc_attr( stripslashes( $table_obj->header_font_color ) ) . '" ';
		echo 'data-header-link-color="' . esc_attr( stripslashes( $table_obj->header_link_color ) ) . '" ';
		echo 'data-header-border-color="' . esc_attr( stripslashes( $table_obj->header_border_color ) ) . '" ';
		echo 'data-header-position-alignment="' . esc_attr( stripslashes( $table_obj->header_position_alignment ) ) . '" ';
		echo 'data-body-font-size="' . intval( $table_obj->body_font_size, 10 ) . '" ';
		$this->echo_font_family_html_attribute( 'data-body-font-family', $table_obj->body_font_family );
		echo 'data-body-font-weight="' . esc_attr( stripslashes( $table_obj->body_font_weight ) ) . '" ';
		echo 'data-body-font-style="' . esc_attr( stripslashes( $table_obj->body_font_style ) ) . '" ';
		echo 'data-even-rows-background-color="' . esc_attr( stripslashes( $table_obj->even_rows_background_color ) ) . '" ';
		echo 'data-odd-rows-background-color="' . esc_attr( stripslashes( $table_obj->odd_rows_background_color ) ) . '" ';
		echo 'data-even-rows-font-color="' . esc_attr( stripslashes( $table_obj->even_rows_font_color ) ) . '" ';
		echo 'data-even-rows-link-color="' . esc_attr( stripslashes( $table_obj->even_rows_link_color ) ) . '" ';
		echo 'data-odd-rows-font-color="' . esc_attr( stripslashes( $table_obj->odd_rows_font_color ) ) . '" ';
		echo 'data-odd-rows-link-color="' . esc_attr( stripslashes( $table_obj->odd_rows_link_color ) ) . '" ';
		echo 'data-rows-border-color="' . esc_attr( stripslashes( $table_obj->rows_border_color ) ) . '" ';

		// Autoalignment options.
		echo 'data-autoalignment-priority="' . esc_attr( stripslashes( $table_obj->autoalignment_priority ) ) . '" ';
		echo 'data-autoalignment-affected-rows-left="' . esc_attr( stripslashes( $table_obj->autoalignment_affected_rows_left ) ) . '" ';
		echo 'data-autoalignment-affected-rows-center="' . esc_attr( stripslashes( $table_obj->autoalignment_affected_rows_center ) ) . '" ';
		echo 'data-autoalignment-affected-rows-right="' . esc_attr( stripslashes( $table_obj->autoalignment_affected_rows_right ) ) . '" ';
		echo 'data-autoalignment-affected-columns-left="' . esc_attr( stripslashes( $table_obj->autoalignment_affected_columns_left ) ) . '" ';
		echo 'data-autoalignment-affected-columns-center="' . esc_attr( stripslashes( $table_obj->autoalignment_affected_columns_center ) ) . '" ';
		echo 'data-autoalignment-affected-columns-right="' . esc_attr( stripslashes( $table_obj->autoalignment_affected_columns_right ) ) . '" ';

		// Responsive options.
		echo 'data-tablet-breakpoint="' . intval( $table_obj->tablet_breakpoint, 10 ) . '" ';
		echo 'data-hide-tablet-list="' . esc_attr( stripslashes( $table_obj->hide_tablet_list ) ) . '"';
		echo 'data-tablet-header-font-size="' . intval( $table_obj->tablet_header_font_size, 10 ) . '" ';
		echo 'data-tablet-body-font-size="' . intval( $table_obj->tablet_body_font_size, 10 ) . '" ';
		echo 'data-tablet-hide-images="' . intval( $table_obj->tablet_hide_images, 10 ) . '" ';
		echo 'data-phone-breakpoint="' . intval( $table_obj->phone_breakpoint, 10 ) . '" ';
		echo 'data-hide-phone-list="' . esc_attr( stripslashes( $table_obj->hide_phone_list ) ) . '" ';
		echo 'data-phone-header-font-size="' . intval( $table_obj->phone_header_font_size, 10 ) . '" ';
		echo 'data-phone-body-font-size="' . intval( $table_obj->phone_body_font_size, 10 ) . '" ';
		echo 'data-phone-hide-images="' . intval( $table_obj->phone_hide_images, 10 ) . '" ';

		// Advanced options.
		echo 'data-enable-cell-properties="' . intval( $table_obj->enable_cell_properties, 10 ) . '" ';
	}

	/**
	 * Echo the HTML of a table header cell based on the provided parameters.
	 *
	 * @param object $table_obj An object with all the properties of the table.
	 * @param array  $table_cells_properties An array which includes all the cell properties of this table.
	 * @param string $cell_data The data of the cell.
	 * @param string $key1 The row index.
	 * @param string $key2 The column index.
	 *
	 * @return void
	 */
	private function echo_cell_data_th( $table_obj, $table_cells_properties, $cell_data, $key1, $key2 ) {

		echo '<th>';

		if ( 1 === intval( $table_obj->enable_cell_properties, 10 ) ) {

			/**
			 * Search the properties of this cell in the array which include all the cell properties
			 *  of this table.
			 */
			$cell_properties = false;
			foreach ( $table_cells_properties as $key => $val ) {
				if ( intval( $val['row_index'], 10 ) === $key1 && intval( $val['column_index'], 10 ) === $key2 ) {
					$cell_properties = $val;
					break;
				}
			}
		}

		if ( isset( $cell_properties ) && false !== $cell_properties && 1 === intval( $table_obj->enable_cell_properties, 10 ) ) {

			if ( 1 === intval( $table_obj->enable_cell_properties, 10 ) ) {
				/*
				 * Use a link instead of the text if the "link" property is set and the
				 * "Enable Manual Sorting" option is disabled
				 */
				if ( strlen( trim( $cell_properties['link'] ) ) > 0 && 0 === intval( $table_obj->enable_manual_sorting, 10 ) ) {
					echo '<a href="' . esc_url( stripslashes( $cell_properties['link'] ) ) . '">' . esc_html( $cell_data ) . '</a>';
				} else {
					echo esc_html( $cell_data );
				}

				// Add an image to the left of the cell if the "image_left" property is set.
				if ( strlen( trim( $cell_properties['image_left'] ) ) > 0 ) {
					echo '<img class="daextletal-image-left" src="' . esc_url( stripslashes( $cell_properties['image_left'] ) ) . '">';
				}

				// Add an image to the right of the cell if the "image_right" property is set.
				if ( strlen( trim( $cell_properties['image_right'] ) ) > 0 ) {
					echo '<img class="daextletal-image-right" src="' . esc_url( stripslashes( $cell_properties['image_right'] ) ) . '">';
				}
			}
		} else {
			echo esc_html( $cell_data );
		}

		echo '</th>';
	}

	/**
	 * Echo the HTML of a table body cell based on the provided parameters.
	 *
	 * @param object $table_obj An object with all the properties of the table.
	 * @param array  $table_cells_properties An array which includes all the cell properties of this table.
	 * @param string $cell_data The data of the cell.
	 * @param string $key1 The row index.
	 * @param string $key2 The column index.
	 *
	 * @return void
	 */
	private function echo_cell_data_td( $table_obj, $table_cells_properties, $cell_data, $key1, $key2 ) {

		echo '<td>';

		if ( intval( $table_obj->enable_cell_properties, 10 ) === 1 ) {

			/**
			 * Search the properties of this cell in the array which include all the cell properties
			 * of this table.
			 */
			$cell_properties = false;
			foreach ( $table_cells_properties as $key => $val ) {
				if ( intval( $val['row_index'], 10 ) === $key1 && intval( $val['column_index'], 10 ) === $key2 ) {
					$cell_properties = $val;
					break;
				}
			}
		}

		if ( isset( $cell_properties ) && false !== $cell_properties && 1 === intval( $table_obj->enable_cell_properties, 10 ) ) {

			if ( 1 === intval( $table_obj->enable_cell_properties, 10 ) ) {

				// Use a link instead of the text if the "link" property is set.
				if ( strlen( trim( $cell_properties['link'] ) ) > 0 ) {
					echo '<a href="' . esc_url( stripslashes( $cell_properties['link'] ) ) . '">' . esc_html( $cell_data ) . '</a>';
				} else {
					echo esc_html( $cell_data );
				}

				// Add an image to the left of the cell if the "image_left" property is set.
				if ( strlen( trim( $cell_properties['image_left'] ) ) > 0 ) {
					echo '<img class="daextletal-image-left" src="' . esc_url( stripslashes( $cell_properties['image_left'] ) ) . '">';
				}

				// Add an image to the left of the cell if the "image_right" property is set.
				if ( strlen( trim( $cell_properties['image_right'] ) ) > 0 ) {
					echo '<img class="daextletal-image-right" src="' . esc_url( stripslashes( $cell_properties['image_right'] ) ) . '">';
				}
			}
		} else {

			echo esc_html( $cell_data );

		}

		echo '</td>';
	}


	/**
	 * Echo the font family in the related HTML attribute.
	 *
	 * Note that each font family is extracted from the $font_family string and the quotes and double quotes are
	 * removed. Single quote are then added to make this separator consistent.
	 *
	 * @param string $rule The rule of the font family.
	 * @param string $font_family The font family.
	 *
	 * @return void
	 */
	private function echo_font_family_html_attribute( $rule, $font_family ) {

		// Use a regex to extract the various font families from $font_family.
		preg_match_all( '/[^,]+/', $font_family, $fonts );

		// For each font remove quotes and double quotes.
		echo esc_attr( $rule ) . '="';

		$cleaned_fonts = array();
		foreach ( $fonts[0] as $key => $font ) {

			$font = trim( $font );

			// Use a regex to remove quotes and double quotes from the beginning of the font.
			preg_match( '/["\'](.+)["\']/', $font, $matches );

			if ( isset( $matches[1] ) ) {
				$cleaned_fonts[] = $matches[1];
			} else {
				$cleaned_fonts[] = $font;
			}
		}

		// Echo the cleaned fonts.
		foreach ( $cleaned_fonts as $key => $cleaned_font ) {
			echo "'" . esc_attr( $cleaned_font ) . "'";
			if ( $key < count( $cleaned_fonts ) - 1 ) {
				echo ', ';
			}
		}

		echo '" ';
	}
}