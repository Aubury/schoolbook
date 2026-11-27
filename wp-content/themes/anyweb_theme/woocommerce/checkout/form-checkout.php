<?php
/**
 * Checkout Form
 *
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_checkout_form', $checkout );

if (
    ! $checkout->is_registration_enabled()
    && $checkout->is_registration_required()
    && ! is_user_logged_in()
) {
    echo esc_html(
        apply_filters(
            'woocommerce_checkout_must_be_logged_in_message',
            __( 'You must be logged in to checkout.', 'woocommerce' )
        )
    );

    return;
}

$cart = WC()->cart;

// Количество всех единиц товара.
$product_count = $cart->get_cart_contents_count();

// Общая стоимость товаров по обычным ценам.
$regular_products_total = 0;

foreach ( $cart->get_cart() as $cart_item ) {
    $product = $cart_item['data'] ?? null;

    if ( ! $product || ! $product->exists() ) {
        continue;
    }

    $regular_price = $product->get_regular_price();

    // Если обычная цена не указана, используем текущую.
    if ( '' === $regular_price ) {
        $regular_price = $product->get_price();
    }

    $regular_products_total += wc_get_price_to_display(
        $product,
        [
            'price'           => (float) $regular_price,
            'qty'             => (float) $cart_item['quantity'],
            'display_context' => 'cart',
        ]
    );
}

// Стоимость товаров после акций и купонов.
$discounted_products_total = (float) $cart->get_cart_contents_total();

if ( $cart->display_prices_including_tax() ) {
    $discounted_products_total += (float) $cart->get_cart_contents_tax();
}

// Скидка по применённым купонам.
$coupon_discount = (float) $cart->get_discount_total();

if ( $cart->display_prices_including_tax() ) {
    $coupon_discount += (float) $cart->get_discount_tax();
}

// Скидка за счёт акционных цен товаров.
$sale_discount = max(
    0,
    $regular_products_total
    - $discounted_products_total
    - $coupon_discount
);

// Порог, заданный в Carbon Fields.
$free_shipping_min = function_exists( 'carbon_get_theme_option' )
    ? (float) carbon_get_theme_option( 'min_price_delivery' )
    : 0;
?>
<?php
$recommendations_total = (float) WC()->cart->get_cart_contents_total()
    + (float) WC()->cart->get_cart_contents_tax();

$min_price_delivery = carbon_get_theme_option( 'min_price_delivery' );

$products_total = (float) WC()->cart->get_cart_contents_total()
    + (float) WC()->cart->get_cart_contents_tax();
?>


    <form
            name="checkout"
            method="post"
            class="checkout woocommerce-checkout anyweb-checkout"
            action="<?php echo esc_url( wc_get_checkout_url() ); ?>"
            enctype="multipart/form-data"
            aria-label="<?php esc_attr_e( 'Checkout', 'woocommerce' ); ?>"
    >
        <div class="anyweb-checkout__layout">

            <div class="anyweb-checkout__main">

                <?php if ( $checkout->get_checkout_fields() ) : ?>

                    <?php
                    do_action( 'woocommerce_checkout_before_customer_details' );
                    ?>

                    <div id="customer_details"
                         class="anyweb-checkout__customer">

                        <div class="anyweb-checkout__billing">
                            <?php
                            do_action( 'woocommerce_checkout_billing' );
                            ?>
                        </div>

                    </div>

                    <?php
                    do_action( 'woocommerce_checkout_after_customer_details' );
                    ?>

                <?php endif; ?>

                <div class="anyweb-checkout__shipping">
                    <?php
                    if ( ! empty( $min_price_delivery && $products_total < $min_price_delivery) ): ?>
                        <div id="cart-free-shipping-message"
                             data-url="<?php echo esc_url(
                                 WC_AJAX::get_endpoint( 'anyweb_free_shipping_progress' )
                             ); ?>"
                        >
                            <?php anyweb_free_shipping_progress(); ?>
                        </div>
                    <?php

                    endif;

                    ?>

                    <?php
                    do_action( 'woocommerce_checkout_shipping' );
                    ?>

                    <?php
                    do_action( 'woocommerce_checkout_after_customer_details' );
                    ?>

                    <?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>

                        <?php do_action( 'woocommerce_review_order_before_shipping' ); ?>

                        <div class="woocommerce_checkout_shipping__wrap">
                            <h4>Спосіб доставки</h4>
                            <?php wc_cart_totals_shipping_html(); ?>
                        </div>

                        <?php do_action( 'woocommerce_review_order_after_shipping' ); ?>

                    <?php endif; ?>
                </div>

                <section id="anyweb-checkout-payment"
                         class="anyweb-checkout__section">
                    <h4>Оплата <span id="anyweb-payment-error" class="anyweb-payment-error" role="alert" hidden>не обрана, будь ласка, виберіть опцію</span></h4>

                    <?php do_action( 'anyweb_checkout_after_payment_methods' ); ?>

                    <div id="anyweb-checkout-payment-methods"></div>

                </section>

            </div>

            <aside class="anyweb-checkout__sidebar">

                <?php
                do_action( 'woocommerce_checkout_before_order_review_heading' );
                ?>



                <?php
                do_action( 'woocommerce_checkout_before_order_review' );
                ?>

                <div id="order_review"
                     class="woocommerce-checkout-review-order">
                    <?php
                    anyweb_render_checkout_coupon();
                    do_action( 'woocommerce_checkout_order_review' );
                    ?>
                </div>

                <?php
                do_action( 'woocommerce_checkout_after_order_review' );
                ?>

            </aside>

        </div>
    </form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>