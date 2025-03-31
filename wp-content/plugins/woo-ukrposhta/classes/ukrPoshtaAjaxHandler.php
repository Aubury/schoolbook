<?php

namespace deliveryplugin\Ukrposhta\classes;

use deliveryplugin\Ukrposhta\Api\ukrPoshtaApi;

if ( ! defined('ABSPATH')) {
	exit;
}

class ukrPoshtaAjaxHandler
{
  private $apiLoader;

	public function __construct()
	{
	  $this->apiLoader = new ukrPoshtaApiLoader(new ukrPoshtaApi(''));

		// Activation
    add_action('wp_ajax_morkva_ukrposhta_up_load_areas', [ $this, 'apiLoadAreas' ]);
    add_action('wp_ajax_nopriv_morkva_ukrposhta_up_load_areas', [ $this, 'apiLoadAreas' ]);

    add_action('wp_ajax_morkva_ukrposhta_up_load_cities', [ $this, 'apiLoadCities' ]);
    add_action('wp_ajax_nopriv_morkva_ukrposhta_up_load_cities', [ $this, 'apiLoadCities' ]);

    add_action('wp_ajax_morkva_ukrposhta_up_load_warehouses', [ $this, 'apiLoadWarehouses' ]);
    add_action('wp_ajax_nopriv_morkva_ukrposhta_up_load_warehouses', [ $this, 'apiLoadWarehouses' ]);
    // End Activation
	}

	public function apiLoadAreas()
  {
  	$result = $this->apiLoader->loadAreas();

    echo json_encode([
      'result' => $result
    ]);

    wp_die();
  }

  public function apiLoadCities()
  {
  	$result = $this->apiLoader->loadCities();

    echo json_encode([
      'result' => $result
    ]);

    wp_die();
  }

  public function apiLoadWarehouses()
  {
  	$result = $this->apiLoader->loadWarehouses();

    echo json_encode([
      'result' => $result
    ]);

    Activator::setPluginState('activated');

    wp_die();
  }
}