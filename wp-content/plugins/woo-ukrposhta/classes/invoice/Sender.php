<?php

namespace deliveryplugin\Ukrposhta\classes\invoice;

use deliveryplugin\Ukrposhta\classes\invoice\UkrposhtaApiClass;

// If this file is called directly, abort.
defined( 'ABSPATH' ) or die();

class Sender
{
	public $bearer = '';
    public $tbearer = '';
    public $token = '';

	public $ukrposhtaApi;

    public $addressMsg = '';

	public $clientMsg = '';

    public function __construct()
    {
        $this->bearer = $this->get_bearer();
        $this->tbearer = $this->get_tbearer();
        $this->token = $this->get_token();
		$this->ukrposhtaApi = new UkrposhtaApiClass( $this->bearer, $this->token, $this->tbearer);
    }

    /**
     * Get PROD BEARER eCom
     * @return string
     */
    public function get_bearer()
    {
        return $this->bearer = \get_option( 'production_bearer_ecom' );

    }

    /**
     * Get PROD BEARER Status Tracking
     * @return string
     */
    public function get_tbearer()
    {
        return $this->tbearer = \get_option( 'production_bearer_status_tracking' );
    }

    /**
     * Get PROD COUNTERPARTY TOKEN
     * @return string
     */
    public function get_token()
    {
        return $this->token = \get_option( 'production_cp_token' );
    }

	public function getAddress()
	{
		if (isset($_POST['mrkv_up_my_form_nonce'])) {
            // Remove escaping from input data
            $nonce = sanitize_text_field(wp_unslash($_POST['mrkv_up_my_form_nonce']));

            if(wp_verify_nonce($nonce, 'mrkv_up_form_action'))
            {
                if ( isset( $_POST['index1'] ) ) {
			        $address = $this->ukrposhtaApi->modelAdressPost( array( "postcode" => sanitize_text_field( wp_unslash( $_POST['index1'] ) ) ) );
			        if ( isset( $address['id'] ) ) {
			            return $this->addressId = $address['id'];
			        } else {
			            $failed = true;
			            $this->addressMsg .= 'Помилка в поштовому індексі Відправника. ';
			            $this->addressMsg .= $address['message'] . '. ';
			        }
			    } 
            }
            else {
		        $this->addressMsg .= 'Відстуній поштовий індекс Відправника. ';
		    }
        }
        else {
	        $this->addressMsg .= 'Відстуній поштовий індекс Відправника. ';
	    }
	}

	public function getType(): string
	{
		$up_sender_type = \sanitize_text_field( \get_option( 'up_sender_type' ));

        if (isset($_POST['mrkv_up_my_form_nonce'])) {
            // Remove escaping from input data
            $nonce = sanitize_text_field(wp_unslash($_POST['mrkv_up_my_form_nonce']));

            if(wp_verify_nonce($nonce, 'mrkv_up_form_action'))
            {
                $up_sender_type = ( isset( $_POST['up_sender_type'] ) ) ? sanitize_text_field( wp_unslash( $_POST['up_sender_type'] ) ) : sanitize_text_field( get_option( 'up_sender_type' ) );
            }
        }
		return $up_sender_type;
	}

