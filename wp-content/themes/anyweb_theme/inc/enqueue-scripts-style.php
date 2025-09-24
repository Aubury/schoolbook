<?php
if (!defined("ABSPATH")) {
  exit;
}

/**
 * Enqueue styles.
 */

function anyweb_styles() {
	wp_enqueue_style("bootstrap",get_bloginfo('stylesheet_directory').'/assets/css/bootstrap.min.css');
    wp_enqueue_style(
        'photoswipe-css',
        get_template_directory_uri() . '/assets/js/photoswipe/dist/photoswipe.css'
    );
	wp_enqueue_style( 'anyweb-style', get_stylesheet_uri(), array(),'1.0' );
	wp_enqueue_style( 'so-font', get_template_directory_uri() . '/assets/fonts/fonts.css', array(), null, 'all' );
	wp_enqueue_style("font-awesome",get_bloginfo('stylesheet_directory').'/assets/css/font-awesome.min.css');
	wp_enqueue_style("slick-theme",get_bloginfo('stylesheet_directory').'/assets/css/slick-theme.css');
	wp_enqueue_style("slick",get_bloginfo('stylesheet_directory').'/assets/css/slick.css');
	wp_enqueue_style("template_styles",get_bloginfo('stylesheet_directory').'/assets/css/template_styles.css');
	// wp_enqueue_style("owl_carousel_min",get_stylesheet_directory_uri() ."/assets/css/owl.carousel.min.css");
	// wp_enqueue_style("owl_theme_default_min",get_stylesheet_directory_uri() ."//assets/css/owl.theme.default.min.css");
}
add_action( 'wp_enqueue_scripts', 'anyweb_styles' );

/**
 * Enqueue scripts
 */

function anyweb_scripts() {
	// wp_enqueue_script( 'jqur', '//code.jquery.com/jquery-3.7.1.min.js', null, true );
	// wp_enqueue_script( 'migrate', '//code.jquery.com/jquery-migrate-3.4.1.min.js', null, true );



	wp_enqueue_script( 'jqur', '//code.jquery.com/jquery-3.6.0.min.js', null, true );
	wp_enqueue_script( 'migrate', '//code.jquery.com/jquery-migrate-3.3.2.min.js', null, true );
    wp_enqueue_script("so-bootstrap",get_template_directory_uri() . "/assets/js/bootstrap.bundle.min.js",array('jqur', 'migrate'),_S_VERSION, true);

    wp_enqueue_script(
        'photoswipe-core',
        get_template_directory_uri() . '/assets/js/photoswipe/dist/umd/photoswipe.umd.min.js',
        array(),
        null,
        true
    );

    wp_enqueue_script(
        'photoswipe-lightbox',
        get_template_directory_uri() . '/assets/js/photoswipe/dist/umd/photoswipe-lightbox.umd.min.js',
        array('photoswipe-core'),
        null,
        true
    );

    wp_enqueue_script("so-formValidate",get_template_directory_uri() . "/assets/js/formValidate/validate.js",array('jqur', 'migrate'),_S_VERSION, true);
    wp_enqueue_script("so-app",get_template_directory_uri() . "/assets/js/app.js",array('jqur', 'migrate'),_S_VERSION, true);
    wp_enqueue_script("so-navigation",get_template_directory_uri() . "/assets/js/navigation.js",array('jqur', 'migrate'),_S_VERSION, true);
    // wp_enqueue_script("so-cutomizer",get_template_directory_uri() . "/assets/js/customizer.js",array('jqur', 'migrate'),_S_VERSION, true);
    wp_enqueue_script( 'slickminjs', get_stylesheet_directory_uri() . '/assets/js/slick.min.js', array('jqur', 'migrate'), null, true );
    wp_enqueue_script( 'slickjs', get_stylesheet_directory_uri() . '/assets/js/slick.js', array('jqur', 'migrate'), null, true );
    wp_enqueue_script( 'mCustomScrollbar', get_stylesheet_directory_uri() . '/assets/js/mCustomScrollbar/jquery.mCustomScrollbar.js', array('jqur', 'migrate'), null, true );
	wp_enqueue_script( 'formstyler', get_stylesheet_directory_uri() . '/assets/js/formstyler/jquery.formstyler.js', array('jqur', 'migrate', 'mCustomScrollbar'), null, true );

	$data = [
		'directory_uri' => get_stylesheet_directory_uri()
	];
	
	wp_add_inline_script( 'so-app', 'const myScriptData = ' . wp_json_encode( $data ), 'before' );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
	wp_localize_script('so-app', 'soJsLet',  array(
		'ajaxurl' => admin_url('admin-ajax.php'),
		'nonce' => wp_create_nonce('so_creator_nonce')
	));

    $nonce = wp_create_nonce('add_more-nonce');

    wp_localize_script('so-app',
        'add_more_object',
        array(
            'url' => admin_url( 'admin-ajax.php' ),
            'nonce' => $nonce
        )
    );
	
	 wp_localize_script('aws-script', 'aws_vars', array(
        'sale'       => __('Розпродаж!', 'advanced-woo-search'),
         'showmore'   => __('Переглянути всі результати', 'advanced-woo-search'),
         'noresults'  => __('Нічого не знайдено', 'advanced-woo-search'),
        ));
}
add_action( 'wp_enqueue_scripts', 'anyweb_scripts' );

