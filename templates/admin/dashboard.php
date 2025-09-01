<?php
/**
 * Admin Dashboard Template
 *
 * @package WeCatering
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'We Catering Dashboard', 'we-catering' ); ?></h1>
    
    <div class="we-catering-dashboard">
        <!-- Today's Summary -->
        <div class="we-catering-summary-cards">
            <?php
            // Get statistics
            $menu_item = new \WeLabs\WeCatering\Models\MenuItem();
            $organization = new \WeLabs\WeCatering\Models\Organization();
            $order_model = new \WeLabs\WeCatering\Models\Order();

            $total_menu_items = $menu_item->count( array( 'status' => 'active' ) );
            $total_organizations = $organization->count( array( 'status' => 'active' ) );

            // Get today's order statistics
            $today = current_time( 'Y-m-d' );
            $daily_summary = $order_model->get_daily_summary( $today );
            $total_orders = $daily_summary ? $daily_summary->total_orders : 0;
            $total_revenue = $daily_summary ? $daily_summary->total_revenue : 0;
            ?>
            
            <div class="we-catering-card">
                <h3><?php esc_html_e( 'Today\'s Orders', 'we-catering' ); ?></h3>
                <div class="we-catering-card-content">
                    <span class="we-catering-number"><?php echo esc_html( $total_orders ); ?></span>
                    <span class="we-catering-label"><?php esc_html_e( 'Total Orders', 'we-catering' ); ?></span>
                </div>
            </div>
            
            <div class="we-catering-card">
                <h3><?php esc_html_e( 'Today\'s Revenue', 'we-catering' ); ?></h3>
                <div class="we-catering-card-content">
                    <span class="we-catering-number">$<?php echo esc_html( number_format( $total_revenue, 2 ) ); ?></span>
                    <span class="we-catering-label"><?php esc_html_e( 'Total Revenue', 'we-catering' ); ?></span>
                </div>
            </div>
            
            <div class="we-catering-card">
                <h3><?php esc_html_e( 'Active Organizations', 'we-catering' ); ?></h3>
                <div class="we-catering-card-content">
                    <span class="we-catering-number"><?php echo esc_html( $total_organizations ); ?></span>
                    <span class="we-catering-label"><?php esc_html_e( 'Organizations', 'we-catering' ); ?></span>
                </div>
            </div>
            
            <div class="we-catering-card">
                <h3><?php esc_html_e( 'Menu Items', 'we-catering' ); ?></h3>
                <div class="we-catering-card-content">
                    <span class="we-catering-number"><?php echo esc_html( $total_menu_items ); ?></span>
                    <span class="we-catering-label"><?php esc_html_e( 'Available Items', 'we-catering' ); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="we-catering-quick-actions">
            <h2><?php esc_html_e( 'Quick Actions', 'we-catering' ); ?></h2>
            <div class="we-catering-action-buttons">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=we-catering-menu' ) ); ?>" class="button button-primary">
                    <?php esc_html_e( 'Manage Menu', 'we-catering' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=we-catering-orders' ) ); ?>" class="button button-secondary">
                    <?php esc_html_e( 'View Orders', 'we-catering' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=we-catering-organizations' ) ); ?>" class="button button-secondary">
                    <?php esc_html_e( 'Manage Organizations', 'we-catering' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=we-catering-reports' ) ); ?>" class="button button-secondary">
                    <?php esc_html_e( 'View Reports', 'we-catering' ); ?>
                </a>
            </div>
        </div>
        
        <!-- Recent Orders -->
        <div class="we-catering-recent-orders">
            <h2><?php esc_html_e( 'Recent Orders', 'we-catering' ); ?></h2>
            <div class="we-catering-table-container">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Order ID', 'we-catering' ); ?></th>
                            <th><?php esc_html_e( 'Organization', 'we-catering' ); ?></th>
                            <th><?php esc_html_e( 'Customer', 'we-catering' ); ?></th>
                            <th><?php esc_html_e( 'Items', 'we-catering' ); ?></th>
                            <th><?php esc_html_e( 'Total', 'we-catering' ); ?></th>
                            <th><?php esc_html_e( 'Status', 'we-catering' ); ?></th>
                            <th><?php esc_html_e( 'Date', 'we-catering' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="7"><?php esc_html_e( 'No orders found.', 'we-catering' ); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
