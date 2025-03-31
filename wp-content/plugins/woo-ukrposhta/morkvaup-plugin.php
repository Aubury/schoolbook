<?php
/**
 * Plugin Name: Ukrposhta
 * Plugin URI: https://morkva.co.ua/shop/woo-ukrposhta-pro-lifetime
 * Description:  Генеруйте накладні просто зі сторінки замовлення і зекономте тонну часу на відділенні при відправці.
 * Version: 1.17.7
 * Author: Morkva
 * Text Domain: woo-ukrposhta-pro
 * Domain Path: /languages
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Tested up to: 6.6
 * WC requires at least: 3.8
 * WC tested up to: 8.8
 */

if (!defined('ABSPATH'))
{
    exit;
}

add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

include_once 'autoload.php';

define('MORKVA_UKRPOSHTA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MORKVA_UKRPOSHTA_PLUGIN_PATH', __FILE__);
define('MORKVA_UKRPOSHTA_PLUGIN_DIR', plugin_dir_path(__FILE__));

require_once ABSPATH . 'wp-admin/includes/plugin.php';
$plugin_data = get_plugin_data( __FILE__ );
$plugin_version = $plugin_data['Version'];
define( 'MORKVA_UKRPOSHTA_VERSION', $plugin_version );


\deliveryplugin\Ukrposhta\classes\ukrposhtaShipping::instance();
\deliveryplugin\Ukrposhta\classes\ukrposhtaAddressShipping::instance();

if (!function_exists('morkva_ukrposhta'))
{

    function morkva_ukrposhta()
    {
        return \deliveryplugin\Ukrposhta\classes\ukrposhtaShipping::instance();
    }

}

if (!function_exists('morkva_ukrposhta_import_svg'))
{

    function morkva_ukrposhta_import_svg($image)
    {
        return file_get_contents(MORKVA_UKRPOSHTA_PLUGIN_DIR . '/image/' . $image);
    }

}

if (!function_exists('morkva_ukrposhta_get_option'))
{

    function morkva_ukrposhta_get_option($key)
    {
        return \deliveryplugin\Ukrposhta\classes\OptionsRepository::getOption($key);
    }

}

if (!function_exists('morkva_ukrposhta_address_get_option'))
{

    function morkva_ukrposhta_address_get_option($key)
    {
        return \deliveryplugin\Ukrposhta\classes\OptionsRepository::getOption($key);
    }

}

define('MORKVA_UKRPOSHTA_UP_SHIPPING_NAME', 'ukrposhta_shippping');
define('MORKVA_UKRPOSHTA_UP_SHIPPING_TITLE', 'Укрпошта до відділення');
define('MORKVA_UKRPOSHTA_UP_ADDRESS_SHIPPING_NAME', 'ukrposhta_address_shippping');
define('MORKVA_UKRPOSHTA_UP_ADDRESS_SHIPPING_TITLE', 'Укрпошта на адресу');

/*
clear shipping rates cache because woocommerce caching these values
*/
add_filter('woocommerce_checkout_update_order_review', 'clear_wc_shipping_rates_cache');

function clear_wc_shipping_rates_cache()
{
    $packages = WC()
        ->cart
        ->get_shipping_packages();

    foreach ($packages as $key => $value)
    {
        $shipping_session = "shipping_for_package_$key";

        unset(WC()
            ->session
            ->$shipping_session);
    }
}

function mrkv_cart_product_max_size( $default_length ) {
    $items = WC()->cart->get_cart();
    $dimension_unit = get_option( 'woocommerce_dimension_unit' );
    $array_cm = array();
    foreach ( $items as $item => $values ) {
        $_product = wc_get_product( $values['data']->get_id() );
        $array_prod_sizes = array(
            wc_get_dimension( $_product->get_length(), 'cm', $dimension_unit )  ,
            wc_get_dimension( $_product->get_width(), 'cm', $dimension_unit )  ,
            wc_get_dimension( $_product->get_height(), 'cm', $dimension_unit )
        );
        array_push( $array_cm, max( $array_prod_sizes ) );
    }
    $length = max( $default_length, max($array_cm));
    return wc_get_dimension( $length, 'cm', $dimension_unit );
}

