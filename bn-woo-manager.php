<?php
/**
 * Plugin Name: BN Woo Manager
 * Plugin URI: https://wordpress.org/plugins/bn-woo-manager/
 * Description: AddOn for WooCommerce - AJAX Product Edit, Shipping-Zones & Unit Price Management
 * Version: 1.17
 * Author: BN-KareM
 * Author URI: https://wordpress.org/plugins/bn-woo-manager/
 * 
 * Requires at least: 4.1
 * Tested up to: 4.9
 * WC tested up to: 3.3
 * WC requires at least: 2.3
 * License: GPLv3
 *
 * Domain Path: /languages/
 * Text Domain: bn-woo-manager
 *
 * @author BN-KareM
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit();
}

if ( !class_exists( 'BN_Woo_Manager' ) ) :

/**
 * BN Woo Manager
 *
 * @class       BN_Woo_Manager
 * @version     1.16
 * @author      BN-KareM
 */
class BN_Woo_Manager {

    /**
     * Instance of this class.
     *
     * @var object
     */
    protected static $instance = null;

    public static $path = ''; // Path of plugin installation
    public static $url = ''; // URL of plugin installation

    // BN Woo Manager Options
    public static $bn_woo_manager_option_package_qty;
    public static $bn_woo_manager_option_total_units;
    public static $bn_woo_manager_units_show_on;
    public static $bn_woo_manager_html_before_unit_price;
    public static $bn_woo_manager_html_after_unit_price;
    public static $bn_woo_manager_unit_price_add_package;
    public static $bn_woo_manager_ajax_product_edit_query_key;
    public static $bn_woo_manager_ajax_product_edit_products_per_page;
    public static $bn_woo_manager_option_price_view;
    public static $bn_woo_manager_option_add_tax_info;
    public static $bn_woo_manager_option_tax_show_only_on_product_page;
    public static $bn_woo_manager_html_before_tax_info;
    public static $bn_woo_manager_html_after_tax_info;

    private function __construct() {
        // Load plugin textdomain
        add_action( 'init', __CLASS__ . '::bn_load_textdomain' );

        // Check WooCommerce Version
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        if ( is_plugin_active( 'woocommerce/woocommerce.php' ) && defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.3', '>=' ) ) {
            self::init();
        } else {
            add_action( 'admin_notices', __CLASS__ . '::bn_woocommerce_is_missing_notice' );
        }
    }

