<?php

namespace WeLabs\WeCatering\Models;

/**
 * MenuItem Model
 *
 * @class MenuItem The class that handles menu item operations
 */
class MenuItem {

    /**
     * Database table name
     *
     * @var string
     */
    private $table_name;

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'we_catering_menu_items';
    }

    /**
     * Get all menu items
     *
     * @param array $args
     * @return array
     */
    public function get_all( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'status' => 'active',
            'category' => '',
            'orderby' => 'name',
            'order' => 'ASC',
            'limit' => 0,
            'offset' => 0,
        );

        $args = wp_parse_args( $args, $defaults );

        $where_conditions = array();
        $where_values = array();

        if ( ! empty( $args['status'] ) ) {
            $where_conditions[] = 'status = %s';
            $where_values[] = $args['status'];
        }

        if ( ! empty( $args['category'] ) ) {
            $where_conditions[] = 'category = %s';
            $where_values[] = $args['category'];
        }

        $where_clause = '';
        if ( ! empty( $where_conditions ) ) {
            $where_clause = 'WHERE ' . implode( ' AND ', $where_conditions );
        }

        $order_clause = 'ORDER BY ' . esc_sql( $args['orderby'] ) . ' ' . esc_sql( $args['order'] );

        $limit_clause = '';
        if ( $args['limit'] > 0 ) {
            $limit_clause = 'LIMIT ' . intval( $args['limit'] );
            if ( $args['offset'] > 0 ) {
                $limit_clause .= ' OFFSET ' . intval( $args['offset'] );
            }
        }

        $sql = "SELECT * FROM {$this->table_name} {$where_clause} {$order_clause} {$limit_clause}";

        if ( ! empty( $where_values ) ) {
            $sql = $wpdb->prepare( $sql, $where_values );
        }

        return $wpdb->get_results( $sql );
    }

    /**
     * Get menu item by ID
     *
     * @param int $id
     * @return object|null
     */
    public function get_by_id( $id ) {
        global $wpdb;

        $sql = $wpdb->prepare( "SELECT * FROM {$this->table_name} WHERE id = %d", $id );
        return $wpdb->get_row( $sql );
    }

    /**
     * Create new menu item
     *
     * @param array $data
     * @return int|false
     */
    public function create( $data ) {
        global $wpdb;

        $defaults = array(
            'name' => '',
            'description' => '',
            'items' => '',
            'price' => 0.00,
            'category' => '',
            'dietary_type' => '',
            'status' => 'active',
        );

        $data = wp_parse_args( $data, $defaults );

        // Validate required fields
        if ( empty( $data['name'] ) ) {
            return false;
        }

        // Sanitize data
        $insert_data = array(
            'name' => sanitize_text_field( $data['name'] ),
            'description' => sanitize_textarea_field( $data['description'] ),
            'items' => sanitize_textarea_field( $data['items'] ),
            'price' => floatval( $data['price'] ),
            'category' => sanitize_text_field( $data['category'] ),
            'dietary_type' => sanitize_text_field( $data['dietary_type'] ),
            'status' => sanitize_text_field( $data['status'] ),
        );

        $result = $wpdb->insert( $this->table_name, $insert_data );

        if ( $result === false ) {
            return false;
        }

        return $wpdb->insert_id;
    }

    /**
     * Update menu item
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update( $id, $data ) {
        global $wpdb;

        $update_data = array();

        if ( isset( $data['name'] ) ) {
            $update_data['name'] = sanitize_text_field( $data['name'] );
        }

        if ( isset( $data['description'] ) ) {
            $update_data['description'] = sanitize_textarea_field( $data['description'] );
        }

        if ( isset( $data['items'] ) ) {
            $update_data['items'] = sanitize_textarea_field( $data['items'] );
        }

        if ( isset( $data['price'] ) ) {
            $update_data['price'] = floatval( $data['price'] );
        }

        if ( isset( $data['category'] ) ) {
            $update_data['category'] = sanitize_text_field( $data['category'] );
        }

        if ( isset( $data['dietary_type'] ) ) {
            $update_data['dietary_type'] = sanitize_text_field( $data['dietary_type'] );
        }

        if ( isset( $data['status'] ) ) {
            $update_data['status'] = sanitize_text_field( $data['status'] );
        }

        if ( empty( $update_data ) ) {
            return false;
        }

        $result = $wpdb->update(
            $this->table_name,
            $update_data,
            array( 'id' => $id ),
            null,
            array( '%d' )
        );

        return $result !== false;
    }

    /**
     * Delete menu item
     *
     * @param int $id
     * @return bool
     */
    public function delete( $id ) {
        global $wpdb;

        $result = $wpdb->delete(
            $this->table_name,
            array( 'id' => $id ),
            array( '%d' )
        );

        return $result !== false;
    }

    /**
     * Get menu item categories
     *
     * @return array
     */
    public function get_categories() {
        global $wpdb;

        $sql = "SELECT DISTINCT category FROM {$this->table_name} WHERE category != '' AND status = 'active' ORDER BY category";
        $results = $wpdb->get_col( $sql );

        return $results;
    }

    /**
     * Get menu items for daily menu
     *
     * @param string $date
     * @return array
     */
    public function get_daily_menu_items( $date ) {
        global $wpdb;

        $daily_menus_table = $wpdb->prefix . 'we_catering_daily_menus';

        $sql = $wpdb->prepare(
            "SELECT mi.*, dm.is_available 
             FROM {$this->table_name} mi 
             INNER JOIN {$daily_menus_table} dm ON mi.id = dm.menu_item_id 
             WHERE dm.menu_date = %s AND mi.status = 'active' 
             ORDER BY mi.category, mi.name",
            $date
        );

        return $wpdb->get_results( $sql );
    }

    /**
     * Count menu items
     *
     * @param array $args
     * @return int
     */
    public function count( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'status' => 'active',
            'category' => '',
        );

        $args = wp_parse_args( $args, $defaults );

        $where_conditions = array();
        $where_values = array();

        if ( ! empty( $args['status'] ) ) {
            $where_conditions[] = 'status = %s';
            $where_values[] = $args['status'];
        }

        if ( ! empty( $args['category'] ) ) {
            $where_conditions[] = 'category = %s';
            $where_values[] = $args['category'];
        }

        $where_clause = '';
        if ( ! empty( $where_conditions ) ) {
            $where_clause = 'WHERE ' . implode( ' AND ', $where_conditions );
        }

        $sql = "SELECT COUNT(*) FROM {$this->table_name} {$where_clause}";

        if ( ! empty( $where_values ) ) {
            $sql = $wpdb->prepare( $sql, $where_values );
        }

        return (int) $wpdb->get_var( $sql );
    }
}
