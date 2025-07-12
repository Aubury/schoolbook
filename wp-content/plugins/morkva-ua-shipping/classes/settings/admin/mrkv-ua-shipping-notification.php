<?php
# Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 

# Check if class exist
if (!class_exists('MRKV_UA_SHIPPING_NOTIFICATION'))
{
	/**
	 * Class for setup plugin admin notification
	 */
	class MRKV_UA_SHIPPING_NOTIFICATION
	{
		/**
		 * Constructor for plugin admin notification
		 * */
		function __construct()
		{
			add_action('admin_init', array($this, 'mrkv_check_api_status_once_per_day'));
			add_action('admin_notices', array($this, 'mrkv_show_api_error_notice'));
		}

		public function mrkv_check_api_status_once_per_day()
		{
			$m_ua_active_plugins = get_option('m_ua_active_plugins');

			if(isset($m_ua_active_plugins['nova-poshta']['enabled']) && $m_ua_active_plugins['nova-poshta']['enabled'] == 'on')
			{
				$last_check_np = get_option('mrkv_api_last_check_np');
	    		$api_fixed_np = get_option('mrkv_api_fixed_np');

	    		if ($api_fixed_np || !$last_check_np || (time() - $last_check_np) > DAY_IN_SECONDS) {
			        $api_working = $this->mrkv_is_api_working('nova-poshta');

			        if ($api_working) {
			            update_option('mrkv_api_fixed_np', false); 
			        } else {
			            update_option('mrkv_api_fixed_np', true); 
			        }

			        update_option('mrkv_api_last_check_np', time()); 
			    }
			}
		}

		public function mrkv_is_api_working($key_shipping)
		{
			require_once MRKV_UA_SHIPPING_PLUGIN_PATH . 'classes/shipping_methods/' . $key_shipping . '/api/mrkv-ua-shipping-api-' . $key_shipping . '.php';
			$mrkv_object_api = new MRKV_UA_SHIPPING_API_NOVA_POSHTA(get_option($key_shipping . '_m_ua_settings'));

			if(is_string($mrkv_object_api->active_api))
			{
				return false;
			}
			
			return true;
		}

		public function mrkv_show_api_error_notice()
		{
			$api_fixed = get_option('mrkv_api_fixed_np');

		    if ($api_fixed) {
		        echo '<div class="notice notice-error is-dismissible">
		                <p>' . __('Your Nova Poshta API key needs to be updated so that the branches are pulled up on the Checkout page, update the API key in your <a href="http://my.novaposhta.ua/" target="blanc">Nova Poshta account</a> and save it in <a href="', 'mrkv-ua-shipping') . esc_url(admin_url('admin.php?page=mrkv_ua_shipping_nova-poshta')) . __('">the settings</a>.', 'mrkv-ua-shipping') .'</p>
		              </div>';
		    }
		}
	}
}