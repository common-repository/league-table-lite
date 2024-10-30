<?php
/**
 * Plugin Name: League Table
 * Description: Generates tables in your WordPress blog. (Lite version)
 * Version: 1.17
 * Author: DAEXT
 * Author URI: https://daext.com
 * Text Domain: league-table-lite
 * License: GPLv3
 *
 * @package league-table-lite
 */

// Prevent direct access to this file.
if ( ! defined( 'WPINC' ) ) {
	die();
}

// Shared across public and admin.
require_once plugin_dir_path( __FILE__ ) . 'shared/class-daextletal-shared.php';

require_once plugin_dir_path( __FILE__ ) . 'public/class-daextletal-public.php';
add_action( 'plugins_loaded', array( 'Daextletal_Public', 'get_instance' ) );

// Admin.
require_once plugin_dir_path( __FILE__ ) . 'admin/class-daextletal-admin.php';

// If it's the admin area and this is not an AJAX request, create a new singleton instance of the admin class.
if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
	add_action( 'plugins_loaded', array( 'daextletal_Admin', 'get_instance' ) );
}

// Activate.
register_activation_hook( __FILE__, array( 'Daextletal_Admin', 'ac_activate' ) );

// Update the plugin db tables and options if they are not up-to-date.
Daextletal_Admin::ac_create_database_tables();
Daextletal_Admin::ac_initialize_options();

// Register AJAX actions.
if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

	// Admin.
	require_once plugin_dir_path( __FILE__ ) . 'class-daextletal-ajax.php';
	add_action( 'plugins_loaded', array( 'daextletal_Ajax', 'get_instance' ) );

}

/**
 * Customize the action links in the "Plugins" menu.
 *
 * @param array $actions An array of plugin action links.
 *
 * @return mixed
 */
function daextletal_customize_action_links( $actions ) {
	$actions[] = '<a href="https://daext.com/league-table/" target="_blank">' . esc_html__( 'Buy the Pro Version', 'league-table-lite' ) . '</a>';
	return $actions;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'daextletal_customize_action_links' );
