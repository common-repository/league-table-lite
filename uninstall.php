<?php
/**
 * Uninstall plugin.
 *
 * @package league-table-lite
 */

// Exit if this file is not called during the uninstallation process.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die();
}

require_once plugin_dir_path( __FILE__ ) . 'shared/class-daextletal-shared.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/class-daextletal-admin.php';

// Delete options and tables.
Daextletal_Admin::un_delete();
