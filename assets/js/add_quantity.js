jQuery(function( $ ) {
    $( "form.checkout" ).on( "click", "input.qty", function( e ) {
        var data = {
            action: 'update_order_review',
            security: wc_checkout_params.update_order_review_nonce,
            post_data: $( 'form.checkout' ).serialize()
        };

        jQuery.post( add_quantity.ajax_url, data, function( response )
        {
            $( 'body' ).trigger( 'update_checkout' );
        });
    });
});