<?php

namespace deliveryplugin\Ukrposhta\classes;

if ( ! defined('ABSPATH')) {
	exit;
}

class ukrPoshtaApiLoader
{
	private $api;

	public function __construct($api)
	{
		$this->api = $api;
	}

	public function loadAreas()
	{
		$result = $this->api->getAreas();

		if ($result['success']) {
			global $wpdb;

			foreach ($result['data'] as $area) {
				$ref = sanitize_text_field( $area['Ref'] );
				$description = sanitize_textarea_field( $area['Description'] );

				$wpdb->insert(
				    $wpdb->prefix . 'ukrposhta_up_areas', 
				    array( 
				        'Ref' => $ref,
				        'Description' => $description
				    ),
				    array( '%s', '%s' )
				);
			}

			return true;
		}

		return false;
	}

	public function loadCities()
	{
		$result = $this->api->getCities();

		if ($result['success']) {
			global $wpdb;

			foreach ($result['data'] as $city) {
				$ref = sanitize_text_field( $city['Ref'] );
				$description = sanitize_textarea_field( $city['Description'] );
				$area = sanitize_text_field( $city['Area'] );

				$wpdb->insert(
				    $wpdb->prefix . 'ukrposhta_up_cities',  
				    array( 
				        'Ref' => $ref,
				        'Description' => $description,
				        'Area' => $area
				    ),
				    array( '%s', '%s', '%s' ) 
				);
			}

			return true;
		}

		return false;
	}

  public function loadWarehouses()
	{
		$result = $this->api->getWarehouses();

		if ($result['success']) {
      		global $wpdb;

			foreach ($result['data'] as $warehouse) 
			{
				$ref = sanitize_text_field( $warehouse['Ref'] );
				$description = sanitize_textarea_field( $warehouse['Description'] );
				$city_ref = sanitize_text_field( $warehouse['CityRef'] );

				$wpdb->insert(
				    $wpdb->prefix . 'ukrposhta_up_warehouses',
				    array( 
				        'Ref' => $ref,
				        'Description' => $description,
				        'CityRef' => $city_ref
				    ),
				    array( '%s', '%s', '%s' )
				);
			}

			return true;
		}

		return false;
	}
}