<?php

namespace WeLabs\WeCatering;

/**
 * WeCatering class
 *
 * @class WeCatering The class that holds the entire WeCatering plugin
 */
final class WeCatering {

    /**
     * Plugin version
     *
     * @var string
     */
    public $version = '0.0.1';

    /**
     * Instance of self
     *
     * @var WeCatering
     */
    private static $instance = null;

    /**
     * Holds various class instances
     *
     * @since 2.6.10
     *
     * @var array
     */
    private $container = [];

    /**
     * Constructor for the WeCatering class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     */
    private function __construct() {
        $this->define_constants();

        register_activation_hook( WE_CATERING_FILE, [ $this, 'activate' ] );
        register_deactivation_hook( WE_CATERING_FILE, [ $this, 'deactivate' ] );

        add_action( 'plugins_loaded', [ $this, 'init_plugin' ] );
        add_action( 'woocommerce_flush_rewrite_rules', [ $this, 'flush_rewrite_rules' ] );
    }

    /**
     * Initializes the WeCatering() class
     *
     * Checks for an existing WeCatering instance
     * and if it doesn't find one then create a new one.
     *
     * @return WeCatering
     */
    public static function init() {
        if ( self::$instance === null ) {
			self::$instance = new self();
		}

        return self::$instance;
    }

    /**
     * Magic getter to bypass referencing objects
     *
     * @since 2.6.10
     *
     * @param string $prop
     *
     * @return Class Instance
     */
    public function __get( $prop ) {
		if ( array_key_exists( $prop, $this->container ) ) {
            return $this->container[ $prop ];
		}
    }

    /**
     * Plugin activation function
     */
    public function activate() {
        // Create database tables
        $database = new Database();
        $database->create_tables();
        
        // Update database version
        update_option( 'we_catering_db_version', $database->get_db_version() );
        
        // Rewrite rules during we_catering activation
        if ( $this->has_woocommerce() ) {
            $this->flush_rewrite_rules();
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Flush rewrite rules after we_catering is activated or woocommerce is activated
     *
     * @since 3.2.8
     */
    public function flush_rewrite_rules() {
        // fix rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Placeholder for deactivation function
     *
     * Nothing being called here yet.
     */
    public function deactivate() {     }

    /**
     * Define all constants
     *
     * @return void
     */
    public function define_constants() {
        defined( 'WE_CATERING_PLUGIN_VERSION' ) || define( 'WE_CATERING_PLUGIN_VERSION', $this->version );
        defined( 'WE_CATERING_DIR' ) || define( 'WE_CATERING_DIR', dirname( WE_CATERING_FILE ) );
        defined( 'WE_CATERING_INC_DIR' ) || define( 'WE_CATERING_INC_DIR', WE_CATERING_DIR . '/includes' );
        defined( 'WE_CATERING_TEMPLATE_DIR' ) || define( 'WE_CATERING_TEMPLATE_DIR', WE_CATERING_DIR . '/templates' );
        defined( 'WE_CATERING_PLUGIN_ASSET' ) || define( 'WE_CATERING_PLUGIN_ASSET', plugins_url( 'assets', WE_CATERING_FILE ) );
        defined( 'WE_CATERING_PLUGIN_ADMIN_ASSET' ) || define( 'WE_CATERING_PLUGIN_ADMIN_ASSET', WE_CATERING_PLUGIN_ASSET . '/admin' );
        defined( 'WE_CATERING_PLUGIN_PUBLIC_ASSET' ) || define( 'WE_CATERING_PLUGIN_PUBLIC_ASSET', WE_CATERING_PLUGIN_ASSET . '/public' );

        // give a way to turn off loading styles and scripts from parent theme
        defined( 'WE_CATERING_LOAD_STYLE' ) || define( 'WE_CATERING_LOAD_STYLE', true );
        defined( 'WE_CATERING_LOAD_SCRIPTS' ) || define( 'WE_CATERING_LOAD_SCRIPTS', true );
    }

    /**
     * Load the plugin after WP User Frontend is loaded
     *
     * @return void
     */
    public function init_plugin() {
        $this->includes();
        $this->init_hooks();

        do_action( 'we_catering_loaded' );
    }

    /**
     * Initialize the actions
     *
     * @return void
     */
    public function init_hooks() {
        // initialize the classes
        add_action( 'init', [ $this, 'init_classes' ], 4 );
        add_action( 'plugins_loaded', [ $this, 'after_plugins_loaded' ] );
    }

    /**
     * Include all the required files
     *
     * @return void
     */
    public function includes() {
        // include_once STUB_PLUGIN_DIR . '/functions.php';
    }

    /**
     * Init all the classes
     *
     * @return void
     */
    public function init_classes() {
        $this->container['scripts']  = new Assets();
        $this->container['admin']    = new Admin();
        $this->container['database'] = new Database();
        $this->container['ajax']     = new Ajax();
        $this->container['shortcodes'] = new Shortcodes();
        $this->container['settings'] = new Settings();
        $this->container['reports']  = new Reports();
    }

    /**
     * Executed after all plugins are loaded
     *
     * At this point we_catering Pro is loaded
     *
     * @since 2.8.7
     *
     * @return void
     */
    public function after_plugins_loaded() {
        // Initiate background processes and other tasks
    }

    /**
     * Check whether woocommerce is installed and active
     *
     * @since 2.9.16
     *
     * @return bool
     */
    public function has_woocommerce() {
        return class_exists( 'WooCommerce' );
    }

    /**
     * Check whether woocommerce is installed
     *
     * @since 3.2.8
     *
     * @return bool
     */
    public function is_woocommerce_installed() {
        return in_array( 'woocommerce/woocommerce.php', array_keys( get_plugins() ), true );
    }

    /**
	 * Get the plugin url.
	 *
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', WE_CATERING_FILE ) );
	}

    /**
     * Get the template file path to require or include.
     *
     * @param string $name
     * @return string
     */
    public function get_template( $name ) {
        $template = untrailingslashit( WE_CATERING_TEMPLATE_DIR ) . '/' . untrailingslashit( $name );

        return apply_filters( 'we-catering_template', $template, $name );
    }
}
