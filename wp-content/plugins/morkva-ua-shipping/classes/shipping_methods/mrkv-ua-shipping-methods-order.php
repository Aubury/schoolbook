<?php
# Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 

# Check if class exist
if (!class_exists('MRKV_UA_SHIPPING_METHODS_ORDER'))
{
	/**
	 * Class for setup shipping methods checkout order meta save
	 */
	class MRKV_UA_SHIPPING_METHODS_ORDER
	{
		/**
		 * Constructor for checkout order meta save
		 * */
		function __construct()
		{
			add_action('woocommerce_checkout_create_order', array($this, 'mrkv_ua_ship_create_order'));
		}

		public function mrkv_ua_ship_create_order($order)
		{
			# Check if isset shipping method
	        if ( isset( $_POST['shipping_method'][0] ) ) 
	        {
	        	$keys_shipping = array_keys(MRKV_UA_SHIPPING_LIST);
	        	$key = '';
	        	$current_shipping = '';
	        	$has_mrkv_ua_ship = '';

				foreach($keys_shipping as $key_ship)
				{
					if(str_contains($_POST['shipping_method'][0], $key_ship))
					{
						$key = $key_ship;
					}
				}

				if($key)
				{
					foreach(MRKV_UA_SHIPPING_LIST[$key]['method'] as $method_slug => $method_data)
					{
						$clean_shipping_method = preg_replace('/_\d+$/', '', $_POST['shipping_method'][0]);
						
						if($clean_shipping_method == $method_slug)
						{
							$current_shipping = $method_slug;
							$has_mrkv_ua_ship = true; 
							break;
						}
					}

					if($has_mrkv_ua_ship && $current_shipping)
					{
						foreach(MRKV_UA_SHIPPING_LIST[$key]['method'][$current_shipping]['checkout_fields'] as $field_id => $field_val)
		    			{
		    				if(isset($_POST[$current_shipping . $field_id]) && isset($field_val['replace']))
		    				{
		    					if($field_val['replace'] == '_city'){
		    						$billing_city_unslashed = stripslashes( $_POST[ $current_shipping . $field_id ] );
		    						# Add billing city name to Thank you page
						            $order->set_billing_city( $billing_city_unslashed );
						            # Add shipping city name to Thankyou page
						            $order->set_shipping_city( $billing_city_unslashed );

						            update_user_meta( get_current_user_id(), 'billing_city', $billing_city_unslashed);
						            update_user_meta( get_current_user_id(), 'shipping_city', $billing_city_unslashed);
						            update_user_meta( get_current_user_id(), $current_shipping . $field_id, $billing_city_unslashed);
		    					}
		    					elseif($field_val['replace'] == '_state')
		    					{
		    						$billing_state_unslashed = stripslashes( $_POST[ $current_shipping . $field_id ] );
		    						$order->set_billing_state(esc_attr($billing_state_unslashed) );
	              					$order->set_shipping_state( esc_attr($billing_state_unslashed) );

	              					update_user_meta( get_current_user_id(), 'billing_state', $billing_state_unslashed);
						            update_user_meta( get_current_user_id(), 'shipping_state', $billing_state_unslashed);
						            update_user_meta( get_current_user_id(), $current_shipping . $field_id, $billing_state_unslashed);
		    					}
		    					elseif($field_val['replace'] == '_address_1')
		    					{
		    						$address = $_POST[$current_shipping . $field_id];
		    						$address = str_replace('\"', '', $address);
		    						$address = str_replace("\\'", "'", $address);
		    						$address = stripslashes( $address );

		    						$order->set_billing_address_1(esc_attr($address) );
	              					$order->set_shipping_address_1( esc_attr($address) );

	              					update_user_meta( get_current_user_id(), 'billing_address_1', $address);
						            update_user_meta( get_current_user_id(), 'shipping_address_1', $address);
						            update_user_meta( get_current_user_id(), $current_shipping . $field_id, $address);
		    					}
		    					elseif($field_val['replace'] == '_postcode')
		    					{
		    						$order->set_billing_postcode(esc_attr($_POST[$current_shipping . $field_id]) );
	              					$order->set_shipping_postcode( esc_attr($_POST[$current_shipping . $field_id]) );

	              					update_user_meta( get_current_user_id(), 'billing_postcode', $_POST[$current_shipping . $field_id]);
						            update_user_meta( get_current_user_id(), 'shipping_postcode', $_POST[$current_shipping . $field_id]);
						            update_user_meta( get_current_user_id(), $current_shipping . $field_id, $_POST[$current_shipping . $field_id]);
		    					}
		    					elseif($field_val['replace'] == '_address_2')
		    					{
		    						$billing_address_unslashed = stripslashes( $_POST[ $current_shipping . $field_id ] );
		    						$order->set_billing_address_2(esc_attr($billing_address_unslashed) );
	              					$order->set_shipping_address_2( esc_attr($billing_address_unslashed) );

	              					update_user_meta( get_current_user_id(), 'billing_address_2', $billing_address_unslashed);
						            update_user_meta( get_current_user_id(), 'shipping_address_2', $billing_address_unslashed);
						            update_user_meta( get_current_user_id(), $current_shipping . $field_id, $billing_address_unslashed);
		    					}
		    					else
		    					{
		    						$billing_additional_unslashed = stripslashes( $_POST[ $current_shipping . $field_id ] );
		    						$order->update_meta_data( $current_shipping . $field_id, esc_attr($billing_additional_unslashed) );

		    						update_user_meta( get_current_user_id(), $current_shipping . $field_id, $billing_additional_unslashed);
						            update_user_meta( get_current_user_id(), $current_shipping . $field_id, $billing_additional_unslashed);
		    					}
		    				}
		    			}
		    			
		    			$order->save();
					}
				}
	        }
		}
	}
}