<?php


require_once 'wp-load.php';
require __DIR__ . '/vendor/autoload.php';

use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;

/******************************************
 *         WOO API SETTINGS
 *****************************************/

$url = home_url();
$login = WOOCOMMERCE_LOGIN;
$password = WOOCOMMERCE_PASS;
$key = WOOCOMMERCE_API_CK;
$secret = WOOCOMMERCE_API_CS;

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
$ordersDirectory = __DIR__ . '/FTP_EXCHANGE/ORDERS/';

if (!file_exists($ordersDirectory)) {
    mkdir($ordersDirectory, 0777, true);
    chmod($ordersDirectory, 0777);
}

$data = file_get_contents('php://input'); // Получаем данные
$order = json_decode($data, true); // Преобразуем JSON в массив

function creatFileAllOrders ($order) {
    
    $data_order = wc_get_order($order['id']);
    $ukr_poshta_patronymic = $data_order->get_meta('mrkv_ua_shipping_ukr-poshta_address_patronymic');
    $nova_poshta_patronymic = $data_order->get_meta('mrkv_ua_shipping_nova-poshta_address_patronymic');
    $ukr_poshta_patronymic !== '' ? $patronymic = $ukr_poshta_patronymic : $patronymic = $nova_poshta_patronymic;
    
    $_update = [];
    $_update['NomerZakaza']         = $order['id'];
    $_update['KlientID']            = $order['customer_id'];
    $_update['email']               = $order['billing']['email'];
    $_update['phone']               = $order['billing']['phone'];
    $_update['first_name']          = $order['billing']['first_name'];
    $_update['last_name']           = $order['billing']['last_name'];
    $_update['patronymic']          = $patronymic;
    $_update['subtotal']            = array_sum(array_map(fn($i) => (float)$i['subtotal'], $order['line_items']));
    $_update['discount']            = '-' . $order['coupon_lines'][0]['discount'];
    $_update['SummaZakaza']         = $order['total'];
    $_update['coupon']              = $order['coupon_lines'];
    $_update['Valuta']              = $order['currency'];
    $_update['date_created']        = $order['date_created'];
    $_update['payment_method']      = $order['payment_method'];
    $_update['prepayment_amount']   = $order['prepayment_amount'];
    $_update['prepayment_status']   = $order['prepayment_status'];
    
    switch ( $order['status'] ) {
        case 'pending': $_update['status'] = 'Очікування оплати';
                        break;

        case 'processing': $_update['status'] = 'В обробці';
                           break;

        case 'completed': $_update['status'] = 'Виконано';
                           break;

        case 'cancelled': $_update['status'] = 'Скасовано';
                          break;

        case 'refunded': $_update['status'] = 'Повернено кошти';
                         break;

        case 'failed': $_update['status'] = 'Не вдалося';
                        break;

        case 'on-hold' : $_update['status'] = 'На утриманні';
    }

      /**
     * error - Failed payment. Data is incorrect
     * failure - Failed payment
     * reversed - Payment refunded
     * subscribed - Subscribed successfully framed
     * success - Successful payment
     * unsubscribed - Subscribed successfully deactivated
     */

        switch (  $order['payment_status'] ) {
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
        case 'cash_wait'      : $_update['payment_status'] = 'Очікується оплата готівкою';
            break;

        case 'invoice_wait'      : $_update['payment_status'] = 'Інвойс створений успішно, очікується оплата';
            break;

        case 'prepared'      : $_update['payment_status'] = 'Платіж створений, очікується його завершення відправником';
            break;

        case 'processing'      : $_update['payment_status'] = 'Платіж обробляється';
            break;

        case 'wait_accept'      : $_update['payment_status'] = 'Кошти з клієнта списані, але магазин ще не пройшов перевірку. Якщо магазин не пройде активацію протягом 60 днів, платежі будуть автоматично скасовані';
            break;

        case 'wait_secure'      : $_update['payment_status'] = 'Платіж на перевірці';
            break;

        case 'try_again'      : $_update['payment_status'] = 'Оплата неуспішна. Клієнт може повторити спробу ще раз';
            break;
            
        case 'cancelled'      : $_update['payment_status'] = 'Скасування платежу';
                 break;    

        default: $_update['payment_status'] = $order['payment_status'];
    }
    
    $_update['payment_detail'] = $order['payment_detail'];
    
    $full_shipping_address = $data_order->get_meta('_shipping_address_index');
    $full_billing_address = $data_order->get_meta('_billing_address_index');
    $nova_poshta_address_city = $data_order->get_meta('mrkv_ua_shipping_nova-poshta_city');
    $nova_poshta_address_flat = $data_order->get_meta('mrkv_ua_shipping_nova-poshta_address_flat');
    $ukr_poshta_address_flat = $data_order->get_meta('mrkv_ua_shipping_ukr-poshta_address_flat');
    $nova_poshta_address_flat !== '' ? $flat = $nova_poshta_address_flat : $flat = $ukr_poshta_address_flat;

    $_update['shipping']       = [
        'first_name'               => $order['shipping']['first_name'] ?? $order['billing']['first_name'],
        'last_name'                => $order['shipping']['last_name'] ?? $order['billing']['last_name'],
        'patronymic'               => $patronymic,
        'shipping_method'          => $order['shipping_lines']['0']['method_title'],
        'city'                     => $order['billing']['city'] ?? $order['shipping']['city'],
        'nova_poshta_address_city' => $nova_poshta_address_city,
        'postcode'                 => $order['billing']['postcode'] ?? $order['shipping']['postcode'],
        'address_1'                => $order['billing']['address_1'] ?? $order['shipping']['address_1'],
        'address_2'                => $order['billing']['address_2'] ?? $order['shipping']['address_2'],
        'ukr_poshta_address_flat'  => $ukr_poshta_address_flat,
        'nova_poshta_address_flat' => $nova_poshta_address_flat,
        'flat'                     => $flat,
        'full_shipping_address'    =>  $full_shipping_address,
        'full_billing_address'     =>  $full_billing_address,
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