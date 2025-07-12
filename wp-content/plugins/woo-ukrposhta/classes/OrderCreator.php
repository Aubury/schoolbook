<?php
namespace deliveryplugin\Ukrposhta\classes;

use deliveryplugin\Ukrposhta\Services\StorageService;

if ( ! defined('ABSPATH')) {
    exit;
}

class OrderCreator
{
    public function __construct()
    {
        add_action('woocommerce_checkout_create_order', [ $this, 'createOrder' ]);
    }

    public function createOrder($order)
    {
       // Validate nonce to protect against CSRF attacks
        if ( ! isset($_POST['woocommerce-process-checkout-nonce']) || ! wp_verify_nonce(sanitize_key($_POST['woocommerce-process-checkout-nonce']), 'woocommerce-process_checkout') ) {
            return; // Nonce validation failed
        }

        // Ensure shipping method is set and contains the correct value
        if ( isset($_POST['shipping_method'][0]) ) {
            $shipping_method = sanitize_text_field(wp_unslash($_POST['shipping_method'][0]));

            if ( strpos($shipping_method, MORKVA_UKRPOSHTA_UP_ADDRESS_SHIPPING_NAME) !== false ) {
            
                // Sanitize the shipping and billing fields to prevent unsafe input
                if (isset($_POST['billing_up_address_surname'])) {
                    $order->set_billing_company(sanitize_text_field(wp_unslash($_POST['billing_up_address_surname'])));
                }
                if (isset($_POST['shipping_up_address_surname']) && $_POST['shipping_up_address_surname'] != '') {
                    $order->set_shipping_company(sanitize_text_field(wp_unslash($_POST['shipping_up_address_surname'])));
                } elseif (isset($_POST['billing_up_address_surname'])) {
                    $order->set_shipping_company(sanitize_text_field(wp_unslash($_POST['billing_up_address_surname'])));
                }
            }

            if ( strpos($shipping_method, MORKVA_UKRPOSHTA_UP_SHIPPING_NAME) !== false ) {
                // Set order data for shipping and billing address
                $_POST['billing_country'] = isset($_POST['billing_country']) ? sanitize_text_field(wp_unslash($_POST['billing_country'])) : 'UA';

                if ('UA' == $_POST['billing_country']) {
                    // Sanitize and process city and warehouse data
                    if (isset($_POST[MORKVA_UKRPOSHTA_UP_SHIPPING_NAME . '_city_select'])) {
                        $city_parts = explode(',', sanitize_text_field(wp_unslash($_POST[MORKVA_UKRPOSHTA_UP_SHIPPING_NAME . '_city_select'])));
                        $chosen_city = sanitize_text_field(wp_unslash($city_parts[0]));
                    }
                    $input_city = isset($_POST[MORKVA_UKRPOSHTA_UP_SHIPPING_NAME . '_city']) ? sanitize_text_field(wp_unslash($_POST[MORKVA_UKRPOSHTA_UP_SHIPPING_NAME . '_city'])) : $chosen_city;

                    // Sanitize city name and ensure no apostrophes
                    if (strpos($input_city, "'") !== false) {
                        $input_city = str_replace("\\", "", $input_city);
                    }

                    $input_city_tolower = wc_strtolower($input_city);
                    $input_city_tolower_ucf = mb_convert_case($input_city_tolower, MB_CASE_TITLE, "UTF-8");

                    // Set sanitized billing and shipping city
                    $order->set_billing_city(esc_attr($input_city_tolower_ucf));
                    $order->set_shipping_city(esc_attr($input_city_tolower_ucf));

                    // Set shipping address (static for Ukrposhta)
                    $order->set_shipping_address_1(esc_attr('Відділення Укрпошти'));

                    // Sanitize and process surname
                    if (isset($_POST['ukrposhta_shippping_surname'])) {
                        $surname = sanitize_text_field(wp_unslash($_POST['ukrposhta_shippping_surname']));
                        $order->set_billing_company($surname);
                        $order->set_shipping_company($surname);
                    } else {
                        $surname = '';
                    }

                    // Process warehouse data
                    $warehouse_parts = isset($_POST[MORKVA_UKRPOSHTA_UP_SHIPPING_NAME . '_postcode_selected']) ? explode(' ', sanitize_text_field(wp_unslash($_POST[MORKVA_UKRPOSHTA_UP_SHIPPING_NAME . '_postcode_selected']))) : '';
                    $input_postcode = is_array($warehouse_parts) ? sanitize_text_field(wp_unslash($warehouse_parts[0])) : '';
                    $input_warehouse = isset($_POST[MORKVA_UKRPOSHTA_UP_SHIPPING_NAME . '_warehouse']) ? sanitize_text_field(wp_unslash($_POST[MORKVA_UKRPOSHTA_UP_SHIPPING_NAME . '_warehouse'])) : $input_postcode;

                    // Set postcode and warehouse data
                    $order->set_billing_postcode(esc_attr($input_warehouse));
                    $order->set_shipping_postcode(esc_attr($input_warehouse));
                } else {
                    // For non-Ukraine orders, sanitize and process default address fields
                    if (isset($_POST['billing_city'])) {
                        $order->set_billing_city(esc_attr(sanitize_text_field(wp_unslash($_POST['billing_city']))));
                        $order->set_shipping_city(esc_attr(sanitize_text_field(wp_unslash($_POST['billing_city']))));
                    }

                    if (isset($_POST['billing_address_1'])) {
                        $order->set_billing_address_1(esc_attr(sanitize_text_field(wp_unslash($_POST['billing_address_1']))));
                        $order->set_shipping_address_1(esc_attr(sanitize_text_field(wp_unslash($_POST['billing_address_1']))));
                    }

                    if (isset($_POST['billing_address_2'])) {
                        $order->set_billing_address_2(esc_attr(sanitize_text_field(wp_unslash($_POST['billing_address_2']))));
                        $order->set_shipping_address_2(esc_attr(sanitize_text_field(wp_unslash($_POST['billing_address_2']))));
                    }

                    if (isset($_POST['billing_state'])) {
                        $order->set_billing_state(esc_attr(sanitize_text_field(wp_unslash($_POST['billing_state']))));
                        $order->set_shipping_state(esc_attr(sanitize_text_field(wp_unslash($_POST['billing_state']))));
                    }

                    if (isset($_POST['billing_postcode'])) {
                        $order->set_shipping_postcode(esc_attr(sanitize_text_field(wp_unslash($_POST['billing_postcode']))));
                        $order->set_billing_postcode(esc_attr(sanitize_text_field(wp_unslash($_POST['billing_postcode']))));
                    }
                }
            }

            // Save the order after all modifications
            $order->save();
        }
    }
}