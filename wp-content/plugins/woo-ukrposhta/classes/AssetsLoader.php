<?php

namespace deliveryplugin\Ukrposhta\classes;

if ( ! defined('ABSPATH')) {
  exit;
}

class AssetsLoader
{
  public function __construct()
  {
    add_action('admin_enqueue_scripts', [ $this, 'loadAdminAssets' ]);
    add_action('admin_enqueue_scripts', [ $this, 'injectGlobals' ]);
    add_action('wp_enqueue_scripts', [ $this, 'injectGlobals' ]);
  }

  public function loadAdminAssets()
  {
    wp_enqueue_script( 'wp-color-picker' );
    wp_enqueue_style( 'wp-color-picker' );

    wp_enqueue_style(
      'morkva_ukrposhta_admin_css',
      MORKVA_UKRPOSHTA_PLUGIN_URL . 'assets/css/admin.min.css',
      [],
      filemtime(MORKVA_UKRPOSHTA_PLUGIN_DIR . 'assets/css/admin.min.css')
    );


  }

  public function injectGlobals()
  {
  
    /*$requestHandler = get_transient('morkva_ukrposhta_request_handler');

    if ($requestHandler === false) {
      $requestHandler = 'rest';
    }

    if ($requestHandler === 'rest') {
      $routerScript = 'assets/js/rest-router.js';
    }
    else {
      $routerScript = 'assets/js/ajax-router.js';
    }*/



  }
}
