<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * BN Woo Manager Settings
 *
 * @class       BN_Woo_Manager_Settings
 * @version     1.1
 * @author      BN-KareM
 */
class BN_Woo_Manager_Settings {
    /**
     * Bootstraps the class and hooks required actions & filters.
     *
     */
    public static function init() {
        add_filter( 'woocommerce_settings_tabs_array', __CLASS__ . '::bn_add_settings_tab', 50 );
        add_action( 'woocommerce_settings_tabs_bn_woo_manager_settings', __CLASS__ . '::bn_settings_tab' );
        add_action( 'woocommerce_update_options_bn_woo_manager_settings', __CLASS__ . '::bn_update_settings' );
        //add custom type
        add_action( 'woocommerce_admin_field_custom_type', __CLASS__ . '::bn_output_custom_type', 10, 1 );

        // Option BN Woo Manager Package QTY
        if ( BN_Woo_Manager::$bn_woo_manager_option_package_qty ) {
            // Add Package-QTY field to product
            add_action( 'woocommerce_product_options_shipping', __CLASS__ . '::bn_product_add_package_qty_field' );
            // Save Package-QTY Field product
            add_action( 'woocommerce_process_product_meta', __CLASS__ . '::bn_product_package_qty_save' );
            // Add Package-QTY field to variable product
            add_action( 'woocommerce_product_after_variable_attributes', __CLASS__ . '::bn_add_package_qty_to_variations_metabox', 10, 3 );
            // Save Package-QTY Field variable product
            add_action( 'woocommerce_save_product_variation', __CLASS__ . '::bn_save_package_qty_at_product_variation', 20, 2 );
        }
    }

    public static function bn_output_custom_type( $value ) {
    // output the custom type
        echo $value['desc'];
    }

    /**
     * Add a new settings tab to the WooCommerce settings tabs array
     *
     * @param array $settings_tabs Array of WooCommerce setting tabs
     * @return array $settings_tabs Array of WooCommerce setting tabs
     */
    public static function bn_add_settings_tab( $settings_tabs ) {
        $settings_tabs['bn_woo_manager_settings'] = 'BN Woo Manager';
        return $settings_tabs;
    }

    /**
     * Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function
     *
     * @uses woocommerce_admin_fields()
     * @uses self::bn_get_settings()
     */
    public static function bn_settings_tab() {
        wp_enqueue_script( 'bn_woo_manager_script' );
        woocommerce_admin_fields( self::bn_get_settings() );
    }

    /**
     * Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function
     *
     * @uses woocommerce_update_options()
     * @uses self::bn_get_settings()
     */
    public static function bn_update_settings() {
        woocommerce_update_options( self::bn_get_settings() );
        WC_Cache_Helper::get_transient_version( 'shipping', true );
    }

