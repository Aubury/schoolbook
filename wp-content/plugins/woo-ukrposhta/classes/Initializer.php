<?php

namespace deliveryplugin\Ukrposhta\classes;

if ( ! defined('ABSPATH')) {
  exit;
}

class Initializer
{
  public function __construct()
  {
    if (defined('morkva_ukrposhta_ERROR_MEMORY')) {
      return;
    }

    if ( ! Activator::isActualDb()) {
      Activator::setPluginState('updating');
    }

    add_action('admin_init', function () {
      $this->checkDBVersion();
    });

    add_action('init', [$this, 'routeActivationPage']);

    new ukrPoshtaAjaxHandler();
    new ukrPoshtaRest();
  }

  public function routeActivationPage()
  {
    if ( ! current_user_can('administrator') || Activator::isPluginActivated()) {
      return;
    }

    if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] === '/wc-ukrposhta/activation') {
      wp_enqueue_style('wc-ukrposhta-css', plugin_dir_url(__DIR__) . 'assets/css/style.min.css', array(), filemtime(plugin_dir_path(__DIR__) . 'assets/css/style.min.css'));
      wp_enqueue_script('jquery');

      $data['ajax_url'] = admin_url('admin-ajax.php');

      echo esc_html(View::render('activation', esc_html($data)));
      exit;
  }
  }

  private function checkDBVersion()
  {
    if ( ! Activator::isActualDb()) {
      Activator::setPluginState('updating');
      $this->updateDB();

      wp_redirect(home_url('wc-ukrposhta/activation'), 301);
      exit;
    }
  }

  private function updateDB()
  {
    global $wpdb;

    $wpdb->query("DROP TABLE IF EXISTS morkva_ukrposhta_up_areas");
    $wpdb->query("DROP TABLE IF EXISTS morkva_ukrposhta_up_cities");
    $wpdb->query("DROP TABLE IF EXISTS morkva_ukrposhta_up_warehouses");

    // Get the charset and collation for the current WordPress database
    $collate = $wpdb->get_charset_collate();

    // Prepare and run queries for table creation
    $wpdb->query(
        $wpdb->prepare(
            "CREATE TABLE {$wpdb->prefix}morkva_ukrposhta_up_areas (
                ref varchar(36) NOT NULL,
                description varchar(255) NOT NULL
            ) %s",
            $collate
        )
    );

    $wpdb->query(
        $wpdb->prepare(
            "CREATE TABLE {$wpdb->prefix}morkva_ukrposhta_up_cities (
                ref varchar(36) NOT NULL,
                description varchar(255) NOT NULL,
                area_ref varchar(36)
            ) %s",
            $collate
        )
    );

    $wpdb->query(
        $wpdb->prepare(
            "CREATE TABLE {$wpdb->prefix}morkva_ukrposhta_up_warehouses (
                ref varchar(36) NOT NULL,
                description varchar(255) NOT NULL,
                city_ref varchar(36)
            ) %s",
            $collate
        )
    );

    update_option('morkva_ukrposhta_db_version', morkva_ukrposhta_DB_VERSION);
  }
}