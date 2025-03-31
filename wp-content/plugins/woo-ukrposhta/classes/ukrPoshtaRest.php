<?php

namespace deliveryplugin\Ukrposhta\classes;

class ukrPoshtaRest
{
  public function __construct()
  {
    add_action('rest_api_init', [ $this, 'initRoutes' ]);
  }

  public function initRoutes()
  {
    register_rest_route( 'morkva_ukrposhta/v1', 'ukrposhta/area', [
      'callback' => [ $this, 'getAreas' ]
    ]);

    register_rest_route( 'morkva_ukrposhta/v1', 'ukrposhta/cities/(?P<ref>[^\/]*)', [
      'callback' => [ $this, 'getCities' ]
    ]);

    register_rest_route( 'morkva_ukrposhta/v1', 'ukrposhta/warehouses/(?P<ref>[^\/]*)', [
      'callback' => [ $this, 'getWarehouses' ]
    ]);
  }

  public function getAreas(\WP_REST_Request $request)
  {
    try {
      global $wpdb;

      $upAreaTranslator = new UPAreaTranslator();
      $areas = $wpdb->get_results("SELECT * FROM morkva_ukrposhta_up_areas", ARRAY_A);

      return [
        'result' => true,
        'data' => $upAreaTranslator->translateAreas($areas)
      ];
    }
    catch (\Error $e) {
      return [
        'result' => false,
        'data' => $e->getMessage()
      ];
    }
  }

  public function getCities(\WP_REST_Request $request)
  {
    try {
      global $wpdb;

      $ref = $request['ref'];
      $cities = $wpdb->get_results("SELECT * FROM morkva_ukrposhta_up_cities WHERE area_ref='" . esc_attr($ref) . "' ORDER BY description", ARRAY_A);

      return [
        'result' => true,
        'data' => $cities
      ];
    }
    catch (\Error $e) {
      return [
        'result' => false,
        'data' => $e->getMessage()
      ];
    }
  }

  public function getWarehouses(\WP_REST_Request $request)
  {
    try {
      global $wpdb;

      $ref = $request['ref'];

      $warehouses = $wpdb->get_results("
        SELECT * 
        FROM morkva_ukrposhta_up_warehouses 
        WHERE city_ref='" . esc_attr($ref) . "' 
        ORDER BY number ASC
      ", ARRAY_A);

      return [
        'result' => true,
        'data' => $warehouses
      ];
    }
    catch (\Error $e) {
      return [
        'result' => false,
        'data' => $e->getMessage()
      ];
    }
  }
}