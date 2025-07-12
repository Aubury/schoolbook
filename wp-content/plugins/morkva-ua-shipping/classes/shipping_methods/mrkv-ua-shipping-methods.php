<?php
# Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

# Include ajax shipping
require_once 'mrkv-ua-shipping-methods-ajax.php';
# Include checkout settings shipping
require_once 'mrkv-ua-shipping-methods-checkout.php';  
# Include order settings shipping
require_once 'mrkv-ua-shipping-methods-order.php'; 

# Check if class exist
if (!class_exists('MRKV_UA_SHIPPING_METHODS'))
{
	/**
	 * Class for setup shipping methods 
	 */
	class MRKV_UA_SHIPPING_METHODS
	{
		/**
		 * Constructor for plugin shipping methods
		 * */
		function __construct()
		{
			# Setup woo plugin shipping methods ajax
			new MRKV_UA_SHIPPING_METHODS_AJAX();

			# Load settings page constants
			add_action( 'wp_loaded', array($this, 'get_shipping_admin_settings'));
		}

		/**
		 * Add shippings settings admin page
		 * **/
		public function get_shipping_admin_settings()
		{
			$m_ua_active_plugins = get_option('m_ua_active_plugins');
			
			foreach(MRKV_UA_SHIPPING_LIST as $slug => $shipping)
			{
				if(isset($m_ua_active_plugins[$slug]['enabled']) && $m_ua_active_plugins[$slug]['enabled'] == 'on')
				{
					# Setup woo plugin shipping methods checkout
					new MRKV_UA_SHIPPING_METHODS_CHECKOUT();

					# Setup woo plugin shipping methods order
					new MRKV_UA_SHIPPING_METHODS_ORDER();

					break;
				}
			}
			
			# Check page data
			if(isset($_GET['page']))
			{
				# Loop all shipping
				foreach(MRKV_UA_SHIPPING_LIST as $slug => $shipping)
				{
					# Check if settings shipping page
					if($_GET['page'] == 'mrkv_ua_shipping_' . $slug)
					{
						define('SETTINGS_MRKV_UA_SHIPPING_SLUG', $slug);

						require_once MRKV_UA_SHIPPING_PLUGIN_PATH . 'classes/settings/global/mrkv-ua-shipping-option-fields.php';
						require_once MRKV_UA_SHIPPING_PLUGIN_PATH . 'classes/shipping_methods/' . SETTINGS_MRKV_UA_SHIPPING_SLUG 
							. '/api/mrkv-ua-shipping-api-' . SETTINGS_MRKV_UA_SHIPPING_SLUG . '.php';

						global $mrkv_global_option_generator;
						$mrkv_global_option_generator = new MRKV_UA_SHIPPING_OPTION_FILEDS();
						define('MRKV_OPTION_OBJECT_NAME', SETTINGS_MRKV_UA_SHIPPING_SLUG . '_m_ua_settings');
						define('MRKV_SHIPPING_SETTINGS', get_option(MRKV_OPTION_OBJECT_NAME));
						
						$api_class = MRKV_UA_SHIPPING_LIST[SETTINGS_MRKV_UA_SHIPPING_SLUG]['api_class'];
						global $mrkv_global_shipping_object;
						$mrkv_global_shipping_object = new $api_class(MRKV_SHIPPING_SETTINGS);

						require_once MRKV_UA_SHIPPING_PLUGIN_PATH . 'classes/shipping_methods/' . $slug . '/settings/mrkv-ua-shipping-settings-' . $slug . '.php';

						$shipping_setting_class = MRKV_UA_SHIPPING_LIST[$slug]['settings_class'];

						new $shipping_setting_class();
					}
					elseif(str_contains($_GET['page'], 'mrkv_ua_shipping_' . $slug))
					{
						define('SETTINGS_MRKV_UA_SHIPPING_SLUG', $slug);
						define('SETTINGS_MRKV_UA_PAGE_SLUG', str_replace('mrkv_ua_shipping_' . $slug . '_', "", $_GET['page']));

						if(SETTINGS_MRKV_UA_PAGE_SLUG == 'invoices')
						{
							require_once MRKV_UA_SHIPPING_PLUGIN_PATH . 'classes/shipping_methods/' . SETTINGS_MRKV_UA_SHIPPING_SLUG 
							. '/api/mrkv-ua-shipping-api-' . SETTINGS_MRKV_UA_SHIPPING_SLUG . '.php';

							define('MRKV_OPTION_OBJECT_NAME', SETTINGS_MRKV_UA_SHIPPING_SLUG . '_m_ua_settings');
							define('MRKV_SHIPPING_SETTINGS', get_option(MRKV_OPTION_OBJECT_NAME));
						
							$api_class = MRKV_UA_SHIPPING_LIST[SETTINGS_MRKV_UA_SHIPPING_SLUG]['api_class'];
							global $mrkv_global_shipping_object;
							$mrkv_global_shipping_object = new $api_class(MRKV_SHIPPING_SETTINGS);
						}
					}
				}
			}
		}
	}
}