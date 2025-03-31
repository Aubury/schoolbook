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
        if ( isset( $_POST['shipping_method'] ) ) {
            if(strpos( $_POST['shipping_method'][0], MORKVA_UKRPOSHTA_UP_ADDRESS_SHIPPING_NAME ) !== false)
            {
                if ( isset( $_POST['billing_up_address_surname'] ) ) {
                    $order->set_billing_company($_POST['billing_up_address_surname']);
                }
                if ( isset( $_POST['shipping_up_address_surname'] ) && $_POST['shipping_up_address_surname'] != '') {
                    $order->set_shipping_company($_POST['shipping_up_address_surname']);
                }
                elseif(isset( $_POST['billing_up_address_surname'] ))
                {
                    $order->set_shipping_company($_POST['billing_up_address_surname']);
                }

                $order->save();
            }
            if ( strpos( $_POST['shipping_method'][0], MORKVA_UKRPOSHTA_UP_SHIPPING_NAME ) === false) return;
        }

        // Set billing_country field value if it is absent on Checkout page
        $_POST['billing_country'] = isset( $_POST['billing_country'] ) ? $_POST['billing_country'] : 'UA';

        if ( 'UA' ==  $_POST['billing_country'] ) {
            // Save Order city name and warehouse data. They will be shown on Thank you page.
            if ( isset( $_POST[MORKVA_UKRPOSHTA_UP_SHIPPING_NAME . '_city_select'] ) ) {
                $city_parts =  explode( ',', $_POST[MORKVA_UKRPOSHTA_UP_SHIPPING_NAME . '_city_select'] );
                $chosen_city = $city_parts[0];
            }
            $input_city = $_POST[MORKVA_UKRPOSHTA_UP_SHIPPING_NAME . '_city'] ?? $chosen_city;

            // Check on apostrophe in city name
            if ( strpos( $input_city, "'" ) !== false ) {
                $input_city = str_replace( "\\", "", $input_city );
            }

            $input_city_tolower = wc_strtolower( $input_city );
            // Make the first city letters upper case
            $input_city_tolower_ucf = mb_convert_case( $input_city_tolower, MB_CASE_TITLE, "UTF-8" );

            // Add billing city name to Thank you page
            $order->set_billing_city( esc_attr( $input_city_tolower_ucf ) );
            // Add shipping city name to Thankyou page
            $order->set_shipping_city( esc_attr( $input_city_tolower_ucf ) );

            $order->set_shipping_address_1( esc_attr( 'Відділення Укрпошти' ) );

            if ( isset( $_POST['ukrposhta_shippping_surname'] ) ) {
                $surname = $_POST['ukrposhta_shippping_surname'];
                $order->set_billing_company($_POST['ukrposhta_shippping_surname']);
                $order->set_shipping_company($_POST['ukrposhta_shippping_surname']);
            } else {
                $surname = '';
            }

            // Warehouse data from API-УП
            $warehouse_parts = isset( $_POST[MORKVA_UKRPOSHTA_UP_SHIPPING_NAME . '_postcode_selected'] )
                ? explode( ' ', $_POST[MORKVA_UKRPOSHTA_UP_SHIPPING_NAME . '_postcode_selected'] ) : '';
            $input_postcode = ( is_array( $warehouse_parts ) ) ? $warehouse_parts[0] : '';

            $input_warehouse = $_POST[MORKVA_UKRPOSHTA_UP_SHIPPING_NAME . '_warehouse'] ?? $input_postcode;

          // Add recipient data to WC_Order object and Thankyou page
          $order->set_billing_postcode( esc_attr( $input_warehouse ) );
          $order->set_shipping_postcode( esc_attr( $input_warehouse ) );
        } else {

            if (  $_POST['billing_city']  ) {
              $order->set_billing_city(esc_attr($_POST['billing_city']) );
              $order->set_shipping_city( esc_attr($_POST['billing_city']) );
            }

            if (  $_POST['billing_address_1']  ) {
              $order->set_billing_address_1(esc_attr($_POST['billing_address_1']) );
              $order->set_shipping_address_1( esc_attr($_POST['billing_address_1']) );
            }

            if (  $_POST['billing_address_2']  ) {
              $order->set_billing_address_2(esc_attr($_POST['billing_address_2']) );
              $order->set_shipping_address_2( esc_attr($_POST['billing_address_2']) );
            }

            if (  $_POST['billing_state']  ) {
              $order->set_billing_state(esc_attr($_POST['billing_state']) );
              $order->set_shipping_state( esc_attr($_POST['billing_state']) );
            }

            if (  $_POST['billing_postcode']  ) {
              $order->set_shipping_postcode(esc_attr($_POST['billing_postcode']) );
              $order->set_billing_postcode( esc_attr($_POST['billing_postcode']) );
            }
        }
    }

}
