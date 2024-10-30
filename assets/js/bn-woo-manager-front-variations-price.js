/* BN Woo Manager Shop Variatons Price
 *
 * @version     1.1
 * @author      BN-KareM
 */
jQuery(function($) {
    if ( $( '#bn_variation_price_data' ).text().length )
        var bn_variation_price_data = JSON.parse( $( '#bn_variation_price_data' ).text() );
        else
        var bn_variation_price_data = '';
    $( document ).on( 'found_variation',  function( event, variation ) {
        bn_variation_price_value = bn_variation_price_data[variation.variation_id].bn_woo_manager_variation_price;
        $( '.bn_selected_variation_price' ).html( bn_variation_price_value );
    });
    $( window ).on( 'load', function() {
        $( 'form.variations_form .variations select' ).trigger( 'change' );
        $( 'form.variations_form .variations input:radio:checked' ).trigger( 'change' );
    });
});
