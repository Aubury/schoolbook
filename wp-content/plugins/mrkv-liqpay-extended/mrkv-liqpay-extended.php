<?php
/*
 * Plugin Name:morkva Liqpay Extended
 * Description: LiqPay Payment Gateway with callback by morkva
 * Version: 1.0.5
 * Tested up to: 7.0
 * Requires at least: 5.2
 * Requires PHP: 7.1
 * Author: morkva
 * Author URI: https://morkva.co.ua
 * Text Domain: mrkv-liqpay-extended
 * WC requires at least: 5.4.0
 * WC tested up to: 9.8.0
 * Domain Path: /i18n
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

# This prevents a public user from directly accessing your .php files
if (! defined('ABSPATH')) 
{
    # Exit if accessed directly
    exit;
}

add_action( 'before_woocommerce_init', function() {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
    }
} );

require_once ABSPATH . 'wp-admin/includes/plugin.php';

define ( 'LIQPAY_VERSION', '1.0.5' );
define( 'LIQPAY_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'LIQPAY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'LIQPAY_PLUGIN_FILE_NAME', basename( __FILE__ ) );
define( 'LIQPAY_PLUGIN_NAME', plugin_basename( __DIR__ ) );

# Activation woocommerce check
register_activation_hook(__FILE__, 'mrkv_liqpay_check_woocommerce_installed');

# Include liqpay to menu Wordpress
require_once plugin_dir_path(__FILE__) . 'includes/class-morkva-liqpay-menu.php';

# Create page and show in menu
new MorkvaLiqpayMenu();

# Load classes
add_action('plugins_loaded', 'mrkv_liqpay_extended_init_gateway_class', 11);

# Add filter to Payment Gateway
add_filter('woocommerce_payment_gateways', 'mrkv_liqpay_extended_add_gateway_class');

# Add filter block supports
add_action( 'woocommerce_blocks_loaded', 'mrkv_liqpay_extended_add_gateway_block_support' );

# Load translation
add_action( 'plugins_loaded', 'mrkv_liqpay_true_load_plugin_textdomain', 11 );

# Add plugin scripts and styles
add_action('admin_enqueue_scripts', 'mrkv_liqpay_styles_and_scripts');

# Add plugin scripts and styles
add_action( 'wp_enqueue_scripts', 'mrkv_liqpay_styles_and_scripts_front' );

function mrkv_liqpay_styles_and_scripts()
{
    $section = filter_input( INPUT_GET, 'section', FILTER_DEFAULT );
    
    $allowed_sections = array( 'morkva-liqpay' );
    
    if ( in_array( $section, $allowed_sections, true ) ) {
        wp_enqueue_style('admin-mrkv-liqpay', LIQPAY_PLUGIN_URL . '/css/morkva-liqpay-admin.css', array(), LIQPAY_VERSION);
        wp_enqueue_script('admin-mrkv-liqpay', LIQPAY_PLUGIN_URL . '/js/admin/admin-mrkv-liqpay.js', array('jquery'), LIQPAY_VERSION, true);
    }
}

function mrkv_liqpay_styles_and_scripts_front()
{
    if ( is_checkout() ) 
    {
        wp_enqueue_style('front-mrkv-liqpay', LIQPAY_PLUGIN_URL . '/css/morkva-liqpay-front.css', array(), LIQPAY_VERSION);
    }
}

/**
 * Check WooCommerce is installed
 * */
function mrkv_liqpay_check_woocommerce_installed() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        wp_die( wp_kses( 
            __( '<a href="https://wordpress.org/plugins/woocommerce/">WooCommerce</a> is not installed. Please install and activate WooCommerce before activating this plugin.', 'mrkv-liqpay-extended' ), 
            'mrkv-liqpay-extended' 
        ), array( 'link' => array( 'href' => array() ) ) );
    }
}

/**
 * Load translate 
 * */
function mrkv_liqpay_true_load_plugin_textdomain() 
{
    # Get languages path
    $plugin_path = dirname( plugin_basename( __FILE__ ) ) . '/i18n/';
    // phpcs:ignore PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound
    load_plugin_textdomain( 'mrkv-liqpay-extended', false, $plugin_path );
}

/**
 * Loaded all payment classes
 * */
function mrkv_liqpay_extended_init_gateway_class()
{
    # Iclude Morkva Liqpay Gateway
    require_once(__DIR__ . '/includes/class-wc-gateway-morkva-liqpay.php');
}

# Add Morkva liqpay Gateway to Woocommerce
function mrkv_liqpay_extended_add_gateway_class($methods)
{

    # Include Liqpay
    $methods[] = 'WC_Gateway_Morkva_Liqpay';

    # Return all methods
    return $methods;
}   

/**
 * Check woo blocks support
 * */
function mrkv_liqpay_extended_add_gateway_block_support()
{
    if ( !class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) 
    {
        return;
    }

    # Including Liqpay gateway blocks
    require_once LIQPAY_PLUGIN_PATH . 'includes/blocks/class-wc-gateway-liqpay-blocks.php';

    # Registering the PHP class we have just included
    add_action(
        'woocommerce_blocks_payment_method_type_registration',
        function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) 
        {
            # Register an instance of WC_Gateway_Morkva_Liqpay_Blocks
            $payment_method_registry->register( new WC_Gateway_Morkva_Liqpay_Blocks );
        }
    );
}

# Include liqpay orders data
require_once plugin_dir_path(__FILE__) . 'includes/class-morkva-liqpay-orders.php';

# Create liqpay orders data
new MRKV_LIQPAY_ORDERS();
