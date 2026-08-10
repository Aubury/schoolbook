<?php

if (!defined("ABSPATH")) {
    exit;
}

require_once(WP_PLUGIN_DIR . '/mrkv-liqpay-extended/includes/class-wc-gateway-morkva-liqpay.php');
require_once(WP_PLUGIN_DIR . '/mrkv-liqpay-extended/includes/classes/MorkvaLiqPay.php');

/**
 * Обработка предоплаты через LiqPay при выборе "Оплата при отриманні"
 */

add_action('woocommerce_checkout_order_processed', 'redirect_to_liqpay_for_prepayment');
function redirect_to_liqpay_for_prepayment($order_id) {
    $order = wc_get_order($order_id);
    $total = $order->get_total();

    if ($total >= 300 && isset($_POST['payment_method']) && $_POST['payment_method'] === 'cod') {
        $prepayment = 200;

        $gateway_morkva_liqpay = new WC_Gateway_Morkva_Liqpay();

        # Check test mode
        if($gateway_morkva_liqpay->get_option( 'test_enabled_admin' ) == 'yes' && ( current_user_can('editor') || current_user_can('administrator') ))

            // Store Order ID in session, so it can be re-used after payment failure.
            WC()->session->set( 'order_awaiting_payment', $order_id );
        WC()->session->save_data();

        // Process Payment.
        $result = process_payment( $order_id );

        // Redirect to success/confirmation/payment page.
        if ( isset( $result['result'] ) && 'success' === $result['result'] ) {
            $result['order_id'] = $order_id;

            $result = apply_filters( 'woocommerce_payment_successful_result', $result, $order_id );

            if ( ! wp_doing_ajax() ) {
                // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
                wp_redirect( $result['redirect'] );
                exit;
            }

            // Using wp_send_json will gracefully handle any problem encoding data.
            wp_send_json( $result );
        }
    }
}


/**
 * Process the payment and return the result.
 *
 * @param int $order_id Order ID
 * @return array
 */
function process_payment($order_id) {
    $prepayment = 200;
    # Get order data by id
    $order = wc_get_order($order_id);
    $gateway_morkva_liqpay = new WC_Gateway_Morkva_Liqpay();

    # Check order total
    if ($order->get_total() > 0)
    {
        # Send email notification
        pending_new_order_notification($order->get_id());
    }

    # Remove cart data
    WC()->cart->empty_cart();

    # Check test mode
    if ($gateway_morkva_liqpay->get_option( 'test_enabled_admin' ) == 'yes'
        && ( current_user_can('editor') || current_user_can('administrator') )
    ) {
        # Use test keys
        $morkva_liqPay = new MorkvaLiqPay($gateway_morkva_liqpay->get_option('test_public_key'),
            $gateway_morkva_liqpay->get_option('test_private_key'));

    } elseif ($gateway_morkva_liqpay->get_option( 'test_enabled' ) == 'yes'
        && $gateway_morkva_liqpay->get_option( 'test_enabled_admin' ) != 'yes') {
        # Use test keys
        $morkva_liqPay = new MorkvaLiqPay($gateway_morkva_liqpay->get_option('test_public_key'),
            $gateway_morkva_liqpay->get_option('test_private_key'));

    } else {
        # Use main keys
        $morkva_liqPay = new MorkvaLiqPay($gateway_morkva_liqpay->get_option('public_key'),
            $gateway_morkva_liqpay->get_option('private_key'));
    }

    # Create argument of query
    $arrayData = array(
        'version' => '3',
        'action' => 'pay',
        'amount' => $prepayment,
        'currency' => $order->get_currency(),
        'description' => __('Передоплата замовлення № ', 'mrkv-liqpay-extended') . $order_id,
        'order_id' => $order->get_id(),
        'result_url' => $gateway_morkva_liqpay->get_return_url($order),
        'language' => 'uk',
        'server_url' => WC()->api_request_url( 'WC_Gateway_Morkva_Liqpay' )
    );

    # Create result link
    $url = $morkva_liqPay->cnb_link($arrayData);

    # Return result
    return array(
        'result' => 'success',
        'redirect' => $url,
    );
}

/**
 * New order notification function
 *
 * @param $order_id Order id
 */
function pending_new_order_notification($order_id) {
    # Get order data
    $order = wc_get_order($order_id);

    # Only for "pending" order status
    if (!$order->has_status('pending')) return;

    # Get an instance of the WC_Email_New_Order object
    $wc_email = WC()->mailer()->get_emails()['WC_Email_New_Order'];

    # Create email data
    $wc_email->settings['subject'] = '{site_title} - ' . __('Нове замовлення', 'mrkv-liqpay-extended') . ' ({order_number}) - {order_date}';
    $wc_email->settings['heading'] = __('Нове замовлення', 'mrkv-liqpay-extended');

    # Send email
    $wc_email->trigger($order_id);
}

add_action('woocommerce_thankyou', 'show_modal_on_success_payment', 10, 1);
function show_modal_on_success_payment( $order_id ) {
    // Получаем объект заказа
    $order = wc_get_order( $order_id );

    $payment_status = $order->get_status();

    $custom_preorder_countdown = null;
    $flag = false;
    // Проходим по товарам в заказе
    foreach( $order->get_items() as $item_id => $item ) {
        $product_id = $item->get_product_id(); // Получаем ID товара
        $product = wc_get_product( $product_id );

        if ($product->is_on_backorder()) {
            $custom_preorder_countdown = get_post_meta($product_id, '_custom_preorder_countdown', true);
            $flag = true;
            break;
        }
    }

    $order_status = $order->get_status(); //processing

    if ( $flag && $order_status == 'processing') {
        // Выводим JavaScript для модального окна
        ?>
        <style>
            .modal.show {
                background-color: rgba(0,0,0,0.7);
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .modal {
                display: none;
            }

            .modal .modal-content {
                max-width: 90%;
                width: 560px;
            }
        </style>

        <!-- Само модальное окно -->
        <div class="modal fade show" tabindex="-1" aria-hidden="true" id="custom-modal"
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h3 class="modal-title text-center" id="preorderModalLabel">
                        ДЯКУЄМО, ВАШЕ ЗАМОВЛЕННЯ ЗАПЛАНОВАНО ДО ВІДПРАВКИ НА<br><span class="fw-bolder"><?php echo date('d.m.Y', strtotime($custom_preorder_countdown)) ?></span>
                    </h3>
                </div>
            </div>
        </div>
        </div>

        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('.btn-close').on('click', function (e) {
                    e.preventDefault();
                    $('#custom-modal').removeClass('show');
                })
            });
        </script>
        <?php

    }
}