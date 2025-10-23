<?php

require_once 'wp-load.php';
require __DIR__ . '/vendor/autoload.php';

use Automattic\WooCommerce\Client;

$root_path = ABSPATH;

// 1. Проверяем URL
$url = home_url();
if (!preg_match('/^https?:\/\//', $url)) {
    $url = 'http://' . $url; // Подстраховка, если home_url() возвращает без протокола
}

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
//        'query_string_auth' => true,
        'verify_ssl' => false, // Отключение проверки SSL
        'timeout' => 120, // Увеличьте время ожидания // Force Basic Authentication as query string true and using under HTTPS
    ]
);

//echo '<pre>';
//print_r($woocommerce->get('orders'));
//echo '</pre>';

/******************************************
 *             SETTINGS
 ******************************************/

error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/FTP_EXCHANGE/ERROR_LOG/error_log.txt');
ini_set('max_execution_time', 6000);
ini_set('memory_limit', '512M');
ini_set('post_max_size', '100M');
ini_set('upload_max_filesize', '256M');
ini_set('max_input_vars', 5000);
ini_set('display_startup_errors', 1);
set_time_limit(0);
ignore_user_abort(true);
ob_start("fatal_error_handler");
set_error_handler('error_handler');
set_exception_handler('exception_handler');
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function error_handler($code, $msg, $file, $line) {
    global $error_info, $errorDirectory;
    $_file = $errorDirectory . $error_info;
    $allNameError = "Произошла ошибка $msg ($code)\n $file ($line)";
    file_put_contents($_file, $allNameError, FILE_APPEND);

    return;
}

function exception_handler($exception) {
    global $error_info, $errorDirectory;
    $err_file = $errorDirectory . $error_info;
    $trace = $exception->getTrace();
    $msg = $exception->getMessage();
    $file = $trace[0]['file'];
    $line = $trace[0]['line'];
    $allNameError = "Произошла ошибка $msg \n $file ($line)";
    file_put_contents($err_file, $allNameError, FILE_APPEND);

    return;
}

function fatal_error_handler($buffer) {
    global $error_info, $errorDirectory;
    $file = $errorDirectory . $error_info;
    if (preg_match("|(Fatal error</b>:)(.+)(<br)|", $buffer, $regs) ) {
        file_put_contents($file, $buffer, FILE_APPEND);
    }

    return $buffer;
}

/******************************************
 *            END SETTINGS
 ******************************************/

file_put_contents(__DIR__ . '/FTP_EXCHANGE/cron-log.txt', date('Y-m-d H:i:s') . " - Cron запущен\n", FILE_APPEND);


/******************************************
 *            DIRECTORY
 /******************************************/

$ftpDirectory = __DIR__ . '/FTP_EXCHANGE/';
$ordersDirectory = __DIR__ . '/FTP_EXCHANGE/ORDERS/';
$imagesDirectory =  __DIR__ . '/FTP_EXCHANGE/IMAGES/';
$imagesDirectoryURL  = home_url('/FTP_EXCHANGE/IMAGES/');
$importDirectory = __DIR__ . '/FTP_EXCHANGE/IMPORT_LOG/';
$errorDirectory = __DIR__ . '/FTP_EXCHANGE/ERROR_LOG/';
$stateFile = __DIR__ . '/FTP_EXCHANGE/directory_state.json';

$import_info = 'import_log_' . date('d-m-Y') . '.log'; // Логирование импорт
$error_info = 'log_' . date('d-m-Y') . '.log';
$orders_info = 'orders_' . date('d-m-Y H.i.s') . '.log';
$importFile = 'Tovary.json';

$importFilePath = $ftpDirectory . $importFile;

function ensure_directory($path) {
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
        chmod($path, 0777);
    }
}

$directories = [
    $ftpDirectory,
    $importDirectory,
    $imagesDirectory,
    $ordersDirectory,
    $errorDirectory
];

array_map('ensure_directory', $directories);

// initialize the application WooCommerce
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/******************************************
 *           END DIRECTORY
 ******************************************/

/******************************************
 *           GLOBAL VARIABLES
 ******************************************/
global $wpdb;
define ( 'DB_PREFIX', $wpdb->prefix );

