<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * BN Woo Manager Unit Price
 *
 * @class       BN_Woo_Manager_Unit_Price
 * @version     1.11
 * @author      BN-KareM
 */
class BN_Woo_Manager_Unit_Price {
    /**
     * Bootstraps the class and hooks required actions & filters.
     *
     */
    public static function init() {
        // add BN Unit Price to single product_page
        if ( in_array( 'product_page', BN_Woo_Manager::$bn_woo_manager_units_show_on ) ) {
            // simple products
            add_filter( 'woocommerce_get_price_html', __CLASS__ . '::bn_simple_unit_price_html', 10, 2 );
            // add BN Unit Price json-DATA to variable product
            add_action( 'woocommerce_before_variations_form', __CLASS__ . '::bn_unit_price_before_variations_form', 10 );
        }
        // add BN Unit Price to cart / checkout / order details / thankyou pages
        if ( in_array( 'cart', BN_Woo_Manager::$bn_woo_manager_units_show_on ) ) {
            // cart item price
            add_filter( 'woocommerce_cart_item_price', __CLASS__ . '::bn_cart_product_unit_price', 10, 3 );
        }
        if ( in_array( 'cart_checkout_subtotal', BN_Woo_Manager::$bn_woo_manager_units_show_on ) ) {
            // cart + checkout subtotal
            add_filter( 'woocommerce_cart_item_subtotal', __CLASS__ . '::bn_cart_product_unit_price',10, 3 );
        }
        if ( in_array( 'thankyou', BN_Woo_Manager::$bn_woo_manager_units_show_on ) ) {
            // order details / thankyou subtotal
            add_filter( 'woocommerce_order_formatted_line_subtotal', __CLASS__ . '::bn_cart_product_unit_price', 10, 3 );
        }

        // Add Total Units field to product
        add_action( 'woocommerce_product_options_general_product_data', __CLASS__ . '::bn_add_total_units_field_to_product' );
        // Save Total Units Field product
        add_action( 'woocommerce_process_product_meta', __CLASS__ . '::bn_product_total_units_save',999,1 );
        // Add Total Units field to variable product
        add_action( 'woocommerce_product_after_variable_attributes', __CLASS__ . '::bn_add_total_units_to_variations_metabox', 10, 3 );
        // Save Total Units Field variable product
        add_action( 'woocommerce_save_product_variation', __CLASS__ . '::bn_save_total_units_product_variation', 20, 2 );
        // Units Taxonomy
        add_action( 'init', __CLASS__ . '::bn_taxonomy_units', 11 );
    }

    // BN Unit Price - simple products - shop single view -
    public static function bn_simple_unit_price_html( $price, $product ) {
        $bn_unit_price_html = '';
        if ( is_product() ) {
            $bn_unit_price_html = self::bn_calculate_unit_price( $product->get_id(), 'shop' );
            $bn_unit_price_html = ( $bn_unit_price_html == '' ) ? '' : BN_Woo_Manager::$bn_woo_manager_html_before_unit_price . $bn_unit_price_html . self::bn_add_package_to_unit_price( $product->get_id() ) . BN_Woo_Manager::$bn_woo_manager_html_after_unit_price;
            return $price . $bn_unit_price_html;
        } else return $price;
    }

    // add BN Unit Price json-DATA to variable product - shop single view -
    public static function bn_unit_price_before_variations_form() {
        global $product;
        $available_variations = $product->get_available_variations();
        $unit_price_data = array();
        foreach ( $available_variations as $prod_variation ) :
            $variation_id = $prod_variation['variation_id'];
            $bn_unit_price_html = self::bn_calculate_unit_price( $variation_id, 'shop' );
            $bn_unit_price_html = ( $bn_unit_price_html == '' ) ? '' : BN_Woo_Manager::$bn_woo_manager_html_before_unit_price . $bn_unit_price_html . self::bn_add_package_to_unit_price( $variation_id ) . BN_Woo_Manager::$bn_woo_manager_html_after_unit_price;
            if ( BN_Woo_Manager::$bn_woo_manager_option_add_tax_info )
                $bn_unit_price_html .= BN_Woo_Manager::$bn_woo_manager_html_before_tax_info . BN_Woo_Manager::bn_get_tax_info( $variation_id ) . BN_Woo_Manager::$bn_woo_manager_html_after_tax_info;
            $unit_price_data[$variation_id] = array(
                'bn_woo_manager_variation_price' => $bn_unit_price_html
            );
        endforeach;
        echo '<span style="display:none!important;" id="bn_variation_price_data">' . json_encode( $unit_price_data, JSON_HEX_QUOT | JSON_HEX_TAG ) . '</span>';
    }

