<?php
if (!defined("ABSPATH")) {
  exit;
}

add_action('wp_ajax_backsjax', 'backsjax' );
add_action('wp_ajax_nopriv_backsjax', 'backsjax' );
	
	
//function backsjax(){
//  $nonce_back =  $_POST['nonce_code'];
//  $nonce_front = wp_create_nonce('so_creator_nonce');
//  check_ajax_referer( 'so_creator_nonce', 'nonce_code' );
// WC()->cart->add_to_cart( $_POST['id'] );
//
// $arrRespCart[] = WC()->cart->get_cart_contents_count();
// $arrRespCart[] = '<span class="cart-title">У кошику: </span>' . sprintf(_n('%d товар', '%d товарів', WC()->cart->cart_contents_count, 'store'), WC()->cart->cart_contents_count) . '<br><span class="basket-sum">на cуму <strong>'. WC()->cart->get_cart_total(). '</strong></span>';
//
//$data = json_encode($arrRespCart,true);
//
//echo $data;
//  wp_die();
//}

function backsjax() {
    check_ajax_referer( 'so_creator_nonce', 'nonce_code' );

    $product_id = isset( $_POST['id'] )
        ? absint( $_POST['id'] )
        : 0;

    $cart_item_key = WC()->cart->add_to_cart( $product_id );

    if ( ! $cart_item_key ) {
        wp_send_json( [
            'error'   => true,
            'message' => 'Не вдалося додати товар до кошика.',
        ] );
    }

    WC()->cart->calculate_totals();

    $count = WC()->cart->get_cart_contents_count();

    // Обновлённый текст рядом с иконкой корзины.
    $basket_html =
        '<span class="cart-title">товарів на суму</span><br>' .
        '<span class="basket-sum"><strong>' .
        WC()->cart->get_cart_total() .
        '</strong></span>';

    // Содержимое выпадающей мини-корзины.
    ob_start();
    woocommerce_mini_cart();
    $mini_cart_html = ob_get_clean();

    // Именно тот товар, который добавлен сейчас.
    $cart_item = WC()->cart->get_cart_item( $cart_item_key );
    $product   = $cart_item['data'];

    ob_start();
    ?>
    <div class="cart-added-notice__header">
        <span class="cart-added-notice__header_text">Додано в кошик!</span>

        <button
            type="button"
            class="cart-added-notice__close"
            aria-label="Закрити"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M7 7L17 17M7 17L17 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke="#989898"/>
            </svg></button>
    </div>

    <div class="cart-added-notice__product">
        <a href="<?php echo esc_url( $product->get_permalink() ); ?>"
           class="cart-added-notice__image">
            <?php echo $product->get_image( 'woocommerce_thumbnail' ); ?>
            <?php echo anyweb_product_discount_badge( $product ); ?>
        </a>

        <a
            class="cart-added-notice__name"
            href="<?php echo esc_url( $product->get_permalink() ); ?>"
        >
            <?php echo esc_html( $product->get_name() ); ?>
        </a>

        <div class="cart-added-notice__price">
            <?php echo wp_kses_post( $product->get_price_html() ); ?>
        </div>
    </div>
    <?php
    $notice_html = ob_get_clean();

    wp_send_json( [
        $count,           // response[0]
        $basket_html,     // response[1]
        $mini_cart_html,  // response[2]
        $notice_html,     // response[3]
    ] );
}

add_action('wp_ajax_one_click_buying', 'one_click_buying' );
add_action('wp_ajax_nopriv_one_click_buying', 'one_click_buying' );

