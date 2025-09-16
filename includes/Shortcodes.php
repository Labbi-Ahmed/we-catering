<?php

namespace WeLabs\WeCatering;

use WeLabs\WeCatering\Models\DailyMenu;
use WeLabs\WeCatering\Models\Organization;

class Shortcodes {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_shortcodes' ) );
	}

	/**
	 * Register shortcodes
	 */
	public function register_shortcodes() {
		add_shortcode( 'we_catering_menu', array( $this, 'render_menu_shortcode' ) );
		add_shortcode( 'we_catering_my_orders', array( $this, 'render_my_orders_shortcode' ) );
	}

	/**
	 * Render the menu shortcode
	 *
	 * @param array $atts
	 * @return string
	 */
	public function render_menu_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'date'          => current_time( 'Y-m-d' ),
				'organization'  => 0,
			),
			$atts,
			'we_catering_menu'
		);

		// Enqueue public assets
		if ( defined( 'WE_CATERING_LOAD_STYLE' ) && WE_CATERING_LOAD_STYLE ) {
			wp_enqueue_style( 'we_catering_style' );
		}
		if ( defined( 'WE_CATERING_LOAD_SCRIPTS' ) && WE_CATERING_LOAD_SCRIPTS ) {
			wp_enqueue_script( 'we_catering_script' );
		}

		// Prepare data
		$date             = sanitize_text_field( $atts['date'] );
		$organization_id  = isset( $atts['organization'] ) ? absint( $atts['organization'] ) : 0;

		$daily_menu = new DailyMenu();
		$items      = $daily_menu->get_daily_menu( $date );
		if ( empty( $items ) ) {
			// Fallback: show all active items if no daily menu configured
			$menu_model = new \WeLabs\WeCatering\Models\MenuItem();
			$items      = $menu_model->get_all( array( 'status' => 'active', 'orderby' => 'name' ) );
		}

		// Organization context (optional)
		$organization_name = '';
		if ( $organization_id ) {
			$org_model = new Organization();
			$org       = $org_model->get_by_id( $organization_id );
			$organization_name = $org ? $org->name : '';
		}

		ob_start();
		$template = WE_CATERING_TEMPLATE_DIR . '/public/menu.php';
		$items    = apply_filters( 'we_catering_public_menu_items', $items, $date, $organization_id );
		$context  = array(
			'date'              => $date,
			'organization_id'   => $organization_id,
			'organization_name' => $organization_name,
			'items'             => $items,
		);
		// Make context variables available to template in a scoped way
		extract( $context, EXTR_SKIP );
		if ( file_exists( $template ) ) {
			include $template;
		}

		return ob_get_clean();
	}

	/**
	 * Render the current user's orders (today)
	 *
	 * @param array $atts
	 * @return string
	 */
	public function render_my_orders_shortcode( $atts ) {
		if ( ! is_user_logged_in() ) {
			return '<p>' . esc_html__( 'Please log in to view your orders.', 'we-catering' ) . '</p>';
		}

		$atts = shortcode_atts(
			array(
				'date' => current_time( 'Y-m-d' ),
			),
			$atts,
			'we_catering_my_orders'
		);

		$user_id = get_current_user_id();
		$date = sanitize_text_field( $atts['date'] );

		$order_model = new \WeLabs\WeCatering\Models\Order();
		$orders = $order_model->get_all( array( 'user_id' => $user_id, 'order_date' => $date, 'orderby' => 'created_at', 'order' => 'DESC' ) );

		ob_start();
		echo '<div class="we-catering-public">';
		echo '<h3>' . esc_html__( 'My Orders', 'we-catering' ) . ' — ' . esc_html( date_i18n( 'M j, Y', strtotime( $date ) ) ) . '</h3>';
		if ( empty( $orders ) ) {
			echo '<p>' . esc_html__( 'You have not placed any orders today.', 'we-catering' ) . '</p>';
		} else {
			echo '<div class="we-catering-orders-list">';
			foreach ( $orders as $order ) {
				$items = $order_model->get_order_items( $order->id );
				echo '<div class="we-catering-order-box" style="border:1px solid #ddd;border-radius:8px;padding:12px;margin:10px 0;">';
				echo '<div style="display:flex;justify-content:space-between;align-items:center;">';
				echo '<strong>' . esc_html__( 'Order #', 'we-catering' ) . esc_html( $order->order_number ) . '</strong>';
				echo '<span>' . esc_html( sprintf( __( 'Total: $%s', 'we-catering' ), number_format( (float) $order->total_amount, 2 ) ) ) . '</span>';
				echo '</div>';
				if ( ! empty( $items ) ) {
					echo '<ul style="margin:8px 0 0 16px;">';
					foreach ( $items as $it ) {
						$line = sprintf( __( '%1$s — Qty: %2$d — $%3$.2f', 'we-catering' ), $it->menu_item_name, (int) $it->quantity, (float) $it->total_price );
						echo '<li>' . esc_html( $line ) . '</li>';
					}
					echo '</ul>';
				}
				echo '</div>';
			}
			echo '</div>';
		}
		echo '</div>';
		return ob_get_clean();
	}
}