    // add BN Unit Price to cart / checkout / order details / thankyou pages
    public static function bn_cart_product_unit_price( $price, $cart_item, $cart_item_key ) {
        if ( $cart_item['variation_id'] )
            $product_id = $cart_item['variation_id'];
            else
            $product_id = $cart_item['product_id'];
        $bn_unit_price_html = self::bn_calculate_unit_price( $product_id, 'cart' );
        $bn_unit_price_html = ( $bn_unit_price_html == '' ) ? '' : BN_Woo_Manager::$bn_woo_manager_html_before_unit_price . $bn_unit_price_html . self::bn_add_package_to_unit_price( $product_id ) . BN_Woo_Manager::$bn_woo_manager_html_after_unit_price;
        return $price . $bn_unit_price_html;
    }

    // Generate Package HTML add to Unit Price
    public static function bn_add_package_to_unit_price( $product_id ) {
        if ( BN_Woo_Manager::$bn_woo_manager_unit_price_add_package ) {
            $total_units = get_post_meta( $product_id, '_bn_woo_manager_total_units', true );
            $unit_term = get_term_by( 'slug', get_post_meta( $product_id, '_bn_woo_manager_unit', true ), 'bn_woo_manager_units' );
            $unit = $unit_term->name;
            $total_units_2 = ( $total_units == intval( $total_units ) ) ? intval( $total_units ) : $total_units;
            return ' <span class="bn_unit_price_add_package"><small><nobr>(' . __( 'pkg.', 'bn-woo-manager' ) . ': ' . $total_units_2 . ' ' . $unit . ')</nobr></small></span>';
        } else {
            return '';
        }
    }

    // Add Total Units field to product
    public static function bn_add_total_units_field_to_product() {
        global $post, $wp_locale;
        // unit select
        $unit_x = get_terms( 'bn_woo_manager_units', array(
            'hide_empty' => false,
        ) );
        $unit_select = '<select name="bn_woo_manager_unit_product"><option value="_none">' . __( 'None', 'woocommerce' ) . '</option>';
        foreach ( $unit_x as $key => $valuey ) {
            if ( esc_attr( $valuey->slug ) == get_post_meta( $post->ID, '_bn_woo_manager_unit', true ) )
                $sely = 'selected'; else $sely = '';
            $unit_select .= '<option value="' . esc_attr( $valuey->slug ) . '" ' . $sely . '>' . $valuey->name . '</option>';
        }
        $unit_select .= '</select>';
        if ( wc_get_product( $post->ID )->is_type( 'simple' ) ) {
            $bn_woo_man_total = get_post_meta( $post->ID, '_bn_woo_manager_total_units', true );
    ?>
            <div class="bn_product_options_group">
                <strong>BN Woo Manager <?php echo __( 'Unit Price', 'bn-woo-manager' ); ?></strong>&nbsp;&nbsp;&nbsp;<span><strong><?php echo self::bn_calculate_unit_price( $post->ID, 'product' ); ?></strong></span><br>
                    <div class="bn_product_options_field">
                    <label><?php echo __( 'Units', 'bn-woo-manager' ); ?></label><br>
                    <input type="text" size="5" name="bn_woo_manager_total_units_product" value="<?php echo ( empty( $bn_woo_man_total ) ) ? $bn_woo_man_total : number_format_i18n( floatval( $bn_woo_man_total ), 1 ); ?>" />
                    </div>
                    <div class="bn_product_options_field">
                    <label><?php echo __( 'Unit', 'bn-woo-manager' ); ?></label><br>
                    <?php echo $unit_select; ?>
                    </div>
                    <div class="bn_product_options_field">
                    <label><?php echo __( 'Units Base', 'bn-woo-manager' ); ?></label><br>
                    <input type="number" size="5" name="bn_woo_manager_base_units_product" value="<?php echo get_post_meta( $post->ID, '_bn_woo_manager_base_units', true ); ?>" />
                    </div>
            </div>
    <?php
        }
    }
    // Save Total Units Field product
    public static function bn_product_total_units_save( $post_id ) {
        if ( wc_get_product(  $post_id )->is_type( 'simple' ) ) {
            if ( isset( $_POST['bn_woo_manager_total_units_product'] ) )
                $bn_woo_manager_total_units_product = ( $_POST['bn_woo_manager_total_units_product'] < 1 ) ? '' : BN_Woo_Manager_AJAX_Product_Edit::bn_price_format( $_POST['bn_woo_manager_total_units_product'] );
                update_post_meta( $post_id, '_bn_woo_manager_total_units', $bn_woo_manager_total_units_product );
            if ( isset( $_POST['bn_woo_manager_unit_product'] ) )
                update_post_meta( $post_id, '_bn_woo_manager_unit', $_POST['bn_woo_manager_unit_product'] );
            if ( isset( $_POST['bn_woo_manager_base_units_product'] ) )
                $bn_woo_manager_base_units_product = ( $_POST['bn_woo_manager_base_units_product'] < 1 ) ? '' : $_POST['bn_woo_manager_base_units_product'];
                update_post_meta( $post_id, '_bn_woo_manager_base_units', $bn_woo_manager_base_units_product );
        }
    }

