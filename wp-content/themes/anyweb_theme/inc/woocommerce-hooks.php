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

add_action( 'wp_footer', 'force_shipping_selection_script' );
function force_shipping_selection_script() {
    // Работает только на страницах корзины и оформления
    if ( is_cart() || is_checkout() ) {
        ?>
        <script type="text/javascript">
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

add_action( 'woocommerce_check_cart_items', 'prevent_checkout_without_shipping' );
add_action( 'woocommerce_checkout_process', 'prevent_checkout_without_shipping' );
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