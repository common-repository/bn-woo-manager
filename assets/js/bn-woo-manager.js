/* BN Woo Manager
 *
 * @version     1.1
 * @author      BN-KareM
 */
jQuery( document ).ready( function( $ ) {
    // BN Woo Manager Logo mouse over
    $( '.bn_logo' ).bind( 'mouseenter mouseleave', function() {
        $( this ).attr({
            src: $( this ).attr( 'data-src' )
            , 'data-src': $( this ).attr( 'src' )
        })
    });
});