function one_click_buying(){
    $phone = isset( $_POST['phone'] ) && is_string( $_POST['phone'] )
        ? trim( wp_unslash( $_POST['phone'] ) )
        : '';

    if ( ! preg_match( '/^\+380[0-9]{9}$/', $phone ) ) {
        wp_send_json_error(
            [ 'message' => 'Номер телефона в невірному форматі.' ],
            422
        );
    }
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
    $OneClickRespCart['discount'] = WC()->cart->get_cart_discount_total();
    $OneClickRespCart['total']    = WC()->cart->total;

    $fb_link = carbon_get_theme_option( 'fb' );
    $inst_link = carbon_get_theme_option( 'inst' );

	$response = '
	  
		  <div class="popup-form-wrap">
			  <div class="popup-title">
			    Дяку<span class="color-blue">є</span>мо за замов<span class="color-orange">л</span>ення!
			  </div>
			  <div class="wrap-popup">
			   <p>
                    Наш менеджер зв’яжеться з вами найближчим часом (наші робочі години з понеділка по п’ятницю з 10:00 до 19:00)
              </p>
              
              <div class="modal-image">
                  <img
                    src="' . esc_url( get_stylesheet_directory_uri() . '/assets/image/preparing-dog.png' ) . '"
                        alt="Дякуємо за замовлення"
                  >
              </div>
              
			  <div class="social-media">
				  <p>Якщо залишилися запитання,<br>(063) 766-00-77 — ми на зв’язку!</p>
				  <div class="social-links">
				     <a class="fb social_sidebar-icon" 
                        target="_blank" 
                        href="' . $fb_link . '" 
                        rel="nofollow">
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                              <path d="M13 13.5H15.5L16.5 9.5H13V7.5C13 6.47 13 5.5 15 5.5H16.5V2.14C16.174 2.097 14.943 2 13.643 2C10.928 2 9 3.657 9 6.7V9.5H6V13.5H9V22H13V13.5Z" fill="#00B0A8"/>
                            </svg>
                        </span>
                     </a>
                     <a class="inst social_sidebar-icon"
                         target="_blank"
                         href="' . $inst_link . '"
                         rel="nofollow">
                         <span>
                             <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                  <path d="M13.0276 2C14.1526 2.003 14.7236 2.009 15.2166 2.023L15.4106 2.03C15.6346 2.038 15.8556 2.048 16.1226 2.06C17.1866 2.11 17.9126 2.278 18.5496 2.525C19.2096 2.779 19.7656 3.123 20.3216 3.678C20.8303 4.17773 21.2238 4.78247 21.4746 5.45C21.7216 6.087 21.8896 6.813 21.9396 7.878C21.9516 8.144 21.9616 8.365 21.9696 8.59L21.9756 8.784C21.9906 9.276 21.9966 9.847 21.9986 10.972L21.9996 11.718V13.028C22.002 13.7574 21.9944 14.4868 21.9766 15.216L21.9706 15.41C21.9626 15.635 21.9526 15.856 21.9406 16.122C21.8906 17.187 21.7206 17.912 21.4746 18.55C21.2238 19.2175 20.8303 19.8223 20.3216 20.322C19.8219 20.8307 19.2171 21.2242 18.5496 21.475C17.9126 21.722 17.1866 21.89 16.1226 21.94L15.4106 21.97L15.2166 21.976C14.7236 21.99 14.1526 21.997 13.0276 21.999L12.2816 22H10.9726C10.2429 22.0026 9.51312 21.9949 8.78359 21.977L8.58959 21.971C8.3522 21.962 8.11487 21.9517 7.87759 21.94C6.81359 21.89 6.08759 21.722 5.44959 21.475C4.78242 21.2241 4.17804 20.8306 3.67859 20.322C3.16954 19.8224 2.7757 19.2176 2.52459 18.55C2.27759 17.913 2.10959 17.187 2.05959 16.122L2.02959 15.41L2.02459 15.216C2.00616 14.4868 1.99782 13.7574 1.99959 13.028V10.972C1.99682 10.2426 2.00416 9.5132 2.02159 8.784L2.02859 8.59C2.03659 8.365 2.04659 8.144 2.05859 7.878C2.10859 6.813 2.27659 6.088 2.52359 5.45C2.77529 4.7822 3.16982 4.17744 3.67959 3.678C4.17875 3.16955 4.78278 2.77607 5.44959 2.525C6.08759 2.278 6.81259 2.11 7.87759 2.06C8.14359 2.048 8.36559 2.038 8.58959 2.03L8.78359 2.024C9.51278 2.00623 10.2422 1.99857 10.9716 2.001L13.0276 2ZM11.9996 7C10.6735 7 9.40174 7.52678 8.46406 8.46447C7.52638 9.40215 6.99959 10.6739 6.99959 12C6.99959 13.3261 7.52638 14.5979 8.46406 15.5355C9.40174 16.4732 10.6735 17 11.9996 17C13.3257 17 14.5974 16.4732 15.5351 15.5355C16.4728 14.5979 16.9996 13.3261 16.9996 12C16.9996 10.6739 16.4728 9.40215 15.5351 8.46447C14.5974 7.52678 13.3257 7 11.9996 7ZM11.9996 9C12.3936 8.99993 12.7837 9.07747 13.1477 9.22817C13.5117 9.37887 13.8424 9.5998 14.1211 9.87833C14.3997 10.1569 14.6207 10.4875 14.7715 10.8515C14.9224 11.2154 15 11.6055 15.0001 11.9995C15.0002 12.3935 14.9226 12.7836 14.7719 13.1476C14.6212 13.5116 14.4003 13.8423 14.1218 14.121C13.8432 14.3996 13.5126 14.6206 13.1486 14.7714C12.7847 14.9223 12.3946 14.9999 12.0006 15C11.2049 15 10.4419 14.6839 9.87927 14.1213C9.31666 13.5587 9.00059 12.7956 9.00059 12C9.00059 11.2044 9.31666 10.4413 9.87927 9.87868C10.4419 9.31607 11.2049 9 12.0006 9M17.2506 5.5C16.9191 5.5 16.6011 5.6317 16.3667 5.86612C16.1323 6.10054 16.0006 6.41848 16.0006 6.75C16.0006 7.08152 16.1323 7.39946 16.3667 7.63388C16.6011 7.8683 16.9191 8 17.2506 8C17.5821 8 17.9001 7.8683 18.1345 7.63388C18.3689 7.39946 18.5006 7.08152 18.5006 6.75C18.5006 6.41848 18.3689 6.10054 18.1345 5.86612C17.9001 5.6317 17.5821 5.5 17.2506 5.5Z" fill="#00B0A8"/>
                             </svg>
                         </span>
                      </a>		  
                </div>
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
    $msg .= 'Знижка: ' . $OneClickRespCart['discount'] . 'грн'. "\r\n\r\n";
    $msg .= 'Всього: ' . $OneClickRespCart['total'] . 'грн'. "\r\n\r\n";

foreach ($filds as $key => $value) {

	$msg .=$value['value']. "\r\n";
}

	
	
$token = '6343897793:AAE_Jbf8b0Mi0sfahr-8hZBSMspoVMz8z_A';
file_get_contents('https://api.telegram.org/bot'. $token .'/sendMessage?chat_id=-1002006914562&text=' . urlencode($msg));

	echo $data;
	  wp_die();
	}

