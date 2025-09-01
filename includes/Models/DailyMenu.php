<?php

namespace WeLabs\WeCatering\Models;

/**
 * DailyMenu Model
 *
 * @class DailyMenu The class that handles daily menu operations
 */
class DailyMenu {

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
        $this->table_name = $wpdb->prefix . 'we_catering_daily_menus';
    }

    /**
     * Set daily menu items
     *
     * @param string $date
     * @param array $menu_items
     * @return bool
     */
    public function set_daily_menu( $date, $menu_items ) {
        global $wpdb;

        // Start transaction
        $wpdb->query( 'START TRANSACTION' );

        try {
            // Remove existing menu items for this date
            $wpdb->delete(
                $this->table_name,
                array( 'menu_date' => $date ),
                array( '%s' )
            );

            // Insert new menu items
            if ( ! empty( $menu_items ) ) {
                foreach ( $menu_items as $menu_item_id ) {
                    $insert_data = array(
                        'menu_date' => $date,
                        'menu_item_id' => intval( $menu_item_id ),
                        'is_available' => 1,
                    );

                    $result = $wpdb->insert( $this->table_name, $insert_data );

                    if ( $result === false ) {
                        throw new Exception( 'Failed to insert menu item' );
                    }
                }
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
     * Get daily menu items
     *
     * @param string $date
     * @return array
     */
    public function get_daily_menu( $date ) {
        global $wpdb;

        $menu_items_table = $wpdb->prefix . 'we_catering_menu_items';

        $sql = $wpdb->prepare(
            "SELECT dm.*, mi.name, mi.description, mi.price, mi.category, mi.dietary_type 
             FROM {$this->table_name} dm 
             INNER JOIN {$menu_items_table} mi ON dm.menu_item_id = mi.id 
             WHERE dm.menu_date = %s AND dm.is_available = 1 AND mi.status = 'active' 
             ORDER BY mi.category, mi.name",
            $date
        );

        return $wpdb->get_results( $sql );
    }

    /**
     * Get daily menu items with availability status
     *
     * @param string $date
     * @return array
     */
    public function get_daily_menu_with_status( $date ) {
        global $wpdb;

        $menu_items_table = $wpdb->prefix . 'we_catering_menu_items';

        $sql = $wpdb->prepare(
            "SELECT mi.id, mi.name, mi.description, mi.price, mi.category, mi.dietary_type, 
                    COALESCE(dm.is_available, 0) as is_available 
             FROM {$menu_items_table} mi 
             LEFT JOIN {$this->table_name} dm ON mi.id = dm.menu_item_id AND dm.menu_date = %s 
             WHERE mi.status = 'active' 
             ORDER BY mi.category, mi.name",
            $date
        );

        return $wpdb->get_results( $sql );
    }

    /**
     * Check if menu item is available for date
     *
     * @param int $menu_item_id
     * @param string $date
     * @return bool
     */
    public function is_item_available( $menu_item_id, $date ) {
        global $wpdb;

        $sql = $wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} 
             WHERE menu_item_id = %d AND menu_date = %s AND is_available = 1",
            $menu_item_id,
            $date
        );

        return (bool) $wpdb->get_var( $sql );
    }

    /**
     * Update menu item availability
     *
     * @param int $menu_item_id
     * @param string $date
     * @param bool $is_available
     * @return bool
     */
    public function update_item_availability( $menu_item_id, $date, $is_available ) {
        global $wpdb;

        $exists = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} 
             WHERE menu_item_id = %d AND menu_date = %s",
            $menu_item_id,
            $date
        ) );

        if ( $exists ) {
            // Update existing record
            $result = $wpdb->update(
                $this->table_name,
                array( 'is_available' => $is_available ? 1 : 0 ),
                array(
                    'menu_item_id' => $menu_item_id,
                    'menu_date' => $date,
                ),
                array( '%d' ),
                array( '%d', '%s' )
            );
        } else {
            // Insert new record
            $result = $wpdb->insert(
                $this->table_name,
                array(
                    'menu_item_id' => $menu_item_id,
                    'menu_date' => $date,
                    'is_available' => $is_available ? 1 : 0,
                ),
                array( '%d', '%s', '%d' )
            );
        }

        return $result !== false;
    }

    /**
     * Get menu items by category for a specific date
     *
     * @param string $date
     * @return array
     */
    public function get_menu_by_category( $date ) {
        $menu_items = $this->get_daily_menu( $date );
        $categorized = array();

        foreach ( $menu_items as $item ) {
            $category = $item->category ?: 'Other';
            if ( ! isset( $categorized[ $category ] ) ) {
                $categorized[ $category ] = array();
            }
            $categorized[ $category ][] = $item;
        }

        return $categorized;
    }

    /**
     * Get available dates for menu items
     *
     * @param int $menu_item_id
     * @return array
     */
    public function get_available_dates( $menu_item_id ) {
        global $wpdb;

        $sql = $wpdb->prepare(
            "SELECT menu_date FROM {$this->table_name} 
             WHERE menu_item_id = %d AND is_available = 1 
             ORDER BY menu_date",
            $menu_item_id
        );

        return $wpdb->get_col( $sql );
    }

    /**
     * Copy menu from one date to another
     *
     * @param string $from_date
     * @param string $to_date
     * @return bool
     */
    public function copy_menu( $from_date, $to_date ) {
        global $wpdb;

        // Get menu items from source date
        $source_items = $wpdb->get_col( $wpdb->prepare(
            "SELECT menu_item_id FROM {$this->table_name} 
             WHERE menu_date = %s AND is_available = 1",
            $from_date
        ) );

        if ( empty( $source_items ) ) {
            return false;
        }

        // Set menu for target date
        return $this->set_daily_menu( $to_date, $source_items );
    }

    /**
     * Get menu statistics
     *
     * @param string $date
     * @return array
     */
    public function get_menu_stats( $date ) {
        global $wpdb;

        $menu_items_table = $wpdb->prefix . 'we_catering_menu_items';

        $sql = $wpdb->prepare(
            "SELECT 
                COUNT(dm.menu_item_id) as total_items,
                COUNT(CASE WHEN dm.is_available = 1 THEN 1 END) as available_items,
                COUNT(CASE WHEN dm.is_available = 0 THEN 1 END) as unavailable_items,
                COUNT(DISTINCT mi.category) as total_categories
             FROM {$this->table_name} dm 
             INNER JOIN {$menu_items_table} mi ON dm.menu_item_id = mi.id 
             WHERE dm.menu_date = %s AND mi.status = 'active'",
            $date
        );

        return $wpdb->get_row( $sql );
    }

    /**
     * Delete daily menu for a specific date
     *
     * @param string $date
     * @return bool
     */
    public function delete_daily_menu( $date ) {
        global $wpdb;

        $result = $wpdb->delete(
            $this->table_name,
            array( 'menu_date' => $date ),
            array( '%s' )
        );

        return $result !== false;
    }

    /**
     * Get upcoming menu dates
     *
     * @param int $limit
     * @return array
     */
    public function get_upcoming_dates( $limit = 7 ) {
        global $wpdb;

        $sql = $wpdb->prepare(
            "SELECT DISTINCT menu_date FROM {$this->table_name} 
             WHERE menu_date >= %s 
             ORDER BY menu_date 
             LIMIT %d",
            current_time( 'Y-m-d' ),
            $limit
        );

        return $wpdb->get_col( $sql );
    }
}
