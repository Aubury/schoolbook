<?php

namespace deliveryplugin\Ukrposhta\Http;

use deliveryplugin\Ukrposhta\Api\ukrPoshtaApi;
use deliveryplugin\Ukrposhta\classes\UPTranslator;
use deliveryplugin\Ukrposhta\DB\ukrPoshtaRepository;
use deliveryplugin\Ukrposhta\DB\OptionsRepository;
use deliveryplugin\Ukrposhta\Validators\OptionsValidator;

class ukrPoshtaRest
{
  private $api;
  private $ukrPoshtaRepository;
  private $optionsRepository;

  public function __construct()
  {
    $this->api = new ukrPoshtaApi();
    $this->ukrPoshtaRepository = new ukrPoshtaRepository();
    $this->optionsRepository = new OptionsRepository();

    add_action('rest_api_init', [ $this, 'initRoutes' ]);
  }

  public function initRoutes()
  {
    // Test route (need to check if REST active)
    register_rest_route( 'wc-ukrposhta/v1', 'test', [
      'callback' => function (\WP_REST_Request $request) {
        set_transient('morkva_ukrposhta_request_handler', 'rest', 3600 * 24);

        return Response::make('success');
      }
    ]);

    // Options Save
    register_rest_route( 'wc-ukrposhta/v1', 'settings', [
      'methods' => 'POST',
      'callback' => [ $this, 'saveSettings' ],
      'permission_callback' => [ $this, 'checkPermission' ]
    ]);

    // Options Areas Load to DB
    register_rest_route( 'wc-ukrposhta/v1', 'db/areas/load', [
      'methods' => 'POST',
      'callback' => [ $this, 'loadAreas' ],
      'permission_callback' => [ $this, 'checkPermission' ]
    ]);

    // Options Cities load to DB
    register_rest_route( 'wc-ukrposhta/v1', 'db/cities/load', [
      'methods' => 'POST',
      'callback' => [ $this, 'loadCities' ],
      'permission_callback' => [ $this, 'checkPermission' ]
    ]);

    // Options Warehouses load to DB
    register_rest_route( 'wc-ukrposhta/v1', 'db/warehouses/load', [
      'methods' => 'POST',
      'callback' => [ $this, 'loadWarehouses' ],
      'permission_callback' => [ $this, 'checkPermission' ]
    ]);

    // Frontend Areas
    register_rest_route( 'morkva_ukrposhta/v1', 'ukrposhta/area', [
      'callback' => [ $this, 'getAreas' ]
    ]);

    // Frontend Cities
    register_rest_route( 'morkva_ukrposhta/v1', 'ukrposhta/cities/(?P<ref>[^\/]*)', [
      'callback' => [ $this, 'getCities' ]
    ]);

    // Frontend Warehouses
    register_rest_route( 'morkva_ukrposhta/v1', 'ukrposhta/warehouses/(?P<ref>[^\/]*)', [
      'callback' => [ $this, 'getWarehouses' ]
    ]);
  }

  public function saveSettings(\WP_REST_Request $request)
  {
    $validator = new OptionsValidator();
    $result = $validator->validateRequest($_POST);

    if ($result !== true) {
      return Response::make('error', [
        'errors' => $result
      ]);
    }

    $this->optionsRepository->save($_POST);

    return Response::make('success', [
      'api_key' => get_option('morkva_ukrposhta_up_api_key', ''),
      'message' => 'Настройки успешно сохранены'
    ]);
  }

  public function getAreas(\WP_REST_Request $request)
  {
    try {
      $areas = $this->ukrPoshtaRepository->getAreas();
      $upAreaTranslator = new UPTranslator();

      return Response::make('success', $upAreaTranslator->translateAreas($areas));
    }
    catch (\Error $e) {
      return Response::make('error', $e->getMessage());
    }
  }

  public function getCities(\WP_REST_Request $request)
  {
    try {
      $cities = $this->ukrPoshtaRepository->getCities($request['ref']);

      return Response::make('success', $cities);
    }
    catch (\Error $e) {
      return Response::make('error', $e->getMessage());
    }
  }

  public function getWarehouses(\WP_REST_Request $request)
  {
    try {
      $warehouses = $this->ukrPoshtaRepository->getWarehouses($request['ref']);

      return Response::make('success', $warehouses);
    }
    catch (\Error $e) {
      return Response::make('error', $e->getMessage());
    }
  }

  public function loadAreas()
  {
    $result = $this->api->getAreas();

    if ($result['success']) {
      $this->ukrPoshtaRepository->saveAreas($result['data']);

      return Response::make('success');
    }

    return Response::make('error', [
      'errors' => $result['errors']
    ]);
  }

  public function loadCities()
  {
    $result = $this->api->getCities((int)$_POST['page']);

    if ($result['success']) {
      $this->ukrPoshtaRepository->saveCities($result['data'], (int)$_POST['page']);

      return Response::make('success', [
        'loaded' => count($result['data']) === 0
      ]);
    }

    return Response::make('error', [
      'errors' => $result['errors']
    ]);
  }

  public function loadWarehouses()
  {
    $result = $this->api->getWarehouses((int)$_POST['page']);

    if ($result['success']) {
      $this->ukrPoshtaRepository->saveWarehouses($result['data'], (int)$_POST['page']);

      return Response::make('success', [
        'loaded' => count($result['data']) === 0
      ]);
    }

    return Response::make('error', [
      'errors' => $result['errors']
    ]);
  }

  public function checkPermission(\WP_REST_Request $request)
  {
    return current_user_can('manage_options');
  }
}