<?php

if (!defined("ABSPATH")) {
    exit;
}

function any_web_theme_has_woocommerce(): bool {
    return class_exists('WooCommerce');
}

/**
 * ЗАМЕННА СИМВОЛА В ЦЕНЕ ПРОДУКТА 'UAH' -> 'грн'
 */

add_filter('woocommerce_currency_symbol', 'grncurrency_symbol', 10, 2);
function grncurrency_symbol( $currency_symbol, $currency ) {
    switch( $currency ) {
        case 'UAH': $currency_symbol = 'грн'; break;
    }
    return $currency_symbol;
}

/**
 * УДАЛЕНИЕ ЛИШНИХ ПРОБЕЛОВ В ЦЕНЕ ТОВАРА
 */
add_filter('woocommerce_get_price_html', function ($price) {
    return preg_replace('/\x{00A0}/u', '', $price);
}, 999);

/**
 *
 */
add_action( 'woocommerce_process_product_meta', 'art_woo_custom_fields_save', 10 );
function art_woo_custom_fields_save( $post_id ) {

    // Сохранение текстового поля.
    $woocommerce_text_field = $_POST['_page_count'];
    if ( ! empty( $woocommerce_text_field ) ) {
        update_post_meta( $post_id, '_page_count', esc_attr( $woocommerce_text_field ) );
    }
    // Сохранение текстового поля.
    $woocommerce_text_field = $_POST['_isbn'];
    if ( ! empty( $woocommerce_text_field ) ) {
        update_post_meta( $post_id, '_isbn', esc_attr( $woocommerce_text_field ) );
    }
    // Сохранение текстового поля.
    $woocommerce_text_field = $_POST['_mass'];
    if ( ! empty( $woocommerce_text_field ) ) {
        update_post_meta( $post_id, '_mass', esc_attr( $woocommerce_text_field ) );
    }
    // Сохранение текстового поля.
    $woocommerce_text_field = $_POST['_size'];
    if ( ! empty( $woocommerce_text_field ) ) {
        update_post_meta( $post_id, '_size', esc_attr( $woocommerce_text_field ) );
    }

}

/**
 * Используйте сохраненное значение в вашем коде
 * @param $price
 * @param $product
 * @return float|mixed
 */

add_filter('woocommerce_product_get_price', 'custom_category_discount', 10, 2);
// add_filter('woocommerce_product_get_regular_price', 'custom_category_discount', 10, 2);
add_filter('woocommerce_product_get_sale_price', 'custom_category_discount', 10, 2);
function custom_category_discount($price, $product) {
    $category = get_the_terms($product->get_id(), 'product_cat');

    if ($category) {
        // Получаем значение из метаполя
        $discount_option = carbon_get_term_meta($category[0]->term_id, 'category_discount');

        // Применяем скидку, если выбрана скидка
        if ($discount_option && $discount_option !== 'none') {
            $discount = floatval($discount_option);
            $price = floatval($price);
            $discount_amount = $price * ($discount / 100.0); // Преобразование в float
            $price -= $discount_amount;
        }
    }

    return $price;
}

/**
 * Добавляем поле "Новинка" во вкладку "Основные" в товарах WooCommerce
 * @return void
 */
add_action('woocommerce_product_options_general_product_data', 'add_custom_meta_field');
function add_custom_meta_field() {

    $post_id = get_the_ID();
    $is_new = get_post_meta($post_id, '_is_new', true);

    echo '<div class="options_group">';
    echo '<p class="form-field custom-checkbox-field">';
    echo '<label for="is_new">' . __('Новинка', 'textdomain') . '</label>';
    echo '<span class="description">' . __('Поставте прапорець якщо товар являєтся новинкой.', 'textdomain') . '</span>';
    echo '<input type="checkbox" class="checkbox" name="is_new" id="is_new" value="1" ' . checked(1, $is_new, false) . ' />';
    echo '</p>';
    echo '</div>';
}

add_action('woocommerce_process_product_meta', 'save_custom_meta_field');
function save_custom_meta_field($post_id) {
    // Проверяем, выполняется ли автосохранение или необходимые права доступа
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    // Проверяем, установлен ли чекбокс "Новинка"
    $is_new = isset($_POST['is_new']) ? '1' : '0';

    // Обновляем значение мета-поля "_is_new"
    update_post_meta($post_id, '_is_new', $is_new);

    // Если чекбокс был установлен, сохраняем текущую дату
    if ($is_new === '1') {
        update_post_meta($post_id, '_is_new_date', current_time('mysql'));
    } else {
        // Если чекбокс был снят, удаляем дату
        delete_post_meta($post_id, '_is_new_date');
    }
}

/**
 * ДОБАВЛЕНИЕ ПОЛЯ ВИДЕО
 * @return void
 */
add_action('woocommerce_product_options_general_product_data', 'add_custom_video_field');
function add_custom_video_field() {
    $post_id = get_the_ID();

    $video_link = get_post_meta($post_id, '_video_link', true);

    echo '<div class="options_group">';
    echo '<p class="form-field custom-video-field">';
    echo '<label for="video_link">' . __('Посилння на відео', 'textdomain') . '</label>';
    echo '<input type="text" class="input-text" name="video_link" id="video_link" value="' . esc_attr($video_link) . '" />';
    echo '</p>';
    echo '</div>';
}

add_action('woocommerce_process_product_meta', 'save_custom_video_field');
function save_custom_video_field($post_id) {

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $video_link = sanitize_text_field($_POST['video_link']);
    update_post_meta($post_id, '_video_link', $video_link);
}

/**
 * Добавление текстового поля и чекбокса во вкладку "общие"
 * Віртуальна позиція (задля роялті)
 */
add_action( 'woocommerce_product_options_general_product_data', 'add_custom_fields_to_general_tab' );
function add_custom_fields_to_general_tab() {
    global $post;

    echo '<div class="options_group">';
    echo '<h3 class="adminka-field">' . __( 'Віртуальна позиція (задля роялті)', 'woocommerce' ) . '</h3>';

    woocommerce_wp_text_input(
        array(
            'id'          => '_custom_status',
            'label'       => __( 'Статус', 'woocommerce' ),
            'placeholder' => '',
            'desc_tip'    => 'true',
            'description' => __( 'Введіть статус.', 'woocommerce' ),
        )
    );

    woocommerce_wp_checkbox(
        array(
            'id'          => '_custom_royalty',
            'label'       => __( 'Роялті', 'woocommerce' ),
            'description' => __( 'Поставте прапорець, якщо товар належить до роялті', 'woocommerce' ),
        )
    );

    echo '
	<p>Якщо встановлено прапорець "роялті", то кнопка "купити" буде схована в картці товару, а замість неї відобразиться текст з поля "статус"</p>
	</div>';
}

add_action( 'woocommerce_process_product_meta', 'save_custom_fields_general_tab' );
function save_custom_fields_general_tab( $post_id ) {
    $custom_status = isset( $_POST['_custom_status'] ) ? sanitize_text_field( $_POST['_custom_status'] ) : '';
    update_post_meta( $post_id, '_custom_status', $custom_status );

    $custom_royalty = isset( $_POST['_custom_royalty'] ) ? 'yes' : 'no';
    update_post_meta( $post_id, '_custom_royalty', $custom_royalty );
}

