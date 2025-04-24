<?php


//composer require automattic/woocommerce

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

//echo '<pre>';
//print_r($woocommerce->get('orders'));
//echo '</pre>';

/*************
/* FTP directory
 **************/

$ftpDirectory = __DIR__ . '/public_html/';
$ordersDirectory = __DIR__ . '/public_html/ORDERS/';
$imagesDirectory =  __DIR__ . '/public_html/IMAGES/';
$importDirectory = __DIR__ . '/public_html/IMPORT_LOG/';
$errorDirectory = __DIR__ . '/public_html/ERROR_LOG/';

$stateFile = __DIR__ . '/public_html/directory_state.json'; // Сохранение состояния директории
$import_info = 'import_log_' . date('d-m-Y') . '.log'; // Логирование импорт
$error_info = 'log_' . date('d-m-Y') . '.log';
$orders_info = 'orders_' . date('d-m-Y H.i.s') . '.log';

$importFile = 'Tovary.json';
$importFilePath = $ftpDirectory . $importFile;

if(!defined('DS')) {
    define('DS' ,DIRECTORY_SEPARATOR);
}

// initialize the application WooCommerce
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

if (!file_exists($ftpDirectory)) {
    mkdir($ftpDirectory, 0777, true);
    chmod($ftpDirectory, 0777);
}

if (!file_exists($importDirectory)) {
    mkdir($importDirectory, 0777, true);
    chmod($importDirectory, 0777);
}


if (!file_exists($imagesDirectory)) {
    mkdir($imagesDirectory, 0777, true);
    chmod($imagesDirectory, 0777);
}

if (!file_exists($ordersDirectory)) {
    mkdir($ordersDirectory, 0777, true);
    chmod($ordersDirectory, 0777);
}

if (!file_exists($errorDirectory)) {
    mkdir($errorDirectory, 0777, true);
    chmod($errorDirectory, 0777);
}

/******************************************
 *             SETTINGS
 ******************************************/
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('html_errors', 'on');

//@header('X-Accel-Buffering: no');
//@ini_set('output_buffering', 'Off');
//@ini_set('output_handler', '');
//@ini_set('zlib.output_handler','');
//@ini_set('zlib.output_compression', 'Off');
error_reporting(E_ALL & ~E_NOTICE);
@ignore_user_abort(true);
@set_time_limit(6000);

//*********************Системые настройки***************************
//define( '_JEXEC', 1 );
//define ( 'VM_ZIPSIZE', 1073741824 ); // Максимальный размер отправляемого архива в байтах (по умолчанию 1 гб)
$accumulate_trash = ob_clean();
ob_start("fatal_error_handler");
set_error_handler('error_handler');
set_exception_handler('exception_handler');


global $wpdb;
define ( 'DB_PREFIX', $wpdb->prefix );
$allCategories = $woocommerce->get('products/categories', array('per_page' => 100, 'page' => 1));
$allAttributes = $woocommerce->get('products/attributes');
$subcategories = array_filter((array)$allCategories, fn($category) => $category->parent != 0);