$allCategories = $woocommerce->get('products/categories', array('per_page' => 100, 'page' => 1));
$allAttributes = $woocommerce->get('products/attributes');
$subcategories = array_filter((array)$allCategories, fn($category) => $category->parent != 0);

/******************************************
 *         END GLOBAL VARIABLES
 ******************************************/

/******************************************
 *      FUNCTIONS
 ******************************************/

function getAttributeIdByName ($name) {
    global $allAttributes;

    foreach ($allAttributes as $attribute) {
        if ($attribute->name == $name) {
            return $attribute->id;
        }
    }

    return false;
}

/**
 * @return array[]
 *  Attributes
 */
function generateAttributes( $product )
{
    $authors = normalize_list($product['Autors']);
    !empty($authors) ? $authorsName = $authors : $authorsName = '';

    $attributeMap = [
            'Автор'                   => $product['AutorBook'] ?? '',
            'Автори'                  => $authorsName,
            'Кількість стрінок'       => $product['NumberOfPages'] ?? '',
            'Код прайса'              => $product['PriceCode'] ?? '',
            'Короткий опис'           => $product['BriefDescription'] ?? '',
            'КТ Книги для дошкільнят' => $product['BooksForPreschoolers'] ?? '',
            'Мова видання'            => $product['LanguageOfThePublication'] ?? '',
            'Обкладинка'              => $product['Cover'] ?? '',
            'Рік видання'             => $product['YearOfPublication'] ?? '',
            'Розмір'                  => $product['Size'] ?? '',
            'Стандарт пачки'          => $product['PackStandard'] ?? '',
            'Серія книги'             => $product['BookSeries'] ?? '',
            'Книги для всіх'          => $product['BooksForEveryone'] ?? '',
            'Виробник'                => $newData['Publisher'] ?? '',
            'Вік'                     => $product['Age'] ?? '',
            'Книжки на картоні'       => $product['BooksForPreschoolers'] ?? '',
            'КТ Книги для молодших школярів' => $product['BooksForYoungerStudents'] ?? '',
            'КТ Книги для школярів середнього та старшого віку' => $product['BooksForMiddleAndHighSchoolStudents'] ?? '',
    ];

    return $attributeMap;
}

function clean_name($s): string
{
    $s = preg_replace('/[\p{P}\p{S}]+$/u', '', (string)$s); // сносим хвостовые . , … и т.п.
    $s = preg_replace('/\s+/u', ' ', $s);
    return trim($s);
}

/**
 * Нормализуем список авторов:
 * - принимает строку "Ім'я., Прізвище; Інший Автор" или массив имен
 * - режем по , ; |
 * - у каждого имени убираем пунктуацию в КОНЦЕ (.,;:…— и т.п.)
 * - схлопываем пробелы
 * - возвращаем уникальный массив имен без пустых элементов
 */
function normalize_authors($raw): string
{
    // получаем массив
    $items = is_array($raw) ? $raw : preg_split('/[,;|]+/u', (string)$raw);

    $cleanOne = function ($s) {
        // убрать хвостовую пунктуацию (точки, запятые, тире, кавычки, многоточия и т.п.)
        $s = preg_replace('/[\p{P}\p{S}]+$/u', '', (string)$s);
        // схлопнуть лишние пробелы
        $s = preg_replace('/\s+/u', ' ', $s);
        return trim($s);
    };

    $out = array_map($cleanOne, $items);
    // убрать пустые и дубли
    $out = array_values(array_unique(array_filter($out, fn($v) => $v !== '')));

    return implode(', ', $out);
}


function normalize_list($raw): array {
    if (is_array($raw)) {
        $a = $raw;
    } else {
        $a = preg_split('/[,;|]+/u', (string)$raw);
    }
    $a = array_map(fn($s) => trim(preg_replace('/\s+/u', ' ', $s)), $a);
    return array_values(array_filter($a, fn($s) => $s !== ''));
}


