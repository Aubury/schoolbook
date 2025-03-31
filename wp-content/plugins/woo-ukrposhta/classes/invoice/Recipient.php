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
		if ( isset( $_POST['index2'] ) ) {
			if($order_data)
			{
				$shipping_methods = $order_data->get_shipping_methods();
				$shipping_method = @array_shift( $shipping_methods );
				$shipping_method_id = $shipping_method->get_method_id();

				if( 'ukrposhta_shippping' == $shipping_method_id )
				{
					$address = $this->ukrposhtaApi->modelAdressPost( array(
						"postcode" => $_POST['index2'],
						"country" => $_POST['country_rec'],
					) );
				}
				else
				{
					$address = $this->ukrposhtaApi->modelAdressPost( array(
						"postcode" => $_POST['index2'],
						"country" => $_POST['country_rec'],
						"region" => $_POST['region_data'],
						"city" => $_POST['city_data'],
						"street" => $_POST['street_data'],
						"apartmentNumber" => $_POST['apartmentNumber_data']
					) );
				}
			}
			else
			{
				$address = $this->ukrposhtaApi->modelAdressPost( array(
					"postcode" => $_POST['index2'],
					"country" => $_POST['country_rec'],
				) );	
			}
			
	        if ( isset( $address['id'] ) ) {
	            return $this->addressId = $address['id'];
	        } else {
	            $failed = true;
	            $this->addressMsg .= 'Помилка в поштовому індексі Одержувача. ';
	            $this->addressMsg .= $address['message'] . '. ';
	        }
	    } else {
	        $this->addressMsg .= 'Відстуній поштовий індекс Одержувача. ';
	    }
	}

    public function hasApostrophe($string) : string
    {
        if ( strpos ( $string, "'" ) !== false ) {
            return $string = str_replace( "\\", "", $string );
        } else {
            return $string;
        }
    }

    public function getFirstName() : string
    {
        $name = isset( $_POST['rec_first_name'] ) ? \sanitize_text_field( $_POST['rec_first_name'] ) : '';
        return $this->hasApostrophe( $name );
    }

	public function getMiddleName() : string
	{
		return isset( $_POST['rec_middle_name'] ) ? \sanitize_text_field( $_POST['rec_middle_name'] ) : '';
	}

	public function getLastName() : string
	{
		return isset( $_POST['rec_last_name'] ) ? \sanitize_text_field( $_POST['rec_last_name'] ) : '';
	}

	public function getPhoneNumber()
	{
		return isset( $_POST['phone2'] ) ? \sanitize_text_field( $_POST['phone2'] ) : '';
	}

    public function createClient($addressId)
	{
		$client_arr = array(
			"type"			=> 'INDIVIDUAL',
    	    "firstName"		=> $this->getFirstName(),
    	    "middleName"	=> $this->getMiddleName(),
    	    "lastName"		=> $this->getLastName(),
    	    "addressId"		=> $addressId,
    	    "phoneNumber"	=> $this->getPhoneNumber(),
    	    "checkOnDeliveryAllowed" => true,
		);
		$client = $this->ukrposhtaApi->modelClientsPost( $client_arr );
		return $client;
    }

    public function getClientUuid($clientName, $addressId)
    {
        $client = $this->createClient( $addressId );
        if ( isset( $client['uuid'] ) ) {
            return $client['uuid'];
        } else {
            return $this->clientMsg .=  'Клієнт ' . $clientName .  ' не створений.';
        }
    }

}
