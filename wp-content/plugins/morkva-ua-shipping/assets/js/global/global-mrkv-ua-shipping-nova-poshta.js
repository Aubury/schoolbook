jQuery(window).on('load', function() 
{
	/** NOVA POSHTA SHIPPING **/

    var np_settings_city_select = { 
		minimumInputLength: 3,
			ajax: {
				delay: 200,
		    url: mrkv_ua_ship_helper.ajax_url,
		    type: "POST",
		    data: function (params) {
		    	if(params.term && params.term.length > 2)
		    	{
		    		var query = {
				      	action: 'mrkv_ua_ship_nova_poshta_city',
				        name: params.term,
				        nonce: mrkv_ua_ship_helper.nonce,
				    }
		    	}
		    	else
		    	{
		    		var query = {
				      	action: 'mrkv_ua_ship_nova_poshta_city',
				      	nonce: mrkv_ua_ship_helper.nonce,
				    }
		    	}

		      return query;
		    },
		    processResults: function (json) {
		    	var data;
		    
		    	if(typeof json == 'string')
		    	{
		    		data = JSON.parse(json);

		    		return {
				        results: data.map(function(item) {
	                        return { id: item.label, text: item.label, ref: item.value, area: item.area, simple_label: item.label_simple };
	                    })
				    };
		    	}
		    	else
		    	{
		    		data = json;

		    		return {
				        results: mrkv_ua_ship_helper.nova_city_area.map(function(item) {
	                        return { id: item.label, text: item.label, ref: item.value, area: item.area, simple_label: item.label };
	                    })
				    };	
		    	}
		    },
	  	},
		};

	if(jQuery('#mrkv_ua_shipping_nova-poshta_city').length != 0)
 	{
 		if(!jQuery('#mrkv_ua_shipping_nova-poshta_city_ref').val())
 		{
 			jQuery('#mrkv_ua_shipping_nova-poshta_city option:first-child').attr('selected','selected');
 		}
 		
 		jQuery('#mrkv_ua_shipping_nova-poshta_city').select2(np_settings_city_select);

 		jQuery('#mrkv_ua_shipping_nova-poshta_city').on('select2:opening', function (e) {
 			jQuery(this).data('select2').$dropdown.find(':input.select2-search__field').attr('placeholder', mrkv_ua_ship_helper.city_placeholder);
 		});
 		jQuery('#mrkv_ua_shipping_nova-poshta_city').on('select2:closing', function (e) {
 			jQuery(this).data('select2').$dropdown.find(':input.select2-search__field').attr('placeholder', '');
 		});

 		jQuery('#mrkv_ua_shipping_nova-poshta_city').on('select2:select', function (e) {
 			let current_option = e.params.data;
 			jQuery(this).val(current_option.id);
	    	jQuery('#mrkv_ua_shipping_nova-poshta_city_ref').val(current_option.ref);
	    	jQuery('#mrkv_ua_shipping_nova-poshta_area_name').val(current_option.area);
	    	jQuery('#mrkv_ua_shipping_nova-poshta_city_label').val(current_option.simple_label);
	    	jQuery('#mrkv_ua_shipping_nova-poshta_warehouse_ref').val('');
    		jQuery('#mrkv_ua_shipping_nova-poshta_warehouse_number').val('');

	    	jQuery(this).removeClass('ui-autocomplete-loading');
	    	mrkvUaShipUpdateCart();
	        
	        jQuery.ajax({
	            type: 'POST',
	            url: mrkv_ua_ship_helper.ajax_url,
	            data: {
	                action: 'mrkv_ua_ship_nova_poshta_warehouse',
	                ref: current_option.ref,
	                warehouse_type: mrkv_ua_ship_helper.nova_warehouse_type,
	                nonce: mrkv_ua_ship_helper.nonce,
	            },
	            beforeSend: function() {
	                if (jQuery('#mrkv_ua_shipping_nova-poshta_warehouse').length != 0) {
	                    jQuery('#mrkv_ua_shipping_nova-poshta_warehouse').find('option').remove();
	                    jQuery('#mrkv_ua_shipping_nova-poshta_warehouse').addClass('mrkv-ua-shipping-loading');
	                }
	            },
	            success: function (json) {
	                var data = JSON.parse(json);

	               	if(data)
	               	{
               			jQuery.each(data, function(key, value) {
			                jQuery('#mrkv_ua_shipping_nova-poshta_warehouse')
			                .append(jQuery("<option></option>")
			                  .attr('value', this.label)
			                  .text(this.label)
			                  .attr('data-number', this.number)
			                  .attr('data-ref', this.value)
			                );
		              });
	               	}

	               	jQuery('#mrkv_ua_shipping_nova-poshta_warehouse').removeClass('mrkv-ua-shipping-loading');
	            }
	        });
		});
 	}

 	if(jQuery('#mrkv_ua_shipping_nova-poshta_warehouse').length != 0)
 	{
 		jQuery('#mrkv_ua_shipping_nova-poshta_warehouse').selectWoo();

 		let mrkv_ua_ship_warehouse = jQuery('#mrkv_ua_shipping_nova-poshta_city_ref').val();
 		let mrkv_ua_ship_choosen_warehouse = jQuery('#mrkv_ua_shipping_nova-poshta_warehouse_number').val();

 		jQuery.ajax({
            type: 'POST',
            url: mrkv_ua_ship_helper.ajax_url,
            data: {
                action: 'mrkv_ua_ship_nova_poshta_warehouse',
                ref: mrkv_ua_ship_warehouse,
                warehouse_type: '',
                nonce: mrkv_ua_ship_helper.nonce,
            },
            beforeSend: function() {
                if (jQuery('#mrkv_ua_shipping_nova-poshta_warehouse').length != 0) {
                    jQuery('#mrkv_ua_shipping_nova-poshta_warehouse').find('option:not(:first-child)').remove();
                    jQuery('#mrkv_ua_shipping_nova-poshta_warehouse').addClass('mrkv-ua-shipping-loading');
                }
            },
            success: function (json) {
                var data = JSON.parse(json);

               	if(data)
               	{
           			jQuery.each(data, function(key, value) {
		                jQuery('#mrkv_ua_shipping_nova-poshta_warehouse')
		                .append(jQuery("<option></option>")
		                  .attr('value', this.label)
		                  .text(this.label)
		                  .attr('data-number', this.number)
		                  .attr('data-ref', this.value)
		                );
	              });

           			if(mrkv_ua_ship_choosen_warehouse)
           			{
           				jQuery('#mrkv_ua_shipping_nova-poshta_warehouse option[data-number="' + mrkv_ua_ship_choosen_warehouse + '"]').attr('selected','selected');
           			}
           			else
           			{
           				mrkv_ua_ship_choosen_warehouse = jQuery('#mrkv_ua_shipping_nova-poshta_warehouse_ref').val();
           				jQuery('#mrkv_ua_shipping_nova-poshta_warehouse option[data-ref="' + mrkv_ua_ship_choosen_warehouse + '"]').attr('selected','selected');
           			}
               	}

               	jQuery('#mrkv_ua_shipping_nova-poshta_warehouse').removeClass('mrkv-ua-shipping-loading');
            }
        });

 		jQuery('body').on('change', '#mrkv_ua_shipping_nova-poshta_warehouse', function() {
		    let option_selected = jQuery(this).find('option:selected');
		    jQuery('#mrkv_ua_shipping_nova-poshta_warehouse_ref').val(jQuery(option_selected).attr('data-ref'));
		    jQuery('#mrkv_ua_shipping_nova-poshta_warehouse_number').val(jQuery(option_selected).attr('data-number'));
		});
 	}

 	/** NOVA POSHTA SHIPPING POSHTAMAT **/
	
	if(jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_city').length != 0)
 	{
 		if(!jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_city_ref').val())
 		{
 			jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_city option:first-child').attr('selected','selected');
 		}

 		jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_city').select2(np_settings_city_select);

 		jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_city').on('select2:opening', function (e) {
 			jQuery(this).data('select2').$dropdown.find(':input.select2-search__field').attr('placeholder', mrkv_ua_ship_helper.city_placeholder);
 		});
 		jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_city').on('select2:closing', function (e) {
 			jQuery(this).data('select2').$dropdown.find(':input.select2-search__field').attr('placeholder', '');
 		});

 		jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_city').on('select2:select', function (e) {
 			let current_option = e.params.data;
 			jQuery(this).val(current_option.id);

    		jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_city_ref').val(current_option.ref);
	    	jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_area_name').val(current_option.area);
	    	jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_city_label').val(current_option.simple_label);
	    	jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_ref').val('');
    		jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_number').val('');

	    	jQuery(this).removeClass('ui-autocomplete-loading');
	    	mrkvUaShipUpdateCart();
	        
	        jQuery.ajax({
	            type: 'POST',
	            url: mrkv_ua_ship_helper.ajax_url,
	            data: {
	                action: 'mrkv_ua_ship_nova_poshta_warehouse',
	                ref: current_option.ref,
	                warehouse_type: mrkv_ua_ship_helper.nova_poshtamat_type,
	                nonce: mrkv_ua_ship_helper.nonce,
	            },
	            beforeSend: function() {
	                if (jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_name').length != 0) {
	                    jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_name').find('option:not(:first-child)').remove();
	                    jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_name').addClass('mrkv-ua-shipping-loading');
	                }
	            },
	            success: function (json) {
	                var data = JSON.parse(json);
	               	if(data)
	               	{
               			jQuery.each(data, function(key, value) {
			                jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_name')
			                .append(jQuery("<option></option>")
			                  .attr('value', this.label)
			                  .text(this.label)
			                  .attr('data-number', this.number)
			                  .attr('data-ref', this.value)
			                );
		              });
	               	}

	               	jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_name').removeClass('mrkv-ua-shipping-loading');
	            }
	        });
		});
 	}

 	if(jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_name').length != 0)
 	{
 		jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_name').selectWoo();

 		let mrkv_ua_ship_poshtamat = jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_city_ref').val();
 		let mrkv_ua_ship_choosen_poshtamat = jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_number').val();

 		jQuery.ajax({
            type: 'POST',
            url: mrkv_ua_ship_helper.ajax_url,
            data: {
                action: 'mrkv_ua_ship_nova_poshta_warehouse',
                ref: mrkv_ua_ship_poshtamat,
                warehouse_type: 'f9316480-5f2d-425d-bc2c-ac7cd29decf0',
                nonce: mrkv_ua_ship_helper.nonce,
            },
            beforeSend: function() {
                if (jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_name').length != 0) {
                    jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_name').find('option:not(:first-child)').remove();
                    jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_name').addClass('mrkv-ua-shipping-loading');
                }
            },
            success: function (json) {
                var data = JSON.parse(json);
               	if(data)
               	{
           			jQuery.each(data, function(key, value) {
		                jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_name')
		                .append(jQuery("<option></option>")
		                  .attr('value', this.label)
		                  .text(this.label)
		                  .attr('data-number', this.number)
		                  .attr('data-ref', this.value)
		                );
	              	});

           			if(mrkv_ua_ship_choosen_poshtamat)
           			{
           				jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_name option[data-number="' + mrkv_ua_ship_choosen_poshtamat + '"]').attr('selected','selected');
           			}	
               	}

               	jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_name').removeClass('mrkv-ua-shipping-loading');
            }
        });

 		jQuery('body').on('change', '#mrkv_ua_shipping_nova-poshta_poshtamat_name', function() {
		    let option_selected = jQuery(this).find('option:selected');
		    jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_ref').val(jQuery(option_selected).attr('data-ref'));
		    jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_number').val(jQuery(option_selected).attr('data-number'));
		});
 	}

 	/** NOVA POSHTA SHIPPING ADDRESS **/

 	if(jQuery('#mrkv_ua_shipping_nova-poshta_address_city').length != 0)
 	{
 		jQuery('#mrkv_ua_shipping_nova-poshta_address_city').select2(np_settings_city_select);

 		jQuery('#mrkv_ua_shipping_nova-poshta_address_city').on('select2:opening', function (e) {
 			jQuery(this).data('select2').$dropdown.find(':input.select2-search__field').attr('placeholder', mrkv_ua_ship_helper.city_placeholder);
 		});
 		jQuery('#mrkv_ua_shipping_nova-poshta_address_city').on('select2:closing', function (e) {
 			jQuery(this).data('select2').$dropdown.find(':input.select2-search__field').attr('placeholder', '');
 		});

 		jQuery('#mrkv_ua_shipping_nova-poshta_address_city').on('select2:select', function (e) {
 			let current_option = e.params.data;
 			jQuery(this).val(current_option.id);

	    	jQuery('#mrkv_ua_shipping_nova-poshta_address_city_ref').val(current_option.ref);
	    	jQuery('#mrkv_ua_shipping_nova-poshta_address_area_name').val(current_option.area);
	    	jQuery('#mrkv_ua_shipping_nova-poshta_address_city_label').val(current_option.simple_label);
	    	jQuery('#mrkv_ua_shipping_nova-poshta_address_street_ref').val('');
	        jQuery('#mrkv_ua_shipping_nova-poshta_address_house').val('');
	        jQuery('#mrkv_ua_shipping_nova-poshta_address_flat').val('');
	    	jQuery(this).removeClass('ui-autocomplete-loading');
	        mrkvUaShipUpdateCart();
	       
		});
 	}

 	if(jQuery('#mrkv_ua_shipping_nova-poshta_address_street').length != 0)
 	{
 		jQuery('#mrkv_ua_shipping_nova-poshta_address_street').autocomplete({
			source: function (request, response) {

				var city_ref = jQuery('#mrkv_ua_shipping_nova-poshta_address_city_ref').val();

		        jQuery.ajax({
		            type: 'POST',
		            url: mrkv_ua_ship_helper.ajax_url,
		            data: {
		                action: 'mrkv_ua_ship_nova_poshta_street',
		                name: request.term,
		                ref: city_ref,
		                nonce: mrkv_ua_ship_helper.nonce,
		            },
		            success: function (json) {
		                var data = JSON.parse(json);
		                response(data);
		                jQuery(this).removeClass('ui-autocomplete-loading');
		            }
		        })
		    },
		    focus: function (event, ui) {
		        return false;
		    },
		    select: function (event, ui) {
		    	if(ui.item.value != 'none')
		    	{
		    		jQuery(this).val(ui.item.label);
			    	jQuery('#mrkv_ua_shipping_nova-poshta_address_street_ref').val(ui.item.value);
			    	jQuery('#mrkv_ua_shipping_nova-poshta_address_house').val('');
			    	jQuery('#mrkv_ua_shipping_nova-poshta_address_flat').val('');
			    	jQuery('#mrkv_ua_shipping_nova-poshta_address_address_ref').val('');
			    	jQuery(this).removeClass('ui-autocomplete-loading');
		    	}

		        return false;
		    }
		});
 	}

 	if(jQuery('#mrkv_ua_shipping_nova-poshta_address_house').length != 0)
 	{
 		jQuery('#mrkv_ua_shipping_nova-poshta_address_house').change(function(){
 			jQuery('#mrkv_ua_shipping_nova-poshta_address_flat').val('');
 		});
 	}

 	if(jQuery('#mrkv_ua_shipping_nova-poshta_address_flat').length != 0)
 	{
 		var mrkv_np_typing_timer;
	    var mrkv_np_done_typing_interval = 2000;

	    jQuery('#mrkv_ua_shipping_nova-poshta_address_flat').on('keyup', function() 
	    {
	    	clearTimeout(mrkv_np_typing_timer);
	  		mrkv_np_typing_timer = setTimeout(mrkvUaShipNPGetAddress, mrkv_np_done_typing_interval);       
	    });

	    jQuery('#mrkv_ua_shipping_nova-poshta_address_flat').on('keydown', function() 
	    {
	    	clearTimeout(mrkv_np_typing_timer);       
	    });

	    function mrkvUaShipNPGetAddress() 
	    {
			let sender_street_ref = jQuery('#mrkv_ua_shipping_nova-poshta_address_street_ref').val();
			let sender_building_number = jQuery('#mrkv_ua_shipping_nova-poshta_address_house').val();
			let sender_flat = jQuery('#mrkv_ua_shipping_nova-poshta_address_flat').val();

			if(sender_street_ref && sender_building_number && sender_flat)
			{
				jQuery.ajax({
		            type: 'POST',
		            url: mrkv_ua_ship_helper.ajax_url,
		            data: {
		                action: 'mrkv_ua_ship_nova_poshta_sender_get_address_ref',
						sender_street_ref: sender_street_ref,
						sender_building_number: sender_building_number,
						sender_flat: sender_flat,
						nonce: mrkv_ua_ship_helper.nonce,
		            },
		            success: function (data) {
		                if(data)
		                {
	                		jQuery('#mrkv_ua_shipping_nova-poshta_address_address_ref').val(data.replace(/['"]+/g, ''));
		                }	                
		            }
		        });
			}
		}
 	}

 	if(jQuery('#mrkv_ua_shipping_nova-poshta_address_patronymic_field').length != 0)
 	{
 		if(mrkv_ua_ship_helper.nova_middlename_exclude == 'yes')
 		{
 			jQuery('#mrkv_ua_shipping_nova-poshta_address_patronymic_field').hide();
 		}
 		if(mrkv_ua_ship_helper.nova_middlename_required == 'no')
 		{
 			jQuery('#mrkv_ua_shipping_nova-poshta_address_patronymic_enabled').val('off');
 		}
 	}

 	function mrkvUaShipUpdateCart()
 	{

	}
});