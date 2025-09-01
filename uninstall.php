<?php
/**
 * Uninstall We Catering Plugin
 *
 * @package WeCatering
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Include the Database class to access drop_tables method
require_once plugin_dir_path( __FILE__ ) . 'includes/Database.php';

use WeLabs\WeCatering\Database;

// Drop all plugin tables
$database = new Database();
$database->drop_tables();

// Delete any additional options that might have been created
delete_option( 'we_catering_db_version' );
delete_option( 'we_catering_order_window_start' );
delete_option( 'we_catering_order_window_end' );
delete_option( 'we_catering_currency' );
delete_option( 'we_catering_order_confirmation_email' );
delete_option( 'we_catering_order_reminder_email' );
delete_option( 'we_catering_max_items_per_order' );
delete_option( 'we_catering_max_quantity_per_item' );

// Clear any cached data that has been removed
wp_cache_flush();
