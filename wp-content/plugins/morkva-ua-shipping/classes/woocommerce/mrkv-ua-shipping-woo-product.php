<?php
# Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;
use Automattic\WooCommerce\Utilities\OrderUtil;

# Check if class exist
if (!class_exists('MRKV_UA_SHIPPING_WOO_PRODUCT'))
{
	/**
	 * Class for setup WOO settings product
	 */
	class MRKV_UA_SHIPPING_WOO_PRODUCT
	{
		/**
		 * Constructor for WOO settings product
		 * */
		function __construct()
		{
			add_filter('woocommerce_product_data_tabs', [$this, 'admin_mrkv_ua_shipping_add_new_tab']);
			add_action('woocommerce_product_data_panels', [$this, 'admin_mrkv_ua_shipping_add_new_tab_content']);
			add_action('woocommerce_process_product_meta', [$this, 'admin_mrkv_ua_shipping_add_new_tab_data_save']);
		}

		public function admin_mrkv_ua_shipping_add_new_tab($tabs)
		{
			$tabs['mrkv_ua_shipping'] = [
		        'label'    => __('MRKV UA Shipping', 'mrkv-ua-shipping'),
		        'target'   => 'mrkv_ua_shipping_options',
		        'class'    => [],
		        'priority' => 50,
		    ];
		    return $tabs;
		}

		public function admin_mrkv_ua_shipping_add_new_tab_content()
		{
			global $post;
    		$product = wc_get_product($post->ID);

    		if($product)
    		{
    			?>
    				<div id="mrkv_ua_shipping_options" class="panel woocommerce_options_panel">
        				<h3 style="padding: 0px 20px;"><?php _e('MRKV UA Shipping', 'mrkv-ua-shipping'); ?></h3>
        				<?php
        					$m_ua_active_plugins = get_option('m_ua_active_plugins');

        					foreach(MRKV_UA_SHIPPING_LIST as $slug => $shipping)
							{
								if(isset($m_ua_active_plugins[$slug]['enabled']) && $m_ua_active_plugins[$slug]['enabled'] == 'on')
								{
									# Get settings 
									$settings_shipping = get_option($slug . '_m_ua_settings');

									# Include settings checkout by shipping
									include MRKV_UA_SHIPPING_PLUGIN_PATH . 'classes/shipping_methods/' . $slug . '/product/mrkv-ua-shipping-product.php';
								}
							}
        				?>
        				<hr>
        			</div>
    			<?php
    		}
		}

		public function admin_mrkv_ua_shipping_add_new_tab_data_save($post_id)
		{
			if (isset($_POST['_mrkv_tire_type'])) {
		        update_post_meta($post_id, '_mrkv_tire_type', sanitize_text_field($_POST['_mrkv_tire_type']));
		    }
		    if (isset($_POST['_mrkv_document_weight'])) {
		        update_post_meta($post_id, '_mrkv_document_weight', sanitize_text_field($_POST['_mrkv_document_weight']));
		    }
		}
	}
}