/**
 * @param $permission
 * @param $context
 * @param $object_id
 * @param $post_type
 * @return true
 */
add_filter( 'woocommerce_rest_check_permissions', 'my_woocommerce_rest_check_permissions', 90, 4 );
function my_woocommerce_rest_check_permissions( $permission, $context, $object_id, $post_type  ){
    return true;
}

/************************************
/******** ПРЕДОПЛАТА 200 грн
 **********************************/
/**
 * Отключаем "Оплату при получении" для заказов менее 300 грн
 **/
add_filter('woocommerce_available_payment_gateways', 'disable_cod_for_orders_below_300');
function disable_cod_for_orders_below_300($available_gateways) {

    if(! is_admin() && WC()->cart) {
        // Получаем общую сумму заказа
        $total = WC()->cart->total;

        // Если сумма заказа меньше 300 грн
        if ($total < 300) {
            // Отключаем "Оплату при получении"
            if (isset($available_gateways['cod'])) {
                unset($available_gateways['cod']);
            }
        } else {
            if (isset($available_gateways['cod'])) {
                // Изменяем заголовок метода оплаты
                $available_gateways['cod']->title = 'Попередня оплата 200 грн';
            }
        }

        foreach (WC()->cart->get_cart() as $cart_item) {
            $product = $cart_item['data'];

            // Проверяем, доступен ли товар для предзаказа
            if ($product->is_on_backorder()) {
                // Убираем способ оплаты "Оплата при получении", если товар на предзаказе
                if (isset($available_gateways['cod'])) {
                    unset($available_gateways['cod']);
                }
                break; // Выходим из цикла, если хотя бы один товар на предзаказе
            }
        }
    }

    return $available_gateways;
}

/**
 * Добавляем некликабельную приписку для заказов менее 300 грн
 */

add_action(
    'anyweb_checkout_after_payment_methods',
    'custom_notice_for_low_amount'
);
function custom_notice_for_low_amount() {
    $total = WC()->cart->total;

//    echo do_shortcode('[saphali_user_discount]');

    $str = '';

    // Если сумма заказа меньше 300 грн
    if ($total < 300) {
        $str = '<p class="payment_methods_info"">Оплата під час отримання доступна для замовлень на суму більше ніж 300 грн.</p>';
    }

    foreach (WC()->cart->get_cart() as $cart_item) {
        $product = $cart_item['data'];

        // Проверяем, доступен ли товар для предзаказа
        if ($product->is_on_backorder()) {
            $str = '<p class="payment_methods_info">Опція “оплата під час отримання” недоступна для акції “передзамовлення”. Будь ласка, оберіть онлайн-оплату через LiqPay.</p>';
            break; // Выходим из цикла, если хотя бы один товар на предзаказе
        }
    }

    echo $str;
}

/**
 * Добавляем информацию о предоплате в метаданные заказа
 */
add_action('woocommerce_checkout_update_order_meta', 'add_prepayment_info_to_order_meta');
function add_prepayment_info_to_order_meta($order_id) {
    // Предположим, что у вас есть переменная с суммой предоплаты
    $prepayment = 200; // Например, 200 грн
    $payment_status = 'Не оплачено'; // Начальный статус
    $order = wc_get_order($order_id);
    $total = $order->get_total();

    if ($total >= 300 && isset($_POST['payment_method']) && $_POST['payment_method'] === 'cod') {

        update_post_meta($order_id, '_prepayment_amount', $prepayment);
        update_post_meta($order_id, '_prepayment_status', $payment_status);

    }
}

/**
 * Добавляем информацию о предоплате к деталям заказа в админке
 */
add_action('woocommerce_admin_order_data_after_order_details', 'display_prepayment_info_in_admin_order');
function display_prepayment_info_in_admin_order($order) {
    // Получаем мета-данные предоплаты
    $prepayment = get_post_meta($order->get_id(), '_prepayment_amount', true);
    $payment_status = get_post_meta($order->get_id(), '_prepayment_status', true);
    $total = $order->get_total();
    $payment_method = $order->get_payment_method();
    $payment_status === 'Сплачено' ? $color = 'green' : $color = 'red';

    if ($total >= 300 && $payment_method === 'cod') {
        echo '<p class="mt-2 form-field form-field-wide" style="color: ' . $color . '"><strong>Передплата: </strong>' . wc_price($prepayment) . '</p>';
        echo '<p class="form-field form-field-wide" style="color: ' . $color . '"><strong>Статус передоплати: </strong>' . esc_html($payment_status) . '</p>';
    }
}

/**
 * Обновляем Статус передоплати после успешной оплаты
 */
add_action('woocommerce_order_status_processing', 'update_prepayment_status');
function update_prepayment_status($order_id) {
    $order = wc_get_order($order_id);
    $total = $order->get_total();
    $payment_method = $order->get_payment_method();

    if ($total >= 300 && $payment_method === 'cod') {
        update_post_meta($order_id, '_prepayment_status', 'Сплачено');
    }
}

/**
 * Отображаем информацию о предоплате для пользователя на странице заказа
 */
add_action('woocommerce_order_details_after_order_table', 'display_prepayment_info_after_order_table', 10, 1);
function display_prepayment_info_after_order_table($order) {
    $total = $order->get_total();
    $payment_method = $order->get_payment_method();

    if ($total >= 300 && $payment_method === 'cod') {
        $prepayment = get_post_meta($order->get_id(), '_prepayment_amount', true);
        $payment_status = get_post_meta($order->get_id(), '_prepayment_status', true);
        $color = ($payment_status === 'Сплачено' || $payment_status === 'Оплачено') ? 'green' : 'red';

        echo '<section class="woocommerce-order-prepayment" style="margin-top:20px;">';
        echo '<h3>Інформація про передплату</h3>';
        echo '<p style="color: ' . $color . '"><strong>Передплата: </strong>' . wc_price($prepayment) . '</p>';
        echo '<p style="color: ' . $color . '"><strong>Статус передоплати: </strong>' . esc_html($payment_status) . '</p>';
        echo '</section>';
    }
}

/**
 * Отображаем информацию о предоплате для АДМИНИ на странице заказа
 */
add_action('woocommerce_admin_order_totals_after_total', 'display_prepayment_in_admin_order');
function display_prepayment_in_admin_order($order_id) {
    $prepayment = get_post_meta($order_id, '_prepayment_amount', true);
    $payment_status = get_post_meta($order_id, '_prepayment_status', true);
    $order = wc_get_order($order_id);
    $total = $order->get_total();
    $payment_method = $order->get_payment_method();

    ($payment_status === 'Сплачено' || $payment_status === 'Оплачено') ? $payment = '200' : $payment = '0';

    if ($payment_method === 'cod' && !empty($payment_status)) {
        echo '<tr>';
        echo '<td class="label">' . __('Передплата', 'woocommerce') . ':</td>';
        echo '<td width="1%"></td>';
        echo '<td class="total">' . wc_price($payment) . '</td>';
        echo '</tr>';
    }
}

