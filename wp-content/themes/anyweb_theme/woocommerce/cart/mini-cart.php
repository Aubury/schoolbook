<?php
/**
 * Mini-cart
 *
 * Contains the markup for the mini-cart, used by the cart widget.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/mini-cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 11.2.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_mini_cart' ); ?>

<?php if ( WC()->cart && ! WC()->cart->is_empty() ) : ?>
    <div class="cart_list__wrap">

	<ul class="woocommerce-mini-cart cart_list product_list_widget <?php echo esc_attr( $args['list_class'] ); ?>">
		<?php
		do_action( 'woocommerce_before_mini_cart_contents' );

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

			/**
			 * Filter whether this cart item is visible in the mini-cart.
			 *
			 * @since 1.6.0
			 * @param bool   $visible       Whether the cart item is visible. Default true.
			 * @param array  $cart_item     The cart item data.
			 * @param string $cart_item_key The cart item key.
			 */
			$visible = apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key );

			if ( $_product instanceof WC_Product && $_product->exists() && $cart_item['quantity'] > 0 && $visible ) {
				$cart_item_name = WC()->cart->get_item_product_name( $cart_item, $_product );
				/**
				 * This filter is documented in woocommerce/templates/cart/cart.php.
				 *
				 * @since 2.1.0
				 */
				$product_name      = apply_filters( 'woocommerce_cart_item_name', $cart_item_name, $cart_item, $cart_item_key );
				$thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
				$product_price     = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
				$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
				?>
				<li class="woocommerce-mini-cart-item <?php echo esc_attr( apply_filters( 'woocommerce_mini_cart_item_class', 'mini_cart_item', $cart_item, $cart_item_key ) ); ?>">
                    <div class="woocommerce-mini-cart-item__wrap">
					<?php
					echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						'woocommerce_cart_item_remove_link',
						sprintf(
							'<a role="button" href="%s" class="remove remove_from_cart_button" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s" data-success_message="%s">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                      <path d="M2.66536 4.00065H3.9987M2.66536 4.00065L3.7187 13.4807C3.7548 13.8069 3.90999 14.1084 4.15453 14.3273C4.39907 14.5463 4.71579 14.6673 5.04403 14.6673H10.9534C11.2816 14.6673 11.5983 14.5463 11.8429 14.3273C12.0874 14.1084 12.2426 13.8069 12.2787 13.4807L13.332 4.00065M2.66536 4.00065H1.33203M3.9987 4.00065H11.9987M3.9987 4.00065L4.89536 2.09865C5.0032 1.86996 5.17384 1.67665 5.38737 1.54126C5.6009 1.40587 5.84853 1.33398 6.10137 1.33398H9.89603C10.149 1.33385 10.3968 1.40568 10.6104 1.54108C10.8241 1.67648 10.9948 1.86986 11.1027 2.09865L11.9987 4.00065M11.9987 4.00065H13.332M13.332 4.00065H14.6654M6.66536 7.33398V10.6673M9.33203 7.33398V10.6673" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" stroke="#989898"/>
                                    </svg></a>',
							esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
							/* translators: %s is the product name */
							esc_attr( sprintf( __( 'Remove %s from cart', 'woocommerce' ), wp_strip_all_tags( $product_name ) ) ),
							esc_attr( $product_id ),
							esc_attr( $cart_item_key ),
							esc_attr( $_product->get_sku() ),
							/* translators: %s is the product name */
							esc_attr( sprintf( __( '&ldquo;%s&rdquo; has been removed from your cart', 'woocommerce' ), wp_strip_all_tags( $product_name ) ) )
						),
						$cart_item_key
					);
					?>
					<?php if ( empty( $product_permalink ) ) : ?>
						<?php echo '<div class="cart_item_image">' . anyweb_product_discount_badge( $_product ) . $thumbnail . '</div><div class="cart_item_name__wrap"><div class="cart_item_name">' . wp_kses_post( $product_name ) . '</div></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php else : ?>
						<a href="<?php echo esc_url( $product_permalink ); ?>" class="woocommerce-mini-cart__wrap">
							<?php echo '<div class="cart_item_image">' . anyweb_product_discount_badge( $_product ) . $thumbnail  . '</div><div class="cart_item_name__wrap"><div class="cart_item_name">' . wp_kses_post( $product_name ) . '</div></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</a>
					<?php endif; ?>
					<?php echo wc_get_formatted_cart_item_data( $cart_item, false, $cart_item_name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        <?php
                        $quantity = (int) $cart_item['quantity'];

                        $current_price = (float) $_product->get_price();
                        $regular_price = $_product->get_regular_price();
                        ?>

                        <div class="mini-cart-price">
                            <?php if (
                                '' !== $regular_price
                                && (float) $regular_price > $current_price
                            ) : ?>
                                <del class="mini-cart-price__old">
                                    <?php
                                    echo wp_kses_post(
                                        wc_price(
                                            wc_get_price_to_display(
                                                $_product,
                                                array(
                                                    'price'           => (float) $regular_price,
                                                    'qty'             => $quantity,
                                                    'display_context' => 'cart',
                                                )
                                            )
                                        )
                                    );
                                    ?>
                                </del>
                            <?php endif; ?>

                            <span class="mini-cart-price__current">
                                <?php
                                echo wp_kses_post(
                                    WC()->cart->get_product_subtotal( $_product, $quantity )
                                );
                                ?>
                            </span>
                        </div>
                    </div>
                </li>
				<?php
			}
		}

		do_action( 'woocommerce_mini_cart_contents' );
		?>
	</ul>
    </div>
    <div class="woocommerce-mini-cart__total__wrap">
        <p class="woocommerce-mini-cart__total total">
            <?php
            /**
             * Hook: woocommerce_widget_shopping_cart_total.
             *
             * @hooked woocommerce_widget_shopping_cart_subtotal - 10
             */
            do_action( 'woocommerce_widget_shopping_cart_total' );
            ?>
        </p>

        <?php do_action( 'woocommerce_widget_shopping_cart_before_buttons' ); ?>

        <p class="woocommerce-mini-cart__buttons buttons"><?php do_action( 'woocommerce_widget_shopping_cart_buttons' ); ?></p>

        <?php do_action( 'woocommerce_widget_shopping_cart_after_buttons' ); ?>
    </div>

<?php else : ?>

	<p class="woocommerce-mini-cart__empty-message"><?php esc_html_e( 'No products in the cart.', 'woocommerce' ); ?></p>

<?php endif; ?>

<?php do_action( 'woocommerce_after_mini_cart' ); ?>
