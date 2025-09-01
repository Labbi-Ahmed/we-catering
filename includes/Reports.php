<?php

namespace WeLabs\WeCatering;

use WeLabs\WeCatering\Models\Order;

class Reports {
	public function __construct() {
		add_action( 'admin_post_we_catering_export_csv', array( $this, 'export_csv' ) );
	}

	/**
	 * Generate CSV export based on query args
	 */
	public function export_csv() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}

		check_admin_referer( 'we_catering_export_csv' );

		$report_type = isset( $_GET['report_type'] ) ? sanitize_text_field( wp_unslash( $_GET['report_type'] ) ) : 'daily';
		$date_from  = isset( $_GET['date_from'] ) ? sanitize_text_field( wp_unslash( $_GET['date_from'] ) ) : current_time( 'Y-m-d' );
		$date_to    = isset( $_GET['date_to'] ) ? sanitize_text_field( wp_unslash( $_GET['date_to'] ) ) : current_time( 'Y-m-d' );

		$order_model = new Order();

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=we-catering-' . $report_type . '-' . $date_from . '-to-' . $date_to . '.csv' );

		$fh = fopen( 'php://output', 'w' );
		// BOM for Excel
		fprintf( $fh, chr(0xEF) . chr(0xBB) . chr(0xBF) );

		if ( $report_type === 'daily' ) {
			fputcsv( $fh, array( 'Date', 'Total Orders', 'Total Revenue', 'Unique Customers', 'Unique Organizations' ) );

			$cur = strtotime( $date_from );
			$end = strtotime( $date_to );
			while ( $cur <= $end ) {
				$day = gmdate( 'Y-m-d', $cur );
				$summary = $order_model->get_daily_summary( $day );
				fputcsv( $fh, array( $day, $summary ? (int) $summary->total_orders : 0, $summary ? (float) $summary->total_revenue : 0, $summary ? (int) $summary->unique_customers : 0, $summary ? (int) $summary->unique_organizations : 0 ) );
				$cur = strtotime( '+1 day', $cur );
			}
		} elseif ( $report_type === 'organization' ) {
			fputcsv( $fh, array( 'Date', 'Organization', 'Total Orders', 'Total Revenue', 'Unique Customers' ) );
			$cur = strtotime( $date_from );
			$end = strtotime( $date_to );
			while ( $cur <= $end ) {
				$day = gmdate( 'Y-m-d', $cur );
				$rows = $order_model->get_organization_summary( $day );
				if ( ! empty( $rows ) ) {
					foreach ( $rows as $r ) {
						fputcsv( $fh, array( $day, (string) $r->organization_name, (int) $r->total_orders, (float) $r->total_revenue, (int) $r->unique_customers ) );
					}
				} else {
					fputcsv( $fh, array( $day, '-', 0, 0, 0 ) );
				}
				$cur = strtotime( '+1 day', $cur );
			}
		} else {
			// Fallback: export all orders list
			fputcsv( $fh, array( 'Order #', 'Date', 'Organization', 'Customer', 'Total', 'Status' ) );
			$orders = $order_model->get_all( array( 'order' => 'DESC' ) );
			if ( ! empty( $orders ) ) {
				foreach ( $orders as $o ) {
					fputcsv( $fh, array( $o->order_number, $o->order_date, (string) $o->organization_name, (string) $o->customer_name, (float) $o->total_amount, (string) $o->status ) );
				}
			}
		}

		fclose( $fh );
		exit;
	}
}