add_action( 'woocommerce_before_cart', 'open_blosks', 10 );
function open_blosks() {
    echo '<h1 class="entry-title">К<span class="color-orange">о</span>ш<span class="color-blue">и</span>к</h1>';
}



add_action( 'woocommerce_after_cart', 'close_blosks', 10 );
function close_blosks(){


          echo '
          <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
         
          
            <div class="modal-dialog">
             <div class="mob-close-modal">
                    <svg width="50" height="50" viewBox="0 0 50 50" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="50" height="50" rx="25" fill="#99988C"/>
                    <path d="M3.79331 39.3432C3.25009 38.9187 2.65143 38.0417 2.28005 37.3434C1.44304 35.5894 0.866555 34.1147 0.855469 31.8636C0.855469 31.1207 0.977416 30.1878 1.98072 30.1934C2.96184 30.1934 3.18911 31.0704 3.29997 31.7519C3.45518 32.7406 3.37203 33.6455 3.85428 34.8912C4.33653 36.1369 4.62477 36.6899 5.17908 37.4942C5.61698 38.131 6.10477 38.779 5.50612 39.5108C5.01279 40.1141 4.2811 39.7901 3.79331 39.3488V39.3432Z" fill="white"/>
                    <path d="M12.5358 6.25994C11.8873 6.67889 10.6678 7.39948 10.1578 6.72916C9.63121 6.04208 9.63121 5.5561 10.4904 4.61207C10.9505 4.19312 11.7819 3.55074 12.3418 3.22117C14.0823 2.19335 15.457 1.51186 17.4303 1.07056C18.4281 0.847125 19.6365 1.12642 19.8083 2.09838C19.9691 2.99772 18.7884 3.4055 17.8017 3.66804C16.1333 4.1205 13.0624 5.91919 12.5358 6.25994Z" fill="white"/>
                    <path d="M3.54536 11.661C4.56529 9.80089 5.4799 8.69486 6.5608 7.71732C7.14837 7.19224 7.94102 6.86826 8.53968 7.42685C9.14942 7.99662 9.01638 8.85127 8.46208 9.40987C6.4333 11.4543 5.07525 14.4708 4.50431 15.8281C4.16064 16.6381 3.51764 17.0012 2.73052 16.6102C2.00992 16.2527 2.11524 15.236 2.28153 14.5545C2.28153 14.5545 3.06311 12.5827 3.54536 11.6554V11.661Z" fill="white"/>
                    <path d="M41.9012 10.5547C41.1418 9.82293 41.2859 8.97387 41.818 8.56051C42.7825 7.91812 43.5031 8.42085 44.2348 9.0409C45.737 10.3033 46.7846 11.8897 47.5385 13.6828C47.9099 14.5654 48.6305 15.7664 47.7547 16.392C46.7292 17.0512 45.7924 16.1071 45.166 15.1296C44.7947 14.3923 44.2071 13.4817 43.8357 12.7444C43.2925 11.7948 42.7881 11.158 41.8956 10.5547H41.9012Z" fill="white"/>
                    <path d="M37.9035 6.30147C36.7007 5.26248 35.9357 4.92174 34.3005 4.10619C33.4579 3.68724 32.0666 3.31857 32.7706 2.0338C33.4025 0.877502 34.7495 1.68746 35.592 2.06172C36.6674 2.54211 37.4711 3.21802 38.5188 3.77103C38.6574 3.8995 39.4334 4.29053 40.1207 5.01671C40.7194 5.64792 41.44 6.37969 40.9245 7.15055C40.3591 7.99962 39.5997 7.94935 38.8237 7.13379L37.898 6.2959L37.9035 6.30147Z" fill="white"/>
                    <path d="M41.1749 43.89C40.0552 44.8061 38.7859 45.6384 38.481 45.7837C37.6385 46.1803 36.6573 46.75 36.0753 45.6272C35.4046 44.3313 37.4001 43.2588 37.4001 43.2588C38.2593 42.6834 39.9 41.678 41.0696 40.3262C41.6794 39.6167 42.921 38.4549 43.7857 39.4771C45.0884 41.0132 42.1949 43.0689 41.1749 43.8956V43.89Z" fill="white"/>
                    <path d="M47.9782 34.6701C47.7011 35.4577 47.2466 36.357 46.7532 37.0218C46.1823 37.7926 45.4672 38.53 44.5249 38.0272C43.5382 37.5021 44.0316 36.5134 44.4972 35.6476C45.4118 34.0053 46.2322 32.0502 46.5814 31.028C46.8419 30.246 47.1246 29.6762 48.0503 29.8382C48.9649 30.0002 49.1312 30.8437 48.9815 31.5866C48.8152 32.4077 48.1002 34.3461 47.9782 34.6701Z" fill="white"/>
                    <path d="M11.4372 43.9442C12.3074 44.4079 13.6599 44.8939 13.1333 46.0446C12.7342 46.916 11.415 46.5082 10.5835 45.9384C9.50815 45.2067 8.03924 44.1063 7.37407 43.3801C6.79759 42.7544 5.90515 41.7378 6.74769 41.0563C7.44058 40.4977 8.13347 41.1624 8.83744 41.8551C9.39729 42.3187 10.2398 43.313 11.4372 43.9498V43.9442Z" fill="white"/>
                    <path d="M28.8355 0.204203C29.5839 0.287993 30.4209 0.444412 30.31 1.43872C30.1825 2.57825 29.3788 2.5224 28.1704 2.34923C26.6294 2.00849 25.3323 2.13138 23.7747 2.20958C23.043 2.2431 22.0231 2.39949 21.9289 1.37726C21.8235 0.243309 22.9322 0.181871 23.6583 0.0589796C24.3789 -0.0639118 27.1006 0.0142857 28.8411 0.209795L28.8355 0.204203Z" fill="white"/>
                    <path d="M28.9643 49.557C28.227 49.6296 26.542 50.1826 26.4145 48.8084C26.2814 47.3114 27.7947 47.3672 28.6871 47.155C29.6572 46.9259 30.605 46.6131 31.564 46.345C32.3123 46.1383 33.2269 45.4792 33.7092 46.5628C34.2524 47.7862 33.1382 48.1213 32.34 48.5012C31.2647 49.0151 30.3944 49.3614 28.9587 49.5514L28.9643 49.557Z" fill="white"/>
                    <path d="M47.3288 20.2154C47.2456 19.5786 47.1126 18.8356 47.9219 18.5787C48.8586 18.277 49.3132 18.992 49.4795 19.7126C49.9673 21.7906 50.2112 23.8965 49.7677 26.0304C49.6291 26.6895 49.4628 27.4101 48.6702 27.3822C47.6336 27.3375 47.7057 26.4437 47.7168 25.7175C47.75 23.9915 47.5006 21.4945 47.3343 20.2209L47.3288 20.2154Z" fill="white"/>
                    <path d="M18.3688 46.9629C19.2169 47.2031 20.963 47.784 22.6204 47.7617C23.3022 47.7505 23.984 47.8231 24.0616 48.661C24.1558 49.6162 23.4574 49.9067 22.6702 49.9961C21.0406 50.0463 19.2557 49.7056 17.5318 49.1191C16.6782 48.8286 15.4143 48.2644 15.6859 47.1193C16.0241 45.6781 18.3743 46.9629 18.3743 46.9629H18.3688Z" fill="white"/>
                    <path d="M2.98384 19.0701C3.3386 19.5505 2.80092 21.8463 2.61799 23.1981C2.30758 25.4772 3.00047 27.9462 1.55372 28.0858C-0.00942918 28.2366 -0.00943088 26.3821 0.0016553 25.3878C0.0182846 24.0919 -0.175728 19.6622 1.50383 18.6623C2.24106 18.221 2.7732 18.6399 2.98384 19.0756V19.0701Z" fill="white"/>
                    <path d="M18.3498 19.9984C18.1162 19.7649 17.9996 19.4902 18 19.1742C18.0004 18.8583 18.1168 18.5835 18.3492 18.3498C18.5815 18.1162 18.8562 17.9996 19.1733 18C19.4903 18.0004 19.7651 18.1168 19.9976 18.3492L31.6451 29.9979C31.8787 30.2314 31.9956 30.5064 31.9957 30.8229C31.9958 31.1394 31.8791 31.414 31.6457 31.6465C31.4122 31.879 31.1373 31.9959 30.8208 31.9971C30.5043 31.9983 30.2297 31.8817 29.9972 31.6471L18.3498 19.9984ZM20.0031 31.6528C19.769 31.8857 19.4939 32.0014 19.1779 32C18.862 31.9986 18.5876 31.8813 18.3547 31.6482C18.1218 31.4151 18.0061 31.14 18.0075 30.8229C18.0089 30.5059 18.1262 30.2314 18.3593 29.9996L30.0036 18.348C30.2377 18.1151 30.5131 17.9991 30.8296 18C31.1461 18.0009 31.4202 18.1184 31.652 18.3526C31.8838 18.5868 31.9998 18.8622 32 19.1787C32.0002 19.4953 31.8827 19.7694 31.6474 20.0012L20.0031 31.6528Z" fill="white"/>
                    <path d="M19.1779 32C19.4939 32.0014 19.769 31.8857 20.0031 31.6528L31.6474 20.0012C31.8827 19.7694 32.0002 19.4953 32 19.1787C31.9998 18.8622 31.8838 18.5868 31.652 18.3526C31.4202 18.1184 31.1461 18.0009 30.8296 18C30.5131 17.9991 30.2377 18.1151 30.0036 18.348L18.3593 29.9996C18.1262 30.2314 18.0089 30.5059 18.0075 30.8229C18.0061 31.14 18.1218 31.4151 18.3547 31.6482C18.5876 31.8813 18.862 31.9986 19.1779 32Z" fill="white"/>
                    </svg>
              </div>
              <div class="modal-content oneclickform">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                            <path d="M3.77719 5.06804C3.61032 4.90129 3.52704 4.70506 3.52734 4.47936C3.52765 4.25367 3.61078 4.05738 3.77674 3.89049C3.9427 3.72361 4.13892 3.64032 4.36539 3.64063C4.59185 3.64093 4.78813 3.72407 4.95422 3.89004L13.2738 12.2106C13.4407 12.3773 13.5242 12.5737 13.5243 12.7998C13.5243 13.0259 13.441 13.222 13.2743 13.3881C13.1075 13.5542 12.9111 13.6377 12.685 13.6386C12.459 13.6394 12.2629 13.5561 12.0968 13.3886L3.77719 5.06804ZM4.95816 13.3926C4.79088 13.559 4.5944 13.6416 4.36872 13.6406C4.14304 13.6396 3.94703 13.5558 3.78068 13.3893C3.61434 13.2228 3.53168 13.0263 3.53271 12.7999C3.53373 12.5734 3.61749 12.3774 3.78398 12.2118L12.1013 3.88922C12.2686 3.72287 12.4653 3.64001 12.6913 3.64064C12.9174 3.64127 13.1132 3.72523 13.2788 3.89251C13.4443 4.0598 13.5272 4.25649 13.5273 4.48258C13.5275 4.70867 13.4435 4.9045 13.2755 5.07006L4.95816 13.3926Z" fill="#989898"/>
                            <path d="M4.36872 13.6406C4.5944 13.6416 4.79088 13.559 4.95816 13.3926L13.2755 5.07006C13.4435 4.9045 13.5275 4.70867 13.5273 4.48258C13.5272 4.25649 13.4443 4.0598 13.2788 3.89251C13.1132 3.72523 12.9174 3.64127 12.6913 3.64064C12.4653 3.64001 12.2686 3.72287 12.1013 3.88922L3.78398 12.2118C3.61749 12.3774 3.53373 12.5734 3.53271 12.7999C3.53168 13.0263 3.61434 13.2228 3.78068 13.3893C3.94703 13.5558 4.14304 13.6396 4.36872 13.6406Z" fill="#989898"/>
                        </svg>
                    </button>
                </div>

                <div class="modal-body modal-body--one-click">
                <div class="popup-form-wrap">
                    <div class="popup-title">
                        Шви<span class="color-blue">д</span>ке замов<span class="color-orange">л</span>ення
                    </div>
                    <p>
                        Наш менеджер зв’яжеться з вами найближчим часом (наші робочі години з понеділка по п’ятницю з 10:00 до 19:00)
                    </p>
                    <div class="modal-form">
                        <form action="#" method="post" id="one-click-buying-form" class="form_validate" novalidate>
            
                          <div class="inputs-wrap">
                            <div class="input-holder">
                                <label for="name">Ім’я</label>
                                <input type="text" 
                                        name="name" 
                                        value="" 
                                        class="input" 
                                        data-validate="required-alfa"
                                        required 
                                        placeholder="Як до вас звертатись?">
                            </div>
                        
                        
                            <div class="input-holder phone-holder">
                                <label for="order-phone">
                                    Номер телефона
                                    <span class="phone-error" hidden>в невірному форматі</span>
                                </label>
                            
                                <div class="phone-field">
                                    <span class="phone-field__prefix">+380</span>
                            
                                    <input
                                        type="tel"
                                        id="order-phone"
                                        class="phone-field__input"
                                        inputmode="numeric"
                                        autocomplete="tel-national"
                                        placeholder="XX XXX XX XX"
                                        aria-required="true"
                                        aria-invalid="false"
                                    >
                            
                                    <input type="hidden" name="phone" class="phone-field__value">
                                </div>
                            </div>
                          </div> 
                            <div class="submit-holder">
                                <input type="submit" class="submit-btn" value="Замовити">
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
    echo '<div class="buying-click-block">
           <span type="button" class="buying-click" data-bs-toggle="modal" data-bs-target="#exampleModal">
            Замовити в 1 клік
          </span>
          <svg xmlns="http://www.w3.org/2000/svg" width="153" height="4" viewBox="0 0 153 4" fill="none">
              <path d="M31.1106 0.853003C32.0208 0.853003 32.8072 1.19794 32.7198 2.04051C32.6543 2.67386 32.2247 2.97923 31.0742 3.04144C26.9674 3.09233 23.0427 3.14886 18.9359 3.12624C18.215 3.12624 17.5087 2.82654 17.4723 2.11403C17.4286 1.20925 18.2878 1.03393 19.1543 1C21.0839 0.920833 23.0135 0.926515 24.9431 0.886931C25.0961 0.886931 29.0645 0.836033 31.1179 0.847343L31.1106 0.853003Z" fill="#009E97"/>
              <path d="M6.59017 1.2258C9.11686 1.19188 9.97607 1.04484 11.6654 1.04484C12.6193 1.04484 13.6023 1.47463 13.5659 2.3681C13.5295 3.15413 12.663 3.41989 11.7382 3.40858C8.3523 3.37465 5.21396 3.34638 1.83534 3.27287C0.910584 3.25025 -0.137955 2.90529 0.014957 2.06271C0.12418 1.44068 0.997967 1.01092 1.88631 1.04484C3.3499 1.10139 5.17027 1.27104 6.59017 1.22014V1.2258Z" fill="#009E97"/>
              <path d="M125.338 3.39153C123.096 3.30105 121.53 3.2445 119.965 3.17664C119.076 3.13706 118.181 2.97871 118.166 2.14179C118.159 1.35576 118.996 0.948614 119.863 0.942959C124.435 0.90903 129.008 0.926004 133.588 0.988207C134.331 0.999517 134.914 1.47452 134.819 2.15311C134.702 2.99569 133.872 3.14837 132.984 3.17664C130.217 3.26146 127.443 3.33498 125.338 3.39719V3.39153Z" fill="#009E97"/>
              <path d="M82.4576 2.3413C81.3799 2.3922 79.4576 2.48268 77.528 2.55619C76.6324 2.59012 75.744 2.47137 75.5911 1.64576C75.4382 0.808842 76.2392 0.503489 77.1421 0.418666C81.227 0.0510995 85.3265 -0.0676501 89.4333 0.0680668C90.4163 0.101996 91.494 0.294248 91.5377 1.25558C91.5813 2.31869 90.4308 2.31871 89.4697 2.33002C87.4163 2.35263 85.3629 2.33568 82.4576 2.33568V2.3413Z" fill="#009E97"/>
              <path d="M144.113 3.0816C143.101 3.0816 139.431 3.16076 139.424 1.79228C139.417 0.423799 142.169 0.746117 143.654 0.757426C145.926 0.774391 148.045 0.768735 150.39 0.225867C151.475 -0.0286022 152.531 -0.271735 152.924 0.712212C153.295 1.63396 152.232 1.86014 151.358 2.18813C149.363 2.72534 146.858 3.03637 144.12 3.0816H144.113Z" fill="#009E97"/>
              <path d="M48.3513 0.350479C49.4435 0.31655 50.7323 0.26564 50.7542 1.46447C50.776 2.41449 49.691 2.63506 48.6862 2.65768C45.3148 2.73684 41.9362 2.78207 38.5649 2.82166C37.7858 2.83296 37.0722 2.64636 37.0066 1.94516C36.9338 1.1252 37.7202 0.751985 38.594 0.678471C40.3853 0.52579 46.9022 0.395723 48.3513 0.356139V0.350479Z" fill="#009E97"/>
              <path d="M61.6732 2.74215C60.1077 2.74215 58.5421 2.76476 56.9766 2.73083C55.9353 2.70821 54.9669 2.43679 55.0324 1.46981C55.0834 0.672471 55.9645 0.434986 56.8601 0.429331C60.2315 0.412367 63.6101 0.401057 66.9814 0.429331C67.8697 0.440641 68.6926 0.762969 68.7217 1.56031C68.7581 2.58384 67.7678 2.73084 66.7339 2.73649C65.0445 2.7478 63.3625 2.73649 61.6732 2.73649V2.74215Z" fill="#009E97"/>
              <path d="M104.054 2.4419C102.364 2.40231 100.668 2.38532 98.9785 2.31181C98.3669 2.28354 97.7771 2.06302 97.6606 1.52015C97.5149 0.841566 98.0538 0.406133 98.811 0.31C102.415 -0.159355 106.027 0.106415 109.624 0.406123C110.403 0.468326 110.869 0.999886 110.738 1.64454C110.6 2.32313 109.872 2.5267 109.122 2.5267C107.432 2.5267 105.736 2.45884 104.046 2.41926C104.046 2.42491 104.046 2.43059 104.046 2.4419H104.054Z" fill="#009E97"/>
            </svg>
        </div>';
}