function ensure_terms_and_return_names(int $attrId, array $names): array {
    global $woocommerce;

    $norm = fn($s) => mb_strtolower(clean_name($s), 'UTF-8');
    $out  = [];

    foreach ($names as $name) {
        $name   = clean_name($name);
        if ($name === '') continue;

        // ищем кандидатов
        $found = $woocommerce->get("products/attributes/{$attrId}/terms",
                ['search' => $name, 'hide_empty' => false, 'per_page' => 100]);

        $hit = null;
        foreach ((array)$found as $t) {
            if (isset($t->name) && $norm($t->name) === $norm($name)) { $hit = $t; break; }
        }

        if ($hit) {
            // если найденный отличается только пунктуацией — переименуем на чистый
            if ($hit->name !== $name) {
                // убедимся, что «чистого» ещё нет
                $exists = $woocommerce->get("products/attributes/{$attrId}/terms",
                        ['search' => $name, 'per_page' => 100]);
                $exact = null;
                foreach ((array)$exists as $e) {
                    if (mb_strtolower($e->name,'UTF-8') === mb_strtolower($name,'UTF-8')) { $exact = $e; break; }
                }
                if (!$exact) {
                    $hit = $woocommerce->put("products/attributes/{$attrId}/terms/{$hit->id}", ['name' => $name]);
                } else {
                    $hit = $exact; // уже есть чистый — используем его
                }
            }
        } else {
            // нет термина — создаём сразу чистый
            $hit = $woocommerce->post("products/attributes/{$attrId}/terms", ['name' => $name]);
        }

        $out[] = $hit->name; // теперь это чистое имя
    }

    return array_values(array_unique($out));
}

function generatedProductAttributes( $newAttributes, $product_id = null ): array
{
    global $woocommerce, $allAttributes;
    $updates = [];

    if (!empty($newAttributes['Автори']) || !empty($newAttributes['Автор'])) {
        $attributeAuthorsID = getAttributeIdByName('Автори');
        $authorsNames = ensure_terms_and_return_names($attributeAuthorsID, $newAttributes['Автори']);
        $newAttributes['Автори'] = $authorsNames;

        $attributeAuthorID = getAttributeIdByName('Автор');
        $attributeAuthorName = normalize_authors($newAttributes['Автор']);
        $authorName = ensure_terms_and_return_names($attributeAuthorID, array($attributeAuthorName));
        $newAttributes['Автор'] = $authorName;

        if ($product_id !== null) {
            $woocommerce->put("products/$product_id", ['attributes' => []]);
        }
    }

    foreach ( $newAttributes as $name => $value ) {
        if ( $value !== '' ) {
            foreach ( $allAttributes as $key => $attribute ) {
                is_array($value) ? $options = $value : $options = [ $value ];
                if ( $attribute->name === $name ) {
                    if (is_array($value)) {

                        $updates[] = [
                                'id' => $attribute->id,
                                "visible" => true,
                                "variation" => false,
                                'options' => $options,
                        ];

                    } else {
                        $updates[] = [
                                'id' => $attribute->id,
                                'name' => $attribute->name,
                                'slug' => $attribute->slug,
                                "visible" => true,
                                'options' => $options
                        ];
                    }

                }
            }
        }
    }

    return $updates;
}

/**
 * @param $existingCategories
 * @param $newData
 * @return array
 */
function compareAndUpdateCategories ($existingCategories, $newData): array
{

    $updatedCategories = [];
    $currentCategoryIds = array_map(fn($category) => $category->id, $existingCategories);
    // Фильтруем категории, исключая те, у которых ID = 0
    $filteredCategories = array_filter($newData['Categories'], fn($id) => $id !== 0);
    $filteredCategoriesIDs = [];

    foreach ($filteredCategories as $category) {
        $filteredCategoriesIDs[] = $category;
    }

    $_subcategories = getCategoriesFromAttributes( $newData );
    $_subcategoriesIDs = array_map(fn($category) => $category->id, $_subcategories);

    //    $merge_array = array_merge($_subcategoriesIDs, $filteredCategoriesIDs, $defaultCategory);
    $merge_array = array_merge($_subcategoriesIDs, $filteredCategoriesIDs);

    // Сравнение текущих категорий с новыми
    $categoriesToAdd = array_diff($merge_array, $currentCategoryIds);
    $categoriesToRemove = array_diff($currentCategoryIds, $merge_array);

    if (!empty($categoriesToAdd) || !empty($categoriesToRemove)) {
        foreach (array_unique($merge_array) as $category) {
            $updatedCategories[] = [ 'id' => $category] ;
        }
    }

    return $updatedCategories;
}

