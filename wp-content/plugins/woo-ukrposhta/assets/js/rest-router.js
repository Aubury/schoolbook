'use strict';

(function ($) {

  window.WCUkrShippingRouter = {

    saveOptions: function (options) {
      $.ajax({
        method: 'POST',
        url: morkva_ukrposhta_globals.homeUrl + '/wp-json/wc-ukrposhta/v1/settings',
        data: options.data,
        dataType: 'json',
        success: function (json) {
          options.success(json);
        },
        error: function (xhr) {
          console.log(xhr);
        },
        complete: function() {
          options.complete();
        }
      });
    },

    loadAreas: function (options) {
      $.ajax({
        method: 'POST',
        url: morkva_ukrposhta_globals.homeUrl + '/wp-json/wc-ukrposhta/v1/db/areas/load',
        data: options.data,
        dataType: 'json',
        success: function (json) {
          options.success(json);
        }
      });
    },

    loadCities: function (options) {
      $.ajax({
        method: 'POST',
        url: morkva_ukrposhta_globals.homeUrl + '/wp-json/wc-ukrposhta/v1/db/cities/load',
        data: options.data,
        dataType: 'json',
        success: function (json) {
          options.success(json);
        }
      });
    },

    loadWarehouses: function (options) {
      $.ajax({
        method: 'POST',
        url: morkva_ukrposhta_globals.homeUrl + '/wp-json/wc-ukrposhta/v1/db/warehouses/load',
        data: options.data,
        dataType: 'json',
        success: function (json) {
          options.success(json);
        }
      });
    },

    getAreas: function (options) {
      $.ajax({
        method: 'GET',
        url: morkva_ukrposhta_globals.homeUrl + '/wp-json/morkva_ukrposhta/v1/ukrposhta/area',
        dataType: 'json',
        success: function (json) {
          options.success(json);
        }
      });
    },

    getCities: function (options) {
      $.ajax({
        method: 'GET',
        url: morkva_ukrposhta_globals.homeUrl + '/wp-json/morkva_ukrposhta/v1/ukrposhta/cities/' + options.areaRef,
        dataType: 'json',
        success: function (json) {
          options.success(json);
        }
      });
    },

    getWarehouses: function (options) {
      $.ajax({
        method: 'GET',
        url: morkva_ukrposhta_globals.homeUrl + '/wp-json/morkva_ukrposhta/v1/ukrposhta/warehouses/' + options.cityRef,
        dataType: 'json',
        success: function (json) {
          options.success(json);
        }
      });
    }

  };

})(jQuery);