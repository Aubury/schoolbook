<?php

namespace deliveryplugin\Ukrposhta\classes\invoice;

use deliveryplugin\Ukrposhta\classes\invoice\InvoiceOrder;
use deliveryplugin\Ukrposhta\classes\invoice\UkrposhtaApiClass;
use deliveryplugin\Ukrposhta\classes\invoice\Sender;
use deliveryplugin\Ukrposhta\classes\invoice\Recipient;
use Automattic\WooCommerce\Utilities\OrderUtil;


// If this file is called directly, abort.
defined( 'ABSPATH' ) or die();

class InvoiceController
{
	public $isInternational;

    public function __construct()
    {
        $this->isInternational = $this->is_international();
    }

	public function isInvoiceFailed($request)
	{
		if ( ! isset( $request['uuid'] ) ) { // If API UkrPoshta response was failed
		    return true;
		}
		return false;
	}

	public function isOrdersReferer()
	{
		if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
			$referer_url = parse_url( $_SERVER['HTTP_REFERER'], PHP_URL_QUERY );
			parse_str( $referer_url, $query_url_arr );
			if ( ! empty( $query_url_arr['post'] ) && 'edit' == $query_url_arr['action'] ) {
				return true;
			} else {
				return false;
			}
		}


		if ( isset( $order_data ) && is_object( $order_data ) ) {
			return true;
		} else {
			return wp_die('<h3>Для створення накладної перейдіть на <a href="edit.php?post_type=shop_order">сторінку Замовлення</a></h3>');
		}
	}

	public function is_international()
	{
		$invoiceOrder = new InvoiceOrder();
		$order_data = $invoiceOrder->order_data;
		if ( is_object( $order_data ) ) {
			if($order_data->get_billing_country())
			{
				return ( 'UA' == $order_data->get_billing_country() ) ? false : true;
			}
			else
			{
				return false;
			}
		}
		return true;
	}

	public function createInvoiceRequest($senderClient, $senderAddrId, $recipientClient)
	{
		$invoiceOrder = new InvoiceOrder();
		$invoice_arr = array(
	    	"sender" 			=> array( "uuid" => $senderClient['uuid'] ),
	    	"recipient" 		=> array( "uuid" => $recipientClient['uuid'] ),
	    	"type" 				=> $invoiceOrder->getShipmentType(),
	        "packageType"       => get_option( 'senduptype' ),
	        "senderAddressId"   => $senderAddrId,
	    	"deliveryType"		=> $invoiceOrder->getDeliveryType(),
	    	"paidByRecipient"	=> $invoiceOrder->isPaidByRecipient(),
	        "weight"            => $invoiceOrder->getWeight(),
	        "length"            => $invoiceOrder->getLength(),
	    	"description" 		=> $invoiceOrder->getDescription(),
	    	"onFailReceiveType" => $invoiceOrder->getOnFailReceveType(),
	    	"postPay" 			=> $invoiceOrder->getPostPay(),
	    	"parcels"			=> array( array(
	    		"weight"			=> $invoiceOrder->getWeight(),
	    		"length"			=> $invoiceOrder->getLength(),
	    		"declaredPrice" 	=> $invoiceOrder->getDeclaredPrice(),
	    )),
	    "checkOnDelivery"	=> true,
	    "transferPostPayToBankAccount" => $invoiceOrder->isShowSenderBankAccountOnSticker()
	    );
		error_log('$invoice_arr MorkvaUP');error_log(print_r($invoice_arr,1));
	    return $invoice_arr;
	}

	public function displayNav()  // display nav bar
	{
		$tabs = array(
			array(
				'slug' => 'morkvaup_plugin', 'label' => 'Налаштування'
			),
			array(
				'slug' => 'morkvaup_invoice', 'label' => 'Відправлення'
			),
			array(
				'slug' => 'morkvaup_invoices', 'label' => 'Мої відправлення'
			),
		);

		echo "<nav class=\"newnaw nav-tab-wrapper woo-nav-tab-wrapper\">";
		$tab = $_GET['page'];
		for( $i=0; $i<sizeof( $tabs ); $i++ ) {
			$echoclass = 'nav-tab';
			if ( $tab == $tabs[$i]['slug']) {
				$echoclass = 'nav-tab-active nav-tab';
			}
			echo '<a href=admin.php?page='.$tabs[$i]['slug'].' class="'.$echoclass.'">'.$tabs[$i]['label'].'</a>';
		}
		echo "</nav>";
	}

	public function displaySuccessNotice($invoice)
	{
		echo '<h3>Відправлення ' . $invoice['barcode'] . ' успішно створене!</h3><p>';
		echo 'Тип відправлення: ' . $invoice['type'] . '<br>';
		echo 'Відправник: ' . $invoice['sender']['name'] . '</br>';
		echo 'Адреса відправлення: '. $invoice['sender']['addresses'][0]['address']['postcode'] . ' ';
		echo $invoice['sender']['addresses'][0]['address']['detailedInfo'] . '<br>';
		echo 'Одержувач: ' . $invoice['recipient']['name'] . '</br>';
		echo 'Адреса отримання: ' . $invoice['recipient']['addresses'][0]['address']['postcode'] . ' ';
		echo $invoice['recipient']['addresses'][0]['address']['detailedInfo'] . '<br></p>';
	}

	public function displayWarningNotice()
	{
		$invoiceOrder = new InvoiceOrder();
		if ( $this->isInternational ) {
		} else {
			echo '<p>Встановлена довжина відправлення за замовчуванням: ' . $invoiceOrder->getWPOptionLength() . ' см.</p>';
		}
	}

	public function displayRequestNotice($request)
	{
	    if ( $this->isInvoiceFailed( $request ) ) {
	        if ( $this->isInternational ) {
	            $error_code = $request['code'] ?? ' немає';
	            return '<br>Помилка з API Укрпошта: ' . $request['message'] .
					' (Код помилки: ' . $error_code . ')';
	        } else {
	            $error_code = $request['code'] ?? ' немає';
	            return '<br>Помилка з API Укрпошта: ' . $request['message'] .
					' (Код помилки: ' . $error_code . ')';
	        }
	    }
	}

	public function saveInvoiceInDB($invoice, $order_data) : array // TODO
	{
		try {
			$response = array();
			$ref = $invoice['uuid'];
		    $barcode = $invoice['barcode'];
		    global $wpdb;
		    $query = 'INSERT INTO ' . $wpdb->prefix . 'uposhta_invoices (order_id, order_invoice, invoice_ref) VALUES ("'.$order_data->get_id() . '", "' . $barcode . '", "' . $ref . '");';
		    //echo $query;
		    $requested = true;
		    $wpdb->query( $query );
		    $order = wc_get_order( $order_data->get_id() );

		    $meta_key = 'ukrposhta_ttn';

		    if(class_exists( \Automattic\WooCommerce\Utilities\OrderUtil::class ) && OrderUtil::custom_orders_table_usage_is_enabled())
            {
            	$order->update_meta_data( $meta_key, $barcode );
            }
            else
            {
            	update_post_meta( $order_id, $meta_key, $barcode );
            }

		    $note = "Укрпошта, номер ТТН: " . $barcode;
		    $order->add_order_note( $note );
		    $order->save();
			return $response[] = true; // записати в змінну $requested = в файлі форми
		}
		catch( Exception $e ) {
			echo 'Error writing to database: ',  $e->getMessage(), "\n";
			return $response[] = 'Error writing to database: ' . $e->getMassage(); // записати в $message .= у файлі форми
		}
	}

}
