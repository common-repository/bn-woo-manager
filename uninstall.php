<?php
/**
 * BN Woo Manager Plugin Uninstall Procedure
 *
 * @version     1.1
 * @author      BN-KareM
 */

// Make sure that we are uninstalling
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
    exit();

// Uninstall taxonomy terms BN Woo Manager Units
function delete_custom_terms( $taxonomy ) {
    global $wpdb;
    $query = 'SELECT t.name, t.term_id
            FROM ' . $wpdb->terms . ' AS t
            INNER JOIN ' . $wpdb->term_taxonomy . ' AS tt
            ON t.term_id = tt.term_id
            WHERE tt.taxonomy = "' . $taxonomy . '"';
    $terms = $wpdb->get_results( $query );
    foreach ( $terms as $term ) {
        wp_delete_term( $term->term_id, $taxonomy );
    }
}

global $wpdb;

if ( !is_multisite() ) {
    // Delete plugin options
    if ( get_option( 'bn_woo_manager_uninstall_zones' ) == 'no' )
        delete_option ( 'woocommerce_bn_woo_manager_shipping_zones_settings' );
    if ( get_option ( 'bn_woo_manager_uninstall_package_qty_field' ) == 'no' )
        delete_post_meta_by_key( '_bn_woo_manager_package_qty' );
    if ( get_option ( 'bn_woo_manager_uninstall_total_units_field' ) == 'no' ) {
        delete_post_meta_by_key( '_bn_woo_manager_total_units' );
        delete_post_meta_by_key( '_bn_woo_manager_unit' );
        delete_post_meta_by_key( '_bn_woo_manager_base_units' );
        // Delete all custom terms for this taxonomy
        delete_custom_terms( 'bn_woo_manager_units' );
    }
    if ( get_option( 'bn_woo_manager_uninstall_settings' ) == 'no' ) {
        // Delete options
        $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE ('bn_woo_manager_%')" );
    }
} else {
    $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
    foreach ( $blog_ids as $blog_id ) {
        switch_to_blog( $blog_id );
        // Delete plugin options
        if ( get_option( 'bn_woo_manager_uninstall_zones' ) == 'no' )
            delete_option ( 'woocommerce_bn_woo_manager_shipping_zones_settings' );
        if ( get_option ( 'bn_woo_manager_uninstall_package_qty_field' ) == 'no' )
            delete_post_meta_by_key( '_bn_woo_manager_package_qty' );
        if ( get_option ( 'bn_woo_manager_uninstall_total_units_field' ) == 'no' ) {
            delete_post_meta_by_key( '_bn_woo_manager_total_units' );
            delete_post_meta_by_key( '_bn_woo_manager_unit' );
            delete_post_meta_by_key( '_bn_woo_manager_base_units' );
            // Delete all custom terms for this taxonomy
            delete_custom_terms( 'bn_woo_manager_units' );
        }
        if ( get_option( 'bn_woo_manager_uninstall_settings' ) == 'no' ) {
            // Delete options
            $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE ('bn_woo_manager_%')" );
        }
        restore_current_blog();
    }
}



