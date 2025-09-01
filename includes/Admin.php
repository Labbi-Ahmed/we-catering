<?php

namespace WeLabs\WeCatering;

/**
 * Admin class
 *
 * @class Admin The class that handles all admin functionality
 */
class Admin {

    /**
     * Constructor for the Admin class
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'init_admin' ) );
    }

    /**
     * Initialize admin functionality
     */
    public function init_admin() {
        // Admin initialization code will go here
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            __( 'We Catering', 'we-catering' ),
            __( 'We Catering', 'we-catering' ),
            'manage_options',
            'we-catering',
            array( $this, 'dashboard_page' ),
            'dashicons-food',
            30
        );

        // Dashboard submenu
        add_submenu_page(
            'we-catering',
            __( 'Dashboard', 'we-catering' ),
            __( 'Dashboard', 'we-catering' ),
            'manage_options',
            'we-catering',
            array( $this, 'dashboard_page' )
        );

        // Menu Management submenu
        add_submenu_page(
            'we-catering',
            __( 'Menu Management', 'we-catering' ),
            __( 'Menu Management', 'we-catering' ),
            'manage_options',
            'we-catering-menu',
            array( $this, 'menu_management_page' )
        );

        // Order Management submenu
        add_submenu_page(
            'we-catering',
            __( 'Order Management', 'we-catering' ),
            __( 'Order Management', 'we-catering' ),
            'manage_options',
            'we-catering-orders',
            array( $this, 'order_management_page' )
        );

        // Organizations submenu
        add_submenu_page(
            'we-catering',
            __( 'Organizations', 'we-catering' ),
            __( 'Organizations', 'we-catering' ),
            'manage_options',
            'we-catering-organizations',
            array( $this, 'organizations_page' )
        );

        // Reports submenu
        add_submenu_page(
            'we-catering',
            __( 'Reports', 'we-catering' ),
            __( 'Reports', 'we-catering' ),
            'manage_options',
            'we-catering-reports',
            array( $this, 'reports_page' )
        );

        // Settings submenu
        add_submenu_page(
            'we-catering',
            __( 'Settings', 'we-catering' ),
            __( 'Settings', 'we-catering' ),
            'manage_options',
            'we-catering-settings',
            array( $this, 'settings_page' )
        );
    }

    /**
     * Dashboard page callback
     */
    public function dashboard_page() {
        $template = $this->get_template( 'admin/dashboard.php' );
        if ( file_exists( $template ) ) {
            include $template;
        } else {
            echo '<div class="wrap"><h1>' . esc_html__( 'Dashboard', 'we-catering' ) . '</h1><p>' . esc_html__( 'Dashboard template not found.', 'we-catering' ) . '</p></div>';
        }
    }

    /**
     * Menu Management page callback
     */
    public function menu_management_page() {
        $template = $this->get_template( 'admin/menu-management.php' );
        if ( file_exists( $template ) ) {
            include $template;
        } else {
            echo '<div class="wrap"><h1>' . esc_html__( 'Menu Management', 'we-catering' ) . '</h1><p>' . esc_html__( 'Menu management template not found.', 'we-catering' ) . '</p></div>';
        }
    }

    /**
     * Order Management page callback
     */
    public function order_management_page() {
        $template = $this->get_template( 'admin/order-management.php' );
        if ( file_exists( $template ) ) {
            include $template;
        } else {
            echo '<div class="wrap"><h1>' . esc_html__( 'Order Management', 'we-catering' ) . '</h1><p>' . esc_html__( 'Order management template not found.', 'we-catering' ) . '</p></div>';
        }
    }

    /**
     * Organizations page callback
     */
    public function organizations_page() {
        $template = $this->get_template( 'admin/organizations.php' );
        if ( file_exists( $template ) ) {
            include $template;
        } else {
            echo '<div class="wrap"><h1>' . esc_html__( 'Organizations', 'we-catering' ) . '</h1><p>' . esc_html__( 'Organizations template not found.', 'we-catering' ) . '</p></div>';
        }
    }

    /**
     * Reports page callback
     */
    public function reports_page() {
        $template = $this->get_template( 'admin/reports.php' );
        if ( file_exists( $template ) ) {
            include $template;
        } else {
            echo '<div class="wrap"><h1>' . esc_html__( 'Reports', 'we-catering' ) . '</h1><p>' . esc_html__( 'Reports template not found.', 'we-catering' ) . '</p></div>';
        }
    }

    /**
     * Settings page callback
     */
    public function settings_page() {
        $template = $this->get_template( 'admin/settings.php' );
        if ( file_exists( $template ) ) {
            include $template;
        } else {
            echo '<div class="wrap"><h1>' . esc_html__( 'Settings', 'we-catering' ) . '</h1><p>' . esc_html__( 'Settings template not found.', 'we-catering' ) . '</p></div>';
        }
    }

    /**
     * Get template path
     *
     * @param string $template_name
     * @return string
     */
    private function get_template( $template_name ) {
        return apply_filters( 'we-catering_admin_template', WE_CATERING_TEMPLATE_DIR . '/' . $template_name, $template_name );
    }
}
