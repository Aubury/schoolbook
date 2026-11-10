<?php
/**
 * Cart Page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.8.0
 */

defined( 'ABSPATH' ) || exit;

// Recommendations are rendered below the products in this override.
remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display', 10 );

do_action( 'woocommerce_before_cart' );

$min_price_delivery = carbon_get_theme_option( 'min_price_delivery' );

$products_total = (float) WC()->cart->get_cart_contents_total()
    + (float) WC()->cart->get_cart_contents_tax();

//$remaining = max( 0, $min_price_delivery - $products_total );
?>

<div class="anyweb-cart-layout">
    <div class="anyweb-cart-main">

        <form class="woocommerce-cart-form anyweb-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
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

            <div class="anyweb-form-wrap">

            <?php do_action( 'woocommerce_before_cart_table' ); ?>

            <table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">

                <tbody>
                <?php do_action( 'woocommerce_before_cart_contents' ); ?>

                <?php
                foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                    $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                    $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

                    /**
                     * Filter whether this cart item is visible in the cart.
                     *
                     * @since 2.1.0
                     * @param bool   $visible     Whether the cart item is visible. Default true.
                     * @param array  $cart_item     The cart item data.
                     * @param string $cart_item_key The cart item key.
                     */
                    $visible = apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key );

                    if ( $_product instanceof WC_Product && $_product->exists() && $cart_item['quantity'] > 0 && $visible ) {
                        /**
                         * Filter the product name.
                         *
                         * @since 2.1.0
                         * @param string $product_name Name of the product in the cart.
                         * @param array $cart_item The product in the cart.
                         * @param string $cart_item_key Key for the product in the cart.
                         */
                        $product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
                        $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
                        ?>
                        <tr class="woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">

                            <td class="product-remove">
                                <?php
                                echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                    'woocommerce_cart_item_remove_link',
                                    sprintf(
                                        '<a role="button" href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 17 17" fill="none">
                                                      <path d="M2.25 3.75H3.75M2.25 3.75L3.435 14.415C3.47562 14.782 3.6502 15.1212 3.92531 15.3675C4.20042 15.6138 4.55673 15.75 4.926 15.75H11.574C11.9433 15.75 12.2996 15.6138 12.5747 15.3675C12.8498 15.1212 13.0244 14.782 13.065 14.415L14.25 3.75M2.25 3.75H0.75M3.75 3.75H12.75M3.75 3.75L4.75875 1.61025C4.88006 1.35298 5.07203 1.1355 5.31225 0.983183C5.55248 0.830869 5.83106 0.749997 6.1155 0.75H10.3845C10.6691 0.749854 10.9478 0.83066 11.1882 0.982982C11.4286 1.13531 11.6206 1.35286 11.742 1.61025L12.75 3.75M12.75 3.75H14.25M14.25 3.75H15.75M6.75 7.5V11.25M9.75 7.5V11.25" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" stroke="#989898"/>
                                                    </svg>
                                                    </a>',
                                        esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
                                        /* translators: %s is the product name */
                                        esc_attr( sprintf( __( 'Remove %s from cart', 'woocommerce' ), wp_strip_all_tags( $product_name ) ) ),
                                        esc_attr( $product_id ),
                                        esc_attr( $_product->get_sku() )
                                    ),
                                    $cart_item_key
                                );
                                ?>
                            </td>

                            <td class="product-thumbnail">
                                <?php
                                /**
                                 * Filter the product thumbnail displayed in the WooCommerce cart.
                                 *
                                 * This filter allows developers to customize the HTML output of the product
                                 * thumbnail. It passes the product image along with cart item data
                                 * for potential modifications before being displayed in the cart.
                                 *
                                 * @param string $thumbnail     The HTML for the product image.
                                 * @param array  $cart_item     The cart item data.
                                 * @param string $cart_item_key Unique key for the cart item.
                                 *
                                 * @since 2.1.0
                                 */
                                $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );

                                $is_sale = '';
                                if ($_product->is_on_sale()) {
                                    $regular_price = $_product->get_regular_price();
                                    $sale_price = $_product->get_sale_price();
                                    $discount_percentage = round((($regular_price - $sale_price) / $regular_price) * 100);
                                    $is_sale = '<span class="sale_book">-' . $discount_percentage . '<span class="percent">%</span></span>';
                                }

                                echo $is_sale;

                                if ( ! $product_permalink ) {
                                    echo $thumbnail; // PHPCS: XSS ok.
                                } else {
                                    printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail ); // PHPCS: XSS ok.
                                }
                                ?>
                            </td>

                            <td scope="row" role="rowheader" class="product-name" data-title="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>">
                               <div class="anyweb-table-column">
                                <?php
                                if ( ! $product_permalink ) {
                                    echo wp_kses_post( $product_name . '&nbsp;' );
                                } else {
                                    /**
                                     * This filter is documented above.
                                     *
                                     * @since 2.1.0
                                     */
                                    echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s" class="anyweb-table-product-name">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key ) );
                                }

                                echo '<div class="anyweb-cart-author-block">';
                                $author = $_product->get_attribute( 'pa_avtori' );
                                if ( ! $author && $_product->is_type( 'variation' ) ) {
                                    $parent = wc_get_product( $_product->get_parent_id() );
                                    $author = $parent ? $parent->get_attribute( 'pa_avtori' ) : '';
                                }

                                if ( $author ) {
                                    echo '<div class="anyweb-cart-author">' . esc_html( $author ) . '</div>';
                                }

                                $age = implode(', ', wc_get_product_terms($product_id, 'pa_vik', ['fields' => 'names']));

                                if ( $author ) {
                                    echo '<div class="anyweb-cart-author">' . esc_html( $age ) . '</div>';
                                }

                                echo '</div>';

                                do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );

                                // Meta data.
                                echo wc_get_formatted_cart_item_data( $cart_item ); // PHPCS: XSS ok.

                                // Backorder notification.
                                if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
                                    echo wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>', $product_id ) );
                                }
                                ?>
                               </div>
                            </td>



                            <td class="product-quantity" data-title="<?php esc_attr_e( 'Quantity', 'woocommerce' ); ?>">
                                <?php
                                if ( $_product->is_sold_individually() ) {
                                    $min_quantity = 1;
                                    $max_quantity = 1;
                                } else {
                                    $min_quantity = 0;
                                    $max_quantity = $_product->get_max_purchase_quantity();
                                }

                                $product_quantity = woocommerce_quantity_input(
                                    array(
                                        'input_name'   => "cart[{$cart_item_key}][qty]",
                                        'input_value'  => $cart_item['quantity'],
                                        'max_value'    => $max_quantity,
                                        'min_value'    => $min_quantity,
                                        'product_name' => $product_name,
                                    ),
                                    $_product,
                                    false
                                );

                                echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ); // PHPCS: XSS ok.
                                ?>
                            </td>

                            <td class="product-subtotal" data-title="<?php esc_attr_e( 'Subtotal', 'woocommerce' ); ?>">
                                <?php
                                $quantity = (float) $cart_item['quantity'];

                                // Обычная цена товара или выбранной вариации.
                                $regular_price = $_product->get_regular_price();

                                if ( '' === $regular_price ) {
                                    $regular_price = $_product->get_price();
                                }

                                // Сумма по обычной цене с учётом настроек отображения налогов.
                                $regular_total = wc_get_price_to_display(
                                    $_product,
                                    [
                                        'price'           => (float) $regular_price,
                                        'qty'             => $quantity,
                                        'display_context' => 'cart',
                                    ]
                                );

                                // Фактическая сумма строки после скидок и купонов.
                                $current_total = (float) $cart_item['line_total'];

                                if ( WC()->cart->display_prices_including_tax() ) {
                                    $current_total += (float) $cart_item['line_tax'];
                                }

                                $decimals = wc_get_price_decimals();

                                $has_discount = round( $regular_total, $decimals )
                                    > round( $current_total, $decimals );
                                ?>

                                <div class="cart-item-prices">
                                    <?php if ( $has_discount ) : ?>
                                        <del class="cart-item-prices__old">
                                            <?php echo wp_kses_post( wc_price( $regular_total ) ); ?>
                                        </del>
                                    <?php endif; ?>

                                    <span class="cart-item-prices__current">
                                        <?php echo wp_kses_post( wc_price( $current_total ) ); ?>
                                    </span>
                                </div>
                            </td>
                        </tr>
                        <?php
                    }
                }
                ?>

                <?php do_action( 'woocommerce_cart_contents' ); ?>

                <div class="actions" hidden="hidden">

                    <button type="submit" class="button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'woocommerce' ); ?>"><?php esc_html_e( 'Update cart', 'woocommerce' ); ?></button>

                    <?php do_action( 'woocommerce_cart_actions' ); ?>

                    <?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
                </div>

                <?php do_action( 'woocommerce_after_cart_contents' ); ?>
                </tbody>
            </table>
            <?php do_action( 'woocommerce_after_cart_table' ); ?>
            </div>
        </form>

        <!--///////////////////////// Наші рекомендації /////////////////////////    -->

        <?php

            $front_page_id = (int) get_option('page_on_front');
            $slider = carbon_get_post_meta( $front_page_id, 'rcmnd_slider' );
            if ( ! empty( $slider ) ): ?>
                <div class="anyweb-cart-recommendations">
                    <div class="recommendations-anyweb-cart js-slick-slider">
                            <div class="slider-line-top-left"></div>
                            <div class="slider-line-top-right"></div>
                        <h2>Ва<span class="color-blue">м</span>
                            м<span class="color-orange">о</span>же
                            сподо<span class="color-blue">б</span>атися</h2>
                        <div class="slider slick-sliders">
                            <?php
                            foreach ( $slider as $cnt => $item ):
                                echo so_render_product($item['rcmnd_product_id']);
                            endforeach;
                            ?>
                        </div>
                    </div>
                </div>
            <?php endif;

        ?>

    </div><!-- .anyweb-cart-main -->

    <?php do_action( 'woocommerce_before_cart_collaterals' ); ?>

    <div class="cart-collaterals">

        <?php do_action( 'woocommerce_cart_actions' ); ?>

        <?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
        <?php
        /**
         * Cart collaterals hook.
         *
         * @hooked woocommerce_cross_sell_display
         * @hooked woocommerce_cart_totals - 10
         */
        do_action( 'woocommerce_cart_collaterals' );
        ?>
    </div>

</div><!-- .anyweb-cart-layout -->

<?php do_action( 'woocommerce_after_cart' ); ?>