add_action('woocommerce_order_details_after_order_table', 'display_prepayment_in_order_details', 10, 1);
function display_prepayment_in_order_details($order) {
    $prepayment = get_post_meta($order->get_id(), '_prepayment_amount', true);
    $total = $order->get_total();

    $_payment_status = get_post_meta($order->get_id(), '_prepayment_status', true);

    ($_payment_status === 'Сплачено' || $_payment_status === 'Оплачено') ? $payment = '200' : $payment = '0';

    if ($total >= 300 && isset($_POST['payment_method']) && $_POST['payment_method'] === 'cod') {
        echo '<p><strong>' . __('Передплата', 'woocommerce') . ':</strong> ' . wc_price($payment) . '</p>';
        echo $_payment_status;
    }

}

add_action('woocommerce_admin_order_totals_after_total', 'modify_admin_order_total');
function modify_admin_order_total($order_id) {
    $order = wc_get_order($order_id);
    $prepayment = get_post_meta($order_id, '_prepayment_amount', true);
    $payment_status = get_post_meta($order_id, '_prepayment_status', true);

    if (!empty($prepayment) && ($payment_status === 'Сплачено' || $payment_status === 'Оплачено') ) {
        $total = $order->get_total() - $prepayment;
        echo '<tr>';
        echo '<td class="label">' . __('Разом з урахуванням передоплати', 'woocommerce') . ':</td>';
        echo '<td width="1%"></td>';
        echo '<td class="total">' . wc_price($total) . '</td>';
        echo '</tr>';
    }
}

/************************************
/******** END ПРЕДОПЛАТА 200 грн ***
 ***********************************/

/************************************
/******** ПРЕДЗАКАЗ *****************
 ************************************/
/**
 * Добавление поля выбора даты
 * "Дата закінчення передзамовлення (заявка в telegram)"
 * на вкладку "Общие"
 */
add_action( 'woocommerce_product_options_general_product_data', 'add_preorder_datepicker_to_general_tab' );
function add_preorder_datepicker_to_general_tab() {
    global $post;

    echo '<div class="options_group">';

    woocommerce_wp_text_input(
        array(
            'id'          => '_custom_preorder',
            'label'       => __( 'Дата закінчення передзамовлення (заявка в telegram)', 'woocommerce' ),
            'description' => __( 'Виберіть дату закінчення передзамовлення. Заявки приходять в telegram канал', 'woocommerce' ),
            'type'        => 'date',
        )
    );

    echo '</div>';
}

add_action( 'woocommerce_product_options_general_product_data', 'add_preorder_datepicker_for_countdown' );
function add_preorder_datepicker_for_countdown() {
    global $post;

    echo '<div class="options_group">';

    woocommerce_wp_text_input(
        array(
            'id'          => '_custom_preorder_countdown',
            'label'       => __( 'Дата закінчення передзамовлення', 'woocommerce' ),
            'description' => __( 'Виберіть дату закінчення передзамовлення. Для зворотного відліку', 'woocommerce' ),
            'type'        => 'date',
        )
    );

    echo '</div>';
}

add_action( 'woocommerce_process_product_meta', 'save_preorder_date_general_tab' );
function save_preorder_date_general_tab( $post_id ) {

    $custom_preorder_date = isset( $_POST['_custom_preorder'] ) ? $_POST['_custom_preorder'] : '';
    update_post_meta( $post_id, '_custom_preorder', $custom_preorder_date );
}

add_action( 'woocommerce_process_product_meta', 'save_preorder_date_countdown' );
function save_preorder_date_countdown( $post_id ) {

    $custom_preorder_countdown = isset( $_POST['_custom_preorder_countdown'] ) ? $_POST['_custom_preorder_countdown'] : '';
    update_post_meta( $post_id, '_custom_preorder_countdown', $custom_preorder_countdown );
}


add_filter('woocommerce_cart_item_name', 'custom_preorder_message_in_cart', 10, 3);
function custom_preorder_message_in_cart($product_name, $cart_item, $cart_item_key) {
    // Получаем объект товара
    $product = $cart_item['data'];

    if ($product->get_stock_status() === 'onbackorder') {
        $custom_preorder_countdown = get_post_meta($product->get_id(), '_custom_preorder_countdown', true);

        // Добавляем текст к названию товара
        $product_name .= '<div class="px-2 py-1 bg-success" style="width: fit-content;">
                              <p class="p-0 mb-0">ВІДПРАВЛЕННЯ ОЧІКУЄТЬСЯ З ' . date("d.m.Y", strtotime($custom_preorder_countdown)) . '</p>
                          </div>';
    }

    return $product_name;
}

/************************************
/******** END ПРЕДЗАКАЗ *****************
 ************************************/

/****************************************************
/* ДОБАВЛЕНИЯ ФУНКЦИОНАЛА ДЛЯ ВЫБОРА МЕТОДА ДОСТАВКИ *
 *****************************************************/
add_filter( 'woocommerce_shipping_chosen_method', '__return_false', 999 );

add_action( 'woocommerce_before_cart', 'default_shipping_country_force' );
add_action( 'woocommerce_before_checkout_form', 'default_shipping_country_force' );
function default_shipping_country_force() {
    // Проверяем наличие WooCommerce и объекта клиента
    if ( is_admin() || ! function_exists( 'WC' ) || ! WC()->customer ) {
        return;
    }

    // Если страна еще не определена, ставим UA
    if ( empty( WC()->customer->get_shipping_country() ) ) {
        WC()->customer->set_billing_country( 'UA' );
        WC()->customer->set_shipping_country( 'UA' );

        // Пересчитываем корзину
        WC()->cart->calculate_totals();
    }
}

//add_action( 'wp_footer', 'force_shipping_selection_script' );
function force_shipping_selection_script() {
    // Работает только на страницах корзины и оформления
    if ( is_cart() || is_checkout() ) {
        ?>
        <script type="text/javascript" xmlns="http://www.w3.org/1999/html">
            jQuery(function($){
                // Функция проверки выбора
                function checkShippingSelection() {
                    var isSelected = $('input[name^="shipping_method"]:checked').length > 0;
                    var checkoutBtn = $('.checkout-button, #place_order');

                    if ( !isSelected ) {
                        checkoutBtn.css({
                            'pointer-events': 'none',
                            'opacity': '0.5',
                            'filter': 'grayscale(1)'
                        });
                    } else {
                        checkoutBtn.css({
                            'pointer-events': 'auto',
                            'opacity': '1',
                            'filter': 'none'
                        });
                    }
                }

                // Проверяем при загрузке и при каждом обновлении корзины (AJAX)
                $(document.body).on('updated_cart_totals updated_checkout', function(){
                    checkShippingSelection();
                });

                // Проверяем при клике на метод доставки
                $(document).on('change', 'input[name^="shipping_method"]', function(){
                    checkShippingSelection();
                });

                checkShippingSelection(); // Инициализация при первой загрузке
            });
        </script>
        <?php
    }
}

