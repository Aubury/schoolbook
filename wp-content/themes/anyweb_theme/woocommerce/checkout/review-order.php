<?php
/**
 * Review order table
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/review-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 5.2.0
 */

defined( 'ABSPATH' ) || exit;

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
<table class="shop_table woocommerce-checkout-review-order-table">
	<tbody>
		<?php
		do_action( 'woocommerce_review_order_before_cart_contents' );
        do_action( 'woocommerce_review_order_after_cart_contents' );
        ?>

	</tbody>
	<tfoot>

    <tr class="cart-summary-table-row cart-table-row-checkout">
        <td colspan="2">
            <div class="cart-summary">
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

                    <?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>

                        <?php
                        $is_saphali_discount = in_array(
                            $coupon->get_discount_type(),
                            [ 'fixed_total_shop', 'fixed_total_cart' ],
                            true
                        );

                        $amount = (float) $cart->get_coupon_discount_amount(
                            $code,
                            ! $cart->display_prices_including_tax()
                        );

                        if ( $is_saphali_discount && round( $amount, wc_get_price_decimals() ) <= 0 ) {
                            continue;
                        }

                        ?>
                        <div class="cart-summary__row cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
                            <span><?php wc_cart_totals_coupon_label( $coupon ); ?></span>
                            <span><?php echo wp_kses_post( wc_price( -$amount ) ); ?></span>
                        </div>
                    <?php endforeach; ?>

                    <?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
                        <div class="cart-summary__row fee">
                            <span><?php echo esc_html( $fee->name ); ?></span>
                            <span><?php wc_cart_totals_fee_html( $fee ); ?></span>
                        </div>
                    <?php endforeach; ?>

                    <?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
                        <?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
                            <?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>
                                <div class="cart-summary__row tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
                                    <span><?php echo esc_html( $tax->label ); ?></span>
                                    <span><?php echo wp_kses_post( $tax->formatted_amount ); ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <div class="cart-summary__row tax-total">
                                <span><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></span>
                                <span><?php wc_cart_totals_taxes_total_html(); ?></span>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                </div>
            </div>
        </td>
    </tr>

		<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>

		<tr class="order-total">
			<th><?php esc_html_e( 'До оплати:', 'any-web-theme' ); ?></th>
			<td><?php wc_cart_totals_order_total_html(); ?></td>
		</tr>

		<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>

	</tfoot>
</table>
