<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * BN Woo Manager Shipping Zones
 *
 * @class       BN_Woo_Manager_Shipping_Zones
 * @version     1.15
 * @author      BN-KareM
 * Code-Base    WooThemes
 */
class BN_Woo_Manager_Shipping_Zones extends WC_Shipping_Method {

    /** @var string cost passed to [fee] shortcode */
    protected $fee_cost = '';

    /**
     * Constructor
     */
    public function __construct() {
        $this->id                 = 'bn_woo_manager_shipping_zones';
        $this->method_title       = 'BN Woo Manager ' . __( 'Shipping Zones', 'bn-woo-manager' );
        $this->method_description = '
            <div id="bn_zones">
                <div class="bn_head_left">
                    <a class="bn-switch" href="'.admin_url( "edit.php?post_type=product&page=bn_woo_manager_ajax_product_edit" ) .'"><img class="bn_logo" alt="BN Shipping Zones" width="250" height="125" src="'.BN_Woo_Manager::$url .'assets/img/bn_woo_manager_x.png" data-src="'.BN_Woo_Manager::$url . 'assets/img/bn_woo_manager_y.png"></a><br><br><span><strong class="bn_darkred">' . __( 'Switch to', 'bn-woo-manager' ) . '&nbsp;&nbsp;<a href="'.admin_url("edit.php?post_type=product&page=bn_woo_manager_ajax_product_edit" ).'">' . __( 'BN AJAX Product Edit', 'bn-woo-manager' ) . '</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="'.admin_url("admin.php?page=wc-settings&tab=bn_woo_manager_settings" ).'">' . __( 'BN Settings', 'bn-woo-manager' ) . '</a></strong></span>
                </div>
                <div class="bn_head_right">
                    <span class="bn_plugin_title"><img width="364" height="25" src="'.BN_Woo_Manager::$url . 'assets/img/bn_woo_manager.png">&nbsp;&nbsp;<span class="bn_darkred">' . __( 'Shipping Zones', 'bn-woo-manager' ) . '</span></span><br><a href="http://www.gnu.org/licenses/gpl-3.0.html" title="License" target="_blank">License: GPLv3</a><br>
                    <div class="bn_donate">
                    <div id="bn_paypal"><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=HQ5X73WB7S5ZQ" target="_blank"><img src="'.BN_Woo_Manager::$url . 'assets/img/btn_donate_LG.gif"></a></div>' . __( 'If you like to use this plugin, <strong>please be so cool</strong> and support the development with a small donation, for example <strong>10 Euro</strong>.<br><strong>Many thanks.</strong>', 'bn-woo-manager' ) . '
                    </div>
                </div>
            </div><hr>
            <p>' . __( 'Please enter all prices exclusive tax!', 'bn-woo-manager' ) . '</p>
            <h3 class="wc-settings-sub-title bn_zones_head"><span class="bn_arrow">&#9650;&nbsp;&nbsp;</span>Shortcodes</h3>
            <div class="form-table">
                <div class="bn_shortcodes_wrapper">
                    <div>
                        <span class="bn_font_13">Shortcodes</span><br>All Shortcodes can be combined<br>Example: 1.5 + 10*[qty] + [cost]*0.1 + [fee percent="5" min_fee="20"]
                    </div>
                    <div class="bn_shortcode_left">
                        <ul>
                            <li><span class="bn_sc">[qty]</span><br>Example: 10.2 * [qty]<br><small>Charge shipping-cost per Order-Quantity or Class Quantity or Most expensive Class Quantity all in combination with the optional BN Product Packages (QTY per package)</small></li>
                            <li><span class="bn_sc">[cost]</span> <br>Example: 0.1 * [cost]<br><small>Charge 10% shipping cost of the cost per item</small></li>
                        </ul>
                    </div>
                    <div class="bn_shortcode_right">
                        <ul>
                            <li><span class="bn_sc">[fee percent="XXX"]</span><br>Example: [fee percent="10"]<br><small>Charge 10% shipping cost of the Order-Total or Class-Total or Most expensive Class Total</small></li>
                            <li><span class="bn_sc">[fee percent="XXX" min_fee="XXX"]</span><br>Example: [fee percent="10" min_fee="12"]<br><small>Charge 10% shipping cost of the Order-Total or Class-Total or Most expensive Class Total by a minimum shipping-rate of 12</small></li>
                        </ul>
                    </div>
                </div>
            </div>';
        $this->init();

        add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );

    }

    /**
     * init function.
     */
    public function init() {
        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables
        $this->enabled      = $this->get_option( 'enabled' );
        $this->title        = $this->get_option( 'title' );
        $this->availability = $this->get_option( 'availability' );
        $this->countries    = $this->get_option( 'countries' );
        $this->tax_status   = $this->get_option( 'tax_status' );
        $this->cost         = $this->get_option( 'cost' );
        $this->type         = $this->get_option( 'type', 'class' );
        $this->zone_0       = $this->get_option( 'zone_0' );
        $this->zone_1       = $this->get_option( 'zone_1' );
        $this->zone_2       = $this->get_option( 'zone_2' );
        $this->zone_3       = $this->get_option( 'zone_3' );
        $this->zone_4       = $this->get_option( 'zone_4' );
        $this->zone_5       = $this->get_option( 'zone_5' );

    }

    public function admin_options() {
        wp_enqueue_script( 'bn_woo_manager_shipping_zones_script' );
        wp_enqueue_script( 'bn_woo_manager_script' );
        ?>
        <?php echo ( ! empty( $this->method_description ) ) ? wpautop( $this->method_description ) : ''; ?>
         <table class="form-table bn-form-table">
             <?php $this->generate_settings_html(); ?>
         </table><?php
    }

    /**
     * Initialise Settings Form Fields
     */
    public function init_form_fields() {
        $this->form_fields = include( BN_Woo_Manager::$path . 'includes/bn-woo-manager-shipping-zones-settings.php' );
    }

    /**
     * Evaluate a cost from a sum/string
     * @param  string $sum
     * @param  array  $args
     * @return string
     */
    protected function evaluate_cost( $sum, $args = array() ) {
        if ( !class_exists('WC_Eval_Math') ) include_once( BN_Woo_Manager::$path . 'includes/class-wc-eval-math.php' );

        $locale   = localeconv();
        $decimals = array( wc_get_price_decimal_separator(), $locale['decimal_point'], $locale['mon_decimal_point'] );

        $this->fee_cost = $args['cost'];

        // Expand shortcodes
        add_shortcode( 'fee', array( $this, 'fee' ) );

        $sum = do_shortcode( str_replace(
            array(
                '[qty]',
                '[cost]'
            ),
            array(
                $args['qty'],
                $args['cost']
            ),
            $sum
        ) );

        remove_shortcode( 'fee', array( $this, 'fee' ) );

        // Remove whitespace from string
        $sum = preg_replace( '/\s+/', '', $sum );

        // Remove locale from string
        $sum = str_replace( $decimals, '.', $sum );

        // Trim invalid start/end characters
        $sum = rtrim( ltrim( $sum, "\t\n\r\0\x0B+*/" ), "\t\n\r\0\x0B+-*/" );

        // Do the math
        return $sum ? WC_Eval_Math::evaluate( $sum ) : 0;
    }

    /**
     * Work out fee (shortcode)
     * @param  array $atts
     * @return string
     */
    public function fee( $atts ) {
        $atts = shortcode_atts( array(
            'percent' => '',
            'min_fee' => ''
        ), $atts );

        $calculated_fee = 0;

        if ( $atts['percent'] ) {
            $calculated_fee = $this->fee_cost * ( floatval( $atts['percent'] ) / 100 );
        }

        if ( $atts['min_fee'] && $calculated_fee < $atts['min_fee'] ) {
            $calculated_fee = $atts['min_fee'];
        }

        return $calculated_fee;
    }

    /**
     * calculate_shipping function.
     *
     * @param array $package (default: array())
     */
    public function calculate_shipping( $package = array() ) {
        $rate = array(
            'id'    => $this->id,
            'label' => $this->title,
            'cost'  => 0,
        );

        // Calculate the costs
        $has_costs = false; // True when a cost is set. False if all costs are blank strings.
        $cost      = $this->get_option( 'cost' );

        if ( $cost !== '' ) {
            $has_costs    = true;
            $rate['cost'] = $this->evaluate_cost( $cost, array(
                'qty'  => $this->get_package_item_qty( $package ),
                'cost' => $package['contents_cost']
            ) );
        }

        // Add shipping class costs
        $found_shipping_classes = $this->find_shipping_classes( $package );
        $highest_class_cost     = 0;

        $cus_country = WC()->customer->get_shipping_country();
        $cus_postcode = WC()->customer->get_shipping_postcode();
        if (         in_array( $cus_country, (array)$this->zone_0 ) ) $zone=0;
            elseif ( in_array( $cus_country, (array)$this->zone_1 ) ) $zone=1;
            elseif ( in_array( $cus_country, (array)$this->zone_2 ) ) $zone=2;
            elseif ( in_array( $cus_country, (array)$this->zone_3 ) ) $zone=3;
            elseif ( in_array( $cus_country, (array)$this->zone_4 ) ) $zone=4;
            elseif ( in_array( $cus_country, (array)$this->zone_5 ) ) $zone=5;
            else                                                      $zone=6;

        //Postcode surcharge
        $postc = in_array( $cus_postcode, explode( ',', $this->get_option( 'postcodes_'. $zone, '' ) ) ) ? $this->get_option( 'postcode_' . $zone, '' ) : '';

        foreach ( $found_shipping_classes as $shipping_class => $products ) {
            $class_cost_string = $shipping_class ? $this->get_option( 'class_cost_' . $shipping_class . '_' . $zone, '' ) : $this->get_option( 'no_class_cost', '' );

            //Postcode surcharge
            if ( ( $postc != '' ) && ( $class_cost_string === '' ) ) $class_cost_string .= $postc;
                elseif ( ( $postc != '' ) && ( $class_cost_string != '' ) ) $class_cost_string .= ' + (' . $postc . ')';

            if ( $class_cost_string === '' ) {
                continue;
            }

            $qty_base = [];
            foreach ( $products as $product) {
                if ( BN_Woo_Manager::$bn_woo_manager_option_package_qty ) {
                    $bn_base_qty = 1;
                    $bn_pq_v = get_post_meta( $product['variation_id'], '_bn_woo_manager_package_qty', true );
                    $bn_pq_p = get_post_meta( $product['product_id'], '_bn_woo_manager_package_qty', true );
                    if ( $bn_pq_v >= 1 ) $bn_base_qty = $bn_pq_v;
                        else if ( $bn_pq_p > 1 ) $bn_base_qty = $bn_pq_p;
                    if ( $bn_base_qty > 1 ) $qty_base[] = (int)ceil( (float)$product['quantity'] / (float)$bn_base_qty );
                        else $qty_base[] = $product['quantity'];
                } else
                $qty_base[] = $product['quantity'];
            }
            $has_costs  = true;
            $class_cost_string = str_replace( ',', '.', $class_cost_string );
            $class_cost = $this->evaluate_cost( $class_cost_string, array(
                'qty'  => array_sum( $qty_base ),
                'cost' => array_sum( wp_list_pluck( $products, 'line_total' ) )
            ) );

            if ( $this->type === 'class' ) {
                $rate['cost'] += $class_cost;
            } else {
                $highest_class_cost = $class_cost > $highest_class_cost ? $class_cost : $highest_class_cost;
            }
        }

        if ( $this->type === 'order' && $highest_class_cost ) {
            $rate['cost'] += $highest_class_cost;
        }

        // Add the rate
        if ( $has_costs ) {
            $this->add_rate( $rate );
        }

        do_action( 'woocommerce_' . $this->id . '_shipping_add_rate', $this, $rate, $package );

    }

    /**
     * Get items in package
     * @param  array $package
     * @return int
     */
    public function get_package_item_qty( $package ) {
        $total_quantity = 0;
        foreach ( $package['contents'] as $item_id => $values ) {
            if ( $values['quantity'] > 0 && $values['data']->needs_shipping() ) {
                if ( BN_Woo_Manager::$bn_woo_manager_option_package_qty ) {
                    $bn_base_qty = 1;
                    $bn_pq_v = get_post_meta( $values['variation_id'], '_bn_woo_manager_package_qty', true );
                    $bn_pq_p = get_post_meta( $values['product_id'], '_bn_woo_manager_package_qty', true );
                    if ( $bn_pq_v >= 1 ) $bn_base_qty = $bn_pq_v;
                        else if ( $bn_pq_p > 1 ) $bn_base_qty = $bn_pq_p;
                    if ( $bn_base_qty > 1 ) $total_quantity += (int)ceil( (float)$values['quantity'] / (float)$bn_base_qty );
                        else $total_quantity += $values['quantity'];
                } else
                $total_quantity += $values['quantity'];
            }
        }
        return $total_quantity;
    }

    /**
     * Finds and returns shipping classes and the products with said class.
     * @param mixed $package
     * @return array
     */
    public function find_shipping_classes( $package ) {
        $found_shipping_classes = array();

        foreach ( $package['contents'] as $item_id => $values ) {
            if ( $values['data']->needs_shipping() ) {
                $found_class = $values['data']->get_shipping_class();

                if ( ! isset( $found_shipping_classes[ $found_class ] ) ) {
                    $found_shipping_classes[ $found_class ] = array();
                }

                $found_shipping_classes[ $found_class ][ $item_id ] = $values;
            }
        }

        return $found_shipping_classes;
    }

}
