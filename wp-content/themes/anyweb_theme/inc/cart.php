<?php
if (!defined("ABSPATH")) {
  exit;
}

add_action('wp_ajax_backsjax', 'backsjax' );
add_action('wp_ajax_nopriv_backsjax', 'backsjax' );
	
	
	function backsjax(){
	  $nonce_back =  $_POST['nonce_code'];
	  $nonce_front = wp_create_nonce('so_creator_nonce');
	  check_ajax_referer( 'so_creator_nonce', 'nonce_code' );
	 WC()->cart->add_to_cart( $_POST['id'] );
	 
	 $arrRespCart[] = WC()->cart->get_cart_contents_count();
	 $arrRespCart[] = '<span class="cart-title">У кошику: </span>' . sprintf(_n('%d товар', '%d товарів', WC()->cart->cart_contents_count, 'store'), WC()->cart->cart_contents_count) . '<br><span class="basket-sum">на cуму <strong>'. WC()->cart->get_cart_total(). '</strong></span>';

	$data = json_encode($arrRespCart,true);

	echo $data;
	  wp_die();
	}



	add_action('wp_ajax_one_click_buying', 'one_click_buying' );
	add_action('wp_ajax_nopriv_one_click_buying', 'one_click_buying' );
	
	
	function one_click_buying(){
	  $nonce_back =  $_POST['nonce_code'];
	  $filds =  $_POST['filds'];
	  $nonce_front = wp_create_nonce('so_creator_nonce');
	  check_ajax_referer( 'so_creator_nonce', 'nonce_code' );
	  $OneClickRespCart= array();

	  foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
		$product = $cart_item['data'];
		$product_id = $cart_item['product_id'];
		$one_product = wc_get_product( $product_id );
		$quantity = $cart_item['quantity'];
		// Anything related to $product, check $product tutorial
		$attributes = $product->get_attributes();
		$OneClickRespCart[$product_id]['name'] =  $product->get_title();
		$OneClickRespCart[$product_id]['product_id'] = $product_id ;
		$OneClickRespCart[$product_id]['quantity'] = $quantity ;
		$OneClickRespCart[$product_id]['price'] =  $product->get_price() ;
		$OneClickRespCart[$product_id]['link'] = get_permalink( $product_id );
		
	 }
	$OneClickRespCart['subtotal'] = WC()->cart->subtotal;
	  $response = '
	  <div class="modal-body modal-body--one-click-thx">
		  <div class="popup-form-wrap">
			  <h2 class="h2-title">Дя<span class="color-orange">ку</span>є<span class="color-blue">мо</span>!</h2>
			  <div class="text-center">
				  <h3>Ми зателефонуємо вам найближчим часом</h3>
			  </div>
		  </div>
	  </div>
  ';
	$data = json_encode($response,true);
	WC()->cart->empty_cart( $clear_persistent_cart = true );

	$msg = 'Купити в 1 клік' . "\r\n\r\n";

	foreach ($OneClickRespCart as $key => $value) {
		if(!empty( $value['product_id'])){
		$msg .= '[ID ' . $value['product_id'].'] ' . $value['name'] . ' - ' . $value['quantity'] . ' шт. /' . $value['price'] . "грн\r\n" ;
	}
	}
$msg .= 'Загальна сума: ' . $OneClickRespCart['subtotal'] . 'грн'. "\r\n\r\n";
foreach ($filds as $key => $value) {

	$msg .=$value['value']. "\r\n";
}

	
	
$token = '6343897793:AAE_Jbf8b0Mi0sfahr-8hZBSMspoVMz8z_A';
file_get_contents('https://api.telegram.org/bot'. $token .'/sendMessage?chat_id=-1002006914562&text=' . urlencode($msg));

	echo $data;
	  wp_die();
	}





	add_action( 'woocommerce_before_cart', 'open_blosks', 10 );
	function open_blosks(){
		echo '
   <h2 class="h2-title">К<span class="color-orange">о</span>ш<span class="color-blue">и</span>к</h2>';

		
	}



	add_action( 'woocommerce_after_cart', 'close_blosks', 10 );
	function close_blosks(){


			  echo '
			  <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
				<div class="modal-dialog">
				  <div class="modal-content oneclickform">
					<div class="modal-header">
					  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
					</div>

					<div class="modal-body modal-body--one-click">
					<div class="popup-form-wrap">
						<span class="popup-title">Введіть ім’я та номер телефону</span>
						<div class="modal-form">
							<form action="#" method="post" id="one-click-buying-form" class="form_validate">
				
								<div class="row">
									<div class="input-holder">
										<input type="text" name="name" value="" class="input" data-validate="required-alfa" placeholder="Ім`я">
									</div>
								</div>
								<div class="row">
									<div class="input-holder">
										<input type="tel" name="phone" value="" class="input validate_phone" data-validate="required-number" placeholder="Телефон">
									</div>
								</div>
								<div class="submit-holder">
									<input type="submit" class="submit-btn btn" value="Відправити">
								</div>
							</form>
						</div>
					</div>
				</div>




				  </div>
				</div>
			  </div>';
	}
	


	add_action( 'woocommerce_proceed_to_checkout', 'add_insta_cart', 10 );
	function add_insta_cart(){
echo '<span type="button" class="buying-click" data-bs-toggle="modal" data-bs-target="#exampleModal">
Купити в 1 клік
</span>';
	}
	