<?php
defined( 'ABSPATH' ) || exit;

if ( ! WC()->cart ) {
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
?>



<div class="cart_totals <?php echo $cart->needs_shipping() && $cart->show_shipping() ? 'calculated_shipping' : ''; ?>">

    <div class="js-cart-recommendations-state"
         data-total="<?php echo esc_attr( $recommendations_total ); ?>"
         hidden>
    </div>

    <?php do_action( 'woocommerce_before_cart_totals' ); ?>

    <?php if ( wc_coupons_enabled() ) : ?>

        <?php
        $applied_coupons = $cart->get_applied_coupons();

        // В поле показываем первый применённый промокод.
        $applied_code = ! empty( $applied_coupons )
            ? reset( $applied_coupons )
            : '';

        $remove_coupon_url = $applied_code
            ? add_query_arg(
                'remove_coupon',
                rawurlencode( $applied_code ),
                wc_get_cart_url()
            )
            : '';
        ?>

        <form
                class="cart-promo-form <?php echo $applied_code ? 'is-applied' : ''; ?>"
                action="<?php echo esc_url( wc_get_cart_url() ); ?>"
                method="post"
        >

            <label class="screen-reader-text" for="cart-promo-code">
                <?php esc_html_e( 'Промокод', 'any-web-theme' ); ?>
            </label>

            <input
                    type="text"
                    name="coupon_code"
                    id="cart-promo-code"
                    class="cart-promo-form__input"
                    placeholder="<?php esc_attr_e( 'Маєте промо-код?', 'any-web-theme' ); ?>"
                    value="<?php echo esc_attr( $applied_code ); ?>"
                <?php if ( $applied_code ) : ?>
                    readonly
                <?php endif; ?>
            >

            <?php if ( $applied_code ) : ?>

                <a
                        href="<?php echo esc_url( $remove_coupon_url ); ?>"
                        class="cart-promo-form__remove"
                        aria-label="<?php esc_attr_e( 'Видалити промокод', 'any-web-theme' ); ?>"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M7 7L17 17M7 17L17 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke="#989898"/>
                    </svg>
                </a>

                <span class="cart-promo-form__button is-applied">
                <?php esc_html_e( 'Застосовано', 'any-web-theme' ); ?>
            </span>

            <?php else : ?>

                <button
                        type="submit"
                        name="apply_coupon"
                        value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>"
                        class="cart-promo-form__button"
                >
                    <?php esc_html_e( 'Застосувати', 'any-web-theme' ); ?>
                </button>

            <?php endif; ?>

            <?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>

        </form>

    <?php endif; ?>

    <div class="cart-summary">

        <div class="cart-summary__row">
            <span>
                <?php
                printf(
                    esc_html(
                        _n(
                            '%s товар на суму:',
                            '%s товарів на суму:',
                            $product_count,
                            'any-web-theme'
                        )
                    ),
                    esc_html( number_format_i18n( $product_count ) )
                );
                ?>
            </span>

            <span>
                <?php echo wp_kses_post( wc_price( $regular_products_total ) ); ?>
            </span>
        </div>

        <?php if ( round( $sale_discount, wc_get_price_decimals() ) > 0 ) : ?>
            <div class="cart-summary__row cart-summary__discount">
                <span><?php esc_html_e( 'Знижка:', 'any-web-theme' ); ?></span>

                <span>
            − <?php echo wp_kses_post( wc_price( $sale_discount ) ); ?>
        </span>
            </div>
        <?php endif; ?>

        <?php if ( round( $coupon_discount, wc_get_price_decimals() ) > 0 ) : ?>
            <div class="cart-summary__row cart-summary__coupon-discount">
                <span><?php esc_html_e( 'Промо-код:', 'any-web-theme' ); ?></span>

                <span>
            − <?php echo wp_kses_post( wc_price( $coupon_discount ) ); ?>
        </span>
            </div>
        <?php endif; ?>

        <?php if ( $cart->needs_shipping() && $free_shipping_min > 0 ) : ?>

            <div class="cart-summary__row cart-summary__shipping">
        <span>
            <?php esc_html_e( 'Доставка:', 'any-web-theme' ); ?>
        </span>

                <span>
            <?php if ( $discounted_products_total >= $free_shipping_min ) : ?>

                <strong class="cart-summary__free-shipping">
                    <?php esc_html_e( 'БЕЗКОШТОВНА', 'any-web-theme' ); ?>
                </strong>

            <?php else : ?>

                <?php
                printf(
                /* %s — минимальная сумма бесплатной доставки. */
                    esc_html__( 'від %s — БЕЗКОШТОВНА', 'any-web-theme' ),
                    wp_kses_post( wc_price( $free_shipping_min ) )
                );
                ?>

            <?php endif; ?>
        </span>
            </div>

        <?php endif; ?>

        <?php do_action( 'woocommerce_cart_totals_before_order_total' ); ?>

        <div class="cart-summary__row cart-summary__total">
            <strong>
                <?php esc_html_e( 'Загальна сума:', 'any-web-theme' ); ?>
            </strong>

            <strong>
                <?php wc_cart_totals_order_total_html(); ?>
            </strong>
        </div>

        <?php do_action( 'woocommerce_cart_totals_after_order_total' ); ?>

    </div>

    <div class="wc-proceed-to-checkout cart-summary__actions">
        <?php do_action( 'woocommerce_proceed_to_checkout' ); ?>
    </div>

    <?php do_action( 'woocommerce_after_cart_totals' ); ?>

</div>