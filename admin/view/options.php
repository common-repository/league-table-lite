<?php
/**
 * The file used to display the "Options" menu in the admin area.
 *
 * @package league-table-lite
 */

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_attr__( 'You do not have sufficient capabilities to access this page.', 'league-table-lite' ) );
}

// Sanitization -------------------------------------------------------------------------------------------------.
$data['settings_updated'] = isset( $_GET['settings-updated'] ) ? sanitize_key( $_GET['settings-updated'] ) : null;
$data['active_tab']       = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';

?>

<div class="wrap">

	<h2><?php esc_html_e( 'League Table - Options', 'league-table-lite' ); ?></h2>

	<?php

	// Settings errors.
	if ( ! is_null( $data['settings_updated'] ) && 'true' === $data['settings_updated'] ) {
		settings_errors();
	}

	?>

	<div id="daext-options-wrapper">

		<div class="nav-tab-wrapper">
			<a href="?page=daextletal-options&tab=general"
				class="nav-tab <?php echo 'general' === $data['active_tab'] ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'General', 'league-table-lite' ); ?></a>
		</div>

		<form method='post' action='options.php'>

			<?php

			if ( 'general' === $data['active_tab'] ) {

				settings_fields( $this->shared->get( 'slug' ) . '_general_options' );
				do_settings_sections( $this->shared->get( 'slug' ) . '_general_options' );

			}

			?>

			<div class="daext-options-action">
				<input type="submit" name="submit" id="submit" class="button"
						value="<?php esc_attr_e( 'Save Changes', 'league-table-lite' ); ?>">
			</div>

		</form>

	</div>

</div>