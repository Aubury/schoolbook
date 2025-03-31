<?php
/*
 * Plugin Name: Morkva Liqpay Extended
 * Description: LiqPay Payment Gateway with callback by Morkva
 * Version: 0.7.0
 * Tested up to: 6.6
 * Requires at least: 5.2
 * Requires PHP: 7.1
 * Author: MORKVA
 * Author URI: https://morkva.co.ua
 * Text Domain: mrkv-liqpay-extended
 * WC requires at least: 5.4.0
 * WC tested up to: 8.8.0
 * Domain Path: /i18n
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
add_action('plugins_loaded', 'init_mrkv_liqpay_gateway_class', 11);

# Add filter to Payment Gateway
add_filter('woocommerce_payment_gateways', 'add_morkva_liqpay_gateway_class');

# Add filter block supports
add_action( 'woocommerce_blocks_loaded', 'morkva_liqpay_gateway_block_support' );

# Load translation
add_action( 'plugins_loaded', 'mrkv_liqpay_true_load_plugin_textdomain', 11 );

# Add plugin scripts and styles
add_action('admin_enqueue_scripts', 'mrkv_liqpay_styles_and_scripts');

# Add plugin scripts and styles
add_action( 'wp_enqueue_scripts', 'mrkv_liqpay_styles_and_scripts_front' );

function mrkv_liqpay_styles_and_scripts()
{
    if(isset($_GET['section']) && $_GET['section'] == 'morkva-liqpay')
    {
        wp_enqueue_style('admin-mrkv-liqpay', LIQPAY_PLUGIN_URL . '/css/morkva-liqpay-admin.css', array(), '');
        wp_enqueue_script('admin-mrkv-liqpay', LIQPAY_PLUGIN_URL . '/js/admin/admin-mrkv-liqpay.js', array('jquery'), '', true);
    }
}

function mrkv_liqpay_styles_and_scripts_front()
{
    if ( is_checkout() ) 
    {
        wp_enqueue_style('front-mrkv-liqpay', LIQPAY_PLUGIN_URL . '/css/morkva-liqpay-front.css', array(), '');
    }
}

/**
 * Check WooCommerce is installed
 * */
function mrkv_liqpay_check_woocommerce_installed() 
{
    if (!class_exists('WooCommerce')) 
    {
        wp_die(__('<a href="https://wordpress.org/plugins/woocommerce/">WooCommerce</a> is not installed. Please install <a href="https://wordpress.org/plugins/woocommerce/">WooCommerce</a> before activating this plugin', 'mrkv-liqpay-extended'));
    }
}

/**
 * Load translate 
 * */
function mrkv_liqpay_true_load_plugin_textdomain() 
{
    # Get languages path
    $plugin_path = dirname( plugin_basename( __FILE__ ) ) . '/i18n/';
    # Load languages
    load_plugin_textdomain( 'mrkv-liqpay-extended', false, $plugin_path );
}

/**
 * Loaded all payment classes
 * */
function init_mrkv_liqpay_gateway_class()
{
    # Iclude Morkva Liqpay Gateway
    require_once(__DIR__ . '/includes/class-wc-gateway-morkva-liqpay.php');
}

# Add Morkva liqpay Gateway to Woocommerce
function add_morkva_liqpay_gateway_class($methods)
{

    # Include Liqpay
    $methods[] = 'WC_Gateway_Morkva_Liqpay';

    # Return all methods
    return $methods;
}

/**
 * Check woo blocks support
 * */
function morkva_liqpay_gateway_block_support()
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


