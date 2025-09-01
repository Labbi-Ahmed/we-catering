<?php
/**
 * Reports Template
 *
 * @package WeCatering
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Reports', 'we-catering' ); ?></h1>
    
    <div class="we-catering-reports">
        <!-- Report Filters -->
        <div class="we-catering-report-filters">
            <form method="get" action="">
                <input type="hidden" name="page" value="we-catering-reports" />
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="report_type"><?php esc_html_e( 'Report Type', 'we-catering' ); ?></label>
                        </th>
                        <td>
                            <select id="report_type" name="report_type" class="regular-text">
                                <option value="daily"><?php esc_html_e( 'Daily Summary', 'we-catering' ); ?></option>
                                <option value="organization"><?php esc_html_e( 'Organization Summary', 'we-catering' ); ?></option>
                                <option value="menu"><?php esc_html_e( 'Menu Summary', 'we-catering' ); ?></option>
                                <option value="user"><?php esc_html_e( 'User Summary', 'we-catering' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="date_from"><?php esc_html_e( 'Date From', 'we-catering' ); ?></label>
                        </th>
                        <td>
                            <input type="date" id="date_from" name="date_from" value="<?php echo esc_attr( current_time( 'Y-m-d', true ) ); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="date_to"><?php esc_html_e( 'Date To', 'we-catering' ); ?></label>
                        </th>
                        <td>
                            <input type="date" id="date_to" name="date_to" value="<?php echo esc_attr( current_time( 'Y-m-d', true ) ); ?>" class="regular-text" />
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="generate_report" id="generate_report" class="button button-primary" value="<?php esc_attr_e( 'Generate Report', 'we-catering' ); ?>" />
                    <a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=we_catering_export_csv&report_type=' . ( isset( $_GET['report_type'] ) ? sanitize_text_field( wp_unslash( $_GET['report_type'] ) ) : 'daily' ) . '&date_from=' . ( isset( $_GET['date_from'] ) ? sanitize_text_field( wp_unslash( $_GET['date_from'] ) ) : current_time( 'Y-m-d', true ) ) . '&date_to=' . ( isset( $_GET['date_to'] ) ? sanitize_text_field( wp_unslash( $_GET['date_to'] ) ) : current_time( 'Y-m-d', true ) ) ), 'we_catering_export_csv' ) ); ?>"><?php esc_html_e( 'Export CSV', 'we-catering' ); ?></a>
                </p>
            </form>
        </div>
        
        <!-- Report Content -->
        <div class="we-catering-report-content">
            <h2><?php esc_html_e( 'Report Results', 'we-catering' ); ?></h2>
            <div class="we-catering-report-summary">
                <div class="we-catering-report-stat">
                    <span class="we-catering-report-stat-number">0</span>
                    <span class="we-catering-report-stat-label"><?php esc_html_e( 'Total Orders', 'we-catering' ); ?></span>
                </div>
                <div class="we-catering-report-stat">
                    <span class="we-catering-report-stat-number">$0.00</span>
                    <span class="we-catering-report-stat-label"><?php esc_html_e( 'Total Revenue', 'we-catering' ); ?></span>
                </div>
                <div class="we-catering-report-stat">
                    <span class="we-catering-report-stat-number">0</span>
                    <span class="we-catering-report-stat-label"><?php esc_html_e( 'Total Items', 'we-catering' ); ?></span>
                </div>
                <div class="we-catering-report-stat">
                    <span class="we-catering-report-stat-number">0</span>
                    <span class="we-catering-report-stat-label"><?php esc_html_e( 'Active Organizations', 'we-catering' ); ?></span>
                </div>
            </div>
            
            <div class="we-catering-report-table">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'No data available', 'we-catering' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php esc_html_e( 'Please select a report type and date range to generate a report.', 'we-catering' ); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