/**
 * @param $product
 * @return array
 */
function getCategoriesFromAttributes ( $product ): array
{
    global $subcategories;
    $_subcategories = null;

    foreach ($product['Categories'] as $category ) {
        if ((int)$category !== 0) {
            foreach ($subcategories as $subcategory) {
                if ($subcategory->parent === (int)$category ) {
                    $_subcategories[] = $subcategory;
                }
            }
        }
    }

    $_category = array_filter($_subcategories, fn($item) => array_search($item->name, $product));

    return $_category;
}

/**
 * @param $str
 * @return string[]|null
 */
function setSizeProduct( $str ): ?array
{
    $size = explode(' х ', $str);
    $data = null;

    if (!empty($size) && $size[0] !== '-' && $size[1] !== '-' && $size[2] !== '-') {
        $length = (int)$size[0] / 10;
        $width = (int)$size[1] / 10;
        $height = (int)$size[2] / 10;

        $data = [
            "length" => (string)$length,
            "width" => (string)$width,
            "height" => (string)$height
        ];
    }

    return $data;
}

/**
 * function for add new product
 * @param $product
 * @return array
 */
function addProduct ( $product ): array
{
    global $imagesDirectory, $imagesDirectoryURL, $allAttributes;

    $data['name'] = $product['Name'];
    $data['sku']  = $product['ISBN'];
    $data['type'] = 'simple';
    $data['status'] = 'publish';
    $data['description'] = $product['Description'] ?? '';
    $data['short_description'] = $product['BriefDescription'] ?? '';
    $data['regular_price'] = isset($product['MainPrice']) ? str_replace(',', '.', $product['MainPrice']) : '';
    $data['stock_quantity'] = isset($product['Ostatok']) ? (int)$product['Ostatok'] : 0;
    $data['weight'] = $product['Weight'] ?? '';
    $data['manage_stock'] = true;

    if (isset($product['Size'])) {
        $data['dimensions'] = setSizeProduct($product['Size']);
    }

    if (isset($product['Categories'])) {
        $i = 0;
        foreach ( $product['Categories'] as $category ) {
            if ( (int)$category !== 0 ) {
                $data['categories'][$i] = [
                    'id' => $category
                ];
            }

            $i++;
        }
    }

    $_subcategories = getCategoriesFromAttributes( $product );

    $data['categories'] = array_merge($data['categories'], $_subcategories);

    $attributesMap = generateAttributes( $product );
    $data['attributes'] = generatedProductAttributes( $attributesMap );

    if (isset($product['Foto'])) {
        $i = 0;
        foreach ($product['Foto'] as $key => $image) {
            $url = $imagesDirectoryURL . $image;
            $data['images'][$i] = [ 'src' =>  $url ];
            $i++;
        }
    }

    $data['meta_data'] = [
        [
            'key'   => 'Kod1C',
            'value' => $product['Kod1C']
        ],
        [
            'key'   => 'UniID',
            'value' => $product['UniID']
        ],
        [
            'key'   => 'ISBN',
            'value' => $product['ISBN']
        ],
        [
            'key'   => 'product_1c_id',
            'value' => $product['UniID']
        ]
    ];

    return $data;
}

/**
 * function for update existing product
 * @param $product
 * @param $existing_product
 * @return array
 */

