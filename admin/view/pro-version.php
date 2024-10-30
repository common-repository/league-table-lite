<?php
/**
 * The file used to display the "Pro Version" menu in the admin area.
 *
 * @package league-table-lite
 */

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'league-table-lite' ) );
}

?>

<!-- output -->

<div class="wrap">

	<h2><?php esc_html_e( 'League Table - Pro Version', 'league-table-lite' ); ?></h2>

	<div id="daext-menu-wrapper">

		<p><?php echo esc_html__( 'For professional users, we distribute a', 'league-table-lite' ) . ' <a href="https://daext.com/league-table/">' . esc_html__( 'Pro Version', 'league-table-lite' ) . '</a> ' . esc_html__( 'of this plugin.', 'league-table-lite' ) . '</p>'; ?>
		<h2><?php esc_html_e( 'Additional Features Included in the Pro Version', 'league-table-lite' ); ?></h2>
		<ul>
			<li><?php esc_html_e( 'Create backups of the plugin data or move the plugin data between different WordPress installations with the Import and Export menus', 'league-table-lite' ); ?></li>
			<li><?php esc_html_e( 'Use up to five sorting criteria to sort the table based on the data available in multiple columns', 'league-table-lite' ); ?></li>
			<li><?php esc_html_e( 'Merge the table cells', 'league-table-lite' ); ?></li>
			<li><?php esc_html_e( 'Create formulas with the following arithmetical operation: Sum, Subtraction, Minimum, Maximum, Average', 'league-table-lite' ); ?></li>
			<li><?php esc_html_e( 'Manually apply colors, custom typographic styles, or custom alignments to individual cells', 'league-table-lite' ); ?></li>
			<li><?php esc_html_e( 'Automatically apply colors to specific ranking positions of the table or defined lists of rows or columns', 'league-table-lite' ); ?></li>
			<li><?php esc_html_e( 'Enter custom HTML content in the table cells', 'league-table-lite' ); ?></li>
			<li><?php esc_html_e( 'Specify and display the table caption', 'league-table-lite' ); ?></li>
			<li><?php esc_html_e( 'Include tables in the posts with a dedicated Gutenberg block', 'league-table-lite' ); ?></li>
		</ul>
		<h2><?php esc_html_e( 'Additional Benefits of the Pro Version', 'league-table-lite' ); ?></h2>
		<ul>
			<li><?php esc_html_e( '24 hours support provided seven days a week', 'league-table-lite' ); ?></li>
			<li><?php esc_html_e( 'Unlimited future updates (perpetual license)', 'league-table-lite' ); ?></li>
		</ul>
		<h2><?php esc_html_e( 'Get Started', 'league-table-lite' ); ?></h2>
		<p><?php echo esc_html__( 'Download the', 'league-table-lite' ) . ' <a href="https://daext.com/league-table/">' . esc_html__( 'Pro Version', 'league-table-lite' ) . '</a> ' . esc_html__( 'now by selecting one of the available plans.', 'league-table-lite' ); ?></p>
	</div>

</div>