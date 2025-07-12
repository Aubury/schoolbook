<?php

namespace deliveryplugin\Ukrposhta\classes;

class ukrPoshtaRepository
{
  public function getAreas()
  {
    global $wpdb;

    return $wpdb->get_results("SELECT * FROM morkva_ukrposhta_up_areas", ARRAY_A);
  }

  public function getCities($areaRef)
  {
    global $wpdb;

    return $wpdb->get_results(
        $wpdb->prepare(
            "SELECT *
            FROM morkva_ukrposhta_up_cities
            WHERE area_ref = %s
            ORDER BY description",
            $areaRef
        ),
        ARRAY_A
    );
  }

  public function getWarehouses($cityRef)
  {
    global $wpdb;

    return $wpdb->get_results(
        $wpdb->prepare(
            "SELECT *
            FROM morkva_ukrposhta_up_warehouses
            WHERE city_ref = %s
            ORDER BY number ASC",
            $cityRef
        ),
        ARRAY_A
    );
  }

  public function saveAreas($areas)
  {
    global $wpdb;

    $wpdb->query("TRUNCATE morkva_ukrposhta_up_areas");

    foreach ($areas as $area) {
      $wpdb->insert(
          'morkva_ukrposhta_up_areas', 
          array( 
              'ref' => $area['Ref'],
              'description' => $area['Description']
          ),
          array( '%s', '%s' ) 
      );
    }
  }

  public function saveCities($cities, $page)
  {
    global $wpdb;

    if ($page === 1) {
      $wpdb->query("TRUNCATE morkva_ukrposhta_up_cities");
    }

    foreach ($cities as $city) {
      $wpdb->insert(
        'morkva_ukrposhta_up_cities',
        array(
            'ref' => $city['Ref'],
            'description' => $city['Description'],
            'description_ru' => $city['DescriptionRu'],
            'area_ref' => $city['Area']
        ),
        array( '%s', '%s', '%s', '%s' )
    );
    }
  }

  public function saveWarehouses($warehouses, $page)
  {
    global $wpdb;

    if ($page === 1) {
      $wpdb->query("TRUNCATE morkva_ukrposhta_up_warehouses");
    }

    foreach ($warehouses as $warehouse) {
      $wpdb->insert(
        'morkva_ukrposhta_up_warehouses', 
        array( 
            'ref' => $warehouse['Ref'],
            'description' => $warehouse['Description'],
            'description_ru' => $warehouse['DescriptionRu'],
            'city_ref' => $warehouse['CityRef'],
            'number' => (int)$warehouse['Number']
        ),
        array( '%s', '%s', '%s', '%s', '%d' ) 
    );
    }
  }

  public function dropTables()
  {
  	global $wpdb;

  	$wpdb->query("DROP TABLE IF EXISTS morkva_ukrposhta_up_areas");
	  $wpdb->query("DROP TABLE IF EXISTS morkva_ukrposhta_up_cities");
	  $wpdb->query("DROP TABLE IF EXISTS morkva_ukrposhta_up_warehouses");
  }
}