function get_price_shipping($country, $citycost, $addr)
{
	$weight_unit = get_option( 'woocommerce_weight_unit' );
	$dimension_unit = get_option( 'woocommerce_dimension_unit' );
	$cartTotal = max( 1, WC()->cart->cart_contents_total );

	$cartWeight = '';
	$length = '';

	if ($country == "UA") {
		if(get_option('mrkvup_default_order_weight')){
			$cartWeight = get_option('mrkvup_default_order_weight');
		}
		else{
			$cartWeight = max( 0.5, WC()->cart->cart_contents_weight ); // Якщо у товарів немає ваги, то вага кошика 0.5 кг
		    $cartWeight = ( 'kg' == $weight_unit ) ? $cartWeight * 1000 : $cartWeight;
		}

		if(get_option('mrkvup_default_order_length')){
			$length = get_option('mrkvup_default_order_length');
		}
		else{
			$length = intval( ceil( floatval( mrkv_cart_product_max_size( 30 ) ) ) ); // Якщо у товарів немає розмірів, то максимальний розмір товару в кошику 30 см
		}

        $up_shipping_postcode = isset( $_COOKIE['up_shipping_postcode'] ) ? $_COOKIE['up_shipping_postcode'] : '';
	    $params = array(
	        "weight" => $cartWeight,
	        "length" => $length,
	        "addressFrom" => array(
	            "postcode"  => get_option('warehouse')
	        ),
	        "addressTo" => array(
	            "postcode"  => $up_shipping_postcode
	        ),
	        "type"  => get_option('sendtype'),
	        "deliveryType"  => "W2W",
	        "declaredPrice" => $cartTotal
	    );
	    if ( ! class_exists( 'UkrposhtaApi' ) ) {
	        require_once 'admin/partials/api.php';
	    }
	    $bearer = ( null !== get_option( 'production_bearer_ecom' ) ) ? get_option( 'production_bearer_ecom' ) : '';
	    $cptoken = ( null !== get_option( 'production_cp_token' ) ) ? get_option( 'production_cp_token' ) : '';
	    $tbearer = ( null !== get_option( 'production_bearer_status_tracking' ) ) ? get_option( 'production_bearer_status_tracking' ) : '';
	    $ukrposhtaApi = new UkrposhtaApi($bearer, $cptoken, $tbearer);
	    $invoice = $ukrposhtaApi->howcostsua($params);
	    $shipping_price = ( isset( $invoice['deliveryPrice'] ) ) ? $invoice['deliveryPrice'] : 0;
	    return $shipping_price;
	} 
}

add_filter('woocommerce_shipping_methods', 'morkva_ukrposhta_add_up_shipping_method');
function morkva_ukrposhta_add_up_shipping_method($methods)
{
    include_once 'classes/ukrPoshtaShippingMethod.php';

    $methods[MORKVA_UKRPOSHTA_UP_SHIPPING_NAME] = 'ukrPoshtaShippingMethod';

    return $methods;
}

add_filter('woocommerce_shipping_methods', 'morkva_ukrposhta_add_up_shipping_address_method');
function morkva_ukrposhta_add_up_shipping_address_method($methods)
{
    include_once 'classes/ukrPoshtaAddressShippingMethod.php';

    $methods[MORKVA_UKRPOSHTA_UP_ADDRESS_SHIPPING_NAME] = 'ukrPoshtaAddressShippingMethod';

    return $methods;
}

new \deliveryplugin\Ukrposhta\classes\ukrPoshtaFrontendInjector();
new \deliveryplugin\Ukrposhta\classes\CheckoutValidator();
new \deliveryplugin\Ukrposhta\classes\OrderCreator();

add_action('woocommerce_admin_order_data_after_shipping_address', function ($order)
{
    $shippingMethod = $order->get_shipping_methods();
    $shippingMethod = reset($shippingMethod);

    if ($shippingMethod && $shippingMethod->get_method_id() === MORKVA_UKRPOSHTA_UP_SHIPPING_NAME)
    {
?>
    <input type="hidden" name="_shipping_state" value="<?=esc_attr($order->get_shipping_state()); ?>" />
<?php
    }
});

add_filter('plugin_action_links_' . plugin_basename(__FILE__) , function ($links)
{
    $settings_link = '<a href="' . home_url('wp-admin/admin.php?page=morkva_ukrposhta_options') . '">Настройки</a>';
    array_unshift($links, $settings_link);

    return $links;
});

////////


require_once ABSPATH . 'wp-admin/includes/plugin.php';
$plugData = get_plugin_data(__FILE__);

define('MUP_PLUGIN_VERSION', $plugData['Version']);
define('MUP_PLUGIN_NAME', $plugData['Name']);
define('MUP_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('MUP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MUP_TABLEDB', 'uposhta_invoices');

function activate_morkvaup_plugin()
{
    global $wpdb;

    $table_name = $wpdb->prefix . MUP_TABLEDB;

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name)
    {
        // if table not exists, create this table in DB
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
       id int(11) AUTO_INCREMENT,
       order_id int(11) NOT NULL,
       order_invoice varchar(255) NOT NULL,
                   invoice_ref varchar(255) NOT NULL,
       PRIMARY KEY(id)
     ) $charset_collate;";
        require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    else
    {

    }

    flush_rewrite_rules();
}
/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-morkvaup-plugin-deactivator.php
 */
