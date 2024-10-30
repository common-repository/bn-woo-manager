<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Settings for BN Woo Manager Shipping Zones
 *
 * @version     1.15
 * @author      BN-KareM
 */
$cost_desc = __( 'Enter a cost (excl. tax) or sum, e.g. <code>10.00 * [qty]</code>.', 'woocommerce' ) . '<br/>' . __( 'Supports the following placeholders: <code>[qty]</code> = number of items, <code>[cost]</code> = cost of items, <code>[fee percent="10" min_fee="20"]</code> = Percentage based fee.', 'woocommerce' );

$settings = array(
    'enabled' => array(
        'title'         => __( 'Enable/Disable', 'woocommerce' ),
        'type'          => 'checkbox',
        'label'         => __( 'Enable this shipping method', 'woocommerce' ),
        'default'       => 'no',
    ),
    'title' => array(
        'title'         => __( 'Method Title', 'woocommerce' ),
        'type'          => 'text',
        'description'   => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
        'default'       => __( 'Shipping / Parcel Service', 'bn-woo-manager' ),
        'desc_tip'      => true
    ),
    'availability' => array(
        'title'         => __( 'Availability', 'woocommerce' ),
        'type'          => 'select',
        'default'       => 'all',
        'class'         => 'availability wc-enhanced-select',
        'options'       => array(
            'all'       => __( 'All allowed countries', 'woocommerce' ),
            'specific'  => __( 'Specific Countries', 'woocommerce' ),
        ),
    ),
    'countries' => array(
        'title'         => __( 'Specific Countries', 'woocommerce' ),
        'type'          => 'multiselect',
        'class'         => 'wc-enhanced-select',
        'css'           => 'width: 450px;',
        'default'       => '',
        'options'       => WC()->countries->get_shipping_countries(),
        'custom_attributes' => array(
            'data-placeholder' => __( 'Select some countries', 'woocommerce' )
        )
    ),
    'tax_status' => array(
        'title'         => __( 'Tax Status', 'woocommerce' ),
        'type'          => 'select',
        'class'         => 'wc-enhanced-select',
        'default'       => 'taxable',
        'options'       => array(
            'taxable'   => __( 'Taxable', 'woocommerce' ),
            'none'      => _x( 'None', 'Tax status', 'woocommerce' )
        )
    ),
    'cost' => array(
        'title'         => __( 'Cost', 'woocommerce' ),
        'type'          => 'text',
        'placeholder'   => '',
        'css'           => 'color:#046;',
        'description'   => $cost_desc,
        'default'       => '',
        'desc_tip'      => true
    )
);

$shipping_classes = WC()->shipping->get_shipping_classes();

$settings['bn_zones_section'] = array(
        'title'         => '<span class="bn_046">' . __( 'Shipping Costs can be calculated by Shipping-Class and 6 specific Country-Zones & additional Postcode (Island) surcharge.','bn-woo-manager' ) . '<br><small>'.sprintf( __( 'These costs can optionally be added based on the %sproduct shipping class%s.', 'woocommerce' ), '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=shipping&section=classes' ) . '" target="_blank">', '</a>' ).'</small></span>',
        'type'          => 'title',
        'class'         => 'bn_zones_sub'
);

$settings['type'] = array(
        'title'         => __( 'Calculation Type', 'woocommerce' ),
        'type'          => 'select',
        'class'         => 'wc-enhanced-select',
        'default'       => 'class',
        'options'       => array(
            'class'     => __( 'Per Class: Charge shipping for each shipping class individually', 'woocommerce' ),
            'order'     => __( 'Per Order: Charge shipping for the most expensive shipping class', 'woocommerce' ),
        ),
);

$settings['no_class_cost'] = array(
        'title'         => '<span class="bn_darkred">' . __( 'No Shipping Class Cost','bn-woo-manager' ) . '<br><small>' . __( 'Products without Shipping-Class!','bn-woo-manager' ) . '</small></span>',
        'type'          => 'text',
        'placeholder'   => __( 'N/A', 'woocommerce' ),
        'css'           => 'color:#046;',
        'description'   => $cost_desc,
        'default'       => '1000* [qty]',
        'desc_tip'      => true
    );

$settings['general'] = array(
        'title'         => '<span class="bn_arrow">&#9650;&nbsp;&nbsp;</span>' . __( 'General / Default / Worldzone', 'bn-woo-manager' ),
        'type'          => 'title',
        'class'         => 'bn_zones_head'
);

