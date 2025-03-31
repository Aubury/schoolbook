<?php

namespace deliveryplugin\Ukrposhta\classes;

use deliveryplugin\Ukrposhta\Http\ukrPoshtaAjax;

if ( ! defined('ABSPATH')) {
  exit;
}

final class UkrposhtaShipping
{
  private static $instance = null;

  private $activator;
  private $assetsLoader;
  private $optionsPage;

  private function __construct()
  {
    $this->activator = new Activator();
    $this->assetsLoader = new AssetsLoader();
  }

  private function __clone() { }
  public function __wakeup() { }

  public static function instance()
  {
    if ( ! self::$instance) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  public function __get($name)
  {
    return $this->$name;
  }
}