//add_action( 'woocommerce_check_cart_items', 'prevent_checkout_without_shipping' );
//add_action( 'woocommerce_checkout_process', 'prevent_checkout_without_shipping' );
function prevent_checkout_without_shipping() {
    $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );

    if ( WC()->cart->get_cart_contents_count() > 0 ) {
        // Если метод не выбран или массив пуст
        if (empty($chosen_methods) || !isset($chosen_methods[0])) {
            wc_add_notice('Будь ласка, оберіть метод доставки, щоб продовжити оформлення замовлення.', 'error');
        }
    }
}

/****************************************************
/* END - ФУНКЦИОНАЛА ДЛЯ ВЫБОРА МЕТОДА ДОСТАВКИ *
 *****************************************************/

/****************************************************
/* Корзина — скрываем доставку.
 * Оформление заказа — разрешаем её расчёт и вывод. *
 *****************************************************/
add_filter(
    'woocommerce_cart_ready_to_calc_shipping',
    function ( $ready ) {
        if ( is_cart() ) {
            return false;
        }

        if ( is_checkout() ) {
            return true;
        }

        return $ready;
    }
);

/****************************************************
 *         END
/* Корзина — скрываем доставку.
 * Оформление заказа — разрешаем её расчёт и вывод. *
 *****************************************************/

/**
 * Сообщение о доступной бесплатной доставке.
 */
function anyweb_free_shipping_notice() {
    if ( ! WC()->cart || ! WC()->cart->needs_shipping() ) {
        return;
    }

    foreach ( WC()->shipping()->get_packages() as $package ) {
        foreach ( $package['rates'] ?? [] as $rate ) {
            if ( 'free_shipping' === $rate->get_method_id() ) {
                echo '<div class="free-shipping-notice">';
                echo esc_html__( 'Для вашого замовлення доступна безкоштовна доставка!', 'any-web-theme' );
                echo '</div>';

                return;
            }
        }
    }
}


// Оформление заказа — перед выбором оплаты.
add_action(
    'woocommerce_review_order_before_payment',
    'anyweb_free_shipping_notice'
);



function anyweb_free_shipping_progress() {
    if ( ! WC()->cart || WC()->cart->is_empty() ) {
        return;
    }

    // Временно заданный порог.
    // Далее заменим на значение из настроек Morkva.
    $min_price_delivery = carbon_get_theme_option( 'min_price_delivery' );

    if ( $min_price_delivery <= 0 ) {
        return;
    }

    // Стоимость товаров после скидок, включая налог.
    // Доставка и дополнительные сборы не учитываются.

    $products_total = (float) WC()->cart->get_cart_contents_total()
        + (float) WC()->cart->get_cart_contents_tax();



    $remaining = max( 0, $min_price_delivery - $products_total );

    echo '<div class="form-information-block free-shipping-progress">';

    if ( $remaining > 0 ) {
        echo 'Додайте ще на '
            . wp_kses_post( wc_price( $remaining ) )
            . ' для БЕЗКОШТОВНОЇ доставки';
    } else {
        echo 'Вам доступна БЕЗКОШТОВНА доставка!';
    }

    echo '</div>';
}

add_action(
    'wc_ajax_anyweb_free_shipping_progress',
    'anyweb_ajax_free_shipping_progress'
);

function anyweb_ajax_free_shipping_progress() {
    if ( ! WC()->cart ) {
        wp_send_json_error();
    }

    WC()->cart->calculate_totals();

    ob_start();
    anyweb_free_shipping_progress();
    $html = ob_get_clean();

    wp_send_json_success( [ 'html' => $html ] );
}

add_action( 'woocommerce_before_quantity_input_field', function () {
    if ( is_cart() ) {
        echo '<button type="button" class="cart-qty-minus" aria-label="Зменшити кількість">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                  <path d="M4 10H16" stroke-width="2" stroke="currentColor"/>
                </svg>
               </button>';
    }
} );

add_action( 'woocommerce_after_quantity_input_field', function () {
    if ( is_cart() ) {
        echo '<button type="button" class="cart-qty-plus" aria-label="Збільшити кількість">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                  <path d="M4 10H10M10 10H16M10 10V4M10 10V16" stroke-width="2" stroke="currentColor"/>
                </svg>
                </button>';
    }
} );

// Заголовок мини-корзины.
add_action( 'woocommerce_before_mini_cart', function () {
    if ( ! WC()->cart ) {
        return;
    }

    printf(
        '<div class="mini-cart-heading">Кошик (%s)</div>',
        esc_html( WC()->cart->get_cart_contents_count() )
    );
} );

// Заменяем стандартный промежуточный итог своими итогами.
remove_action(
    'woocommerce_widget_shopping_cart_total',
    'woocommerce_widget_shopping_cart_subtotal',
    10
);

add_action(
    'woocommerce_widget_shopping_cart_total',
    'anyweb_mini_cart_totals',
    10
);

function anyweb_mini_cart_totals() {
    if ( ! WC()->cart ) {
        return;
    }

    $cart = WC()->cart;

    $minimum = function_exists( 'carbon_get_theme_option' )
        ? (float) carbon_get_theme_option( 'min_price_delivery' )
        : 0;

    // Сумма товаров после скидок и купонов, включая налог.
    $products_total = (float) $cart->get_cart_contents_total()
        + (float) $cart->get_cart_contents_tax();

    $remaining = max( 0, $minimum - $products_total );

    if ( $minimum > 0 && $cart->needs_shipping() ) {
        if ( $remaining > 0 ) {
            echo '<span class="mini-cart-free-shipping">';

            printf(
                'Додайте ще на %s для БЕЗКОШТОВНОЇ доставки',
                wp_kses_post( wc_price( $remaining ) )
            );

            echo '</span>';
        }

        echo '<span class="mini-cart-summary-row">';
        echo '<span>Доставка:</span>';

        if ( $remaining <= 0 ) {
            echo '<strong class="mini-cart-shipping-free">';
            echo 'БЕЗКОШТОВНА';
            echo '</strong>';
        } else {
            echo '<span>';

            printf(
                'від %s — БЕЗКОШТОВНА',
                wp_kses_post( wc_price( $minimum ) )
            );

            echo '</span>';
        }

        echo '</span>';
    }

    echo '<span class="mini-cart-summary-row mini-cart-grand-total">';
    echo '<strong>Загальна сума:</strong>';
    echo '<strong>' . wp_kses_post( $cart->get_total() ) . '</strong>';
    echo '</span>';
}

add_action( 'wp_loaded', function () {
    remove_action(
        'woocommerce_widget_shopping_cart_buttons',
        'woocommerce_widget_shopping_cart_button_view_cart',
        10
    );

    remove_action(
        'woocommerce_widget_shopping_cart_buttons',
        'woocommerce_widget_shopping_cart_proceed_to_checkout',
        20
    );

    add_action(
        'woocommerce_widget_shopping_cart_buttons',
        'anyweb_mini_cart_buttons',
        10
    );
} );