	public function getFirstName() : string
	{
		$sender_first_name = \sanitize_text_field( get_option( 'names1' ) );

        if (isset($_POST['mrkv_up_my_form_nonce'])) {
            // Remove escaping from input data
            $nonce = sanitize_text_field(wp_unslash($_POST['mrkv_up_my_form_nonce']));

            if(wp_verify_nonce($nonce, 'mrkv_up_form_action'))
            {
                $sender_first_name = isset( $_POST['sender_first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['sender_first_name'] ) ) : sanitize_text_field( get_option( 'names1' ) );
            }
        }
		return $sender_first_name;
	}

	public function getMiddleName() : string
	{
		return ( null !== \get_option( 'names3' ) ) ?? sanitize_text_field( \get_option( 'names3' ) );
	}

	public function getLastName() : string
	{
		$sender_last_name = \sanitize_text_field( \get_option( 'names2' ) );

        if (isset($_POST['mrkv_up_my_form_nonce'])) {
            // Remove escaping from input data
            $nonce = sanitize_text_field(wp_unslash($_POST['mrkv_up_my_form_nonce']));

            if(wp_verify_nonce($nonce, 'mrkv_up_form_action'))
            {
                $sender_last_name = isset( $_POST['sender_last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['sender_last_name'] ) ) : sanitize_text_field( get_option( 'names2' ) );
            }
        }
		return $sender_last_name;
	}

	public function getPhoneNumber()
	{
		$phone = \sanitize_text_field( \get_option( 'phone' ) );

        if (isset($_POST['mrkv_up_my_form_nonce'])) {
            // Remove escaping from input data
            $nonce = sanitize_text_field(wp_unslash($_POST['mrkv_up_my_form_nonce']));

            if(wp_verify_nonce($nonce, 'mrkv_up_form_action'))
            {
                $phone = isset( $_POST['phone1'] ) ? sanitize_text_field( wp_unslash( $_POST['phone1'] ) ) : sanitize_text_field( get_option( 'phone' ) );
            }
        }
		return $phone;
	}

	public function getName() : string
	{
		$up_company_sender_name = \sanitize_text_field( \get_option( 'up_company_name' ) );

        if (isset($_POST['mrkv_up_my_form_nonce'])) {
            // Remove escaping from input data
            $nonce = sanitize_text_field(wp_unslash($_POST['mrkv_up_my_form_nonce']));

            if(wp_verify_nonce($nonce, 'mrkv_up_form_action'))
            {
                $up_company_sender_name = isset( $_POST['up_company_sender_name'] ) ? sanitize_text_field( wp_unslash( $_POST['up_company_sender_name'] ) ) : sanitize_text_field( get_option( 'up_company_name' ) );
            }
        }
		return $up_company_sender_name;
	}

	public function getEdrpou()
	{
		$up_company_sender_edrpou = '';

        if (isset($_POST['mrkv_up_my_form_nonce'])) {
            // Remove escaping from input data
            $nonce = sanitize_text_field(wp_unslash($_POST['mrkv_up_my_form_nonce']));

            if(wp_verify_nonce($nonce, 'mrkv_up_form_action'))
            {
                $up_company_sender_edrpou = isset( $_POST['up_company_sender_edrpou'] ) ? sanitize_text_field( wp_unslash( $_POST['up_company_sender_edrpou'] ) ) : '';
            }
        }
		return $up_company_sender_edrpou;
	}

	public function getTin()
	{
		$up_sep_tin = \sanitize_text_field( \get_option( 'up_tin' ) );

        if (isset($_POST['mrkv_up_my_form_nonce'])) {
            // Remove escaping from input data
            $nonce = sanitize_text_field(wp_unslash($_POST['mrkv_up_my_form_nonce']));

            if(wp_verify_nonce($nonce, 'mrkv_up_form_action'))
            {
                $up_sep_tin = isset( $_POST['up_sep_tin'] ) ? sanitize_text_field( wp_unslash( $_POST['up_sep_tin'] ) ) : sanitize_text_field( get_option( 'up_tin' ) );
            }
        }
		return $up_sep_tin;
	}

	public function createClient($addressId)
	{
		$client_arr = array(
			"type"			=> $this->getType(),
    	    "name"			=> $this->getName(),
    	    "edrpou"		=> $this->getEdrpou(),
            "tin"           => $this->getTin(),
    	    "firstName"		=> $this->getFirstName(),
    	    "middleName"    => $this->getMiddleName(),
    	    "lastName"		=> $this->getLastName(),
    	    "addressId"		=> $addressId,
    	    "phoneNumber"	=> $this->getPhoneNumber(),
    	    "bankAccount"	=> \sanitize_text_field( \get_option('mrkvup_sender_iban') ) ?? '',
		);
		return $this->ukrposhtaApi->modelClientsPost( $client_arr );
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
