<?php

namespace WeLabs\WeCatering\Models;

use Exception;

/**
 * Order Model
 *
 * @class Order The class that handles order operations
 */
class Order {

    /**
     * Database table name
     *
     * @var string
     */
    private $table_name;

    /**
     * Order items table name
     *
     * @var string
     */
    private $order_items_table;

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'we_catering_orders';
        $this->order_items_table = $wpdb->prefix . 'we_catering_order_items';
    }

    /**
     * Generate unique order number
     *
     * @return string
     */
    private function generate_order_number() {
        global $wpdb;

        $prefix = 'WC';
        $date = current_time( 'Ymd' );
        $random = strtoupper( substr( md5( uniqid() ), 0, 4 ) );

        $order_number = $prefix . $date . $random;

        // Check if order number already exists
        $exists = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE order_number = %s",
            $order_number
        ) );

        if ( $exists ) {
            // If exists, generate a new one
            return $this->generate_order_number();
        }

        return $order_number;
    }

    /**
     * Create new order
     *
     * @param array $data
     * @return int|false
     */
    public function create( $data ) {
        global $wpdb;

        $defaults = array(
            'user_id' => 0,
            'organization_id' => 0,
            'order_date' => current_time( 'Y-m-d' ),
            'total_amount' => 0.00,
            'status' => 'pending',
            'notes' => '',
            'order_items' => array(),
        );

        $data = wp_parse_args( $data, $defaults );

        // Validate required fields (organization can be 0 for general ordering)
        if ( empty( $data['user_id'] ) ) {
            return false;
        }

        // Check if order window is open
        if ( ! $this->is_order_window_open() ) {
            return false;
        }
        error_log('call here');

        // Generate order number
        $order_number = $this->generate_order_number();

        // Start transaction
        $wpdb->query( 'START TRANSACTION' );

        try {
            // Insert order
            $insert_data = array(
                'order_number' => $order_number,
                'user_id' => intval( $data['user_id'] ),
                'organization_id' => intval( $data['organization_id'] ),
                'order_date' => sanitize_text_field( $data['order_date'] ),
                'total_amount' => floatval( $data['total_amount'] ),
                'status' => sanitize_text_field( $data['status'] ),
                'notes' => sanitize_textarea_field( $data['notes'] ),
            );

            $result = $wpdb->insert( $this->table_name, $insert_data );

            if ( $result === false ) {
                throw new Exception( 'Failed to create order' );
            }

            $order_id = $wpdb->insert_id;

            // Insert order items
            if ( ! empty( $data['order_items'] ) ) {
                foreach ( $data['order_items'] as $item ) {
                    $item_data = array(
                        'order_id' => $order_id,
                        'menu_item_id' => intval( $item['menu_item_id'] ),
                        'quantity' => intval( $item['quantity'] ),
                        'unit_price' => floatval( $item['unit_price'] ),
                        'total_price' => floatval( $item['quantity'] * $item['unit_price'] ),
                    );

                    $item_result = $wpdb->insert( $this->order_items_table, $item_data );

                    if ( $item_result === false ) {
                        throw new Exception( 'Failed to create order item' );
                    }
                }
            }

            // Commit transaction
            $wpdb->query( 'COMMIT' );

            return $order_id;

        } catch ( Exception $e ) {
            // Rollback transaction
            $wpdb->query( 'ROLLBACK' );
            return false;
        }
    }

    /**
     * Get existing order for user and date (pending) if any
     *
     * @param int $user_id
     * @param string $date
     * @param int $organization_id
     * @return object|null
     */
    public function get_user_order_for_date( $user_id, $date, $organization_id = 0 ) {
        global $wpdb;

        $sql = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE user_id = %d AND order_date = %s AND status = 'pending' AND organization_id = %d ORDER BY id DESC LIMIT 1",
            $user_id,
            $date,
            $organization_id
        );

        return $wpdb->get_row( $sql );
    }

    /**
     * Create or update an order for the same day by merging item quantities
     *
     * @param array $data Same structure as create()
     * @return int|false Returns order_id
     */
    public function create_or_update_same_day( $data ) {
        global $wpdb;

        $defaults = array(
            'user_id' => 0,
            'organization_id' => 0,
            'order_date' => current_time( 'Y-m-d' ),
            'total_amount' => 0.00,
            'status' => 'pending',
            'notes' => '',
            'order_items' => array(),
        );
        $data = wp_parse_args( $data, $defaults );

        if ( empty( $data['user_id'] ) ) {
            return false;
        }

        if ( ! $this->is_order_window_open() ) {
            return false;
        }

        // Look up existing order
        $existing = $this->get_user_order_for_date( (int) $data['user_id'], $data['order_date'], (int) $data['organization_id'] );

        if ( ! $existing ) {
            // No existing order -> create new
            return $this->create( $data );
        }

        $order_id = (int) $existing->id;

        // Start transaction
        $wpdb->query( 'START TRANSACTION' );
        try {
            // Build current items map
            $current_items = array();
            $rows = $wpdb->get_results( $wpdb->prepare( "SELECT menu_item_id, quantity, unit_price FROM {$this->order_items_table} WHERE order_id = %d", $order_id ) );
            foreach ( (array) $rows as $row ) {
                $current_items[ (int) $row->menu_item_id ] = array( 'quantity' => (int) $row->quantity, 'unit_price' => (float) $row->unit_price );
            }

            // Merge incoming items
            foreach ( (array) $data['order_items'] as $item ) {
                $menu_item_id = (int) $item['menu_item_id'];
                $qty = max( 0, (int) $item['quantity'] );
                $price = (float) $item['unit_price'];

                if ( isset( $current_items[ $menu_item_id ] ) ) {
                    $new_qty = $current_items[ $menu_item_id ]['quantity'] + $qty;
                    $current_items[ $menu_item_id ]['quantity'] = $new_qty;
                    $current_items[ $menu_item_id ]['unit_price'] = $price; // latest price wins
                } else {
                    $current_items[ $menu_item_id ] = array( 'quantity' => $qty, 'unit_price' => $price );
                }
            }

            // Rewrite order items: delete and re-insert for simplicity
            $wpdb->delete( $this->order_items_table, array( 'order_id' => $order_id ), array( '%d' ) );

            $new_total = 0.0;
            foreach ( $current_items as $mid => $ci ) {
                if ( $ci['quantity'] <= 0 ) {
                    continue;
                }
                $total_price = (float) ( $ci['quantity'] * $ci['unit_price'] );
                $new_total += $total_price;
                $wpdb->insert( $this->order_items_table, array(
                    'order_id' => $order_id,
                    'menu_item_id' => (int) $mid,
                    'quantity' => (int) $ci['quantity'],
                    'unit_price' => (float) $ci['unit_price'],
                    'total_price' => $total_price,
                ) );
            }

            // Update order total and notes if provided
            $wpdb->update( $this->table_name, array(
                'total_amount' => $new_total,
                'notes' => isset( $data['notes'] ) ? sanitize_textarea_field( $data['notes'] ) : $existing->notes,
            ), array( 'id' => $order_id ), array( '%f', '%s' ), array( '%d' ) );

            $wpdb->query( 'COMMIT' );
            return $order_id;
        } catch ( Exception $e ) {
            $wpdb->query( 'ROLLBACK' );
            return false;
        }
    }

    /**
     * Get order by ID
     *
     * @param int $id
     * @return object|null
     */
    public function get_by_id( $id ) {
        global $wpdb;

        $sql = $wpdb->prepare(
            "SELECT o.*, u.display_name as customer_name, u.user_email as customer_email, org.name as organization_name 
             FROM {$this->table_name} o 
             LEFT JOIN {$wpdb->users} u ON o.user_id = u.ID 
             LEFT JOIN {$wpdb->prefix}we_catering_organizations org ON o.organization_id = org.id 
             WHERE o.id = %d",
            $id
        );

        return $wpdb->get_row( $sql );
    }

    /**
     * Get order by order number
     *
     * @param string $order_number
     * @return object|null
     */
    public function get_by_order_number( $order_number ) {
        global $wpdb;

        $sql = $wpdb->prepare(
            "SELECT o.*, u.display_name as customer_name, u.user_email as customer_email, org.name as organization_name 
             FROM {$this->table_name} o 
             LEFT JOIN {$wpdb->users} u ON o.user_id = u.ID 
             LEFT JOIN {$wpdb->prefix}we_catering_organizations org ON o.organization_id = org.id 
             WHERE o.order_number = %s",
            $order_number
        );

        return $wpdb->get_row( $sql );
    }

    /**
     * Get order items
     *
     * @param int $order_id
     * @return array
     */
    public function get_order_items( $order_id ) {
        global $wpdb;

        $sql = $wpdb->prepare(
            "SELECT oi.*, mi.name as menu_item_name, mi.description as menu_item_description 
             FROM {$this->order_items_table} oi 
             LEFT JOIN {$wpdb->prefix}we_catering_menu_items mi ON oi.menu_item_id = mi.id 
             WHERE oi.order_id = %d 
             ORDER BY oi.id",
            $order_id
        );

        return $wpdb->get_results( $sql );
    }

    /**
     * Get all orders
     *
     * @param array $args
     * @return array
     */
    public function get_all( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'status' => '',
            'organization_id' => 0,
            'user_id' => 0,
            'order_date' => '',
            'orderby' => 'created_at',
            'order' => 'DESC',
            'limit' => 0,
            'offset' => 0,
        );

        $args = wp_parse_args( $args, $defaults );

        $where_conditions = array();
        $where_values = array();

        if ( ! empty( $args['status'] ) ) {
            $where_conditions[] = 'o.status = %s';
            $where_values[] = $args['status'];
        }

        if ( ! empty( $args['organization_id'] ) ) {
            $where_conditions[] = 'o.organization_id = %d';
            $where_values[] = $args['organization_id'];
        }

        if ( ! empty( $args['user_id'] ) ) {
            $where_conditions[] = 'o.user_id = %d';
            $where_values[] = $args['user_id'];
        }

        if ( ! empty( $args['order_date'] ) ) {
            $where_conditions[] = 'o.order_date = %s';
            $where_values[] = $args['order_date'];
        }

        $where_clause = '';
        if ( ! empty( $where_conditions ) ) {
            $where_clause = 'WHERE ' . implode( ' AND ', $where_conditions );
        }

        $order_clause = 'ORDER BY o.' . esc_sql( $args['orderby'] ) . ' ' . esc_sql( $args['order'] );

        $limit_clause = '';
        if ( $args['limit'] > 0 ) {
            $limit_clause = 'LIMIT ' . intval( $args['limit'] );
            if ( $args['offset'] > 0 ) {
                $limit_clause .= ' OFFSET ' . intval( $args['offset'] );
            }
        }

        $sql = "SELECT o.*, u.display_name as customer_name, u.user_email as customer_email, org.name as organization_name 
                FROM {$this->table_name} o 
                LEFT JOIN {$wpdb->users} u ON o.user_id = u.ID 
                LEFT JOIN {$wpdb->prefix}we_catering_organizations org ON o.organization_id = org.id 
                {$where_clause} {$order_clause} {$limit_clause}";

        if ( ! empty( $where_values ) ) {
            $sql = $wpdb->prepare( $sql, $where_values );
        }

        return $wpdb->get_results( $sql );
    }

    /**
     * Update order status
     *
     * @param int $order_id
     * @param string $status
     * @return bool
     */
    public function update_status( $order_id, $status ) {
        global $wpdb;

        $valid_statuses = array( 'pending', 'confirmed', 'closed', 'cancelled' );

        if ( ! in_array( $status, $valid_statuses, true ) ) {
            return false;
        }

        $result = $wpdb->update(
            $this->table_name,
            array( 'status' => $status ),
            array( 'id' => $order_id ),
            array( '%s' ),
            array( '%d' )
        );

        return $result !== false;
    }

    /**
     * Update order
     *
     * @param int $order_id
     * @param array $data
     * @return bool
     */
    public function update( $order_id, $data ) {
        global $wpdb;

        // Check if order window is open for status changes
        if ( isset( $data['status'] ) && ! $this->is_order_window_open() ) {
            return false;
        }

        $update_data = array();

        if ( isset( $data['total_amount'] ) ) {
            $update_data['total_amount'] = floatval( $data['total_amount'] );
        }

        if ( isset( $data['status'] ) ) {
            $update_data['status'] = sanitize_text_field( $data['status'] );
        }

        if ( isset( $data['notes'] ) ) {
            $update_data['notes'] = sanitize_textarea_field( $data['notes'] );
        }

        if ( empty( $update_data ) ) {
            return false;
        }

        $result = $wpdb->update(
            $this->table_name,
            $update_data,
            array( 'id' => $order_id ),
            null,
            array( '%d' )
        );

        return $result !== false;
    }

    /**
     * Delete order
     *
     * @param int $order_id
     * @return bool
     */
    public function delete( $order_id ) {
        global $wpdb;

        // Check if order window is open
        if ( ! $this->is_order_window_open() ) {
            return false;
        }

        // Start transaction
        $wpdb->query( 'START TRANSACTION' );

        try {
            // Delete order items first
            $wpdb->delete(
                $this->order_items_table,
                array( 'order_id' => $order_id ),
                array( '%d' )
            );

            // Delete order
            $result = $wpdb->delete(
                $this->table_name,
                array( 'id' => $order_id ),
                array( '%d' )
            );

            if ( $result === false ) {
                throw new Exception( 'Failed to delete order' );
            }

            // Commit transaction
            $wpdb->query( 'COMMIT' );

            return true;

        } catch ( Exception $e ) {
            // Rollback transaction
            $wpdb->query( 'ROLLBACK' );
            return false;
        }
    }

    /**
     * Get orders summary for dashboard
     *
     * @param string $date
     * @return array
     */
    public function get_daily_summary( $date = null ) {
        global $wpdb;

        if ( ! $date ) {
            $date = current_time( 'Y-m-d' );
        }

        $sql = $wpdb->prepare(
            "SELECT 
                COUNT(*) as total_orders,
                SUM(total_amount) as total_revenue,
                COUNT(DISTINCT user_id) as unique_customers,
                COUNT(DISTINCT organization_id) as unique_organizations
             FROM {$this->table_name} 
             WHERE order_date = %s AND status != 'cancelled'",
            $date
        );

        return $wpdb->get_row( $sql );
    }

    /**
     * Get organization orders summary
     *
     * @param string $date
     * @return array
     */
    public function get_organization_summary( $date = null ) {
        global $wpdb;

        if ( ! $date ) {
            $date = current_time( 'Y-m-d' );
        }

        $sql = $wpdb->prepare(
            "SELECT 
                org.name as organization_name,
                COUNT(o.id) as total_orders,
                SUM(o.total_amount) as total_revenue,
                COUNT(DISTINCT o.user_id) as unique_customers
             FROM {$this->table_name} o 
             LEFT JOIN {$wpdb->prefix}we_catering_organizations org ON o.organization_id = org.id 
             WHERE o.order_date = %s AND o.status != 'cancelled' 
             GROUP BY o.organization_id 
             ORDER BY total_revenue DESC",
            $date
        );

        return $wpdb->get_results( $sql );
    }

    /**
     * Check if order window is open
     *
     * @return bool
     */
    public function is_order_window_open() {
        $settings = get_option( 'we_catering_settings', array(
            'order_window_start'    => '10:00',
            'order_window_end'      => '11:30',
        ) );

        // Get start and end times from settings
        $start_time = isset( $settings['order_window_start'] ) ? $settings['order_window_start'] : '10:00';
        $end_time   = isset( $settings['order_window_end'] ) ? $settings['order_window_end'] : '11:30';
        
        // Get current time in server timezone
        $current_time = current_time( 'H:i' );
        
        // Debug logging
        error_log( 'Order window check - Start time: ' . $start_time );
        error_log( 'Order window check - End time: ' . $end_time );
        error_log( 'Order window check - Current time: ' . $current_time );
        
        // Convert times to comparable format (minutes since midnight)
        $start_minutes = $this->time_to_minutes( $start_time );
        $end_minutes   = $this->time_to_minutes( $end_time );
        $current_minutes = $this->time_to_minutes( $current_time );
        
        error_log( 'Order window check - Start minutes: ' . $start_minutes );
        error_log( 'Order window check - End minutes: ' . $end_minutes );
        error_log( 'Order window check - Current minutes: ' . $current_minutes );
        
        // Handle overnight windows (e.g., 22:00 to 02:00)
        if ( $end_minutes < $start_minutes ) {
            // Window spans midnight
            $is_open = $current_minutes >= $start_minutes || $current_minutes <= $end_minutes;
            error_log( 'Order window check - Overnight window, is_open: ' . ( $is_open ? 'true' : 'false' ) );
            return $is_open;
        } else {
            // Normal window within same day
            $is_open = $current_minutes >= $start_minutes && $current_minutes <= $end_minutes;
            error_log( 'Order window check - Normal window, is_open: ' . ( $is_open ? 'true' : 'false' ) );
            return $is_open;
        }
    }

    /**
     * Convert time string to minutes since midnight
     *
     * @param string $time Time in HH:MM format
     * @return int Minutes since midnight
     */
    private function time_to_minutes( $time ) {
        $parts = explode( ':', $time );
        if ( count( $parts ) !== 2 ) {
            return 0;
        }
        
        $hours = intval( $parts[0] );
        $minutes = intval( $parts[1] );
        
        return ( $hours * 60 ) + $minutes;
    }

    /**
     * Get order window status with details
     *
     * @return array
     */
    public function get_order_window_status() {
        $settings = get_option( 'we_catering_settings', array(
            'order_window_start'    => '10:00',
            'order_window_end'      => '11:30',
        ) );

        $start_time = isset( $settings['order_window_start'] ) ? $settings['order_window_start'] : '10:00';
        $end_time   = isset( $settings['order_window_end'] ) ? $settings['order_window_end'] : '11:30';
        $current_time = current_time( 'H:i' );
        
        $is_open = $this->is_order_window_open();
        
        return array(
            'is_open' => $is_open,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'current_time' => $current_time,
            'message' => $is_open ? 
                sprintf( __( 'Order window is open until %s', 'we-catering' ), $end_time ) :
                sprintf( __( 'Order window is closed. Next window: %s - %s', 'we-catering' ), $start_time, $end_time )
        );
    }

    /**
     * Count orders
     *
     * @param array $args
     * @return int
     */
    public function count( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'status' => '',
            'organization_id' => 0,
            'user_id' => 0,
            'order_date' => '',
        );

        $args = wp_parse_args( $args, $defaults );

        $where_conditions = array();
        $where_values = array();

        if ( ! empty( $args['status'] ) ) {
            $where_conditions[] = 'status = %s';
            $where_values[] = $args['status'];
        }

        if ( ! empty( $args['organization_id'] ) ) {
            $where_conditions[] = 'organization_id = %d';
            $where_values[] = $args['organization_id'];
        }

        if ( ! empty( $args['user_id'] ) ) {
            $where_conditions[] = 'user_id = %d';
            $where_values[] = $args['user_id'];
        }

        if ( ! empty( $args['order_date'] ) ) {
            $where_conditions[] = 'order_date = %s';
            $where_values[] = $args['order_date'];
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
