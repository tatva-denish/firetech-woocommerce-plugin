<?php

/**
 * Admin Reports
 *
 * Functions used for displaying sales and customer reports in admin.
 *
 * @author      WooThemes
 * @category    Admin
 * @package     WooCommerce/Admin/Reports
 * @version     2.0.0
 */
if (!defined('ABSPATH')) {
    exit;
}
//echo 'siddhi';exit;
if (class_exists('WC_Admin_Custom_Reports', false)) {
    return;
}

/**
 * WC_Admin_Custom_Reports Class.
 */
class WC_Admin_Custom_Reports {

    
        
    /**
     * Handles output of the reports page in admin.
     */
    public static function output() {
         global $woocommerce;
        $reports = self::get_reports();
        $first_tab = array_keys($reports);
        $current_tab = !empty($_GET['tab']) ? sanitize_title($_GET['tab']) : $first_tab[0];
        $current_report = isset($_GET['report']) ? sanitize_title($_GET['report']) : current(array_keys($reports[$current_tab]['reports']));
        include_once( $woocommerce->plugin_path() . '/includes/admin/reports/class-wc-admin-report.php' );
        include_once( dirname(__FILE__) . '/views/html-admin-page-custom-reports.php' );
    }

    /**
     * Returns the definitions for the reports to show in admin.
     *
     * @return array
     */
    public static function get_reports() {
        $reports = array(
            'sales' => array(
                'title' => __('Sales', 'woocommerce'),
                'reports' => array(
                    'sales_by_date' => array(
                        'title' => __('Sales by date', 'woocommerce'),
                        'description' => '',
                        'hide_title' => true,
                        'callback' => array(__CLASS__, 'get_report'),
                    )
                ),
            ),
            'inventory' => array(
                'title' => __('Inventory', 'woocommerce'),
                'reports' => array(
                    'inventory_custom' => array(
                        'title' => __('Inventory Custom', 'woocommerce'),
                        'description' => '',
                        'hide_title' => true,
                        'callback' => array(__CLASS__, 'get_report'),
                    )
                ),
            ),
        );

        $reports = apply_filters('woocommerce_admin_reports', $reports);
        $reports = apply_filters('woocommerce_reports_charts', $reports); // Backwards compatibility.

        foreach ($reports as $key => $report_group) {
            if (isset($reports[$key]['charts'])) {
                $reports[$key]['reports'] = $reports[$key]['charts'];
            }

            foreach ($reports[$key]['reports'] as $report_key => $report) {
                if (isset($reports[$key]['reports'][$report_key]['function'])) {
                    $reports[$key]['reports'][$report_key]['callback'] = $reports[$key]['reports'][$report_key]['function'];
                }
            }
        }

        return $reports;
    }

    /**
     * Get a report from our reports subfolder.
     *
     * @param string $name
     */
    public static function get_report($name) {
        global $woocommerce;
        $name = sanitize_title(str_replace('_', '-', $name));
        $class = 'WC_Report_Custom_' . str_replace('-', '_', $name);
        include_once( WC_CUSTOM_REPORT_PLUGIN_FILE.'includes/admin/reports/class-wc-report-custom-' . $name . '.php' );
        
        if (!class_exists($class)) {
            return;
        }

        $report = new $class();
        $report->output_report();
    }

}