/**
* Enqueue admin panel styles.
*/
function anyweb_admin_styles(){
	wp_enqueue_style("style-admin",get_bloginfo('stylesheet_directory')."/assets/css/adminstyle.css");
	}

	add_action('admin_head', 'anyweb_admin_styles');



// Подключение скрипта admin-custom-script.js
add_action('admin_enqueue_scripts', 'custom_admin_script');

function custom_admin_script() {
    wp_enqueue_script('admin-custom-script', get_stylesheet_directory_uri() . '/assets/js/admin-custom-script.js', array('jquery'), '1.0', true);
}

/*****
 * передплата 200 грн
 *
 ***/

// Отключаем "Оплату при получении" для заказов менее 300 грн
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

// Добавляем некликабельную приписку для заказов менее 300 грн
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

require_once(WP_PLUGIN_DIR . '/mrkv-liqpay-extended/includes/class-wc-gateway-morkva-liqpay.php');
require_once(WP_PLUGIN_DIR . '/mrkv-liqpay-extended/includes/classes/MorkvaLiqPay.php');
// Обработка предоплаты через LiqPay при выборе "Оплата при отриманні"
add_action('woocommerce_checkout_order_processed', 'redirect_to_liqpay_for_prepayment');

function redirect_to_liqpay_for_prepayment($order_id) {
    $order = wc_get_order($order_id);
    $total = $order->get_total();

    if ($total > 300 && isset($_POST['payment_method']) && $_POST['payment_method'] === 'cod') {
        $prepayment = 200;

        $gateway_morkva_liqpay = new WC_Gateway_Morkva_Liqpay();

        # Check test mode
        if($gateway_morkva_liqpay->get_option( 'test_enabled_admin' ) == 'yes' && ( current_user_can('editor') || current_user_can('administrator') ))

        // Store Order ID in session, so it can be re-used after payment failure.
        WC()->session->set( 'order_awaiting_payment', $order_id );
        WC()->session->save_data();

        // Process Payment.
        $result = process_payment( $order_id );

        // Redirect to success/confirmation/payment page.
        if ( isset( $result['result'] ) && 'success' === $result['result'] ) {
            $result['order_id'] = $order_id;

            $result = apply_filters( 'woocommerce_payment_successful_result', $result, $order_id );

            if ( ! wp_doing_ajax() ) {
                // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
                wp_redirect( $result['redirect'] );
                exit;
            }

            // Using wp_send_json will gracefully handle any problem encoding data.
            wp_send_json( $result );
        }
    }
}


/**
 * Process the payment and return the result.
 *
 * @param int $order_id Order ID
 * @return array
 */
