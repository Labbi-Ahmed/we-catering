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
}
