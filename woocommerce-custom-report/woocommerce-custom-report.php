<?php

/**
 * Plugin Name: WooCommerce Firetech Report
 * Description: WooCommerce Firetech Report.
 */
if (!defined('ABSPATH')) {
    exit;
}

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) { /* Check if woocommerce is Activated or not   */

    class WC_Custom_Reports_Admin {

        /**
         * Constructor.
         */
        public function __construct() {
            add_action('admin_menu', array($this, 'add_wc_submenu_menu'), 25);
            add_action('admin_enqueue_scripts', array($this, 'styles_and_scripts'));
        }

        /**
         * Scripts.
         */
        public function styles_and_scripts() {
            wp_enqueue_style('wc-custom-reports-admin-css', plugin_dir_url(__FILE__) . 'assets/css/style.css');
        }

        public function add_wc_submenu_menu() {
            $menu_icon_url = 'dashicons-forms';
            add_submenu_page('woocommerce', __('Custom Reports', 'woocommerce'), __('Firetech Reports', 'woocommerce'), 'view_woocommerce_reports', 'wc-custom-reports', array($this, 'custom_reports_page'));
        }

        /**
         * Init the reports page.
         */
        function custom_reports_page() {
            include_once( dirname(__FILE__) . '/includes/admin/class-wc-admin-custom-reports.php' );
            if (class_exists('WC_Admin_Custom_Reports')) {
                WC_Admin_Custom_Reports::output();
            }
        }

    }

    new WC_Custom_Reports_Admin();

    if (!defined('WC_CUSTOM_REPORT_PLUGIN_FILE')) {
        define('WC_CUSTOM_REPORT_PLUGIN_FILE', plugin_dir_path(__FILE__));
    }

    /* Ajax function to Export CSV */
    add_action('wp_ajax_nopriv_exportcsv', 'export_to_csv');
    add_action('wp_ajax_exportcsv', 'export_to_csv');

    function export_to_csv() {
        $currency_symbol = get_woocommerce_currency_symbol();
        $salesDataObj = (json_decode(stripslashes($_POST['salesdata']), true));
        if (!$salesDataObj) {

            die("There was an error");
        } else {
            $csv_fields = array();
            $leadArray = array();
            $i = 0;
            $monthArray = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC', 'Total'];
            foreach ($salesDataObj as $salesDataOjb) {
                $j = 0;
                foreach ($salesDataOjb as $salesKey => $salesDataOjbs) {
                    if ($i == 0) {
                        $initalYearSting = $key;
                    }
                    $key = $salesKey;
                    foreach ($salesDataOjbs as $salesKeyinner => $salesDataOjbssa) {
                        if ($j == 0) {
                            $leadArray[$i]['month-' . $key] = $monthArray[$i];
                        }
                        $leadArray[$i][$salesKeyinner . '-' . $key] = $salesDataOjbssa;
                    }
                    $j++;
                }
                $i++;
            }

            $initialYear = (int) $initalYearSting;
            $finalYear = $initialYear - 1;
            $csv_fields_change = array(
                'Month',
                'Sales (Inc Tax) ' . $initialYear,
                'Sales (Ex Tax) ' . $initialYear,
                'Orders ' . $initialYear,
                'Products ' . $initialYear,
                'Sales (Inc Tax) ' . $finalYear,
                'Sales (Ex Tax) ' . $finalYear,
                'Orders ' . $finalYear,
                'Products ' . $finalYear
            );

            $strtotimeforname = strtotime(date('YmdHis'));

            $output_filename_paths = WC_CUSTOM_REPORT_PLUGIN_FILE . '/export/Firetech-Report-'.$initialYear.'-'. $finalYear.'-'. $strtotimeforname . '.csv';
            $output_filename_URI = plugin_dir_url(__FILE__) . '/export/Firetech-Report-'.$initialYear.'-'. $finalYear.'-'. $strtotimeforname . '.csv';
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=data.csv');
            $output_handle = @fopen($output_filename_paths, 'w');
            // Insert header row
            fputcsv($output_handle, $csv_fields_change);
            foreach ($leadArray as $leadArrays) {
                fputcsv($output_handle, $leadArrays);
            }
            // Close output file stream
            fclose($output_handle);

            echo json_encode(array('url' => $output_filename_URI));
            die();
        }
    }

} else {
    echo 'Woocommerce is not Active';
}