function error_handler($code, $msg, $file, $line) {
    global $error_info, $errorDirectory;
    $file = $errorDirectory . $error_info;
    $allNameError = "Произошла ошибка $msg ($code)\n $file ($line)";
    file_put_contents($file, $allNameError, FILE_APPEND);

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

/************************
 *      FUNCTIONS
 ************************/

/**
 * @return array[]
 *  Attributes
 */
function generateAttributes()
{
    $attributeMap = [
        'Autors' => [
            'id' => 3,
            'name' => 'Автори',
            'slug' => 'pa_avtori',
            'visible' => true,
        ],
        'NumberOfPages' => [
            'id' => 4,
            'name' => 'Кількість стрінок',
            'slug' => 'pa_kilkist-strinok',
            'visible' => true,
        ],
        'PriceCode' => [
            'id' => 5,
            'name' => 'Код прайса',
            'slug' => 'pa_kod-prajsa',
            'visible' => true,
        ],
        'BriefDescription' => [
            'id' => 6,
            'name' => 'Короткий опис',
            'slug' => 'pa_korotkij-opis',
            'visible' => true,
        ],
        'BooksForPreschoolers' => [
            'id' => 7,
            'name' => 'КТ Книги для дошкільнят',
            'slug' => 'pa_kt-knigi-dla-doskilnat',
            'visible' => true,
        ],
        'LanguageOfThePublication' => [
            'id' => 8,
            'name' => 'Мова видання',
            'slug' => 'pa_mova-vidanna',
            'visible' => true,
        ],
        'Cover' => [
            'id' => 9,
            'name' => 'Обкладинка',
            'slug' => 'pa_obkladinka',
            'visible' => true,
        ],
        'YearOfPublication' => [
            'id' => 10,
            'name' => 'Рік видання',
            'slug' => 'pa_rik-vidanna',
        ],
        'Size' => [
            'id' => 11,
            'name' => 'Розмір',
            'slug' => 'pa_rozmir',
            'visible' => true,
        ],
        'PackStandard' => [
            'id' => 12,
            'name' => 'Стандарт пачки',
            'slug' => 'pa_standart-packi',
            'visible' => true,
        ],
        'BooksForYoungerStudents' => [
            'id' => 13,
            'name' => 'КТ Книги для молодших школярів',
            'slug' => 'pa_kt-knigi-dla-molodsih-skol',
            'visible' => true,
        ],
        'BookSeries' => [
            'id' => 14,
            'name' => 'Серія книги',
            'slug' => 'pa_seria-knigi',
            'visible' => true,
        ],
        'BooksForMiddleAndHighSchoolStudents' => [
            'id' => 15,
            'name' => 'КТ Книги для школярів середнього та старшого віку',
            'slug' => 'pa_kt-knigi-dla-skolariv-sere',
            'visible' => true,
        ],
        'BooksForEveryone' => [
            'id' => 16,
            'name' => 'КТ Книги для всіх',
            'slug' => 'pa_kt-knigi-dla-vsih',
            'visible' => true,
        ],
        'Publisher' => [
            'id' => 17,
            'name' => 'Виробник',
            'slug' => 'pa_virobnik',
            'visible' => true,
        ],
        'Age' => [
            'id' => 18,
            'name' => 'Вік',
            'slug' => 'pa_vik',
            'visible' => true,
        ],
    ];

    return $attributeMap;
}

/**
 * function for compare existing attributes with new info
 * if info change: do update
 * if info new info: add new info
 * @param $existingAttributes
 * @param $newData
 * @return array
 */
function compareAndUpdateAttributes ($existingAttributes, $newData) {
    global $attributeMap, $allCategories;
    $updates = [];

    // Преобразуем существующие атрибуты в удобный формат для сравнения
    $existingMapped = [];
    foreach ($existingAttributes as $attribute) {
        $existingMapped[$attribute->name] = $attribute->options[0];
    }

    // Подготовка атрибутов из JSON
    $newAttributes = [
        'Автори'                  => $newData['Autors'] ?? null,
        'Кількість стрінок'       => $newData['NumberOfPages'] ?? null,
        'Код прайса'              => $newData['PriceCode'] ?? null,
        'Короткий опис'           => $newData['BriefDescription'] ?? null,
        'КТ Книги для дошкільнят' => $newData['BooksForPreschoolers'] ?? null,
        'Мова видання'            => $newData['LanguageOfThePublication'] ?? null,
        'Обкладинка'              => $newData['Cover'] ?? null,
        'Рік видання'             => $newData['YearOfPublication'] ?? null,
        'Розмір'                  => $newData['Size'] ?? null,
        'Стандарт пачки'          => $newData['PackStandard'] ?? null,
        'Серія книги'             => $newData['BookSeries'] ?? null,
        'Книги для всіх'          => $newData['BooksForEveryone'] ?? null,
        'Виробник'                => $newData['Publisher'] ?? null,
        'Вік'                     => $newData['Age'] ?? null,
        'Книжки на картоні'       => $newData['BooksForPreschoolers'] ?? null,
        'КТ Книги для молодших школярів' => $newData['BooksForYoungerStudents'] ?? null,
        'КТ Книги для школярів середнього та старшого віку' => $newData['BooksForMiddleAndHighSchoolStudents'] ?? null,

    ];

    $attributeMap = generateAttributes();
    $filteredAttributes = array_filter($newAttributes, fn($name) => $name !== '');
    $attributesToAdd = array_diff($filteredAttributes, $existingMapped);

    foreach ($attributeMap as $key => $meta) {
        if (isset($attributesToAdd[$meta['name']])) {
            $updates[] = [
                'id' => $meta['id'],
                'name' => $meta['name'],
                'slug' => $meta['slug'],
                'visible' => true,
                'options' => [
                    $newAttributes[$meta['name']]
                ],
            ];
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
//    $defaultCategory[] = 15;
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
function getCategoriesFromAttributes ( $product ) {
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
function setSizeProduct( $str ) {
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
 * @param $ID
 * @param $product_unq_id
 * @return void
 */
function setProduct1CId ($ID, $product_unq_id) {
    global $wpdb;
    $product_1c_id = sanitize_text_field($product_unq_id); // Очистка значения
    // Выполнение запроса UPDATE
    $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$wpdb->prefix}posts
                  SET product_1c_id = %s
                  WHERE ID = %d AND post_type = 'product'",
            $product_1c_id,
            $ID
        )
    );
}

/**
 * function for add new product
 * @param $product
 * @return array
 */
function addProduct ( $product ): array
{
    global $imagesDirectory;

    $data['name'] = $product['Name'];
    $data['sku']  = $product['ISBN'];
    $data['type'] = 'simple';
    $data['status'] = 'publish';
    $data['description'] = $product['Description'] ?? '';
    $data['short_description'] = $product['Annotation'] ?? '';
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

    $attributesMap = generateAttributes();

    $i = 0;
    foreach ($attributesMap as $key => $attribute) {
        if (isset($product[$key])) {
            $data['attributes'][$i] = [
                'id' => $attribute['id'],
                'name' => $attribute['name'],
                'slug' => $attribute['slug'],
                'visible' => true,
                'options' => [$product[$key]]
            ];
        }

        $i++;
    }

    if (isset($product['Foto'])) {
        $i = 0;
        foreach ($product['Foto'] as $key => $image) {
            $data['images'][$i] = [ 'src' =>  home_url() . $imagesDirectory . $image ];
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
    global $imagesDirectory, $wpdb;
    $updates = [];

    if ($existing_product->product_1c_id !== $product['UniID']) {
        (int)$ID = $existing_product->id ?? $existing_product[0]->ID;
        setProduct1CId($ID, $product['UniID']);
    }

    if ($existing_product->sku !== $product['ISBN']) {
        $updates['sku'] = $product['ISBN'];
    }

    if ($existing_product->name !== $product['Name']) {
        $updates['name'] = $product['Name'];
    }

    if (str_replace(',', '.', $existing_product->regular_price) !== str_replace(',', '.', $product['MainPrice'])) {
        $updates['regular_price'] = str_replace(',', '.', $product['MainPrice']);
    }

    // Приводим цены к единому формату (числовому)
//    $currentPriceFormatted = (float)$existing_product->regular_price;
//    $newPriceFormatted = (float)str_replace(',', '.', $product['MainPrice']);
//
//    if ($currentPriceFormatted !== $newPriceFormatted) {
//        if ($currentPriceFormatted > $newPriceFormatted) {
//            $updates['sale_price'] = str_replace(',', '.', $product['MainPrice']);
//        } else {
//            $updates['sale_price'] = '';
//        }
//    } elseif ($existing_product->sale_price !== '') {
//        $updates['sale_price'] = '';
//    }

    if (strcmp(strip_tags($existing_product->description), $product['Description']) !== 0 ) {
        $updates['description'] = $product['Description'];
    }

    if (strcmp(strip_tags($existing_product->short_description), $product['Annotation']) !== 0 ) {
        $updates['short_description'] = $product['Annotation'];
    }

    if ($existing_product->stock_quantity !== (int)$product['Ostatok']) {
        $updates['stock_quantity'] = !empty($product['Ostatok']) ? (int)$product['Ostatok'] : 0;
    }

    if (isset($product['Size'])) {
        $updates['dimensions'] = setSizeProduct($product['Size']);
    }


    $attributes = compareAndUpdateAttributes($existing_product->attributes, $product);
    if (isset($attributes) && !empty($attributes)) {
        $updates['attributes'] = $attributes;
    }

    $categories = compareAndUpdateCategories($existing_product->categories, $product);
    if (isset($categories) && !empty($categories)) {
        $updates['categories'] = $categories;
    }

    if (isset($product['Foto'])) {
        $i = 0;
        foreach ($product['Foto'] as $key => $image) {
            if (isset($existing_product->images[$i])) {
                $pathInfNew = pathinfo($image);
                $pathInfOld = pathinfo($existing_product->images[$i]->name);

                if ($pathInfOld['filename'] !== $pathInfNew['filename']) {
                    $updates['images'][$i] =  [ 'src' => home_url() . $imagesDirectory . $image];
                }
            } else {
                $updates['images'][$i] =  [ 'src' => home_url() . $imagesDirectory . $image];
            }
            $i++;
        }
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
        ]
    ];


    return $updates;
}

/**
 * Function for read file with array of products
 * @param $data
 * @return void
 */
function loadProductsFile ($data): void
{
    global $woocommerce, $import_info, $wpdb, $importDirectory;
    $file = $importDirectory . $import_info;

    foreach ($data as $key => $product) {
        try {

            $UniID1c = $product['UniID'];
            $ISBN = $product['ISBN'];
            $ID = null;

            $productIdArray1C = $wpdb->get_results("SELECT ID FROM " . DB_PREFIX ."posts where product_1c_id = '" . $UniID1c . "' AND post_type = 'product'" );
            $productIdArraySKU = $woocommerce->get('products', ['sku' => $ISBN]);

            (isset($productIdArray1C[0])) ? $ID = (int)$productIdArray1C[0]->ID : $ID = $productIdArraySKU[0]->id;
            ($ID !== null) ? $existing_products = $woocommerce->get('products/' . $ID) : $existing_products = null;

            if ($existing_products !== null) {
                // Если товар найден, обновляем его
                $existing_product_id = $existing_products->id;
                $payload = updateProduct($product, $existing_products);
                $response = $woocommerce->put("products/{$existing_product_id}", $payload);
                $message = "{$key}) - Товар '{$product['Name']}' обновлен! ID товара: {$response->id}, UniID: {$product['UniID']}\n";

            } else {
                // Если товар не найден, создаем его
                $payload = addProduct($product);
                $response = $woocommerce->post('products', $payload);
                setProduct1CId($response->id, $product['UniID']);
                $message = "{$key}) - Товар '{$product['Name']}' добавлен! ID товара: {$response->id}, UniID: {$product['UniID']}\n";
            }

            // Формируем сообщение с текущей датой и временем
            $log_message = '[' . date('d-m-Y H:i:s') . '] ' . $message . PHP_EOL;
            file_put_contents($file, $log_message, FILE_APPEND);
            flush();
        } catch (Exception $e) {

            $message = "Ошибка для товара '{$product['Name']}', UniID: {$product['UniID']}: {$e->getMessage()}\n";
            $log_message = '[' . date('d-m-Y H:i:s') . '] ' . $message . PHP_EOL;
            file_put_contents($file, $log_message, FILE_APPEND);
            flush();
        }
    }


    $message = "Все товары добавлены или обновлены!\n";
    $log_message = '[' . date('d-m-Y H:i:s') . '] ' . $message . PHP_EOL;
    file_put_contents($file, $log_message, FILE_APPEND);
    chmod($file, 0777);
    echo '<div class="container">' . $log_message . '</div>';
    flush();
}

function order_statuses($status) {
    $order_status = '';

    $order_statuses = array(
        'wc-pending'    => _x( 'Pending payment', 'Order status', 'woocommerce' ),
        'wc-processing' => _x( 'Processing', 'Order status', 'woocommerce' ),
        'wc-on-hold'    => _x( 'On hold', 'Order status', 'woocommerce' ),
        'wc-completed'  => _x( 'Completed', 'Order status', 'woocommerce' ),
        'wc-cancelled'  => _x( 'Cancelled', 'Order status', 'woocommerce' ),
        'wc-refunded'   => _x( 'Refunded', 'Order status', 'woocommerce' ),
        'wc-failed'     => _x( 'Failed', 'Order status', 'woocommerce' ),
    );

    foreach ($order_statuses as $key => $value) {
        if ($status == $key) {
            $order_status = $value;
        }
    }

    return $order_status;
}

/**
 * @param $orders
 * @return void
 */
function creatFileAllOrders ($orders) {
    global $orders_info, $ordersDirectory;
    $update['Orders'] = [];

    foreach ($orders as $key => $order) {
        $_update = [];
        $_update['NomerZakaza']    = $order->id;
        $_update['KlientID']       = $order->customer_id;
        $_update['email']          = $order->billing->email;
        $_update['phone']          = $order->billing->phone;
        $_update['first_name']     = $order->billing->first_name;
        $_update['last_name']      = $order->billing->last_name;
        $_update['SummaZakaza']    = $order->total;
        $_update['Valuta']         = $order->currency;
        $_update['date_created']   = $order->date_created;
        $_update['payment_method'] = $order->payment_method;
        $_update['status']         = $order->status;

        foreach ( $order->meta_data as $meta ) {
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

        /**
         * error - Failed payment. Data is incorrect
         * failure - Failed payment
         * reversed - Payment refunded
         * subscribed - Subscribed successfully framed
         * success - Successful payment
         * unsubscribed - Subscribed successfully deactivated
         */

        $_update['shipping']       = [
            'shipping_method' => $order->shipping_lines['0']->method_title,
            'city'      => $order->billing->city ?? $order->shipping->city,
            'postcode'  => $order->billing->postcode ?? $order->shipping->postcode,
            'address_1' => $order->billing->address_1 ?? $order->shipping->address_1,
            'address_2' => $order->billing->address_2 ?? $order->shipping->address_2,
        ];



        foreach ($order->line_items as $index => $item ) {
            $meta_data = wc_get_product($item->product_id);

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


if (isset($_GET['clean'])) {
    ?>
    <script>
        if (window.history.replaceState) {
            const url = window.location.origin + window.location.pathname; // Убираем параметры
            window.history.replaceState(null, null, url);
        }

    </script>
    <?php
}


/*************************
 *  Reading file for loading products
 *************************/

?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <div class="container mt-5">
        <div class="d-flex align-items-center justify-content-evenly">
            <div class="d-flex">
                <form method="get">
                    <input type="submit" type="button" class="btn btn-primary" name="import" value="Загрузить товар">
                </form>
            </div>
            <div class="d-flex">
                <form method="get">
                    <input type="submit" type="button" class="btn btn-primary" name="orders" value="Заказы">
                </form>
            </div>
            <div class="d-flex">
                <form method="get">
                    <input type="submit" type="button" class="btn btn-primary" name="clean" value="Очистить запрос">
                </form>
            </div>
        </div>

    </div>
<?php

if(isset($_GET['import'])) {
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
}

if (isset($_GET['orders'])) {
    $upTodayOrders = $woocommerce->get('orders', array('per_page' => 100, 'orderby' => 'date'));
    creatFileAllOrders($upTodayOrders);
}



echo '<pre>';
//print_r($_category);
//print_r($woocommerce->post('products', $data));
//print_r($woocommerce->post('products/batch', $data));
//print_r($woocommerce->get('orders'));
//print_r($upTodayOrders);
//print_r($subcategories);
//print_r($allAttributes);
echo '</pre>';