    /**
     * Get all the settings for this plugin for @see woocommerce_admin_fields() function
     *
     * @return array Array of settings for @see woocommerce_admin_fields() function
     */
    public static function bn_get_settings() {
        $options_array = get_option( 'woocommerce_bn_woo_manager_shipping_zones_settings' );
        $bn_woo_manager_shipping_zones = $options_array['enabled'] == 'yes';
        BN_Woo_Manager::$bn_woo_manager_option_total_units = ( get_option( 'bn_woo_manager_option_total_units' ) == 'yes' );
        BN_Woo_Manager::$bn_woo_manager_option_add_tax_info = ( get_option( 'bn_woo_manager_option_add_tax_info' ) == 'yes' );

        if ( ! $bn_woo_manager_shipping_zones ) {
            $option_package_qty = array( 'disabled' => true );
            $bn_shipping_zones_status = '<h3><strong>Status:</strong>&nbsp;&nbsp;<span class="bn_darkred">' . __( 'Inactive', 'bn-woo-manager' ) . '</span>&nbsp;&nbsp;<small><a href="'. admin_url( "admin.php?page=wc-settings&tab=shipping&section=bn_woo_manager_shipping_zones" ).'">' . __( 'Please edit here', 'bn-woo-manager' ) . '</a></small></h3>';
            $option_package_qty_hint = '<br><span class="bn_darkred">' . __( 'This Option is only available if the Shipping-Method BN Woo Manager Shipping-Zones is active!', 'bn-woo-manager' ) . '</span>';
        } else {
            $option_package_qty = '';
            $bn_shipping_zones_status = '<h3><strong>Status:</strong>&nbsp;&nbsp;<span class="bn_darkgreen">' . __( 'Active', 'bn-woo-manager' ) . '</span>&nbsp;&nbsp;<small><a href="'. admin_url( "admin.php?page=wc-settings&tab=shipping&section=bn_woo_manager_shipping_zones" ).'">' . __( 'Please edit here', 'bn-woo-manager' ) . '</a></small></h3>';
            $option_package_qty_hint = '';
        }
        if ( ! BN_Woo_Manager::$bn_woo_manager_option_total_units ) {
            $option_html_before_after = array( 'readonly' => true );
            $option_unit_disabled = array( 'disabled' => true );
            $bn_custom_units_create = '';
        } else {
            $option_html_before_after = '';
            $option_unit_disabled = '';
            $bn_custom_units_create = '<a href="' . admin_url( "edit-tags.php?taxonomy=bn_woo_manager_units&post_type=product" ) .'" target="_blanc">' . __( 'Custom units can be entered here', 'bn-woo-manager' ) . '</a>';
        }
        if ( ! BN_Woo_Manager::$bn_woo_manager_option_add_tax_info ) {
            $option_tax_html_before_after = array( 'readonly' => true );
            $option_tax_disabled = array( 'disabled' => true );
        } else {
            $option_tax_html_before_after = '';
            $option_tax_disabled = '';
        }

        $settings = array(
            'bn_head' => array(
                'type'          => 'custom_type',
                'desc'          => '
                <div id="bn_zones">
                    <div class="bn_head_left">
                        <a class="bn-switch" href="'.admin_url( "edit.php?post_type=product&page=bn_woo_manager_ajax_product_edit" ).'"><img class="bn_logo" alt="BN Shipping Zones" width="250" height="125" src="'.BN_Woo_Manager::$url . 'assets/img/bn_woo_manager_x.png" data-src="'.BN_Woo_Manager::$url .'assets/img/bn_woo_manager_y.png"></a><br><span><strong class="bn_darkred">'. __( 'Switch to', 'bn-woo-manager' ) . '&nbsp;&nbsp;<a href="'.admin_url( "edit.php?post_type=product&page=bn_woo_manager_ajax_product_edit" ).'">' . __( 'BN AJAX Product Edit',  'bn-woo-manager' ) . '</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="'. admin_url( "admin.php?page=wc-settings&tab=shipping&section=bn_woo_manager_shipping_zones" ).'">' . __( 'BN Shipping Zones', 'bn-woo-manager' ) . '</a></strong></span></div>
                    <div class="bn_head_right">
                        <span class="bn_plugin_title"><img width="364" height="25" src="'.BN_Woo_Manager::$url . 'assets/img/bn_woo_manager.png">&nbsp;&nbsp;<span class="bn_darkred">' . __( 'Settings',  'bn-woo-manager' ) . '</span></span><br><a href="http://www.gnu.org/licenses/gpl-3.0.html" title="License" target="_blank">License: GPLv3</a><br>
                        <div class="bn_donate"><div  id="bn_paypal"><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=HQ5X73WB7S5ZQ" target="_blank"><img src="'.BN_Woo_Manager::$url .'assets/img/btn_donate_LG.gif"></a></div>' . __( 'If you like to use this plugin, <strong>please be so cool</strong> and support the development with a small donation, for example <strong>10 Euro</strong>.<br><strong>Many thanks.</strong>', 'bn-woo-manager' ) . '
                        </div>
                    </div>
                </div><hr>',
            ),
            'bn_ajax_product-edit_section'   => array(
                'title'         => __( 'BN AJAX Product Edit', 'bn-woo-manager' ),
                'type'          => 'title',
                'desc'          => ''
            ),
            'bn_default_query_key' => array(
                'title'         => __( 'Default Query Search', 'bn-woo-manager' ),
                'desc'          => '',
                'type'          => 'select',
                'class'         => 'wc-enhanced-select',
                'default'       => '_sku',
                'options'       => array(
                    'product_data' => __( 'Product', 'bn-woo-manager' ),
                    '_sku'      => __( 'SKU', 'bn-woo-manager' ),
                    'id'        => __( 'ID', 'bn-woo-manager' ),
                    '_stock'    => __( 'Stock', 'bn-woo-manager' ),
                    '_price'    => __( 'Price', 'bn-woo-manager' )
                ),
                'id'            => 'bn_woo_manager_ajax_product_edit_query_key'
            ),
            'bn_default_products_per_page' => array(
                'title'         => __( 'Default Products per page', 'bn-woo-manager' ),
                'desc'          => '',
                'type'          => 'select',
                'class'         => 'wc-enhanced-select',
                'default'       => '20',
                'options'       => array(
                    '-1'     => __( 'Show all', 'bn-woo-manager' ),
                    '1'      => '1 ' . __( 'product per page', 'bn-woo-manager' ),
                    '5'      => '5 ' . __( 'products per page', 'bn-woo-manager' ),
                    '10'     => '10 ' . __( 'products per page', 'bn-woo-manager' ),
                    '20'     => '20 ' . __( 'products per page', 'bn-woo-manager' ),
                    '50'     => '50 ' . __( 'products per page', 'bn-woo-manager' ),
                    '100'    => '100 ' . __( 'products per page', 'bn-woo-manager' ),
                    '200'    => '200 ' . __( 'products per page', 'bn-woo-manager' ),
                    '500'    => '500 ' . __( 'products per page', 'bn-woo-manager' )
                ),
                'id'            => 'bn_woo_manager_ajax_product_edit_products_per_page'
            ),
            'bn_ajax_product-edit_section_end' => array(
                'type'          => 'sectionend',
            ),
            'bn_sep0' => array(
                    'type'      => 'custom_type',
                    'desc'      => '<hr>'
            ),
           'bn_shipping_zones_settings_section' => array(
                'title'         => __( 'BN Shipping Zones', 'bn-woo-manager' ),
                'type'          => 'title',
                'desc'          => ''
            ),
            'bn_shipping_zones_status' => array(
                'title'         => __( 'Status', 'bn-woo-manager' ),
                'type'          => 'custom_type',
                'desc'          => $bn_shipping_zones_status,
            ),
            'qty_field' => array(
                'title'         => __( 'Product QTY per calculated Shipping Class', 'bn-woo-manager' ),
                'type'          => 'checkbox',
                'desc'          => __( 'This adds a new data field to the product edit page and the BN Woo Manager AJAX Product Edit Page: <ul><li><strong>Product QTY per calculated Shipping Class</strong></li></ul>Shipping costs are based on shipping class are calculated per entered number of products instead of only per product or per Shipping Class.<br>Example: You send wine bottles still in a 6 box, no matter how many bottles are ordered. The shipping costs will be calculated per box.<br>This means that if your customer buys 1 bottle, he paid the shipping cost for a 6 box. Does he buy 7 bottles, he will pay the shipping costs for two 6 boxes. And so on.', 'bn-woo-manager' ) . $option_package_qty_hint,
                'default'       => 'no',
                'custom_attributes'  => $option_package_qty,
                'id'            => 'bn_woo_manager_option_package_qty'
            ),
            'bn_shipping_zones_settings_section_end' => array(
                 'type' => 'sectionend',
            ),
            'bn_sep1' => array(
                'type'          => 'custom_type',
                'desc'          => '<hr>'
            ),
            'bn_unit_price_section'   => array(
                'title'         => 'BN ' . __( 'Unit Price', 'bn-woo-manager' ),
                'type'          => 'title',
                'desc'          => ''
            ),
            'total_units_field' => array(
                'title'         => __( 'Automatic Unit Price Calculation', 'bn-woo-manager' ),
                'type'          => 'checkbox',
                'desc'          => __( 'This adds 3 new data fields to the product edit page and the BN Woo Manager AJAX Product Edit Page:<ul><li><strong>Units (Total Units of the product)</strong></li><li><strong>Unit (m,l,kg)</strong></li><li><strong>Units Base</strong></li></ul>Here you can enter the total units (litre, meter, pieces, kg ...) of your product and the unit price is calculated automatically.','bn-woo-manager' ) . '<br>' . $bn_custom_units_create,
                'default'       => 'no',
                'id'            => 'bn_woo_manager_option_total_units'
            ),
            'bn_units_show_on' => array(
                'title'         => __( 'Show unit price here' , 'bn-woo-manager' ),
                'description'   => '',
                'type'          => 'multiselect',
                'class'         => 'wc-enhanced-select',
                'css'           => 'width: 450px;',
                'default'       => '',
                'selected'      => array( 'product_page', 'cart', 'checkout'),
                'options'       => array(
                      'product_page'    => __( 'Product Page', 'bn-woo-manager' ),
                      'cart'            => __( 'Cart Price', 'bn-woo-manager' ),
                      'cart_checkout_subtotal'  => __( 'Cart / Checkout Subtotal', 'bn-woo-manager' ),
                      'thankyou'        => __( 'Thankyou / Order Details', 'bn-woo-manager' )
                 ),
                 'custom_attributes'  => $option_unit_disabled,
                 'id'           => 'bn_woo_manager_units_show_on'
            ),
            'html_before_unit_price' => array(
                'title'         => __( 'HTML before Unit Price', 'bn-woo-manager' ),
                'type'          => 'text',
                'css'           => 'width:30em',
                'desc'          => __( 'Enter HTML-Tags this way: [ tag ]', 'bn-woo-manager' ),
                'placeholder'   => '[br][span style="color:#333;font-size:0.8em;"]',
                'default'       => '[br][span style="color:#333;font-size:0.8em;"]',
                'custom_attributes'  => $option_html_before_after,
                'id'            => 'bn_woo_manager_html_before_unit_price'
            ),
            'html_after_unit_price' => array(
                'title'         => __( 'HTML after Unit Price', 'bn-woo-manager' ),
                'type'          => 'text',
                'css'           => 'width:30em',
                'desc'          => __( 'Enter HTML-Tags this way: [ tag ]', 'bn-woo-manager' ),
                'placeholder'   => '[/span]',
                'default'       => '[/span]',
                'custom_attributes'  => $option_html_before_after,
                'id'            => 'bn_woo_manager_html_after_unit_price'
            ),
            'unit_price_add_package' => array(
                'title'         => __( 'Show Package specification (total units) after Unit-Price', 'bn-woo-manager' ),
                'type'          => 'checkbox',
                'desc'          => '',
                'default'       => 'no',
                'custom_attributes'  => $option_unit_disabled,
                'id'            => 'bn_woo_manager_unit_price_add_package'
            ),
            'bn_unit_price_section_end' => array(
                'type'          => 'sectionend',
            ),
           'bn_sep2' => array(
                'type'          => 'custom_type',
                'desc'          => '<hr>'
            ),
            'bn_price_view_section'   => array(
                'title'         => 'BN Shop ' . __( 'Price View', 'bn-woo-manager' ),
                'type'          => 'title',
                'desc'          => ''
            ),
            'price_view' => array(
                'title'         => __( 'Price View Variable Products', 'bn-woo-manager' ),
                'type'          => 'checkbox',
                'desc'          => __( 'Show starting prices (from prices) in the shop for variable products.', 'bn-woo-manager' ),
                'default'       => 'no',
                'id'            => 'bn_woo_manager_option_price_view'
            ),
            'add_tax_info' => array(
                'title'         => __( 'Show VAT Info', 'bn-woo-manager' ),
                'type'          => 'checkbox',
                'desc'          => __( 'Show VAT info (e.g. incl. 20% VAT) after price (and unit-price)', 'bn-woo-manager' ),
                'default'       => 'no',
                'id'            => 'bn_woo_manager_option_add_tax_info'
            ),
            'show_tax_info' => array(
                'title'         => __( 'Show VAT Info only on single product page', 'bn-woo-manager' ),
                'type'          => 'checkbox',
                'desc'          => '',
                'default'       => 'no',
                'custom_attributes'  => $option_tax_disabled,
                'id'            => 'bn_woo_manager_option_tax_show_only_on_product_page'
            ),
            'html_before_tax_info' => array(
                'title'         => __( 'HTML before VAT Info', 'bn-woo-manager' ),
                'type'          => 'text',
                'css'           => 'width:30em',
                'desc'          => __( 'Enter HTML-Tags this way: [ tag ]', 'bn-woo-manager' ),
                'placeholder'   => '[br][span style="color:#333;font-size:0.8em;"]',
                'default'       => '[br][span style="color:#333;font-size:0.8em;"]',
                'custom_attributes'  => $option_tax_html_before_after,
                'id'            => 'bn_woo_manager_html_before_tax_info'
            ),
            'html_after_tax_info' => array(
                'title'         => __( 'HTML after VAT Info', 'bn-woo-manager' ),
                'type'          => 'text',
                'css'           => 'width:30em',
                'desc'          => __( 'Enter HTML-Tags this way: [ tag ]', 'bn-woo-manager' ),
                'placeholder'   => '[/span]',
                'default'       => '[/span]',
                'custom_attributes'  => $option_tax_html_before_after,
                'id'            => 'bn_woo_manager_html_after_tax_info'
            ),
            'bn_price_view_section_end' => array(
                 'type' => 'sectionend',
            ),
            'bn_sep3' => array(
                'type'     => 'custom_type',
                'desc'     => '<hr>'
            ),
            'bn_uninstall_section' => array(
                'title'         => __( 'Actions on Uninstall', 'bn-woo-manager' ),
                'type'          => 'title',
                'desc'          => __('If you uninstall the Plugin, here you can keep the stored Data for Reinstall', 'bn-woo-manager'),
            ),
            'bn_uninstall_zones'  => array(
                'title'         => __( 'Keep BN Shipping Zones Values', 'bn-woo-manager' ),
                'type'          => 'checkbox',
                'default'       => 'yes',
                'id'            => 'bn_woo_manager_uninstall_zones'
            ),
            'bn_uninstall_package_qty_field' => array(
                'title'         => __( 'Keep', 'bn-woo-manager' ) . ' ' . __( 'Product QTY per calculated Shipping Class', 'bn-woo-manager' ) . ' ' . __( 'Values', 'bn-woo-manager' ),
                'type'          => 'checkbox',
                'default'       => 'yes',
                'id'            => 'bn_woo_manager_uninstall_package_qty_field'
            ),
            'bn_uninstall_total_units_field' => array(
                'title'         => __( 'Keep BN Unit Price Values', 'bn-woo-manager' ),
                'desc'          => __( '(incl. Units Table)', 'bn-woo-manager' ),
                'type'          => 'checkbox',
                'default'       => 'yes',
                'id'            => 'bn_woo_manager_uninstall_total_units_field'
            ),
            'bn_uninstall_settings' => array(
                'title'         => __( 'Keep BN Woo Manager General Settings', 'bn-woo-manager' ),
                'type'          => 'checkbox',
                'default'       => 'yes',
                'id'            => 'bn_woo_manager_uninstall_settings'
            ),
            'bn_uninstall_section_end' => array(
                 'type' => 'sectionend',
            )
        );

        return apply_filters( 'bn_woo_manager_settings', $settings );
    }

