<?php
# Get plugin data
require_once ABSPATH . 'wp-admin/includes/plugin.php';
$plugData = get_plugin_data(MRKV_UA_SHIPPING_PLUGIN_FILE,false, false);

# Constans 

# Directories
define('MRKV_UA_SHIPPING_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('MRKV_UA_SHIPPING_PLUGIN_PATH_TEMP', plugin_dir_path(__FILE__) . 'templates');
define('MRKV_UA_SHIPPING_PLUGIN_PATH_SHIP', plugin_dir_path(__FILE__) . 'classes/shipping_methods');

# Links
define('MRKV_UA_SHIPPING_PLUGIN_DIR', plugin_dir_url(__FILE__));
define('MRKV_UA_SHIPPING_ASSETS_URL', plugin_dir_url(__FILE__) . 'assets');
define('MRKV_UA_SHIPPING_IMG_URL', plugin_dir_url(__FILE__) . 'assets/images');

# Data
define('MRKV_UA_SHIPPING_PLUGIN_VERSION', $plugData['Version']);
define('MRKV_UA_SHIPPING_PLUGIN_TEXT_DOMAIN', 'mrkv-ua-shipping');