function anyweb_mini_cart_buttons() {
    ?>
    <a href="<?php echo esc_url( wc_get_cart_url() ); ?>"
       class="button wc-forward">
        <span type="button">Перейти в кошик</span>
        <span class="button__bottom_line">
            <svg xmlns="http://www.w3.org/2000/svg" width="153" height="4" viewBox="0 0 153 4" fill="none">
                <path d="M31.1106 0.853003C32.0208 0.853003 32.8072 1.19794 32.7198 2.04051C32.6543 2.67386 32.2247 2.97923 31.0742 3.04144C26.9674 3.09233 23.0427 3.14886 18.9359 3.12624C18.215 3.12624 17.5087 2.82654 17.4723 2.11403C17.4286 1.20925 18.2878 1.03393 19.1543 1C21.0839 0.920833 23.0135 0.926515 24.9431 0.886931C25.0961 0.886931 29.0645 0.836033 31.1179 0.847343L31.1106 0.853003Z" fill="#009E97"/>
                <path d="M6.59017 1.2258C9.11686 1.19188 9.97607 1.04484 11.6654 1.04484C12.6193 1.04484 13.6023 1.47463 13.5659 2.3681C13.5295 3.15413 12.663 3.41989 11.7382 3.40858C8.3523 3.37465 5.21396 3.34638 1.83534 3.27287C0.910584 3.25025 -0.137955 2.90529 0.014957 2.06271C0.12418 1.44068 0.997967 1.01092 1.88631 1.04484C3.3499 1.10139 5.17027 1.27104 6.59017 1.22014V1.2258Z" fill="#009E97"/>
                <path d="M125.338 3.39153C123.096 3.30105 121.53 3.2445 119.965 3.17664C119.076 3.13706 118.181 2.97871 118.166 2.14179C118.159 1.35576 118.996 0.948614 119.863 0.942959C124.435 0.90903 129.008 0.926004 133.588 0.988207C134.331 0.999517 134.914 1.47452 134.819 2.15311C134.702 2.99569 133.872 3.14837 132.984 3.17664C130.217 3.26146 127.443 3.33498 125.338 3.39719V3.39153Z" fill="#009E97"/>
                <path d="M82.4576 2.3413C81.3799 2.3922 79.4576 2.48268 77.528 2.55619C76.6324 2.59012 75.744 2.47137 75.5911 1.64576C75.4382 0.808842 76.2392 0.503489 77.1421 0.418666C81.227 0.0510995 85.3265 -0.0676501 89.4333 0.0680668C90.4163 0.101996 91.494 0.294248 91.5377 1.25558C91.5813 2.31869 90.4308 2.31871 89.4697 2.33002C87.4163 2.35263 85.3629 2.33568 82.4576 2.33568V2.3413Z" fill="#009E97"/>
                <path d="M144.113 3.0816C143.101 3.0816 139.431 3.16076 139.424 1.79228C139.417 0.423799 142.169 0.746117 143.654 0.757426C145.926 0.774391 148.045 0.768735 150.39 0.225867C151.475 -0.0286022 152.531 -0.271735 152.924 0.712212C153.295 1.63396 152.232 1.86014 151.358 2.18813C149.363 2.72534 146.858 3.03637 144.12 3.0816H144.113Z" fill="#009E97"/>
                <path d="M48.3513 0.350479C49.4435 0.31655 50.7323 0.26564 50.7542 1.46447C50.776 2.41449 49.691 2.63506 48.6862 2.65768C45.3148 2.73684 41.9362 2.78207 38.5649 2.82166C37.7858 2.83296 37.0722 2.64636 37.0066 1.94516C36.9338 1.1252 37.7202 0.751985 38.594 0.678471C40.3853 0.52579 46.9022 0.395723 48.3513 0.356139V0.350479Z" fill="#009E97"/>
                <path d="M61.6732 2.74215C60.1077 2.74215 58.5421 2.76476 56.9766 2.73083C55.9353 2.70821 54.9669 2.43679 55.0324 1.46981C55.0834 0.672471 55.9645 0.434986 56.8601 0.429331C60.2315 0.412367 63.6101 0.401057 66.9814 0.429331C67.8697 0.440641 68.6926 0.762969 68.7217 1.56031C68.7581 2.58384 67.7678 2.73084 66.7339 2.73649C65.0445 2.7478 63.3625 2.73649 61.6732 2.73649V2.74215Z" fill="#009E97"/>
                <path d="M104.054 2.4419C102.364 2.40231 100.668 2.38532 98.9785 2.31181C98.3669 2.28354 97.7771 2.06302 97.6606 1.52015C97.5149 0.841566 98.0538 0.406133 98.811 0.31C102.415 -0.159355 106.027 0.106415 109.624 0.406123C110.403 0.468326 110.869 0.999886 110.738 1.64454C110.6 2.32313 109.872 2.5267 109.122 2.5267C107.432 2.5267 105.736 2.45884 104.046 2.41926C104.046 2.42491 104.046 2.43059 104.046 2.4419H104.054Z" fill="#009E97"/>
            </svg>
        </span>
    </a>

    <a href="<?php echo esc_url( wc_get_checkout_url() ); ?>"
       class="button checkout wc-forward">
        Оформити замовлення
    </a>
    <?php
}

add_filter(
    'woocommerce_add_to_cart_fragments',
    'anyweb_header_cart_fragments'
);

function anyweb_header_cart_fragments( $fragments ) {
    if ( ! WC()->cart ) {
        return $fragments;
    }

    // Полное содержимое выпадающей мини-корзины.
    ob_start();
    ?>
    <div class="header-cart__content">
        <?php woocommerce_mini_cart(); ?>
    </div>
    <?php
    $fragments['div.header-cart__content'] = ob_get_clean();

    // Количество товаров в шапке.
    $fragments['.header-basket-block .bubble'] =
        '<span class="bubble">'
        . esc_html( WC()->cart->get_cart_contents_count() )
        . '</span>';

    // Сумма в шапке.
    ob_start();
    ?>
    <div class="basket-link-wrap">
        <span class="cart-title">товарів на суму</span>
        <br>
        <span class="basket-sum">
            <strong>
                <?php echo wp_kses_post( WC()->cart->get_cart_total() ); ?>
            </strong>
        </span>
    </div>
    <?php
    $fragments['div.basket-link-wrap'] = ob_get_clean();

    return $fragments;
}

add_filter( 'woocommerce_checkout_fields', function ( $fields ) {
    $settings = array(
        'billing_last_name' => array(
            'label'        => 'Прізвище',
            'placeholder'  => 'Введіть дані',
            'priority'     => 10,
            'autocomplete' => 'family-name',
        ),
        'billing_first_name' => array(
            'label'        => 'Ім’я',
            'placeholder'  => 'Введіть дані',
            'priority'     => 20,
            'autocomplete' => 'given-name',
        ),
        'billing_phone' => array(
            'label'        => 'Номер телефона',
            'placeholder'  => '+380 XX XXX XX XX',
            'priority'     => 30,
            'autocomplete' => 'tel',
        ),
        'billing_email' => array(
            'label'        => 'E-mail',
            'placeholder'  => 'Введіть ваш e-mail',
            'priority'     => 40,
            'autocomplete' => 'email',
        ),
    );

    foreach ( $settings as $key => $options ) {
        if ( isset( $fields['billing'][ $key ] ) ) {
            $fields['billing'][ $key ] = array_merge(
                $fields['billing'][ $key ],
                $options
            );

            $fields['billing'][ $key ]['class'] = array(
                'form-row-wide',
                'anyweb-contact-field',
            );
        }
    }

    unset( $fields['billing']['billing_city'] );

    return $fields;
}, 100 );

