<?php
/**
 * Order Management Template
 *
 * @package WeCatering
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Order Management', 'we-catering' ); ?></h1>
    
    <div class="we-catering-order-management">
        <!-- Order Filters -->
        <div class="we-catering-order-filters">
            <form method="get" action="">
                <input type="hidden" name="page" value="we-catering-orders" />
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="order_date"><?php esc_html_e( 'Order Date', 'we-catering' ); ?></label>
                        </th>
                        <td>
                            <input type="date" id="order_date" name="order_date" value="<?php echo esc_attr( current_time( 'Y-m-d' ) ); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="organization"><?php esc_html_e( 'Organization', 'we-catering' ); ?></label>
                        </th>
                        <td>
                            <select id="organization" name="organization" class="regular-text">
                                <option value=""><?php esc_html_e( 'All Organizations', 'we-catering' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="order_status"><?php esc_html_e( 'Order Status', 'we-catering' ); ?></label>
                        </th>
                        <td>
                            <select id="order_status" name="order_status" class="regular-text">
                                <option value=""><?php esc_html_e( 'All Statuses', 'we-catering' ); ?></option>
                                <option value="pending"><?php esc_html_e( 'Pending', 'we-catering' ); ?></option>
                                <option value="confirmed"><?php esc_html_e( 'Confirmed', 'we-catering' ); ?></option>
                                <option value="closed"><?php esc_html_e( 'Closed', 'we-catering' ); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="filter" id="filter" class="button button-secondary" value="<?php esc_attr_e( 'Filter Orders', 'we-catering' ); ?>" />
                </p>
            </form>
        </div>
        
        <!-- Orders Summary -->
        <div class="we-catering-orders-summary">
            <h2><?php esc_html_e( 'Orders Summary', 'we-catering' ); ?></h2>
            <?php
            $order_model = new \WeLabs\WeCatering\Models\Order();
            $organization_model = new \WeLabs\WeCatering\Models\Organization();
            
            // Get filter parameters
            $filter_date = isset( $_GET['order_date'] ) ? sanitize_text_field( $_GET['order_date'] ) : current_time( 'Y-m-d' );
            $filter_org = isset( $_GET['organization'] ) ? intval( $_GET['organization'] ) : 0;
            $filter_status = isset( $_GET['order_status'] ) ? sanitize_text_field( $_GET['order_status'] ) : '';
            
            // Get orders for the filtered date
            $order_args = array( 'order_date' => $filter_date );
            if ( $filter_org ) {
                $order_args['organization_id'] = $filter_org;
            }
            if ( $filter_status ) {
                $order_args['status'] = $filter_status;
            }
            
            $orders = $order_model->get_all( $order_args );
            $total_orders = count( $orders );
            $total_revenue = 0;
            $total_items = 0;
            
            foreach ( $orders as $order ) {
                $total_revenue += $order->total_amount;
                $order_items = $order_model->get_order_items( $order->id );
                $total_items += count( $order_items );
            }
            ?>
            <div class="we-catering-summary-stats">
                <div class="we-catering-stat">
                    <span class="we-catering-stat-number"><?php echo esc_html( $total_orders ); ?></span>
                    <span class="we-catering-stat-label"><?php esc_html_e( 'Total Orders', 'we-catering' ); ?></span>
                </div>
                <div class="we-catering-stat">
                    <span class="we-catering-stat-number">$<?php echo esc_html( number_format( $total_revenue, 2 ) ); ?></span>
                    <span class="we-catering-stat-label"><?php esc_html_e( 'Total Revenue', 'we-catering' ); ?></span>
                </div>
                <div class="we-catering-stat">
                    <span class="we-catering-stat-number"><?php echo esc_html( $total_items ); ?></span>
                    <span class="we-catering-stat-label"><?php esc_html_e( 'Total Items', 'we-catering' ); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Orders List -->
        <div class="we-catering-orders-list">
            <h2><?php esc_html_e( 'Orders', 'we-catering' ); ?></h2>
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
                            <th><?php esc_html_e( 'Order Date', 'we-catering' ); ?></th>
                            <th><?php esc_html_e( 'Actions', 'we-catering' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( ! empty( $orders ) ) : ?>
                            <?php foreach ( $orders as $order ) : ?>
                                <tr>
                                    <td><?php echo esc_html( $order->order_number ); ?></td>
                                    <td><?php echo esc_html( $order->organization_name ); ?></td>
                                    <td><?php echo esc_html( $order->customer_name ); ?></td>
                                    <td>
                                        <?php
                                        $order_items = $order_model->get_order_items( $order->id );
                                        $item_count = count( $order_items );
                                        echo esc_html( $item_count . ' item' . ( $item_count !== 1 ? 's' : '' ) );
                                        ?>
                                    </td>
                                    <td>$<?php echo esc_html( number_format( $order->total_amount, 2 ) ); ?></td>
                                    <td>
                                        <span class="we-catering-status <?php echo esc_attr( $order->status ); ?>">
                                            <?php echo esc_html( ucfirst( $order->status ) ); ?>
                                        </span>
                                    </td>
                                    <td><?php echo esc_html( date( 'M j, Y', strtotime( $order->order_date ) ) ); ?></td>
                                    <td>
                                        <a href="#" class="button button-small view-order" data-order-id="<?php echo esc_attr( $order->id ); ?>">
                                            <?php esc_html_e( 'View', 'we-catering' ); ?>
                                        </a>
                                        <select class="update-status-select" data-order-id="<?php echo esc_attr( $order->id ); ?>" style="margin-left:6px;">
                                            <option value="pending" <?php selected( $order->status, 'pending' ); ?>><?php esc_html_e( 'Pending', 'we-catering' ); ?></option>
                                            <option value="accepted" <?php selected( $order->status, 'confirmed' ); ?>><?php esc_html_e( 'Accepted', 'we-catering' ); ?></option>
                                            <option value="rejected" <?php selected( $order->status, 'cancelled' ); ?>><?php esc_html_e( 'Rejected', 'we-catering' ); ?></option>
                                        </select>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="8"><?php esc_html_e( 'No orders found.', 'we-catering' ); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
