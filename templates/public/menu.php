<?php
/**
 * Public Menu Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get order window status
$order_model = new \WeLabs\WeCatering\Models\Order();
$window_status = $order_model->get_order_window_status();
$is_order_window_open = $window_status['is_open'];
?>

<div class="we-catering-public">
	<div class="we-catering-menu-header">
		<h2><?php echo esc_html( $organization_name ? $organization_name . ' – ' : '' ); ?><?php echo esc_html( date_i18n( 'M j, Y', strtotime( $date ) ) ); ?></h2>
		<div class="we-catering-view-toggle">
			<button type="button" class="button button-secondary wec-toggle" data-view="grid"><?php esc_html_e( 'Grid', 'we-catering' ); ?></button>
			<button type="button" class="button button-secondary wec-toggle" data-view="list"><?php esc_html_e( 'List', 'we-catering' ); ?></button>
		</div>
		
		<div class="we-catering-order-window-status <?php echo $is_order_window_open ? 'open' : 'closed'; ?>">
			<?php if ( $is_order_window_open ) : ?>
				<div class="we-catering-window-open">
					<span class="we-catering-status-icon">✓</span>
					<span class="we-catering-status-text"><?php echo esc_html( $window_status['message'] ); ?></span>
				</div>
			<?php else : ?>
				<div class="we-catering-window-closed">
					<span class="we-catering-status-icon">✗</span>
					<span class="we-catering-status-text"><?php echo esc_html( $window_status['message'] ); ?></span>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<form id="we-catering-order-form" class="we-catering-form <?php echo $is_order_window_open ? '' : 'disabled'; ?>" method="post">
		<input type="hidden" name="date" value="<?php echo esc_attr( $date ); ?>" />
		<input type="hidden" name="organization_id" value="<?php echo esc_attr( $organization_id ); ?>" />

		<div class="we-catering-menu-grid" id="we-catering-menu-grid">
			<?php if ( ! empty( $items ) ) : ?>
				<?php foreach ( $items as $item ) : ?>
					<div class="we-catering-menu-card <?php echo $is_order_window_open ? '' : 'disabled'; ?>">
						<div class="we-catering-menu-title">
							<strong><?php echo esc_html( $item->name ); ?></strong>
							<span class="we-catering-price">$<?php echo esc_html( number_format( (float) $item->price, 2 ) ); ?></span>
						</div>
						<?php if ( ! empty( $item->description ) ) : ?>
							<p class="we-catering-desc"><?php echo esc_html( $item->description ); ?></p>
						<?php endif; ?>
						<div class="we-catering-qty">
							<label for="qty-<?php echo esc_attr( $item->id ); ?>"><?php esc_html_e( 'Qty', 'we-catering' ); ?></label>
							<button type="button" class="button wec-qty-dec" <?php echo $is_order_window_open ? '' : 'disabled'; ?>>−</button>
							<input type="number" 
								id="qty-<?php echo esc_attr( $item->id ); ?>" 
								name="items[<?php echo esc_attr( $item->id ); ?>]" 
								min="0" 
								step="1" 
								value="0" 
								<?php echo $is_order_window_open ? '' : 'disabled'; ?>
								class="<?php echo $is_order_window_open ? '' : 'disabled'; ?>"
							/>
							<button type="button" class="button wec-qty-inc" <?php echo $is_order_window_open ? '' : 'disabled'; ?>>+</button>
						</div>
					</div>
				<?php endforeach; ?>
			<?php else : ?>
				<p><?php esc_html_e( 'No items available for this date.', 'we-catering' ); ?></p>
			<?php endif; ?>
		</div>

		<div class="we-catering-order-summary">
			<div class="we-catering-total">
				<span><?php esc_html_e( 'Total:', 'we-catering' ); ?></span>
				<strong id="we-catering-total">$0.00</strong>
			</div>
			<div class="we-catering-actions">
				<?php if ( $is_order_window_open ) : ?>
					<button type="submit" class="button button-primary"><?php esc_html_e( 'Place Order', 'we-catering' ); ?></button>
				<?php else : ?>
					<button type="button" class="button button-secondary" disabled><?php esc_html_e( 'Order Window Closed', 'we-catering' ); ?></button>
				<?php endif; ?>
			</div>
		</div>
	</form>
</div>
