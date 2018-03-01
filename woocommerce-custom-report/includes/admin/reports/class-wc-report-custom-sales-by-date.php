<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * WC_Report_Custom_Sales_By_Date
 *
 * @author      WooThemes
 * @category    Admin
 * @package     WooCommerce/Admin/Reports
 * @version     2.1.0
 */
class WC_Report_Custom_Sales_By_Date extends WC_Admin_Report {

    private $report_data;

    /**
     * Constructor. 
     */
    public function __constuctor() {
        
    }

    /**
     * Output the report.
     */
    public function output_report() {
        /*  Check if $_REQUEST['time'] paramenter is set    */
        if (isset($_REQUEST['time'])) {
            $selected_year = $_REQUEST['time'];
        } else { /*  Check if $_REQUEST['time'] paramenter is not set then set it as current year    */
            $selected_year = date('Y');
        }

        /* Call filter to show data with select status */
        add_filter('woocommerce_reports_get_order_report_query', 'woocommerce_reports_get_order_report_query_filter');
        $sales_report_data_by_month = $this->get_sales_report_data_by_month($selected_year);

        remove_filter('woocommerce_reports_get_order_report_query', 'woocommerce_reports_get_order_report_query_filter');
        // List Sales Items
        if (!empty($sales_report_data_by_month)) {
            echo $html = $this->getTableSales($sales_report_data_by_month);
        } else {
            printf(__('<p class="awts-no-sales-overview"><a href="#"><strong>(%s)</strong> Currently, there is no sale for this month</a></p>', 'woocommerce'), date('d D - M, Y', current_time('timestamp')));
        }
    }

    /**
     * Get sales report data.
     * @return object
     */
    function get_sales_report_data_by_month($year) {
        global $woocommerce;
        include_once( $woocommerce->plugin_path() . '/includes/admin/reports/class-wc-report-sales-by-date.php' );
        $output_array = array();

        $month_array = [sprintf('%02d', 1), sprintf('%02d', 2), sprintf('%02d', 3), sprintf('%02d', 4), sprintf('%02d', 5), sprintf('%02d', 6), sprintf('%02d', 7), sprintf('%02d', 8), sprintf('%02d', 9), '10', '11', '12'];
        $year_array = [$year, $year - 1];
        foreach ($month_array as $month) {
            foreach ($year_array as $year) {
                /* Initialize object of WC_Report_Sales_By_Date */
                $sales_by_date = new WC_Report_Sales_By_Date();

                $date_string = $year . '-' . $month . '-01';
                /* Set start_date for the Report */
                $sales_by_date->start_date = strtotime(date($date_string, current_time('timestamp')));
                /* Set end_date for the Report */
                $sales_by_date->end_date = strtotime('+1month', $sales_by_date->start_date) - 86400;

                /*  Get Required Fields of Report */
                $output_array[$month][$year]['totalsales'] = $sales_by_date->get_report_data()->total_sales;
                $output_array[$month][$year]['netsales'] = $sales_by_date->get_report_data()->net_sales;
                $output_array[$month][$year]['totalorders'] = $sales_by_date->get_report_data()->total_orders;
                $output_array[$month][$year]['totalitems'] = $sales_by_date->get_report_data()->total_items;
            }
        }
        return $output_array;
    }

