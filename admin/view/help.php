<?php
/**
 * The file used to display the "Help" menu in the admin area.
 *
 * @package league-table-lite
 */

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'league-table-lite' ) );
}

?>

<!-- output -->

<div class="wrap">

	<h2><?php esc_html_e( 'League Table - Help', 'league-table-lite' ); ?></h2>

	<div id="daext-menu-wrapper">

		<p><?php esc_html_e( 'Visit the resources below to find your answers or to ask questions directly to the plugin developers.', 'league-table-lite' ); ?></p>
		<ul>
			<li><a href="https://daext.com/doc/league-table/"><?php esc_html_e( 'Plugin Documentation', 'league-table-lite' ); ?></a></li>
			<li><a href="https://daext.com/support/"><?php esc_html_e( 'Support Conditions', 'league-table-lite' ); ?></li>
			<li><a href="https://daext.com/"><?php esc_html_e( 'Developer Website', 'league-table-lite' ); ?></a></li>
			<li><a href="https://daext.com/league-table/"><?php esc_html_e( 'Pro Version', 'league-table-lite' ); ?></a></li>
			<li><a href="https://wordpress.org/plugins/league-table-lite/"><?php esc_html_e( 'WordPress.org Plugin Page', 'league-table-lite' ); ?></a></li>
			<li><a href="https://wordpress.org/support/plugin/league-table-lite/"><?php esc_html_e( 'WordPress.org Support Forum', 'league-table-lite' ); ?></a></li>
		</ul>

	</div>

</div>

