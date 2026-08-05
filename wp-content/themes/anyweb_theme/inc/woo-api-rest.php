<?php
/**
 * Woo Api Rest
 */

add_action('rest_api_init', function () {
    register_rest_field('shop_order', 'prepayment_amount', [
        'get_callback' => function ($order_arr) {
            return get_post_meta($order_arr['id'], '_prepayment_amount', true);
        },
        'schema' => null,
    ]);

    register_rest_field('shop_order', 'prepayment_status', [
        'get_callback' => function ($order_arr) {
            return get_post_meta($order_arr['id'], '_prepayment_status', true);
        },
        'schema' => null,
    ]);

    register_rest_field('shop_order', 'payment_status', [
        'get_callback' => function ($order_arr) {
            return get_post_meta($order_arr['id'], 'payment_status', true);
        },
        'schema' => null,
    ]);

    register_rest_field('shop_order', 'payment_detail', [
        'get_callback' => function ($order_arr) {
            return get_post_meta($order_arr['id'], '_payment_detail', true);
        },
        'schema' => null,
    ]);
});