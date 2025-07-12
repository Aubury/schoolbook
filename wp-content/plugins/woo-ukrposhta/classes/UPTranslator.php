<?php

namespace deliveryplugin\Ukrposhta\classes;

class UPTranslator
{
  private $areaTranslates = [
  ];

  /**
   * @return array
   */
  public function getTranslates()
  {
    return apply_filters('morkva_ukrposhta_get_ukr_poshta_translates', [
      'method_title' => morkva_ukrposhta_get_option('morkva_ukrposhta_up_method_title'),
      'block_title' => morkva_ukrposhta_get_option('morkva_ukrposhta_up_block_title'),
      'placeholder_area' => morkva_ukrposhta_get_option('morkva_ukrposhta_up_placeholder_area'),
      'placeholder_city' => morkva_ukrposhta_get_option('morkva_ukrposhta_up_placeholder_city'),
      'placeholder_warehouse' => morkva_ukrposhta_get_option('morkva_ukrposhta_up_placeholder_warehouse'),
      'address_title' => morkva_ukrposhta_get_option('morkva_ukrposhta_up_address_title'),
      'address_placeholder' => morkva_ukrposhta_get_option('morkva_ukrposhta_up_address_placeholder')
    ]);
  }

  public function translateAreas($areas)
  {
    if (apply_filters('morkva_ukrposhta_language', get_option('morkva_ukrposhta_up_lang', 'ru')) === 'ru') {
      foreach ($areas as &$area) {
        if (isset($this->areaTranslates[ $area['ref'] ])) {
          $area['description'] = $this->areaTranslates[ $area['ref'] ];
        }
      }
    }

    return $areas;
  }
}
