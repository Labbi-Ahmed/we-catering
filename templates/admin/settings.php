<?php
/**
 * Settings Template
 *
 * @package WeCatering
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'We Catering Settings', 'we-catering' ); ?></h1>
    
    <div class="we-catering-settings">
        <form method="post" action="options.php">
            <?php settings_fields( 'we_catering_settings' ); ?>
            <?php do_settings_sections( 'we_catering_settings' ); ?>
            
            <!-- Email Settings -->
            <div class="we-catering-settings-section">
                <h2><?php esc_html_e( 'Email Settings', 'we-catering' ); ?></h2>
                <p><?php esc_html_e( 'Email notifications will be configured later.', 'we-catering' ); ?></p>
            </div>
            
            <!-- Advanced Settings -->
            <div class="we-catering-settings-section">
                <h2><?php esc_html_e( 'Advanced Settings', 'we-catering' ); ?></h2>
                <?php do_settings_fields( 'we_catering_settings', 'we_catering_settings_limits' ); ?>
            </div>
            
            <?php submit_button(); ?>
        </form>
    </div>

    <!-- Debug Section -->
    <div class="we-catering-debug-section" style="margin-top: 30px; padding: 20px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 5px;">
        <h3><?php esc_html_e( 'Debug Information', 'we-catering' ); ?></h3>
        <?php
        $settings = get_option( 'we_catering_settings', array() );
        $order_model = new \WeLabs\WeCatering\Models\Order();
        $window_status = $order_model->get_order_window_status();
        ?>
        <p><strong><?php esc_html_e( 'Current Settings:', 'we-catering' ); ?></strong></p>
        <pre><?php echo esc_html( wp_json_encode( $settings, JSON_PRETTY_PRINT ) ); ?></pre>
        
        <p><strong><?php esc_html_e( 'Order Window Status:', 'we-catering' ); ?></strong></p>
        <pre><?php echo esc_html( wp_json_encode( $window_status, JSON_PRETTY_PRINT ) ); ?></pre>
        
        <p><strong><?php esc_html_e( 'Current Server Time:', 'we-catering' ); ?></strong> <?php echo esc_html( current_time( 'Y-m-d H:i:s' ) ); ?></p>
        
        <p><strong><?php esc_html_e( 'Order Window Open:', 'we-catering' ); ?></strong> 
            <span style="color: <?php echo $window_status['is_open'] ? 'green' : 'red'; ?>; font-weight: bold;">
                <?php echo $window_status['is_open'] ? 'YES' : 'NO'; ?>
            </span>
        </p>

        <!-- Test Order Creation -->
        <div style="margin-top: 20px; padding: 15px; background: #fff; border: 1px solid #ccc; border-radius: 4px;">
            <h4><?php esc_html_e( 'Test Order Creation', 'we-catering' ); ?></h4>
            <p><?php esc_html_e( 'Click the button below to test order creation with current settings:', 'we-catering' ); ?></p>
            <button type="button" id="test-order-creation" class="button button-secondary">
                <?php esc_html_e( 'Test Order Creation', 'we-catering' ); ?>
            </button>
            <div id="test-result" style="margin-top: 10px; padding: 10px; display: none;"></div>
        </div>
    </div>
</div>