    // Add Total Units field to variable product
    public static function bn_add_total_units_to_variations_metabox( $loop, $variation_data, $variation ) {
        global $wp_locale;
        // unit select
        $unit_x = get_terms( 'bn_woo_manager_units', array(
            'hide_empty' => false,
        ) );
        $unit_select = '<select name="bn_woo_manager_unit[' . $loop . ']"><option value="_none">' . __( 'None', 'woocommerce' ) . '</option>';
        foreach ( $unit_x as $key => $valuey ) {
            if ( esc_attr( $valuey->slug ) == get_post_meta( $variation->ID, '_bn_woo_manager_unit', true ) )
                $sely = 'selected'; else $sely = '';
            $unit_select .= '<option value="' . esc_attr( $valuey->slug ) . '" ' . $sely . '>' . $valuey->name . '</option>';
        }
        $unit_select .= '</select>';
        $bn_woo_man_total = get_post_meta( $variation->ID, '_bn_woo_manager_total_units', true );
    ?>
        <div class="bn_product_options_group">
            <strong>BN Woo Manager <?php echo __( 'Unit Price', 'bn-woo-manager' ); ?></strong>&nbsp;&nbsp;&nbsp;<span><strong><?php echo self::bn_calculate_unit_price( $variation->ID, 'product' ); ?></strong></span><br>
                <div class="bn_product_options_field">
                <label><?php echo __( 'Units', 'bn-woo-manager' ); ?></label><br>
                <input type="text" size="5" name="bn_woo_manager_total_units[<?php echo $loop; ?>]" value="<?php echo ( empty( $bn_woo_man_total ) ) ? $bn_woo_man_total : number_format_i18n( floatval( $bn_woo_man_total  ), 1 ); ?>" />
                </div>
                <div class="bn_product_options_field">
                <label><?php echo __( 'Unit', 'bn-woo-manager' ); ?></label><br>
                <?php echo $unit_select; ?>
                </div>
                <div class="bn_product_options_field">
                <label><?php echo __( 'Units Base', 'bn-woo-manager' ); ?></label><br>
                <input type="number" size="5" name="bn_woo_manager_base_units[<?php echo $loop; ?>]" value="<?php echo get_post_meta( $variation->ID, '_bn_woo_manager_base_units', true ); ?>" />
                </div>
        </div>
    <?php
    }
    // Save Total Units Field variable product
    public static function bn_save_total_units_product_variation( $variation_id, $loop ) {
        if ( isset( $_POST['bn_woo_manager_total_units'][$loop] ) ) {
            $bn_woo_manager_total_units = ( $_POST['bn_woo_manager_total_units'][$loop] < 1 ) ? '' : BN_Woo_Manager_AJAX_Product_Edit::bn_price_format( $_POST['bn_woo_manager_total_units'][$loop] );
            update_post_meta( $variation_id, '_bn_woo_manager_total_units', $bn_woo_manager_total_units );
        }
        if ( isset( $_POST['bn_woo_manager_unit'][$loop] ) ) {
            $bn_woo_manager_unit = $_POST['bn_woo_manager_unit'][$loop];
            update_post_meta( $variation_id, '_bn_woo_manager_unit', $bn_woo_manager_unit );
        }
        if ( isset( $_POST['bn_woo_manager_base_units'][$loop] ) ) {
            $bn_woo_manager_base_units = ( $_POST['bn_woo_manager_base_units'][$loop] < 1 ) ? '' : $_POST['bn_woo_manager_base_units'][$loop];
            update_post_meta( $variation_id, '_bn_woo_manager_base_units', $bn_woo_manager_base_units );
        }
    }

