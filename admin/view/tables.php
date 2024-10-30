<?php
/**
 * The file used to display the "Tables" menu in the admin area.
 *
 * @package league-table-lite
 */

if ( ! current_user_can( get_option( $this->shared->get( 'slug' ) . '_tables_menu_capability' ) ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'league-table-lite' ) );
}

?>

<!-- process data -->

<?php

// Initialize variables -----------------------------------------------------------------------------------------------.
$dismissible_notice_a = array();

// Preliminary operations ---------------------------------------------------------------------------------------------.
global $wpdb;

// Sanitization ---------------------------------------------------------------------------------------------.

// Actions.
$data['edit_id']   = isset( $_GET['edit_id'] ) ? intval( $_GET['edit_id'], 10 ) : null;
$data['delete_id'] = isset( $_POST['delete_id'] ) ? intval( $_POST['delete_id'], 10 ) : null;
$data['clone_id']  = isset( $_POST['clone_id'] ) ? intval( $_POST['clone_id'], 10 ) : null;
$data['update_id'] = isset( $_POST['update_id'] ) ? intval( $_POST['update_id'], 10 ) : null;

// Filter and search data.
$data['s'] = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : null;

// if the temporary tables are more than 100 clear the older (first inserted) temporary table.
$this->delete_old_temporary_table();

// delete a table.
if ( ! is_null( $data['delete_id'] ) ) {

	// Nonce verification.
	check_admin_referer( 'daextletal_delete_table_' . $data['delete_id'], 'daextletal_delete_table_nonce' );

	// delete this table.
	$table_name     = $wpdb->prefix . $this->shared->get( 'slug' ) . '_table';
	$safe_sql       = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}daextletal_table WHERE id = %d ", $data['delete_id'] );
	$query_result_1 = $wpdb->query( $safe_sql ); // phpcs:ignore

	// delete all the rows of this table.
	$table_name     = $wpdb->prefix . $this->shared->get( 'slug' ) . '_data';
	$safe_sql       = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}daextletal_data WHERE table_id = %d ", $data['delete_id'] );
	$query_result_2 = $wpdb->query( $safe_sql ); // phpcs:ignore

	// delete all the cells of this table.
	$table_name     = $wpdb->prefix . $this->shared->get( 'slug' ) . '_cell';
	$safe_sql       = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}daextletal_cell WHERE table_id = %d ", $data['delete_id'] );
	$query_result_3 = $wpdb->query( $safe_sql ); // phpcs:ignore

	if ( false !== $query_result_1 && false !== $query_result_2 && false !== $query_result_3 ) {
		$dismissible_notice_a[] = array(
			'message' => __( 'The table has been successfully deleted.', 'league-table-lite' ),
			'class'   => 'updated',
		);
		$invalid_data           = true;
	}
}

// clone the table.
if ( ! is_null( $data['clone_id'] ) ) {

	// Nonce verification.
	check_admin_referer( 'daextletal_clone_table_' . $data['clone_id'], 'daextletal_clone_table_nonce' );

	// clone the table.
	$table_name   = $wpdb->prefix . $this->shared->get( 'slug' ) . '_table';
	$result       = $this->shared->duplicate_record( $table_name, 'id', $data['clone_id'] );
	$table_id_new = $result['last_inserted_id'];

	// Clone the rows.
	$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . '_data';
	$safe_sql   = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}daextletal_data WHERE table_id = %d", $data['clone_id'] );
	$rows       = $wpdb->get_results( $safe_sql, ARRAY_A ); // phpcs:ignore
	foreach ( $rows as $row ) {

		// Retrieve the record to duplicate.
		$safe_sql = $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}daextletal_data WHERE id = %d",
			$row['id']
		);
		$record   = $wpdb->get_row( $safe_sql, ARRAY_A ); // phpcs:ignore

		// Remove the primary key from the record.
		unset( $record['id'] );

		// Update the table_id.
		$record['table_id'] = $table_id_new;

		// Insert the record into the database.
		$query_result = $wpdb->insert( $table_name, $record ); // phpcs:ignore

	}

	// Clone the cells.
	$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . '_cell';
	$safe_sql   = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}daextletal_cell WHERE table_id = %d", $data['clone_id'] );
	$cells       = $wpdb->get_results( $safe_sql, ARRAY_A ); // phpcs:ignore
	foreach ( $cells as $cell ) {

		// Retrieve the record to duplicate.
		$safe_sql = $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}daextletal_cell WHERE id = %d",
			$cell['id']
		);
		$record   = $wpdb->get_row( $safe_sql, ARRAY_A ); // phpcs:ignore

		// Remove the primary key from the record.
		unset( $record['id'] );

		// Update the table_id.
		$record['table_id'] = $table_id_new;

		// Insert the record into the database.
		$query_result = $wpdb->insert( $table_name, $record ); // phpcs:ignore

	}

	$dismissible_notice_a[] = array(
		'message' => __( 'The table has been successfully duplicated.', 'league-table-lite' ),
		'class'   => 'updated',
	);

}

// get the table data.
$display_form = true;
if ( ! is_null( $data['edit_id'] ) ) {
	$safe_sql  = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}daextletal_table WHERE temporary = 0 AND id = %d", $data['edit_id'] );
	$table_obj  = $wpdb->get_row( $safe_sql ); // phpcs:ignore
	if ( null === $table_obj ) {
		$display_form = false;
	}
}

?>

<!-- output -->

