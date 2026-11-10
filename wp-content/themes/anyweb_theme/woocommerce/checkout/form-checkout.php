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
                    do_action( 'woocommerce_checkout_shipping' );
                    ?>

                    <?php
                    do_action( 'woocommerce_checkout_after_customer_details' );
                    ?>

                    <?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>

                        <?php do_action( 'woocommerce_review_order_before_shipping' ); ?>

                        <?php wc_cart_totals_shipping_html(); ?>

                        <?php do_action( 'woocommerce_review_order_after_shipping' ); ?>

                    <?php endif; ?>
                </div>

                <section id="anyweb-checkout-payment"
                         class="anyweb-checkout__section">
                    <h3>Оплата</h3>

                    <div id="anyweb-checkout-payment-methods"></div>
                </section>

            </div>

            <aside class="anyweb-checkout__sidebar">

                <?php
                do_action( 'woocommerce_checkout_before_order_review_heading' );
                ?>

                <h3 id="order_review_heading">
                    <?php esc_html_e( 'Your order', 'woocommerce' ); ?>
                </h3>

                <?php
                do_action( 'woocommerce_checkout_before_order_review' );
                ?>

                <div id="order_review"
                     class="woocommerce-checkout-review-order">
                    <?php
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