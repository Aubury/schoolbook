<?php
/**
 * Plugin Name: Orders XLSX Export (daily)
 * Description: Ежедневно формирует XLSX с заказами за последние 24 часа в /uploads/exports/.
 */

if ( ! defined('ABSPATH') ) exit;

register_activation_hook(__FILE__, function () {
    if ( ! wp_next_scheduled('orders_xlsx_export_daily') ) {
        // каждый день в 02:10 по времени WP
        wp_schedule_event(strtotime('tomorrow 02:10'), 'daily', 'orders_xlsx_export_daily');
    }
});

register_deactivation_hook(__FILE__, function () {
    wp_clear_scheduled_hook('orders_xlsx_export_daily');
});

// --- Запуск по крону (можете вызывать вручную: do_action('orders_xlsx_export_daily');)
add_action('orders_xlsx_export_daily', function () {
    // Укажите корректный путь к автозагрузчику Composer:
    $autoload = ABSPATH . 'vendor/autoload.php';
    if ( ! file_exists($autoload) ) {
        error_log('[Orders XLSX Export] Composer autoload not found: ' . $autoload);
        return;
    }
    require_once $autoload;

    // Дата-диапазон: последние 24 часа (можете изменить под себя)
    $date_to   = current_time('mysql'); // локальное WP время
    $date_from = gmdate('Y-m-d H:i:s', strtotime($date_to) - DAY_IN_SECONDS + ( get_option('gmt_offset') * HOUR_IN_SECONDS ));

    // Получаем заказы (пример: все статусы, можно сузить: processing/completed/on-hold)
    $orders = wc_get_orders([
        'type'         => 'shop_order',
        'status'       => ['processing', 'completed', 'on-hold', 'pending', 'failed', 'cancelled', 'refunded'],
        'date_created' => $date_from . '...' . $date_to,
        'limit'        => -1,
        'orderby'      => 'date',
        'order'        => 'ASC',
        'return'       => 'objects',
    ]);

    if ( empty($orders) ) {
        error_log('[Orders XLSX Export] За последние 24 часа заказов нет.');
        return;
    }

    // Готовим папку для экспорта
    $upload_dir = wp_upload_dir();
    $export_dir = trailingslashit($upload_dir['basedir']) . 'exports/';
    if ( ! wp_mkdir_p($export_dir) ) {
        error_log('[Orders XLSX Export] Не удалось создать папку: ' . $export_dir);
        return;
    }

    // Создаём книгу
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet       = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Orders');

    // Шапка
    $headers = [
        'A1' => 'Order ID',
        'B1' => 'Date',
        'C1' => 'Status',
        'D1' => 'Customer',
        'E1' => 'Email',
        'F1' => 'Phone',
        'G1' => 'Payment',
        'H1' => 'Shipping',
        'I1' => 'Total',
        'J1' => 'Currency',
        'K1' => 'Items (name × qty)',
        'L1' => 'Billing Address',
        'M1' => 'Shipping Address',
        'N1' => 'Notes',
    ];
    foreach ($headers as $cell => $label) {
        $sheet->setCellValue($cell, $label);
    }

    // Данные
    $row = 2;
    foreach ($orders as $order) {
        /** @var WC_Order $order */
        $items = [];
        foreach ($order->get_items() as $item) {
            $items[] = $item->get_name() . ' × ' . $item->get_quantity();
        }

        $sheet->setCellValue('A' . $row, $order->get_id());
        $sheet->setCellValue('B' . $row, $order->get_date_created() ? $order->get_date_created()->date_i18n('Y-m-d H:i:s') : '');
        $sheet->setCellValue('C' . $row, wc_get_order_status_name($order->get_status()));
        $sheet->setCellValue('D' . $row, trim($order->get_formatted_billing_full_name()));
        $sheet->setCellValue('E' . $row, $order->get_billing_email());
        $sheet->setCellValue('F' . $row, $order->get_billing_phone());
        $sheet->setCellValue('G' . $row, $order->get_payment_method_title());
        $sheet->setCellValue('H' . $row, $order->get_shipping_method());
        $sheet->setCellValue('I' . $row, $order->get_total());
        $sheet->setCellValue('J' . $row, $order->get_currency());
        $sheet->setCellValue('K' . $row, implode(', ', $items));
        $sheet->setCellValue('L' . $row, trim(preg_replace('/\s+/', ' ', $order->get_formatted_billing_address())));
        $sheet->setCellValue('M' . $row, trim(preg_replace('/\s+/', ' ', $order->get_formatted_shipping_address())));
        $sheet->setCellValue('N' . $row, $order->get_customer_note());
        $row++;
    }

    // Немного автоширины (без фанатизма)
    foreach (range('A', 'N') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Сохраняем
    $filename   = 'orders_' . date('Y-m-d_H-i', current_time('timestamp')) . '.xlsx';
    $filepath   = $export_dir . $filename;
    $public_url = trailingslashit($upload_dir['baseurl']) . 'exports/' . $filename;

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    try {
        $writer->save($filepath);
        error_log('[Orders XLSX Export] OK: ' . $filepath);
    } catch (\Throwable $e) {
        error_log('[Orders XLSX Export] Ошибка сохранения: ' . $e->getMessage());
        return;
    }

    // (Опционально) отправить ссылку на почту админа
    $admin_email = get_option('admin_email');
    wp_mail(
        $admin_email,
        'Ежедневный экспорт заказов (XLSX)',
        "Файл сформирован: {$public_url}"
    );
});