function updateProduct ( $product, $existing_product) {
    global $imagesDirectory, $imagesDirectoryURL, $wpdb;
    $updates = [];


    if ($existing_product->sku !== $product['ISBN']) {
        $updates['sku'] = $product['ISBN'];
    }

    if ($existing_product->name !== $product['Name']) {
        $updates['name'] = $product['Name'];
    }

    if (str_replace(',', '.', $existing_product->regular_price) !== str_replace(',', '.', $product['MainPrice'])) {
        $updates['regular_price'] = str_replace(',', '.', $product['MainPrice']);
    }

    if (strcmp(strip_tags($existing_product->description), $product['Description']) !== 0 ) {
        $updates['description'] = $product['Description'];
    }

    if (strcmp(strip_tags($existing_product->short_description), $product['BriefDescription']) !== 0 ) {
        $updates['short_description'] = $product['BriefDescription'];
    }

    if ($existing_product->stock_quantity !== (int)$product['Ostatok']) {
        $updates['stock_quantity'] = !empty($product['Ostatok']) ? (int)$product['Ostatok'] : 0;
    }

    if (isset($product['Size'])) {
        $updates['dimensions'] = setSizeProduct($product['Size']);
    }

    $attributesMap = generateAttributes( $product );
    $attributes = generatedProductAttributes( $attributesMap, $existing_product->id);

    if (isset($attributes) && !empty($attributes)) {
        $updates['attributes'] = $attributes;
    }

    $categories = compareAndUpdateCategories($existing_product->categories, $product);
    if (isset($categories) && !empty($categories)) {
        $updates['categories'] = $categories;
    }

    // Меняем только главную, не трогая галерею
    if (!empty($product['Foto'])) {

        // 1) Текущая галерея по ID (чтобы сохранить)
        $existingImages = [];
        if (!empty($existing_product->images)) {
            foreach ($existing_product->images as $img) {
                if (!empty($img->id)) {
                    $existingImages[] = ['id' => (int) $img->id];
                } else {
                    // fallback, если почему-то нет id
                    $existingImages[] = ['src' => $img->src];
                }
            }
        }

        // 2) URL новой главной (из твоей папки на сервере)
        $newMainRel = reset($product['Foto']);        // первая из массива Foto
        $newMainUrl = $imagesDirectoryURL . $newMainRel;     // ДОЛЖЕН быть публичный http(s) URL

        // 3) Проверяем: изменилась ли реально главная картинка
        $needChangeMain = true;
        if (!empty($existing_product->images[0]->name)) {
            $oldFile = pathinfo($existing_product->images[0]->name, PATHINFO_FILENAME);
            $newFile = pathinfo($newMainRel, PATHINFO_FILENAME);
            $needChangeMain = ($oldFile !== $newFile);
        }

        if ($needChangeMain) {
            // 4) Уберём из существующих возможный дубль новой
            array_shift($existingImages);
            $filtered = [];
            foreach ($existingImages as $it) {
                if (isset($it['src']) && $it['src'] === $newMainUrl) continue;
                $filtered[] = $it;
            }

            // 5) Полный итоговый массив: новая главная + старая галерея
            $updates['images'] = array_merge(
                [['src' => $newMainUrl, 'position' => 0]],
                $filtered
            );
        }
        // Если главная не менялась — НЕ отправляем 'images' вообще.
    }


    $updates['meta_data'] = [
        [
            'key'   => 'Kod1C',
            'value' => $product['Kod1C']
        ],
        [
            'key'   => 'UniID',
            'value' => $product['UniID']
        ],
        [
            'key'   => 'ISBN',
            'value' => $product['ISBN']
        ],
        [
            'key'   => 'product_1c_id',
            'value' => $product['UniID']
        ]
    ];


    return $updates;
}

/**
 * Function for read file with array of products
 * @param $data
 * @return void
 * @throws Exception
 */
