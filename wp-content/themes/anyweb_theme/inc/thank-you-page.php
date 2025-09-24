<?php
if (!defined("ABSPATH")) {
  exit;
}


add_action( 'woocommerce_before_thankyou', 'open_thankyou', 20 );

function open_thankyou(){
    echo '
<div class="checkout-content">
<h2 class="h2-title">Д<span class="color-orange">я</span>ку<span class="color-blue">є</span>мо</h2>';


}


add_action( 'woocommerce_thankyou', 'close_thankyou', 10 );
function close_thankyou(){
    echo "</div>";

}