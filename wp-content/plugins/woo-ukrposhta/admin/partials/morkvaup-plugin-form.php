<?php
if ( ! session_id() ) {
    @session_start();
}

// If this file is called directly, abort.
defined( 'ABSPATH' ) or die();

use deliveryplugin\Ukrposhta\classes\invoice\Sender;
use deliveryplugin\Ukrposhta\classes\invoice\Recipient;
use deliveryplugin\Ukrposhta\classes\invoice\InvoiceOrder;
use deliveryplugin\Ukrposhta\classes\invoice\InvoiceController;
use deliveryplugin\Ukrposhta\classes\invoice\UkrposhtaApiClass;
use deliveryplugin\Ukrposhta\classes\invoice\InvoiceModel;

$invoiceController = new InvoiceController();
$sender = new Sender();
$senderType = $sender->getType();
$recipient = new Recipient();
$invoiceOrder = new InvoiceOrder();

$bearer = $sender->bearer;
$tbearer = $sender->tbearer;
$token = $sender->token;

if ( empty( $bearer ) || empty( $token ) ) {
    wp_redirect('admin.php?page=morkvaup_plugin&credentials=not_found');
}

$message = ''; // Invoice status messages
$invoice = array( "message" => '', "code" => '');
$isInternational = $invoiceController->isInternational;

// Order credentials
$order_id = $invoiceOrder->order_id;
$order_data = $invoiceOrder->getOrderData();
$weight = $invoiceOrder->getWeight(); // TODO
$length = $invoiceOrder->getLength();

$ukrposhtaApi = new UkrposhtaApiClass($bearer, $token, $tbearer);
if ( isset( $_POST['morkvaup_checkforminputs'] ) ) { // Аfter `Створити` button clicked

    // Create Addresses
    $senderAddrId = ( null !== $sender->getAddress() ) ? $sender->getAddress(): null;
    $recipientAddrId = ( null !== $recipient->getAddress($order_data) ) ? $recipient->getAddress($order_data): null;

    // Create Clients
    $senderClient = $sender->createClient( $senderAddrId );
    $message .= $invoiceController->displayRequestNotice( $senderClient );

    $recipientClient = $recipient->createClient( $recipientAddrId );
    $message .= $invoiceController->displayRequestNotice( $recipientClient );

    // Create Invoice
    $invoice = $ukrposhtaApi->modelShipmentsPost( $invoiceController->createInvoiceRequest( $senderClient, $senderAddrId, $recipientClient ) );
    $message .= $invoiceController->displayRequestNotice( $invoice );
}

// Save info about new invoice in DB
if ( ( isset( $senderClient['uuid'] ) && isset( $recipientClient['uuid'] ) ) && ! $isInternational ) {
    $invoiceModel = new InvoiceModel();
    if ( isset( $invoice['uuid'] ) ) {
        $invoiceModel->saveInvoiceRowDB($invoice, $order_id);
    }
}
echo '<br>';

require MORKVA_UKRPOSHTA_PLUGIN_DIR . 'admin/partials/invoice/view/invoice_header.php';
require MORKVA_UKRPOSHTA_PLUGIN_DIR . 'admin/partials/invoice/view/invoice_sender.php';
require MORKVA_UKRPOSHTA_PLUGIN_DIR . 'admin/partials/invoice/view/invoice_recipient.php';
require MORKVA_UKRPOSHTA_PLUGIN_DIR . 'admin/partials/invoice/view/invoice_params.php';