    // calculate & return unit price html excl / incl tax
    public static function bn_calculate_unit_price( $product_id, $bn_place ) {
        global $wp_locale;
        $total_units = round( floatval( get_post_meta( $product_id, '_bn_woo_manager_total_units', true ) ), 1 );
        $unit = get_post_meta( $product_id, '_bn_woo_manager_unit', true );
        $unit_base = get_post_meta( $product_id, '_bn_woo_manager_base_units', true );
        $price = get_post_meta( $product_id, '_price', true );
        if ( !empty( $unit ) && $unit != '_none' && $total_units > 0 && $unit_base > 0 && $price > 0 ) {
            $product = wc_get_product( $product_id );
            $total_units_2 = ( $total_units == intval( $total_units ) ) ? intval( $total_units ) : $total_units;
            $unit_term = get_term_by( 'slug', get_post_meta( $product_id, '_bn_woo_manager_unit', true ), 'bn_woo_manager_units' );
            $unit = $unit_term->name;
            $unit_base_2 = ( $unit_base > 1 ) ? $unit_base . ' ' : '';
            $unit_price = floatval( BN_Woo_Manager::bn_get_display_price( $product, $price, $bn_place ) ) / $total_units * floatval( $unit_base );
            $unit_price_html = '<span class="bn_unit_price_html"><nobr>' . strip_tags( wc_price( $unit_price ) ) . ' / ' . $unit_base_2 . $unit . '</nobr> </span>';
            return $unit_price_html;
        } else {
            return '';
        }
    }

    // Register Units Taxonomy
    public static function bn_taxonomy_units() {
        $labels = array(
            'name'                       => 'BN Woo Manager ' . __( 'Units', 'bn-woo-manager' ),
            'singular_name'              => 'BN Woo Manager '. __( 'Unit', 'bn-woo-manager' ),
            'menu_name'                  => 'BN Woo Manager ' . __( 'Units', 'bn-woo-manager' ),
            'all_items'                  => 'All Units',
            'parent_item'                => 'Parent Unit',
            'parent_item_colon'          => 'Parent Unit:',
            'new_item_name'              => 'New Item Name',
            'add_new_item'               => __( 'Add New Unit', 'bn-woo-manager' ),
            'edit_item'                  => __( 'Edit Unit', 'bn-woo-manager' ),
            'update_item'                => 'Update',
            'separate_items_with_commas' => 'Separate Unit with commas',
            'search_items'               => __( 'Search', 'bn-woo-manager' ),
            'add_or_remove_items'        => 'Add or remove Units',
            'choose_from_most_used'      => 'Choose from the most used Units',
        );
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => false,
            'public'                     => false,
            'show_ui'                    => true,
            'show_admin_column'          => false,
            'show_in_nav_menus'          => false,
            'show_tagcloud'              => false,
        );
        register_taxonomy( 'bn_woo_manager_units', 'product', $args );

        $state = get_terms( 'bn_woo_manager_units', array( 'hide_empty' => false ) );
        if ( empty( $state ) ) self::bn_add_taxonomy_units();
    }

    // pre-add some units
    private static function bn_add_taxonomy_units() {
        $bn_install_units = array(
            'm'         => 'm',
            'l'         => 'l',
            'g'         => 'g',
            'kg'        => 'kg',
            'pc'        => __( 'pc.', 'bn-woo-manager' ),
        );
        foreach ( $bn_install_units as $slug => $name )
            wp_insert_term( $name, 'bn_woo_manager_units', array( 'slug' => $slug, 'description' => 'BN Woo Manager default' ) );
    }

}
