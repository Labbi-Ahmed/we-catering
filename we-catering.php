<?php
/**
 * Plugin Name: We Catering
 * Plugin URI:  https://welabs.dev
 * Description: This is for manage catering system. to make smooth and time consuming system for both client and vendor
 * Version: 0.0.1
 * Author: Labbi Ahmed
 * Author URI: https://welabs.dev
 * Text Domain: we-catering
 * WC requires at least: 5.0.0
 * Domain Path: /languages/
 * Requires Plugins: 
 * License: GPL2
 */
use WeLabs\WeCatering\WeCatering;

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'WE_CATERING_FILE' ) ) {
    define( 'WE_CATERING_FILE', __FILE__ );
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Load We_Catering Plugin when all plugins loaded
 *
 * @return \WeLabs\WeCatering\WeCatering
 */
function welabs_we_catering() {
    return WeCatering::init();
}

// Lets Go....
welabs_we_catering();
