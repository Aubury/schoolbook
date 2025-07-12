<?php
# Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 

# Check if class exist
if (!class_exists('MRKV_UA_SHIPPING_METHODS_CHECKOUT_VALIDATION'))
{
	/**
	 * Class for setup shipping methods checkout validation
	 */
	class MRKV_UA_SHIPPING_METHODS_CHECKOUT_VALIDATION
	{
		/**
		 * @param array Active shipping list
		 * */
		private $current_shipping;

		/**
		 * @param array Active shipping list
		 * */
		private $current_shipping_global;

		/**
		 * @param array Active shipping list
		 * */
		private $has_mrkv_ua_ship;

		/**
		 * @param array Active shipping list
		 * */
		private $type_shipping;

		/**
		 * Constructor for plugin shipping methods validation
		 * */
		function __construct()
		{	
			$this->current_shipping = '';
			$this->current_shipping_global = '';
			$this->has_mrkv_ua_ship = false; 
			$this->type_shipping = 'billing';

			add_action('woocommerce_checkout_process', array($this, 'mrkv_ua_ship_validate_fields'));
		    add_filter('woocommerce_checkout_fields', array($this, 'mrkv_ua_ship_remove_default_fields_from_validation'));
		}

		/**
		 * Check on disabled fields
		 * 
		 * @return bool Answer
		 * */
		private function mrkv_ua_ship_maybe_disable_default_fields()
		{
			$this->type_shipping = (isset($_POST['ship_to_different_address']) && $_POST['ship_to_different_address']) ? 'shipping' : 'billing';

			if(isset($_POST['shipping_method'][0]))
			{
				$keys = array_keys(MRKV_UA_SHIPPING_LIST);

				foreach($keys as $key)
				{
					if(str_contains($_POST['shipping_method'][0], $key))
					{
						$this->current_shipping_global = $key;
					}
				}

				if($this->current_shipping_global){
					foreach(MRKV_UA_SHIPPING_LIST[$this->current_shipping_global]['method'] as $method_slug => $method_data)
					{
						$clean_shipping_method = preg_replace('/_\d+$/', '', $_POST['shipping_method'][0]);
						
						if($clean_shipping_method == $method_slug)
						{
							$this->current_shipping = $method_slug;
							$this->has_mrkv_ua_ship = true; 
							return;
						}
					}
				}
			}
		}

		public function mrkv_ua_ship_validate_fields()
		{
			$this->mrkv_ua_ship_maybe_disable_default_fields();

			# Check current shipping
		    if ( isset( $_POST['shipping_method'][0] ) ) 
		    {
		    	# Stop job
	        	if (!$this->has_mrkv_ua_ship) return;
	    	}
		    else
		    {
		    	# Stop job
		      	return;
		    }

		    # Include settings checkout by shipping
			include 'mrkv_ua_shipping_translate.php';

			$settings = get_option($this->current_shipping_global . '_m_ua_settings');

		    foreach(MRKV_UA_SHIPPING_LIST[$this->current_shipping_global]['method'][$this->current_shipping]['checkout_fields'] as $field_id => $field_val)
		    {
		    	$options_data_loader = '';

		    	if(isset($field_val['options']))
		    	{
		    		$options_data_loader = $translate_labels[$this->current_shipping_global]['method'][$this->current_shipping]['checkout_fields'][$field_id]['options'][''];
		    	}

		    	if(!isset($_POST[$this->current_shipping . $field_id]) || $_POST[$this->current_shipping . $field_id] == ''
		    		|| ($options_data_loader && $_POST[$this->current_shipping . $field_id] == $options_data_loader))
		    	{
		    		if(isset($settings['checkout']['middlename']['required']) && $settings['checkout']['middlename']['required'] == 'on' && '_patronymic' == $field_id)
					{
						$translated_field_val = $translate_labels[$this->current_shipping_global]['method'][$this->current_shipping]['checkout_fields'][$field_id]['label'];

		    			wc_add_notice(__('Field', 'mrkv-ua-shipping') . ' ' . $translated_field_val . ' ' . __('is required', 'mrkv-ua-shipping'), 'error');

		    			return;	

					}
					
		    		if(isset($field_val['exclude']) && $field_val['exclude'] && isset($_POST[$this->current_shipping . $field_id . '_enabled']) && $_POST[$this->current_shipping . $field_id . '_enabled'] == 'off')
		    		{
		    			continue;
		    		}

		    		if(isset($field_val['required']) && !$field_val['required'])
		    		{
		    			continue;
		    		}

		    		if(isset($field_val['label']))
		    		{
		    			$translated_field_val = $translate_labels[$this->current_shipping_global]['method'][$this->current_shipping]['checkout_fields'][$field_id]['label'];
		    			wc_add_notice(__('Field', 'mrkv-ua-shipping') . ' ' . $translated_field_val . ' ' . __('is required', 'mrkv-ua-shipping'), 'error');
		    			return;
		    		}
		    	}
		    }
		}

		public function mrkv_ua_ship_remove_default_fields_from_validation($fields)
		{
			$this->mrkv_ua_ship_maybe_disable_default_fields();

			if($this->has_mrkv_ua_ship)
			{
				# Disable all fields
		        unset($fields['billing']['billing_address_1']);
		        unset($fields['billing']['billing_address_2']);
		        unset($fields['billing']['billing_city']);
		        unset($fields['billing']['billing_state']);
		        unset($fields['billing']['billing_postcode']);
		        unset($fields['shipping']['shipping_address_1']);
				unset($fields['shipping']['shipping_address_2']);
				unset($fields['shipping']['shipping_city']);
				unset($fields['shipping']['shipping_state']);
				unset($fields['shipping']['shipping_postcode']);
			}

			# Return fields
		    return $fields;
		}
	}
}