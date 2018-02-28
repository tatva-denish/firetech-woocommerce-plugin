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

    public function __constuctor() {
        
    }

    /**
     * Output the report.
     */
    public function output_report() {
        if (isset($_REQUEST['time'])) {
            $selctedYear = $_REQUEST['time'];
        } else {
            $selctedYear = date('Y');
        }
        add_filter('woocommerce_reports_get_order_report_query', 'woocommerce_reports_get_order_report_query_filter');
        $salesReportDataByMonth = $this->get_sales_report_data_by_month($selctedYear);
//        
        remove_filter('woocommerce_reports_get_order_report_query', 'woocommerce_reports_get_order_report_query_filter');
        // List Sales Items
        if (!empty($salesReportDataByMonth)) {
            echo $html = $this->getTableSales($salesReportDataByMonth);
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
        $outputArray = array();

        $monthArray = [sprintf('%02d', 1), sprintf('%02d', 2), sprintf('%02d', 3), sprintf('%02d', 4), sprintf('%02d', 5), sprintf('%02d', 6), sprintf('%02d', 7), sprintf('%02d', 8), sprintf('%02d', 9), '10', '11', '12'];
        $yearArray = [$year, $year - 1];
        foreach ($monthArray as $montht) {
            foreach ($yearArray as $yeara) {
                $sales_by_date = new WC_Report_Sales_By_Date();
                $date_string = $yeara . '-' . $montht . '-01';
                $sales_by_date->start_date = strtotime(date($date_string, current_time('timestamp')));
                $sales_by_date->end_date = strtotime('+1month', $sales_by_date->start_date) - 86400;
                $outputArray[$montht][$yeara]['totalsales'] = $sales_by_date->get_report_data()->total_sales;
                $outputArray[$montht][$yeara]['netsales'] = $sales_by_date->get_report_data()->net_sales;
                $outputArray[$montht][$yeara]['totalorders'] = $sales_by_date->get_report_data()->total_orders;
                $outputArray[$montht][$yeara]['totalitems'] = $sales_by_date->get_report_data()->total_items;
            }
        }
        return $outputArray;
    }

    public function getTableSales($salesReportDataByMonth) {
        $page = 'wc-custom-reports';
        $currency_symbol = get_woocommerce_currency_symbol();
        $selectedYear = date('Y');
        if (isset($_REQUEST['time']))
            $selectedYear = (int) $_REQUEST['time'];

        $previousYear = date('Y') - 1;
        if (isset($_REQUEST['time']))
            $previousYear = (int) $_REQUEST['time'] - 1;

        $selectedStatus = 'wc-completed';
        if (isset($_REQUEST['status'])) {
            $selectedStatus = $_REQUEST['status'];
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
                                        <span style="padding-right: 51px;">Year :  </span>
                                        <select name="time"  class="filter_select" id="year_filter_select" length>
                                            <option value="<?php echo date('Y'); ?>"  <?php echo $selectedYear == date('Y') ? 'selected="selected"' : ''; ?>><?php echo date('Y'); ?></option>
                                            <option value="<?php echo date('Y') - 1; ?>" <?php echo $selectedYear == date('Y') - 1 ? 'selected="selected"' : ''; ?> ><?php echo date('Y') - 1; ?></option>
                                            <option value="<?php echo date('Y') - 2; ?>" <?php echo $selectedYear == date('Y') - 2 ? 'selected="selected"' : ''; ?> ><?php echo date('Y') - 2; ?></option>
                                            <option value="<?php echo date('Y') - 3; ?>" <?php echo $selectedYear == date('Y') - 3 ? 'selected="selected"' : ''; ?> ><?php echo date('Y') - 3; ?></option>
                                            <option value="<?php echo date('Y') - 4; ?>" <?php echo $selectedYear == date('Y') - 4 ? 'selected="selected"' : ''; ?> ><?php echo date('Y') - 4; ?></option>
                                            <option value="<?php echo date('Y') - 5; ?>" <?php echo $selectedYear == date('Y') - 5 ? 'selected="selected"' : ''; ?> ><?php echo date('Y') - 5; ?></option>
                                        </select>
                                    </fieldset>
                                </td>
                            </tr>
                            <?php
                            $statusArray = wc_get_order_statuses();
                            ?>
                            <tr>
                                <td>
                                    <fieldset class="widefat inline-controls">
                                        <span style="vertical-align: top;">Order Status : </span>
                                        <select name="status[]" id="sortBy_select" class="filter_select" multiple>
                                            <?php
                                            foreach ($statusArray as $statusKey => $status) {
                                                if ($statusKey != 'wc-pending') {
                                                    if (isset($_REQUEST['status'])) {
                                                        ?>
                                                        <option value="<?php echo $statusKey; ?>" <?php echo (in_array($statusKey, $selectedStatus)) ? 'selected' : ''; ?> ><?php echo $status; ?></option>
                                                        <?php
                                                    } else {
//                                                    echo '$selectedStatus'.$selectedStatus;exit;
                                                        ?>
                                                        <option value="<?php echo $statusKey; ?>" <?php echo ($statusKey == $selectedStatus) ? 'selected' : ''; ?> ><?php echo $status; ?></option>
                                                        <?php
                                                    }
                                                }
                                            }
                                            ?>
                                        </select>
                                        <input type="submit" value="Filter" class="button button-default">
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

                    <th class="manage-column" colspan="4"><?php _e('Year ' . $selectedYear) ?></th>
                    <th class="manage-column" colspan="4"><?php _e('Year ' . $previousYear) ?></th>     
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
                $monthArray = ['01' => 'JAN', '02' => 'FEB', '03' => 'MAR', '04' => 'APR', '05' => 'MAY', '06' => 'JUN', '07' => 'JUL', '08' => 'AUG', '09' => 'SEP', '10' => 'OCT', '11' => 'NOV', '12' => 'DEC'];
                $checkAlternate = 1;
                foreach ($salesReportDataByMonth as $key => $value) {
                    foreach ($value as $innerkey => $innervalue) {
                        if ($innerkey == $selectedYear) {
                            $initialYearTotalSales[] = (float) $innervalue['totalsales'];
                            $initialYearNetsales[] = (float) $innervalue['netsales'];
                            $initialYearTotalItems[] = (float) $innervalue['totalitems'];
                            $initialYearTotalOrders[] = (float) $innervalue['totalorders'];
                            echo '<tr class="' . (ceil($checkAlternate / 2) == ($checkAlternate / 2)) ? "" : "alternate" . '">';
                            echo '<td>' . $monthArray[$key] . '</td>                                
                                    <td>' . $innervalue['totalsales'] . '</td>                                
                                    <td>' . $innervalue['netsales'] . '</td>                                
                                    <td>' . $innervalue['totalorders'] . '</td>                                
                                    <td>' . $innervalue['totalitems'] . '</td>';
                        } else {
                            $finalYearTotalSales[] = (float) $innervalue['totalsales'];
                            $finalYearNetsales[] = (float) $innervalue['netsales'];
                            $finalYearTotalItems[] = (float) $innervalue['totalitems'];
                            $finalYearTotalOrders[] = (float) $innervalue['totalorders'];
                            echo '<td>' . $innervalue['totalsales'] . '</td>                                
                                    <td>' . $innervalue['netsales'] . '</td>                                
                                    <td>' . $innervalue['totalorders'] . '</td>                                
                                    <td>' . $innervalue['totalitems'] . '</td>';
                            echo '</tr>';
                        }
                        $checkAlternate++;
                    }
                }
                ?>
                <tr class="">
                    <td>Total</td>                                
                    <td> <?php echo number_format(array_sum($initialYearTotalSales), 2) ?></td>                                
                    <td> <?php echo number_format(array_sum($initialYearNetsales), 2) ?></td>                                
                    <td><?php echo array_sum($initialYearTotalOrders) ?></td>                                
                    <td><?php echo array_sum($initialYearTotalItems); ?></td>                                
                    <td> <?php echo number_format(array_sum($finalYearTotalSales), 2) ?></td>                                
                    <td><?php echo number_format(array_sum($finalYearNetsales), 2); ?></td>                                
                    <td><?php echo array_sum($finalYearTotalOrders); ?></td>                                
                    <td><?php echo array_sum($finalYearTotalItems); ?></td>                                
                </tr>
                <?php
                $salesReportDataByMonth['13'] = array(
                    $selectedYear => array(
                        'totalof_totalsales' => number_format(array_sum($initialYearTotalSales), 2),
                        'totalof_netsales' => number_format(array_sum($initialYearNetsales), 2),
                        'totalof_totalorders' => array_sum($initialYearTotalOrders),
                        'totalof_totalitems' => array_sum($initialYearTotalItems)
                    ),
                    $previousYear => array(
                        'totalof_totalsales' => number_format(array_sum($finalYearTotalSales), 2),
                        'totalof_netsales' => number_format(array_sum($finalYearNetsales), 2),
                        'totalof_totalorders' => array_sum($finalYearTotalOrders),
                        'totalof_totalitems' => array_sum($finalYearTotalItems)
                    )
                );
                ?>
            </tbody>
        </table>
        </form>
        <input type="hidden" name="sales-data" id="sales-data" value='<?php echo json_encode($salesReportDataByMonth, JSON_UNESCAPED_SLASHES); ?>' >
        <script>
            jQuery(document).ready(function () {
                jQuery("#export-csv").on("click", function (e) {
                    e.preventDefault();
                    var formData = jQuery("#filter_form").serializeArray();

                    var sales_data = jQuery("#sales-data").val();
                    console.log(sales_data);
                    formData.push({name: 'action', value: 'exportcsv'});
                    formData.push({name: 'salesdata', value: sales_data});
                    var ajaxurl = jQuery("#admin-ajax").val();

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
        $selectedStatus = implode("','", $_REQUEST['status']);
        $query['where'] .= "AND 	posts.post_status IN ('" . $selectedStatus . "')";
    } else {
        $selectedStatus = "wc-completed";
        $query['where'] .= "AND 	posts.post_status IN ('" . $selectedStatus . "')";
    }
    return $query;
}