function deactivate_morkvaup_plugin()
{
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'activate_morkvaup_plugin');
register_deactivation_hook(__FILE__, 'deactivate_morkvaup_plugin');
/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-morkvaup-plugin.php';
/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_morkvaup_plugin()
{
    $plugin = new MUP_Plugin();
    $plugin->run();
}

add_action( 'before_woocommerce_init', function() {
	run_morkvaup_plugin();
} );


// Get city data from API-УП
add_action( 'wp_ajax_city_autocomplete', 'mrkvup_city_autocomplete' );
add_action( 'wp_ajax_nopriv_city_autocomplete', 'mrkvup_city_autocomplete' );
function mrkvup_city_autocomplete() {
	// Check for nonce security
	if ( ! wp_verify_nonce( $_POST['mrkvupnonce'], 'mrkvup_ajax_nonce' ) ) {
		wp_die('Permission Denied.');
	}
	$bearer = get_option('production_bearer_ecom');
	$mrkvup_city_suggestion = $_POST['term']; // User three-letter input in Checkout
	$city_arr = array(); // Found city data

	$cities = wp_remote_get('https://www.ukrposhta.ua/address-classifier-ws/get_city_by_region_id_and_district_id_and_city_ua?region_id=&district_id=&city_ua=' .
			$mrkvup_city_suggestion  . '&fuzzy=1', [
	    'headers' => [
	      'Сontent-type' => 'application/json, charset=utf-8',
	      'Accept' => 'application/json',
	      'Authorization' => 'Bearer ' . $bearer,
	    ],
	  'timeout' => 30
	]);

	if ( 200 == $cities['response']['code']) {
		$city_body = json_decode($cities['body']);
		$city_entries = $city_body->Entries;
		$cities_arr = $city_entries->Entry;
    } else {
      echo 'API Укрпошта (cities): ' . $cities['response']['code'] . ' ' . $cities['response']['message'];
      wp_die();
    }

	foreach ( $cities_arr as $k => $v ) {
	  $city_arr[$k] = array(
	  	"label" => $v->CITY_UA . ', ' . $v->DISTRICT_UA . ' район',
	  	"value" => $v->CITY_ID,
	  );
	}
	echo json_encode( $city_arr );
	wp_die();
}

// Get postoffice data from API-УП
add_action('wp_ajax_morkva_ukrposhta_load_postcodes', 'mrkvuploadPostcodesFromApiUP' );
add_action('wp_ajax_nopriv_morkva_ukrposhta_load_postcodes', 'mrkvuploadPostcodesFromApiUP' );
function mrkvuploadPostcodesFromApiUP()
{
	// Check for nonce security
	if ( ! wp_verify_nonce( $_POST['mrkvupnonce'], 'mrkvup_ajax_nonce' ) ) {
		wp_die('Permission Denied.');
	}
	$bearer = get_option('production_bearer_ecom');
	$mrkvup_cityid = $_POST['mrkvup_cityid']; // id of chosen in CHeckout city

	$postcodes = wp_remote_get('https://www.ukrposhta.ua/address-classifier-ws/get_postoffices_by_city_id?city_id=' .
		$mrkvup_cityid, [
	  	'headers' => [
			'Сontent-type' => 'application/json, charset=utf-8',
			'Accept' => 'application/json',
			'Authorization' => 'Bearer ' . $bearer,
		],
	  	'timeout' => 30
	]);

	if ( 200 == $postcodes['response']['code'] && ! empty($postcodes['body'])) {
      $postcode_body = json_decode($postcodes['body']);
    } else {
    	$postcode_body = json_encode('{"Entries":{"Entry":[{"POSTINDEX":"Немає відділень","CITY_UA":"","ADDRESS":""}]}}');
		echo 'API Укрпошта (postcode): ' . $postcodes['response']['code'] . ' ' . $postcodes['response']['message'];
		wp_die();
    }

	$postcode_entries = $postcode_body->Entries;
	if ( is_object( $postcode_entries ) && property_exists( $postcode_entries, 'Entry' ) ) {
		$postcodes_arr = $postcode_entries->Entry;
	} else {
		$po_obj = new stdClass();
		$po_obj->POSTINDEX = 'Немає відділень';
		$po_obj->CITY_UA = '';
		$po_obj->ADDRESS = '';
		$postcodes_arr[0] = $po_obj;
	}

	foreach ( $postcodes_arr as $k => $v ) {
	  $postcode_arr[] = $v->POSTINDEX . ' ' . $v->CITY_UA . ' ' . $v->ADDRESS;
	}
	echo json_encode( $postcode_arr );
	wp_die();
}
