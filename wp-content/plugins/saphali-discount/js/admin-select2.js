jQuery(function($){
    $('#customer_email_set').select2({
      ajax: {
        url: ajaxurl,
        dataType: 'json',
        delay: 250,
        data: function (params) {
          return {
            action: 'saphali_search_users_by_email',
            q: params.term
          };
        },
        processResults: function (data) {
          return {
            results: data
          };
        },
        cache: true
      },
      placeholder: $('#customer_email_set').data('placeholder'),
      width: 'resolve',
      minimumInputLength: 2,
      multiple: true
    });
  
    $('#customer_cart_club').select2({
      ajax: {
        url: ajaxurl,
        dataType: 'json',
        delay: 250,
        data: function (params) {
          return {
            action: 'saphali_search_cart_club',
            q: params.term
          };
        },
        processResults: function (data) {
          return {
            results: data
          };
        },
        cache: true
      },
      placeholder: $('#customer_cart_club').data('placeholder'),
      width: 'resolve',
      minimumInputLength: 2,
      multiple: true
    });
  });