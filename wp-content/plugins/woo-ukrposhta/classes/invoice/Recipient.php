<?php
namespace deliveryplugin\Ukrposhta\classes\invoice;

// If this file is called directly, abort.
defined( 'ABSPATH' ) or die();

class Recipient extends Sender
{
    public $addressId = ''; 
    public $addressMsg = '';

    public function getAddress($order_data = '')
    {
        if (isset($_POST['mrkv_up_my_form_nonce'])) {
            // Remove escaping from input data
            $nonce = sanitize_text_field(wp_unslash($_POST['mrkv_up_my_form_nonce']));

            if (wp_verify_nonce($nonce, 'mrkv_up_form_action') && isset( $_POST['index2'] ) ) {
                $index2 = sanitize_text_field( wp_unslash( $_POST['index2'] ) );
                $country_rec = isset( $_POST['country_rec'] ) ? sanitize_text_field( wp_unslash( $_POST['country_rec'] ) ) : '';

                if ( $order_data ) {
                    $shipping_methods = $order_data->get_shipping_methods();
                    $shipping_method = @array_shift( $shipping_methods );
                    $shipping_method_id = $shipping_method ? $shipping_method->get_method_id() : '';

                    if ( 'ukrposhta_shippping' === $shipping_method_id ) {
                        $address = $this->ukrposhtaApi->modelAdressPost( array(
                            "postcode" => $index2,
                            "country"  => $country_rec,
                        ) );
                    } else {
                        $address = $this->ukrposhtaApi->modelAdressPost( array(
                            "postcode"        => $index2,
                            "country"         => $country_rec,
                            "region"          => isset( $_POST['region_data'] ) ? sanitize_text_field( wp_unslash( $_POST['region_data'] ) ) : '',
                            "city"            => isset( $_POST['city_data'] ) ? sanitize_text_field( wp_unslash( $_POST['city_data'] ) ) : '',
                            "street"          => isset( $_POST['street_data'] ) ? sanitize_text_field( wp_unslash( $_POST['street_data'] ) ) : '',
                            "apartmentNumber" => isset( $_POST['apartmentNumber_data'] ) ? sanitize_text_field( wp_unslash( $_POST['apartmentNumber_data'] ) ) : '',
                        ) );
                    }
                } else {
                    $address = $this->ukrposhtaApi->modelAdressPost( array(
                        "postcode" => $index2,
                        "country"  => $country_rec,
                    ) );
                }
                
                if ( isset( $address['id'] ) ) {
                    return $this->addressId = $address['id'];
                } else {
                    $this->addressMsg .= 'Помилка в поштовому індексі Одержувача. ';
                    $this->addressMsg .= isset( $address['message'] ) ? sanitize_text_field( $address['message'] ) . '. ' : '';
                }
            } else {
                $this->addressMsg .= 'Відсутній поштовий індекс Одержувача. ';
            }
        }
        else {
            $this->addressMsg .= 'Відсутній поштовий індекс Одержувача. ';
        }
    }

    public function hasApostrophe($string) : string
    {
        if ( strpos( $string, "'" ) !== false ) {
            return str_replace( "\\", "", $string );
        }
        return $string;
    }

    public function getFirstName() : string
    {
        $name = '';

        if (isset($_POST['mrkv_up_my_form_nonce'])) {
            // Remove escaping from input data
            $nonce = sanitize_text_field(wp_unslash($_POST['mrkv_up_my_form_nonce']));

            if(wp_verify_nonce($nonce, 'mrkv_up_form_action'))
            {
                $name = isset( $_POST['rec_first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['rec_first_name'] ) ) : '';
            }
        }
        return $this->hasApostrophe( $name );
    }

    public function getMiddleName() : string
    {
        $rec_middle_name = '';

        if (isset($_POST['mrkv_up_my_form_nonce'])) {
            // Remove escaping from input data
            $nonce = sanitize_text_field(wp_unslash($_POST['mrkv_up_my_form_nonce']));

            if(wp_verify_nonce($nonce, 'mrkv_up_form_action'))
            {
                $rec_middle_name = isset( $_POST['rec_middle_name'] ) ? sanitize_text_field( wp_unslash( $_POST['rec_middle_name'] ) ) : '';
            }
        }
        return $rec_middle_name;
    }

    public function getLastName() : string
    {
        $rec_last_name = '';

        if (isset($_POST['mrkv_up_my_form_nonce'])) {
            // Remove escaping from input data
            $nonce = sanitize_text_field(wp_unslash($_POST['mrkv_up_my_form_nonce']));

            if(wp_verify_nonce($nonce, 'mrkv_up_form_action'))
            {
                $rec_last_name = isset( $_POST['rec_last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['rec_last_name'] ) ) : '';
            }
        }
        return $rec_last_name;
    }

    public function getPhoneNumber()
    {
        $phone = '';

        if (isset($_POST['mrkv_up_my_form_nonce'])) {
            // Remove escaping from input data
            $nonce = sanitize_text_field(wp_unslash($_POST['mrkv_up_my_form_nonce']));

            if(wp_verify_nonce($nonce, 'mrkv_up_form_action'))
            {
                $phone = isset( $_POST['phone2'] ) ? sanitize_text_field( wp_unslash( $_POST['phone2'] ) ) : '';
            }
        }
        return $phone;
    }

    public function createClient($addressId)
    {
        $client_arr = array(
            "type"                     => 'INDIVIDUAL',
            "firstName"                => $this->getFirstName(),
            "middleName"               => $this->getMiddleName(),
            "lastName"                 => $this->getLastName(),
            "addressId"                => $addressId,
            "phoneNumber"              => $this->getPhoneNumber(),
            "checkOnDeliveryAllowed"   => true,
        );
        $client = $this->ukrposhtaApi->modelClientsPost( $client_arr );
        return $client;
    }

    public function getClientUuid($clientName, $addressId)
    {
        $client = $this->createClient( $addressId );
        if ( isset( $client['uuid'] ) ) {
            return $client['uuid'];
        }
        $this->clientMsg .= 'Клієнт ' . sanitize_text_field( $clientName ) . ' не створений.';
    }
}