add_action(
    'woocommerce_before_checkout_billing_form',
    'anyweb_checkout_customer_tabs'
);

function anyweb_checkout_customer_tabs() {
    $logged_in = is_user_logged_in();
    ?>

    <div class="anyweb-checkout-tabs">
        <?php if ( $logged_in ) : ?>

            <a class="anyweb-checkout-tabs__active"
               href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">
                Особистий кабінет
            </a>

        <?php else : ?>

            <div class="button-container-login">
                <button
                        type="button"
                        class="anyweb-checkout-tabs__button"
                        data-checkout-view="guest"
                        aria-pressed="true"
                >
                    Я новий клієнт
                </button>

                <span class="anyweb-checkout-tabs__underline" aria-hidden="true">
                   <svg xmlns="http://www.w3.org/2000/svg" width="125" height="4" viewBox="0 0 125 4" fill="none">
  <path d="M36.2721 0.515113C37.2839 0.515113 38.1581 0.860046 38.061 1.70262C37.9881 2.33597 37.5106 2.64134 36.2316 2.70355C31.6664 2.75444 27.3034 2.81097 22.7382 2.78835C21.9368 2.78835 21.1516 2.48865 21.1112 1.77614C21.0626 0.871359 22.0177 0.69604 22.981 0.66211C25.126 0.582942 27.2711 0.588625 29.4161 0.549041C29.5861 0.549041 33.9976 0.498143 36.2802 0.509452L36.2721 0.515113Z" fill="#009E97"/>
  <path d="M7.24654 0.887914C10.0249 0.853984 10.9697 0.706954 12.8272 0.706954C13.8761 0.706954 14.9571 1.13674 14.917 2.03021C14.877 2.81624 13.9242 3.082 12.9073 3.07069C9.18418 3.03676 5.73327 3.00849 2.01813 2.93497C1.00128 2.91235 -0.151695 2.5674 0.0164467 1.72482C0.136548 1.10279 1.09736 0.673024 2.07419 0.706954C3.68354 0.763502 5.68523 0.933147 7.24654 0.882253V0.887914Z" fill="#009E97"/>
  <path d="M57.2307 0.0125884C58.4334 -0.0213409 59.8524 -0.0722511 59.8765 1.12658C59.9005 2.0766 58.7059 2.29717 57.5995 2.31979C53.8874 2.39895 50.1674 2.44418 46.4553 2.48376C45.5974 2.49507 44.8117 2.30847 44.7395 1.60727C44.6594 0.78731 45.5252 0.414094 46.4873 0.340581C48.4596 0.187899 55.6353 0.0578329 57.2307 0.0182488V0.0125884Z" fill="#009E97"/>
  <path d="M73.8123 2.40426C72.0893 2.40426 70.3662 2.42687 68.6432 2.39294C67.4972 2.37032 66.4313 2.0989 66.5035 1.13192C66.5596 0.334581 67.5293 0.0970956 68.515 0.0914407C72.2255 0.0744761 75.944 0.0631663 79.6545 0.0914407C80.6323 0.10275 81.5379 0.425078 81.5699 1.22241C81.61 2.24595 80.5201 2.39295 79.3821 2.3986C77.5228 2.40991 75.6716 2.3986 73.8123 2.3986V2.40426Z" fill="#009E97"/>
  <path d="M100.653 0.0125884C101.855 -0.0213409 103.274 -0.0722511 103.298 1.12658C103.322 2.0766 102.128 2.29717 101.021 2.31979C97.3093 2.39895 93.5892 2.44418 89.8772 2.48376C89.0193 2.49507 88.2336 2.30847 88.1614 1.60727C88.0812 0.78731 88.9471 0.414094 89.9092 0.340581C91.8815 0.187899 99.0571 0.0578329 100.653 0.0182488V0.0125884Z" fill="#009E97"/>
  <path d="M117.242 2.40426C115.519 2.40426 113.796 2.42687 112.073 2.39294C110.927 2.37032 109.861 2.0989 109.933 1.13192C109.989 0.334581 110.959 0.0970956 111.945 0.0914407C115.655 0.0744761 119.374 0.0631663 123.084 0.0914407C124.062 0.10275 124.968 0.425078 125 1.22241C125.04 2.24595 123.95 2.39295 122.812 2.3986C120.953 2.40991 119.101 2.3986 117.242 2.3986V2.40426Z" fill="#009E97"/>
</svg>
                </span>
            </div>

            <div class="button-container-login">
                <button
                        type="button"
                        class="anyweb-checkout-tabs__button"
                        data-checkout-view="login"
                        aria-pressed="false"
                        aria-controls="anyweb-checkout-login-panel"
                >
                    Увійти в особистий кабінет
                </button>

                <span class="anyweb-checkout-tabs__underline" aria-hidden="true">
                   <svg xmlns="http://www.w3.org/2000/svg" width="232" height="4" viewBox="0 0 232 4" fill="none">
                    <path d="M26.3559 0.853003C27.1272 0.853003 27.7937 1.19794 27.7196 2.04051C27.6641 2.67386 27.3 2.97923 26.3251 3.04144C22.8448 3.09233 19.5188 3.14886 16.0385 3.12624C15.4276 3.12624 14.8291 2.82654 14.7982 2.11403C14.7612 1.20925 15.4893 1.03393 16.2237 1C17.8589 0.920833 19.4941 0.926515 21.1294 0.886931C21.2589 0.886931 24.622 0.836033 26.3621 0.847343L26.3559 0.853003Z" fill="#009E97"/>
                    <path d="M5.58481 1.2258C7.72604 1.19188 8.45418 1.04484 9.88578 1.04484C10.6941 1.04484 11.5272 1.47463 11.4963 2.3681C11.4655 3.15413 10.7312 3.41989 9.94749 3.40858C7.07812 3.37465 4.41855 3.34638 1.55535 3.27287C0.77167 3.25025 -0.116909 2.90529 0.0126752 2.06271C0.105236 1.44068 0.845723 1.01092 1.59855 1.04484C2.83885 1.10139 4.38152 1.27104 5.58481 1.22014V1.2258Z" fill="#009E97"/>
                    <path d="M106.215 3.39153C104.314 3.30105 102.988 3.2445 101.661 3.17664C100.908 3.13706 100.149 2.97871 100.137 2.14179C100.131 1.35576 100.84 0.948614 101.575 0.942959C105.45 0.90903 109.325 0.926004 113.206 0.988207C113.836 0.999517 114.329 1.47452 114.249 2.15311C114.15 2.99569 113.447 3.14837 112.694 3.17664C110.349 3.26146 107.998 3.33498 106.215 3.39719V3.39153Z" fill="#009E97"/>
                    <path d="M69.8857 2.3413C68.9724 2.3922 67.3434 2.48268 65.7081 2.55619C64.9491 2.59012 64.1963 2.47137 64.0668 1.64576C63.9372 0.808842 64.6159 0.503489 65.3811 0.418666C68.8429 0.0510995 72.317 -0.0676501 75.7972 0.0680668C76.6303 0.101996 77.5435 0.294248 77.5806 1.25558C77.6176 2.31869 76.6426 2.31871 75.8281 2.33002C74.0879 2.35263 72.3478 2.33568 69.8857 2.33568V2.3413Z" fill="#009E97"/>
                    <path d="M122.146 3.0816C121.288 3.0816 118.178 3.16076 118.172 1.79228C118.166 0.423799 120.498 0.746117 121.757 0.757426C123.682 0.774391 125.478 0.768735 127.465 0.225867C128.384 -0.0286022 129.279 -0.271735 129.612 0.712212C129.927 1.63396 129.026 1.86014 128.286 2.18813C126.595 2.72534 124.472 3.03637 122.152 3.0816H122.146Z" fill="#009E97"/>
                    <path d="M40.9617 0.350479C41.8873 0.31655 42.9795 0.26564 42.998 1.46447C43.0165 2.41449 42.097 2.63506 41.2455 2.65768C38.3885 2.73684 35.5253 2.78207 32.6683 2.82166C32.008 2.83296 31.4033 2.64636 31.3477 1.94516C31.286 1.1252 31.9524 0.751985 32.6929 0.678471C34.2109 0.52579 39.7337 0.395723 40.9617 0.356139V0.350479Z" fill="#009E97"/>
                    <path d="M52.2827 2.74215C50.956 2.74215 49.6293 2.76476 48.3026 2.73083C47.4202 2.70821 46.5995 2.43679 46.655 1.46981C46.6982 0.672471 47.4449 0.434986 48.2039 0.429331C51.0609 0.412367 53.9241 0.401057 56.7811 0.429331C57.5339 0.440641 58.2312 0.762969 58.2559 1.56031C58.2867 2.58384 57.4475 2.73084 56.5713 2.73649C55.1397 2.7478 53.7143 2.73649 52.2827 2.73649V2.74215Z" fill="#009E97"/>
                    <path d="M88.1764 2.4419C86.7448 2.40231 85.307 2.38532 83.8754 2.31181C83.357 2.28354 82.8572 2.06302 82.7585 1.52015C82.6351 0.841566 83.0917 0.406133 83.7335 0.31C86.7879 -0.159355 89.8486 0.106415 92.8969 0.406123C93.5572 0.468326 93.9521 0.999886 93.841 1.64454C93.7238 2.32313 93.1067 2.5267 92.4711 2.5267C91.0395 2.5267 89.6018 2.45884 88.1702 2.41926C88.1702 2.42491 88.1702 2.43059 88.1702 2.4419H88.1764Z" fill="#009E97"/>
                    <path d="M172.671 2.3413C171.758 2.3922 170.129 2.48268 168.493 2.55619C167.734 2.59012 166.981 2.47137 166.852 1.64576C166.722 0.808842 167.401 0.503489 168.166 0.418666C171.628 0.0510995 175.102 -0.0676501 178.582 0.0680668C179.415 0.101996 180.329 0.294248 180.366 1.25558C180.403 2.31869 179.428 2.31871 178.613 2.33002C176.873 2.35263 175.133 2.33568 172.671 2.33568V2.3413Z" fill="#009E97"/>
                    <path d="M206.54 3.0816C205.683 3.0816 202.573 3.16076 202.566 1.79228C202.56 0.423799 204.893 0.746117 206.152 0.757426C208.077 0.774391 209.873 0.768735 211.859 0.225867C212.779 -0.0286022 213.674 -0.271735 214.007 0.712212C214.322 1.63396 213.421 1.86014 212.68 2.18813C210.989 2.72534 208.867 3.03637 206.547 3.0816H206.54Z" fill="#009E97"/>
                    <path d="M143.747 0.350479C144.672 0.31655 145.765 0.26564 145.783 1.46447C145.802 2.41449 144.882 2.63506 144.031 2.65768C141.174 2.73684 138.31 2.78207 135.453 2.82166C134.793 2.83296 134.188 2.64636 134.133 1.94516C134.071 1.1252 134.738 0.751985 135.478 0.678471C136.996 0.52579 142.519 0.395723 143.747 0.356139V0.350479Z" fill="#009E97"/>
                    <path d="M155.068 2.74215C153.741 2.74215 152.414 2.76476 151.088 2.73083C150.205 2.70821 149.385 2.43679 149.44 1.46981C149.483 0.672471 150.23 0.434986 150.989 0.429331C153.846 0.412367 156.709 0.401057 159.566 0.429331C160.319 0.440641 161.016 0.762969 161.041 1.56031C161.072 2.58384 160.233 2.73084 159.356 2.73649C157.925 2.7478 156.499 2.73649 155.068 2.73649V2.74215Z" fill="#009E97"/>
                    <path d="M190.981 2.4419C189.549 2.40231 188.112 2.38532 186.68 2.31181C186.162 2.28354 185.662 2.06302 185.563 1.52015C185.44 0.841566 185.896 0.406133 186.538 0.31C189.593 -0.159355 192.653 0.106415 195.702 0.406123C196.362 0.468326 196.757 0.999886 196.646 1.64454C196.528 2.32313 195.911 2.5267 195.276 2.5267C193.844 2.5267 192.406 2.45884 190.975 2.41926C190.975 2.42491 190.975 2.43059 190.975 2.4419H190.981Z" fill="#009E97"/>
                    <path d="M226.317 2.4419C224.885 2.40231 223.448 2.38532 222.016 2.31181C221.498 2.28354 220.998 2.06302 220.899 1.52015C220.776 0.841566 221.232 0.406133 221.874 0.31C224.929 -0.159355 227.989 0.106415 231.038 0.406123C231.698 0.468326 232.093 0.999886 231.982 1.64454C231.864 2.32313 231.247 2.5267 230.612 2.5267C229.18 2.5267 227.742 2.45884 226.311 2.41926C226.311 2.42491 226.311 2.43059 226.311 2.4419H226.317Z" fill="#009E97"/>
                </svg>
                </span>
            </div>

        <?php endif; ?>
    </div>

    <?php if ( ! $logged_in ) : ?>
        <div id="anyweb-checkout-login-panel" hidden></div>
    <?php endif; ?>

    <?php
    if ( function_exists( 'anyweb_checkout_loyalty_notice' ) ) {
        echo anyweb_checkout_loyalty_notice();
    }
}

