<?php
//echo 'debug27';exit;
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
class WC_Report_Custom_Inventory_Custom extends WC_Admin_Report {

    /**
     * Chart colors.
     *
     * @var array
     */
    public $chart_colours = array();

    /**
     * The report data.
     *
     * @var stdClass
     */
    private $report_data;

    /**
     * Get report data.
     * @return stdClass
     */
    public function get_report_data() {
        if (empty($this->report_data)) {
            $this->query_report_data();
        }
        return $this->report_data;
    }

   
    /**
     * Output the report.
     */
    public function output_report() {
        $ranges = array(
            'year' => __('Year', 'woocommerce'),
            'last_month' => __('Last month', 'woocommerce'),
            'month' => __('This month', 'woocommerce'),
            '7day' => __('Last 7 days', 'woocommerce'),
        );

        $current_range = !empty($_GET['range']) ? sanitize_text_field($_GET['range']) : '7day';

        if (!in_array($current_range, array('custom', 'year', 'last_month', 'month', '7day'))) {
            $current_range = '7day';
        }

        $this->check_current_range_nonce($current_range);
        $this->calculate_current_range($current_range);

        global $woocommerce;
        include_once($woocommerce->plugin_path() . '/includes/admin/reports/class-wc-admin-report.php');

        $table = $this->getTableInventory();
        echo $table;
    }

  
    public function getTableInventory() {
        global $wpdb;
        ?>
        <div style="margin-top: 10px;">
            <div class="pdb-searchform">
                <form id="sort_filter_form" action="?page=<?php echo $page; ?>" method="post">
                    <input type="hidden" id="page_url" value="<?php echo admin_url('/admin.php?page=' . $page); ?>">                
                    <input type="hidden" value="sort_search" name="action">
                    <table class="form-table filter-custom-table">
                        <tbody>
                            <tr>
                                <td>
                                    <fieldset class="widefat inline-controls">
                                        <span style="padding-right: 11px;">Product Search :  </span>
                                        <input type="text">
                                        <input type="submit" value="Search" name="submit-button" class="button button-default">
                                        <input type="submit" value="Export CSV" name="export-csv-button" class="button button-default export-csv-class">
                                    </fieldset>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                
            </div>
        </div>
            

            <table class="widefat page fixed" id="final-table-inventory" cellpadding="0" border="1" bordercolor="#e1e1e1">
                <thead>
                    <tr class="">
                        <th class="manage-column"><?php _e('Product Name') ?></th>
                        <th class="manage-column"><?php _e('Variation (SKU)') ?></th>
                        <th class="manage-column"><?php _e('Original Stock') ?></th>     
                        <th class="manage-column"><?php _e('Total Sales') ?></th>     
                        <th class="manage-column"><?php _e('Available Stock') ?></th>    
                        <th class="manage-column"><?php _e('Sold %') ?></th>
                        <th class="manage-column"><?php _e('Unsold%') ?></th>      
                    </tr>
                </thead>
                <tbody>
                    <?php
                  
                            ?>
                            <tr  class="">
                                <td rowspan="3">Duke T-Shit</td>                                
                                <td>DUK-001</td>                                
                                <td></td>                                
                                <td></td>                                
                                <td></td>                                
                                <td></td>                                
                                <td></td>                                
                            </tr>
                            <tr class="alternate">
                                <td>DUK-002</td>                                
                                <td></td>                                
                                <td></td>                                
                                <td></td>                                
                                <td></td>                                
                                <td></td>                                
                            </tr>
                            <tr class="">
                                <td>DUK-003</td>                                
                                <td></td>                                
                                <td></td>                                
                                <td></td>                                
                                <td></td>                                
                                <td></td>                                
                            </tr>
                           <tr class="alternate">
                                <td>Total</td>                                
                                <td></td>                                
                                <td></td>                                
                                <td></td>                                
                                <td></td>                                
                                <td></td>                                
                                <td></td>                              
                            </tr>
                            <tr  class="">
                                <td rowspan="3">Denim Jeans</td>                                
                                <td>DNM-001</td>                                
                                <td></td>                                
                                <td></td>                                
                                <td></td>                                
                                <td></td>                                
                                <td></td>                                
                            </tr>
                            <tr class="alternate">
                                <td>DNM-002</td>                                
                                <td></td>                                
                                <td></td>                                
                                <td></td>                                
                                <td></td>                                
                                <td></td>                                
                            </tr>
                            <tr class="">
                                <td>DNM-003</td>                                
                                <td></td>                                
                                <td></td>                                
                                <td></td>                                
                                <td></td>                                
                                <td></td>                                
                            </tr>
                           <tr class="alternate">
                                <td>Total</td>                                
                                <td></td>                                
                                <td></td>                                
                                <td></td>                                
                                <td></td>                                
                                <td></td>                                
                                <td></td>                              
                            </tr>
                            <?php
                      
                    ?>
                </tbody>
            </table>
        </form>
        <?php
    }

}
