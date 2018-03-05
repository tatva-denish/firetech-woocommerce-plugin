<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * WC_Report_Custom_Inventory_Custom
 * 
 */
class WC_Report_Custom_Inventory_Custom extends WC_Admin_Report {

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
        global $woocommerce;
        include_once($woocommerce->plugin_path() . '/includes/admin/reports/class-wc-admin-report.php');

        $arr_prodct = $this->get_woocommerce_product_list();
        if (isset($_REQUEST['tab']) && $_REQUEST['tab'] == 'inventory-paginate') {
            $table = $this->getTableInventoryWithPagination($arr_prodct);
        } else {
            $table = $this->getTableInventory($arr_prodct);
        }
        echo $table;
    }

    /**
     *   getTableInventoryWithPagination : Function to Render Inventory Table with Pagination
     * */
    public function getTableInventoryWithPagination($arr_prodct) {
        $page = 'wc-custom-reports';
        ?>
        <input type="hidden" name="admin-ajax" value="<?php echo admin_url('admin-ajax.php'); ?>" id="admin-ajax">
        <div style="margin-top: 10px;">
            <div class="pdb-searchform">
                <form id="export_filter_form" action="" method="get">
                    <input type="hidden" name="page" value="<?php echo $page; ?>" />
                    <input type="hidden" name="tab" value="inventory" />
                    <table class="form-table filter-custom-table">
                        <tbody>
                            <tr>
                                <td>
                                    <fieldset class="widefat inline-controls">
                                        <span class="status_notes"><span class="disable_block">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>Disabled</span>
                                    </fieldset>
                                </td>
                            </tr>
                        </tbody>
                    </table>
            </div>
        </div>
        <table class="widefat page fixed table table-striped table-bordered" id="final-table-inventory" cellpadding="0" border="1" bordercolor="#e1e1e1">
            <thead>
                <tr class="">
                    <th class="manage-column" width="25%"><?php _e('Product Name') ?></th>
                    <th class="manage-column" width="50%"><?php _e('Variation (SKU)') ?></th>
                    <th class="manage-column"><?php _e('Original Stock') ?></th>     
                    <th class="manage-column"><?php _e('Total Sales') ?></th>     
                    <th class="manage-column"><?php _e('Available Stock') ?></th>    
                    <th class="manage-column"><?php _e('Sold %') ?></th>
                    <th class="manage-column"><?php _e('Unsold %') ?></th>      
                </tr>
            </thead>
            <tbody>
                <?php
                if (count($arr_prodct) > 0) {
                    foreach ($arr_prodct as $product_key => $arr_product) {
                        if (isset($arr_product['variants']) && !empty($arr_product['variants'])) {
                            $counter = 0;
                            $variant_original_stock = array();
                            $variant_total_sales = array();
                            $variant_available_stock = array();
                            $variant_stock_sold = array();
                            $variant_stock_unsold = array();
                            foreach ($arr_product['variants'] as $variant_key => $variant_value) {
                                $variant_original_stock[] = $variant_value['original_stock'];
                                $variant_total_sales[] = $variant_value['total_sales'];
                                $variant_available_stock[] = $variant_value['available_stock'];
                                $variant_stock_sold[] = (float) number_format($variant_value['stock_sold'], 2);
                                $variant_stock_unsold[] = (float) number_format($variant_value['stock_unsold'], 2);

                                if ($variant_value['is_enabled'] != 'publish') {
                                    echo '<tr  class="disabled-class -' . $variant_value['is_enabled'] . '">';
                                } else {
                                    echo '<tr  class="">';
                                }
                                echo '<td>' . $arr_product['title'] . '</td>';
                                echo '<td>' . $variant_value['title'];
                                echo ($variant_value['sku'] != '') ? ' (' . $variant_value['sku'] . ')' : '';
                                echo '</td>';
                                echo '<td class="original-stock-total">' . $variant_value['original_stock'] . '</td>                                
                                <td>' . $variant_value['total_sales'] . '</td>                                
                                <td>' . $variant_value['available_stock'] . '</td>                                
                                <td>' . $variant_value['stock_sold'] . '</td>                                
                                <td>' . $variant_value['stock_unsold'] . '</td> ';
                                echo '</tr>';

                                $counter++;
                            }
                            $variant_original_stock_total = array_sum($variant_original_stock);
                            $variant_total_sales_total = array_sum($variant_total_sales);
                            $variant_available_stock_total = array_sum($variant_available_stock);
                            $variant_stock_sold_total = number_format(array_sum($variant_stock_sold), 2);
                            $variant_stock_unsold_total = number_format(array_sum($variant_stock_unsold), 2);
                            if ($counter == count($arr_product['variants'])) {
                                echo '<tr class="alternate">
                                        <td>Total</td>                                
                                        <td></td>                                
                                        <td class="original-stock-total">' . $variant_original_stock_total . '</td>                                
                                        <td>' . $variant_total_sales_total . '</td>                                
                                        <td>' . $variant_available_stock_total . '</td>                                
                                        <td>' . $variant_stock_sold_total . '</td>                                
                                        <td>' . $variant_stock_unsold_total . '</td>                             
                                    </tr>';
                            }
                        } else {
                            echo '<tr  class="">
                                <td>' . $arr_product['title'] . '</td>                                 
                                <td>' . $arr_product['title'] . ' (' . $arr_product['sku'] . ')' . '</td>                                
                                <td>' . $arr_product['original_stock'] . '</td>                                
                                <td>' . $arr_product['total_sales'] . '</td>                                
                                <td>' . $arr_product['available_stock'] . '</td>                                
                                <td>' . $arr_product['stock_sold'] . '</td>                                
                                <td>' . $arr_product['stock_unsold'] . '</td>     
                            </tr>';
                        }
                    }
                } else {
                    echo '<tr  class="">'
                    . '<td colspan="7" style="text-align:center">No records found, try again please</td>'
                    . '</tr>';
                }
                ?>
            </tbody>
        </table>
        </form>
        <input type="hidden" name="sales-data" id="inventory-data" value='<?php echo json_encode($arr_prodct, JSON_UNESCAPED_SLASHES); ?>' >
        <script>
            jQuery(document).ready(function () {
                /* On Click of Export CSV Button Call ajax to export table data in CSV Format */
                jQuery("#export-csv").on("click", function (e) {
                    e.preventDefault();
                    var formData = jQuery("#export_filter_form").serializeArray();
                    var inventory_data = jQuery("#inventory-data").val();
                    var ajaxurl = jQuery("#admin-ajax").val();

                    /* Pass Action in ajax Request */
                    formData.push({name: 'action', value: 'export_inventory_csv'});

                    /* Pass Sales Data in ajax Request */
                    formData.push({name: 'inventorydata', value: inventory_data});

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

                jQuery('#final-table-inventory').dataTable({
                    "ordering": false
                });
            });
        </script>
        <?php
    }
    /**
     *   getTableInventory : Function to Render Inventory Table
     * */
    public function getTableInventory($arr_prodct) {
        $page = 'wc-custom-reports';
        ?>
        <input type="hidden" name="admin-ajax" value="<?php echo admin_url('admin-ajax.php'); ?>" id="admin-ajax">
        <div style="margin-top: 10px;">
            <div class="pdb-searchform">
                <form id="export_filter_form" action="" method="get">
                    <input type="hidden" name="page" value="<?php echo $page; ?>" />
                    <input type="hidden" name="tab" value="inventory" />
                    <table class="form-table filter-custom-table">
                        <tbody>
                            <tr>
                                <td>
                                    <fieldset class="widefat inline-controls">
                                        <span style="padding-right: 11px;">Product Search :  </span>
                                        <input type="text" name="search">
                                        <input type="submit" value="Search"  class="button button-default">
                                        <?php if (isset($_REQUEST['search'])) { ?>
                                            <a href="<?php echo admin_url('admin.php') . '?page=' . $page . '&tab=inventory'; ?>" class="button button-default"> Clear Search</a>
        <?php } ?>
                                        <input type="submit" value="Export CSV" class="button button-default export-csv-class" id="export-csv">
                                        <span class="status_notes"><span class="disable_block">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>Disabled</span>
                                    </fieldset>
                                </td>
                            </tr>
                        </tbody>
                    </table>
            </div>
        </div>
        <table class="widefat page fixed table table-striped table-bordered" id="final-table-inventory" cellpadding="0" border="1" bordercolor="#e1e1e1">
            <thead>
                <tr class="">
                    <th class="manage-column" width="25%"><?php _e('Product Name') ?></th>
                    <th class="manage-column" width="50%"><?php _e('Variation (SKU)') ?></th>
                    <th class="manage-column"><?php _e('Original Stock') ?></th>     
                    <th class="manage-column"><?php _e('Total Sales') ?></th>     
                    <th class="manage-column"><?php _e('Available Stock') ?></th>    
                    <th class="manage-column"><?php _e('Sold %') ?></th>
                    <th class="manage-column"><?php _e('Unsold %') ?></th>      
                </tr>
            </thead>
            <tbody>
                <?php
                if (count($arr_prodct) > 0) {
                    foreach ($arr_prodct as $product_key => $arr_product) {
                        if (isset($arr_product['variants']) && !empty($arr_product['variants'])) {
                            $counter = 0;
                            $variant_original_stock = array();
                            $variant_total_sales = array();
                            $variant_available_stock = array();
                            $variant_stock_sold = array();
                            $variant_stock_unsold = array();
                            foreach ($arr_product['variants'] as $variant_key => $variant_value) {
                                $variant_original_stock[] = $variant_value['original_stock'];
                                $variant_total_sales[] = $variant_value['total_sales'];
                                $variant_available_stock[] = $variant_value['available_stock'];
                                $variant_stock_sold[] = (float) number_format($variant_value['stock_sold'], 2);
                                $variant_stock_unsold[] = (float) number_format($variant_value['stock_unsold'], 2);

                                if ($variant_value['is_enabled'] != 'publish') {
                                    echo '<tr  class="disabled-class -' . $variant_value['is_enabled'] . '">';
                                } else {
                                    echo '<tr  class="">';
                                }
                                if (count($arr_product['variants']) > 1) {
                                    if ($counter == 0) {
                                        echo '<td  rowspan="' . count($arr_product['variants']) . '">' . $arr_product['title'] . '</td>';
                                    }
                                } else {
                                    echo '<td>' . get_the_title($arr_product['parent_product_id']) . '</td> ';
                                }
                                echo '<td>' . $variant_value['title'];
                                echo ($variant_value['sku'] != '') ? ' (' . $variant_value['sku'] . ')' : '';
                                echo '</td>';
                                echo '<td class="original-stock-total">' . $variant_value['original_stock'] . '</td>                                
                                <td>' . $variant_value['total_sales'] . '</td>                                
                                <td>' . $variant_value['available_stock'] . '</td>                                
                                <td>' . $variant_value['stock_sold'] . '</td>                                
                                <td>' . $variant_value['stock_unsold'] . '</td> ';
                                echo '</tr>';

                                $counter++;
                            }
                            $variant_original_stock_total = array_sum($variant_original_stock);
                            $variant_total_sales_total = array_sum($variant_total_sales);
                            $variant_available_stock_total = array_sum($variant_available_stock);
                            $variant_stock_sold_total = number_format(array_sum($variant_stock_sold), 2);
                            $variant_stock_unsold_total = number_format(array_sum($variant_stock_unsold), 2);
                            if ($counter == count($arr_product['variants'])) {
                                echo '<tr class="alternate">
                                        <td>Total</td>                                
                                        <td></td>                                
                                        <td class="original-stock-total">' . $variant_original_stock_total . '</td>                                
                                        <td>' . $variant_total_sales_total . '</td>                                
                                        <td>' . $variant_available_stock_total . '</td>                                
                                        <td>' . $variant_stock_sold_total . '</td>                                
                                        <td>' . $variant_stock_unsold_total . '</td>                             
                                    </tr>';

                            }
                        } else {
                            echo '<tr  class="">
                                <td>' . $arr_product['title'] . '</td>                                 
                                <td>' . $arr_product['title'] . ' (' . $arr_product['sku'] . ')' . '</td>                                
                                <td>' . $arr_product['original_stock'] . '</td>                                
                                <td>' . $arr_product['total_sales'] . '</td>                                
                                <td>' . $arr_product['available_stock'] . '</td>                                
                                <td>' . $arr_product['stock_sold'] . '</td>                                
                                <td>' . $arr_product['stock_unsold'] . '</td>     
                            </tr>';
                        }
                    }
                } else {
                    echo '<tr  class="">'
                    . '<td colspan="7" style="text-align:center">No records found, try again please</td>'
                    . '</tr>';
                }
                ?>
            </tbody>
        </table>
        </form>
        <input type="hidden" name="sales-data" id="inventory-data" value='<?php echo json_encode($arr_prodct, JSON_UNESCAPED_SLASHES); ?>' >
        <script>
            jQuery(document).ready(function () {
                /* On Click of Export CSV Button Call ajax to export table data in CSV Format */
                jQuery("#export-csv").on("click", function (e) {
                    e.preventDefault();
                    var formData = jQuery("#export_filter_form").serializeArray();
                    var inventory_data = jQuery("#inventory-data").val();
                    var ajaxurl = jQuery("#admin-ajax").val();

                    /* Pass Action in ajax Request */
                    formData.push({name: 'action', value: 'export_inventory_csv'});

                    /* Pass Sales Data in ajax Request */
                    formData.push({name: 'inventorydata', value: inventory_data});

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

    // ********* Get all products and variations and sort alphbetically, return in array (title, sku, id)*******
    function get_woocommerce_product_list() {

        $available_variations = 0;
        $available_variations = array();
        $full_product_list = array();
        if (isset($_REQUEST['search'])) {
            $search_string = $_REQUEST['search'];

            $loop = new WP_Query(array('post_type' => array('product', 'product_variation'), 'posts_per_page' => -1, 'orderby' => 'ID', 'order' => 'ASC',
                'meta_query' => array(
                    array(
                        'key' => '_sku',
                        'value' => $search_string,
                        'type' => 'CHAR',
                        'compare' => 'LIKE',
                    ),
                )
            ));
        } else {

            $loop = new WP_Query(array('post_type' => array('product', 'product_variation'), 'posts_per_page' => -1, 'orderby' => 'ID', 'order' => 'ASC'));
        }
        while ($loop->have_posts()) : $loop->the_post();
            $theid = get_the_ID();
            $product = wc_get_product($theid);
            $parent_id = wp_get_post_parent_id($theid);
            if ($parent_id && get_post_type() == 'product_variation') {
                $sku = get_post_meta($theid, '_sku', true);
                $is_enabled = get_post_status($theid);
                $thetitle = get_the_title();
                $original_stock = $total_sales + $available_stock;
                $total_sales = ($product->get_total_sales()) ? $product->get_total_sales() : 0;
                $available_stock = ($product->get_stock_quantity() != '') ? $product->get_stock_quantity() : 0;
                $stock_sold = number_format((($total_sales * 100) / $original_stock), 2);
                $stock_unsold = number_format((($available_stock * 100) / $original_stock), 2);

                $variant_original_stock[] = $original_stock;
                $variant_total_sales[] = $total_sales;
                $variant_available_stock[] = $available_stock;
                $variant_stock_sold[] = (float) number_format($stock_sold, 2);
                $variant_stock_unsold[] = (float) number_format($stock_unsold, 2);

                $arr_temp = array(
                    'title' => $thetitle,
                    'sku' => $sku,
                    'original_stock' => $original_stock,
                    'total_sales' => $total_sales,
                    'available_stock' => $available_stock,
                    'stock_sold' => $stock_sold,
                    'stock_unsold' => $stock_unsold,
                    'parent_product_id' => $parent_id,
                    'is_enabled' => $is_enabled,
                );
                $arr_total = array(
                    'variant_total_sales_total' => array_sum($variant_original_stock),
                    'variant_total_sales' => array_sum($variant_total_sales),
                    'variant_available_stock' => array_sum($variant_available_stock),
                    'variant_stock_sold' => (float) number_format(array_sum($variant_stock_sold), 2),
                    'variant_stock_unsold' => (float) number_format(array_sum($variant_stock_unsold), 2)
                );
                $full_product_list[$parent_id]['variants'][] = $arr_temp;
                $full_product_list[$parent_id]['total'][] = $arr_total;
            } else {
                $sku = get_post_meta($theid, '_sku', true);
                $thetitle = get_the_title();
                $total_sales = ($product->get_total_sales()) ? $product->get_total_sales() : 0;
                $available_stock = ($product->get_stock_quantity() != '') ? $product->get_stock_quantity() : 0;
                $original_stock = $total_sales + $available_stock;
                $stock_sold = number_format((($total_sales * 100) / $original_stock), 2);
                $stock_unsold = number_format((($available_stock * 100) / $original_stock), 2);

                $full_product_list[$theid] = array(
                    'title' => $thetitle,
                    'sku' => $sku,
                    'original_stock' => $original_stock,
                    'total_sales' => $total_sales,
                    'available_stock' => $available_stock,
                    'stock_sold' => $stock_sold,
                    'stock_unsold' => $stock_unsold,
                    'parent_product_id' => '',
                    'is_enabled' => $is_enabled,
                    'variants' => []
                );
            }

        endwhile;
        wp_reset_query();
        return $full_product_list;
    }
}
