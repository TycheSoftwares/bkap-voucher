jQuery( document ).ready(function($) {
    
    if( jQuery("input[name='payment_type']" ).length > 0 ){
    
        let selected_type = jQuery("input[name='payment_type']:checked").val();

        if ( 'rent' == selected_type ) {
            jQuery(".bkapef_field").hide();
            jQuery(".voucher-fields-wrapper").hide();
        }
    }
    
    jQuery( document.body ).on( 'payment_type_product_rent', function(){
    	jQuery(".bkapef_field").hide();
        jQuery(".voucher-fields-wrapper").hide();
    
    } );
    
    jQuery( document.body ).on( 'payment_type_product_sale', function(){
        jQuery(".bkapef_field").show();
        jQuery(".voucher-fields-wrapper").show();
    } );
});