function anyweb_checkout_loyalty_notice() {
    $discounts = array();

    $cart = WC()->cart;

    if ( ! $cart ) {
        return;
    }

//    echo do_shortcode('[saphali_user_discount]');

    $loyalty_discount = (float) $cart->get_coupon_discount_amount(
        'loyalnist',
        ! $cart->display_prices_including_tax()
    );

    if ( round( $loyalty_discount, wc_get_price_decimals() ) <= 0 ) {
        return;
    }

    if (
        is_user_logged_in()
        && function_exists('WC')
        && WC()->session
    ) {
        $stored = WC()->session->get('discount_saphali_shop', array());

        foreach ((array) $stored as $value) {
            if (!is_scalar($value)) {
                continue;
            }

            $value = trim((string) $value);

            if ($value === '') {
                continue;
            }

            if (strpos($value, '%') !== false) {
                $discounts[] = esc_html($value);
            } elseif (is_numeric($value) && (float) $value > 0) {
                $discounts[] = wc_price((float) $value);
            }
        }

        $discounts = array_unique($stored);
    }

    ob_start();

    ?>
    <div class="anyweb-loyalty-notice"
         <?php if (empty($discounts)) : ?>hidden<?php endif; ?>>

        <?php if (!empty($discounts)) : ?>
            Застосована ваша накопичувальна <strong>знижка
            <?php echo wp_kses_post(implode(', ', $discounts)); ?>
            </strong>
            за системою лояльності!
        <?php endif; ?>

    </div>
    <?php

    return ob_get_clean();
}

