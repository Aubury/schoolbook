<?php

phpinfo();

require_once 'wp-load.php';
require __DIR__ . '/vendor/autoload.php';

use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;

/******************************************
 *         WOO API SETTINGS
 *****************************************/

$url = home_url();
$login = 'scbook';
$password = 'bgm5naOZM(yGv6HU#d';
$key = 'ck_740f2455bb10ddbaaa66deb7acf66000bbaa306b';
$secret = 'cs_acd3f9214481e54579edf56fe84016efac403aa3';

$woocommerce = new Client(
    $url,
    $key,
    $secret,
    [
        'wp_api' => true,
        'version' => 'wc/v3',
        'verify_ssl' => false, // Отключение проверки SSL
        'timeout' => 60, // Увеличьте время ожидания
    ]
);

/*************
/* FTP directory
 **************/
$ordersDirectory = __DIR__ . '/public_html/ORDERS/';

if (!file_exists($ordersDirectory)) {
    mkdir($ordersDirectory, 0777, true);
    chmod($ordersDirectory, 0777);
}

$data = file_get_contents('php://input'); // Получаем данные
$order = json_decode($data, true); // Преобразуем JSON в массив

function creatFileAllOrders ($order) {
    $_update = [];
    $_update['NomerZakaza']    = $order['id'];
    $_update['KlientID']       = $order['customer_id'];
    $_update['email']          = $order['billing']['email'];
    $_update['phone']          = $order['billing']['phone'];
    $_update['first_name']     = $order['billing']['first_name'];
    $_update['last_name']      = $order['billing']['last_name'];
    $_update['SummaZakaza']    = $order['total'];
    $_update['Valuta']         = $order['currency'];
    $_update['date_created']   = $order['date_created'];
    $_update['payment_method'] = $order['payment_method'];
    $_update['status']         = $order['status'];

    foreach ( $order['meta_data'] as $meta ) {
        if ( $meta->key === 'payment_status' ) {

            switch ( $meta->value ) {
                case 'error'        : $_update['payment_status'] = 'Невдала оплата. Дані невірні';
                    break;
                case 'failure'      : $_update['payment_status'] = 'Невдала оплата';
                    break;
                case 'reversed'     : $_update['payment_status'] = 'Оплата повернута';
                    break;
                case 'subscribed'   : $_update['payment_status'] = 'Підписка успішно оформлена';
                    break;
                case 'unsubscribed' : $_update['payment_status'] = 'Підписку успішно деактивовано';
                    break;
                case 'success'      : $_update['payment_status'] = 'Успішна оплата';
                    break;
            }

        }
    }

    $_update['shipping']       = [
        'shipping_method' => $order['shipping_lines']['0']['method_title'],
        'city'      => $order['billing']['city'] ?? $order['shipping']['city'],
        'postcode'  => $order['billing']['postcode'] ?? $order['shipping']['postcode'],
        'address_1' => $order['billing']['address_1'] ?? $order['shipping']['address_1'],
        'address_2' => $order['billing']['address_2'] ?? $order['shipping']['address_2'],

    ];

    foreach ($order['line_items'] as $index => $item ) {
        $meta_data = wc_get_product($item['product_id']);

        $Kod1C = $meta_data->get_meta('Kod1C') ?? '';
        $UniID = $meta_data->get_meta('UniID') ?? '';
        $ISBN = $meta_data->get_meta('ISBN') ?? '';

        $_update['Tovary'][$index] = [
            'SKU'         => $item['sku'],
            'Kod1C'       => $Kod1C,
            'UniID'       => $UniID,
            'ISBN'        => $ISBN,
            'Name'        => $item['name'],
            'MainPrice'   => $item['price'],
            'Kolichestvo' => $item['quantity'],
            'Summa'       => $item['total']
        ];
    }

    $update['Order'] = $_update;

    return $update['Order'];
}

// Проверяем данные заказа
if (isset($order['id'])) {
    $action = $_SERVER['HTTP_X_WC_WEBHOOK_EVENT'];
    $order_file = $ordersDirectory . 'order_' . $order['id'] . '_' . $action . '.log';
    $update = creatFileAllOrders($order);
    file_put_contents($order_file, json_encode($update, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    chmod($order_file, 0777);
}

// Ответ для WooCommerce
http_response_code(200); // Возвращаем успешный статус