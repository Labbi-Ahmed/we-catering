<?php

namespace WeLabs\WeCatering;

/**
 * Database class
 *
 * @class Database The class that handles all database operations
 */
class Database {

    /**
     * Database version
     *
     * @var string
     */
    private $db_version = '1.0.0';

    /**
     * Constructor for the Database class
     */
    public function __construct() {
        add_action( 'init', array( $this, 'check_db_version' ) );
    }

    /**
     * Check database version and update if needed
     */
    public function check_db_version() {
        $current_version = get_option( 'we_catering_db_version', '0.0.0' );

        if ( version_compare( $current_version, $this->db_version, '<' ) ) {
            $this->create_tables();
            update_option( 'we_catering_db_version', $this->db_version );
        }
    }

    /**
     * Create database tables
     */
    public function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Organizations table
        $organizations_table = $wpdb->prefix . 'we_catering_organizations';
        $organizations_sql = "CREATE TABLE $organizations_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            contact_person varchar(255),
            email varchar(255),
            phone varchar(50),
            address text,
            status enum('active','inactive') DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";

        // Menu items table
        $menu_items_table = $wpdb->prefix . 'we_catering_menu_items';
        $menu_items_sql = "CREATE TABLE $menu_items_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            items text,
            price decimal(10,2) NOT NULL DEFAULT 0.00,
            category varchar(100),
            dietary_type varchar(100),
            status enum('active','inactive') DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY category (category),
            KEY status (status),
            KEY price (price)
        ) $charset_collate;";

        // Daily menus table
        $daily_menus_table = $wpdb->prefix . 'we_catering_daily_menus';
        $daily_menus_sql = "CREATE TABLE $daily_menus_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            menu_date date NOT NULL,
            menu_item_id bigint(20) unsigned NOT NULL,
            is_available tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY menu_date_item (menu_date, menu_item_id),
            KEY menu_date (menu_date),
            KEY menu_item_id (menu_item_id),
            FOREIGN KEY (menu_item_id) REFERENCES $menu_items_table(id) ON DELETE CASCADE
        ) $charset_collate;";

        // Orders table
        $orders_table = $wpdb->prefix . 'we_catering_orders';
        $orders_sql = "CREATE TABLE $orders_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            order_number varchar(50) NOT NULL,
            user_id bigint(20) unsigned NOT NULL,
            organization_id bigint(20) unsigned NOT NULL,
            order_date date NOT NULL,
            total_amount decimal(10,2) NOT NULL DEFAULT 0.00,
            status enum('pending','confirmed','closed','cancelled') DEFAULT 'pending',
            notes text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY order_number (order_number),
            KEY user_id (user_id),
            KEY organization_id (organization_id),
            KEY order_date (order_date),
            KEY status (status),
            FOREIGN KEY (user_id) REFERENCES {$wpdb->users}(ID) ON DELETE CASCADE,
            FOREIGN KEY (organization_id) REFERENCES $organizations_table(id) ON DELETE CASCADE
        ) $charset_collate;";

        // Order items table
        $order_items_table = $wpdb->prefix . 'we_catering_order_items';
        $order_items_sql = "CREATE TABLE $order_items_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            order_id bigint(20) unsigned NOT NULL,
            menu_item_id bigint(20) unsigned NOT NULL,
            quantity int(11) NOT NULL DEFAULT 1,
            unit_price decimal(10,2) NOT NULL DEFAULT 0.00,
            total_price decimal(10,2) NOT NULL DEFAULT 0.00,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY order_id (order_id),
            KEY menu_item_id (menu_item_id),
            FOREIGN KEY (order_id) REFERENCES $orders_table(id) ON DELETE CASCADE,
            FOREIGN KEY (menu_item_id) REFERENCES $menu_items_table(id) ON DELETE CASCADE
        ) $charset_collate;";

        // User organizations table (for user-organization relationships)
        $user_organizations_table = $wpdb->prefix . 'we_catering_user_organizations';
        $user_organizations_sql = "CREATE TABLE $user_organizations_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            organization_id bigint(20) unsigned NOT NULL,
            role enum('admin','user') DEFAULT 'user',
            status enum('active','inactive') DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY user_organization (user_id, organization_id),
            KEY user_id (user_id),
            KEY organization_id (organization_id),
            KEY status (status),
            FOREIGN KEY (user_id) REFERENCES {$wpdb->users}(ID) ON DELETE CASCADE,
            FOREIGN KEY (organization_id) REFERENCES $organizations_table(id) ON DELETE CASCADE
        ) $charset_collate;";

        // Execute SQL statements
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        
        dbDelta( $organizations_sql );
        dbDelta( $menu_items_sql );
        dbDelta( $daily_menus_sql );
        dbDelta( $orders_sql );
        dbDelta( $order_items_sql );
        dbDelta( $user_organizations_sql );

        // Add default options
        $this->add_default_options();
    }

    /**
     * Add default plugin options
     */
    private function add_default_options() {
        $default_settings = array(
            'order_window_start' => '10:00',
            'order_window_end' => '11:30',
            'currency' => 'USD',
            'order_confirmation_email' => 1,
            'order_reminder_email' => 1,
            'max_items_per_order' => 10,
            'max_quantity_per_item' => 5,
        );

        foreach ( $default_settings as $key => $value ) {
            if ( ! get_option( 'we_catering_' . $key ) ) {
                add_option( 'we_catering_' . $key, $value );
            }
        }
    }

    /**
     * Get table name with prefix
     *
     * @param string $table_name
     * @return string
     */
    public function get_table_name( $table_name ) {
        global $wpdb;
        return $wpdb->prefix . 'we_catering_' . $table_name;
    }

    /**
     * Check if table exists
     *
     * @param string $table_name
     * @return bool
     */
    public function table_exists( $table_name ) {
        global $wpdb;
        $full_table_name = $this->get_table_name( $table_name );
        $result = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $full_table_name ) );
        return $result === $full_table_name;
    }

    /**
     * Get database version
     *
     * @return string
     */
    public function get_db_version() {
        return $this->db_version;
    }

    /**
     * Drop all plugin tables (for uninstall)
     */
    public function drop_tables() {
        global $wpdb;

        $tables = array(
            'user_organizations',
            'order_items',
            'orders',
            'daily_menus',
            'menu_items',
            'organizations',
        );

        foreach ( $tables as $table ) {
            $table_name = $this->get_table_name( $table );
            $wpdb->query( "DROP TABLE IF EXISTS $table_name" );
        }

        // Delete options
        $options = array(
            'we_catering_db_version',
            'we_catering_order_window_start',
            'we_catering_order_window_end',
            'we_catering_currency',
            'we_catering_order_confirmation_email',
            'we_catering_order_reminder_email',
            'we_catering_max_items_per_order',
            'we_catering_max_quantity_per_item',
        );

        foreach ( $options as $option ) {
            delete_option( $option );
        }
    }
}
