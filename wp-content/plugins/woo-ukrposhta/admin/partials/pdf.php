<?php
require_once dirname(__FILE__, 6) . '/wp-load.php';
if (isset($_POST['generate_invoice_nonce'])) {
    $nonce = sanitize_text_field(wp_unslash($_POST['generate_invoice_nonce']));

    if(wp_verify_nonce( $nonce, 'generate_invoice_nonce_action' ))
    {
        header("Content-type:application/pdf");
        //header("filename=ttn.pdf");//deprecated on some hostings
        if(isset($_POST['download'])){
            header("Content-disposition: attachment; filename=ttn.pdf");
        }

        header("Content-disposition: inline; filename=ttn.pdf");

        require("api.php");

        if ( isset( $_POST['bearer'] ) && isset( $_POST['cp_token'] ) && isset( $_POST['ttn'] ) && isset( $_POST['type'] ) ) {
            $token = sanitize_text_field( wp_unslash( $_POST['bearer'] ) ); 
            $cptoken = sanitize_text_field( wp_unslash( $_POST['cp_token'] ) );
            $ttn = sanitize_text_field( wp_unslash( $_POST['ttn'] ) );
            $type = sanitize_text_field( wp_unslash( $_POST['type'] ) );

            $size = ''; 
            if ( $type == '1' ) {
                $size = '&size=SIZE_A4';
            } elseif ( $type == '2' ) {
                $size = '&size=SIZE_A5';
            } 

            $url = 'https://www.ukrposhta.ua/ecom/0.0.1/shipments/'.$ttn.'/sticker?token='.$cptoken.$size;
            $formurl = 'https://www.ukrposhta.ua/forms/ecom/0.0.1/';

            $authorization = "Bearer " . $token;  // Authorization token

            $args = array(
                'headers' => array(
                    'Authorization' => $authorization,
                    'Content-Type' => 'application/json',
                ),
            );

            $response = wp_remote_get( $url, $args );

            if ( is_wp_error( $response ) ) {
                $error_message = $response->get_error_message();
                echo esc_html( "Something went wrong: $error_message" );
            } else {
                echo wp_remote_retrieve_body( $response );
            }

        } else {
            echo esc_html( "Missing required parameters" );
        }
    }
}