    // Add Package-QTY field to product
    public static function bn_product_add_package_qty_field() {
        global $post;
    ?>
        <div class="bn_product_options_group_2"><strong>BN Woo Manager <?php echo __( 'Product QTY per calculated Shipping Class', 'bn-woo-manager' ); ?></strong><br>
            <div class="bn_product_options_field">
                <label>Anzahl</label><br>
                <input type="number" size="5" name="bn_woo_manager_package_qty_product" value="<?php echo get_post_meta( $post->ID, '_bn_woo_manager_package_qty', true ); ?>" />
                </label>
            </div>
        </div>
    <?php
    }
    // Save Package-QTY Field
    public static function bn_product_package_qty_save( $post_id ) {
        if ( isset( $_POST['bn_woo_manager_package_qty_product'] ) )
            $bn_woo_manager_package_qty_product = ( $_POST['bn_woo_manager_package_qty_product'] < 1 ) ? '' : $_POST['bn_woo_manager_package_qty_product'];
            update_post_meta( $post_id, '_bn_woo_manager_package_qty', $bn_woo_manager_package_qty_product );
    }
    // Add Package-QTY field to variable product
    public static function bn_add_package_qty_to_variations_metabox( $loop, $variation_data, $variation ) {
    ?>
        <div class="bn_product_options_group_2"><strong>BN Woo Manager <?php echo __( 'Product QTY per calculated Shipping Class', 'bn-woo-manager' ); ?></strong><br>
            <div class="bn_product_options_field">
                <label>Anzahl</label><br>
                <input type="number" size="5" name="bn_woo_manager_package_qty[<?php echo $loop; ?>]" value="<?php echo get_post_meta( $variation->ID, '_bn_woo_manager_package_qty', true ); ?>" />
                </label>
            </div>
        </div>

    <?php
    }
    // Save Package-QTY Field variable
    public static function bn_save_package_qty_at_product_variation( $variation_id, $loop ) {
        if ( isset( $_POST['bn_woo_manager_package_qty'][$loop] ) ) {
            $bn_woo_manager_package_qty = ( $_POST['bn_woo_manager_package_qty'][$loop] < 1 ) ? '' : $_POST['bn_woo_manager_package_qty'][$loop];
            update_post_meta( $variation_id, '_bn_woo_manager_package_qty', $bn_woo_manager_package_qty );
        }
    }
}
