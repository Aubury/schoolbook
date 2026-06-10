jQuery(window).on('load', function() 
{
	var languageTexts = {
	    errorLoading: function () {
	        return mrkv_ua_ship_helper.select2_texts.errorLoading;
	    },
	    inputTooLong: function (args) {
	        return mrkv_ua_ship_helper.select2_texts.inputTooLong.replace('%d', args.input.length - args.maximum);
	    },
	    inputTooShort: function (args) {
	        return mrkv_ua_ship_helper.select2_texts.inputTooShort.replace('%d', args.minimum - args.input.length);
	    },
	    loadingMore: function () {
	        return mrkv_ua_ship_helper.select2_texts.loadingMore;
	    },
	    maximumSelected: function (args) {
	        return mrkv_ua_ship_helper.select2_texts.maximumSelected.replace('%d', args.maximum);
	    },
	    noResults: function () {
	        return mrkv_ua_ship_helper.select2_texts.noResults;
	    },
	    searching: function () {
	        return mrkv_ua_ship_helper.select2_texts.searching;
	    },
	    removeAllItems: function () {
	        return mrkv_ua_ship_helper.select2_texts.removeAllItems;
	    }
	};

	jQuery.fn.select2.amd.define('select2/data/extended-ajax',['./ajax','../utils','jquery'], function(AjaxAdapter, Utils, $){

	  function ExtendedAjaxAdapter ($element,options) {
	    //we need explicitly process minimumInputLength value 
	    //to decide should we use AjaxAdapter or return defaultResults,
	    //so it is impossible to use MinimumLength decorator here
	    this.minimumInputLength = options.get('minimumInputLength');
	    this.defaultResults     = options.get('defaultResults');

	    ExtendedAjaxAdapter.__super__.constructor.call(this,$element,options);
	  }

	  Utils.Extend(ExtendedAjaxAdapter,AjaxAdapter);
	  
	  //override original query function to support default results
	  var originQuery = AjaxAdapter.prototype.query;

	  ExtendedAjaxAdapter.prototype.query = function (params, callback) {
	    var defaultResults = (typeof this.defaultResults == 'function') ? this.defaultResults.call(this) : this.defaultResults;

	    if (defaultResults && defaultResults.length && (!params.term || params.term.length < this.minimumInputLength)){
	      var processedResults = this.processResults(defaultResults,params.term);
	      callback(processedResults);
	    }
	    else {
	      originQuery.call(this, params, callback);
	    }
	  };

	  return ExtendedAjaxAdapter;
	});

	var default_cities = [];

	mrkv_ua_ship_helper.nova_city_area.map(function(item) {
        default_cities.push({ id: item.label, text: item.label, ref: item.value, area: item.area, simple_label: item.label });
    });

    var $select;

    var np_settings_city_select = { 
			data: default_cities,
			dataAdapter: jQuery.fn.select2.amd.require('select2/data/extended-ajax'),
			defaultResults: default_cities,
			language: languageTexts,
		minimumInputLength: 3,
			ajax: {
				delay: 800,
		    url: mrkv_ua_ship_helper.ajax_url,
		    type: "POST",
		    data: function (params) {
		    	$select = jQuery(this);
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
		    beforeSend: function (e) {
		    	/*var current_select_name = 'select2-' + jQuery($select).attr('name') + '-results';
		    	jQuery('input[aria-owns="' + current_select_name + '"]').prop('disabled', true);
		    	jQuery('input[aria-owns="' + current_select_name + '"]').closest('.select2-search').append('<span class="mrkv-public-loader"></span>');*/

		    },
		    complete: function () {
		    	/*var current_select_name = 'select2-' + jQuery($select).attr('name') + '-results';
		    	jQuery('input[aria-owns="' + current_select_name + '"]').prop('disabled', false);
		    	jQuery('input[aria-owns="' + current_select_name + '"]').focus();
		    	jQuery('.mrkv-public-loader').remove();*/
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

	/** NOVA POSHTA SHIPPING **/

	if(jQuery('#mrkv_ua_shipping_nova-poshta_city').length != 0)
 	{
 		jQuery('#mrkv_ua_shipping_nova-poshta_city').select2(np_settings_city_select);

 		let nova_poshta_city = jQuery('#mrkv_ua_shipping_nova-poshta_city').attr('data-default');

 		if(nova_poshta_city)
 		{
 			jQuery('#mrkv_ua_shipping_nova-poshta_city').val(nova_poshta_city).trigger('change');
 		}

 		jQuery('#mrkv_ua_shipping_nova-poshta_city').on('select2:opening', function (e) {
 			jQuery(this).data('select2').$dropdown.find(':input.select2-search__field').attr('placeholder', mrkv_ua_ship_helper.city_placeholder);
 		});
 		jQuery('#mrkv_ua_shipping_nova-poshta_city').on('select2:closing', function (e) {
 			jQuery(this).data('select2').$dropdown.find(':input.select2-search__field').attr('placeholder', '');
 		});

 		var isWarehouseDataLoaded = true;

 		jQuery('#mrkv_ua_shipping_nova-poshta_city').on('select2:select', function (e) {
 			let current_option = e.params.data;
 			isWarehouseDataLoaded = false;
 			jQuery(this).val(current_option.id);
	    	jQuery('#mrkv_ua_shipping_nova-poshta_city_ref').val(current_option.ref);
	    	jQuery('#mrkv_ua_shipping_nova-poshta_area_name').val(current_option.area);
	    	jQuery('#mrkv_ua_shipping_nova-poshta_city_label').val(current_option.simple_label);
	    	jQuery('#mrkv_ua_shipping_nova-poshta_warehouse_ref').val('');
    		jQuery('#mrkv_ua_shipping_nova-poshta_warehouse_number').val('');

	    	jQuery(this).removeClass('ui-autocomplete-loading');
	    	mrkvUaShipUpdateCart();
	        
	        if(mrkv_ua_ship_helper.nova_search_by_number == 'yes')
	        {
	        	jQuery.ajax({
		            type: 'POST',
		            url: mrkv_ua_ship_helper.ajax_url,
		            data: {
		                action: 'mrkv_ua_ship_nova_poshta_warehouse',
		                ref: current_option.ref,
		                warehouse_type: mrkv_ua_ship_helper.nova_warehouse_type,
		                source_query: 'front',
		                default_content: 'part',
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
		               		var default_warehouse = [];
		               		jQuery.each(data, function(key, value) {
					                default_warehouse.push({ id: this.label, text: this.label, ref: this.value, number: this.number });
				            });

	               			let np_settings_warehouse_select_def = {
				        		data: default_warehouse,
								dataAdapter: jQuery.fn.select2.amd.require('select2/data/extended-ajax'),
								defaultResults: default_warehouse,
				        		language: {
						            inputTooShort: function () {
						                return mrkv_ua_ship_helper.enter_search_text;
						            }
						        }, 
								minimumInputLength: 1,
								ajax: {
									delay: 800,
							    	url: mrkv_ua_ship_helper.ajax_url,
							    	type: "POST",
								    data: function (params) 
								    {
								    	let city_ref = jQuery('#mrkv_ua_shipping_nova-poshta_city_ref').val();

								    	var query = {
									      	action: 'mrkv_ua_ship_nova_poshta_warehouse',
									      	ref: city_ref,
						                	warehouse_type: mrkv_ua_ship_helper.nova_warehouse_type,
						                	search_by: 'yes',
						                	source_query: 'front',
						                	name: params.term,
						                	nonce: mrkv_ua_ship_helper.nonce,
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
						                        return { id: item.label, text: item.label, ref: item.value, number: item.number };
						                    })
									    };
							    	}
							    	else
							    	{
							    		data = json;

							    		return {
									        results: default_warehouse
									    };	
							    	}
							    },
						  	},
							};
				        	jQuery('#mrkv_ua_shipping_nova-poshta_warehouse').select2(np_settings_warehouse_select_def);
		               	}

		               	if(data.length == 1 && data[0].value == 'none')
		               	{
		               		jQuery('#mrkv_ua_shipping_nova-poshta_warehouse_field .select2-selection__rendered').hide();
		               		setTimeout(function(){ 
		               			jQuery('#mrkv_ua_shipping_nova-poshta_warehouse_field .select2-selection__rendered').text(mrkv_ua_ship_helper.city_text_weight);
		               			jQuery('#mrkv_ua_shipping_nova-poshta_warehouse_field .select2-selection__rendered').show();
		               		}, 10);
		               	}

		               	jQuery('#mrkv_ua_shipping_nova-poshta_warehouse').removeClass('mrkv-ua-shipping-loading');
		               	isWarehouseDataLoaded = true;
		            }
		        });
	        }
	        else
	        {
	        	jQuery.ajax({
		            type: 'POST',
		            url: mrkv_ua_ship_helper.ajax_url,
		            data: {
		                action: 'mrkv_ua_ship_nova_poshta_warehouse',
		                ref: current_option.ref,
		                warehouse_type: mrkv_ua_ship_helper.nova_warehouse_type,
		                source_query: 'front',
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

	               			let first_element = jQuery('#mrkv_ua_shipping_nova-poshta_warehouse option:first');
	               			jQuery('#mrkv_ua_shipping_nova-poshta_warehouse_ref').val(jQuery(first_element).attr('data-ref'));
		    				jQuery('#mrkv_ua_shipping_nova-poshta_warehouse_number').val(jQuery(first_element).attr('data-number'));
		               	}

		               	if(data.length == 1 && data[0].value == 'none')
		               	{
		               		jQuery('#mrkv_ua_shipping_nova-poshta_warehouse_field .select2-selection__rendered').hide();
		               		setTimeout(function(){ 
		               			jQuery('#mrkv_ua_shipping_nova-poshta_warehouse_field .select2-selection__rendered').text(mrkv_ua_ship_helper.city_text_weight);
		               			jQuery('#mrkv_ua_shipping_nova-poshta_warehouse_field .select2-selection__rendered').show();
		               		}, 10);
		               	}

		               	jQuery('#mrkv_ua_shipping_nova-poshta_warehouse').removeClass('mrkv-ua-shipping-loading');
		               	isWarehouseDataLoaded = true;
		            }
		        });
	        }
		});
 	}

 	if(jQuery('#mrkv_ua_shipping_nova-poshta_warehouse').length != 0)
 	{
 		var np_settings_warehouse_select = {};

 		if(mrkv_ua_ship_helper.nova_search_by_number == 'yes')
        {
        	np_settings_warehouse_select = {
        		language: {
		            inputTooShort: function () {
		                return mrkv_ua_ship_helper.enter_search_text;
		            }
		        }, 
				minimumInputLength: 1,
				ajax: {
					delay: 800,
			    	url: mrkv_ua_ship_helper.ajax_url,
			    	type: "POST",
				    data: function (params) 
				    {
				    	let city_ref = jQuery('#mrkv_ua_shipping_nova-poshta_city_ref').val();

				    	var query = {
					      	action: 'mrkv_ua_ship_nova_poshta_warehouse',
					      	ref: city_ref,
		                	warehouse_type: mrkv_ua_ship_helper.nova_warehouse_type,
		                	search_by: 'yes',
		                	name: params.term,
		                	source_query: 'front',
		                	nonce: mrkv_ua_ship_helper.nonce,
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
		                        return { id: item.label, text: item.label, ref: item.value, number: item.number };
		                    })
					    };
			    	}
			    	else
			    	{
			    		data = json;

			    		return {
					        results: mrkv_ua_ship_helper.nova_city_area.map(function(item) {
		                        return { id: item.label, text: item.label, ref: item.value, number: item.number };
		                    })
					    };	
			    	}
			    },
		  	},
			};
        }

 		jQuery('#mrkv_ua_shipping_nova-poshta_warehouse').select2(np_settings_warehouse_select);

 		jQuery('#mrkv_ua_shipping_nova-poshta_warehouse').on('select2:opening', function(e) {
	        if (!isWarehouseDataLoaded) {
	            e.preventDefault();
	        }
	    });

 		let mrkv_ua_ship_warehouse = jQuery('#mrkv_ua_shipping_nova-poshta_city_ref').val();
 		let mrkv_ua_ship_choosen_warehouse = jQuery('#mrkv_ua_shipping_nova-poshta_warehouse_number').val();

 		if(mrkv_ua_ship_warehouse)
 		{
 			jQuery.ajax({
	            type: 'POST',
	            url: mrkv_ua_ship_helper.ajax_url,
	            data: {
	                action: 'mrkv_ua_ship_nova_poshta_warehouse',
	                ref: mrkv_ua_ship_warehouse,
	                warehouse_type: mrkv_ua_ship_helper.nova_warehouse_type,
	                source_query: 'front',
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

	           			jQuery('#mrkv_ua_shipping_nova-poshta_warehouse option[data-number="' + mrkv_ua_ship_choosen_warehouse + '"]').attr('selected','selected');
	               	}

	               	jQuery('#mrkv_ua_shipping_nova-poshta_warehouse').removeClass('mrkv-ua-shipping-loading');
	            }
	        });
 		}

 		if(mrkv_ua_ship_helper.nova_search_by_number == 'yes')
        {
        	jQuery('#mrkv_ua_shipping_nova-poshta_warehouse').on('select2:select', function (e) 
        	{
        		let current_option = e.params.data;

        		jQuery('#mrkv_ua_shipping_nova-poshta_warehouse_ref').val(current_option.ref);
    			jQuery('#mrkv_ua_shipping_nova-poshta_warehouse_number').val(current_option.number);
        	});
        }

 		jQuery('body').on('change', '#mrkv_ua_shipping_nova-poshta_warehouse', function() {
		    let option_selected = jQuery(this).find('option:selected');
		    jQuery('#mrkv_ua_shipping_nova-poshta_warehouse_ref').val(jQuery(option_selected).attr('data-ref'));
		    jQuery('#mrkv_ua_shipping_nova-poshta_warehouse_number').val(jQuery(option_selected).attr('data-number'));
		});
 	}

 	/** NOVA POSHTA SHIPPING POSHTAMAT **/
	
	if(jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_city').length != 0)
 	{
 		jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_city').select2(np_settings_city_select);

 		let nova_poshta_poshtamat_city = jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_city').attr('data-default');

 		if(nova_poshta_poshtamat_city)
 		{
 			jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_city').val(nova_poshta_poshtamat_city).trigger('change');
 		}

 		jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_city').on('select2:opening', function (e) {
 			jQuery(this).data('select2').$dropdown.find(':input.select2-search__field').attr('placeholder', mrkv_ua_ship_helper.city_placeholder);
 		});
 		jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_city').on('select2:closing', function (e) {
 			jQuery(this).data('select2').$dropdown.find(':input.select2-search__field').attr('placeholder', '');
 		});

 		var isPoshtamatDataLoaded = true;

 		jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_city').on('select2:select', function (e) {
 			let current_option = e.params.data;
 			isPoshtamatDataLoaded = false;
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
	                source_query: 'front',
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

               			let first_element = jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_name option:first');
               			jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_ref').val(jQuery(first_element).attr('data-ref'));
	    				jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_number').val(jQuery(first_element).attr('data-number'));
	               	}

	               	if(data.length == 1 && data[0].value == 'none')
	               	{
	               		jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_name_field .select2-selection__rendered').hide();
	               		setTimeout(function(){ 
	               			jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_name_field .select2-selection__rendered').text(mrkv_ua_ship_helper.city_text_weight);
	               			jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_name_field .select2-selection__rendered').show();
	               		}, 10);
	               	}

	               	jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_name').removeClass('mrkv-ua-shipping-loading');
	               	isPoshtamatDataLoaded = true;
	            }
	        });
		});
 	}

 	if(jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_name').length != 0)
 	{
 		jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_name').select2();

 		jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_name').on('select2:opening', function(e) {
	        if (!isPoshtamatDataLoaded) {
	            e.preventDefault();
	        }
	    });

 		let mrkv_ua_ship_poshtamat = jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_city_ref').val();
 		let mrkv_ua_ship_choosen_poshtamat = jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_number').val();

 		if(mrkv_ua_ship_poshtamat)
 		{
 			jQuery.ajax({
	            type: 'POST',
	            url: mrkv_ua_ship_helper.ajax_url,
	            data: {
	                action: 'mrkv_ua_ship_nova_poshta_warehouse',
	                ref: mrkv_ua_ship_poshtamat,
	                warehouse_type: mrkv_ua_ship_helper.nova_poshtamat_type,
	                source_query: 'front',
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

		              	jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_name option[data-number="' + mrkv_ua_ship_choosen_poshtamat + '"]').attr('selected','selected');
	               	}

	               	jQuery('#mrkv_ua_shipping_nova-poshta_poshtamat_name').removeClass('mrkv-ua-shipping-loading');
	            }
	        });
 		}

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

 		let nova_poshta_address_city = jQuery('#mrkv_ua_shipping_nova-poshta_address_city').attr('data-default');

 		if(nova_poshta_address_city)
 		{
 			jQuery('#mrkv_ua_shipping_nova-poshta_address_city').val(nova_poshta_address_city).trigger('change');
 		}

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
	        
	        jQuery.ajax({
	            type: 'POST',
	            url: mrkv_ua_ship_helper.ajax_url,
	            data: {
	                action: 'mrkv_ua_ship_nova_poshta_street_default',
	                ref: current_option.ref,
	                nonce: mrkv_ua_ship_helper.nonce,
	            },
	            beforeSend: function() {
	                if (jQuery('#mrkv_ua_shipping_nova-poshta_address_street').length != 0) {
	                    jQuery('#mrkv_ua_shipping_nova-poshta_address_street').find('option:not(:first-child)').remove();
	                    jQuery('#mrkv_ua_shipping_nova-poshta_address_street').addClass('mrkv-ua-shipping-loading');
	                }
	            },
	            success: function (json) {
	                var data = JSON.parse(json);
	               	if(data)
	               	{
	               		var default_streets = [];
               			jQuery.each(data, function(key, value) {
			                default_streets.push({ id: this.label, text: this.label, ref: this.value });
		              });

               			var np_settings_street_select = { 
				 			data: default_streets,
							dataAdapter: jQuery.fn.select2.amd.require('select2/data/extended-ajax'),
							defaultResults: default_streets,
							language: languageTexts,
							minimumInputLength: 3,
							ajax: {
								delay: 800,
						    	url: mrkv_ua_ship_helper.ajax_url,
						    	type: "POST",
							    data: function (params) {
							    	$select = jQuery(this);
							    	let ref_city = jQuery('#mrkv_ua_shipping_nova-poshta_address_city_ref').val();
							    	if(params.term && params.term.length > 2)
							    	{
							    		var query = {
									      	action: 'mrkv_ua_ship_nova_poshta_street',
									        name: params.term,
									        ref: ref_city,
									        nonce: mrkv_ua_ship_helper.nonce,
									    }
							    	}
							    	else
							    	{
							    		var query = {
									      	action: 'mrkv_ua_ship_nova_poshta_street',
									      	nonce: mrkv_ua_ship_helper.nonce,
									    }
							    	}

							      return query;
							    },
							    beforeSend: function (e) {},
							    complete: function () {},
							    processResults: function (json) {
							    	var data;
							    
							    	if(typeof json == 'string')
							    	{
							    		data = JSON.parse(json);

							    		return {
									        results: data.map(function(item) {
						                        return { id: item.label, text: item.label, ref: item.value };
						                    })
									    };
							    	}
							    	else
							    	{
							    		data = json;

							    		return {
									        results: default_streets
									    };	
							    	}
							    },
						  	},
						};

				 		jQuery('#mrkv_ua_shipping_nova-poshta_address_street').select2(np_settings_street_select);
	               	}

	               	if(data.length == 1 && data[0].value == 'none')
	               	{
	               		jQuery('#mrkv_ua_shipping_nova-poshta_address_street_field .select2-selection__rendered').hide();
	               		setTimeout(function(){ 
	               			jQuery('#mrkv_ua_shipping_nova-poshta_address_street_field .select2-selection__rendered').text(mrkv_ua_ship_helper.city_text_weight);
	               			jQuery('#mrkv_ua_shipping_nova-poshta_address_street_field .select2-selection__rendered').show();
	               		}, 10);
	               	}

	               	jQuery('#mrkv_ua_shipping_nova-poshta_address_street').removeClass('mrkv-ua-shipping-loading');
	            }
	        });
		});
 	}

 	if(jQuery('#mrkv_ua_shipping_nova-poshta_address_street').length != 0)
 	{
 		jQuery('#mrkv_ua_shipping_nova-poshta_address_street').select2();

 		jQuery('#mrkv_ua_shipping_nova-poshta_address_street').on('select2:select', function (e) {
		    let current_option = e.params.data;
		    jQuery('#mrkv_ua_shipping_nova-poshta_address_street_ref').val(current_option.ref);
		});
		jQuery('#mrkv_ua_shipping_nova-poshta_address_street').on('select2:opening', function (e) {
 			jQuery(this).data('select2').$dropdown.find(':input.select2-search__field').attr('placeholder', mrkv_ua_ship_helper.city_placeholder);
 		});
 		jQuery('#mrkv_ua_shipping_nova-poshta_address_street').on('select2:closing', function (e) {
 			jQuery(this).data('select2').$dropdown.find(':input.select2-search__field').attr('placeholder', '');
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

 	if(jQuery('#mrkv_ua_shipping_nova-poshta_international_warehouse').length != 0)
 	{
 		let autoSelectCityPo = function() 
		{
		    jQuery('#mrkv_ua_shipping_nova-poshta_international_warehouse').autocomplete({

		    source: function(request, response) { 

		      if(request.term.length > 2){
		        var country_sender = jQuery('#billing_country').length ? jQuery('#billing_country').val() : 'UA';
		        jQuery('#mrkv_ua_shipping_nova-poshta_international_warehouse').addClass('ui-autocomplete-loading');
		        jQuery.ajax({
		            method: 'POST',
		            url: mrkv_ua_ship_helper.ajax_url,
		            dataType: 'json',
		            data: {
		              term: request.term,
		              action: 'mrkv_ua_ship_novapost_divisions',
		              mrkvup_country_suggestion: country_sender,
		              nonce: mrkv_ua_ship_helper.nonce,
		            },
		            success: function(data) {
		              if(!Array.isArray(data))
		              {
		                response(data.response);
		              }
		              else
		              {
		                response(data);
		              }

		              
		              jQuery('#mrkv_ua_shipping_nova-poshta_international_warehouse').removeClass('ui-autocomplete-loading');
		            },
		                error: function(xhr, status, error) {
		                    
		                },
		          });
		      }
		    },
		    select: function(event, ui) {
		      event.preventDefault();
		      jQuery(this).val( ui.item.label );
		      jQuery( "#mrkv_ua_shipping_nova-poshta_international_warehouse_ref" ).val( ui.item.value );
		      jQuery( "#mrkv_ua_shipping_nova-poshta_international_warehouse_number" ).val( ui.item.number );
		      jQuery('body').trigger('update_checkout');
		      },
		      minLength: 0,
		      delay: 0,
		    }).focus(function(){            
		            jQuery(this).data("uiAutocomplete").search(jQuery(this).val());
		        });
		  }

	  autoSelectCityPo();
 	}

 	if(jQuery('#mrkv_ua_shipping_nova-poshta_inter_address_postcode').length != 0)
 	{
 		let typingTimerNovaPost;
	    const doneTypingIntervalNovaPost = 3000;
	    const $input = jQuery('#mrkv_ua_shipping_nova-poshta_inter_address_postcode');

	    $input.on('keyup input', function() {
	        clearTimeout(typingTimerNovaPost);
	        typingTimerNovaPost = setTimeout(doneTypingNovaPost, doneTypingIntervalNovaPost);
	    });

	    $input.on('keydown', function() {
	        clearTimeout(typingTimerNovaPost);
	    });

	    function doneTypingNovaPost() {
	     	mrkvUaShipUpdateCart();   
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
 			jQuery('label[for="mrkv_ua_shipping_nova-poshta_address_patronymic"] abbr').hide();
 		}
 		if(mrkv_ua_ship_helper.nova_warehouse_text != '')
 		{
 			jQuery('label[for="mrkv_ua_shipping_nova-poshta_warehouse"]').text(mrkv_ua_ship_helper.nova_warehouse_text);
 		}
 	}

 	function mrkvUaShipUpdateCart()
 	{
 		jQuery('body').trigger('update_checkout', { update_shipping_method: true });
	}
});