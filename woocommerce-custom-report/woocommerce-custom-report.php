<?php

/**
 * Plugin Name: Firetech WooCommerce Custom Report
 * Description: This Custom Woocommerce Add on provides Custom Report
 * for comparing Sales of selected Year and Previous Year. It
 * provides comparison for both year-on-year and month-to-month basis.
 */
if (!defined('ABSPATH')) {
    exit;
}

class WC_Custom_Reports_Admin {

    /**
     * Constructor : Here Action is called to add Sub-menu to the admin panel's menu Woocommerce.
     * And another action is called to load custom CSS documents to Firetech Reports page. 
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_wc_submenu_menu'), 25);
        /* Enqueue Scripts only for our custom menu page */
        if(isset($_REQUEST['page']) && $_REQUEST['page'] == 'wc-custom-reports'){
        add_action('admin_enqueue_scripts', array($this, 'styles_and_scripts'));
        }
    }

    /**
     * Call Back Function for Enqueuing custom style.
     * 
     */
    public function styles_and_scripts() {
        wp_enqueue_style('wc-custom-reports-admin-css', plugin_dir_url(__FILE__) . 'assets/css/style.css');
        wp_enqueue_style('wc-custom-reports-admin-bootstrap-css', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css');
        wp_enqueue_style('wc-custom-reports-admin-datatable-bootstrap-css', 'https://cdn.datatables.net/1.10.16/css/dataTables.bootstrap.min.css');
        wp_enqueue_script('wc-custom-jquery-datatable-js', plugin_dir_url(__FILE__) . 'assets/js/jquery.dataTables.min.js', array('jquery'));
        wp_enqueue_script('wc-custom-jquery-datatable-bbotstrap-js', plugin_dir_url(__FILE__) . 'assets/js/dataTables.bootstrap.min.js', array('jquery'));
    }

    /**
     * Call Back Function to add Sub-menu to the admin panel's menu Woocommerce.
     * 
     */
    public function add_wc_submenu_menu() {
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

/* Check if woocommerce is Activated or not   */
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    new WC_Custom_Reports_Admin();
} else {
    echo 'Woocommerce is not Active';
}

/**
 * Define a constant WC_CUSTOM_REPORT_PLUGIN_FILE for path of Firetech WooCommerce Custom Report plugin.
 * 
 */
if (!defined('WC_CUSTOM_REPORT_PLUGIN_FILE')) {
    define('WC_CUSTOM_REPORT_PLUGIN_FILE', plugin_dir_path(__FILE__));
}

/**
 * Hook to create custom handlers for export_inventory_csv AJAX request 
 * 
 */
add_action('wp_ajax_nopriv_exportcsv', 'export_to_csv');
add_action('wp_ajax_exportcsv', 'export_to_csv');

/**
 * Ajax Function to Export CSV 
 * 
 */
function export_to_csv() {
    $currency_symbol = get_woocommerce_currency_symbol();
    /*  Get Json object of Sales Data */
    $sales_data_obj = (json_decode(stripslashes($_POST['salesdata']), true));

    /*  When JSON Object is not there   */
    if (!$sales_data_obj) {
        die("There was an error");
    } else {
        $arr_csv_columns = array();
        $arr_csv_values = array();
        $i = 0;
        $arr_month_and_total = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC', 'Total'];

        /*  Access Sales Data JSON Object   */
        foreach ($sales_data_obj as $sales_data_ojbs) {
            $j = 0;
            foreach ($sales_data_ojbs as $sales_key_year => $sales_data_ojb) {
                if ($i == 0) {
                    $inital_year_sting = $key;
                }
                $key = $sales_key_year;
                foreach ($sales_data_ojb as $sales_key_field => $sales_data_ojb_inner) {
                    /*  Set First Column's Value from array $arr_month_and_total  */
                    if ($j == 0) {
                        $arr_csv_values[$i]['month-' . $key] = $arr_month_and_total[$i];
                    }

                    $arr_csv_values[$i][$sales_key_field . '-' . $key] = $sales_data_ojb_inner;
                }
                $j++;
            }
            $i++;
        }

        $initial_year = (int) $inital_year_sting;
        $final_year = $initial_year - 1;

        /*  Set Labels of Columns in CSV with dynamic Years   */
        $arr_csv_columns = array(
            'Month',
            'Sales (Inc Tax) ' . $initial_year,
            'Sales (Ex Tax) ' . $initial_year,
            'Orders ' . $initial_year,
            'Products ' . $initial_year,
            'Sales (Inc Tax) ' . $final_year,
            'Sales (Ex Tax) ' . $final_year,
            'Orders ' . $final_year,
            'Products ' . $final_year
        );

        $str_to_time_for_name = strtotime(date('YmdHis'));

        $upload_dir = wp_upload_dir();
        //The path of the directory that we need to create.
        $directory_path = trailingslashit($upload_dir['basedir']) . 'export/';

        //Check if the directory already exists.
        if (!file_exists($directory_path)) {
            //Directory does not exist, so lets create it.
            mkdir($directory_path, 0777);
        }

        $output_filename_paths = trailingslashit($upload_dir['basedir']) . 'export/Firetech-Report-' . $initial_year . '-' . $final_year . '-' . $str_to_time_for_name . '.csv';
        $output_filename_URI = trailingslashit($upload_dir['baseurl']) . 'export/Firetech-Report-' . $initial_year . '-' . $final_year . '-' . $str_to_time_for_name . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=data.csv');
        $output_handle = @fopen($output_filename_paths, 'w');

        // Insert header row
        fputcsv($output_handle, $arr_csv_columns);
        foreach ($arr_csv_values as $arr_csv_valuess) {
            fputcsv($output_handle, $arr_csv_valuess);
        }

        // Close output file stream
        fclose($output_handle);

        echo json_encode(array('url' => $output_filename_URI));
        die();
    }
}
/**
 * Hook to create custom handlers for exportcsv AJAX request 
 * 
 */
add_action('wp_ajax_nopriv_export_inventory_csv', 'export_inventory_csv');
add_action('wp_ajax_export_inventory_csv', 'export_inventory_csv');

/**
 * Ajax Function to Export CSV 
 * 
 */
function export_inventory_csv() {
    /*  Get Json object of Sales Data */
    $inventory_data_obj = (json_decode(stripslashes($_POST['inventorydata']), true));
    /*  When JSON Object is not there   */
    if (!$inventory_data_obj) {
        die("There was an error");
    } else {
        $arr_csv_columns = array();
        $arr_csv_values = array();
        $i = 0;
        $arr_month_and_total = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC', 'Total'];

        /*  Access Inventory Data JSON Object   */
        foreach ($inventory_data_obj as $inventory_data_objs) {
            $j = 0;
            foreach ($inventory_data_objs as $inventory_key => $inventory_data_ojb) {
                /* Get Varaint data into out csv value array */
                if ($inventory_key == 'variants') {
                    if (isset($inventory_data_ojb) && !empty($inventory_data_ojb)) {
                        foreach ($inventory_data_ojb as $key => $inventory_data_ojb_value) {

                            $arr_csv_values[$i] = $inventory_data_ojb_value;
                            $arr_csv_values[$i]['title'] = get_the_title($inventory_data_ojb_value['parent_product_id']);
                            $arr_csv_values[$i]['sku'] = ($inventory_data_ojb_value['sku'] != '') ? $inventory_data_ojb_value['title'] . ' (' . $inventory_data_ojb_value['sku'] . ')' : $inventory_data_ojb_value['title'];
                            ;
                            unset($arr_csv_values[$i]['parent_product_id']);
                            unset($arr_csv_values[$i]['is_enabled']);
                            $i++;
                        }
                    } 
                } else if ($inventory_key != 'parent_product_id' && $inventory_key != 'is_enabled') { /* Skip values with keys parent_product_id and is_enabled */
                    $arr_csv_values[$i][$inventory_key] = $inventory_data_ojb;
                } 
            }
            $i++;
        }

        /*  Set Labels of Columns in CSV with dynamic Years   */
        $arr_csv_columns = array(
            'Product Name',
            'Variation (SKU)',
            'Original Stock',
            'Total Sales',
            'Available Stock',
            'Sold %',
            'Unsold %'
        );

        $str_to_time_for_name = strtotime(date('YmdHis'));

        $upload_dir = wp_upload_dir();
        //The path of the directory that we need to create.
        $directory_path = trailingslashit($upload_dir['basedir']) . 'export/';

        //Check if the directory already exists.
        if (!file_exists($directory_path)) {
            //Directory does not exist, so lets create it.
            mkdir($directory_path, 0777);
        }
        //The path of the directory that we need to create.
        $directory_path_inventory = trailingslashit($upload_dir['basedir']) . 'export/inventory';

        //Check if the directory already exists.
        if (!file_exists($directory_path_inventory)) {
            //Directory does not exist, so lets create it.
            mkdir($directory_path_inventory, 0777);
        }

        $output_filename_paths = trailingslashit($upload_dir['basedir']) . 'export/inventory/Firetech-Inventroy-Report-' . '-' . $str_to_time_for_name . '.csv';
        $output_filename_URI = trailingslashit($upload_dir['baseurl']) . 'export/inventory/Firetech-Inventroy-Report-' . '-' . $str_to_time_for_name . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=data.csv');
        $output_handle = @fopen($output_filename_paths, 'w');

        // Insert header row
        fputcsv($output_handle, $arr_csv_columns);
        foreach ($arr_csv_values as $csv_values) {
            if (is_array($csv_values) && isset($csv_values['total'])) {
                $total = count($csv_values['total']);
                if ($total) {
                    $csv_values = array_merge(array('Total', ' '), $csv_values['total'][$total - 1]);
                }
            }
            fputcsv($output_handle, $csv_values);
        }

        // Close output file stream
        fclose($output_handle);

        echo json_encode(array('url' => $output_filename_URI));
        die();
    }
}
