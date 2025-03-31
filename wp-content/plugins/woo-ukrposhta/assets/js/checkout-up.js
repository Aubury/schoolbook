'use strict';

(function (jQuery) {

  if (jQuery('form[name="checkout"]').length === 0) return; // On Checkout page only

  let jQueryshippingBox = jQuery('#ukrposhta_shippping_fields'); // Доставка на Відділення
  var currentCountry = jQuery('#billing_country').length ? jQuery('#billing_country').val() : 'UA';

  let setLoadingState = function () {
    jQueryshippingBox.addClass('wcus-state-loading');
  };

  let unsetLoadingState = function () {
    jQueryshippingBox.removeClass('wcus-state-loading');
  };

  jQuery('.woocommerce-shipping-fields').css('display', 'none'); // Доставка на іншу адресу

  let getCurrentShippingMethod = function() {
    let currentShippingMethod,
      currentShipping = jQuery('.shipping_method').length;
    if (1 == currentShipping) {
      currentShippingMethod = jQuery('.shipping_method').val();
    } else {
      currentShippingMethod = jQuery('.shipping_method:checked').val()
    }
      return currentShippingMethod;
  }

  let isukrPoshtaShippingSelected = function () {
    let currentShipping = jQuery('.shipping_method').length >= 1 ?
      jQuery('.shipping_method:checked').val() :
      jQuery('.shipping_method').val();


    return currentShipping && currentShipping.match(/^ukrposhta_shippping.+/i);
  };

  let isukrPoshtaAddressShippingSelected = function () {
    let currentShipping = jQuery('.shipping_method').length > 1 ?
      jQuery('.shipping_method:checked').val() :
      jQuery('.shipping_method').val();
    return currentShipping && currentShipping.match(/^ukrposhta_address_shippping.+/i);
  };

  let selectShipping = function () {
    if (isukrPoshtaShippingSelected()) {
      if(currentCountry === 'UA'){
        jQuery('#ukrposhta_shippping_fields').css('display', 'block');
        jQuery('.woocommerce-shipping-fields').css('display', 'none');
      }
      else{
        jQuery('#ukrposhta_shippping_fields').css('display', 'none');
        jQuery('.woocommerce-billing-fields').css('display', 'block');
      }
    }
    else {
      jQuery('#ukrposhta_shippping_fields').css('display', 'none');
      jQuery('.woocommerce-shipping-fields').css('display', 'block');
    }
  };

  let disableDefaultBillingFieldsforup = function () {
    if (isukrPoshtaShippingSelected() && morkva_ukrposhta_globals.disableDefaultBillingFields === 'true') {
      //console.log('way1');
      jQuery('#billing_address_1_field').css('display', 'none');
      jQuery('#billing_address_2_field').css('display', 'none');
      jQuery('#billing_city_field').css('display', 'none');
      jQuery('#billing_state_field').css('display', 'none');
      jQuery('#billing_postcode_field').css('display', 'none');
    }
    else {
      //console.log('way2');
      // jQuery('#billing_address_1_field').css('display', 'block');
      // jQuery('#billing_address_2_field').css('display', 'block');
      // jQuery('#billing_city_field').css('display', 'block');
      // jQuery('#billing_state_field').css('display', 'block');
      // jQuery('#billing_postcode_field').css('display', 'block');

    }

    if(isukrPoshtaAddressShippingSelected())
    {
      jQuery('.woocommerce-billing-fields').css('display', 'block');
      jQuery('#billing_address_1_field').css('display', 'block');
      jQuery('#billing_address_2_field').css('display', 'block');
      jQuery('#billing_city_field').css('display', 'block');
      jQuery('#billing_state_field').css('display', 'block');
      jQuery('#billing_postcode_field').css('display', 'block');
    }

    // Set billing_country field value if it is absent on Checkout page
    // currentCountry = jQuery('#billing_country').length ? jQuery('#billing_country').val() : 'UA';
    /*if(currentCountry !== 'UA'){
        jQuery('.woocommerce-billing-fields').css('display', 'block');


        jQuery('#billing_address_1_field').css('display', 'block');
        jQuery('#billing_address_2_field').css('display', 'block');
        jQuery('#billing_city_field').css('display', 'block');
        jQuery('#billing_state_field').css('display', 'block');
        jQuery('#billing_postcode_field').css('display', 'block');
    }*/
  };

  let serialixe = function(a){
    //console.log('serialize');
    console.log('currentCountry= '+currentCountry);
    Cookies.set('shipping_country', jQuery('#billing_country').val() );
    Cookies.set('shipping_city', jQuery('#billing_city').val() );
    Cookies.set('up_shipping_postcode', jQuery('#ukrposhta_shippping_warehouse').val() );
    Cookies.set('ukrposhta_shippping_postcode_selected', jQuery('#ukrposhta_shippping_postcode_selected').val() );
    Cookies.set('ukrposhta_address_shippping_postcode', jQuery('#billing_postcode').val() );


    var addr = jQuery('#up_custom_address').attr('checked') || 'unchecked';
    //console.log(addr);
    if(addr == 'unchecked'){
        Cookies.set('up_custom_address',"",-1);
    }
    else{
      Cookies.set('up_custom_address', addr );
    }
    jQuery('body').trigger('update_checkout'); // What is it?

  }
  // console.log('serialixe= '+serialixe());
  let initialize = function () {
    jQuery('#ukrposhta_shippping_warehouse').on('change', function(){
      serialixe();
    });

    jQuery('#ukrposhta_shippping_postcode_selected').on('change', function(){
      serialixe();
    });

    jQuery('#ukrposhta_shippping_city').on('change', function(){
      serialixe();
    });

    jQuery('#ukrposhta_shippping_city2').on('change', function(){
      serialixe();
    });
    jQuery('#billing_country').on('change', function(){
      serialixe();
    });
    jQuery('#up_custom_address').on('change', function(){
      serialixe();
    });


    // let jQuerycustomAddressCheckbox = document.getElementById('up_custom_address');

    // let showCustomAddress = function () {

    //   if (jQuerycustomAddressCheckbox.checked) {
    //     jQuery('#nova-poshta-shipping-info').slideUp(400);
    //     jQuery('#up_custom_address_block').slideDown(400);

    //   }
    //   else {
    //     jQuery('#nova-poshta-shipping-info').slideDown(400);
    //     jQuery('#up_custom_address_block').slideUp(400);
    //   }

    //   disableDefaultBillingFieldsforup();

    // };

    // if (jQuerycustomAddressCheckbox) {
    //   showCustomAddress();
    //   jQuerycustomAddressCheckbox.onclick = showCustomAddress;
    // }


  };

    // Check if cash on delivery (cod) payment gateway has been chosen
    let usingPaymentGateway = function(){
        if(jQuery('form[name="checkout"] input[name="payment_method"]:checked').val() == 'cod'){
            jQuery('#ukrposhta_shippping_surname_field').show();
        }else{
            jQuery('#ukrposhta_shippping_surname_field').hide();
        }
    }

    jQuery(function() {
        jQuery('#ukrposhta_shippping_fields').css('display', 'none');

        jQuery(document.body).on('update_checkout', function (event, args) {
            setLoadingState();
        });

        jQuery(document.body).on('updated_checkout', function (event, args) {
            jQuery('input[name="payment_method"]').change(function(){
                usingPaymentGateway();
            });
            currentCountry = jQuery('#billing_country').length ? jQuery('#billing_country').val() : 'UA';
            selectShipping();
            disableDefaultBillingFieldsforup();
            unsetLoadingState();
        });
        initialize();
    });

    // Postcode number validation for Ukraine
    jQuery('body').on('blur change', '#ukrposhta_shippping_warehouse', function(){
        var billing_country = jQuery('#billing_country').val();
        if ( billing_country == 'UA') {
            var wrapper_warehouse = jQuery(this).closest('.form-row');
            var field_warehouse = jQuery(this).val();
            if( /^\d{5}$/.test( field_warehouse ) ) { // check if contains 5 postcode numbers
                wrapper_warehouse.addClass('woocommerce-validated'); // success
            } else {
                wrapper_warehouse.addClass('woocommerce-invalid'); // error
            }
        }
    });

    // City name number validation
    jQuery('body').on('blur change', '#ukrposhta_shippping_city', function(){
        var wrapper_city = jQuery(this).closest('.form-row');
        var field_city = jQuery(this).val();
        if( /\d/.test( field_city ) || field_city.length < 2 ) { // check if contains at least one number
            wrapper_city.addClass('woocommerce-invalid'); // error
        } else {
            wrapper_city.addClass('woocommerce-validated'); // success
        }
    });

    let autoSelectCityPo = function() {
    // Select2 for 'Поштовий код Відділення' field on Checkout page
    let upPostcodeEl = jQuery('#ukrposhta_shippping_postcode_select');
        if (!upPostcodeEl.hasClass("select2-hidden-accessible")) {
            jQuery('#ukrposhta_shippping_postcode_select').selectWoo({
                language: {
                    noResults: function (params) {
                        return "Поштових відділень не знайдено.";
                    }
                },
                templateSelection: function (data, container) {
                    // Add custom attributes to the <option> tag for the selected option
                    jQuery(data.element).attr('data-custom-attribute', data.customValue);
                    return data.text;
                }
            });
        }

    // Autocomplete for 'Населений пункт Отримувача' field on Checkout
    jQuery('#ukrposhta_shippping_city_select').autocomplete({

    source: function(request, response) { // Get city data from API-УП

      if(request.term.length < 3){

        var lang_site = jQuery('html').attr('lang');

        var data = [
            { label: 'Вінниця, Вінницький район', value: '1057' },
            { label: 'Дніпро, Дніпровський район', value: '3641' },
            { label: 'Донецьк, Донецький район', value: '5601' },
            { label: 'Житомир, Житомирський район', value: '6708' },
            { label: 'Запоріжжя, Запорізький район', value: '8968' },
            { label: 'Івано-Франківськ, Івано-Франківський район', value: '9826' },
            { label: 'Київ, Київ район', value: '29713' },
            { label: 'Кропивницький, Кропивницький район', value: '12069' },
            { label: 'Луганськ, Луганський район', value: '12870' },
            { label: 'Луцьк, Луцький район', value: '3477' },
            { label: 'Львів, Львівський район', value: '14288' },
            { label: 'Миколаїв, Миколаївський район', value: '16169' },
            { label: 'Одеса, Одеський район', value: '17069' },
            { label: 'Полтава, Полтавський район', value: '19234' },
            { label: 'Рівне, Рівненський район', value: '20296' },
            { label: 'Суми, Сумський район', value: '21680' },
            { label: 'Тернопіль, Тернопільський район', value: '22662' },
            { label: 'Ужгород, Ужгородський район', value: '8553' },
            { label: 'Харків, Харківський район', value: '24550' },
            { label: 'Херсон, Херсонський район', value: '25448' },
            { label: 'Хмельницький, Хмельницький район', value: '26481' },
            { label: 'Черкаси, Черкаський район', value: '27760' },
            { label: 'Чернівці, Чернівецький район', value: '28188' },
            { label: 'Чернігів, Чернігівський район', value: '29712' }
          ];

        response(data);
      }
      else{
        jQuery.ajax({
          method: 'POST',
          url: morkva_ukrposhta_globals.ajaxUrl,
          dataType: 'json',
          data: {
            term: request.term,
            action: 'city_autocomplete',
            mrkvup_city_suggestion: this.value,
            mrkvupnonce: morkva_ukrposhta_globals.mrkvupnonce
          },
          success: function(data) {
            response(data);
            // Get 'Поштовий код Відділенн' field CSS-width
            let cityInputWidth = jQuery('#nova-poshta-shipping-info').width();
            jQuery('.ui-autocomplete').css('width', cityInputWidth+'px');
          },
              error: function(xhr, status, error) {
                  jQuery('#ukrposhta_shippping_fields').addClass('mrkvup-loading');
                  console.log(xhr.responseText);
                  alert(xhr.responseText);
              },
        });
      }
    },
    select: function(event, ui) { // After city name selected
      jQuery(this).val( ui.item.label );
      jQuery( "#ukrposhta_shippping_city_selected_id" ).val( ui.item.value );
      let cityid = ui.item.value;
      ui.item.value = ui.item.label

        jQuery.ajax({ // Get postoffice data from API-УП
          method: 'POST',
          url: '/wp-admin/admin-ajax.php',
          data: {
            action: 'morkva_ukrposhta_load_postcodes',
            mrkvup_cityid: cityid,
            mrkvupnonce: morkva_ukrposhta_globals.mrkvupnonce
          },
          dataType: 'json',
            beforeSend: function() {
                if (upPostcodeEl.length && upPostcodeEl.is(":visible")) {
                    upPostcodeEl.find('option:not(:first-child)').remove();
                    jQuery('#ukrposhta_shippping_fields').addClass('mrkvup-loading');
                }
            },
          success: function (data) {

            jQuery('#ukrposhta_shippping_fields').removeClass('mrkvup-loading'); // Add spinner
            // Add postoffice names to Select2 field
            if (null === data || undefined === data) {
              jQuery('#ukrposhta_shippping_fields').removeClass('mrkvup-loading');
              jQuery('#ukrposhta_shippping_postcode_select')
                .append(jQuery('<option value="">Немає відділень</option>'));
            } else {
              jQuery.each(data, function(key, value) {
                jQuery('#ukrposhta_shippping_postcode_select')
                  .append(jQuery("<option></option>")
                    .attr("value", key)
                    .text(value)
                  );
              });
              jQuery('#ukrposhta_shippping_fields').removeClass('mrkvup-loading'); // Remove spinner
              jQuery("#ukrposhta_shippping_postcode_select").change (function () {
                let selectedWarehouse = jQuery('#select2-ukrposhta_shippping_postcode_select-container').attr('title');
                let selectedPostcode = selectedWarehouse.substring(0,5);

                if ( jQuery('#ukrposhta_shippping_postcode_selected').length ) {
                  jQuery('#ukrposhta_shippping_postcode_selected').remove();
                }

                jQuery('#ukrposhta_shippping_postcode_select_field')
                  .append(jQuery('<input type="hidden" id="ukrposhta_shippping_postcode_selected" name="ukrposhta_shippping_postcode_selected" >'));
                let hiddenPostcodeVal = isNaN(selectedPostcode) ? '' : selectedPostcode;
                jQuery('#ukrposhta_shippping_postcode_selected').val(hiddenPostcodeVal);
                Cookies.set('ukrposhta_shippping_postcode_selected', hiddenPostcodeVal );
                jQuery('body').trigger('update_checkout');
              });
            }
          },
          error: function(xhr, status, error) {
            console.log(xhr.responseText);
            jQuery('#ukrposhta_shippping_fields').removeClass('mrkvup-loading'); // Remove spinner
          },
        });
      },
      minLength: 0,
      delay: 0,
    }).focus(function(){            
            // As noted by Jonny in his answer, with newer versions use uiAutocomplete
            jQuery(this).data("uiAutocomplete").search(jQuery(this).val());
        });
  } // let autoSelectCityPo = function() {

    let curShippingMethod = getCurrentShippingMethod();
    if (curShippingMethod && curShippingMethod.indexOf('ukrposhta_shippping') >= 0) {
        autoSelectCityPo();
    }

    jQuery(document.body).on('updated_checkout', function (event, args) {
        var curShippingMethod_new = getCurrentShippingMethod();

        if (curShippingMethod_new && curShippingMethod_new.indexOf('ukrposhta_shippping') >= 0) {
          if (document.body.classList.contains('mrkvnp-plugin-is-active')) {
            // If PRO-НП is active on the site
            jQuery('#billing_nova_poshta_region').attr('disabled', 'disabled').closest('.form-row').hide();
            jQuery('#billing_nova_poshta_city').attr('disabled', 'disabled').closest('.form-row').hide();
            jQuery('#billing_nova_poshta_warehouse').attr('disabled', 'disabled').closest('.form-row').hide();
            jQuery('#billing_mrkvnp_street').attr('disabled', 'disabled').closest('.form-row').hide();
            jQuery('#billing_mrkvnp_house').attr('disabled', 'disabled').closest('.form-row').hide();
            jQuery('#billing_mrkvnp_flat').attr('disabled', 'disabled').closest('.form-row').hide();
            jQuery('#billing_mrkvnp_patronymics').attr('disabled', 'disabled').closest('.form-row').hide();
          }
            autoSelectCityPo();
        }
        if (curShippingMethod_new && curShippingMethod_new.indexOf('ukrposhta_address_shippping') >= 0) {
          Cookies.set('ukrposhta_address_shippping_postcode', jQuery('#billing_postcode').val() );
          jQuery('#billing_state').prop("disabled", false);
          jQuery('#billing_address_1').prop("disabled", false);
          jQuery('#billing_address_2').prop("disabled", false);
          jQuery('#billing_city').prop("disabled", false);
          jQuery('#billing_postcode').prop("disabled", false);
          jQuery('#billing_up_address_surname_field').show();
          jQuery('#shipping_up_address_surname_field').show();
        }
        else
        {
          jQuery('#billing_up_address_surname_field').hide();
          jQuery('#shipping_up_address_surname_field').hide();
        }
    });

})(jQuery); // (function (jQuery)
