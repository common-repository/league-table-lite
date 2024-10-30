/**
 * This file is used to initialize the Tables menu.
 *
 * @package league-table-lite
 */

(function ($) {

	// This object is used to save all the variable states of the menu.
	window.DAEXTLETAL.states = {

		daextletal_hot: false,
		synthetic_clipboard: null,
		cell_properties_message_timeout_handler: null,
		table_message_timeout_handler: null,
		tableToDelete: null,

	};

	bindEventListeners();

	/**
	 * Bind the event listeners.
	 */
	function bindEventListeners() {

		'use strict';

		$( document ).ready(
			function () {

				'use strict';

				window.DAEXTLETAL.utility.initialize_handsontable();
				window.DAEXTLETAL.utility.initialize_select2();
				window.DAEXTLETAL.utility.remove_border_last_element_daext_form_table();
				window.DAEXTLETAL.utility.responsive_sidebar_container();
				window.DAEXTLETAL.utility.disable_specific_keyboard_shortcuts();
				window.DAEXTLETAL.utility.refresh_cell_properties_highlight();

				$( document.body ).on(
					'click',
					'#save' ,
					function () {

						'use strict';

						let reload_menu       = parseInt( $( this ).data( 'reload-menu' ), 10 ) == 1 ? true : false;
						let validation_result = window.DAEXTLETAL.utility.save_table( reload_menu );

						// show error message.
						if (validation_result === true) {

							if (reload_menu === false) {

								// hide error message.
								$( '#table-error p' ).html( '' );
								$( '#table-error' ).hide();

								// display temporary success message.
								$( '#table-success p' ).text( objectL10n.table_success );
								$( '#table-success' ).show();
								clearTimeout( window.DAEXTLETAL.states.table_message_timeout_handler );
								window.DAEXTLETAL.states.table_message_timeout_handler = setTimeout(
									function () {

											'use strict';

										$( '#table-success' ).hide();

									},
									3000
								);

							}

						} else {

							// display error message.
							$( '#table-error p' ).
							html( objectL10n.table_error_partial_message + ' <strong>' + validation_result.join( ', ' ) + '</strong>' );
							$( '#table-error' ).show();

						}

					}
				);

				$( document.body ).on(
					'click',
					'#close' ,
					function () {

						'use strict';

						// reload the dashboard menu.
						window.location.replace( window.DAEXTLETAL_PARAMETERS.admin_url + 'admin.php?page=daextletal-tables' );

					}
				);

				$( document.body ).on(
					'change',
					'#rows' ,
					function () {

						'use strict';

						window.DAEXTLETAL.utility.update_rows();

					}
				);

				$( document.body ).on(
					'change',
					'#columns' ,
					function () {

						'use strict';

						window.DAEXTLETAL.utility.update_columns();
						window.DAEXTLETAL.utility.update_order_by();

					}
				);

				$( document.body ).on(
					'click',
					'.update-reset-cell-properties' ,
					function () {

						'use strict';

						let element_id = $( this ).attr( 'id' );

						window.DAEXTLETAL.utility.update_reset_cell_properties( element_id );

					}
				);

				$( document.body ).on(
					'click',
					'.group-trigger' ,
					function () {

						'use strict';

						// open and close the various sections of the tables area.
						let target = $( this ).attr( 'data-trigger-target' );
						$( '.' + target ).toggle();
						$( this ).find( '.expand-icon' ).toggleClass( 'arrow-down' );

						window.DAEXTLETAL.utility.remove_border_last_element_daext_form_table();

					}
				);

				$( window ).on(
					'resize',
					function () {

						'use strict';

						window.DAEXTLETAL.utility.responsive_sidebar_container();

					}
				);

				$(
					function () {

						'use strict';

						$( '.dialog-alert' ).dialog(
							{
								autoOpen: false,
								resizable: false,
								height: 'auto',
								width: 340,
								modal: true,
								buttons: [
								{
									tabIndex: -1,
									text: 'Close',
									click: function () {

										'use strict';

										$( this ).dialog( 'close' );

									},
								},
								],
							}
						);
					}
				);

				// Dialog Confirm -------------------------------------------------------------------------------------.

				/**
				 * Original Version (not compatible with pre-ES5 browser)
				 */
				$(
					function () {

						'use strict';

						$( '#dialog-confirm' ).dialog(
							{
								autoOpen: false,
								resizable: false,
								height: 'auto',
								width: 340,
								modal: true,
								buttons: {
									[objectL10n.delete]: function () {
										$( '#form-delete-' + window.DAEXTLETAL.states.tableToDelete ).submit();
									},
									[objectL10n.cancel]: function () {
										$( this ).dialog( 'close' );
									},
								},
							}
						);

					}
				);

				// Click event handler on the delete button.
				$( document.body ).on(
					'click',
					'.menu-icon.delete' ,
					function () {

						'use strict';

						event.preventDefault();
						window.DAEXTLETAL.states.tableToDelete = $( this ).prev().val();
						$( '#dialog-confirm' ).dialog( 'open' );

					}
				);

			}
		);

	}

}(window.jQuery));