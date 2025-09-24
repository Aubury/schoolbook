<?php
/**
 * Plugin Name: WC → Google Sheets (webhook, every new order)
 */

if ( ! defined('ABSPATH') ) exit;

// URL вашого Apps Script веб-хука:
const GS_WEBHOOK_URL = 'https://script.google.com/macros/s/AKfycbwtq2yan3ln8i8VHLRM0oxtddHzeaOn4rknIfcFWmGqFtULRUxXAZBD_dKqY-CIlfAK/exec';

add_action('woocommerce_new_order', function ($order_id) {
    $order = wc_get_order($order_id);
    if ( ! $order ) return;

    // Товари "Назва × к-ть"
    $items = [];
    foreach ($order->get_items() as $item) {
        $items[] = $item->get_name() . ' × ' . $item->get_quantity();
    }

    // Адреси в одну строку
    $billing_address  = trim(preg_replace('/\s+/', ' ', $order->get_formatted_billing_address()));
    $shipping_address = trim(preg_replace('/\s+/', ' ', $order->get_formatted_shipping_address()));

    $payload = [
        'order_id'         => $order->get_id(),
        'status'           => $order->get_status(),              // на момент створення
        'customer_name'    => trim($order->get_formatted_billing_full_name()),
        'email'            => $order->get_billing_email(),
        'phone'            => $order->get_billing_phone(),
        'payment_method'   => $order->get_payment_method_title(),
        'shipping_method'  => $order->get_shipping_method(),
        'total'            => $order->get_total(),
        'currency'         => $order->get_currency(),
        'items'            => implode(', ', $items),
        'billing_address'  => $billing_address,
        'shipping_address' => $shipping_address,
        'customer_note'    => $order->get_customer_note(),
        // (за потреби додайте власні мета-поля)
    ];

    $resp = wp_remote_post(GS_WEBHOOK_URL, [
        'timeout' => 8,
        'headers' => ['Content-Type' => 'application/json; charset=utf-8'],
        'body'    => wp_json_encode($payload),
    ]);

    if (is_wp_error($resp)) {
        error_log('[WC→Sheets] HTTP error: ' . $resp->get_error_message());
    } else {
        $code = wp_remote_retrieve_response_code($resp);
        if ($code < 200 || $code >= 300) {
            error_log('[WC→Sheets] Bad status: ' . $code . ' Body: ' . wp_remote_retrieve_body($resp));
        }
    }
}, 10, 1);
