<?php
# Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 

# Check if class exist
if (!class_exists('MRKV_UA_SHIPPING_METHODS_AUTOCREATE'))
{
	/**
	 * Class for setup shipping methods autocreate invoice
	 */
	class MRKV_UA_SHIPPING_METHODS_AUTOCREATE
	{
		/**
		 * Constructor for plugin shipping methods autocreate invoice
		 * */
		function __construct()
		{
			# Add function to order status change
			add_action('woocommerce_order_status_changed', array($this, 'mrkv_ua_ship_auto_create_invoice'), 98, 3);

			# Add function to order create
			add_action('woocommerce_checkout_order_processed', array($this, 'mrkv_ua_ship_create_ttn_pending'), 10, 1);
		}

		/**
		 * Do autocreate invoice for pending order
		 * */
		public function mrkv_ua_ship_create_ttn_pending($order_id)
		{
			# Get order
			$order = wc_get_order($order_id);

			# Check status on pending
			if($order->get_status() == 'pending')
			{
				# Autoreate invoice
				$this->mrkv_autocreate_invoice($order, 'pending');
			}
		}

		/**
		 * Autoreate invoice main
		 * @var string Order ID
		 * @var string Old Status
		 * @var string New status
		 * */
		public function mrkv_ua_ship_auto_create_invoice($order_id, $old_status, $new_status)
		{
			# Get order
			$order = wc_get_order($order_id);

			# Autoreate invoice
			$this->mrkv_autocreate_invoice($order, $new_status);
		}

		/**
		 * Autoreate invoice
		 * @param object Order
		 * */
		public function mrkv_autocreate_invoice($order, $new_status)
		{
			# Get invoice number
			$invoice_number = $order->get_meta('mrkv_ua_ship_invoice_number');

			# Check invoice
			if(!$invoice_number)
			{
				$keys_shipping = array_keys(MRKV_UA_SHIPPING_LIST);
	    		$key = '';
	    		$current_shipping = '';

	    		# Get key and shipping method
	            foreach($order->get_shipping_methods() as $shipping)
	            {
	            	foreach($keys_shipping as $key_ship)
					{
						$current_shipping = $shipping->get_method_id();

						# Check method by mrkv
						if(str_contains($shipping->get_method_id(), $key_ship))
						{
							$key = $key_ship;
						}
					}
	            }

	            # Check key and shipping method
	            if($key && $current_shipping)
	            {
	            	# Get settings 
	            	$settings_shipping = get_option($key . '_m_ua_settings');

	            	# Check settings automation
	            	if(isset($settings_shipping['automation']['autocreate']['enabled']) && $settings_shipping['automation']['autocreate']['enabled'] == 'on')
	            	{
	            		# Get creator
	            		require_once MRKV_UA_SHIPPING_PLUGIN_PATH . 'classes/shipping_methods/' . $key . '/invoice/mrkv-ua-shipping-' . $key . '-autoinvoice.php';
	            		$auto_creator = new MRKV_UA_SHIPPING_METHODS_AUTOINVOICE($settings_shipping, $key, $order, $new_status);

	            		# Create invoice
	            		$auto_creator->create_auto_invoice();
	            	}
	            }
			}
		}
	}
}