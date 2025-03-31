jQuery(document).ready(function(){
	jQuery( '#woocommerce-order-items' ).on( 'click', 'button.save-action', function() {
		if(jQuery( 'table.woocommerce_order_items input[name="customer_user_ajax"]').length == 0 && jQuery( 'input#customer_user').length )
		jQuery( 'table.woocommerce_order_items #order_line_items td:first').append('<input type="hidden" name="customer_user_ajax" value="' + jQuery( 'input#customer_user').val() + '" />');
		else if(jQuery( 'table.woocommerce_order_items select[id="customer_user"]').length == 0 && jQuery( 'select#customer_user').length )
		jQuery( 'table.woocommerce_order_items #order_line_items td:first').append('<input type="hidden" name="customer_user_ajax" value="' + jQuery( 'select#customer_user').val() + '" />');
		else if(jQuery( 'input#customer_user').length)
		jQuery( 'table.woocommerce_order_items input[name="customer_user_ajax"]').val( jQuery( 'input#customer_user').val() );
		else if(jQuery( 'select#customer_user').length)
		jQuery( 'table.woocommerce_order_items input[name="customer_user_ajax"]').val( jQuery( 'select#customer_user').val() );
	} );
	if( jQuery( 'table.woocommerce_order_items input[name="customer_user_ajax"]').length == 0 && jQuery( 'table.woocommerce_order_items').length  && jQuery( 'input#customer_user').length)
		jQuery( 'table.woocommerce_order_items #order_line_items td:first').append('<input type="hidden" name="customer_user_ajax" value="' + jQuery( 'input#customer_user').val() + '" />');
	if( jQuery( 'table.woocommerce_order_items input[name="customer_user_ajax"]').length == 0 && jQuery( 'table.woocommerce_order_items').length && jQuery( 'select#customer_user').length )
		jQuery( 'table.woocommerce_order_items #order_line_items td:first').append('<input type="hidden" name="customer_user_ajax" value="' + jQuery( 'select#customer_user').val() + '" />');
});
