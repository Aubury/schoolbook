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

add_action('woocommerce_review_order_before_payment', 'custom_notice_for_low_amount');
function custom_notice_for_low_amount() {
    $total = WC()->cart->total;

//    echo do_shortcode('[saphali_user_discount]');

    $str = '';

    // Если сумма заказа меньше 300 грн
    if ($total < 300) {
        $str = '<h4 style="color: #ff0000;">Оплата під час отримання недоступна для замовлень на суму менше ніж 300 грн. Будь ласка, оберіть онлайн-оплату через LiqPay.</h4>';
    }

    foreach (WC()->cart->get_cart() as $cart_item) {
        $product = $cart_item['data'];

        // Проверяем, доступен ли товар для предзаказа
        if ($product->is_on_backorder()) {
            $str = '<h4 style="color: #ff0000;">Опція “оплата під час отримання” недоступна для акції “передзамовлення”. Будь ласка, оберіть онлайн-оплату через LiqPay.</h4>';
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
                  <path d="M4 10H16" stroke-width="2" stroke="#C2C2C2"/>
                </svg>
               </button>';
    }
} );

add_action( 'woocommerce_after_quantity_input_field', function () {
    if ( is_cart() ) {
        echo '<button type="button" class="cart-qty-plus" aria-label="Збільшити кількість">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                  <path d="M4 10H10M10 10H16M10 10V4M10 10V16" stroke-width="2" stroke="#525252"/>
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