<?php
if(isset($this->active_shipping['nova-global']['methods']['mrkv_ua_shipping_nova-global']))
{
	$instance_id = $this->active_shipping['nova-global']['methods']['mrkv_ua_shipping_nova-global']['instance_id'];
	$shipping_settings = get_option('woocommerce_mrkv_ua_shipping_nova-global_' . $instance_id . '_settings');

	$args['nova_global_type']['mrkv_ua_shipping_nova-global_' . $instance_id] = '';

	if(isset($shipping_settings['warehouse_type']) && $shipping_settings['warehouse_type'])
    {
    	$args['nova_global_type']['mrkv_ua_shipping_nova-global_' . $instance_id] = $shipping_settings['warehouse_type'];
    }
}