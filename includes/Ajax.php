<?php

namespace WeLabs\WeCatering;

use WeLabs\WeCatering\Models\MenuItem;
use WeLabs\WeCatering\Models\Organization;
use WeLabs\WeCatering\Models\Order;
use WeLabs\WeCatering\Models\DailyMenu;

/**
 * Ajax class
 *
 * @class Ajax The class that handles all AJAX operations
 */
class Ajax {

    /**
     * Constructor for the Ajax class
     */
    public function __construct() {
        add_action( 'wp_ajax_we_catering_save_menu_item', array( $this, 'save_menu_item' ) );
        add_action( 'wp_ajax_we_catering_save_organization', array( $this, 'save_organization' ) );
        add_action( 'wp_ajax_we_catering_get_menu_items', array( $this, 'get_menu_items' ) );
        add_action( 'wp_ajax_we_catering_get_organizations', array( $this, 'get_organizations' ) );
        add_action( 'wp_ajax_we_catering_delete_menu_item', array( $this, 'delete_menu_item' ) );
        add_action( 'wp_ajax_we_catering_delete_organization', array( $this, 'delete_organization' ) );
        add_action( 'wp_ajax_we_catering_get_dashboard_stats', array( $this, 'get_dashboard_stats' ) );
        add_action( 'wp_ajax_we_catering_get_menu_item_modal', array( $this, 'get_menu_item_modal' ) );
        add_action( 'wp_ajax_we_catering_get_menu_item_data', array( $this, 'get_menu_item_data' ) );

        // Order management
        add_action( 'wp_ajax_we_catering_create_order', array( $this, 'create_order' ) );
        add_action( 'wp_ajax_we_catering_get_orders', array( $this, 'get_orders' ) );
        add_action( 'wp_ajax_we_catering_update_order_status', array( $this, 'update_order_status' ) );
        add_action( 'wp_ajax_we_catering_delete_order', array( $this, 'delete_order' ) );

        // Daily menu management
        add_action( 'wp_ajax_we_catering_set_daily_menu', array( $this, 'set_daily_menu' ) );
        add_action( 'wp_ajax_we_catering_get_daily_menu', array( $this, 'get_daily_menu' ) );
        add_action( 'wp_ajax_we_catering_update_item_availability', array( $this, 'update_item_availability' ) );

        // Public (frontend) endpoints
        add_action( 'wp_ajax_we_catering_public_create_order', array( $this, 'public_create_order' ) );
        add_action( 'wp_ajax_nopriv_we_catering_public_create_order', array( $this, 'public_create_order' ) );
        add_action( 'wp_ajax_we_catering_get_public_daily_menu', array( $this, 'get_public_daily_menu' ) );
        add_action( 'wp_ajax_nopriv_we_catering_get_public_daily_menu', array( $this, 'get_public_daily_menu' ) );
        add_action( 'wp_ajax_we_catering_get_order_window_status', array( $this, 'get_order_window_status' ) );
        add_action( 'wp_ajax_nopriv_we_catering_get_order_window_status', array( $this, 'get_order_window_status' ) );
        add_action( 'wp_ajax_we_catering_test_order_creation', array( $this, 'test_order_creation' ) );

        // Organization user management
        add_action( 'wp_ajax_we_catering_get_organization_users', array( $this, 'get_organization_users' ) );
        add_action( 'wp_ajax_we_catering_get_available_users', array( $this, 'get_available_users' ) );
        add_action( 'wp_ajax_we_catering_add_user_to_organization', array( $this, 'add_user_to_organization' ) );
        add_action( 'wp_ajax_we_catering_create_user_for_organization', array( $this, 'create_user_for_organization' ) );
        add_action( 'wp_ajax_we_catering_remove_user_from_organization', array( $this, 'remove_user_from_organization' ) );
        add_action( 'wp_ajax_we_catering_update_user_role', array( $this, 'update_user_role' ) );

        // Admin: Order details view
        add_action( 'wp_ajax_we_catering_get_order_details', array( $this, 'get_order_details' ) );
    }