    /**
    *   getTableSales : Function to Render Sales Table
    **/
    public function getTableSales($sales_report_data_by_month) {
        $page = 'wc-custom-reports';
        $currency_symbol = get_woocommerce_currency_symbol();
        $user_selected_year = date('Y');
        if (isset($_REQUEST['time']))
            $user_selected_year = (int) $_REQUEST['time'];

        $user_previous_year = date('Y') - 1;
        if (isset($_REQUEST['time']))
            $user_previous_year = (int) $_REQUEST['time'] - 1;

        $selected_status = 'wc-completed';
        if (isset($_REQUEST['status'])) {
            $selected_status = $_REQUEST['status'];
        }
        ?>
        <input type="hidden" name="admin-ajax" value="<?php echo admin_url('admin-ajax.php'); ?>" id="admin-ajax">
        <div style="margin-top: 10px;">
            <div class="pdb-searchform">
                <form id="filter_form" action="?page=<?php echo $page; ?>" method="get">
                    <input type='hidden' name='page' value='<?php echo $page; ?>'>
                    <table class="form-table filter-custom-table">
                        <tbody>
                            <tr>
                                <td>
                                    <fieldset class="widefat inline-controls">
                                        <span class="year-block">Year :  </span>
                                        <select name="time"  class="filter_select" id="year_filter_select" length>
                                            <option value="<?php echo date('Y'); ?>"  <?php echo $user_selected_year == date('Y') ? 'selected="selected"' : ''; ?>><?php echo date('Y'); ?></option>
                                            <option value="<?php echo date('Y') - 1; ?>" <?php echo $user_selected_year == date('Y') - 1 ? 'selected="selected"' : ''; ?> ><?php echo date('Y') - 1; ?></option>
                                            <option value="<?php echo date('Y') - 2; ?>" <?php echo $user_selected_year == date('Y') - 2 ? 'selected="selected"' : ''; ?> ><?php echo date('Y') - 2; ?></option>
                                            <option value="<?php echo date('Y') - 3; ?>" <?php echo $user_selected_year == date('Y') - 3 ? 'selected="selected"' : ''; ?> ><?php echo date('Y') - 3; ?></option>
                                            <option value="<?php echo date('Y') - 4; ?>" <?php echo $user_selected_year == date('Y') - 4 ? 'selected="selected"' : ''; ?> ><?php echo date('Y') - 4; ?></option>
                                            <option value="<?php echo date('Y') - 5; ?>" <?php echo $user_selected_year == date('Y') - 5 ? 'selected="selected"' : ''; ?> ><?php echo date('Y') - 5; ?></option>
                                        </select>
                                    </fieldset>
                                </td>
                            </tr>
                            <?php
                            /*  Get Woocommerce Order Status */
                            $status_array = wc_get_order_statuses();
                            ?>
                            <tr>
                                <td>
                                    <fieldset class="widefat inline-controls">
                                        <span class="status-block">Order Status : </span>
                                        <select name="status[]" id="sortBy_select" class="filter_select" multiple>
                                            <?php
                                            foreach ($status_array as $status_key => $status) {
                                                /* Exclude Pending status from Status Drop Down */
                                                if ($status_key != 'wc-pending') {
                                                    if (isset($_REQUEST['status'])) {
                                                        ?>
                                                        <option value="<?php echo $status_key; ?>" <?php echo (in_array($status_key, $selected_status)) ? 'selected' : ''; ?> ><?php echo $status; ?></option>
                                                        <?php
                                                    } else {
                                                        ?>
                                                        <option value="<?php echo $status_key; ?>" <?php echo ($status_key == $selected_status) ? 'selected' : ''; ?> ><?php echo $status; ?></option>
                                                        <?php
                                                    }
                                                }
                                            }
                                            ?>
                                        </select>
                                        <input type="submit" value="<?php _e('Filter') ?>" class="button button-default">
                                        <input type="submit" name="export_action" class="button-secondary" id="export-csv" value="<?php _e('Export CSV') ?>"/>
                                    </fieldset>
                                </td>
                            </tr>
                        </tbody>
                    </table>
            </div>
        </div>
        <table class="widefat page fixed" id="final-table" cellpadding="0" border="1" bordercolor="#e1e1e1">
            <thead>
                <tr class="heading-row">
                    <th class="manage-column"></th>
                    <th class="manage-column" colspan="4"><?php _e('Year ' . $user_selected_year) ?></th>
                    <th class="manage-column" colspan="4"><?php _e('Year ' . $user_previous_year) ?></th>     
                </tr>
                <tr class="">
                    <th class="manage-column"><?php _e('Month') ?></th>
                    <th class="manage-column"><?php _e('Sales (Inc Tax) in ' . $currency_symbol) ?></th>
                    <th class="manage-column"><?php _e('Sales (Ex Tax) in ' . $currency_symbol) ?></th>     
                    <th class="manage-column"><?php _e('Orders') ?></th>     
                    <th class="manage-column"><?php _e('Products') ?></th>    
                    <th class="manage-column"><?php _e('Sales (Inc Tax) in ' . $currency_symbol) ?></th>
                    <th class="manage-column"><?php _e('Sales (Ex Tax) in ' . $currency_symbol) ?></th>     
                    <th class="manage-column"><?php _e('Orders') ?></th>     
                    <th class="manage-column"><?php _e('Products') ?></th>    
                </tr>
            </thead>
            <tbody>
                <?php
                $month_array = ['01' => 'JAN', '02' => 'FEB', '03' => 'MAR', '04' => 'APR', '05' => 'MAY', '06' => 'JUN', '07' => 'JUL', '08' => 'AUG', '09' => 'SEP', '10' => 'OCT', '11' => 'NOV', '12' => 'DEC'];
                $check_alternate = 1;
                
                foreach ($sales_report_data_by_month as $key => $value) {
                    foreach ($value as $inner_key => $inner_value) {
                        /* Fill Column Values for the Selected Year */
                        if ($inner_key == $user_selected_year) {
                            $initial_year_total_sales[] = (float) $inner_value['totalsales'];
                            $initial_year_net_sales[] = (float) $inner_value['netsales'];
                            $initial_year_total_items[] = (float) $inner_value['totalitems'];
                            $initial_year_total_orders[] = (float) $inner_value['totalorders'];
                            echo '<tr class="' . (ceil($check_alternate / 2) == ($check_alternate / 2)) ? "" : "alternate" . '">';
                            echo '<td>' . $month_array[$key] . '</td>                                
                                    <td>' . $inner_value['totalsales'] . '</td>                                
                                    <td>' . $inner_value['netsales'] . '</td>                                
                                    <td>' . $inner_value['totalorders'] . '</td>                                
                                    <td>' . $inner_value['totalitems'] . '</td>';
                        } else { /* Fill Column Values for the Previous Year */
                            $final_year_total_sales[] = (float) $inner_value['totalsales'];
                            $final_year_net_sales[] = (float) $inner_value['netsales'];
                            $final_year_total_items[] = (float) $inner_value['totalitems'];
                            $final_year_total_orders[] = (float) $inner_value['totalorders'];
                            echo '<td>' . $inner_value['totalsales'] . '</td>                                
                                    <td>' . $inner_value['netsales'] . '</td>                                
                                    <td>' . $inner_value['totalorders'] . '</td>                                
                                    <td>' . $inner_value['totalitems'] . '</td>';
                            echo '</tr>';
                        }
                        $check_alternate++;
                    }
                }
                ?>
                <tr class="">
                    <td>Total</td>                                
                    <td> <?php echo number_format(array_sum($initial_year_total_sales), 2) ?></td>                                
                    <td> <?php echo number_format(array_sum($initial_year_net_sales), 2) ?></td>                                
                    <td><?php echo array_sum($initial_year_total_orders) ?></td>                                
                    <td><?php echo array_sum($initial_year_total_items); ?></td>                                
                    <td> <?php echo number_format(array_sum($final_year_total_sales), 2) ?></td>                                
                    <td><?php echo number_format(array_sum($final_year_net_sales), 2); ?></td>                                
                    <td><?php echo array_sum($final_year_total_orders); ?></td>                                
                    <td><?php echo array_sum($final_year_total_items); ?></td>                                
                </tr>
                <?php
                $sales_report_data_by_month['13'] = array(
                    $user_selected_year => array(
                        'totalof_totalsales' => number_format(array_sum($initial_year_total_sales), 2),
                        'totalof_netsales' => number_format(array_sum($initial_year_net_sales), 2),
                        'totalof_totalorders' => array_sum($initial_year_total_orders),
                        'totalof_totalitems' => array_sum($initial_year_total_items)
                    ),
                    $user_previous_year => array(
                        'totalof_totalsales' => number_format(array_sum($final_year_total_sales), 2),
                        'totalof_netsales' => number_format(array_sum($final_year_net_sales), 2),
                        'totalof_totalorders' => array_sum($final_year_total_orders),
                        'totalof_totalitems' => array_sum($final_year_total_items)
                    )
                );
                ?>
            </tbody>
        </table>
        </form>
        <input type="hidden" name="sales-data" id="sales-data" value='<?php echo json_encode($sales_report_data_by_month, JSON_UNESCAPED_SLASHES); ?>' >
        <script>
            jQuery(document).ready(function () {
                /* On Click of Export CSV Button Call ajax to export table data in CSV Format */
                jQuery("#export-csv").on("click", function (e) {
                    e.preventDefault();
                    var formData = jQuery("#filter_form").serializeArray();
                    var sales_data = jQuery("#sales-data").val();
                    var ajaxurl = jQuery("#admin-ajax").val();

                    /* Pass Action in ajax Request */
                    formData.push({name: 'action', value: 'exportcsv'});

                    /* Pass Sales Data in ajax Request */
                    formData.push({name: 'salesdata', value: sales_data});

                    jQuery.ajax({
                        type: "POST",
                        url: ajaxurl,
                        data: formData,
                        dataType: 'json',
                        success: function (response)
                        {
                            try {
                                window.open(response.url, '_blank');
                            } catch (error) {
                                console.log('error', error);
                            }
                        }
                    })
                });
            });
        </script>
        <?php
    }

}

function woocommerce_reports_get_order_report_query_filter($query) {
    if (isset($_REQUEST['status'])) {
        $selected_status = implode("','", $_REQUEST['status']);
        $query['where'] .= "AND 	posts.post_status IN ('" . $selected_status . "')";
    } else {
        $selected_status = "wc-completed";
        $query['where'] .= "AND 	posts.post_status IN ('" . $selected_status . "')";
    }
    return $query;
}