function loadProductsFile ($data): void
{
    global $woocommerce, $import_info, $wpdb, $importDirectory;
    $file = $importDirectory . $import_info;
    $index = 0;

    foreach ($data as $key => $product) {
        try {
            $index++;
            $UniID1c = $product['UniID'];
            $ISBN = $product['ISBN'];
            $ID = null;

            $productIdArraySKU = $woocommerce->get('products', ['sku' => $ISBN]);

            if (isset($productIdArraySKU[0])) :
                $ID = $productIdArraySKU[0]->id;
            endif;


            ($ID !== null) ? $existing_products = $woocommerce->get('products/' . $ID) : $existing_products = null;

            $info_product = "Товар id = {$ID}\r\n";
            file_put_contents($file, $info_product, FILE_APPEND);

            if ($existing_products !== null) {
                // Если товар найден, обновляем его
                $existing_product_id = $existing_products->id;
                $payload = updateProduct($product, $existing_products);
                $response = $woocommerce->put("products/{$existing_product_id}", $payload);
                $message = "{$index}) - Товар '{$product['Name']}' обновлен! ID товара: {$response->id}, UniID: {$product['UniID']}\r\n";
                $message .= json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            } else {
                // Если товар не найден, создаем его
                $payload = addProduct($product);
                $response = $woocommerce->post('products', $payload);
                $message = "{$index}) - Товар '{$product['Name']}' добавлен! ID товара: {$response->id}, UniID: {$product['UniID']}\r\n";
                $message .= json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }

            // Формируем сообщение с текущей датой и временем
            $log_message = '[' . date('d-m-Y H:i:s') . '] ' . $message . PHP_EOL;
            file_put_contents($file, $log_message, FILE_APPEND);
            flush();
        } catch (Exception $e) {
            $url     = $e->getRequest()  ? (string)$e->getRequest()->getUrl() : '';
            $status  = $e->getResponse() ? (int)$e->getResponse()->getCode()  : 0;
            $bodyRaw = $e->getResponse() ? (string)$e->getResponse()->getBody() : '';

            // Пытаемся красиво распечатать JSON-ответ
            $pretty = $bodyRaw;
            $decoded = json_decode($bodyRaw, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $pretty = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }

            error_log("HTTP {$status} {$url}");
            error_log("Body:\n{$pretty}");

            $message = "{$index}) - Ошибка для товара '{$product['Name']}', UniID: {$product['UniID']}: {$e->getMessage()} - line: {$e->getLine()} \r\n";
            $log_message = '[' . date('d-m-Y H:i:s') . '] ' . $message . PHP_EOL;
            file_put_contents($file, $log_message, FILE_APPEND);
            flush();
            throw $e;
        }
    }


    $message = "{$index} обработано! Все товары добавлены или обновлены!\n";
    $log_message = '[' . date('d-m-Y H:i:s') . '] ' . $message . PHP_EOL;
    file_put_contents($file, $log_message, FILE_APPEND);
    chmod($file, 0777);
    echo '<div class="container">' . $log_message . '</div>';
    flush();
}

/**
 * @param $orders
 * @return void
 */