<div class="wrap">

	<?php if ( $this->shared->get_number_of_tables() > 0 ) : ?>

		<div id="daext-header-wrapper" class="daext-clearfix">

			<h2><?php esc_attr_e( 'League Table - Tables', 'league-table-lite' ); ?></h2>

			<form action="admin.php" method="get">
				<input type="hidden" name="page" value="daextletal-tables">
				<?php
				if ( ! is_null( $data['s'] ) && strlen( trim( $data['s'] ) ) > 0 ) {
					$search_string = $data['s'];
				} else {
					$search_string = '';
				}
				?>
				<input type="text" name="s" placeholder="<?php esc_attr_e( 'Search...', 'league-table-lite' ); ?>"
						value="<?php echo esc_attr( stripslashes( $search_string ) ); ?>" autocomplete="off" maxlength="255">
				<input type="submit" value="">
			</form>

		</div>

	<?php else : ?>

		<div id="daext-header-wrapper" class="daext-clearfix">

			<h2><?php esc_attr_e( 'League Table - Tables', 'league-table-lite' ); ?></h2>

		</div>

	<?php endif; ?>

	<div id="daext-menu-wrapper">

		<?php $this->dismissible_notice( $dismissible_notice_a ); ?>

		<?php

		// Create the query part used to filter the results when a search is performed.
		if ( ! is_null( $data['s'] ) ) {
			$filter = $wpdb->prepare( 'AND (id LIKE %s OR name LIKE %s OR description LIKE %s)', '%' . $data['s'] . '%', '%' . $data['s'] . '%', '%' . $data['s'] . '%' );
		} else {
			$filter = '';
		}

		// Retrieve the total number of tables.
		$safe_sql = "SELECT COUNT(*) FROM {$wpdb->prefix}daextletal_table WHERE temporary = 0 $filter"; // phpcs:ignore
		$total_items = $wpdb->get_var( $safe_sql ); // phpcs:ignore

		// Initialize the pagination class.
		require_once $this->shared->get( 'dir' ) . '/admin/inc/class-daextletal-pagination.php';
		$pag = new daextletal_pagination();
		$pag->set_total_items( $total_items );// Set the total number of items.
		$pag->set_record_per_page( 10 ); // Set records per page.
		$pag->set_target_page( 'admin.php?page=' . $this->shared->get( 'slug' ) . '-tables' );// Set target page.
		$pag->set_current_page();// set the current page number.

		?>

		<!-- Query the database -->
		<?php
		$query_limit = $pag->query_limit();
		$safe_sql = "SELECT * FROM {$wpdb->prefix}daextletal_table WHERE temporary = 0 $filter ORDER BY id DESC $query_limit"; // phpcs:ignore
		$results     = $wpdb->get_results( $safe_sql, ARRAY_A ); // phpcs:ignore
		?>

		<?php if ( count( $results ) > 0 ) : ?>

			<div class="daext-items-container">

				<!-- list of tables -->
				<table class="daext-items">
					<thead>
					<tr>
						<th>
							<div><?php esc_html_e( 'Name', 'league-table-lite' ); ?></div>
						</th>
						<th>
							<div><?php esc_html_e( 'Description', 'league-table-lite' ); ?></div>
						</th>
						<th>
							<div><?php esc_html_e( 'Shortcode', 'league-table-lite' ); ?></div>
						</th>
						<th></th>
					</tr>
					</thead>
					<tbody>

					<?php foreach ( $results as $result ) : ?>
						<tr>
							<td><?php echo esc_html( stripslashes( $result['name'] ) ); ?></td>
							<td><?php echo esc_html( stripslashes( $result['description'] ) ); ?></td>
							<td><?php echo '[ltl id="' . intval( $result['id'], 10 ) . '"]'; ?></td>
							<td class="icons-container">
								<form method="POST"
										action="admin.php?page=<?php echo esc_attr( $this->shared->get( 'slug' ) ); ?>-tables">
									<?php wp_nonce_field( 'daextletal_clone_table_' . intval( $result['id'], 10 ), 'daextletal_clone_table_nonce' ); ?>
									<input type="hidden" name="clone_id" value="<?php echo esc_attr( $result['id'] ); ?>">
									<input class="menu-icon clone help-icon" type="submit" value="">
								</form>
								<a class="menu-icon edit"
									href="admin.php?page=<?php echo esc_attr( $this->shared->get( 'slug' ) ); ?>-tables&edit_id=<?php echo intval( $result['id'], 10 ); ?>"></a>
								<form method="POST" id="form-delete-<?php echo intval( $result['id'], 10 ); ?>" action="admin.php?page=<?php echo esc_attr( $this->shared->get( 'slug' ) ); ?>-tables">
									<?php wp_nonce_field( 'daextletal_delete_table_' . intval( $result['id'], 10 ), 'daextletal_delete_table_nonce' ); ?>
									<input type="hidden" value="<?php echo intval( $result['id'], 10 ); ?>" name="delete_id">
									<input class="menu-icon delete" type="submit" value="">
								</form>
							</td>
						</tr>
					<?php endforeach; ?>

					</tbody>

				</table>

			</div>

			<!-- Display the pagination -->
			<?php if ( $pag->total_items > 0 ) : ?>
				<div class="daext-tablenav daext-clearfix">
					<div class="daext-tablenav-pages">
						<span
								class="daext-displaying-num"><?php echo esc_html( $pag->total_items ); ?>
							&nbsp<?php esc_attr_e( 'items', 'league-table-lite' ); ?></span>
						<?php $pag->show(); ?>
					</div>
				</div>
			<?php endif; ?>

		<?php endif; ?>

		<div class="table-container">

			<?php if ( $display_form ) : ?>

				<?php if ( ! is_null( $data['edit_id'] ) ) : ?>

					<!-- Edit Table -->

					<div class="daext-form-container">

						<div class="daext-form-title"><?php esc_html_e( 'Edit Table', 'league-table-lite' ); ?> <?php echo intval( $table_obj->id, 10 ); ?></div>

						<table class="daext-form daext-form-table">

							<input type="hidden" id="update-id" value="<?php echo intval( $table_obj->id, 10 ); ?>"/>

							<!-- Name -->
							<tr>
								<th><label for="name"><?php esc_html_e( 'Name', 'league-table-lite' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr( stripslashes( $table_obj->name ) ); ?>" type="text"
											id="name" maxlength="255" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'The name of the table.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Description -->
							<tr>
								<th><label
											for="description"><?php esc_html_e( 'Description', 'league-table-lite' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr( stripslashes( $table_obj->description ) ); ?>"
											type="text" id="description" maxlength="255" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'The description of the table.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Rows -->
							<tr>
								<th><label for="rows"><?php esc_html_e( 'Rows', 'league-table-lite' ); ?></label></th>
								<td>
									<input value="<?php echo intval( $table_obj->rows, 10 ); ?>" type="text"
											id="rows"
											maxlength="5" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'To define the number of rows please enter a number included between 1 and 10000.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Columns -->
							<tr>
								<th><label for="columns"><?php esc_html_e( 'Columns', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input value="<?php echo intval( $table_obj->columns, 10 ); ?>" type="text"
											id="columns"
											maxlength="2" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'To define the number of columns please enter a number included between 1 and 40.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<tr valign="top">
								<th><label for="data"><?php esc_attr_e( 'Data', 'league-table-lite' ); ?></label></th>
								<td id="daextletal-table-td">
									<div id="daextletal-table"></div>
								</td>
							</tr>

							<!-- Sorting Options ---------------------------------------------- -->
							<tr class="group-trigger" data-trigger-target="sorting-options">
								<th class="group-title"><?php esc_attr_e( 'Sorting', 'league-table-lite' ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- Enable Sorting -->
							<tr class="sorting-options">
								<th><label
											for="enable-sorting"><?php esc_html_e( 'Enable Sorting', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="enable-sorting">
										<option
												value="0" <?php selected( intval( $table_obj->enable_sorting, 10 ), 0 ); ?>><?php esc_html_e( 'No', 'league-table-lite' ); ?></option>
										<option
												value="1" <?php selected( intval( $table_obj->enable_sorting, 10 ), 1 ); ?>><?php esc_html_e( 'Yes', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'With this option enabled the table will be sorted based on the criteria defined in this section.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Enable Manual Sorting -->
							<tr class="sorting-options">
								<th><label
											for="enable-manual-sorting"><?php esc_html_e( 'Enable Manual Sorting', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="enable-manual-sorting">
										<option
												value="0" <?php selected( intval( $table_obj->enable_manual_sorting, 10 ), 0 ); ?>><?php esc_html_e( 'No', 'league-table-lite' ); ?></option>
										<option
												value="1" <?php selected( intval( $table_obj->enable_manual_sorting, 10 ), 1 ); ?>><?php esc_html_e( 'Yes', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'This option enables the users to manually sort the table by clicking on the table header. Please note that manual sorting will not be applied if the "Enable Sorting" option is disabled.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Show Position -->
							<tr class="sorting-options">
								<th><label
											for="show-position"><?php esc_html_e( 'Show Position', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="show-position">
										<option
												value="0" <?php selected( intval( $table_obj->show_position, 10 ), 0 ); ?>><?php esc_html_e( 'No', 'league-table-lite' ); ?></option>
										<option
												value="1" <?php selected( intval( $table_obj->show_position, 10 ), 1 ); ?>><?php esc_html_e( 'Yes', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'If enabled, the position column will be automatically generated.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Position Side -->
							<tr class="sorting-options">
								<th><label
											for="position-side"><?php esc_html_e( 'Position Side', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="position-side">
										<option
												value="left" <?php selected( $table_obj->position_side, 'left' ); ?>><?php esc_html_e( 'Left', 'league-table-lite' ); ?></option>
										<option
												value="right" <?php selected( $table_obj->position_side, 'right' ); ?>><?php esc_html_e( 'Right', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'Select if the position column should be generated on the left side or on the right side of the table.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Position Label -->
							<tr class="sorting-options">
								<th><label
											for="position-label"><?php esc_html_e( 'Position Label', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input type="text" value="<?php echo esc_attr( stripslashes( $table_obj->position_label ) ); ?>"
											id="position-label" maxlength="255" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Enter the text that should be displayed in the header of the position column.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Number Format -->
							<tr class="sorting-options">
								<th><label
											for="number-format"><?php esc_html_e( 'Number Format', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="number-format">
										<option
												value="0" <?php selected( intval( $table_obj->number_format, 10 ), '0' ); ?>><?php esc_html_e( 'EU', 'league-table-lite' ); ?></option>
										<option
												value="1" <?php selected( intval( $table_obj->number_format, 10 ), '1' ); ?>><?php esc_html_e( 'US', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'Select if the decimal mark (the symbol used to separate the integer part from the fractional part of a decimal number) in use is the comma (EU) or the point (US). This option affects how the "Currency" data type is sorted.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Order Desc Asc -->
							<tr class="sorting-options">
								<th><label
											for="order-desc-asc"><?php echo esc_html__( 'Order', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="order-desc-asc">
										<option
												value="0" <?php selected( intval( $table_obj->{'order_desc_asc'}, 10 ), 0 ); ?>><?php esc_html_e( 'Disabled', 'league-table-lite' ); ?></option>
										<option
												value="1" <?php selected( intval( $table_obj->{'order_desc_asc'}, 10 ), 1 ); ?>><?php esc_html_e( 'Descending', 'league-table-lite' ); ?></option>
										<option
												value="2" <?php selected( intval( $table_obj->{'order_desc_asc'}, 10 ), 2 ); ?>><?php esc_html_e( 'Ascending', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'This option allows you to enable (in descending or ascending order) or disable the order.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Order By -->
							<tr class="sorting-options">
								<th><label
											for="order-by"><?php echo esc_html__( 'Order By', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="order-by">
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'This option allows you to determine for which column the order should be applied.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Order Data Type -->
							<tr class="sorting-options">
								<th><label
											for="order-data-type"><?php echo esc_html__( 'Order Data Type', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="order-data-type">
										<option
												value="auto" <?php selected( $table_obj->{'order_data_type'}, 'auto' ); ?>><?php esc_html_e( 'Auto', 'league-table-lite' ); ?></option>
										<option
												value="text" <?php selected( $table_obj->{'order_data_type'}, 'text' ); ?>><?php esc_html_e( 'Text', 'league-table-lite' ); ?></option>
										<option
												value="digit" <?php selected( $table_obj->{'order_data_type'}, 'digit' ); ?>><?php esc_html_e( 'Digit', 'league-table-lite' ); ?></option>
										<option
												value="percent" <?php selected( $table_obj->{'order_data_type'}, 'percent' ); ?>><?php esc_html_e( 'Percent', 'league-table-lite' ); ?></option>
										<option
												value="currency" <?php selected( $table_obj->{'order_data_type'}, 'currency' ); ?>><?php esc_html_e( 'Currency', 'league-table-lite' ); ?></option>
										<option
												value="url" <?php selected( $table_obj->{'order_data_type'}, 'url' ); ?>><?php esc_html_e( 'URL', 'league-table-lite' ); ?></option>
										<option
												value="time" <?php selected( $table_obj->{'order_data_type'}, 'time' ); ?>><?php esc_html_e( 'Time', 'league-table-lite' ); ?></option>
										<option
												value="isoDate" <?php selected( $table_obj->{'order_data_type'}, 'isoDate' ); ?>><?php esc_html_e( 'ISO Date', 'league-table-lite' ); ?></option>
										<option
												value="usLongDate" <?php selected( $table_obj->{'order_data_type'}, 'usLongDate' ); ?>><?php esc_html_e( 'US Long Date', 'league-table-lite' ); ?></option>
										<option
												value="shortDate" <?php selected( $table_obj->{'order_data_type'}, 'shortDate' ); ?>><?php esc_html_e( 'Short Date', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'Select the type of data included in the column that determines the order. Please note that if you leave "Auto" the type of data will be automatically determined by the sorting system.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Order Date Format -->
							<tr class="sorting-options">
								<th><label
											for="order-date-format"><?php echo esc_html__( 'Order Date Format', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="order-date-format">
										<option
												value="ddmmyyyy" <?php selected( $table_obj->{'order_date_format'}, 'ddmmyyyy' ); ?>><?php esc_html_e( 'DDMMYYYY', 'league-table-lite' ); ?></option>
										<option
												value="yyyymmdd" <?php selected( $table_obj->{'order_date_format'}, 'yyyymmdd' ); ?>><?php esc_html_e( 'YYYYMMDD', 'league-table-lite' ); ?></option>
										<option
												value="mmddyyyy" <?php selected( $table_obj->{'order_date_format'}, 'mmddyyyy' ); ?>><?php esc_html_e( 'MMDDYYYY', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title="<?php echo esc_attr__( 'Use this option to set the data format of the column that determines the order. Please note that this option will be considered only if the corresponding "Order Data Type" option is set to "Short Date".', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Style Options ---------------------------------------------- -->
							<tr class="group-trigger" data-trigger-target="style-options">
								<th class="group-title"><?php esc_html_e( 'Style', 'league-table-lite' ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- Table Layout -->
							<tr class="style-options">
								<th><label
											for="table-layout"><?php esc_html_e( 'Table Layout', 'league-table-lite' ); ?></label></th>
								<td>
									<select id="table-layout">
										<option
												value="0" <?php selected( intval( $table_obj->table_layout, 10 ), 0 ); ?>><?php esc_html_e( 'Auto', 'league-table-lite' ); ?></option>
										<option
												value="1" <?php selected( intval( $table_obj->table_layout, 10 ), 1 ); ?>><?php esc_html_e( 'Fixed', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'Select the algorithm used to lay out the table.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Table Width -->
							<tr class="style-options">
								<th><label
											for="table-width"><?php esc_html_e( 'Table Width', 'league-table-lite' ); ?></label></th>
								<td>
									<select id="table-width">
										<option
												value="0" <?php selected( intval( $table_obj->table_width, 10 ), 0 ); ?>><?php esc_html_e( 'Full Width', 'league-table-lite' ); ?></option>
										<option
												value="1" <?php selected( intval( $table_obj->table_width, 10 ), 1 ); ?>><?php esc_html_e( 'Auto', 'league-table-lite' ); ?></option>
										<option
												value="2" <?php selected( intval( $table_obj->table_width, 10 ), 2 ); ?>><?php esc_html_e( 'Specified Value', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'With "Full Width" the table width will be equal to the width of the container, with "Auto" the table width will be determined automatically based on the table content, with "Specified Value" the table width will be determined by the value entered in the "Table Width Value" field.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Table Width Value -->
							<tr class="style-options">
								<th><label
											for="table-width-value"><?php esc_html_e( 'Table Width Value', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input type="text" value="<?php echo intval( $table_obj->table_width_value, 10 ); ?>"
											id="table-width-value" maxlength="6" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Set the width of the table. Please note that this option will be used only if the "Table Width" option is set to "Specified Value".', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Table Minimum Width -->
							<tr class="style-options">
								<th><label
											for="table-minimum-width"><?php esc_html_e( 'Table Minimum Width', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input type="text" value="<?php echo intval( $table_obj->table_minimum_width, 10 ); ?>" id="table-minimum-width" maxlength="6" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Set the minimum width of the table.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Column Width -->
							<tr class="style-options">
								<th><label for="column-width"><?php esc_html_e( 'Column Width', 'league-table-lite' ); ?></label></th>
								<td>
									<select id="column-width">
										<option value="0" <?php selected( intval( $table_obj->column_width, 10 ), 0 ); ?>><?php esc_html_e( 'Auto', 'league-table-lite' ); ?></option>
										<option value="1" <?php selected( intval( $table_obj->column_width, 10 ), 1 ); ?>><?php esc_html_e( 'Specified Value', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'Select if the width of the columns should be automatically determined or based on the values provided in the "Column Width Value" field.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Column Width Value -->
							<tr class="style-options">
								<th><label
											for="column-width-value"><?php esc_html_e( 'Column Width Value', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input type="text" value="<?php echo esc_attr( stripslashes( $table_obj->column_width_value ) ); ?>"
											id="column-width-value" maxlength="2000" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Enter a list of column widths, separated by a comma. If only one column width is provided the single value will be applied to all the columns.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Enable Container -->
							<tr class="style-options">
								<th><label
											for="enable-container"><?php esc_html_e( 'Enable Container', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="enable-container">
										<option
												value="0" <?php selected( intval( $table_obj->enable_container, 10 ), 0 ); ?>><?php esc_html_e( 'No', 'league-table-lite' ); ?></option>
										<option
												value="1" <?php selected( intval( $table_obj->enable_container, 10 ), 1 ); ?>><?php esc_html_e( 'Yes', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'Enable this option to include the table in a container. With this feature enabled and the proper values in the "Container Width" and "Container Height" options you will be able to generate a table with an horizontal and/or a vertical scrolling bar.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Container Width -->
							<tr class="style-options">
								<th><label
											for="container-width"><?php esc_html_e( 'Container Width', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input type="text" value="<?php echo intval( $table_obj->container_width, 10 ); ?>"
											id="container-width" maxlength="6" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Enter the width of the container or 0 if you want the width of the container automatically determined. This option will be considered only if the "Enable Container" option is enabled.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Container Height -->
							<tr class="style-options">
								<th><label
											for="container-height"><?php esc_html_e( 'Container Height', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input type="text" value="<?php echo intval( $table_obj->container_height, 10 ); ?>"
											id="container-height" maxlength="6" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Enter the height of the container or 0 if you want the height of the container automatically determined. This option will be considered only if the "Enable Container" option is enabled.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Table Margin Top -->
							<tr class="style-options">
								<th><label
											for="table-margin-top"><?php esc_html_e( 'Table Margin Top', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input value="<?php echo intval( $table_obj->table_margin_top, 10 ); ?>" type="text"
											id="table-margin-top" maxlength="6" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Set the top margin of the table.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Table Margin Bottom -->
							<tr class="style-options">
								<th><label
											for="table-margin-bottom"><?php esc_html_e( 'Table Margin Bottom', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input value="<?php echo intval( $table_obj->table_margin_bottom, 10 ); ?>"
											type="text"
											id="table-margin-bottom" maxlength="6" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Set the bottom margin of the table.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Show Header -->
							<tr class="style-options">
								<th><label
											for="show-header"><?php esc_html_e( 'Show Header', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="show-header">
										<option
												value="0" <?php selected( intval( $table_obj->show_header, 10 ), 0 ); ?>><?php esc_html_e( 'No', 'league-table-lite' ); ?></option>
										<option
												value="1" <?php selected( intval( $table_obj->show_header, 10 ), 1 ); ?>><?php esc_html_e( 'Yes', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'With this option enabled the table header will be displayed.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Header Font Size -->
							<tr class="style-options">
								<th><label
											for="header-font-size"><?php esc_html_e( 'Header Font Size', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input value="<?php echo intval( $table_obj->header_font_size, 10 ); ?>" type="text"
											id="header-font-size" maxlength="3" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Set the font size of the text displayed in the header.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Header Font Family -->
							<tr class="style-options">
								<th><label
											for="header-font-family"><?php esc_html_e( 'Header Font Family', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input value="<?php echo esc_attr( stripslashes( $table_obj->header_font_family ) ); ?>"
											type="text"
											id="header-font-family" maxlength="255" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Set the font family of the text displayed in the header.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Header Font Weight -->
							<tr class="style-options">
								<th><label
											for="header-font-weight"><?php esc_html_e( 'Header Font Weight', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="header-font-weight">
										<option
												value="100" <?php selected( $table_obj->header_font_weight, '100' ); ?>><?php esc_html_e( '100', 'league-table-lite' ); ?></option>
										<option
												value="200" <?php selected( $table_obj->header_font_weight, '200' ); ?>><?php esc_html_e( '200', 'league-table-lite' ); ?></option>
										<option
												value="300" <?php selected( $table_obj->header_font_weight, '300' ); ?>><?php esc_html_e( '300', 'league-table-lite' ); ?></option>
										<option
												value="400" <?php selected( $table_obj->header_font_weight, '400' ); ?>><?php esc_html_e( '400', 'league-table-lite' ); ?></option>
										<option
												value="500" <?php selected( $table_obj->header_font_weight, '500' ); ?>><?php esc_html_e( '500', 'league-table-lite' ); ?></option>
										<option
												value="600" <?php selected( $table_obj->header_font_weight, '600' ); ?>><?php esc_html_e( '600', 'league-table-lite' ); ?></option>
										<option
												value="700" <?php selected( $table_obj->header_font_weight, '700' ); ?>><?php esc_html_e( '700', 'league-table-lite' ); ?></option>
										<option
												value="800" <?php selected( $table_obj->header_font_weight, '800' ); ?>><?php esc_html_e( '800', 'league-table-lite' ); ?></option>
										<option
												value="900" <?php selected( $table_obj->header_font_weight, '900' ); ?>><?php esc_html_e( '900', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'Set the font weight of the text displayed in the header.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Header Font Style -->
							<tr class="style-options">
								<th><label
											for="header-font-style"><?php esc_html_e( 'Header Font Style', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="header-font-style">
										<option
												value="normal" <?php selected( $table_obj->header_font_style, 'normal' ); ?>><?php esc_html_e( 'Normal', 'league-table-lite' ); ?></option>
										<option
												value="italic" <?php selected( $table_obj->header_font_style, 'italic' ); ?>><?php esc_html_e( 'Italic', 'league-table-lite' ); ?></option>
										<option
												value="oblique" <?php selected( $table_obj->header_font_style, 'oblique' ); ?>><?php esc_html_e( 'Oblique', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'Set the font style of the text displayed in the header.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Header Position Alignment -->
							<tr class="style-options">
								<th><label for="header-position-alignment"><?php esc_html_e( 'Header Position Alignment', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="header-position-alignment">
										<option value="left" <?php selected( $table_obj->header_position_alignment, 'left' ); ?>><?php esc_html_e( 'Left', 'league-table-lite' ); ?></option>
										<option value="center" <?php selected( $table_obj->header_position_alignment, 'center' ); ?>><?php esc_html_e( 'Center', 'league-table-lite' ); ?></option>
										<option value="right" <?php selected( $table_obj->header_position_alignment, 'right' ); ?>><?php esc_html_e( 'Right', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'The alignment of the text displayed in the header of the position column.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Header Background Color -->
							<tr class="style-options">
								<th><label
											for="header-background-color"><?php esc_html_e( 'Header Background Color', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input value="<?php echo esc_attr( $table_obj->header_background_color ); ?>"
											class="wp-color-picker" type="text" id="header-background-color"
											maxlength="7"
											size="30"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Set the background color of the header.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Header Font Color -->
							<tr class="style-options">
								<th><label
											for="header-font-color"><?php esc_html_e( 'Header Font Color', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input value="<?php echo esc_attr( $table_obj->header_font_color ); ?>"
											class="wp-color-picker" type="text" id="header-font-color"
											maxlength="7" size="30"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Set the color of the text displayed in the header.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Header Link Color -->
							<tr class="style-options">
								<th><label
											for="header-link-color"><?php esc_html_e( 'Header Link Color', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input value="<?php echo esc_attr( $table_obj->header_link_color ); ?>"
											class="wp-color-picker" type="text" id="header-link-color"
											maxlength="7" size="30"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Set the color of the text used for the links in the header.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Header Border Color -->
							<tr class="style-options">
								<th><label
											for="header-border-color"><?php esc_html_e( 'Header Border Color', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input value="<?php echo esc_attr( $table_obj->header_border_color ); ?>"
											class="wp-color-picker" type="text" id="header-border-color" maxlength="7"
											size="30"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Set the color of the borders in the header.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Body Font Size -->
							<tr class="style-options">
								<th><label
											for="body-font-size"><?php esc_html_e( 'Body Font Size', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input value="<?php echo intval( $table_obj->body_font_size, 10 ); ?>" type="text"
											id="body-font-size" maxlength="6" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Set the font size of the text displayed in the body.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Body Font Family -->
							<tr class="style-options">
								<th><label
											for="body-font-family"><?php esc_html_e( 'Body Font Family', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input value="<?php echo esc_attr( stripslashes( $table_obj->body_font_family ) ); ?>"
											type="text" id="body-font-family" maxlength="255" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Set the font family of the text displayed in the body.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Body Font Weight -->
							<tr class="style-options">
								<th><label
											for="body-font-weight"><?php esc_html_e( 'Body Font Weight', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="body-font-weight">
										<option
												value="100" <?php selected( $table_obj->body_font_weight, '100' ); ?>><?php esc_html_e( '100', 'league-table-lite' ); ?></option>
										<option
												value="200" <?php selected( $table_obj->body_font_weight, '200' ); ?>><?php esc_html_e( '200', 'league-table-lite' ); ?></option>
										<option
												value="300" <?php selected( $table_obj->body_font_weight, '300' ); ?>><?php esc_html_e( '300', 'league-table-lite' ); ?></option>
										<option
												value="400" <?php selected( $table_obj->body_font_weight, '400' ); ?>><?php esc_html_e( '400', 'league-table-lite' ); ?></option>
										<option
												value="500" <?php selected( $table_obj->body_font_weight, '500' ); ?>><?php esc_html_e( '500', 'league-table-lite' ); ?></option>
										<option
												value="600" <?php selected( $table_obj->body_font_weight, '600' ); ?>><?php esc_html_e( '600', 'league-table-lite' ); ?></option>
										<option
												value="700" <?php selected( $table_obj->body_font_weight, '700' ); ?>><?php esc_html_e( '700', 'league-table-lite' ); ?></option>
										<option
												value="800" <?php selected( $table_obj->body_font_weight, '800' ); ?>><?php esc_html_e( '800', 'league-table-lite' ); ?></option>
										<option
												value="900" <?php selected( $table_obj->body_font_weight, '900' ); ?>><?php esc_html_e( '900', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'Set the font weight of the text displayed in the body.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Body Font Style -->
							<tr class="style-options">
								<th><label
											for="body-font-style"><?php esc_html_e( 'Body Font Style', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="body-font-style">
										<option
												value="normal" <?php selected( $table_obj->body_font_style, 'normal' ); ?>><?php esc_html_e( 'Normal', 'league-table-lite' ); ?></option>
										<option
												value="italic" <?php selected( $table_obj->body_font_style, 'italic' ); ?>><?php esc_html_e( 'Italic', 'league-table-lite' ); ?></option>
										<option
												value="oblique" <?php selected( $table_obj->body_font_style, 'oblique' ); ?>><?php esc_html_e( 'Oblique', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'Set the font style of the text displayed in the body.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Even Rows Bg Color -->
							<tr class="style-options">
								<th><label
											for="even-rows-background-color"><?php esc_html_e( 'Even Rows Background Color', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input value="<?php echo esc_attr( $table_obj->even_rows_background_color ); ?>"
											class="wp-color-picker" type="text" id="even-rows-background-color"
											maxlength="7"
											size="30"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Set the background color of the even rows.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Odd Rows Background Color -->
							<tr class="style-options">
								<th><label
											for="odd-rows-bg-color"><?php esc_html_e( 'Odd Rows Background Color', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input value="<?php echo esc_attr( $table_obj->odd_rows_background_color ); ?>"
											class="wp-color-picker" type="text" id="odd-rows-background-color"
											maxlength="7"
											size="30"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Set the background color of the odd rows.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Even Rows Font Color -->
							<tr class="style-options">
								<th><label
											for="even-rows-font-color"><?php esc_html_e( 'Even Rows Font Color', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input value="<?php echo esc_attr( $table_obj->even_rows_font_color ); ?>"
											class="wp-color-picker" type="text" id="even-rows-font-color" maxlength="7"
											size="30"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Set the color of the text displayed in the even rows.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Odd Rows Font Color -->
							<tr class="style-options">
								<th><label
											for="odd-rows-font-color"><?php esc_html_e( 'Odd Rows Font Color', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input value="<?php echo esc_attr( $table_obj->odd_rows_font_color ); ?>"
											class="wp-color-picker" type="text" id="odd-rows-font-color" maxlength="7"
											size="30"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Set the color of the text displayed in the odd rows.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Even Rows Link Color -->
							<tr class="style-options">
								<th><label
											for="even-rows-link-color"><?php esc_html_e( 'Even Rows Link Color', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input value="<?php echo esc_attr( $table_obj->even_rows_link_color ); ?>"
											class="wp-color-picker" type="text" id="even-rows-link-color" maxlength="7"
											size="30"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Set the color of the text displayed for the links of the even rows.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Odd Rows Link Color -->
							<tr class="style-options">
								<th><label
											for="odd-rows-link-color"><?php esc_html_e( 'Odd Rows Link Color', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input value="<?php echo esc_attr( $table_obj->odd_rows_link_color ); ?>"
											class="wp-color-picker" type="text" id="odd-rows-link-color" maxlength="7"
											size="30"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Set the color of the text displayed for the links of the odd rows.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Rows Border Color -->
							<tr class="style-options">
								<th><label
											for="rows-border-color"><?php esc_html_e( 'Rows Border Color', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input value="<?php echo esc_attr( $table_obj->rows_border_color ); ?>"
											class="wp-color-picker" type="text" id="rows-border-color" maxlength="7"
											size="30"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Set the border color of the rows.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Autoalignment Options ---------------------------------------------- -->
							<tr class="group-trigger" data-trigger-target="autoalignment-options">
								<th
									class="group-title"><?php esc_html_e( 'Alignment', 'league-table-lite' ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- Autoalignment Priority -->
							<tr class="autoalignment-options">
								<th><label
											for="autoalignment-priority"><?php esc_html_e( 'Priority', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="autoalignment-priority">
										<option
												value="rows" <?php selected( $table_obj->autoalignment_priority, 'rows' ); ?>><?php esc_html_e( 'Rows', 'league-table-lite' ); ?></option>
										<option
												value="columns" <?php selected( $table_obj->autoalignment_priority, 'columns' ); ?>><?php esc_html_e( 'Columns', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'This option determines which category of alignment has the priority.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Autoalignment Affected Rows Left -->
							<tr class="autoalignment-options">
								<th><label
											for="autoalignment-affected-rows-left"><?php esc_html_e( 'Left (Rows)', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input type="text"
											value="<?php echo esc_attr( stripslashes( $table_obj->autoalignment_affected_rows_left ) ); ?>"
											id="autoalignment-affected-rows-left" maxlength="2000"
											size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php echo esc_attr__( 'Enter a list of row indexes, separated by a comma, where the left alignment should be applied.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Autoalignment Affected Rows Center -->
							<tr class="autoalignment-options">
								<th><label
											for="autoalignment-affected-rows-center"><?php echo esc_html_e( 'Center (Rows)', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input type="text"
											value="<?php echo esc_attr( stripslashes( $table_obj->autoalignment_affected_rows_center ) ); ?>"
											id="autoalignment-affected-rows-center" maxlength="2000"
											size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php echo esc_attr__( 'Enter a list of row indexes, separated by a comma, where the center alignment should be applied.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Autoalignment Affected Rows Right -->
							<tr class="autoalignment-options">
								<th><label
											for="autoalignment-affected-rows-right"><?php echo esc_html_e( 'Right (Rows)', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input type="text"
											value="<?php echo esc_attr( stripslashes( $table_obj->autoalignment_affected_rows_right ) ); ?>"
											id="autoalignment-affected-rows-right" maxlength="2000"
											size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php echo esc_attr__( 'Enter a list of row indexes, separated by a comma, where the right alignment should be applied.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Autoalignment Affected Columns Left -->
							<tr class="autoalignment-options">
								<th><label
											for="autoalignment-affected-columns-left"><?php echo esc_html_e( 'Left (Columns)', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input type="text"
											value="<?php echo esc_attr( stripslashes( $table_obj->autoalignment_affected_columns_left ) ); ?>"
											id="autoalignment-affected-columns-left" maxlength="110"
											size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php echo esc_attr__( 'Enter a list of column indexes, separated by a comma, where the left alignment should be applied.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Autoalignment Affected Columns Center -->
							<tr class="autoalignment-options">
								<th><label
											for="autoalignment-affected-columns-center"><?php echo esc_html_e( 'Center (Columns)', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input type="text"
											value="<?php echo esc_attr( stripslashes( $table_obj->autoalignment_affected_columns_center ) ); ?>"
											id="autoalignment-affected-columns-center" maxlength="110"
											size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php echo esc_attr__( 'Enter a list of column indexes, separated by a comma, where the center alignment should be applied.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Autoalignment Affected Columns Right -->
							<tr class="autoalignment-options">
								<th><label
											for="autoalignment-affected-columns-right"><?php echo esc_html_e( 'Right (Columns)', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input type="text"
											value="<?php echo esc_attr( stripslashes( $table_obj->autoalignment_affected_columns_right ) ); ?>"
											id="autoalignment-affected-columns-right" maxlength="110"
											size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php echo esc_attr__( 'Enter a list of column indexes, separated by a comma, where the right alignment should be applied.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Responsive Options ---------------------------------------------- -->
							<tr class="group-trigger" data-trigger-target="responsive-options">
								<th
									class="group-title"><?php esc_attr_e( 'Responsive', 'league-table-lite' ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- Tablet Breakpoint -->
							<tr class="responsive-options">
								<th><label
											for="tablet-breakpoint"><?php esc_html_e( 'Tablet Breakpoint', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input value="<?php echo intval( $table_obj->tablet_breakpoint, 10 ); ?>" type="text"
											id="tablet-breakpoint" maxlength="6" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'When the browser viewport width goes below this value the device will be considered a tablet.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Hide Tablet List -->
							<tr class="responsive-options">
								<th><label
											for="hide-tablet-list"><?php esc_html_e( 'Tablet Hide List', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input value="<?php echo esc_attr( stripslashes( $table_obj->hide_tablet_list ) ); ?>" type="text"
											id="hide-tablet-list" maxlength="110" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Enter a list of column indexes, separated by a comma, that you want to hide when the device is a tablet.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Tablet Header Font Size -->
							<tr class="responsive-options">
								<th><label
											for="tablet-header-font-size"><?php esc_html_e( 'Tablet Header Font Size', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input value="<?php echo intval( $table_obj->tablet_header_font_size, 10 ); ?>"
											type="text" id="tablet-header-font-size" maxlength="6" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Enter the font size applied to the cells in the table header when the device is a tablet.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Tablet Body Font Size -->
							<tr class="responsive-options">
								<th><label
											for="tablet-body-font-size"><?php esc_html_e( 'Tablet Body Font Size', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input value="<?php echo intval( $table_obj->tablet_body_font_size, 10 ); ?>"
											type="text" id="tablet-body-font-size" maxlength="6" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Enter the font size applied to the cells in the table body when the device is a tablet.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Tablet Hide Images -->
							<tr class="responsive-options">
								<th><label
											for="tablet-hide-images"><?php esc_html_e( 'Tablet Hide Images', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="tablet-hide-images">
										<option
												value="0" <?php selected( intval( $table_obj->tablet_hide_images, 10 ), 0 ); ?>><?php esc_attr_e( 'No', 'league-table-lite' ); ?></option>
										<option
												value="1" <?php selected( intval( $table_obj->tablet_hide_images, 10 ), 1 ); ?>><?php esc_attr_e( 'Yes', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'Select "Yes" if you want to hide all the images included in the cells when the device is a tablet.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Phone Breakpoint -->
							<tr class="responsive-options">
								<th><label
											for="phone-breakpoint"><?php esc_html_e( 'Phone Breakpoint', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input value="<?php echo intval( $table_obj->phone_breakpoint, 10 ); ?>" type="text"
											id="phone-breakpoint" maxlength="6" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'When the browser viewport width goes below this value the device will be considered a phone.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Hide Phone List -->
							<tr class="responsive-options">
								<th><label
											for="hide-phone-list"><?php esc_html_e( 'Phone Hide List', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input value="<?php echo esc_attr( stripslashes( $table_obj->hide_phone_list ) ); ?>" type="text"
											id="hide-phone-list" maxlength="110" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Enter a list of column indexes, separated by a comma, that you want to hide when the device is a phone.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Phone Header Font Size -->
							<tr class="responsive-options">
								<th><label
											for="phone-header-font-size"><?php esc_html_e( 'Phone Header Font Size', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input value="<?php echo intval( $table_obj->phone_header_font_size, 10 ); ?>"
											type="text" id="phone-header-font-size" maxlength="6" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Enter the font size applied to the cells in the table header when the device is a phone.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Phone Body Font Size -->
							<tr class="responsive-options">
								<th><label
											for="phone-body-font-size"><?php esc_html_e( 'Phone Body Font Size', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input value="<?php echo intval( $table_obj->phone_body_font_size, 10 ); ?>"
											type="text"
											id="phone-body-font-size" maxlength="6" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Enter the font size applied to the cells in the table body when the device is a phone.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Phone Hide Images -->
							<tr class="responsive-options">
								<th><label
											for="phone-hide-images"><?php esc_html_e( 'Phone Hide Images', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="phone-hide-images">
										<option
												value="0" <?php selected( intval( $table_obj->phone_hide_images, 10 ), 0 ); ?>><?php esc_html_e( 'No', 'league-table-lite' ); ?></option>
										<option
												value="1" <?php selected( intval( $table_obj->phone_hide_images, 10 ), 1 ); ?>><?php esc_html_e( 'Yes', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'Select "Yes" if you want to hide all the images included in the cells when the device is a phone.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Advanced Options ---------------------------------------------- -->
							<tr class="group-trigger" data-trigger-target="advanced-options">
								<th
									class="group-title"><?php esc_html_e( 'Advanced', 'league-table-lite' ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- Enable Cell Properties -->
							<tr class="advanced-options">
								<th><label
											for="enable-cell-properties"><?php esc_html_e( 'Enable Cell Properties', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="enable-cell-properties">
										<option
												value="0" <?php selected( intval( $table_obj->enable_cell_properties, 10 ), 0 ); ?>><?php esc_html_e( 'No', 'league-table-lite' ); ?></option>
										<option
												value="1" <?php selected( intval( $table_obj->enable_cell_properties, 10 ), 1 ); ?>><?php esc_html_e( 'Yes', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'Select "Yes" to use the cell properties or "No" to ignore the cell properties and improve the performance. When the cell properties are disabled the time required to generate the table HTML with PHP and the time required to render the table with the browser will be reduced.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

						</table>

						<!-- Submit Button -->
						<div class="daext-form-action">
							<input id="save" data-reload-menu="0" class="button" type="submit"
									value="<?php esc_attr_e( 'Update Table', 'league-table-lite' ); ?>">
							<input id="close" data-reload-menu="0" class="button" type="submit"
									value="<?php esc_attr_e( 'Close', 'league-table-lite' ); ?>">
						</div>

					</div>

				<?php else : ?>

					<!-- Create New Table -->

					<?php

					// create temporary table in db table.
					$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . '_table';
					$safe_sql   = $wpdb->prepare(
						"INSERT INTO {$wpdb->prefix}daextletal_table SET
	                        temporary = %d,
	                        name = %s,
	                        `rows` = %d,
	                        columns = %d",
						1,
						'[TEMPORARY]',
						10,
						10
					);
					$result     = $wpdb->query( $safe_sql ); // phpcs:ignore

					// get the automatic id of the inserted element.
					$temporary_table_id = $wpdb->insert_id;

					// initialize the data based on the initial number of rows and columns.
					$this->initialize_table_data( $temporary_table_id, 11, 10 );

					?>

					<div class="daext-form-container">

						<div class="daext-form-title"><?php esc_html_e( 'Create Table', 'league-table-lite' ); ?></div>

						<table class="daext-form daext-form-table">

							<input type="hidden" id="temporary-table-id"
									value="<?php echo esc_attr( $temporary_table_id ); ?>"/>

							<!-- Name -->
							<tr>
								<th><label for="name"><?php esc_html_e( 'Name', 'league-table-lite' ); ?></label></th>
								<td>
									<input type="text" id="name" maxlength="255" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'The name of the table.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Description -->
							<tr>
								<th><label
											for="description"><?php esc_html_e( 'Description', 'league-table-lite' ); ?></label></th>
								<td>
									<input type="text" id="description" maxlength="255" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'The description of the table.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Rows -->
							<tr>
								<th><label for="rows"><?php esc_html_e( 'Rows', 'league-table-lite' ); ?></label></th>
								<td>
									<input type="text" value="10" id="rows"
											maxlength="5" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'To define the number of rows please enter a number included between 1 and 10000.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Columns -->
							<tr>
								<th><label for="columns"><?php esc_html_e( 'Columns', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input type="text" value="10" id="columns"
											maxlength="2" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'To define the number of columns please enter a number included between 1 and 40.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<tr valign="top">
								<th><label for="data"><?php esc_html_e( 'Data', 'league-table-lite' ); ?></label></th>
								<td id="daextletal-table-td">
									<div id="daextletal-table"></div>
								</td>
							</tr>

							<!-- Sorting Options ---------------------------------------------- -->
							<tr class="group-trigger" data-trigger-target="sorting-options">
								<th class="group-title"><?php esc_html_e( 'Sorting', 'league-table-lite' ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- Enable Sorting -->
							<tr class="sorting-options">
								<th><label
											for="enable-sorting"><?php esc_html_e( 'Enable Sorting', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="enable-sorting">
										<option value="0"><?php esc_html_e( 'No', 'league-table-lite' ); ?></option>
										<option value="1"><?php esc_html_e( 'Yes', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'With this option enabled the table will be sorted based on the criteria defined in this section.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Enable Manual Sorting -->
							<tr class="sorting-options">
								<th><label
											for="enable-manual-sorting"><?php esc_html_e( 'Enable Manual Sorting', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="enable-manual-sorting">
										<option value="0"><?php esc_html_e( 'No', 'league-table-lite' ); ?></option>
										<option value="1"><?php esc_html_e( 'Yes', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'This option enables the users to manually sort the table by clicking on the table header. Please note that manual sorting will not be applied if the "Enable Sorting" option is disabled.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Show Position -->
							<tr class="sorting-options">
								<th><label
											for="show-position"><?php esc_html_e( 'Show Position', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="show-position">
										<option value="0" selected="selected"><?php esc_html_e( 'No', 'league-table-lite' ); ?></option>
										<option value="1"><?php esc_html_e( 'Yes', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'With this option enabled the position column will be automatically generated.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Position Side -->
							<tr class="sorting-options">
								<th><label
											for="position-side"><?php esc_html_e( 'Position Side', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="position-side">
										<option value="left"
												selected="selected"><?php esc_html_e( 'Left', 'league-table-lite' ); ?></option>
										<option value="right"><?php esc_html_e( 'Right', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'Select if the position column should be generated on the left side or on the right side of the table.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Position Label -->
							<tr class="sorting-options">
								<th><label
											for="position-label"><?php esc_html_e( 'Position Label', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input type="text" value="#" id="position-label" maxlength="255" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Enter the text that should be displayed in the header of the position column.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Number Format -->
							<tr class="sorting-options">
								<th><label
											for="number-format"><?php esc_html_e( 'Number Format', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="number-format">
										<option value="0"
												selected="selected"><?php esc_html_e( 'EU', 'league-table-lite' ); ?></option>
										<option value="1"><?php esc_html_e( 'US', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'Select if the decimal mark (the symbol used to separate the integer part from the fractional part of a decimal number) in use is the comma (EU) or the point (US). This option affects how the "Currency" data type is sorted.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Order Desc Asc -->
							<tr class="sorting-options">
								<th><label
											for="order-desc-asc"><?php echo esc_html_e( 'Order', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="order-desc-asc">
										<option value="0"><?php esc_html_e( 'Disabled', 'league-table-lite' ); ?></option>
										<option value="1"><?php esc_html_e( 'Descending', 'league-table-lite' ); ?></option>
										<option value="2"><?php esc_html_e( 'Ascending', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'This option allows you to enable (in descending or ascending order) or disable the order.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Order By -->
							<tr class="sorting-options">
								<th><label
											for="order-by"><?php echo esc_html_e( 'Order By', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="order-by">
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'This option allows you to determine for which column the order should be applied.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Order Data Type -->
							<tr class="sorting-options">
								<th><label
											for="order-data-type"><?php echo esc_html_e( 'Order Data Type', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="order-data-type">
										<option value="auto"><?php esc_html_e( 'Auto', 'league-table-lite' ); ?></option>
										<option value="text"><?php esc_html_e( 'Text', 'league-table-lite' ); ?></option>
										<option value="digit"><?php esc_html_e( 'Digit', 'league-table-lite' ); ?></option>
										<option value="percent"><?php esc_html_e( 'Percent', 'league-table-lite' ); ?></option>
										<option value="currency"><?php esc_html_e( 'Currency', 'league-table-lite' ); ?></option>
										<option value="url"><?php esc_html_e( 'URL', 'league-table-lite' ); ?></option>
										<option value="time"><?php esc_html_e( 'Time', 'league-table-lite' ); ?></option>
										<option value="isoDate"><?php esc_html_e( 'ISO Date', 'league-table-lite' ); ?></option>
										<option
												value="usLongDate"><?php esc_html_e( 'US Long Date', 'league-table-lite' ); ?></option>
										<option value="shortDate"><?php esc_html_e( 'Short Date', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'Select the type of data included in the column that determines the order. Please note that if you leave "Auto" the type of data will be automatically determined by the sorting system.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Order Date Format -->
							<tr class="sorting-options">
								<th><label
											for="order-date-format"><?php echo esc_html_e( 'Order Date Format', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="order-date-format">
										<option value="ddmmyyyy"><?php esc_html_e( 'DDMMYYYY', 'league-table-lite' ); ?></option>
										<option value="yyyymmdd"><?php esc_html_e( 'YYYYMMDD', 'league-table-lite' ); ?></option>
										<option value="mmddyyyy"><?php esc_html_e( 'MMDDYYYY', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title="<?php echo esc_attr_e( 'Use this option to set the data format of the column that determines the order. Please note that this option will be considered only if the corresponding "Order Data Type" option is set to "Short Date".', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Style Options ---------------------------------------------- -->
							<tr class="group-trigger" data-trigger-target="style-options">
								<th class="group-title"><?php esc_html_e( 'Style', 'league-table-lite' ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- Table Layout -->
							<tr class="style-options">
								<th><label for="table-layout"><?php esc_html_e( 'Table Layout', 'league-table-lite' ); ?></label></th>
								<td>
									<select id="table-layout">
										<option value="0"><?php esc_html_e( 'Auto', 'league-table-lite' ); ?></option>
										<option value="1"><?php esc_html_e( 'Fixed', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'Select the algorithm used to lay out the table.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Table Width -->
							<tr class="style-options">
								<th><label
											for="table-width"><?php esc_html_e( 'Table Width', 'league-table-lite' ); ?></label></th>
								<td>
									<select id="table-width">
										<option value="0"><?php esc_html_e( 'Full Width', 'league-table-lite' ); ?></option>
										<option value="1"><?php esc_html_e( 'Auto', 'league-table-lite' ); ?></option>
										<option value="2"><?php esc_html_e( 'Specified Value', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'With "Full Width" the table width will be equal to the width of the container, with "Auto" the table width will be determined automatically based on the table content, with "Specified Value" the table width will be determined by the value entered in the "Table Width Value" field.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Table Width Value -->
							<tr class="style-options">
								<th><label
											for="table-width-value"><?php esc_html_e( 'Table Width Value', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input type="text" value="400" id="table-width-value" maxlength="6" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Set the width of the table. Please note that this option will be used only if the "Table Width" option is set to "Specified Value".', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Table Minimum Width -->
							<tr class="style-options">
								<th><label
											for="table-minimum-width"><?php esc_html_e( 'Table Minimum Width', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input type="text" value="0" id="table-minimum-width" maxlength="6" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Set the minimum width of the table.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Column Width -->
							<tr class="style-options">
								<th><label
											for="column-width"><?php esc_html_e( 'Column Width', 'league-table-lite' ); ?></label></th>
								<td>
									<select id="column-width">
										<option value="0"><?php esc_html_e( 'Auto', 'league-table-lite' ); ?></option>
										<option value="1"><?php esc_html_e( 'Specified Value', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'Select if the width of the columns should be automatically determined or based on the values provided in the "Column Width Value" field.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Column Width Value -->
							<tr class="style-options">
								<th><label
											for="column-width-value"><?php esc_html_e( 'Column Width Value', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input type="text" value="100" id="column-width-value" maxlength="2000" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Enter a list of column widths, separated by a comma. If only one column width is provided the single value will be applied to all the columns.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Enable Container -->
							<tr class="style-options">
								<th><label
											for="enable-container"><?php esc_html_e( 'Enable Container', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="enable-container">
										<option value="0"><?php esc_html_e( 'No', 'league-table-lite' ); ?></option>
										<option value="1"><?php esc_html_e( 'Yes', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'Enable this option to include the table in a container. With this feature enabled and the proper values in the "Container Width" and "Container Height" options you will be able to generate a table with an horizontal and/or a vertical scrolling bar.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Container Width -->
							<tr class="style-options">
								<th><label
											for="container-width"><?php esc_html_e( 'Container Width', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input type="text" value="400" id="container-width" maxlength="6" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Enter the width of the container or 0 if you want the width of the container automatically determined. This option will be considered only if the "Enable Container" option is enabled.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Container Height -->
							<tr class="style-options">
								<th><label
											for="container-height"><?php esc_html_e( 'Container Height', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input type="text" value="400" id="container-height" maxlength="6" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Enter the height of the container or 0 if you want the height of the container automatically determined. This option will be considered only if the "Enable Container" option is enabled.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Table Margin Top -->
							<tr class="style-options">
								<th><label
											for="table-margin-top"><?php esc_html_e( 'Table Margin Top', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input type="text" value="20" id="table-margin-top" maxlength="6" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Set the top margin of the table.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Table Margin Bottom -->
							<tr class="style-options">
								<th><label
											for="table-margin-bottom"><?php esc_html_e( 'Table Margin Bottom', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input type="text" value="20" id="table-margin-bottom" maxlength="6" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Set the bottom margin of the table.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Show Header -->
							<tr class="style-options">
								<th><label
											for="show-header"><?php esc_html_e( 'Show Header', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="show-header">
										<option value="0"><?php esc_html_e( 'No', 'league-table-lite' ); ?></option>
										<option value="1"
												selected="selected"><?php esc_html_e( 'Yes', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'With this option enabled the table header will be displayed.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Header Font Size -->
							<tr class="style-options">
								<th><label
											for="header-font-size"><?php esc_html_e( 'Header Font Size', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input type="text" value="11" id="header-font-size" maxlength="6" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Set the font size of the text displayed in the header.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Header Font Family -->
							<tr class="style-options">
								<th><label
											for="header-font-family"><?php esc_html_e( 'Header Font Family', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input type="text" value="'Open Sans', Helvetica, Arial, sans-serif"
											id="header-font-family" maxlength="255" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Set the font family of the text displayed in the header.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Header Font Weight -->
							<tr class="style-options">
								<th><label
											for="header-font-style"><?php esc_html_e( 'Header Font Weight', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="header-font-weight">
										<option value="100"><?php esc_html_e( '100', 'league-table-lite' ); ?></option>
										<option value="200"><?php esc_html_e( '200', 'league-table-lite' ); ?></option>
										<option value="300"><?php esc_html_e( '300', 'league-table-lite' ); ?></option>
										<option value="400"
												selected="selected"><?php esc_html_e( '400', 'league-table-lite' ); ?></option>
										<option value="500"><?php esc_html_e( '500', 'league-table-lite' ); ?></option>
										<option value="600"><?php esc_html_e( '600', 'league-table-lite' ); ?></option>
										<option value="700"><?php esc_html_e( '700', 'league-table-lite' ); ?></option>
										<option value="800"><?php esc_html_e( '800', 'league-table-lite' ); ?></option>
										<option value="900"><?php esc_html_e( '900', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'Set the font weight of the text displayed in the header.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Header Font Style -->
							<tr class="style-options">
								<th><label
											for="header-font-style"><?php esc_html_e( 'Header Font Style', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="header-font-style">
										<option value="normal"
												selected="selected"><?php esc_html_e( 'Normal', 'league-table-lite' ); ?></option>
										<option value="italic"><?php esc_html_e( 'Italic', 'league-table-lite' ); ?></option>
										<option value="oblique"><?php esc_html_e( 'Oblique', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'Set the font style of the text displayed in the header.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Header Position Alignment -->
							<tr class="style-options">
								<th><label
											for="header-position-alignment"><?php esc_html_e( 'Header Position Alignment', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="header-position-alignment">
										<option value="left"><?php esc_html_e( 'Left', 'league-table-lite' ); ?></option>
										<option value="center" selected="selected"><?php esc_html_e( 'Center', 'league-table-lite' ); ?></option>
										<option value="right"><?php esc_html_e( 'Right', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'The alignment of the text displayed in the header of the position column.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Header Background Color -->
							<tr class="style-options">
								<th><label
											for="header-background-color"><?php esc_html_e( 'Header Background Color', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input class="wp-color-picker" type="text" value="#C3512F"
											id="header-background-color"
											maxlength="7" size="30"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Set the background color of the header.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Header Font Color -->
							<tr class="style-options">
								<th><label
											for="header-font-color"><?php esc_html_e( 'Header Font Color', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input class="wp-color-picker" type="text" value="#FFFFFF" id="header-font-color"
											maxlength="7" size="30"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Set the color of the text displayed in the header.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Header Link Color -->
							<tr class="style-options">
								<th><label
											for="header-link-color"><?php esc_html_e( 'Header Link Color', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input class="wp-color-picker" type="text" value="#FFFFFF" id="header-link-color"
											maxlength="7" size="30"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Set the color of the text used for the links in the header.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Header Border Color -->
							<tr class="style-options">
								<th><label
											for="header-border-color"><?php esc_html_e( 'Header Border Color', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input class="wp-color-picker" type="text" value="#B34A2A" id="header-border-color"
											maxlength="7" size="30"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Set the color of the borders in the header.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Body Font Size -->
							<tr class="style-options">
								<th><label
											for="body-font-size"><?php esc_html_e( 'Body Font Size', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input type="text" value="11" id="body-font-size" maxlength="6" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Set the font size of the text displayed in the body.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Body Font Family -->
							<tr class="style-options">
								<th><label
											for="body-font-family"><?php esc_html_e( 'Body Font Family', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input type="text" value="'Open Sans', Helvetica, Arial, sans-serif"
											id="body-font-family" maxlength="255" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Set the font family of the text displayed in the body.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Body Font Weight -->
							<tr class="style-options">
								<th><label
											for="body-font-weight"><?php esc_html_e( 'Body Font Weight', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="body-font-weight">
										<option value="100"><?php esc_html_e( '100', 'league-table-lite' ); ?></option>
										<option value="200"><?php esc_html_e( '200', 'league-table-lite' ); ?></option>
										<option value="300"><?php esc_html_e( '300', 'league-table-lite' ); ?></option>
										<option value="400"
												selected="selected"><?php esc_html_e( '400', 'league-table-lite' ); ?></option>
										<option value="500"><?php esc_html_e( '500', 'league-table-lite' ); ?></option>
										<option value="600"><?php esc_html_e( '600', 'league-table-lite' ); ?></option>
										<option value="700"><?php esc_html_e( '700', 'league-table-lite' ); ?></option>
										<option value="800"><?php esc_html_e( '800', 'league-table-lite' ); ?></option>
										<option value="900"><?php esc_html_e( '900', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'Set the font weight of the text displayed in the body.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Body Font Style -->
							<tr class="style-options">
								<th><label
											for="body-font-style"><?php esc_html_e( 'Body Font Style', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="body-font-style">
										<option value="normal"
												selected="selected"><?php esc_html_e( 'Normal', 'league-table-lite' ); ?></option>
										<option value="italic"><?php esc_html_e( 'Italic', 'league-table-lite' ); ?></option>
										<option value="oblique"><?php esc_html_e( 'Oblique', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'Set the font style of the text displayed in the body.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Even Rows Bg Color -->
							<tr class="style-options">
								<th><label
											for="even-rows-background-color"><?php esc_html_e( 'Even Rows Background Color', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input class="wp-color-picker" type="text" value="#FFFFFF"
											id="even-rows-background-color" maxlength="7" size="30"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Set the background color of the even rows.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Odd Rows Bg Color -->
							<tr class="style-options">
								<th><label
											for="odd-rows-background-color"><?php esc_html_e( 'Odd Rows Background Color', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input class="wp-color-picker" type="text" value="#FCFCFC"
											id="odd-rows-background-color" maxlength="7" size="30"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Set the background color of the odd rows.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Even Rows Font Color -->
							<tr class="style-options">
								<th><label
											for="even-rows-font-color"><?php esc_html_e( 'Even Rows Font Color', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input class="wp-color-picker" type="text" value="#666666" id="even-rows-font-color"
											maxlength="7" size="30"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Set the color of the text displayed in the even rows.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Odd Rows Font Color -->
							<tr class="style-options">
								<th><label
											for="odd-rows-font-color"><?php esc_html_e( 'Odd Rows Font Color', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input class="wp-color-picker" type="text" value="#666666" id="odd-rows-font-color"
											maxlength="7" size="30"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Set the color of the text displayed in the odd rows.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Even Rows Link Color -->
							<tr class="style-options">
								<th><label
											for="even-rows-link-color"><?php esc_html_e( 'Even Rows Link Color', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input class="wp-color-picker" type="text" value="#C3512F" id="even-rows-link-color"
											maxlength="7" size="30"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Set the color of the text displayed for the links of the even rows.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Odd Rows Link Color -->
							<tr class="style-options">
								<th><label
											for="odd-rows-link-color"><?php esc_html_e( 'Odd Rows Link Color', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input class="wp-color-picker" type="text" value="#C3512F" id="odd-rows-link-color"
											maxlength="7" size="30"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Set the color of the text displayed for the links of the odd rows.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Rows Border Color -->
							<tr class="style-options">
								<th><label
											for="rows-border-color"><?php esc_html_e( 'Rows Border Color', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input class="wp-color-picker" type="text" value="#E1E1E1" id="rows-border-color"
											maxlength="7" size="30"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Set the border color of the rows.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Autoalignment Options ---------------------------------------------- -->
							<tr class="group-trigger" data-trigger-target="autoalignment-options">
								<th
									class="group-title"><?php esc_html_e( 'Alignment', 'league-table-lite' ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- Autoalignment Priority -->
							<tr class="autoalignment-options">
								<th><label
											for="autoalignment-priority"><?php esc_html_e( 'Priority', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="autoalignment-priority">
										<option value="rows"><?php esc_html_e( 'Rows', 'league-table-lite' ); ?></option>
										<option value="columns"><?php esc_html_e( 'Columns', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'This option determines which category of alignment has the priority.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Autoalignment Affected Rows Left -->
							<tr class="autoalignment-options">
								<th><label
											for="autoalignment-affected-rows-left"><?php echo esc_html_e( 'Left (Rows)', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input type="text" value="" id="autoalignment-affected-rows-left" maxlength="2000"
											size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php echo esc_attr__( 'Enter a list of row indexes, separated by a comma, where the left alignment should be applied.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Autoalignment Affected Rows Center -->
							<tr class="autoalignment-options">
								<th><label
											for="autoalignment-affected-rows-center"><?php echo esc_html_e( 'Center (Rows)', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input type="text" value="" id="autoalignment-affected-rows-center" maxlength="2000"
											size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php echo esc_attr__( 'Enter a list of row indexes, separated by a comma, where the center alignment should be applied.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Autoalignment Affected Rows Right -->
							<tr class="autoalignment-options">
								<th><label
											for="autoalignment-affected-rows-right"><?php echo esc_html_e( 'Right (Rows)', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input type="text" value="" id="autoalignment-affected-rows-right" maxlength="2000"
											size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php echo esc_attr__( 'Enter a list of row indexes, separated by a comma, where the right alignment should be applied.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Autoalignment Affected Columns Left -->
							<tr class="autoalignment-options">
								<th><label
											for="autoalignment-affected-columns-left"><?php echo esc_html_e( 'Left (Columns)', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input type="text" value="" id="autoalignment-affected-columns-left" maxlength="110"
											size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php echo esc_attr__( 'Enter a list of column indexes, separated by a comma, where the left alignment should be applied.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Autoalignment Affected Columns Center -->
							<tr class="autoalignment-options">
								<th><label
											for="autoalignment-affected-columns-center"><?php echo esc_html_e( 'Center (Columns)', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input type="text" value="" id="autoalignment-affected-columns-center"
											maxlength="110"
											size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php echo esc_attr__( 'Enter a list of column indexes, separated by a comma, where the center alignment should be applied.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Autoalignment Affected Columns Right -->
							<tr class="autoalignment-options">
								<th><label
											for="autoalignment-affected-columns-right"><?php echo esc_html_e( 'Right (Columns)', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input type="text" value="" id="autoalignment-affected-columns-right"
											maxlength="110"
											size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php echo esc_attr__( 'Enter a list of column indexes, separated by a comma, where the right alignment should be applied.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Responsive Options ---------------------------------------------- -->
							<tr class="group-trigger" data-trigger-target="responsive-options">
								<th
									class="group-title"><?php esc_html_e( 'Responsive', 'league-table-lite' ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- Tablet Breakpoint -->
							<tr class="responsive-options">
								<th><label
											for="tablet-breakpoint"><?php esc_html_e( 'Tablet Breakpoint', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input type="text" value="989" id="tablet-breakpoint" maxlength="6" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'When the browser viewport width goes below this value the device will be considered a tablet.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Hide Tablet List -->
							<tr class="responsive-options">
								<th><label
											for="hide-tablet-list"><?php esc_html_e( 'Hide Tablet List', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input type="text" value="" id="hide-tablet-list" maxlength="110" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Enter a list of column indexes, separated by a comma, that you want to hide when the device is a tablet.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Tablet Header Font Size -->
							<tr class="responsive-options">
								<th><label
											for="tablet-header-font-size"><?php esc_html_e( 'Tablet Header Font Size', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input type="text" value="11" id="tablet-header-font-size" maxlength="6" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Enter the font size applied to the cells in the table header when the device is a tablet.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Tablet Body Font Size -->
							<tr class="responsive-options">
								<th><label
											for="tablet-body-font-size"><?php esc_html_e( 'Tablet Body Font Size', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input type="text" value="11" id="tablet-body-font-size" maxlength="6" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Enter the font size applied to the cells in the table body when the device is a tablet.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Tablet Hide Images -->
							<tr class="responsive-options">
								<th><label
											for="tablet-hide-images"><?php esc_html_e( 'Tablet Hide Images', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="tablet-hide-images">
										<option value="0"><?php esc_html_e( 'No', 'league-table-lite' ); ?></option>
										<option value="1"><?php esc_html_e( 'Yes', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'Select "Yes" if you want to hide all the images included in the cells when the device is a tablet.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Phone Breakpoint -->
							<tr class="responsive-options">
								<th><label
											for="phone-breakpoint"><?php esc_html_e( 'Phone Breakpoint', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input type="text" value="479" id="phone-breakpoint" maxlength="6" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'When the browser viewport width goes below this value the device will be considered a phone.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Hide Phone List -->
							<tr class="responsive-options">
								<th><label
											for="hide-phone-list"><?php esc_html_e( 'Hide Phone List', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input type="text" value="" id="hide-phone-list" maxlength="110" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Enter a list of column indexes, separated by a comma, that you want to hide when the device is a phone.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Phone Header Font Size -->
							<tr class="responsive-options">
								<th><label
											for="phone-header-font-size"><?php esc_html_e( 'Phone Header Font Size', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input type="text" value="11" id="phone-header-font-size" maxlength="6" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Enter the font size applied to the cells in the table header when the device is a phone.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Phone Body Font Size -->
							<tr class="responsive-options">
								<th><label
											for="phone-body-font-size"><?php esc_html_e( 'Phone Body Font Size', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<input type="text" value="11" id="phone-body-font-size" maxlength="6" size="30" autocomplete="off"/>
									<div class="help-icon"
										title="<?php esc_attr_e( 'Enter the font size applied to the cells in the table body when the device is a phone.', 'league-table-lite' ); ?>"></div>
								</td>
							</tr>

							<!-- Phone Hide Images -->
							<tr class="responsive-options">
								<th><label
											for="phone-hide-images"><?php esc_html_e( 'Phone Hide Images', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="phone-hide-images">
										<option value="0"><?php esc_attr_e( 'No', 'league-table-lite' ); ?></option>
										<option value="1"><?php esc_attr_e( 'Yes', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'Select "Yes" if you want to hide all the images included in the cells when the device is a phone.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

							<!-- Advanced Options ---------------------------------------------- -->
							<tr class="group-trigger" data-trigger-target="advanced-options">
								<th
									class="group-title"><?php esc_html_e( 'Advanced', 'league-table-lite' ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- Enable Cell Properties -->
							<tr class="advanced-options">
								<th><label
											for="enable-cell-properties"><?php esc_html_e( 'Enable Cell Properties', 'league-table-lite' ); ?></label>
								</th>
								<td>
									<select id="enable-cell-properties">
										<option value="0"><?php esc_html_e( 'No', 'league-table-lite' ); ?></option>
										<option value="1"
												selected="selected"><?php esc_html_e( 'Yes', 'league-table-lite' ); ?></option>
									</select>
									<div class="help-icon"
										title='<?php esc_attr_e( 'Select "Yes" to use the cell properties or "No" to ignore the cell properties and improve the performance. When the cell properties are disabled the time required to generate the table HTML with PHP and the time required to render the table with the browser will be reduced.', 'league-table-lite' ); ?>'></div>
								</td>
							</tr>

						</table>

						<!-- Submit Button -->
						<div class="daext-form-action">
							<input id="save" data-reload-menu="1" class="button" type="submit"
									value="<?php esc_attr_e( 'Add Table', 'league-table-lite' ); ?>">
						</div>

					</div>

				<?php endif; ?>

				<div id="table-error" class="error settings-error notice below-h2"><p></p></div>
				<div id="table-success" class="updated settings-error notice below-h2"><p></p></div>

			<?php endif; ?>

		</div>

		<div id="sidebar-container">

			<div class="daext-form-container">

				<h3 class="daext-form-title" id="cell-properties-title"><?php esc_html_e( 'Body', 'league-table-lite' ); ?>
					&nbsp1:1</h3>

				<table class="daext-form daext-form-cell-properties">

					<tbody>

					<!-- Cell Index Hidden Fields -->
					<input type="hidden" id="cell-property-row-index" value="1">
					<input type="hidden" id="cell-property-column-index" value="0">

					<!-- Link -->
					<tr class="cell-property">
						<th><label for="cell-property-link"><?php esc_html_e( 'Link', 'league-table-lite' ); ?></label>
						</th>
						<td>
							<input maxlength="2083" type="text" id="cell-property-link" autocomplete="off">
							<div class="help-icon"
								title="<?php esc_attr_e( 'Enter an URL to link the text of the cell to a specific destination.', 'league-table-lite' ); ?>"></div>
						</td>
					</tr>

					<!-- Image Left -->
					<tr class="cell-property">
						<th><label><?php esc_html_e( 'Image Left', 'league-table-lite' ); ?></label>
						</th>
						<td>

							<div class="image-uploader">
								<div class="image-container">
									<img class="selected-image" src="" style="display: none">
								</div>
								<input type="hidden" id="cell-property-image-left">
								<a class="button_add_media" data-set-remove="set"
									data-set="<?php esc_attr_e( 'Set image', 'league-table-lite' ); ?>"
									data-remove="<?php esc_attr_e( 'Remove Image', 'league-table-lite' ); ?>"><?php esc_attr_e( 'Set image', 'league-table-lite' ); ?></a>
								<p class="description"><?php esc_attr_e( 'Select an image that should be placed on the left of the text.', 'league-table-lite' ); ?></p>
							</div>

						</td>
					</tr>

					<!-- Image Right -->
					<tr class="cell-property">
						<th><label><?php esc_html_e( 'Image Right', 'league-table-lite' ); ?></label>
						</th>
						<td>

							<div class="image-uploader">
								<div class="image-container">
									<img class="selected-image" src="" style="display: none">
								</div>
								<input type="hidden" id="cell-property-image-right">
								<a class="button_add_media" data-set-remove="set"
									data-set="<?php esc_attr_e( 'Set image', 'league-table-lite' ); ?>"
									data-remove="<?php esc_attr_e( 'Remove Image', 'league-table-lite' ); ?>"><?php esc_html_e( 'Set image', 'league-table-lite' ); ?></a>
								<p class="description"><?php esc_attr_e( 'Select an image that should be placed on the right of the text.', 'league-table-lite' ); ?></p>
							</div>

						</td>
					</tr>

					</tbody>

				</table>

				<!-- submit button -->
				<div class="daext-form-action">
					<input id="update-cell-properties" data-action="" class="update-reset-cell-properties button"
							type="submit" value="">
					<input id="reset-cell-properties" class="update-reset-cell-properties button" type="submit"
							value="<?php esc_attr_e( 'Reset Cell Properties', 'league-table-lite' ); ?>">
				</div>

			</div>

			<div id="cell-properties-error-message" class="error settings-error notice below-h2"><p></p></div>
			<div id="cell-properties-added-updated-message" class="updated settings-error notice below-h2"><p></p></div>

		</div>

	</div>

	<!-- Dialog Keyboard Shortcut -->
	<div class="dialog-alert daext-display-none" data-id="dialog-keyboard-shortcut" title="<?php esc_attr_e( 'Please use the keyboard shortcut', 'league-table-lite' ); ?>">
		<p><?php esc_html_e( 'Due to security reason, modern browsers disallow to read from the system clipboard:', 'league-table-lite' ); ?></p>
		<p><?php echo 'https://www.w3.org/TR/clipboard-apis/#privacy'; ?></p>
		<p><?php esc_html_e( 'Please use Ctrl+V (Windows/Linux) or Command+V (Mac) to perform this operation.', 'league-table-lite' ); ?></p>
	</div>

	<!-- Valid Cell Number -->
	<div class="dialog-alert daext-display-none" data-id="valid-cell-number" title="<?php esc_attr_e( 'Please reduce the number of select cells', 'league-table-lite' ); ?>">
		<p><?php esc_html_e( 'For performance reasons, the maximum number of cells allowed in this context menu operation is equal to 100.', 'league-table-lite' ); ?></p>
		<p><?php esc_html_e( 'Please reduce the number of selected cells to perform this operation.', 'league-table-lite' ); ?></p>
	</div>

	<!-- Specific Shortcut Disabled -->
	<div class="dialog-alert daext-display-none" data-id="specific-shortcut-disabled" title="<?php esc_attr_e( 'Please use the context menu', 'league-table-lite' ); ?>">
		<p><?php esc_html_e( 'Specific keyboard shortcuts are disabled on the spreadsheet editor.', 'league-table-lite' ); ?></p>
		<p><?php esc_html_e( 'Please click the right mouse button and use the context menu.', 'league-table-lite' ); ?></p>
	</div>

	<!-- Dialog Confirm -->
	<div id="dialog-confirm" title="<?php esc_attr_e( 'Delete the table?', 'league-table-lite' ); ?>" class="display-none">
		<p><?php esc_html_e( 'This table will be permanently deleted and cannot be recovered. Are you sure?', 'league-table-lite' ); ?></p>
	</div>

</div>