function process_payment($order_id) {
    $prepayment = 200;
    # Get order data by id
    $order = wc_get_order($order_id);
    $gateway_morkva_liqpay = new WC_Gateway_Morkva_Liqpay();

    # Check order total
    if ($order->get_total() > 0)
    {
        # Send email notification
        pending_new_order_notification($order->get_id());
    }

    # Remove cart data
    WC()->cart->empty_cart();

    # Check test mode
    if ($gateway_morkva_liqpay->get_option( 'test_enabled_admin' ) == 'yes'
        && ( current_user_can('editor') || current_user_can('administrator') )
    ) {
        # Use test keys
        $morkva_liqPay = new MorkvaLiqPay($gateway_morkva_liqpay->get_option('test_public_key'),
            $gateway_morkva_liqpay->get_option('test_private_key'));

    } elseif ($gateway_morkva_liqpay->get_option( 'test_enabled' ) == 'yes'
        && $gateway_morkva_liqpay->get_option( 'test_enabled_admin' ) != 'yes') {
        # Use test keys
        $morkva_liqPay = new MorkvaLiqPay($gateway_morkva_liqpay->get_option('test_public_key'),
            $gateway_morkva_liqpay->get_option('test_private_key'));

    } else {
        # Use main keys
        $morkva_liqPay = new MorkvaLiqPay($gateway_morkva_liqpay->get_option('public_key'),
            $gateway_morkva_liqpay->get_option('private_key'));
    }

    # Create argument of query
    $arrayData = array(
        'version' => '3',
        'action' => 'pay',
        'amount' => $prepayment,
        'currency' => $order->get_currency(),
        'description' => __('Передоплата замовлення № ', 'mrkv-liqpay-extended') . $order_id,
        'order_id' => $order->get_id(),
        'result_url' => $gateway_morkva_liqpay->get_return_url($order),
        'language' => 'uk',
        'server_url' => WC()->api_request_url( 'WC_Gateway_Morkva_Liqpay' )
    );

    # Create result link
    $url = $morkva_liqPay->cnb_link($arrayData);

    # Return result
    return array(
        'result' => 'success',
        'redirect' => $url,
    );
}

/**
 * New order notification function
 *
 * @param $order_id Order id
 */
function pending_new_order_notification($order_id) {
    # Get order data
    $order = wc_get_order($order_id);

    # Only for "pending" order status
    if (!$order->has_status('pending')) return;

    # Get an instance of the WC_Email_New_Order object
    $wc_email = WC()->mailer()->get_emails()['WC_Email_New_Order'];

    # Create email data
    $wc_email->settings['subject'] = '{site_title} - ' . __('Нове замовлення', 'mrkv-liqpay-extended') . ' ({order_number}) - {order_date}';
    $wc_email->settings['heading'] = __('Нове замовлення', 'mrkv-liqpay-extended');

    # Send email
    $wc_email->trigger($order_id);
}

// Добавляем информацию о предоплате в метаданные заказа
add_action('woocommerce_checkout_update_order_meta', 'add_prepayment_info_to_order_meta');
function add_prepayment_info_to_order_meta($order_id) {
    // Предположим, что у вас есть переменная с суммой предоплаты
    $prepayment = 200; // Например, 200 грн
    $payment_status = 'Не оплачено'; // Начальный статус
    $order = wc_get_order($order_id);
    $total = $order->get_total();

    if ($total > 300 && isset($_POST['payment_method']) && $_POST['payment_method'] === 'cod') {

        update_post_meta($order_id, '_prepayment_amount', $prepayment);
        update_post_meta($order_id, '_prepayment_status', $payment_status);

    }
}


// Добавляем информацию о предоплате к деталям заказа в админке
add_action('woocommerce_admin_order_data_after_order_details', 'display_prepayment_info_in_admin_order');
function display_prepayment_info_in_admin_order($order) {
    // Получаем мета-данные предоплаты
    $prepayment = get_post_meta($order->get_id(), '_prepayment_amount', true);
    $payment_status = get_post_meta($order->get_id(), '_prepayment_status', true);
    $total = $order->get_total();
    $payment_method = $order->get_payment_method();
    $payment_status === 'Сплачено' ? $color = 'green' : $color = 'red';

    if ($total > 300 && $payment_method === 'cod') {
        echo '<p class="mt-2 form-field form-field-wide" style="color: ' . $color . '"><strong>Передплата: </strong>' . wc_price($prepayment) . '</p>';
        echo '<p class="form-field form-field-wide" style="color: ' . $color . '"><strong>Статус передоплати: </strong>' . esc_html($payment_status) . '</p>';
    }
}