foreach ( $shipping_classes as $shipping_class ) {
    if ( ! isset( $shipping_class->slug ) ) {
            continue;
    }

    $settings['class_cost_' . $shipping_class->slug.'_6'] = array(
            'title'       => '<span class="bn_darkgreen">'.esc_html( $shipping_class->name ).'</span><br><small>'. __( 'All other Countries!', 'bn-woo-manager' ) . '</small>',
            'type'        => 'text',
            'placeholder' => __( 'N/A', 'woocommerce' ),
            'css'         => 'color:#046;',
            'description' => $cost_desc,
            'default'     => '1000*[qty]',
            'desc_tip'    => true
    );
}

$settings['postcode_6'] = array(
            'title'       => __( 'Postcode / Island surcharge','bn-woo-manager' ) . '<br><small>' . __( 'All other Countries!', 'bn-woo-manager' ) . '</small>',
            'type'        => 'text',
            'placeholder' => __( 'N/A', 'woocommerce' ),
            'css'         => 'color:#046;',
            'description' => $cost_desc,
            'default'     => '',
            'desc_tip'    => true
);
$settings['postcodes_6'] = array(
            'title'       => __( 'Postcodes', 'bn-woo-manager' ) . '<br><small>' . __( 'All other Countries!', 'bn-woo-manager' ) . '</small>',
            'type'        => 'text',
            'placeholder' => __( 'N/A', 'woocommerce' ),
            'description' => __( 'Postcodes Comma separated', 'bn-woo-manager' ),
            'default'     => '',
            'desc_tip'    => true
);

// loop Shipping Zones
for ( $i=0; $i<=5; $i++ ) {

    $zonetitle = $this->get_option( 'zones'.$i, true );
    if ( strlen($zonetitle) < 2 ) $zonetitle = 'Zone '.$i;

    $settings['zones_title' . $i] = array(
        'title'         => '<span class="clicktitle"><span class="bn_arrow">&#9650;&nbsp;&nbsp;</span>'.$zonetitle.'</span>',
        'class'         => 'bn_zones_head',
        'type'          => 'title',
    );

    $settings['zones' . $i] = array(
        'title'         => 'Zone Title',
        'type'          => 'text',
        'placeholder'   => 'Zone '.$i,
        'default'       => 'Zone '.$i,
        'desc_tip'      => true
    );

    $settings['zone_' . $i] = array(
        'title'         => __( 'Country', 'woocommerce' ),
        'type'          => 'multiselect',
        'class'         => 'wc-enhanced-select',
        'css'           => 'width: 450px;',
        'default'       => '',
        'options'       => WC()->countries->get_shipping_countries(),
        'custom_attributes' => array(
            'data-placeholder'  => __( 'Select some countries', 'woocommerce' )
        )
    );

    if ( ! empty( $shipping_classes ) ) {
        foreach ( $shipping_classes as $shipping_class ) {
            if ( ! isset( $shipping_class->slug ) ) {
                    continue;
            }

            $settings['class_cost_' . $shipping_class->slug . '_' . $i] = array(
                'title'       => '<span class="bn_darkgreen">['.$zonetitle.'] '.esc_html( $shipping_class->name ).'</span>',
                'type'        => 'text',
                'placeholder' => __( 'N/A', 'woocommerce' ),
                'css'         => 'color:#046;',
                'description' => $cost_desc,
                'default'     => '',
                'desc_tip'    => true
                );
        }
    }

    $settings['postcode_' . $i] = array(
            'title'       => __( 'Postcode / Island surcharge', 'bn-woo-manager' ),
            'type'        => 'text',
            'placeholder' => __( 'N/A', 'woocommerce' ),
            'css'         => 'color:#046;',
            'description' => $cost_desc,
            'default'     => '',
            'desc_tip'    => true
    );
    $settings['postcodes_' . $i] = array(
            'title'       =>  __( 'Postcodes', 'bn-woo-manager' ),
            'type'        => 'text',
            'placeholder' => __( 'N/A', 'woocommerce' ),
            'description' => __( 'Postcodes Comma separated', 'bn-woo-manager' ),
            'default'     => '',
            'desc_tip'    => true
    );
} // end loop Shipping Zones

return $settings;