add_filter(
    'woocommerce_update_order_review_fragments',
    function ( $fragments ) {
        $fragments['.anyweb-loyalty-notice'] =
            anyweb_checkout_loyalty_notice();

        return $fragments;
    }
);

add_filter( 'woocommerce_checkout_fields', function ( $fields ) {
    $fields['account']['account_password'] = array_replace(
        $fields['account']['account_password'] ?? array(),
        array(
            'type'         => 'password',
            'label'        => 'Вигадайте пароль',
            'placeholder'  => 'Введіть ваш пароль',
            'autocomplete' => 'new-password',
            'required'     => true,
            'priority'     => 10,
        )
    );

    $privacy_url = get_privacy_policy_url();

    $fields['account']['account_privacy_consent'] = array(
        'type'     => 'checkbox',
        'label'    => $privacy_url
            ? sprintf(
                'Реєструючись, я даю згоду на обробку персональних даних згідно з <a href="%s">Політикою конфіденційності</a> цього сайту.',
                esc_url( $privacy_url )
            )
            : 'Реєструючись, я даю згоду на обробку персональних даних.',
        'required' => true,
        'priority' => 20,
    );

    return $fields;
}, 110 );

function anyweb_render_checkout_coupon() {
    $code    = anyweb_get_applied_promo_code( WC()->cart );
    $applied = '' !== $code;
    ?>
    <div id="anyweb-checkout-coupon-panel">
        <?php if ( wc_coupons_enabled() ) : ?>
            <div class="cart-promo-form anyweb-checkout-coupon<?php echo $applied ? ' is-applied' : ''; ?>">
                <label class="screen-reader-text" for="anyweb-checkout-coupon-code">
                    <?php esc_html_e( 'Промокод', 'any-web-theme' ); ?>
                </label>

                <input
                        type="text"
                        id="anyweb-checkout-coupon-code"
                        class="cart-promo-form__input"
                        placeholder="<?php esc_attr_e( 'Маєте промо-код?', 'any-web-theme' ); ?>"
                        value="<?php echo esc_attr( $code ); ?>"
                        <?php if ( $applied ) : ?>readonly<?php endif; ?>
                >

                <?php if ( $applied ) : ?>
                    <a
                            href="#"
                            class="cart-promo-form__remove woocommerce-remove-coupon"
                            data-coupon="<?php echo esc_attr( $code ); ?>"
                            aria-label="<?php esc_attr_e( 'Видалити промокод', 'any-web-theme' ); ?>"
                    >
                        <svg width="24" height="24" viewBox="0 0 24 24"
                             fill="none" aria-hidden="true">
                            <path d="M7 7L17 17M7 17L17 7"
                                  stroke="currentColor" stroke-width="2"
                                  stroke-linecap="round"/>
                        </svg>
                    </a>

                    <span class="cart-promo-form__button is-applied">
                        <?php esc_html_e( 'Застосовано', 'any-web-theme' ); ?>
                    </span>
                <?php else : ?>
                    <button
                            type="button"
                            class="cart-promo-form__button anyweb-checkout-coupon__apply"
                    >
                        <?php esc_html_e( 'Застосувати', 'any-web-theme' ); ?>
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

add_filter( 'woocommerce_update_order_review_fragments', function ( $fragments ) {
    ob_start();
    anyweb_render_checkout_coupon();
    $fragments['#anyweb-checkout-coupon-panel'] = ob_get_clean();

    return $fragments;
} );

/**
 * Возвращает первый применённый промокод покупателя.
 * Служебные купоны Saphali остаются в корзине и участвуют в расчёте.
 */
function anyweb_get_applied_promo_code( $cart ) {
    if ( ! $cart ) {
        return '';
    }

    foreach ( $cart->get_coupons() as $code => $coupon ) {
        if ( 'fixed_total_shop' === $coupon->get_discount_type() ) {
            continue;
        }

        return (string) $code;
    }

    return '';
}

add_action( 'woocommerce_after_calculate_totals', function ( $cart ) {
    if ( ! current_user_can( 'manage_woocommerce' ) ) {
        return;
    }

    $coupons = array();

    $applied_coupons = $cart->get_applied_coupons();
    $discount_saphali_shop = WC()->session->get( 'discount_saphali_shop', array() );
    $discount_totals = $cart->get_coupon_discount_totals();
    $cart_coupon_types = wc_get_cart_coupon_types();
    $woocommerce_coupon_is_valid_for_cart = has_filter( 'woocommerce_coupon_is_valid_for_cart' );

    foreach ( $cart->get_coupons() as $code => $coupon ) {
        $coupons[] = array(
            'code'            => $code,
            'type'            => $coupon->get_discount_type(),
            'actual_discount' => (float) $cart->get_coupon_discount_amount(
                $code,
                ! $cart->display_prices_including_tax()
            )
        );
    }

    $data = array(
        'saphali_session'      => WC()->session
            ? WC()->session->get( 'discount_saphali_shop', array() )
            : array(),
        'coupons'              => $coupons,
        'subtotal_ex_tax'      => $cart->get_subtotal(),
        'products_net_ex_tax'  => $cart->get_cart_contents_total(),
        'coupon_discount'      => $cart->get_discount_total(),
        'coupon_discount_tax'  => $cart->get_discount_tax(),
        'fees_ex_tax'          => $cart->get_fee_total(),
        'order_total'          => $cart->get_total( 'edit' ),
    );

    wc_get_logger()->info(
        wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ),
        array( 'source' => 'anyweb-discounts' )
    );
}, PHP_INT_MAX );

add_filter(
    'woocommerce_checkout_required_field_notice',
    function ( $message, $label, $key ) {
        if ( 'account_privacy_consent' === $key ) {
            return __( 'Обов’язкове поле', 'any-web-theme' );
        }

        if ( in_array( $key, [ 'billing_first_name', 'account_password' ], true ) ) {
            return __( 'обов’язкове поле', 'any-web-theme' );
        }

        return $message;
    },
    10,
    3
);

add_filter(
    'gettext_with_context_woocommerce',
    function ( $translation, $text, $context ) {
        if ( 'Billing %s' === $text && 'checkout-validation' === $context ) {
            return '%s';
        }

        return $translation;
    },
    10,
    3
);