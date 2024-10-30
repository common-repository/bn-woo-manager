<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * BN Woo Manager AJAX Product Edit
 *
 * @class       BN_Woo_Manager_AJAX_Product_Edit
 * @version     1.17
 * @author      BN-KareM
 */
class BN_Woo_Manager_AJAX_Product_Edit {
     /**
     * Init
     *
     */
    public static function init() {
        global $wpdb;
        // Hook for adding submenu-page AJAX Product Edit
        add_action( 'admin_menu', __CLASS__ . '::bn_add_page', 99 );
        // AJAX Delete LOG
        add_action( 'wp_ajax_bn_action_delete_log', __CLASS__ . '::bn_ajax_delete_log' );
        // AJAX respond function Show Product List Part
        add_action( 'wp_ajax_bn_action_show', __CLASS__ . '::bn_ajax_product_list_page' );
        // AJAX respond function Product List update fields
        add_action( 'wp_ajax_bn_action_update', __CLASS__ . '::bn_product_data_update' );
        // AJAX respond function Get Price HTML
        add_action( 'wp_ajax_bn_action_get_price_html', __CLASS__ . '::bn_product_data_get_price_html' );
        // AJAX respond function Get Shipping Class
        add_action( 'wp_ajax_bn_action_get_shipping_class', __CLASS__ . '::bn_product_data_get_shipping_class' );
        // Hook product delete & update - transients cache
        add_action( 'before_delete_post', __CLASS__ . '::bn_delete_transients', 10, 1 );
        add_action( 'save_post', __CLASS__ . '::bn_delete_transients', 10, 1 );
        // Hook attribute updates - transients cache
        add_action( 'edited_term', __CLASS__ . '::bn_delete_attribute_transients', 10, 3 );
        // AJAX delete transients Cache
        add_action( 'wp_ajax_bn_action_delete_cache', __CLASS__ . '::bn_delete_cache' );
    }

    // action function for admin_menu hook
    public static function bn_add_page() {
        $bn_woo_manager_product_edit = add_submenu_page( 'edit.php?post_type=product',  'BN Woo Manager AJAX ' . __('Product','bn-woo-manager') . ' Edit', 'BN Woo Manager AJAX ' . __('Product','bn-woo-manager') . ' Edit', 'manage_woocommerce', 'bn_woo_manager_ajax_product_edit',  __CLASS__ . '::bn_ajax_product_edit_page' );
        // Load BN Woo Manager Product Edit Script
        add_action( 'admin_enqueue_scripts', function ( $hook ) use ( $bn_woo_manager_product_edit ) {
            if( $hook !== $bn_woo_manager_product_edit ) {
                return;
            }
            // Get the protocol of the current page
            $protocol = isset( $_SERVER['HTTPS'] ) ? 'https://' : 'http://';
            // jQuery localize array
            $bn_local_jquery = array(
                'ajax_url'              => admin_url( 'admin-ajax.php', $protocol ),
                'NONCE'                 => wp_create_nonce( 'bn_woo_manager_ajax_nonce' ),
                'per_page'              => __( 'Prod. per page', 'bn-woo-manager' ),
                'show_all'              => __( 'Show all', 'bn-woo-manager' ),
                'bn_prev'               => __( 'Prev', 'bn-woo-manager' ),
                'bn_next'               => __( 'Next', 'bn-woo-manager' ),
                'attention_delete_log'  => __( 'Want to delete all LOGS? (Admin-Rigths required)', 'bn-woo-manager' ),
                'attention_delete_cache'  => __( 'Normally, the deletion of the cache is not necessary. But if information or products are missing, you can safely delete the cache here. Want to delete BN Woo Manager Cache now?', 'bn-woo-manager' ),
                'canceled_operation'    => __( 'You have canceled the operation!', 'bn-woo-manager' ),
                'attentiom_all_undo'    => __( 'Do you want to make any visible changes Undo? Do not leave this page before any changes are done !!!', 'bn-woo-manager' ),
                'activate'              => __( 'Activate', 'bn-woo-manager' ),
                'deactivate'            => __( 'Deactivate', 'bn-woo-manager' ),
                'attention_group_edit2' => __( 'selected fields! Want to make the changes?', 'bn-woo-manager' ),
                'notpossible'           => __( 'Not possible!', 'bn-woo-manager' )
            );
            // jQuery js
            wp_enqueue_script( 'bn_woo_manager_script' );
            wp_enqueue_script( 'bn_woo_manager_ajax_product_edit_script' );
            // localize js
            wp_localize_script( 'bn_woo_manager_ajax_product_edit_script', 'bn_woo_manager', $bn_local_jquery );
        } );
    }