// Обновляем Статус передоплати после успешной оплаты
add_action('woocommerce_order_status_processing ', 'update_prepayment_status');
function update_prepayment_status($order_id) {
    $order = wc_get_order($order_id);
    $total = $order->get_total();
    $payment_method = $order->get_payment_method();

    if ($total > 300 && $payment_method === 'cod') {
        update_post_meta($order_id, '_prepayment_status', 'Сплачено');
    }
}

//// Отображаем информацию о предоплате для пользователя на странице заказа
//add_action('woocommerce_order_item_meta_end', 'display_prepayment_info_to_customer', 10, 4);
//function display_prepayment_info_to_customer($item_id, $item, $order, $plain_text) {
//    $total = $order->get_total();
//    $payment_method = $order->get_payment_method();
//
//    if ($total > 300 && $payment_method === 'cod') {
//        $prepayment = get_post_meta($order->get_id(), '_prepayment_amount', true);
//        $payment_status = get_post_meta($order->get_id(), '_prepayment_status', true);
//        ($payment_status === 'Сплачено' || $payment_status === 'Оплачено') ? $color = 'green' : $color = 'red';
//
//        echo '<p class="mt-2 form-field form-field-wide" style="color: ' . $color . '"><strong>Передплата: </strong>' . wc_price($prepayment) . '</p>';
//        echo '<p class="form-field form-field-wide" style="color: ' . $color . '"><strong>Статус передоплати: </strong>' . esc_html($payment_status) . '</p>';
//    }
//}

add_action('woocommerce_order_details_after_order_table', 'display_prepayment_info_after_order_table', 10, 1);
function display_prepayment_info_after_order_table($order) {
    $total = $order->get_total();
    $payment_method = $order->get_payment_method();

    if ($total > 300 && $payment_method === 'cod') {
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

    if ($total > 300 && isset($_POST['payment_method']) && $_POST['payment_method'] === 'cod') {
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

/** END передплата 200 грн */

/** Предзаказ */
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


/** END Предзаказ */

add_action('woocommerce_thankyou', 'show_modal_on_success_payment', 10, 1);
function show_modal_on_success_payment( $order_id ) {
    // Получаем объект заказа
    $order = wc_get_order( $order_id );

    $payment_status = $order->get_status();

    $custom_preorder_countdown = null;
    $flag = false;
    // Проходим по товарам в заказе
    foreach( $order->get_items() as $item_id => $item ) {
        $product_id = $item->get_product_id(); // Получаем ID товара
        $product = wc_get_product( $product_id );

        if ($product->is_on_backorder()) {
            $custom_preorder_countdown = get_post_meta($product_id, '_custom_preorder_countdown', true);
             $flag = true;
            break;
        }
    }

    $order_status = $order->get_status(); //processing

    if ( $flag && $order_status == 'processing') {
        // Выводим JavaScript для модального окна
        ?>
        <style>
            .modal.show {
                background-color: rgba(0,0,0,0.7);
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .modal {
                display: none;
            }

            .modal .modal-content {
                max-width: 90%;
                width: 560px;
            }
        </style>

        <!-- Само модальное окно -->
        <div class="modal fade show" tabindex="-1" aria-hidden="true" id="custom-modal"
            <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h3 class="modal-title text-center" id="preorderModalLabel">
                        ДЯКУЄМО, ВАШЕ ЗАМОВЛЕННЯ ЗАПЛАНОВАНО ДО ВІДПРАВКИ НА<br><span class="fw-bolder"><?php echo date('d.m.Y', strtotime($custom_preorder_countdown)) ?></span>
                    </h3>
                </div>
            </div>
            </div>
        </div>

        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('.btn-close').on('click', function (e) {
                    e.preventDefault();
                    $('#custom-modal').removeClass('show');
                })
            });
        </script>
        <?php

    }
}

/** WOO API REST */

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

/** END WOO API REST */