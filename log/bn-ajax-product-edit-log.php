<?php
/* Set up WordPress environment */
if ( !defined('ABSPATH') ) {
    require_once( '../../../../wp-load.php' );
}

// get LOG-File
if ( current_user_can( 'manage_woocommerce' ) )
    echo file_get_contents( 'bn-ajax-product-edit-log.html' );
    else
    echo 'Access denied';
