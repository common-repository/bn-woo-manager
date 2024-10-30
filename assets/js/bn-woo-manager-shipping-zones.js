/* BN Woo Manager Shipping Zones
 *
 * @version     1.1
 * @author      BN-KareM
 */
jQuery( document ).ready( function( $ ) {
    // accordion like
    $( '.bn_zones_head' ).next( '.form-table' ).hide();
    $( '.bn_zones_head' ).click( function( e ) {
        e.preventDefault();
        if ( $( this ).next( '.form-table' ).attr( 'id' ) == 'bn_current' ) {
            // current close
            $( this ).next( '.form-table' ).attr( 'id', '' ).hide();
            $( this ).find( $( '.bn_arrow' ) ).html( '&#9650;&nbsp;&nbsp;' );
            $( this ).css( 'background-image', 'linear-gradient(to top, #EAF0FF 0%, #FFF 100%)' );
            return
        } else {
            // close all
            $( '.bn_zones_head' ).next( '.form-table' ).attr( 'id', '' ).hide();
            $( '.bn_zones_head' ).find( $( '.bn_arrow' ) ).html( '&#9650;&nbsp;&nbsp;' );
            $( '.bn_zones_head' ).css( 'background-image', 'linear-gradient(to top, #EAF0FF 0%, #FFF 100%)' );
            // open selected
            $( this ).find( $( '.bn_arrow' ) ).html( '&#9660;&nbsp;&nbsp;' );
            $( this ).css( 'background-image', 'linear-gradient(to bottom, #EAF0FF 0%, #FFF 100%)' );
            $( this ).next( '.form-table' ).attr( 'id', 'bn_current' ).fadeIn();
        }
    });
});

