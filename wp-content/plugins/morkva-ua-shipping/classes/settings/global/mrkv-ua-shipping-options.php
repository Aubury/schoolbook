<?php
# Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 

# Check if class exist
if (!class_exists('MRKV_UA_SHIPPING_OPTIONS'))
{
	/**
	 * Class for setup plugin global options
	 */
	class MRKV_UA_SHIPPING_OPTIONS
	{
		/**
		 * Constructor for plugin global options
		 * */
		function __construct()
		{
			# Register settings
			add_action('admin_init', array($this, 'mrkv_ua_shipping_register_settings'));
		}

		/**
		 * Register plugin options
		 * 
		 * */
	    public function mrkv_ua_shipping_register_settings()
	    {
	    	# List of plugin options
	        $options = array(
	            'm_ua_active_plugins',
	        );

	        # Loop of option
	        foreach ($options as $option) 
	        {
	        	# Register option
	            register_setting('mrkv-ua-shipping-settings-group', $option);
	        }

	        foreach(MRKV_UA_SHIPPING_LIST as $slug => $shipping)
			{
				# List of plugin options
		        $options = array(
		            $slug . '_m_ua_settings',
		        );

		        # Loop of option
		        foreach ($options as $option) 
		        {
		        	# Register option
		            register_setting('mrkv-ua-shipping-' . $slug .'-group', $option);
		        }
			}
	    }
	}
}