/**
 * Обёртка корзины.
 * Заголовок open_blosks() выводится раньше — с приоритетом 10.
 */
add_action( 'woocommerce_before_cart', 'anyweb_cart_layout_open', 20 );

function anyweb_cart_layout_open() {
    echo '<div class="custom-cart-layout">';
}


/**
 * Убираем рекомендации из стандартной правой колонки.
 */
add_action( 'wp', 'anyweb_cart_layout_setup' );

function anyweb_cart_layout_setup() {
    if ( ! is_cart() ) {
        return;
    }

    remove_action(
        'woocommerce_cart_collaterals',
        'woocommerce_cross_sell_display',
        10
    );
}


/**
 * Выводим рекомендации отдельно.
 */
add_action( 'woocommerce_after_cart', 'anyweb_cart_recommendations', 1 );

function anyweb_cart_recommendations() {
    echo '<div class="custom-cart-recommendations">';

    woocommerce_cross_sell_display( 4, 2 );

    echo '</div>';
}


/**
 * Закрываем обёртку до модального окна close_blosks(),
 * которое у тебя подключено с приоритетом 10.
 */
add_action( 'woocommerce_after_cart', 'anyweb_cart_layout_close', 5 );

function anyweb_cart_layout_close() {
    echo '</div>';
}

function anyweb_product_discount_badge( $product ) {
    if ( ! $product instanceof WC_Product ) {
        return '';
    }

    $regular = (float) $product->get_regular_price();
    $current = (float) $product->get_price();

    if ( $regular <= 0 || $current >= $regular ) {
        return '';
    }

    $percent = (int) round(
        ( $regular - $current ) / $regular * 100
    );

    if ( $percent < 1 ) {
        return '';
    }

    return '<div class="pr-info-new"><span class="sale_book">-'
        . esc_html( $percent )
        . '<span class="percent">%</span></span></div>';
}

add_action( 'wp_enqueue_scripts', function () {
    if ( is_cart() ) {
        wp_enqueue_script( 'wc-cart-fragments' );
    }
} );