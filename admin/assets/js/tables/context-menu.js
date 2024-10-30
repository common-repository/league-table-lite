/**
 * This file includes the methods used in the callbacks of the handsontable context menu items.
 *
 * @package league-table-lite
 */

(function ($) {

	'use strict';

	/**
	 * This object includes the methods used in the callbacks of the handsontable context menu items.
	 *
	 * Note that the handsontable context menu is defined in the utility::initialize_handsontable().
	 *
	 * Ref: https://handsontable.com/docs/7.0.2/ContextMenu.html
	 */
	window.DAEXTLETAL.contextMenu = {

		/**
		 * Insert row above context menu handler.
		 *
		 * @param row The index of the row placed on the bottom of the one that should be added.
		 */
		insert_row_above: function (row) {

			'use strict';

			/**
			 * Reset the spreadsheet clipboard because after performing this method certain references to cell properties
			 * might be lost.
			 */
			window.DAEXTLETAL.states.synthetic_clipboard = null;

			// increase by 1 the maximum number of rows.
			let number_of_rows = window.DAEXTLETAL.states.daextletal_hot.countRows();
			window.DAEXTLETAL.states.daextletal_hot.updateSettings(
				{
					maxRows: number_of_rows + 1,
				}
			);

			// add row in the spreadsheet.
			window.DAEXTLETAL.states.daextletal_hot.alter( 'insert_row', row, 1 );

			/**
			 * Perform and ajax request and:
			 *
			 * - update the "row" field of "_table"
			 * - update all the subsequent "row_index" in "_data"
			 * - add a new record in "_data" with the missing "row_index"
			 * - update all the subsequent "row_index" in "_cell"
			 */
				// prepare ajax request.
			let data = {
				'action': 'daextletal_insert_row_above',
				'security': window.DAEXTLETAL_PARAMETERS.nonce,
				'table_id': window.DAEXTLETAL.utility.get_table_id(),
				'row': row,
			};

			// set ajax in synchronous mode.
			jQuery.ajaxSetup( {async: false} );

			// use the setDataAtCell() method one single time with a two-dimensional array to avoid performance issues.
			let number_of_columns = window.DAEXTLETAL.states.daextletal_hot.countCols();
			let cells_to_modify   = [];
			for (let i = 0; i < number_of_columns; i++) {
				cells_to_modify.push( [row, i, 0] );
			}
			window.DAEXTLETAL.states.daextletal_hot.setDataAtCell( cells_to_modify );

			// send ajax request.
			$.post(
				window.DAEXTLETAL_PARAMETERS.ajax_url,
				data,
				function () {

					'use strict';

					// update the value of the rows fields.
					$( '#rows' ).val( (parseInt( $( '#rows' ).val(), 10 ) + 1) );

				}
			);

			// set ajax in asynchronous mode.
			jQuery.ajaxSetup( {async: true} );

		},

		/**
		 * Insert row below context menu handler.
		 *
		 * @param row Int The index of the row placed on the top of the one that should be added.
		 */
		insert_row_below: function (row) {

			'use strict';

			/**
			 * Reset the spreadsheet clipboard because after performing this method certain references to cell properties
			 * might be lost.
			 */
			window.DAEXTLETAL.states.synthetic_clipboard = null;

			// increase by 1 the maximum number of rows.
			let number_of_rows = window.DAEXTLETAL.states.daextletal_hot.countRows();
			window.DAEXTLETAL.states.daextletal_hot.updateSettings(
				{
					maxRows: number_of_rows + 1,
				}
			);

			// remove row from the spreadsheet.
			window.DAEXTLETAL.states.daextletal_hot.alter( 'insert_row', row + 1, 1 );

			// prepare ajax request.
			let data = {
				'action': 'daextletal_insert_row_below',
				'security': window.DAEXTLETAL_PARAMETERS.nonce,
				'table_id': window.DAEXTLETAL.utility.get_table_id(),
				'row': row,
			};

			// set ajax in synchronous mode.
			jQuery.ajaxSetup( {async: false} );

			// use the setDataAtCell() method one single time with a two-dimensional array to avoid performance issues.
			let number_of_columns = window.DAEXTLETAL.states.daextletal_hot.countCols();
			let cells_to_modify   = [];
			for (let i = 0; i < number_of_columns; i++) {
				cells_to_modify.push( [row + 1, i, 0] );
			}
			window.DAEXTLETAL.states.daextletal_hot.setDataAtCell( cells_to_modify );

			// send ajax request.
			$.post(
				window.DAEXTLETAL_PARAMETERS.ajax_url,
				data,
				function () {

					'use strict';

					// update the value of the rows fields.
					$( '#rows' ).val( (parseInt( $( '#rows' ).val(), 10 ) + 1) );

				}
			);

			// set ajax in asynchronous mode.
			jQuery.ajaxSetup( {async: true} );

		},

		/**
		 * Insert column left context menu event handler.
		 *
		 * @param column Int The index of the column placed on the right of the one that should be added.
		 */
		insert_column_left: function (column) {

			'use strict';

			/**
			 * Reset the spreadsheet clipboard because after performing this method certain references to cell properties
			 * might be lost.
			 */
			window.DAEXTLETAL.states.synthetic_clipboard = null;

			// increase by 1 the maximum number of rows.
			let number_of_columns = window.DAEXTLETAL.states.daextletal_hot.countCols();
			window.DAEXTLETAL.states.daextletal_hot.updateSettings(
				{
					maxCols: number_of_columns + 1,
				}
			);

			// add the column in the spreadsheet.
			window.DAEXTLETAL.states.daextletal_hot.alter( 'insert_col', column, 1 );

			// use the setDataAtCell() method one single time with a two-dimensional array to avoid performance issues.
			let number_of_rows  = window.DAEXTLETAL.states.daextletal_hot.countRows();
			let cells_to_modify = [];
			cells_to_modify.push( [0, column, 'New Label '] );
			for (let i = 1; i < number_of_rows; i++) {
				cells_to_modify.push( [i, column, 0] );
			}
			window.DAEXTLETAL.states.daextletal_hot.setDataAtCell( cells_to_modify );

			/**
			 * Perform and ajax request and:
			 *
			 * - update the "row" field of "_table"
			 * - update all the subsequent "row_index" in "_data"
			 * - add a new record in "_data" with the missing "row_index"
			 * - update all the subsequent "row_index" in "_cell"
			 */
			// prepare ajax request.
			let data = {
				'action': 'daextletal_insert_column_left',
				'security': window.DAEXTLETAL_PARAMETERS.nonce,
				'table_id': window.DAEXTLETAL.utility.get_table_id(),
				'column': column,
			};

			// set ajax in synchronous mode.
			jQuery.ajaxSetup( {async: false} );

			// send ajax request.
			$.post(
				window.DAEXTLETAL_PARAMETERS.ajax_url,
				data,
				function () {

					'use strict';

					// update the value of the rows fields.
					$( '#columns' ).val( (parseInt( $( '#columns' ).val(), 10 ) + 1) );

				}
			);

			// set ajax in asynchronous mode.
			jQuery.ajaxSetup( {async: true} );

		},

		/**
		 * Insert column left context menu handler.
		 *
		 * @param column Int The index of the column placed on the left of the one that should be added.
		 */
		insert_column_right: function (column) {

			'use strict';

			/**
			 * Reset the spreadsheet clipboard because after performing this method certain references to cell properties
			 * might be lost.
			 */
			window.DAEXTLETAL.states.synthetic_clipboard = null;

			// increase by 1 the maximum number of rows.
			let number_of_columns = window.DAEXTLETAL.states.daextletal_hot.countCols();
			window.DAEXTLETAL.states.daextletal_hot.updateSettings(
				{
					maxCols: number_of_columns + 1,
				}
			);

			// add the column in the spreadsheet.
			window.DAEXTLETAL.states.daextletal_hot.alter( 'insert_col', column + 1, 1 );

			// use the setDataAtCell() method one single time with a two-dimensional array to avoid performance issues.
			let number_of_rows  = window.DAEXTLETAL.states.daextletal_hot.countRows();
			let cells_to_modify = [];
			cells_to_modify.push( [0, column + 1, 'New Label '] );
			for (let i = 1; i < number_of_rows; i++) {
				cells_to_modify.push( [i, column + 1, 0] );
			}
			window.DAEXTLETAL.states.daextletal_hot.setDataAtCell( cells_to_modify );

			// prepare ajax request.
			let data = {
				'action': 'daextletal_insert_column_right',
				'security': window.DAEXTLETAL_PARAMETERS.nonce,
				'table_id': window.DAEXTLETAL.utility.get_table_id(),
				'column': column,
			};

			// set ajax in synchronous mode.
			jQuery.ajaxSetup( {async: false} );

			// send ajax request.
			$.post(
				window.DAEXTLETAL_PARAMETERS.ajax_url,
				data,
				function () {

					'use strict';

					// update the value of the rows fields.
					$( '#columns' ).val( (parseInt( $( '#columns' ).val(), 10 ) + 1) );

				}
			);

			// set ajax in asynchronous mode.
			jQuery.ajaxSetup( {async: true} );

		},

		/**
		 * Remove row context menu handler.
		 *
		 * @param row Int The row that should be removed.
		 */
		remove_row: function (row) {

			'use strict';

			/**
			 * Reset the spreadsheet clipboard because after performing this method certain references to cell properties
			 * might be lost.
			 */
			window.DAEXTLETAL.states.synthetic_clipboard = null;

			// remove row from the spreadsheet.
			window.DAEXTLETAL.states.daextletal_hot.alter( 'remove_row', row, 1 );

			// decrease by 1 the maximum number of rows.
			let number_of_rows = window.DAEXTLETAL.states.daextletal_hot.countRows();
			window.DAEXTLETAL.states.daextletal_hot.updateSettings(
				{
					maxRows: number_of_rows,
				}
			);

			/**
			 * Perform and ajax request and:
			 *
			 * - update the "row" field of "_table"
			 * - remove the row from "_data" (remove the record and update the "row_index" of the other records
			 * - update the cell properties with the new indexes in "_cell"
			 */
			// prepare ajax request.
			let data = {
				'action': 'daextletal_remove_row',
				'security': window.DAEXTLETAL_PARAMETERS.nonce,
				'table_id': window.DAEXTLETAL.utility.get_table_id(),
				'row': row,
			};

			// set ajax in synchronous mode.
			jQuery.ajaxSetup( {async: false} );

			// send ajax request.
			$.post(
				window.DAEXTLETAL_PARAMETERS.ajax_url,
				data,
				function () {

					'use strict';

					// update the value of the rows fields.
					$( '#rows' ).val( (parseInt( $( '#rows' ).val(), 10 ) - 1) );

				}
			);

			// set ajax in asynchronous mode.
			jQuery.ajaxSetup( {async: true} );

		},

		/**
		 * Remove column context menu handler.
		 *
		 * @param column Int The column that should be removed.
		 */
		remove_column: function (column) {

			'use strict';

			/**
			 * Reset the spreadsheet clipboard because after performing this method certain references to cell properties
			 * might be lost.
			 */
			window.DAEXTLETAL.states.synthetic_clipboard = null;

			// remove column from the spreadsheet.
			window.DAEXTLETAL.states.daextletal_hot.alter( 'remove_col', column, 1 );

			// decrease by 1 the maximum number of rows.
			let number_of_columns = window.DAEXTLETAL.states.daextletal_hot.countCols();
			window.DAEXTLETAL.states.daextletal_hot.updateSettings(
				{
					maxCols: number_of_columns,
				}
			);

			// prepare ajax request.
			let data = {
				'action': 'daextletal_remove_column',
				'security': window.DAEXTLETAL_PARAMETERS.nonce,
				'table_id': window.DAEXTLETAL.utility.get_table_id(),
				'column': column,
			};

			// set ajax in synchronous mode.
			jQuery.ajaxSetup( {async: false} );

			// send ajax request.
			$.post(
				window.DAEXTLETAL_PARAMETERS.ajax_url,
				data,
				function () {

					'use strict';

					// update the value of the rows fields.
					$( '#columns' ).val( (parseInt( $( '#columns' ).val(), 10 ) - 1) );

				}
			);

			// set ajax in asynchronous mode.
			jQuery.ajaxSetup( {async: true} );

		},

		/**
		 * Remove the cell properties included in the selection.
		 *
		 * @param options Object An object with included information about the selection.
		 */
		reset_cell_properties: function (options) {

			'use strict';

			/**
			 * Reset the spreadsheet clipboard because after performing this method certain references to cell properties
			 * might be lost.
			 */
			window.DAEXTLETAL.states.synthetic_clipboard = null;

			// prepare ajax request.
			let data = {
				'action': 'daextletal_reset_cell_properties',
				'security': window.DAEXTLETAL_PARAMETERS.nonce,
				'table_id': window.DAEXTLETAL.utility.get_table_id(),
				'options': JSON.stringify( options ),
			};

			// set ajax in synchronous mode.
			jQuery.ajaxSetup( {async: false} );

			// send ajax request.
			$.post(
				window.DAEXTLETAL_PARAMETERS.ajax_url,
				data,
				function () {

					'use strict';

					window.DAEXTLETAL.utility.refresh_cell_properties_highlight();

				}
			);

			// set ajax in asynchronous mode.
			jQuery.ajaxSetup( {async: true} );

		},

		/**
		 * Set 0 to all the cells included in the selection.
		 *
		 * @param options Object An object with information about the selection.
		 */
		reset_data: function (options) {

			'use strict';

			let data;

			for (let i = options.start.row; i <= options.end.row; i++) {
				for (let t = options.start.col; t <= options.end.col; t++) {
					if (i === 0) {
						data = 'New Label';
					} else {
						data = 0;
					}
					window.DAEXTLETAL.states.daextletal_hot.setDataAtCell( i, t, data );
				}
			}

		},

	};

})( window.jQuery );