    /**
     * Get menu item modal template
     */
    public function get_menu_item_modal() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['nonce'] ), 'we_catering_admin_nonce' ) ) {
            wp_die( 'Security check failed' );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }

        $template_path = WE_CATERING_DIR . '/templates/admin/menu-item-modal.php';

        if ( ! file_exists( $template_path ) ) {
            wp_send_json_error(
                array(
                    'message' => 'Template file not found: ' . $template_path,
                )
            );
        }

        ob_start();
        include $template_path;
        $html = ob_get_clean();

        wp_send_json_success(
            array(
                'html' => $html,
            )
        );
    }

    /**
     * Get menu item data for editing
     */
    public function get_menu_item_data() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['nonce'] ), 'we_catering_admin_nonce' ) ) {
            wp_die( 'Security check failed' );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }

        $menu_item_id = isset( $_POST['menu_item_id'] ) ? intval( wp_unslash( $_POST['menu_item_id'] ) ) : 0;

        if ( ! $menu_item_id ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Invalid menu item ID.', 'we-catering' ),
                )
            );
        }

        $menu_item = new MenuItem();
        $item_data = $menu_item->get_by_id( $menu_item_id );

        if ( ! $item_data ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Menu item not found.', 'we-catering' ),
                )
            );
        }

        // Convert items string to array for Select2
        $items_array = array();
        if ( ! empty( $item_data->items ) ) {
            $items_array = array_map( 'trim', explode( ',', $item_data->items ) );
        }

        wp_send_json_success(
            array(
                'menu_item' => array(
                    'id' => $item_data->id,
                    'name' => $item_data->name,
                    'description' => $item_data->description,
                    'items' => $items_array,
                    'price' => $item_data->price,
                    'category' => $item_data->category,
                    'status' => $item_data->status,
                ),
            )
        );
    }

    /**
     * Save menu item
     */
    public function save_menu_item() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['nonce'] ), 'we_catering_admin_nonce' ) ) {
            wp_die( 'Security check failed' );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }

        // Parse form data
        $raw_form = isset( $_POST['form_data'] ) ? wp_unslash( $_POST['form_data'] ) : '';
        parse_str( $raw_form, $form_data );

        $menu_item = new MenuItem();

        // Handle items field - convert array to comma-separated string
        $items = '';
        if ( isset( $form_data['items'] ) ) {
            if ( is_array( $form_data['items'] ) ) {
                $items = implode( ', ', array_map( 'sanitize_text_field', $form_data['items'] ) );
            } else {
                $items = sanitize_text_field( $form_data['items'] );
            }
        }

        $data = array(
            'name' => sanitize_text_field( $form_data['name'] ),
            'description' => sanitize_textarea_field( $form_data['description'] ),
            'items' => $items,
            'price' => floatval( $form_data['price'] ),
            'category' => sanitize_text_field( $form_data['category'] ),
            'status' => 'active',
        );

        // Check if this is an update or create
        $menu_item_id = isset( $form_data['menu_item_id'] ) ? intval( $form_data['menu_item_id'] ) : 0;

        if ( $menu_item_id > 0 ) {
            // Update existing menu item
            $result = $menu_item->update( $menu_item_id, $data );
            $message = __( 'Menu item updated successfully!', 'we-catering' );
        } else {
            // Create new menu item
            $result = $menu_item->create( $data );
            $message = __( 'Menu item saved successfully!', 'we-catering' );
        }

        if ( $result ) {
            wp_send_json_success(
                array(
					'message' => $message,
					'id' => $menu_item_id > 0 ? $menu_item_id : $result,
                )
            );
        } else {
            wp_send_json_error(
                array(
					'message' => __( 'Failed to save menu item. Please try again.', 'we-catering' ),
                )
            );
        }
    }

    /**
     * Save organization
     */
    public function save_organization() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['nonce'] ), 'we_catering_admin_nonce' ) ) {
            wp_die( 'Security check failed' );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }

        // Parse form data
        parse_str( $_POST['form_data'], $form_data );

        $organization = new Organization();

        $data = array(
            'name' => sanitize_text_field( $form_data['name'] ),
            'description' => sanitize_textarea_field( $form_data['description'] ),
            'contact_person' => sanitize_text_field( $form_data['contact_person'] ),
            'email' => sanitize_email( $form_data['email'] ),
            'phone' => sanitize_text_field( $form_data['phone'] ),
            'status' => 'active',
        );

        $result = $organization->create( $data );

        if ( $result ) {
            wp_send_json_success(
                array(
					'message' => __( 'Organization saved successfully!', 'we-catering' ),
					'id' => $result,
                )
            );
        } else {
            wp_send_json_error(
                array(
					'message' => __( 'Failed to save organization. Please try again.', 'we-catering' ),
                )
            );
        }
    }

    /**
     * Get menu items
     */
    public function get_menu_items() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['nonce'] ), 'we_catering_admin_nonce' ) ) {
            wp_die( 'Security check failed' );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }

        $menu_item = new MenuItem();

        $args = array(
            'status' => 'active',
            'orderby' => 'name',
            'order' => 'ASC',
        );

        if ( ! empty( $_POST['category'] ) ) {
            $args['category'] = sanitize_text_field( $_POST['category'] );
        }

        $menu_items = $menu_item->get_all( $args );

        wp_send_json_success(
            array(
				'menu_items' => $menu_items,
            )
        );
    }

    /**
     * Get organizations
     */
    public function get_organizations() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['nonce'] ), 'we_catering_admin_nonce' ) ) {
            wp_die( 'Security check failed' );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }

        $organization = new Organization();

        $args = array(
            'status' => 'active',
            'orderby' => 'name',
            'order' => 'ASC',
        );

        $organizations = $organization->get_all( $args );

        wp_send_json_success(
            array(
				'organizations' => $organizations,
            )
        );
    }

    /**
     * Delete menu item
     */
    public function delete_menu_item() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['nonce'] ), 'we_catering_admin_nonce' ) ) {
            wp_die( 'Security check failed' );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }

        $menu_item_id = isset( $_POST['menu_item_id'] ) ? intval( wp_unslash( $_POST['menu_item_id'] ) ) : 0;

        if ( ! $menu_item_id ) {
            wp_send_json_error(
                array(
					'message' => __( 'Invalid menu item ID.', 'we-catering' ),
                )
            );
        }

        $menu_item = new MenuItem();
        $result = $menu_item->delete( $menu_item_id );

        if ( $result ) {
            wp_send_json_success(
                array(
					'message' => __( 'Menu item deleted successfully!', 'we-catering' ),
                )
            );
        } else {
            wp_send_json_error(
                array(
					'message' => __( 'Failed to delete menu item. Please try again.', 'we-catering' ),
                )
            );
        }
    }

    /**
     * Delete organization
     */
    public function delete_organization() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'we_catering_admin_nonce' ) ) {
            wp_die( 'Security check failed' );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }

        $organization_id = intval( $_POST['organization_id'] );

        if ( ! $organization_id ) {
            wp_send_json_error(
                array(
					'message' => __( 'Invalid organization ID.', 'we-catering' ),
                )
            );
        }

        $organization = new Organization();
        $result = $organization->delete( $organization_id );

        if ( $result ) {
            wp_send_json_success(
                array(
					'message' => __( 'Organization deleted successfully!', 'we-catering' ),
                )
            );
        } else {
            wp_send_json_error(
                array(
					'message' => __( 'Failed to delete organization. Please try again.', 'we-catering' ),
                )
            );
        }
    }

    /**
     * Get dashboard statistics
     */
    public function get_dashboard_stats() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'we_catering_admin_nonce' ) ) {
            wp_die( 'Security check failed' );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }

        $menu_item = new MenuItem();
        $organization = new Organization();

        $stats = array(
            'total_menu_items' => $menu_item->count( array( 'status' => 'active' ) ),
            'total_organizations' => $organization->count( array( 'status' => 'active' ) ),
            'total_orders' => 0, // Will be implemented when Order model is created
            'total_revenue' => 0, // Will be implemented when Order model is created
        );

        wp_send_json_success(
            array(
				'stats' => $stats,
            )
        );
    }

    /**
     * Create order
     */
    public function create_order() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'we_catering_admin_nonce' ) ) {
            wp_die( 'Security check failed' );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }

        $order = new Order();

        $data = array(
            'user_id' => intval( $_POST['user_id'] ),
            'organization_id' => intval( $_POST['organization_id'] ),
            'order_date' => sanitize_text_field( $_POST['order_date'] ),
            'total_amount' => floatval( $_POST['total_amount'] ),
            'notes' => sanitize_textarea_field( $_POST['notes'] ),
            'order_items' => json_decode( stripslashes( $_POST['order_items'] ), true ),
        );

        $result = $order->create( $data );

        if ( $result ) {
            wp_send_json_success(
                array(
					'message' => __( 'Order created successfully!', 'we-catering' ),
					'order_id' => $result,
                )
            );
        } else {
            wp_send_json_error(
                array(
					'message' => __( 'Failed to create order. Please check if order window is open.', 'we-catering' ),
                )
            );
        }
    }

    /**
     * Get orders
     */
    public function get_orders() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'we_catering_admin_nonce' ) ) {
            wp_die( 'Security check failed' );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }

        $order = new Order();

        $args = array(
            'orderby' => 'created_at',
            'order' => 'DESC',
        );

        if ( ! empty( $_POST['status'] ) ) {
            $args['status'] = sanitize_text_field( $_POST['status'] );
        }

        if ( ! empty( $_POST['organization_id'] ) ) {
            $args['organization_id'] = intval( $_POST['organization_id'] );
        }

        if ( ! empty( $_POST['order_date'] ) ) {
            $args['order_date'] = sanitize_text_field( $_POST['order_date'] );
        }

        $orders = $order->get_all( $args );

        wp_send_json_success(
            array(
				'orders' => $orders,
            )
        );
    }

    /**
     * Update order status
     */
    public function update_order_status() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'we_catering_admin_nonce' ) ) {
            wp_die( 'Security check failed' );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }

        $order_id = intval( $_POST['order_id'] );
        $status = sanitize_text_field( $_POST['status'] );

        if ( ! $order_id ) {
            wp_send_json_error(
                array(
					'message' => __( 'Invalid order ID.', 'we-catering' ),
                )
            );
        }

        $order = new Order();
        $result = $order->update_status( $order_id, $status );

        if ( $result ) {
            wp_send_json_success(
                array(
					'message' => __( 'Order status updated successfully!', 'we-catering' ),
                )
            );
        } else {
            wp_send_json_error(
                array(
					'message' => __( 'Failed to update order status.', 'we-catering' ),
                )
            );
        }
    }

    /**
     * Delete order
     */
    public function delete_order() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'we_catering_admin_nonce' ) ) {
            wp_die( 'Security check failed' );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }

        $order_id = intval( $_POST['order_id'] );

        if ( ! $order_id ) {
            wp_send_json_error(
                array(
					'message' => __( 'Invalid order ID.', 'we-catering' ),
                )
            );
        }

        $order = new Order();
        $result = $order->delete( $order_id );

        if ( $result ) {
            wp_send_json_success(
                array(
					'message' => __( 'Order deleted successfully!', 'we-catering' ),
                )
            );
        } else {
            wp_send_json_error(
                array(
					'message' => __( 'Failed to delete order. Please check if order window is open.', 'we-catering' ),
                )
            );
        }
    }

    /**
     * Set daily menu
     */
    public function set_daily_menu() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'we_catering_admin_nonce' ) ) {
            wp_die( 'Security check failed' );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }

        $date = sanitize_text_field( $_POST['date'] );
        $menu_items = json_decode( stripslashes( $_POST['menu_items'] ), true );

        if ( ! $date ) {
            wp_send_json_error(
                array(
					'message' => __( 'Invalid date.', 'we-catering' ),
                )
            );
        }

        $daily_menu = new DailyMenu();
        $result = $daily_menu->set_daily_menu( $date, $menu_items );

        if ( $result ) {
            wp_send_json_success(
                array(
					'message' => __( 'Daily menu set successfully!', 'we-catering' ),
                )
            );
        } else {
            wp_send_json_error(
                array(
					'message' => __( 'Failed to set daily menu.', 'we-catering' ),
                )
            );
        }
    }

    /**
     * Get daily menu
     */
    public function get_daily_menu() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'we_catering_admin_nonce' ) ) {
            wp_die( 'Security check failed' );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }

        $date = sanitize_text_field( $_POST['date'] );

        if ( ! $date ) {
            $date = current_time( 'Y-m-d' );
        }

        $daily_menu = new DailyMenu();
        $menu_items = $daily_menu->get_daily_menu_with_status( $date );

        wp_send_json_success(
            array(
				'menu_items' => $menu_items,
            )
        );
    }

    /**
     * Update item availability
     */
    public function update_item_availability() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'we_catering_admin_nonce' ) ) {
            wp_die( 'Security check failed' );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }

        $menu_item_id = intval( $_POST['menu_item_id'] );
        $date = sanitize_text_field( $_POST['date'] );
        $is_available = (bool) $_POST['is_available'];

        if ( ! $menu_item_id || ! $date ) {
            wp_send_json_error(
                array(
					'message' => __( 'Invalid menu item ID or date.', 'we-catering' ),
                )
            );
        }

        $daily_menu = new DailyMenu();
        $result = $daily_menu->update_item_availability( $menu_item_id, $date, $is_available );

        if ( $result ) {
            wp_send_json_success(
                array(
					'message' => __( 'Item availability updated successfully!', 'we-catering' ),
                )
            );
        } else {
            wp_send_json_error(
                array(
					'message' => __( 'Failed to update item availability.', 'we-catering' ),
                )
            );
        }
    }

    /**
     * Frontend: Create order for logged-in users
     */
    public function public_create_order() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['nonce'] ), 'we_catering_public_nonce' ) ) {
            wp_die( 'Security check failed' );
        }

        // Require logged-in user
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'You must be logged in to place an order.', 'we-catering' ) ) );
        }

        $user_id = get_current_user_id();

        $order = new Order();
        $organization = new Organization();

        // Get user's organization ID
        $organization_id = $organization->get_user_organization_id( $user_id );

        // If user doesn't have an organization, use the one from POST
        if ( ! $organization_id ) {
            $organization_id = isset( $_POST['organization_id'] ) ? absint( $_POST['organization_id'] ) : 0;
        }

        // If still no organization ID, get the first available organization or create a default one
        if ( ! $organization_id ) {
            $organizations = $organization->get_all( array( 'status' => 'active', 'limit' => 1 ) );
            if ( ! empty( $organizations ) ) {
                $organization_id = $organizations[0]->id;
            } else {
                // Create a default organization if none exists
                $default_org_data = array(
                    'name' => 'Default Organization',
                    'description' => 'Default organization for orders',
                    'contact_person' => 'Admin',
                    'email' => get_option( 'admin_email' ),
                    'phone' => '',
                    'status' => 'active'
                );
                $organization_id = $organization->create( $default_org_data );
            }
        }

        // Final validation - ensure we have a valid organization ID
        if ( ! $organization_id ) {
            wp_send_json_error( array( 'message' => __( 'No valid organization found. Please contact administrator.', 'we-catering' ) ) );
        }

        $order_items_json = isset( $_POST['order_items'] ) ? wp_unslash( $_POST['order_items'] ) : '[]';
        $data = array(
            'user_id'         => $user_id,
            'organization_id' => $organization_id,
            'order_date'      => isset( $_POST['order_date'] ) ? sanitize_text_field( wp_unslash( $_POST['order_date'] ) ) : current_time( 'Y-m-d' ),
            'total_amount'    => isset( $_POST['total_amount'] ) ? floatval( wp_unslash( $_POST['total_amount'] ) ) : 0,
            'notes'           => isset( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '',
            'order_items'     => json_decode( $order_items_json, true ),
        );

        // Basic validation
        if ( empty( $data['order_items'] ) ) {
            wp_send_json_error( array( 'message' => __( 'No items selected.', 'we-catering' ) ) );
        }

        // Debug logging
        error_log( 'Order creation attempt - Organization ID: ' . $organization_id );
        error_log( 'Order creation attempt - User ID: ' . $user_id );
        error_log( 'Order creation attempt - Order window open: ' . ( $order->is_order_window_open() ? 'true' : 'false' ) );
        
        // Debug settings
        $settings = get_option( 'we_catering_settings', array() );
        error_log( 'Current settings: ' . wp_json_encode( $settings ) );

        // Always create a new order (no merging)
        $result = $order->create( $data );

        error_log( 'order create or not -> ' . wp_json_encode( $result, JSON_PRETTY_PRINT ) );

        if ( $result ) {
            wp_send_json_success(
                array(
					'message'  => __( 'Order created successfully!', 'we-catering' ),
					'order_id' => $result,
                )
            );
        }

        // Provide detailed error messages
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'Please log in to place an order.', 'we-catering' ) ) );
        }
        if ( ! $order->is_order_window_open() ) {
            $status = $order->get_order_window_status();
            wp_send_json_error( array( 'message' => sprintf( __( 'Order window is closed. Next window: %1$s - %2$s', 'we-catering' ), $status['start_time'], $status['end_time'] ) ) );
        }

        // Log DB error for debugging
        global $wpdb;
        if ( ! empty( $wpdb->last_error ) ) {
            error_log( 'WeCatering order create failed: ' . $wpdb->last_error );
        }

        // If admin, expose DB error to help debugging
        if ( current_user_can( 'manage_options' ) && ! empty( $wpdb->last_error ) ) {
            wp_send_json_error( array( 'message' => sprintf( __( 'Failed to place order: %s', 'we-catering' ), $wpdb->last_error ) ) );
        }

        wp_send_json_error( array( 'message' => __( 'Failed to place order due to a server error. Please try again.', 'we-catering' ) ) );
    }

    /**
     * Frontend: Get daily menu (for dynamic refresh if needed)
     */
    public function get_public_daily_menu() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['nonce'] ), 'we_catering_public_nonce' ) ) {
            wp_die( 'Security check failed' );
        }

        $date = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : current_time( 'Y-m-d' );

        $daily_menu = new DailyMenu();
        $menu_items = $daily_menu->get_daily_menu_with_status( $date );

        wp_send_json_success( array( 'menu_items' => $menu_items ) );
    }

    /**
     * Get order window status
     */
    public function get_order_window_status() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['nonce'] ), 'we_catering_public_nonce' ) ) {
            wp_die( 'Security check failed' );
        }

        $order = new Order();
        $window_status = $order->get_order_window_status();

        wp_send_json_success( $window_status );
    }

    /**
     * Test order creation
     */
    public function test_order_creation() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['nonce'] ), 'we_catering_admin_nonce' ) ) {
            wp_die( 'Security check failed' );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }

        $order = new Order();
        $organization = new Organization();

        // Get first available organization
        $organizations = $organization->get_all( array( 'status' => 'active', 'limit' => 1 ) );
        if ( empty( $organizations ) ) {
            wp_send_json_error( array( 'message' => 'No organizations found. Please create an organization first.' ) );
        }

        $organization_id = $organizations[0]->id;
        $user_id = get_current_user_id();

        // Test data
        $test_data = array(
            'user_id'         => $user_id,
            'organization_id' => $organization_id,
            'order_date'      => current_time( 'Y-m-d' ),
            'total_amount'    => 10.00,
            'notes'           => 'Test order from admin panel',
            'order_items'     => array(
                array(
                    'menu_item_id' => 1,
                    'quantity'     => 1,
                    'unit_price'   => 10.00
                )
            ),
        );

        // Check order window status
        $window_status = $order->get_order_window_status();
        if ( ! $window_status['is_open'] ) {
            wp_send_json_error( array( 
                'message' => 'Order window is closed. ' . $window_status['message'] 
            ) );
        }

        $result = $order->create( $test_data );

        if ( $result ) {
            wp_send_json_success( array( 
                'message' => 'Test order created successfully! Order ID: ' . $result,
                'order_id' => $result,
                'window_status' => $window_status
            ) );
        } else {
            wp_send_json_error( array( 'message' => 'Failed to create test order. Check debug log for details.' ) );
        }
    }

    /**
     * Get organization users
     */
    public function get_organization_users() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['nonce'] ), 'we_catering_admin_nonce' ) ) {
            wp_die( 'Security check failed' );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }

        $organization_id = isset( $_POST['organization_id'] ) ? intval( wp_unslash( $_POST['organization_id'] ) ) : 0;

        if ( ! $organization_id ) {
            wp_send_json_error(
                array(
					'message' => __( 'Invalid organization ID.', 'we-catering' ),
                )
            );
        }

        $organization = new Organization();
        $users = $organization->get_users( $organization_id );

        wp_send_json_success(
            array(
				'users' => $users,
            )
        );
    }

    /**
     * Get available users (not already in organization)
     */
    public function get_available_users() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['nonce'] ), 'we_catering_admin_nonce' ) ) {
            wp_die( 'Security check failed' );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }

        $organization_id = isset( $_POST['organization_id'] ) ? intval( wp_unslash( $_POST['organization_id'] ) ) : 0;

        global $wpdb;
        $user_organizations_table = $wpdb->prefix . 'we_catering_user_organizations';

        // Get users not already in this organization
        $sql = $wpdb->prepare(
            "SELECT ID, display_name, user_email 
             FROM {$wpdb->users} 
             WHERE ID NOT IN (
                 SELECT user_id FROM {$user_organizations_table} WHERE organization_id = %d
             )
             ORDER BY display_name",
            $organization_id
        );

        $available_users = $wpdb->get_results( $sql );

        wp_send_json_success(
            array(
				'users' => $available_users,
            )
        );
    }

    /**
     * Add user to organization
     */
    public function add_user_to_organization() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['nonce'] ), 'we_catering_admin_nonce' ) ) {
            wp_die( 'Security check failed' );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }

        $user_id = isset( $_POST['user_id'] ) ? intval( wp_unslash( $_POST['user_id'] ) ) : 0;
        $organization_id = isset( $_POST['organization_id'] ) ? intval( wp_unslash( $_POST['organization_id'] ) ) : 0;
        $role = isset( $_POST['role'] ) ? sanitize_text_field( wp_unslash( $_POST['role'] ) ) : 'user';

        if ( ! $user_id || ! $organization_id ) {
            wp_send_json_error(
                array(
					'message' => __( 'Invalid user or organization ID.', 'we-catering' ),
                )
            );
        }

        $organization = new Organization();
        $result = $organization->add_user( $user_id, $organization_id, $role );

        if ( $result ) {
            wp_send_json_success(
                array(
					'message' => __( 'User added to organization successfully!', 'we-catering' ),
                )
            );
        } else {
            wp_send_json_error(
                array(
					'message' => __( 'Failed to add user to organization.', 'we-catering' ),
                )
            );
        }
    }

    /**
     * Create new user and add to organization
     */
    public function create_user_for_organization() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['nonce'] ), 'we_catering_admin_nonce' ) ) {
            wp_die( 'Security check failed' );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }

        $username = isset( $_POST['username'] ) ? sanitize_user( wp_unslash( $_POST['username'] ) ) : '';
        $password = isset( $_POST['password'] ) ? sanitize_text_field( wp_unslash( $_POST['password'] ) ) : '';
        $first_name = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
        $last_name = isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
        $email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
        $organization_id = isset( $_POST['organization_id'] ) ? intval( wp_unslash( $_POST['organization_id'] ) ) : 0;
        $role = isset( $_POST['role'] ) ? sanitize_text_field( wp_unslash( $_POST['role'] ) ) : 'user';

        // Validate input
        if ( empty( $username ) || empty( $password ) || empty( $first_name ) || empty( $last_name ) || empty( $email ) || ! $organization_id ) {
            wp_send_json_error(
                array(
					'message' => __( 'All fields are required.', 'we-catering' ),
                )
            );
        }

        // Check if username already exists
        if ( username_exists( $username ) ) {
            wp_send_json_error(
                array(
					'message' => __( 'Username already exists. Please choose a different username.', 'we-catering' ),
                )
            );
        }

        // Check if email already exists
        if ( email_exists( $email ) ) {
            wp_send_json_error(
                array(
					'message' => __( 'Email already exists. Please use a different email or add the existing user.', 'we-catering' ),
                )
            );
        }

        // Create WordPress user
        $user_id = wp_create_user( $username, $password, $email );

        if ( is_wp_error( $user_id ) ) {
            wp_send_json_error(
                array(
					'message' => __( 'Failed to create user: ', 'we-catering' ) . $user_id->get_error_message(),
                )
            );
        }

        // Update user details
        wp_update_user(
            array(
				'ID' => $user_id,
				'first_name' => $first_name,
				'last_name' => $last_name,
				'display_name' => $first_name . ' ' . $last_name,
				'role' => 'subscriber', // Set as subscriber
            )
        );

        // Add user to organization
        $organization = new Organization();
        $result = $organization->add_user( $user_id, $organization_id, $role );

        if ( $result ) {
            // Send notification email (password reset link)
            $this->send_user_creation_email( $user_id, $password );

            wp_send_json_success(
                array(
					'message' => __( 'User created and added to organization successfully! An email has been sent to the user.', 'we-catering' ),
					'user_id' => $user_id,
                )
            );
        } else {
            // Clean up if organization addition failed
            wp_delete_user( $user_id );
            wp_send_json_error(
                array(
					'message' => __( 'Failed to add user to organization. User creation rolled back.', 'we-catering' ),
                )
            );
        }
    }

    /**
     * Generate unique username
     */
    private function generate_unique_username( $username ) {
        $original_username = $username;
        $counter = 1;

        while ( username_exists( $username ) ) {
            $username = $original_username . $counter;
            ++$counter;
        }

        return $username;
    }

    /**
     * Send user creation email with password reset instructions
     */
    private function send_user_creation_email( $user_id, $password ) {
        $user = get_userdata( $user_id );
        $admin_email = get_option( 'admin_email' );
        $site_name = get_bloginfo( 'name' );

        $subject = sprintf( __( 'Your account has been created on %s', 'we-catering' ), $site_name );

        $message = sprintf( __( 'Hello %s,', 'we-catering' ), $user->first_name ) . "\n\n";
        $message .= sprintf( __( 'An account has been created for you on %s.', 'we-catering' ), $site_name ) . "\n\n";
        $message .= __( 'Your login details:', 'we-catering' ) . "\n";
        $message .= sprintf( __( 'Username: %s', 'we-catering' ), $user->user_login ) . "\n";
        $message .= sprintf( __( 'Password: %s', 'we-catering' ), $password ) . "\n\n";
        $message .= __( 'For security reasons, we recommend that you reset your password after first login.', 'we-catering' ) . "\n\n";
        $message .= __( 'You can reset your password here:', 'we-catering' ) . "\n";
        $message .= wp_lostpassword_url() . "\n\n";
        $message .= __( 'Thank you!', 'we-catering' ) . "\n";

        wp_mail( $user->user_email, $subject, $message );
    }

    /**
     * Remove user from organization
     */
    public function remove_user_from_organization() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['nonce'] ), 'we_catering_admin_nonce' ) ) {
            wp_die( 'Security check failed' );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }

        $user_id = isset( $_POST['user_id'] ) ? intval( wp_unslash( $_POST['user_id'] ) ) : 0;
        $organization_id = isset( $_POST['organization_id'] ) ? intval( wp_unslash( $_POST['organization_id'] ) ) : 0;

        if ( ! $user_id || ! $organization_id ) {
            wp_send_json_error(
                array(
					'message' => __( 'Invalid user or organization ID.', 'we-catering' ),
                )
            );
        }

        $organization = new Organization();
        $result = $organization->remove_user( $user_id, $organization_id );

        if ( $result ) {
            wp_send_json_success(
                array(
					'message' => __( 'User removed from organization successfully!', 'we-catering' ),
                )
            );
        } else {
            wp_send_json_error(
                array(
					'message' => __( 'Failed to remove user from organization.', 'we-catering' ),
                )
            );
        }
    }

    /**
     * Update user role in organization
     */
    public function update_user_role() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['nonce'] ), 'we_catering_admin_nonce' ) ) {
            wp_die( 'Security check failed' );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }

        $user_id = isset( $_POST['user_id'] ) ? intval( wp_unslash( $_POST['user_id'] ) ) : 0;
        $organization_id = isset( $_POST['organization_id'] ) ? intval( wp_unslash( $_POST['organization_id'] ) ) : 0;
        $role = isset( $_POST['role'] ) ? sanitize_text_field( wp_unslash( $_POST['role'] ) ) : 'user';

        if ( ! $user_id || ! $organization_id ) {
            wp_send_json_error(
                array(
					'message' => __( 'Invalid user or organization ID.', 'we-catering' ),
                )
            );
        }

        global $wpdb;
        $user_organizations_table = $wpdb->prefix . 'we_catering_user_organizations';

        $result = $wpdb->update(
            $user_organizations_table,
            array( 'role' => $role ),
            array(
                'user_id' => $user_id,
                'organization_id' => $organization_id,
            ),
            array( '%s' ),
            array( '%d', '%d' )
        );

        if ( $result !== false ) {
            wp_send_json_success(
                array(
					'message' => __( 'User role updated successfully!', 'we-catering' ),
                )
            );
        } else {
            wp_send_json_error(
                array(
					'message' => __( 'Failed to update user role.', 'we-catering' ),
                )
            );
        }
    }

    /**
     * Get single order details (admin)
     */
    public function get_order_details() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['nonce'] ), 'we_catering_admin_nonce' ) ) {
            wp_die( 'Security check failed' );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }

        $order_id = isset( $_POST['order_id'] ) ? intval( wp_unslash( $_POST['order_id'] ) ) : 0;
        if ( ! $order_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid order ID.', 'we-catering' ) ) );
        }

        $order = new Order();
        $order_row = $order->get_by_id( $order_id );
        if ( ! $order_row ) {
            wp_send_json_error( array( 'message' => __( 'Order not found.', 'we-catering' ) ) );
        }

        $items = $order->get_order_items( $order_id );
        $items_out = array();
        foreach ( (array) $items as $it ) {
            $items_out[] = array(
                'menu_item_id' => (int) $it->menu_item_id,
                'menu_item_name' => (string) $it->menu_item_name,
                'quantity' => (int) $it->quantity,
                'unit_price' => (float) $it->unit_price,
                'total_price' => (float) $it->total_price,
            );
        }

        wp_send_json_success( array(
            'order' => array(
                'id' => (int) $order_row->id,
                'order_number' => (string) $order_row->order_number,
                'customer_name' => (string) $order_row->customer_name,
                'customer_email' => (string) $order_row->customer_email,
                'organization_name' => (string) $order_row->organization_name,
                'order_date' => (string) $order_row->order_date,
                'status' => (string) $order_row->status,
                'total_amount' => (float) $order_row->total_amount,
                'notes' => (string) $order_row->notes,
            ),
            'items' => $items_out,
        ) );
    }
}