function creatFileAllOrders ($orders) {
    global $orders_info, $ordersDirectory, $woocommerce;
    $update['Orders'] = [];

    foreach ($orders as $key => $order) {

        $data_order = wc_get_order($order->id);
        $ukr_poshta_patronymic = $data_order->get_meta('mrkv_ua_shipping_ukr-poshta_address_patronymic');
        $nova_poshta_patronymic = $data_order->get_meta('mrkv_ua_shipping_nova-poshta_address_patronymic');
        $ukr_poshta_patronymic !== '' ? $patronymic = $ukr_poshta_patronymic : $patronymic = $nova_poshta_patronymic;


        $_update = [];
        $_update['NomerZakaza']         = $order->id;
        $_update['KlientID']            = $order->customer_id;
        $_update['email']               = $order->billing->email;
        $_update['phone']               = $order->billing->phone;
        $_update['first_name']          = $order->billing->first_name;
        $_update['last_name']           = $order->billing->last_name;
        $_update['patronymic']          = $patronymic;
        $_update['SummaZakaza']         = $order->total;
        $_update['Valuta']              = $order->currency;
        $_update['date_created']        = $order->date_created;
        $_update['payment_method']      = $order->payment_method;
        $_update['prepayment_amount']   = $order->prepayment_amount;
        $_update['prepayment_status']   = $order->prepayment_status;

        switch ( $order->status ) {
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

        switch (  $order->payment_status ) {
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

            case 'invoice_wait'  : $_update['payment_status'] = 'Інвойс створений успішно, очікується оплата';
                break;

            case 'prepared'      : $_update['payment_status'] = 'Платіж створений, очікується його завершення відправником';
                break;

            case 'processing'    : $_update['payment_status'] = 'Платіж обробляється';
                break;

            case 'wait_accept'   : $_update['payment_status'] = 'Кошти з клієнта списані, але магазин ще не пройшов перевірку. Якщо магазин не пройде активацію протягом 60 днів, платежі будуть автоматично скасовані';
                break;

            case 'wait_secure'   : $_update['payment_status'] = 'Платіж на перевірці';
                break;

            case 'try_again'     : $_update['payment_status'] = 'Оплата неуспішна. Клієнт може повторити спробу ще раз';
                break;

            case 'cancelled'      : $_update['payment_status'] = 'Скасування платежу';
                break;

            default: $_update['payment_status'] = $order->payment_status;
        }

        $_update['payment_detail'] = $order->payment_detail;
        $full_shipping_address = $data_order->get_meta('_shipping_address_index');
        $full_billing_address = $data_order->get_meta('_billing_address_index');
        $nova_poshta_address_city = $data_order->get_meta('mrkv_ua_shipping_nova-poshta_city');
        $nova_poshta_address_flat = $data_order->get_meta('mrkv_ua_shipping_nova-poshta_address_flat');
        $ukr_poshta_address_flat = $data_order->get_meta('mrkv_ua_shipping_ukr-poshta_address_flat');
        $nova_poshta_address_flat !== '' ? $flat = $nova_poshta_address_flat : $flat = $ukr_poshta_address_flat;


        $_update['shipping']       = [
            'first_name'               => $order->shipping->first_name ?? $order->billing->first_name,
            'last_name'                => $order->shipping->last_name ?? $order->billing->last_name,
            'patronymic'               => $patronymic,
            'shipping_method'          => $order->shipping_lines['0']->method_title,
            'city'                     => $order->billing->city ?? $order->shipping->city,
            'nova_poshta_address_city' => $nova_poshta_address_city,
            'postcode'                 => $order->billing->postcode ?? $order->shipping->postcode,
            'address_1'                => $order->billing->address_1 ?? $order->shipping->address_1,
            'address_2'                => $order->billing->address_2 ?? $order->shipping->address_2,
            'ukr_poshta_address_flat'  => $ukr_poshta_address_flat,
            'nova_poshta_address_flat' => $nova_poshta_address_flat,
            'flat'                     => $flat,
        ];

        $_update['full_shipping_address'] =  $full_shipping_address;
        $_update['full_billing_address'] =  $full_billing_address;

        foreach ($order->line_items as $index => $item ) {
            $meta_data = wc_get_product($item->product_id);

            if (!$meta_data) {
                error_log("Product not found: ID {$item->product_id}");
                continue; // пропускаем товар, если продукт не найден
            }

            $Kod1C = $meta_data->get_meta('Kod1C') ?? '';
            $UniID = $meta_data->get_meta('UniID') ?? '';
            $ISBN = $meta_data->get_meta('ISBN') ?? '';

            $_update['Tovary'][$index] = [
                'SKU'       => $item->sku,
                'Kod1C'       => $Kod1C,
                'UniID'       => $UniID,
                'ISBN'        => $ISBN,
                'Name'        => $item->name,
                'MainPrice'   => $item->price,
                'Kolichestvo' => $item->quantity,
                'Summa'       => $item->total
            ];
        }

        $update['Orders'][$key] = $_update;
    }

    try {

        $fileName = $ordersDirectory . $orders_info;
        // Обновляем файл состояния
        if (!is_dir(dirname( $ordersDirectory))) {
            mkdir(dirname($ordersDirectory), 0777, true); // Создаём директорию, если она не существует
        }

        file_put_contents($fileName, json_encode($update, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        chmod($fileName, 0777);
        echo '<div class="container">Файл заказов готов!</div>';

    } catch (Exception $e) {
        echo '<p>Произошла ошибка!' . $e . '</p>';
    }


}


//if (isset($_GET['clean'])) {
//    ?>
<!--    <script>-->
<!--        if (window.history.replaceState) {-->
<!--            const url = window.location.origin + window.location.pathname; // Убираем параметры-->
<!--            window.history.replaceState(null, null, url);-->
<!--        }-->
<!---->
<!--    </script>-->
<!--    --><?php
//}

/********************************************
 *  Reading file for loading products
 *******************************************/

?>
<!--    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">-->
<!--    <div class="container mt-5">-->
<!--        <div class="d-flex align-items-center justify-content-evenly">-->
<!--            <div class="d-flex">-->
<!--                <form method="get">-->
<!--                    <input type="submit" type="button" class="btn btn-primary" name="import" value="Загрузить товар">-->
<!--                </form>-->
<!--            </div>-->
<!--            <div class="d-flex">-->
<!--                <form method="get">-->
<!--                    <input type="submit" type="button" class="btn btn-primary" name="orders" value="Заказы">-->
<!--                </form>-->
<!--            </div>-->
<!--            <div class="d-flex">-->
<!--                <form method="get">-->
<!--                    <input type="submit" type="button" class="btn btn-primary" name="clean" value="Очистить запрос">-->
<!--                </form>-->
<!--            </div>-->
<!--        </div>-->
<!---->
<!--    </div>-->
<?php

//if(isset($_GET['import'])) {
    // Считываем предыдущее состояние файлов
    $previousState = file_exists($stateFile) ? json_decode(file_get_contents($stateFile), true) : [];
    // Сканируем текущие файлы в директории
    $currentFiles = array_diff(scandir($ftpDirectory), ['.', '..']);
    // Создаём массив для текущего состояния с временными метками
    $currentState = [];
    $newOrUpdatedFiles = [];

    foreach ($currentFiles as $file) {
        $filePath = $ftpDirectory . $file;

        // Проверяем, что это файл, а не директория
        if (is_file($filePath)) {
            $currentState[$file] = filemtime($filePath);

            // Проверяем, новый это файл или обновлённый
            if (!isset($previousState[$file]) || $previousState[$file] !== $currentState[$file]) {
                $newOrUpdatedFiles[] = $file; // Добавляем в список новых или обновлённых
            }
        }
    }

    $fileName = $importDirectory . $import_info;
    // Обновляем файл состояния
    if (!is_dir(dirname( $importDirectory))) {
        mkdir(dirname($importDirectory), 0777, true); // Создаём директорию, если она не существует
    }

    if (!is_writable($importDirectory)) {
        echo "Ошибка: Директория $importDirectory недоступна для записи.\n";
        exit;
    }

    if (file_exists($fileName) && is_dir($fileName)) {
        echo "Ошибка: $import_info является директорией, а не файлом.\n";
        exit;
    }

    // Обработка новых или изменённых файлов
    if (!empty($newOrUpdatedFiles)) {
        // Проверяем, существует ли файл
        if (file_exists($importFilePath)) {
            // Текущая дата изменения файла
            $currentFileTime = filemtime($importFilePath);

            if (isset($previousState[$importFile]) && $previousState[$importFile] === $currentFileTime) {
                echo '<div class="container">Нет новых файлов для обработки!</div>'; // Файл не изменялся.
            } else {
                // Файл '$importFile' обновлён или загружен заново!";
                // Чтение JSON файла
                $json_data = file_get_contents($importFilePath);
                $data = json_decode($json_data, true);

                echo '<div class="container">Найден новый файл! Процесс пошел!</div>';

                // Проверяем, что данные прочитаны
                if (isset($data['Tovary'])) {


                    loadProductsFile($data['Tovary']);

                } else {
                    $message = "Данные о товарах не найдены в JSON файле.\n";
                    $log_message = '[' . date('d-m-Y H:i:s') . '] ' . $message . PHP_EOL;
                    file_put_contents($fileName, $log_message, FILE_APPEND);
                    chmod($fileName, 0777);

                }
            }

            file_put_contents($stateFile, json_encode($currentState, JSON_PRETTY_PRINT));
            chmod($stateFile, 0777);

        } else {
            echo '<div class="container">Файл для импорта отсутствует в директории.!</div>';
        }
    } else {
        $message = "Новых или обновлённых файлов не найдено.\n";
        $log_message = '[' . date('d-m-Y H:i:s') . '] ' . $message . PHP_EOL;
        file_put_contents($fileName, $log_message, FILE_APPEND);
        chmod($fileName, 0777);

    }
//}
//
//if (isset($_GET['orders'])) {
//    $upTodayOrders = $woocommerce->get('orders', array('per_page' => 10, 'orderby' => 'date'));
//    creatFileAllOrders($upTodayOrders);
//}