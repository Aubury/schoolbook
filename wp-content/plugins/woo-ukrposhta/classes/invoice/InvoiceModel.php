<?php
namespace deliveryplugin\Ukrposhta\classes\invoice;

use deliveryplugin\Ukrposhta\classes\invoice\InvoiceOrder;
use Automattic\WooCommerce\Utilities\OrderUtil;

// If this file is called directly, abort.
defined( 'ABSPATH' ) or die();

class InvoiceModel
{
    public $isSuccessSaved;

    public function saveInvoiceRowDB($invoice, $order_id)
    {
        global $wpdb;
        if ( isset( $invoice['uuid'] ) ) {
            $barcode = $invoice['barcode'];

            $table_name = $wpdb->prefix.'uposhta_invoices';
            $hasRowWithOrderId = $wpdb->get_row( "SELECT * FROM $table_name WHERE order_id = $order_id" );
            $existId =  \is_object( $hasRowWithOrderId ) ? $hasRowWithOrderId->id : null;

            $result_check = $wpdb->replace(
                $table_name,
                array(
                    'id'               => $existId,
                    'order_id'         => $order_id,
                    'order_invoice'    => $barcode,
                    'invoice_ref'      => $invoice['uuid'],
                ),
              array( '%d', '%s', '%s', '%s' )
            );

            $this->isSuccessSaved = $result_check;
            $order = wc_get_order( $order_id ); // Get current order object

            if(class_exists( \Automattic\WooCommerce\Utilities\OrderUtil::class ) && OrderUtil::custom_orders_table_usage_is_enabled())
            {
                $order->update_meta_data( 'ukrposhta_ttn', $barcode );
            }
            else
            {
                update_post_meta( $order_id, 'ukrposhta_ttn', $barcode );
            }

            // Add a custom order note in the admin order edit page (right sidebar)
            $order->add_order_note( 'Укрпошта, номер ТТН: ' . $barcode);
            $order->save();
        }
    }
}