    /**
     * Return an instance of this class.
     *
     * @return object A single instance of this class.
     */
    public static function get_instance() {
        // If the single instance hasn't been set, set it now.
        if ( null == self::$instance ) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public static function init() {
        // init path & url
        self::$path = plugin_dir_path( __FILE__ );
        self::$url = plugin_dir_url( __FILE__ );

        // Init BN Woo Manager Options
        self::$bn_woo_manager_option_package_qty = get_option( 'bn_woo_manager_option_package_qty', 'no' ) == 'yes';
        self::$bn_woo_manager_option_total_units = get_option( 'bn_woo_manager_option_total_units', 'no' ) == 'yes';
        if ( !get_option( 'bn_woo_manager_units_show_on' ) ) add_option( 'bn_woo_manager_units_show_on', array( 'product_page', 'cart_checkout_subtotal' ) );
        self::$bn_woo_manager_units_show_on = get_option( 'bn_woo_manager_units_show_on' );
        self::$bn_woo_manager_html_before_unit_price = strtr( get_option( 'bn_woo_manager_html_before_unit_price', '[br][span style="color:#333;font-size:0.8em;"]' ), array( '[' => '<', ']' => '>' ) );
        self::$bn_woo_manager_html_after_unit_price = strtr( get_option( 'bn_woo_manager_html_after_unit_price', '[/span]' ), array( '[' => '<', ']' => '>' ) );
        self::$bn_woo_manager_unit_price_add_package = get_option( 'bn_woo_manager_unit_price_add_package', 'no' ) == 'yes';
        self::$bn_woo_manager_ajax_product_edit_query_key = get_option( 'bn_woo_manager_ajax_product_edit_query_key', '_sku' );
        self::$bn_woo_manager_ajax_product_edit_products_per_page = get_option( 'bn_woo_manager_ajax_product_edit_products_per_page', '20' );
        self::$bn_woo_manager_option_price_view = get_option( 'bn_woo_manager_option_price_view', 'no' ) == 'yes';
        self::$bn_woo_manager_option_add_tax_info = get_option( 'bn_woo_manager_option_add_tax_info', 'no' ) == 'yes';
        self::$bn_woo_manager_option_tax_show_only_on_product_page = get_option( 'bn_woo_manager_option_tax_show_only_on_product_page', 'no' ) == 'yes';
        self::$bn_woo_manager_html_before_tax_info = strtr( get_option( 'bn_woo_manager_html_before_tax_info', '[br][span style="color:#333;font-size:0.8em;"]' ), array( '[' => '<', ']' => '>' ) );
        self::$bn_woo_manager_html_after_tax_info = strtr( get_option( 'bn_woo_manager_html_after_tax_info', '[/span]' ), array( '[' => '<', ']' => '>' ) );

        if ( ! get_option( 'bn_woo_manager_uninstall_zones' ) ) add_option( 'bn_woo_manager_uninstall_zones', 'yes' );
        if ( ! get_option( 'bn_woo_manager_uninstall_package_qty_field' ) ) add_option( 'bn_woo_manager_uninstall_package_qty_field', 'yes' );
        if ( ! get_option( 'bn_woo_manager_uninstall_total_units_field' ) ) add_option( 'bn_woo_manager_uninstall_total_units_field', 'yes' );
        if ( ! get_option( 'bn_woo_manager_uninstall_settings' ) ) add_option( 'bn_woo_manager_uninstall_settings', 'yes' );

        // BN Woo Manager Admin CSS + JS
        add_action( 'admin_enqueue_scripts', __CLASS__ . '::bn_admin_scripts' );

        // Init BN Woo Manager General Settings Tab
        include_once( self::$path . 'includes/class-bn-woo-manager-settings.php' );
        BN_Woo_Manager_Settings::init();

        // Init BN AJAX Product Edit Page
        include_once( self::$path . 'includes/class-bn-woo-manager-ajax-product-edit.php' );
        BN_Woo_Manager_AJAX_Product_Edit::init();

        // Option BN Woo Manager Price View
        if ( self::$bn_woo_manager_option_price_view ) {
            add_filter( 'woocommerce_variable_sale_price_html', __CLASS__ . '::bn_variation_price_format', 10, 2 );
            add_filter( 'woocommerce_variable_price_html', __CLASS__ . '::bn_variation_price_format', 10, 2 );
        }

        // Functions for variation price jQuery Handling
        if ( self::$bn_woo_manager_option_total_units || self::$bn_woo_manager_option_add_tax_info ) {
            // load front js for variations price
            add_action( 'wp_enqueue_scripts', __CLASS__ . '::bn_front_js', 99 );
            // variation html for jQuery injection
            add_filter( 'woocommerce_get_variation_price_html', __CLASS__ . '::bn_add_html_to_variation_price_html', 10, 2 );
        }

        // Option BN Woo Manager Add Tax Info
        if ( self::$bn_woo_manager_option_add_tax_info ) {
            add_filter( 'woocommerce_get_price_html',  __CLASS__ . '::bn_add_tax_info_product', 11, 2 );
            if ( !self::$bn_woo_manager_option_total_units ) {
                // add BN Tax Info json-DATA to variable product
                add_action( 'woocommerce_before_variations_form', __CLASS__ . '::bn_tax_info_before_variations_form', 10 );
            }
        }

        // Option BN Woo Manager Unit Price
        if ( self::$bn_woo_manager_option_total_units ) {
            // Init BN Woo Manager Unit Price
            include_once( self::$path . 'includes/class-bn-woo-manager-unit-price.php' );
            BN_Woo_Manager_Unit_Price::init();
        }

        // Add Shipping Method
        add_filter( 'woocommerce_shipping_methods', __CLASS__ . '::add_bn_shipping_zones' );

        // Init Shipping Method
        add_action( 'woocommerce_shipping_init', __CLASS__ . '::bn_shipping_zones_init' );

    }

    // Load plugin textdomain
    public static function bn_load_textdomain() {
        $locale = apply_filters( 'plugin_locale', get_locale(), 'bn-woo-manager' );
        if ( $locale == 'de_DE' || $locale == 'de_DE_formal' ) load_textdomain( 'bn-woo-manager', WP_PLUGIN_DIR . '/bn-woo-manager/languages/bn-woo-manager-' . $locale . '.mo' );
        load_plugin_textdomain( 'bn-woo-manager', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    // BN Woo Manager Admin CSS + JS
    public static function bn_admin_scripts() {
        // load css
        wp_enqueue_style( 'bn_woo_manager_styles', self::$url . 'assets/css/bn-woo-manager.css', false, '1.0' );
        // register bn_woo_manager_script
        wp_register_script( 'bn_woo_manager_script', self::$url . 'assets/js/bn-woo-manager.js', array( 'jquery' ), '1.0', true );
        // register bn_woo_manager_product_edit__script
        wp_register_script( 'bn_woo_manager_ajax_product_edit_script', self::$url . 'assets/js/bn-woo-manager-ajax-product-edit.js', array( 'jquery' ), '1.0', true );
        // register bn_woo_manager_shipping_zones script
        wp_register_script( 'bn_woo_manager_shipping_zones_script', self::$url . 'assets/js/bn-woo-manager-shipping-zones.js', array( 'jquery' ), '1.0', true );
    }

    // Get display price for shop, cart/checkout & product-edit
    public static function bn_get_display_price( $product, $price, $bn_place ) {
        if ( $bn_place != 'product' ) {
            $display_price = ('incl' === get_option( 'woocommerce_tax_display_shop' ) ) ? wc_get_price_including_tax( $product, array( 'qty' => 1, 'price' => $price ) ) : wc_get_price_excluding_tax( $product, array( 'qty' => 1, 'price' => $price ) );
        } else {
            $display_price = ( get_option( 'woocommerce_prices_include_tax' ) === 'yes' ) ? wc_get_price_including_tax( $product, array( 'qty' => 1, 'price' => $price ) ) : wc_get_price_excluding_tax( $product, array( 'qty' => 1, 'price' => $price ) );
        }
        return $display_price;
    }

    // Option BN Woo Manager Price View
    public static function bn_variation_price_format( $price, $product ) {
        $min_price = self::bn_get_display_price( $product, $product->get_variation_price( 'min', true ), 'shop' );
        $max_price = self::bn_get_display_price( $product, $product->get_variation_price( 'max', true ), 'shop' );
        if ( $min_price != $max_price ) {
            $price = sprintf( __( 'from: %1$s', 'bn-woo-manager' ), wc_price( $min_price ) );
            return $price;
        } else {
            $price = sprintf( __( '%1$s', 'bn-woo-manager' ), wc_price( $min_price ) );
            return $price;
        }
    }

    // Load front js for variations price
    public static function bn_front_js() {
        wp_enqueue_script( 'bn_woo_manager_front_variations_price_script', BN_Woo_Manager::$url . 'assets/js/bn-woo-manager-front-variations-price.js', array( 'jquery' ), '1.0', true );
    }

    // BN Variation Price html for jQuery injection - shop single view -
    public static function bn_add_html_to_variation_price_html( $price, $product ) {
        if ( is_product() ) {
            $bn_variation_price_html = '<span class="bn_selected_variation_price"></span>';
            return $price . $bn_variation_price_html;
        } else return $price;
    }

    // Option BN Woo Manager Tax Info add to Product
    public static function bn_add_tax_info_product( $price, $product ) {
        if ( is_product() || ( ! self::$bn_woo_manager_option_tax_show_only_on_product_page && is_woocommerce() ) ) {
            $price .= self::$bn_woo_manager_html_before_tax_info . self::bn_get_tax_info( $product->get_id() ) . self::$bn_woo_manager_html_after_tax_info;
        }
        return $price;
    }

    // Option BN Woo Manager Tax Info add json-DATA to variable product - shop single view -
    public static function bn_tax_info_before_variations_form() {
        global $product;
        $available_variations = $product->get_available_variations();
        $variation_price_data = array();
        foreach ( $available_variations as $prod_variation ) :
            $variation_id = $prod_variation['variation_id'];
            $bn_tax_info_html = self::$bn_woo_manager_html_before_tax_info . self::bn_get_tax_info( $variation_id ) . self::$bn_woo_manager_html_after_tax_info;
            $variation_price_data[$variation_id] = array(
                'bn_woo_manager_variation_price' => $bn_tax_info_html
            );
        endforeach;
        echo '<span style="display:none!important;" id="bn_variation_price_data">' . json_encode( $variation_price_data, JSON_HEX_QUOT | JSON_HEX_TAG ) . '</span>';
    }

    // Get Tax Info HTML
    public static function bn_get_tax_info( $product_id ) {
        $tax_info = '';
        $product = wc_get_product( $product_id );
        if ( $product->is_taxable() ) {
            $tax_display_mode = get_option( 'woocommerce_tax_display_shop' );
            $tax_rates = array_values( WC_Tax::get_rates( $product->get_tax_class() ) );
            if ( ! $product->is_type( 'variable' ) && ( ! empty( $tax_rates ) ) ) {
                $tax_rate = round( floatval( $tax_rates[0][ 'rate' ] ), 2 );
                $tax_rate_str = ( $tax_rate == intval( $tax_rate ) ) ? strval( intval( $tax_rate ) ) . '%' : strval( $tax_rate ) . '%';
            } else $tax_rate_str = '';
            if ( ! empty( $tax_rates ) ) {
                $tax_info = ( $tax_display_mode == 'incl' && ! WC()->customer->is_vat_exempt() ) ? sprintf( __( 'incl. %s VAT', 'bn-woo-manager' ), $tax_rate_str ) : sprintf( __( 'excl. %s VAT', 'bn-woo-manager' ), $tax_rate_str );
                $tax_info = '<span class="bn_tax_info">' . $tax_info . ' </span>';
            }
        }
        return $tax_info;
    }

    // Add Shipping Method
    public static function add_bn_shipping_zones( $methods ) {
        $methods[] = 'BN_Woo_Manager_Shipping_Zones';
        return $methods;
    }

    // Init Shipping Method
    public static function bn_shipping_zones_init() {
        include_once( self::$path . 'includes/class-bn-woo-manager-shipping-zones.php' );
    }

    /**
     * WooCommerce missing notice.
     *
     * @return string Admin notice.
     */
    public static function bn_woocommerce_is_missing_notice() {
        echo '<div class="error"><p><strong>BN Woo Manager</strong> ' . sprintf( __( 'works only with %s or later!', 'bn-woo-manager' ), '<a href="http://wordpress.org/plugins/woocommerce/" target="blank">WooCommerce</a> 2.3' ) . '</p></div>';
    }

} // Main Class BN_Woo_Manager

// Hooks on plugin activation / deactivation - WooCommerce inactive
register_activation_hook( __FILE__, 'bn_woo_manager_activate_deactivate' );
register_deactivation_hook( __FILE__, 'bn_woo_manager_activate_deactivate' );
function bn_woo_manager_activate_deactivate() {
    global $wpdb;
    if ( !is_multisite() ) {
        $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE ('%_transient_bn_woo_man_xt_%')" );
        $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE ('%_transient_timeout_bn_woo_man_xt_%')" );
        if ( class_exists( 'WooCommerce' ) ) WC_Cache_Helper::get_transient_version( 'shipping', true );
    } else {
        $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
        foreach ( $blog_ids as $blog_id ) {
            switch_to_blog( $blog_id );
            $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE ('%_transient_bn_woo_man_xt_%')" );
            $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE ('%_transient_timeout_bn_woo_man_xt_%')" );
            if ( class_exists( 'WooCommerce' ) ) WC_Cache_Helper::get_transient_version( 'shipping', true );
            restore_current_blog();
        }
    }
}

/**
 * Initialize the plugin
 */
add_action( 'plugins_loaded', array( 'BN_Woo_Manager', 'get_instance' ) );

endif;

?>
