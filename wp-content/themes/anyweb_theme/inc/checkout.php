<?php
if (!defined("ABSPATH")) {
  exit;
}


add_action( 'woocommerce_before_checkout_form_cart_notices', 'open_checkout', 20 );

function open_checkout(){
    echo '<div class="checkout-content">
      <h1 class="entry-title">Оф<span class="color-orange">о</span>рмлен<span class="color-blue">н</span>я замо<span class="color-orange">в</span>ленн<span class="color-blue">я</span></h1>';


}


add_action( 'woocommerce_after_checkout_form', 'close_checkout', 10 );
function close_checkout(){
    echo "</div>";

}