    // Product List Page static
    public static function bn_ajax_product_edit_page() {
        $args1 = array(
        'taxonomy'          => 'product_cat',
        'name'              => 'bn_cat',
        'echo'              => false,
        'hierarchical'      => true,
        'selected'          => 'All',
        'show_option_all'   => __( 'Show all','bn-woo-manager' ),
        'show_count'        => true,
        'value_field'       => 'slug'
        );
        $default_query = BN_Woo_Manager::$bn_woo_manager_ajax_product_edit_query_key;
    ?>
        <div id="bn_wrapper"><div id="bn_zones">
            <div class="bn_head_left">
                <a class="bn-switch" href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=shipping&section=bn_woo_manager_shipping_zones' );?>">
                    <img class="bn_logo" alt="BN Shipping Zones" width="250" height="125" src="<?php echo BN_Woo_Manager::$url . 'assets/img/bn_woo_manager_x.png';?>" data-src="<?php echo BN_Woo_Manager::$url . 'assets/img/bn_woo_manager_y.png';?>">
                </a>
                <br>
                <span>
                    <strong class="bn_darkred"><?php echo __( 'Switch to', 'bn-woo-manager' );?>&nbsp;&nbsp;<a href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=shipping&section=bn_woo_manager_shipping_zones' );?>"><?php echo __('BN Shipping Zones', 'bn-woo-manager' );?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="<?php echo admin_url('admin.php?page=wc-settings&tab=bn_woo_manager_settings' );?>"><?php echo __( 'BN Settings', 'bn-woo-manager' );?></a></strong>
                </span>
            </div>
            <div class="bn_head_right">
                <span class="bn_plugin_title"><img width="364" height="25" src="<?php echo BN_Woo_Manager::$url . 'assets/img/bn_woo_manager.png'; ?>">&nbsp;&nbsp;<span class="bn_darkred"><?php echo __( 'AJAX Product Edit', 'bn-woo-manager' );?></span></span>
                <br>
                <a href="http://www.gnu.org/licenses/gpl-3.0.html" title="License" target="_blank">License: GPLv3</a><br>
                <div class="bn_donate">
                    <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank"><input type="hidden" name="cmd" value="_s-xclick"><input type="hidden" name="hosted_button_id" value="HQ5X73WB7S5ZQ"><input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!"><img alt="" border="0" src="https://www.paypalobjects.com/de_DE/i/scr/pixel.gif" width="1" height="1"></form><?php echo __( 'If you like to use this plugin, <strong>please be so cool</strong> and support the development with a small donation, for example <strong>10 Euro</strong>.<br><strong>Many thanks.</strong>', 'bn-woo-manager' );?>
                </div>
            </div>
        </div><hr>
        <ul class="bn_query">
            <li><strong>QUERY</strong></li>
            <li>
                <select id="bn_query_key" name="bn_query_key">
                    <option value="product_data"<?php echo ( 'product_data' == $default_query ) ? ' selected' : ''; ?>><?php echo  __( 'Product', 'bn-woo-manager' ) . '-DATA';?></option>
                    <option value="_sku"<?php echo ( '_sku' == $default_query ) ? ' selected' : ''; ?>><?php echo  __( 'SKU', 'bn-woo-manager' );?></option>
                    <option value="id"<?php echo ( 'id' == $default_query ) ? ' selected' : ''; ?>>ID</option>
                    <option value="_stock"<?php echo ( '_stock' == $default_query ) ? ' selected' : ''; ?>><?php echo  __( 'Stock', 'bn-woo-manager' );?></option>
                    <option value="_price"<?php echo ( 'price' == $default_query ) ? ' selected' : ''; ?>><?php echo __( 'Price', 'bn-woo-manager' );?></option>
                </select>
            </li>
            <li>
                <select id="bn_query_compare" name="bn_query_compare">
                    <option value="LIKE" selected><?php echo  __( 'LIKE', 'bn-woo-manager' );?></option>
                    <option value="=">=</option>
                    <option value=">">></option>
                    <option value="<"><</option>
                </select>
            </li>
            <li>
                <input id="bn_query_search_string" placeholder="<?php echo  __( 'Search', 'bn-woo-manager' );?>" type="text" size="25" value="">&nbsp;&nbsp;<span title="Reset Query" id="bn_query_reset">X</span>
            </li>
            <li id="bn_refresh_x">
                <span title="Refresh" id="bn_refresh_all">&#8635;</span>
            </li>
            <li id="bn_update_x">
                <span class="bn_spacer"><div class="bn_ajax_loader_h"><div id="bn_circularG_h"><div id="bn_circularG_1_h" class="bn_circularG"></div><div id="bn_circularG_2_h" class="bn_circularG"></div><div id="bn_circularG_3_h" class="bn_circularG"></div><div id="bn_circularG_4_h" class="bn_circularG"></div><div id="bn_circularG_5_h" class="bn_circularG"></div><div id="bn_circularG_6_h" class="bn_circularG"></div><div id="bn_circularG_7_h" class="bn_circularG"></div><div id="bn_circularG_8_h" class="bn_circularG"></div></div></div></span>
            </li>
            <li>&nbsp;&nbsp;
            </li>
            <li><a href="#" class="bn_export">Export CSV</a></li>
            <li><a href="<?php echo BN_Woo_Manager::$url . 'log/bn-ajax-product-edit-log.php';?>" class="bn_logfile" target="_blank">LOG</a></li>
            <li><a href="#" class="bn_delete_log">X</a></li>
            <li><a href="#" class="bn_delete_cache">Cache</a></li>
        </ul><br>
        <ul class="bn_options">
            <li><strong><?php echo __( 'CATEGORY', 'bn-woo-manager' );?></strong><span title="All" id="bn_cat_all"><?php echo __(  'Show all', 'bn-woo-manager' );?></span><br><?php echo wp_dropdown_categories( $args1 );?></li>
            <li><strong>SORT</strong><br>
                <select id="bn_sort_by" name="bn_sort_by">
                    <option value="ID">ID</option>
                    <option value="title" selected><?php echo  __( 'Title', 'bn-woo-manager' );?></option>
                    <option value="_sku"><?php echo __( 'SKU', 'bn-woo-manager' );?></option>
                    <option value="_stock"><?php echo __( 'Stock', 'bn-woo-manager' );?></option>
                    <option value="_price"><?php echo __( 'Price', 'bn-woo-manager' );?></option>
                </select>
            </li>
            <li><strong>SPECIAL</strong><span title="Special Off" id="bn_special_off"><?php echo __( 'Off', 'bn-woo-manager' );?></span><br>
                <select id="bn_special" name="bn_special">
                    <option value="off" selected><?php echo __( 'Off', 'bn-woo-manager' );?></option>
                    <option value="bn1">BN-1 <?php echo __( 'products without shipping class', 'bn-woo-manager' );?></option>
                    <option value="bn2">BN-2 <?php echo __( 'products without manage stock', 'bn-woo-manager' );?></option>
                    <option value="bn3">BN-3 <?php echo __( 'products with managed stock-level < 10', 'bn-woo-manager' );?></option>
                    <option value="bn4">BN-4 <?php echo __( 'products with status out of stock', 'bn-woo-manager' );?></option>
                    <option value="bn5">BN-5 <?php echo __( 'products with price 0', 'bn-woo-manager' );?></option>
                    <option value="bn6">BN-6 <?php echo __( 'inactive products', 'bn-woo-manager' ) . ' (private)';?></option>
                    <option value="bn7">BN-7 <?php echo __( 'password-protected products', 'bn-woo-manager' );?></option>
                    <option value="bn8">BN-8 <?php echo __( 'Pending Reviews', 'bn-woo-manager' );?></option>
                    <option value="bn9">BN-9 <?php echo __( 'Drafts', 'bn-woo-manager' );?></option>
                    <option value="bn10">BN-10 <?php echo __( 'external products', 'bn-woo-manager' );?></option>
                </select>
            </li>
            <li><strong>FILTER</strong><span title="Reset Filter" id="bn_filter_reset">Reset</span><br><input id="bn_filter" size="8" type="text" value="" placeholder="IN"></li>
            <li><input id="bn_filter_or" size="8" type="text" value="" placeholder="<?php echo __( 'OR', 'bn-woo-manager' );?>" readonly></li>
            <li><input id="bn_filter_and" size="8" type="text" value="" placeholder="<?php echo __( 'AND', 'bn-woo-manager' );?>" readonly></li>
            <li><span title="Hide Parents" id="bn_hide_parent">[#P]</span><br><input id="bn_filter_not" size="8" type="text" value="" placeholder="<?php echo __( 'NOT', 'bn-woo-manager' );?>"></li>
            <li><strong>GROUP EDIT</strong><span title="Activate" id="bn_group_edit_activate"><?php echo __( 'Activate', 'bn-woo-manager' );?></span><span title="Toggle" id="bn_group_edit_toggle"><?php echo __( 'Select', 'bn-woo-manager' );?></span><br>
                <ul id="bn_group_edit">
                    <li>
                        <span id="bn_group_edit_action" class="bn_group_edit_do">OK</span>
                    </li>
                    <li>
                        <select id="bn_group_edit_active_fields" name="bn_group_edit_active_fields" disabled>
                            <option value="product_stock">ST <?php echo __( 'Stock', 'bn-woo-manager' );?></option>
                            <option value="regular_price" selected>RP <?php echo __( 'Regular Price', 'bn-woo-manager' );?></option>
                            <option value="sale_price">SP <?php echo __( 'Sale Price', 'bn-woo-manager' );?></option>
                        </select>
                    <li>
                    <li>
                        <select id="bn_group_edit_operation" name="bn_group_edit_operation" disabled>
                            <option value="+" selected>+ <?php echo __( 'Add', 'bn-woo-manager' );?></option>
                            <option value="-">- <?php echo __( 'Subtract', 'bn-woo-manager' );?></option>
                            <option value="*">* <?php echo __( 'Replace', 'bn-woo-manager' );?></option>
                        </select>
                    </li>
                    <li>
                        <input id="bn_group_edit_value" type="text" size="5" value="" readonly>
                    </li>
                    <li>
                        <select id="bn_group_edit_unit" name="bn_group_edit_unit" disabled>
                            <option value="%" selected>% <?php echo __( 'Percent','bn-woo-manager' );?></option>
                            <option value="$">$ <?php echo __( 'Value','bn-woo-manager' );?></option>
                        </select>
                    <li>
                </ul>
             </li>
        </ul>
        <!-- jQuery Pagination -->
        <div id="bn_page_navigation"></div>
        <!-- AJAX insert Product List -->
        <div id="bn_product_list"></div>
        </div>
    <?php
    }

    // AJAX Delete LOG
    public static function bn_ajax_delete_log() {
        // check the nonce
        check_ajax_referer( 'bn_woo_manager_ajax_nonce', 'nonce' );

        global $wpdb;
        if( isset( $_POST['bn_delete_log'] ) && current_user_can( 'manage-options' ) ) {
            $file = BN_Woo_Manager::$path . 'log/bn-ajax-product-edit-log.html';
            file_put_contents( $file, '', LOCK_EX );
        }
        wp_die();
    }

    // BN priceformat
    public static function bn_price_format( $s ) {
        if ( substr( $s, 0, 1 ) == '-' ) return '';
        // convert "," to "."
        $s = str_replace( ',', '.', $s );
        // remove everything except numbers and dot "."
        $s = preg_replace( '/[^0-9\.]/', '', $s );
        // remove all seperators from first part and keep the end
        $s = str_replace( '.', '', substr( $s, 0, -3 ) ) . substr( $s, -3 );
        // return
        return ( preg_match( '/[0-9]+/', $s ) ) ? $s : '';
    }

    // BN Get product attributes
    public static function bn_get_product_attributes( $product, $prod_id ) {
        // get attributes from DB or transient
        if ( false === ( $bn_product_attr = get_transient( 'bn_woo_man_xt_a_' . $prod_id ) ) ) {
            // get product attr
            $attributes = $product->get_attributes();
            $out='';
            if ( $attributes ) {
                foreach ( $attributes as $attribute ) {
                    if ( $attribute['is_taxonomy'] ) {
                        if ( $attribute['is_variation'] ) {
                            //continue;
                        }
                        $terms = wp_get_post_terms( $prod_id, $attribute['name'], 'all' );
                        $tax_object = get_taxonomy( $terms[0]->taxonomy );
                        if ( isset ($tax_object->labels->name) ) {
                            $tax_label = $tax_object->labels->name;
                            } elseif ( isset( $tax_object->label ) ) {
                            $tax_label = $tax_object->label;
                        }
                        //$out .= $tax_label . ': ';
                        foreach ( $terms as $term ) {
                            $out .= $term->name . ', ';
                        }
                        $out = substr($out,0,-2).'<br>';
                    } else {
                        $out .= $attribute['name'] . ': ';
                        $out .= $attribute['value'] . '<br />';
                    }
                }
            }
            $bn_product_attr = $out;
            set_transient( 'bn_woo_man_xt_a_' . $prod_id, $bn_product_attr, DAY_IN_SECONDS * 30 );
        }
        return $bn_product_attr;
    }

    // bn get product data
    public static function bn_get_product_data( $product, $prod_id, $attributes, $typ ) {
        global $wp_locale;
        $package_qty_plh = '';
        if ( $typ == 1 ) {
            $prod_id_parent = wp_get_post_parent_id( $prod_id );
            $package_qty = get_post_meta( $prod_id, '_bn_woo_manager_package_qty', true );
            $ro_parent = '';
            $pro_typ = 'V';
            $row_back = 'bn_back_variable';
            if ( $package_qty == '' ) {
                $package_qty_plh = get_post_meta( $prod_id_parent, '_bn_woo_manager_package_qty', true );
            }
            $pro_url = get_permalink( $prod_id_parent );
            $ed_url = admin_url( 'post.php?post=' . $prod_id_parent . '&action=edit' );
        } else {
            $prod_id_parent = $prod_id;
            $package_qty = $product->bn_woo_manager_package_qty;
            $pro_url = get_permalink( $prod_id );
            $ed_url = admin_url( 'post.php?post=' . $prod_id . '&action=edit' );
            if( $product->is_type( 'simple' ) ) {
                $ro_parent = '';
                $pro_typ = 'S';
                $row_back = 'bn_back_simple';
            } else if ( $product->is_type( 'variable' ) ) {
                $ro_parent = 'readonly';
                $pro_typ = 'P';
                $row_back = 'bn_back_parent';
            } else if ( $product->is_type( 'external') ) {
                $ro_parent = '';
                $pro_typ = 'E';
                $row_back = '';
            }
        }
        $poststatus = get_post_status( $prod_id );
        $password = ( post_password_required( $prod_id ) ) ? 'bn_password' : '';
        if ( get_post_meta( $prod_id, '_manage_stock', true ) == 'yes' ) $manage_stock = ''; else $manage_stock = 'readonly';
        $sku = get_post_meta( $prod_id, '_sku', true );
        if ( $sku == '' && $pro_typ == 'V' ) $sku_plh = $product->sku; else $sku_plh = '';
        $ship = $product->get_shipping_class();
        if ( $ship == '' ) {
            $ship = '_no_shipping_class';
            $ship2 = __( 'No shipping class', 'woocommerce' );
        } else
            $ship2 = str_replace( '-', ' ', $ship );
        $stock = get_post_meta( $prod_id, '_stock', true );
        $stock = ( $stock == '' ) ? $stock : wc_stock_amount( $stock );
        $stock_plh = '';
        if ( ( empty( $stock ) || $stock < 1 ) && $pro_typ == 'V' ) {
            if ( wc_get_product( $prod_id_parent )->managing_stock() )
                $stock_plh = $product->get_stock_quantity();
        }
        $stock_status = ( $product->is_in_stock() ) ? 'bn_instock' : 'bn_outofstock';
        $rprice = $product->regular_price;
        $rprice = ( empty( $rprice ) ) ? $rprice : number_format_i18n( floatval( $rprice ), 2 );
        $sprice = $product->sale_price;
        $sprice = ( empty( $sprice ) ) ? $sprice : number_format_i18n( floatval( $sprice ), 2 );
        $price = $product->get_price_html();
        $price2 = $product->price;
        $total_units = $unit_base = $unit = $unit_price = '';
        // unit price
        if ( BN_Woo_Manager::$bn_woo_manager_option_total_units && $pro_typ != 'E' && $pro_typ != 'P' ) {
            $total_units = $product->bn_woo_manager_total_units;
            $total_units =  ( empty( $total_units ) ) ? $total_units : number_format_i18n( floatval( $total_units ), 1 );
            $unit = $product->bn_woo_manager_unit;
            if ( $unit == '' ) $unit = '_none';
            $unit_base = $product->bn_woo_manager_base_units;
            $unit_price = BN_Woo_Manager_Unit_Price::bn_calculate_unit_price( $prod_id, 'shop' );
        }
        $prod_title = $product->get_title();
        $pro_img = $product->get_image();
        $mark_disabled = '';
        // get product attributes
        $bn_product_attr = ( $attributes == '***bn-woo-manager***' ) ? self::bn_get_product_attributes( $product, $prod_id ) : $attributes;
        // data for filter function
        $search_string = strtolower( implode( ' | ', array( $sku, $prod_title, $stock, $ship, $rprice, $package_qty, $sprice, trim(strip_tags( $price ) ), $prod_id, $manage_stock, $price2, '#'.$pro_typ, $ro_parent, $bn_product_attr, $poststatus, $ship2, $unit, $total_units, $stock_status ) ) );

        // return array
        return array( $pro_typ, $prod_title, $sku, $ship, $rprice, $package_qty, $sprice, $price, $prod_id, $manage_stock, $price2, $pro_img, $pro_url, $ed_url, $stock, $ro_parent, $row_back, $bn_product_attr, $poststatus, $search_string, $prod_id_parent, $unit_price, $unit, $unit_base, $total_units, $stock_status, $password, $package_qty_plh, $sku_plh, $stock_plh, $mark_disabled );
    }

    // WP Query product list
    public static function bn_ajax_product_list_query( $bn_count, $bn_paged, $bn_cat, $bn_sort, $bn_special, $bn_query_key, $bn_query_search_string, $bn_query_compare ) {
        $bn_query_search_string_s = $bn_query_search_string;
        $bn_query_search_string_p = '';
        if ( $bn_query_key == 'id' ) {
            $bn_query_search_string_s = '';
            $bn_query_search_string_p = $bn_query_search_string;
            $meta_query = '';
        } else if ( $bn_query_key != 'product_data' && $bn_query_search_string != '' ) {
            $bn_query_type = ( $bn_query_key == '_price' || $bn_query_key == '_stock' ) ? 'NUMERIC' : 'CHAR';
            $meta_query = array(
                    array(
                    'key'       => $bn_query_key,
                    'value'     => $bn_query_search_string,
                    'compare'   => $bn_query_compare,
                    'type'      => $bn_query_type
                    )
                );
            $bn_query_search_string_s = '';
        } else {
            $meta_query = '';
        }

        $bn_meta_key = '';
        if ( $bn_sort[0] == '_' ) {
            $bn_meta_key = $bn_sort;
            $bn_sort = ( $bn_sort == '_sku' || $bn_sort == 'title' ) ? 'meta_value' : 'meta_value_num';
        }
        $post_status = $post_status_v = array( 'publish', 'private' );
        $has_password = false;
        $product_type_term = array( 'simple', 'variable' );
        switch( $bn_special ) {
            case "bn6":
                $post_status_v = array( 'private' );
                break;
            case "bn7":
                $has_password = true;
                $post_status = array( 'publish', 'pending' );
                break;
            case "bn8":
                $has_password = null;
                $post_status = array( 'pending' );
                break;
            case "bn9":
                $has_password = null;
                $post_status = array( 'draft' );
                break;
            case "bn10":
                $has_password = null;
                $post_status = array( 'publish', 'draft', 'pending', 'private' );
                $product_type_term = array( 'external' );
                break;
            default:
        }

        // get Variations if query / match the query string
        if ( $bn_query_search_string != '' ) {
             $bn_pre_products_query1 = new WP_Query( array(
                'post_type'         =>  array( 'product' ),
                'posts_per_page'    =>  -1,
                'fields'            =>  'ids',
                'no_found_rows'     =>  true,
                'product_cat'       =>  $bn_cat,
                'post_status'       =>  $post_status,
                'has_password'      =>  $has_password,
                'tax_query'         =>  array(
                        array(
                            'taxonomy'  => 'product_type',
                            'field'     => 'slug',
                            'terms'     =>  $product_type_term,
                            'operator'  => 'IN'
                        )
                    )
                )
            );
            $bn_pre_products_query = $bn_pre_products_query1->posts;

            $bn_variations_query1 = new WP_Query( array(
                'post_type'         =>  array( 'product_variation' ),
                'posts_per_page'    =>  -1,
                'fields'            =>  'ids',
                'p'                 =>  $bn_query_search_string_p,
                'no_found_rows'     =>  true,
                's'                 =>  $bn_query_search_string_s,
                'post_status'       =>  $post_status,
                'meta_query'        =>  $meta_query,
                'has_password'      =>  $has_password
                )
            );
            $bn_variations_query = $bn_variations_query1->posts;

            $bn_variations_parent = array();
            foreach( $bn_variations_query as $bn_variations_query_id ) {
                $parent_pre_id =  wp_get_post_parent_id( $bn_variations_query_id );
                if ( in_array( $parent_pre_id, $bn_pre_products_query ) )
                    $bn_variatons_parent[] = $parent_pre_id;
            }
            $bn_variations_parent = array_unique( $bn_variations_parent );
            $bn_count = -1;
        }

        // get products DB or transient
        if ( $bn_query_search_string != '' || false === ( $bn_products = get_transient( 'bn_woo_man_xt_p_' .  hash( 'crc32', $bn_cat . '|' . $bn_count . '|' . $bn_paged . '|' . $bn_sort . '|' . $bn_meta_key . '|' . $bn_special ) ) ) ) {
            $bn_products1 = new WP_Query( array(
                'post_type'         =>  array( 'product' ),
                'posts_per_page'    =>  $bn_count,
                'paged'             =>  $bn_paged,
                'product_cat'       =>  $bn_cat,
                'fields'            =>  'ids',
                'p'                 =>  $bn_query_search_string_p,
                's'                 =>  $bn_query_search_string_s,
                'post_status'       =>  $post_status,
                'meta_query'        =>  $meta_query,
                'has_password'      =>  $has_password,
                'orderby'           =>  $bn_sort,
                'meta_key'          =>  $bn_meta_key,
                'order'             => 'ASC',
                'tax_query'         =>  array(
                        array(
                            'taxonomy'  => 'product_type',
                            'field'     => 'slug',
                            'terms'     =>  $product_type_term,
                            'operator'  => 'IN'
                        )
                    )
                )
            );
            $bn_products = $bn_products1->posts;
            array_push( $bn_products, $bn_products1->max_num_pages );
            if ( $bn_query_search_string == '' )
                set_transient( 'bn_woo_man_xt_p_' . hash( 'crc32', $bn_cat . '|' . $bn_count . '|' . $bn_paged . '|' . $bn_sort . '|' . $bn_meta_key . '|' . $bn_special ), $bn_products, DAY_IN_SECONDS * 30 );
        } // transient

        $bn_max_num_pages = array_pop( $bn_products );
        // add Variations Parents match the query string
        if ( $bn_query_search_string != '' ) {
            if ( !empty( $bn_variatons_parent ) ) $bn_products += $bn_variatons_parent;
            $bn_products = array_unique( $bn_products );
        }
        $bn_product_list = array();

        // loop products
        if ( $bn_sort == 'title' ) {
            $bn_sort = 'meta_value';
            $bn_meta_key = '_sku';
        }
        foreach( $bn_products as $key => $prod_id ):
            $product = wc_get_product( $prod_id );
            $last_parent_key = -1;
            // add products to array
            if ( $bn_special != 'bn6' || ( $bn_special == 'bn6' && get_post_status( $prod_id ) == 'private' ) ) {
                $bn_product_list[] = self::bn_get_product_data( $product, $prod_id, '***bn-woo-manager***', 0 );
                $last_parent_key = $key;
            }
            // variations
            if ( $product->is_type( 'variable' ) ) {
                // get variations from DB or transient
                if ( false === ( $bn_variations = get_transient( 'bn_woo_man_xt_v_' . $prod_id . hash( 'crc32', $prod_id . '|' . $bn_sort . '|' . $bn_meta_key . '|' . $post_status_v[0] ) ) ) ) {
                    $args_variable = array(
                        'post_parent'       => $prod_id,
                        'post_type'         => 'product_variation',
                        'fields'            => 'ids',
                        'post_status'       => $post_status_v,
                        'posts_per_page'    => -1,
                        'orderby'           => $bn_sort,
                        'meta_key'          => $bn_meta_key,
                        'order'             => 'ASC'
                        );
                    $bn_variations_ids = get_posts( $args_variable );
                    $bn_variations = array();
                    foreach ( $bn_variations_ids as $key => $variation_id ) {
                        // get variations attributes
                        $bn_product_attr = str_replace( ',' , '<br>' , wc_get_formatted_variation( wc_get_product( $variation_id )->get_variation_attributes(), true ) );
                        $bn_variations[$variation_id] = $bn_product_attr;
                    }
                    set_transient( 'bn_woo_man_xt_v_' . $prod_id . hash( 'crc32', $prod_id . '|' . $bn_sort . '|' . $bn_meta_key . '|' . $post_status_v[0] ), $bn_variations, DAY_IN_SECONDS * 30 );
                }
                // loop variations
                foreach ( $bn_variations as $variation_id => $variation_attributes ) {
                    $product = wc_get_product( $variation_id );
                    // add variations to array
                    $bn_product_list[] = self::bn_get_product_data( $product, $variation_id, $variation_attributes, 1 );
                }
            }
        endforeach;

        wp_reset_query();

        array_push( $bn_product_list, $bn_max_num_pages );

        return $bn_product_list;
    }

    // AJAX respond function Show Product List Part
    public static function bn_ajax_product_list_page() {
        // check the nonce
        check_ajax_referer( 'bn_woo_manager_ajax_nonce', 'nonce' );

        global $wpdb;
        // POST data
        $list_count                 = ( isset( $_POST['bn_count'] ) ) ? $_POST['bn_count'] : 20;
        $list_page                  = ( isset( $_POST['bn_page'] ) ) ? $_POST['bn_page'] : 1;
        $list_cat                   = ( isset( $_POST['bn_cat'] ) ) ? $_POST['bn_cat'] : 0;
        $list_sort                  = ( isset( $_POST['bn_sort'] ) ) ? $_POST['bn_sort'] : 'title';
        $list_special               = ( isset( $_POST['bn_special'] ) ) ? $_POST['bn_special'] : 'off';
        $list_query_key             = ( isset( $_POST['bn_query_key'] ) ) ? $_POST['bn_query_key'] : 'product_data';
        $list_query_search_string   = ( isset( $_POST['bn_query_search_string'] ) ) ? trim( $_POST['bn_query_search_string'] ) : '';
        $list_query_compare         = ( isset( $_POST['bn_query_compare'] ) ) ? trim( $_POST['bn_query_compare'] ) : 'LIKE';
        $expand                     = ( isset( $_POST['bn_expand'] ) ) ? $_POST['bn_expand'] : '+';
        if ( $list_count == '*' ) $list_count = BN_Woo_Manager::$bn_woo_manager_ajax_product_edit_products_per_page;
        $bn_expand_exp = ( $expand == '-' ) ? 'bn_expand_exp' : '';
        if ( $list_special != 'off' ) $list_count = '-1';

        if ( get_option( 'woocommerce_calc_taxes' ) === 'yes' ) {
            $tax_shop = '<br><span class="bn_prod_edit_tax">' . __( get_option( 'woocommerce_tax_display_shop' ) . '. VAT', 'bn-woo-manager' ) . '</span>';
            $tax_prices = ( get_option( 'woocommerce_prices_include_tax' ) === 'no' ) ? __( 'excl. VAT', 'bn-woo-manager' ) : __( 'incl. VAT', 'bn-woo-manager' );
            $tax_prices = '<br><span class="bn_prod_edit_tax">' . $tax_prices . '</span>';
        } else $tax_shop = $tax_prices = '';
        // product list head
        $product_list = '
            <form id="bn_form" autocomplete="off">
                <table cellspacing="0" cellpadding="0" class="bn_product_list">
                    <tr class="bn_head">
                        <th colspan="2" id="bn_expand" class="' . $bn_expand_exp . '">' . $expand . '</th>
                        <th colspan="3">'. __( 'Product', 'bn-woo-manager' ) . '<br><small>'. __( 'Published', 'bn-woo-manager' ) . ' | Shop-Link | Edit-Link</small></th>
                        <th>' . __( 'SKU', 'bn-woo-manager' ) . '<br><small>unique</small></th>';
        if ( $list_special != 'bn10' ) {
            $product_list .= '
                        <th><a class="bn_shipping" href="' . admin_url( "admin.php?page=wc-settings&tab=shipping&section=classes" ) . '" target="_blank">' . __( 'Shipping<br>Class', 'bn-woo-manager' ) . '</a></th>';
        }
        if ( BN_Woo_Manager::$bn_woo_manager_option_package_qty && $list_special != 'bn10' ) {
            $product_list .= '
                        <th class="bn_shipping">'. __( 'QTY<br>per Class', 'bn-woo-manager' ) . '</th>';
        }
        if ( $list_special != 'bn10' ) {
            $product_list .= '
                        <th colspan="2" class="bn_stock">' . __( 'Stock', 'bn-woo-manager' ) . '<br><small style="font-weight:normal;"><nobr>Manage | Value | Stat.</nobr></small></th>';
        }
            $product_list .= '
                        <th class="bn_prices">' . __( 'Reg. Price', 'bn-woo-manager' ) . $tax_prices . '</th>
                        <th class="bn_prices">' . __( 'Sale Price', 'bn-woo-manager' ) . $tax_prices . '</th>';
        if ( BN_Woo_Manager::$bn_woo_manager_option_total_units && $list_special != 'bn10' ) {
            $product_list .= '
                        <th class="bn_prices"><nobr>Shop ' . __( 'Price', 'bn-woo-manager' ) . '</nobr>' . $tax_shop . '</th>
                        <th class="bn_unit_price">' . __( 'Units', 'bn-woo-manager' ) . '</th>
                        <th><a class="bn_unit_price" href="' . admin_url( "edit-tags.php?taxonomy=bn_woo_manager_units&post_type=product" ) . '" target="_blank">' . __( 'Unit', 'bn-woo-manager' ) . '</a></th>
                        <th class="bn_unit_price">' . __( 'Unit<br>Base', 'bn-woo-manager' ) . '</th>';
        } else {
             $product_list .= '
                        <th class="bn_prices"><nobr>Shop ' . __( 'Price', 'bn-woo-manager' ) . '</nobr>' . $tax_shop . '</th>';
        }
        $product_list .= '
                        <th class="bn_center"><ul id="bn_undo_all"><li>&#8630;</li></ul></th>
                        <th class="bn_search_string"></th>
                    </tr>';

        $anz = $si = $ex = $pa = $va = $check1 = $check2 = 0;
        $shipping_class = get_terms( 'product_shipping_class', array(
                'hide_empty' => false,
                ) );
        $unit_x = get_terms( 'bn_woo_manager_units', array(
                'hide_empty' => false,
            ) );
        // query product list
        $products_array = self::bn_ajax_product_list_query( $list_count, $list_page, $list_cat, $list_sort, $list_special, $list_query_key, $list_query_search_string, $list_query_compare );
        $bn_max_num_pages = array_pop( $products_array );
        // loop products array
        foreach ( $products_array as $key => $value ) {
            switch( $list_special ) {
                case "bn1":
                    if ( $value[3] != '_no_shipping_class' ) continue 2;
                    break;
                case "bn2":
                    if ( $value[9] == '' ) continue 2;
                    break;
                case "bn3":
                    if ( $value[14] > 9 || $value[9] != '' ) continue 2;
                    break;
                case "bn4":
                    if ( $value[25] != 'bn_outofstock' ) continue 2;
                    break;
                case "bn5":
                    if ( $value[10] > 0 ) continue 2;
                    break;
                default:
            }
            switch ( $value[0] ) {
                case 'S':
                    $si++;
                    break;
                case 'E':
                    $ex++;
                    break;
                case 'P':
                    $pa++;
                    break;
                case 'V':
                    $va++;
                    break;
                default:
            }
            // shipping class select
            $class_select = '<select class="product_shipping_class bn_list_shipping_class"  name="_shipping_class" data-bn_old="' . $value[3] . '"><option value="_no_shipping_class">' . __( 'No shipping class', 'woocommerce' ) . '</option>';
            foreach ( $shipping_class as $key => $valuex ) {
                if ( esc_attr( $valuex->slug ) == $value[3] ) $selx = 'selected'; else $selx = '';
                    $class_select .= '<option value="' . esc_attr( $valuex->slug ) . '" ' . $selx . '>' . $valuex->name . '</option>';
            }
            $class_select .= '</select>';
            if ( BN_Woo_Manager::$bn_woo_manager_option_total_units && $value[0] != 'E' ) {
                // unit select
                if ( $value[15] == 'readonly' ) $disabled = 'disabled'; else $disabled = '';
                $unit_select = '<select class="bn_woo_manager_unit bn_list_unit" name="_bn_woo_manager_unit" data-bn_old="' . $value[22] . '" ' . $disabled . '><option value="_none">' . __( 'None', 'woocommerce' ) . '</option>';
                foreach ( $unit_x as $key => $valuey ) {
                    if ( esc_attr( $valuey->slug ) == $value[22] ) $sely = 'selected'; else $sely = '';
                        $unit_select .= '<option value="' . esc_attr( $valuey->slug ) . '" ' . $sely . '>' . $valuey->name . '</option>';
                }
                $unit_select .= '</select>';
            }
            // checkbox product status
            if ( $value[18] == 'publish' ) {
                $checked = 'checked';
                $bn_active = '';
                } else {
                $checked = '';
                $bn_active = 'bn_prod_active';
            }
            // checkbox stock manage
            if ( $value[9] == '' ) {
                $checked_stock = 'checked';
                $stock_manage = 'yes';
                } else {
                $checked_stock = '';
                $stock_manage = 'no';
            }
            // add parent product css classes
            if ( $value[0] == 'P' ) {
                $bn_parent = $expand;
                $bn_parent_class = 'bn_parent';
                $bn_parent_class_2 = 'bn_parent_2';
                } else {
                $bn_parent =  $bn_parent_class = $bn_parent_class_2 = '';
            }
            // hide / show variations
            if ( $value[0] == 'V' && $expand == '-' ) $bn_variation = 'bn_variable';
                else if ( $value[0] == 'V' ) $bn_variation = 'bn_variable bn_variation';
                else $bn_variation = '';
            // product list (hidden fields for CSV Export)
            $product_list .= '
                <tr id="' . $value[8] .'" class="adata ' . $value[16] . ' ' . $bn_variation . ' ' . $value[30] . '" data-bn_parent_id="' . $value[0] . $value[20] . '">
                    <td class="' . $bn_parent_class . ' ' . $value[16] . '">' . $bn_parent . '</td>
                    <td class="bn_pp_id ' . $bn_parent_class_2 . ' ' . $value[16] . '">#' . $value[0] . '<br>' . $value[8] . ' <input type="hidden" value="' . $value[8] . '"><input type="hidden" value="' . $value[20] . '"></td>
                    <td class="bn_center ' . $bn_active . '">
                        <input class="post_status bn_checkbox_active ' . $value[26] . '" type="checkbox" value="' . $value[18] . '" data-bn_old="' .$value[18] . '" ' . $checked . '>
                    </td>
                    <td class="' . $value[26] . '">
                        <div class="bn_img">
                            <a tabindex="-1" href="' . $value[12] . '" target="_blank">' . $value[11] . '</a>
                        </div>
                    </td>
                    <td class="bn_title bn_list_title ' . $value[26] . '">
                        <div><a tabindex="-1" href="' . $value[13] . '" target="_blank">' . $value[1] . '</a></div><div class="bn_product_attr"><small>' . $value[17] . '</small><input type="hidden" value="' . $value[1] . '"></div></td>
                    <td>
                        <input class="sku" type="text" size="14" placeholder="' . $value[28] . '" value="' . $value[2] . '" data-bn_old="' . $value[2] . '">
                    </td>';
            if ( $value[0] != 'E' ) {
                $product_list .= '
                    <td class="bn_shipping_cl">' . $class_select . '</td>';
            }
            if ( BN_Woo_Manager::$bn_woo_manager_option_package_qty && $value[0] != 'E' ) {
                $product_list .= '
                    <td>
                        <input class="bn_woo_manager_package_qty bn_list_package numbersOnly" type="number" placeholder="' . $value[27] . '" value="' . $value[5] . '" data-bn_old="' . $value[5] . '">
                    </td>';
            }
            if ( $value[0] != 'E' ) {
            $product_list .= '
                    <td class="bn_stock_man">
                        <input class="manage_stock bn_checkbox_active" type="checkbox" value="' . $stock_manage . '" data-bn_old="' . $stock_manage . '" ' . $checked_stock . '>
                    </td>
                    <td class="bn_stock_st">
                        <input class="product_stock bn_list_stock numbersOnly" placeholder="' . $value[29] . '" type="number" value="' . $value[14] . '" data-bn_old="' . $value[14] . '" ' . $value[9] . '><div class="' . $value[25] . '">&nbsp;</div><input type="hidden" value="' . $value[25] . '">
                    </td>';
            }
            $product_list .= '
                    <td>
                        <input class="regular_price bn_list_regular_price" type="text" size="6" value="' . $value[4] . '" data-bn_old="' . $value[4] . '" ' . $value[15] . '>
                    </td>
                    <td>
                        <input class="sale_price bn_list_sale_price" type="text" size="6" value="' . $value[6] . '" data-bn_old="' . $value[6] . '" ' . $value[15] . '>
                    </td>';
            if ( BN_Woo_Manager::$bn_woo_manager_option_total_units && $value[0] != 'E' ) {
                $product_list .= '
                    <td class="bn_total_price">
                        <div class="price_x bn_list_price">' . $value[7] . '<br><span class="bn_unit_price">' . $value[21] . '</span></div>
                    </td>
                    <td>
                        <input class="bn_woo_manager_total_units bn_list_total_units" type="text" value="' . $value[24] . '" data-bn_old="' . $value[24] . '" ' . $value[15] . '>
                    </td>
                    <td class="bn_unit_dark">' . $unit_select . '</td>
                    <td>
                        <input class="bn_woo_manager_base_units bn_list_unit_base numbersOnly" type="number" value="' . $value[23] . '" data-bn_old="' . $value[23] . '" ' . $value[15] . '>
                    </td>';
            } else {
                $product_list .= '
                    <td class="bn_total_price">
                        <div class="price_x bn_list_price">' . $value[7] . '</div>
                    </td>';
            }
            $product_list .= '
                    <td class="bn_undo_single">
                    <div class="bn_ajax_loader">
                            <div id="bn_circularG">
                                <div id="bn_circularG_1" class="bn_circularG"></div>
                                <div id="bn_circularG_2" class="bn_circularG"></div>
                                <div id="bn_circularG_3" class="bn_circularG"></div>
                                <div id="bn_circularG_4" class="bn_circularG"></div>
                                <div id="bn_circularG_5" class="bn_circularG"></div>
                                <div id="bn_circularG_6" class="bn_circularG"></div>
                                <div id="bn_circularG_7" class="bn_circularG"></div>
                                <div id="bn_circularG_8" class="bn_circularG"></div>
                            </div>
                        </div>
                        <ul class="bn_options_undo"><li>&#8630;</li></ul>
                    </td>
                    <td class="bn_search_string">
                        ' . $value[19] . '<input type="hidden" value="' . $value[10] . '">
                    </td>
                </tr>';
            $check1 += $value[14];
            $check2 += ( $value[0] == 'P' ) ? 0 : self::bn_price_format( $value[10] );
        } // end loop products array
        $product_list .= '</table></form>';
        $anz = $si + $pa + $ex;
        if ( $anz == 0 ) {
            if ( $va == 0 )
                $product_list .= '<br><strong>' . __('Nothing found...','bn-woo-manager') . '</strong>';
                else
                $product_list .= '<br><strong>' . $va . ' ' . _n('Variation', 'Variations', $va, 'bn-woo-manager') . '</strong><br>Checksum: | ' . $va . ' | ' . $check1 . ' | ' . $check2 . ' | ';
            $list_page = '1';
            $bn_max_num_pages = '1';
            $list_count = BN_Woo_Manager::$bn_woo_manager_ajax_product_edit_products_per_page;
        } else {
            $product_list .= '<br><strong>' . $anz . ' ' . _n( 'Product', 'Products', $anz, 'bn-woo-manager' ) . '&nbsp; ( ' . ( $anz + $va - $pa ) . ' Shop-' . _n( 'Product', 'Products', ( $anz + $va - $pa ), 'bn-woo-manager' ) . ' ) ' . ' | ' . $si . ' ' . __( 'Simple', 'bn-woo-manager' ) . ' | ' . $ex . ' ' . __( 'External', 'bn-woo-manager' ) . ' | ' . $pa . ' ' . __( 'Variable', 'bn-woo-manager' ) . '&nbsp; ( ' . $va . ' ' . _n( 'Variation', 'Variations', $va, 'bn-woo-manager' ) .  ' ) ' . '</strong><br>Checksum: | ' . ( $anz + $va ) . ' | ' . $check1 . ' | ' . $check2 . ' | ';
        }
        // data for jQuery
        $product_list .= '<div id="bn_datastring">' . $list_page . '|' . $bn_max_num_pages . '|' . $list_count . '</div>';

        wp_die( $product_list );
    }

    // AJAX respond function Product List update fields
    public static function bn_product_data_update() {
        // check the nonce
        check_ajax_referer( 'bn_woo_manager_ajax_nonce', 'nonce' );

        global $wpdb;
        if ( isset( $_POST['bn_product_id'] ) && isset( $_POST['bn_field_name'] ) && isset( $_POST['bn_field'] ) ) {
            $product_id = $_POST['bn_product_id'];
            $field_name = $_POST['bn_field_name'];
            $field = trim( $_POST['bn_field'] );
            $product_type = $_POST['bn_product_type'];
            // use field_name
            $resp0 = $resp1 = $old_val = $old_val_stock = '';
            $inh = ' ';
            switch( $field_name ) {
                case "sale_price":
                case "regular_price":
                    $old_val = wc_get_product( $product_id )->$field_name;
                    $old_val = ( empty( $old_val ) ) ? $old_val : number_format_i18n( floatval( $old_val ), 2 );
                    update_post_meta( $product_id, '_' . $field_name, self::bn_price_format( $field ) );
                    $saleprice = get_post_meta( $product_id, '_sale_price', true );
                    $regularprice = get_post_meta( $product_id, '_regular_price', true );
                    if ( ( $saleprice >= 0 && $saleprice != '' ) && ( $saleprice < $regularprice ) )
                        update_post_meta( $product_id, '_price', $saleprice );
                        else
                        update_post_meta( $product_id, '_price', $regularprice );
                    // Sync Variable Product Min/Max Variation Price
                    if ( $product_type == '#V' ) {
                        WC_Product_Variable::sync( wp_get_post_parent_id( $product_id ) );
                    }
                    $resp0 = wc_get_product( $product_id )->$field_name;
                    $resp0 = ( empty( $resp0 ) ) ? $resp0 : number_format_i18n( floatval( $resp0 ), 2 );
                    $resp1 = wc_get_product( $product_id )->get_price_html();
                    update_post_meta( $product_id, '_bn_woo_manager_unit_price_html', '' );
                    break;
                case "product_stock":
                    $old_val = wc_get_product( $product_id )->get_stock_quantity();
                    if ( get_post_meta( $product_id, '_manage_stock', true ) == 'yes' ) {
                        $field = ( $field == '' ) ? 0 : $field;
                        wc_update_product_stock( $product_id, wc_stock_amount( $field ) );
                        if ( $product_type != '#P' ) {
                            if ( $field > 0 )
                                wc_update_product_stock_status( $product_id, 'instock' );
                                else
                                wc_update_product_stock_status( $product_id, 'outofstock' );
                        }
                    } else {
                        update_post_meta( $product_id, '_stock', '' );
                        if ( $product_type == '#V' && wc_get_product( wp_get_post_parent_id( $product_id ) )->managing_stock() ) {
                            $resp1 = wc_get_product( wp_get_post_parent_id( $product_id ) )->get_stock_quantity();
                            $inh = '*';
                        }
                    }
                    // Sync Variable Product Stock Status
                    if ( $product_type == '#V' ) {
                        WC_Product_Variable::sync_stock_status( wc_get_product( wp_get_post_parent_id( $product_id ) ) );
                        wc_get_product( wp_get_post_parent_id( $product_id ) )->check_stock_status();
                    }
                    $resp0 = get_post_meta( $product_id, '_stock', true );
                    $resp0 = ( $resp0 == '' ) ? $resp0 : wc_stock_amount( $resp0 );
                    break;
                case "product_shipping_class":
                    $old_val = wc_get_product( $product_id )->get_shipping_class();
                    $shipping_class = ( '_no_shipping_class' == $field ) ? '' : wc_clean( $field );
                    wp_set_object_terms( $product_id,  $shipping_class, $field_name );
                    $shipr =  wc_get_product( $product_id )->get_shipping_class();
                    if ( $shipr == '' ) {
                        $shipr = '_no_shipping_class';
                    }
                    $resp0 = $shipr;
                    WC_Cache_Helper::get_transient_version( 'shipping', true );
                    break;
                case "bn_woo_manager_package_qty":
                    $old_val = get_post_meta( $product_id, '_' . $field_name, true );
                    $field = ( $field < 1 ) ? '' : $field;
                    update_post_meta( $product_id, '_' . $field_name, $field );
                    $resp0 = get_post_meta( $product_id, '_' . $field_name, true );
                    if ( $resp0 == '' && $product_type == '#V' ) {
                        $resp1 = get_post_meta( wp_get_post_parent_id( $product_id ), '_' . $field_name, true );
                        $inh = '*';
                    }
                    WC_Cache_Helper::get_transient_version( 'shipping', true );
                    break;
                case "sku":
                    $old_val = get_post_meta( $product_id, '_' . $field_name, true );
                    if ( $field == '' || wc_product_has_unique_sku( $product_id, $field ) )
                        update_post_meta( $product_id, '_' . $field_name, wc_clean( stripslashes( $field ) ) );
                    $resp0 = get_post_meta( $product_id, '_' . $field_name, true );
                    if ( $resp0 == '' && $product_type == '#V' ) {
                        $resp1 = get_post_meta( wp_get_post_parent_id( $product_id ), '_' . $field_name, true );
                        $inh = '*';
                    }
                    break;
                case "post_status":
                    $old_val = get_post_status( $product_id );
                    if ( $field == '*' ) $field = 'publish';
                    wp_update_post( array( 'ID' => $product_id, $field_name => $field ) );
                    $resp0 = get_post_status( $product_id );
                    clean_post_cache( $product_id );
                    wp_transition_post_status( $resp0, $old_val, wc_get_product( $product_id ) );
                    if ( $product_type == '#V' )
                        self::bn_delete_transients( wp_get_post_parent_id( $product_id ) );
                        else
                        self::bn_delete_transients( $product_id );
                    break;
                case "manage_stock":
                    $old_val = get_post_meta( $product_id, '_' . $field_name, true );
                    if ( $field == 'no' ) {
                        $field = 'yes';
                        update_post_meta( $product_id, '_' . $field_name, $field );
                        if ( $product_type == '#V' ) {
                            update_post_meta( $product_id, '_stock', '0' );
                            update_post_meta( $product_id, '_stock_status', 'outofstock' );
                        } else {
                            wc_update_product_stock( $product_id, '0' );
                            if ( $product_type != '#P' ) {
                                wc_update_product_stock_status( $product_id, 'outofstock' );
                            }
                        }
                    } else {
                        $field = 'no';
                        $old_val_stock = wc_get_product( $product_id )->get_stock_quantity();
                        update_post_meta( $product_id, '_stock', '' );
                        if ( $product_type == '#V' && wc_get_product( wp_get_post_parent_id( $product_id ) )->managing_stock() && wc_get_product( wp_get_post_parent_id( $product_id ) )->get_stock_quantity() > 0 ) {
                            update_post_meta( $product_id, '_stock_status', 'instock' );
                        }
                        update_post_meta( $product_id, '_' . $field_name, $field );
                    }
                    // Sync Variable Product Stock Status
                    if ( $product_type == '#V' ) {
                        WC_Product_Variable::sync_stock_status( wc_get_product( wp_get_post_parent_id( $product_id ) ) );
                        wc_get_product( wp_get_post_parent_id( $product_id ) )->check_stock_status();
                    }
                    $resp0 = get_post_meta( $product_id, '_' . $field_name, true );
                    break;
                case "bn_woo_manager_unit":
                case "bn_woo_manager_base_units":
                    $old_val = get_post_meta( $product_id, '_' . $field_name, true );
                    if ( $field_name == 'bn_woo_manager_base_units' ) $field = ( $field < 1 ) ? '' : $field;
                    update_post_meta( $product_id, '_' . $field_name, $field );
                    $resp0 = get_post_meta( $product_id, '_' . $field_name, true );
                    $resp1 = wc_get_product( $product_id )->get_price_html();
                    update_post_meta( $product_id, '_bn_woo_manager_unit_price_html', '' );
                    break;
                case "bn_woo_manager_total_units":
                    $old_val = get_post_meta( $product_id, '_' . $field_name, true );
                    $field = ( $field < 1 ) ? '' : $field;
                    update_post_meta( $product_id, '_' . $field_name, self::bn_price_format( $field ) );
                    $resp0 = wc_get_product( $product_id )->$field_name;
                    $resp0 = ( empty( $resp0 ) ) ? $resp0 : number_format_i18n( floatval( $resp0 ), 1 );
                    $resp1 = wc_get_product( $product_id )->get_price_html();
                    update_post_meta( $product_id, '_bn_woo_manager_unit_price_html', '' );
                    break;
                default:
                    wp_die();
            }

            // Clear transients
            if ( $product_type != '#V' )
                wc_delete_product_transients( $product_id );
                else
                wc_delete_product_transients( wp_get_post_parent_id( $product_id ) );

            $new_val = ( $inh == '*' ) ? $resp1 : $resp0;
            if ( $new_val != $old_val ) {
                // write log to file
                global $blog_id;
                $file = BN_Woo_Manager::$path . 'log/bn-ajax-product-edit-log.html';
                file_put_contents( $file, '<pre>' . date_i18n( 'd.m.Y' ) . '-' . date_i18n( 'H:i:s' ) . ' ' . sprintf( '[USER%5s]', wp_get_current_user()->ID ) . ' ' . sprintf( '[SITE%5s]', $blog_id ) . ': ' . $product_type . ' ' . sprintf( '[ID%10s]', $product_id ) . ' ' . sprintf( '[SKU%25s]', wc_get_product( $product_id )->sku ) . ' ' . sprintf( '[FIELD%30s]', $field_name ) . ' ' . sprintf( '[OLD%25s]', $old_val ) . ' ' . sprintf( '[NEW ' . $inh . '%25s]', $new_val ) . '</pre>' . PHP_EOL, FILE_APPEND | LOCK_EX );
            }
            if ( $old_val_stock != '' )
                file_put_contents( $file, '<pre>' . date_i18n( 'd.m.Y' ) . '-' . date_i18n( 'H:i:s' ) . ' ' . sprintf( '[USER%5s]', wp_get_current_user()->ID ) . ' ' . sprintf( '[SITE%5s]', $blog_id ) . ': ' . $product_type . ' ' . sprintf( '[ID%10s]', $product_id ) . ' ' . sprintf( '[SKU%25s]', wc_get_product( $product_id )->sku ) . ' ' . sprintf( '[FIELD%30s]', 'product_stock' ) . ' ' . sprintf( '[OLD%25s]', $old_val_stock ) . ' ' . sprintf( '[NEW  %25s]', '' ) . '</pre>' . PHP_EOL, FILE_APPEND | LOCK_EX );

            // response
            wp_send_json( array( 'resp0' => $resp0, 'resp1' => $resp1 ) );
        } else {
            wp_die();
        }
    }

    // AJAX respond function Get Price HTML
    public static function bn_product_data_get_price_html() {
        // check the nonce
        check_ajax_referer( 'bn_woo_manager_ajax_nonce', 'nonce' );

        global $wpdb;
        if ( isset( $_POST['bn_price_product_id'] ) && isset( $_POST['bn_price_product_type'] ) ) {
            $price_product_id = $_POST['bn_price_product_id'];
            $price_product_type = $_POST['bn_price_product_type'];
            $resp_price = wc_get_product( $price_product_id )->get_price_html();
            // Unit price caculation by Option Total Units
            if ( BN_Woo_Manager::$bn_woo_manager_option_total_units && $price_product_type != '#P' ) {
                // get unit price
                $resp_price .= '<br><span class="bn_unit_price">';
                $resp_price .= BN_Woo_Manager_Unit_Price::bn_calculate_unit_price( $price_product_id, 'shop' );
                $resp_price .= '</span>';
            }
            // response
            wp_die( $resp_price );
        } else {
            wp_die();
        }
    }

    // AJAX respond function Get Shipping Class
    public static function bn_product_data_get_shipping_class() {
        // check the nonce
        check_ajax_referer( 'bn_woo_manager_ajax_nonce', 'nonce' );

        global $wpdb;
        if ( isset( $_POST['bn_ship_product_id'] ) &&  isset( $_POST['bn_old_ship_class'] ) ) {
            $ship_product_id = $_POST['bn_ship_product_id'];
            $old_ship = $_POST['bn_old_ship_class'];
            $ship_product = wc_get_product( $ship_product_id );
            $resp_ship = $ship_product->get_shipping_class();
            if ( $resp_ship == '' ) $resp_ship = '_no_shipping_class';

            if ( $resp_ship != $old_ship ) {
                // write log to file
                global $blog_id;
                $file = BN_Woo_Manager::$path . 'log/bn-ajax-product-edit-log.html';
                file_put_contents( $file, '<pre>' . date_i18n( 'd.m.Y' ) . '-' . date_i18n( 'H:i:s' ) . ' ' . sprintf( '[USER%5s]', wp_get_current_user()->ID ) . ' ' . sprintf( '[SITE%5s]', $blog_id ) . ': #V ' . sprintf( '[ID%10s]',  $ship_product_id ) . ' ' . sprintf( '[SKU%25s]', $ship_product->sku ) . ' ' . sprintf( '[FIELD%30s]', 'product_shipping_class' ) . ' ' . sprintf( '[OLD%25s]', $old_ship ) . ' ' . sprintf( '[NEW *%25s]', $resp_ship ) . '</pre>' . PHP_EOL, FILE_APPEND | LOCK_EX );
            }

            // response
            wp_die( $resp_ship );
        } else {
            wp_die();
        }
    }

    // hook product delete & update - transients cache
    public static function bn_delete_transients( $post_id ) {
        global $wpdb;
        $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE ('%_transient_bn_woo_man_xt_p_%')" );
        $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE ('%_transient_timeout_bn_woo_man_xt_p_%')" );
        // per post_id
        $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE ('%_transient_bn_woo_man_xt_a_$post_id%')" );
        $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE ('%_transient_timeout_bn_woo_man_xt_a_$post_id%')" );
        $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE ('%_transient_bn_woo_man_xt_v_$post_id%')" );
        $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE ('%_transient_timeout_bn_woo_man_xt_v_$post_id%')" );
    }

    // Hook attribute updates - transients cache
    public static function bn_delete_attribute_transients( $term_id, $tt_id, $taxonomy ) {
        global $wpdb;
        $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE ('%_transient_bn_woo_man_xt_a_%')" );
        $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE ('%_transient_timeout_bn_woo_man_xt_a_%')" );
        $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE ('%_transient_bn_woo_man_xt_v_%')" );
        $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE ('%_transient_timeout_bn_woo_man_xt_v_%')" );
    }

    // AJAX Delete Cache
    public static function bn_delete_cache() {
        // check the nonce
        check_ajax_referer( 'bn_woo_manager_ajax_nonce', 'nonce' );
        global $wpdb;
        if( isset( $_POST['bn_delete_cache'] ) ) {
            $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE ('%_transient_bn_woo_man_xt_%')" );
            $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE ('%_transient_timeout_bn_woo_man_xt_%')" );
        }
        wp_die();
    }

}

