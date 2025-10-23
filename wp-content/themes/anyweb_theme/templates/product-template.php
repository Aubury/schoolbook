<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package anyweb
 */

get_header();
?>

	<main id="primary" class="site-main">

	<div class="container bx-content-seection">

	<div class="row">
	
		<div class="col-lg-12" id="navigation">
			<div class="bx-breadcrumb" itemprop="http://schema.org/breadcrumb" itemscope="" itemtype="http://schema.org/BreadcrumbList">
	
				<?php if( function_exists('kama_breadcrumbs') ) kama_breadcrumbs(''); ?>
	
		</div>
	</div>                 
	</div>

	<div class="row">
		
			<div class="bx-content">
				<div class="">
					<div class="">
						<div class="bx-catalog-element">
							<?php
                            $series = "";

							while ( have_posts() ) :
								the_post();
                                $product = wc_get_product($post->ID);

                            $attributes = $product->get_attributes();
                            
                            if (isset($attributes["pa_book-series"]) && is_array($attributes["pa_book-series"])) {
                                $pa_color = $attributes["pa_book-series"];
                                
                                if (!empty($pa_color->get_slugs())) {
                                    $series = $pa_color->get_slugs()[0];
                                }
                            }
                            

  $productID = $post->ID;
  $single_image_id = intval($product->get_image_id("woocommerce_single"));
    $data = (object) [
        "permalink" => esc_url($product->get_permalink()),
        "gallery" => array_merge(array($single_image_id), $product->get_gallery_image_ids())
    ];

    


                                echo '<div class="bx-catalog-element">
                                <div class="container-fluid">

                                <div class="row">
                                <div class="col-xs-12">
                                    <div class="bx-title-row">';
                                    the_title( '<h1 class="bx-title">', '</h1>' );			
                            echo   '
                            <div class="product-rate">
								<div class="rate-items">';
                                $rating_count = $product->get_rating_count();

                                   for ($i=5; $i > 0; $i--) { 
                                        if($rating_count){
                                            echo '<span class="full"></span>';
                                            $rating_count--;
                                        } else {
                                            echo '<span></span>';
                                        }
                                    }
                            echo '	
								</div>
								<span class="rate-value">'.$rating_count.'</span>
							</div>';
                            
                            $review_count = $product->get_review_count();
                            $average      = $product->get_average_rating();
                            $rozmir = '';
                            if ($product->get_attribute('rozmir')) {
                                // Получаем значение атрибута "rozmir"
                                $rozmir = $product->get_attribute('rozmir');}
                                $parts = explode(' ', $rozmir);
                                $numbers = array();

                                foreach ($parts as $part) {
                                    if (preg_match_all('/\d+/', $part, $matches)) {
                                        foreach ($matches[0] as $number) {
                                            $numbers[] = intval($number);
                                        }
                                    }
                                }
                            
                            echo '</div>
                                </div>
                            </div>
								
                                <div class="product-card-section">
                                    <div class="product-view">
                                        <div class="product-item-detail-slider-container">
                                             <span class="product-item-detail-slider-close" data-entity="close-popup"></span>
                                            <div class="product-item-detail-slider-block" data-entity="images-slider-block">

		                        					
                                                <div id="products-gallery" class="product-item-detail-slider-images-container " data-entity="images-container" style="cursor: zoom-in;">
                                                ';
                                                $size='<div class="product-size-height"><span class="product-size-text">'.(isset($numbers[0]) ? $numbers[0] : "").' мм</span></div>
                                                <div class="product-size-width"><span class="product-size-text">'.(isset($numbers[1]) ? $numbers[1] : "").' мм</span></div> ';

                                                foreach ($data->gallery as $key => $item) {
                                                    $full_image = wp_get_attachment_image_src($item, 'full'); // Получаем полный размер
                                                    $thumb = wp_get_attachment_image_src($item, 'post-thumbnail'); // эскиз
                                                    echo '<div class="product-item-detail-slider-image' . ($key === 0 ? ' active firstImage' : '') . '" data-entity="image">';

                                                    if ($key === 0 && isset($numbers[0])) {
                                                        echo $size;
                                                    }

                                                    // Вставляем <img> с нужными data-атрибутами
                                                    echo '<img 
                                                            src="' . esc_url($thumb[0]) . '" 
                                                            data-pswp-src="' . esc_url($full_image[0]) . '" 
                                                            data-pswp-width="' . esc_attr($full_image[1]) . '" 
                                                            data-pswp-height="' . esc_attr($full_image[2]) . '" 
                                                            class="zoomable-image"
                                                            alt="img" 
                                                        />';

                                                    echo '</div>';

                                                }
                                         echo '</div></div>
                                        <div class="product-item-detail-slider-controls-block slick-slider slick-vertical">';
                                        
                                        foreach ($data->gallery as $key => $item) {  
                                           echo   '<div class="product-item-detail-slider-controls-image'; if( $key == 0){echo ' active';}
                                           echo '"
											data-entity="slider-control">' . 
                                                    wp_get_attachment_image($item, 'post-thumbnail', 'true', array());
                                                echo '</div>';
                                                     }
                                        echo '</div>
                                            </div>
                                        </div>';
                                        ?>

                                        <div class="product-info-section">
                                        <div class="product-about">
                                           <div class="book-info">
                                              <div class="book-row">
                                                 <span class="row-title">Автор</span>
                                                 <span  class="row-value name"><?php echo $product->get_attribute('author-book') ?></span>
                                              </div>
                                           </div>
                                           <div class="product-item-detail-pay-block">
                                              <div class="product-item-detail-info-container">
                                                 <div class="product-item-detail-price-old" id="bx_117848907_17177_old_price" style="display: none;">
                                                    <span></span>
                                                    <span class="currency">грн</span>
                                                 </div>
                                                 <div class="product-item-detail-price-current" id="bx_117848907_17177_price" style="font-size: 29px;">
                                                                <?php
                                                                $price = floatval($product->get_price());
                                                                $regular_price = floatval($product->get_regular_price());

                                                                if($price != $regular_price){

                                                                echo 
                                                                '<span class="product-item-price-current">
                                                                    <del aria-hidden="true">
                                                                    <span class="woocommerce-Price-amount amount"><bdi>' . $regular_price . 'грн</bdi></span>
                                                                </del>
                                                                    <ins>
                                                                    <span class="woocommerce-Price-amount amount"><bdi>' . $price . '<span class="currency">грн</span></bdi></span>
                                                                </ins>
                                                                </span>';

                                                                }else{
                                                                echo
                                                                '<span class="woocommerce-Price-amount amount">
                                                                <bdi>' . $price . '<span class="currency">грн</span></bdi>
                                                                </span>';

                                                                }
                                                         ?>
                                                 </div>
                                              </div>
                                              <div data-entity="main-button-container">
                                                 <div id="bx_117848907_17177_basket_actions" style="display: ;">


<?php
                                                 $custom_royalty = get_post_meta($productID, '_custom_royalty', true);

                                                // Если флажок "роялти" установлен, выводим текст из поля "статус"
                                                if ($custom_royalty === 'yes' && !$product->is_on_backorder()) {
                                                    $custom_status = get_post_meta($productID, '_custom_status', true);
                                                    
                                                    // Выводим текст статуса
                                                    echo '<div class="availability"><span class="available"> ' . esc_html($custom_status) . '</span></div>';
                                                } else {

                                                    $custom_preorder = get_post_meta($productID, '_custom_preorder', true);
                                                    $custom_preorder_countdown = get_post_meta($productID, '_custom_preorder_countdown', true);
                                                    $current_date = date('Y-m-d');

                                                    function display_countdown_timer() {
                                                        // Установим дату окончания акции (формат: год-месяц-день час:мин:сек)
                                                        global $custom_preorder_countdown;
                                                        $end_date = $custom_preorder_countdown;

                                                        // Преобразуем дату в формат для JavaScript
                                                        $end_date_js = date('Y-m-d H:i:s', strtotime($end_date));

                                                        // Передаем дату в JavaScript
                                                        echo "<script type='text/javascript'>
                                                                    var endDate = new Date('{$end_date_js}').getTime();
                                                                </script>";
                                                    }

                                                    add_action('wp_footer', 'display_countdown_timer');

                                                    if ($custom_preorder_countdown > $current_date ) {
                                                        echo '<div class="availability">
                                                                    <span class="available">
                                                                        <span class="soldout">
                                                                            Унікальна можливість придбати книгу до початку офіціального продажу за спеціальною ціною
                                                                        </span>
                                                                    </span>
                                                               </div>
                                                               <h5>Акція діє до: ' . date("d.m.Y", strtotime($custom_preorder_countdown)) .'</h5>
                                                               <div class="mt-3 mb-3 p-2 border rounded-3" style="border-color: #ff6840 !important;">
                                                                    <h6 class="text-center" style="color: #ff6840;">До закінчення акціі залишилось:</h6>
                                                                    <h5 id="countdown" class="text-center" style="color: #ff6840;"></h5>
                                                               </div>
                                                               
                                                               <div class="product-item-detail-info-container">
                                                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#preorder">ЗАМОВИТИ</button>
                                                               </div>
                                                            <script type="text/javascript">
                                                            document.addEventListener("DOMContentLoaded", function() {
                                                                // Обновляем таймер каждую секунду
                                                                var x = setInterval(function() {
                                                                    var now = new Date().getTime();
                                                                    var distance = endDate - now;

                                                                    // Расчет дней, часов, минут и секунд
                                                                    var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                                                                    var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                                                    var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                                                                    var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                                                                    // Отображение результата
                                                                    document.getElementById("countdown").innerHTML = days + " дн. " + hours + " год. "
                                                                        + minutes + " хв. " + seconds + " сек.";

                                                                    // Если обратный отсчет закончился
                                                                    if (distance < 0) {
                                                                        clearInterval(x);
                                                                        document.getElementById("countdown").innerHTML = "Акцію завершено!";
                                                                    }
                                                                }, 1000);
                                                            });
                                                            </script>';

                                                    } else {
?>
                                                    <div class="availability"><span class="available">
                                                         <?php 
                                                        if ( !$product->is_in_stock() ) {
                                                                echo '<span class="soldout">Немає в наявності</span>';
                                                            }else{
                                                                
                                                                if($product->get_stock_status()=='onbackorder'){
                                                                    echo 'На замовлення';
                                                                }else{
                                                                    echo 'Є в наявності'; 
                                                                }
                                                            } 
                                                        ?>
                                                        </span></div>


                                                        <div class="product-item-detail-info-container">
                                                            <a class="ga_buy_btn_detail btn btn-default product-item-detail-buy-button add_to_cart_ajx" id="<?php echo $post->ID; ?>" href="">
                                                                <span>До кошика</span>
                                                            </a>
                                                        </div>
                                                    <?php
                                                    }}
                                                    ?>
                                                 </div>                
                                              </div>
                                           </div>
                                           <div class="links-row">
                                              <div class="favorite-box">
                                                 <span id="favorite_17177" class="favorites-link bx-catalog-subscribe-button " style="">
                                                 <i class="icon-favorite">
                                                     <?php
                                                        echo do_shortcode( '[woosw id='.$post->ID.']' );
                                                     ?>
                                                 </i>
                                                 <span class="favorite-text">Вподобати</span>
                                                 </span>
                                              </div>
                                              <div class="social-links">
                                              <a href="<?php echo get_permalink($post->ID) ?>" class="inlaked" target="_blank" onclick="window.open('https://www.facebook.com/sharer/sharer.php?u=<?php echo get_permalink($post->ID) ?>','','height=500,width=500') ">
								<i class="icon-fb"></i>
							</a>
                                                 <!-- <a
                                                    href=""
                                                    target="_blank""
                                                    >
                                                    <i class="icon-gp">
                                                        <svg role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512" class=" fa-google-plus-g"><path d="M386.061 228.496c1.834 9.692 3.143 19.384 3.143 31.956C389.204 370.205 315.599 448 204.8 448c-106.084 0-192-85.915-192-192s85.916-192 192-192c51.864 0 95.083 18.859 128.611 50.292l-52.126 50.03c-14.145-13.621-39.028-29.599-76.485-29.599-65.484 0-118.92 54.221-118.92 121.277 0 67.056 53.436 121.277 118.92 121.277 75.961 0 104.513-54.745 108.965-82.773H204.8v-66.009h181.261zm185.406 6.437V179.2h-56.001v55.733h-55.733v56.001h55.733v55.733h56.001v-55.733H627.2v-56.001h-55.733z" class=""></path></svg>
                                                    </i>
                                                    </a> -->
                                              </div>
                                           </div>
                                           <?php
                                                if(!$product->is_on_backorder()) :
                                                   echo '<a class="vidosik" href="http://' . get_post_meta($post->ID, '_video_link', true) . '">Переглянути<br> відосик</a>';
                                              endif;
                                           ?>
                                           <div class="text-descr">
                                              <div class="text-holder">
                                                 <div class="inner-box">
                                                    <p>
                                                        <?php 
                                                        // echo $product->get_short_description();
                                                    
                                                        $attribute_slug = 'pa_korotkij-opis';
                                                        $product = wc_get_product($post->ID);
                                                        $attribute_value = $product->get_attribute($attribute_slug);
                                                        if (!empty($attribute_value)) {
                                                            echo esc_html($attribute_value);
                                                        }
                                                    
                                                    ?>
                                                    </p>
                                                 </div>
                                              </div>
                                              <a href="#title-info" class="more-link" style="display: inline;">Детальніше</a>
                                           </div>
                                           <!--  -->
                                        </div>
                                        <div class="">
                                           <!-- <span class="aside-title">Замов на 600 грн — доставимо <br>безкоштовно!</span>
                                              <div class="delivery-free-img">
                                                  <img src="/local/templates/schoolbook/images/img-aside-products.png" alt="icon" title="icon">
                                              </div>
                                              <ul class="links-list">
                                                  <li><a onclick="" href="/delivery/"><span>Доставка та оплата</span><i class="link-arrow"></i></a></li>
                                              </ul> -->
                                        </div>
                                     </div>
                                    </div>

                                </div>


                                <div class="description-and-info">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <span class="title-info" id='title-info'>Детальна інформація</span>
                                                <div class="table-info">
                                                    <table>
                                                    <tbody>                                                    
                                                        
                                                        <?php if (!empty($product->get_sku())):?>   
                                                        <tr>
                                                            <td class="heading">ISBN</td>
                                                            <td class="value"><?php echo $product->get_sku();?>                                                                              
                                                            </td>
                                                        </tr>
                                                        <?php endif;?>
                                                        <?php if (!empty($product->get_weight())):?>   
                                                        <tr>
                                                            <td class="heading">Вага</td>
                                                            <td class="value"><?php echo $product->get_weight()?>                                                                      
                                                            </td>
                                                        </tr>
                                                        <?php endif;
                                                        
                                                        
                                                        $attributes = $product->get_attributes();
                                                        foreach ($attributes as $attribute) {
                                                            $attribute_slug = strtolower($attribute->get_name()); // Получаем слаг атрибута в нижнем регистре
                                                        
                                                            // Проверка, что слаг атрибута не равен "korotkij-opis"
                                                            if ($attribute_slug === 'pa_korotkij-opis'
                                                                    || strpos($attribute_slug, 'pa_kt') === 0
                                                                    || $attribute_slug === 'pa_author-book') {
                                                                continue; // Пропускаем вывод для этого атрибута
                                                            }
                                                        
                                                            $attribute_name = wc_attribute_label($attribute_slug);
                                                            $attribute_value = $product->get_attribute($attribute_slug);
                                                        
                                                            ?>
                                                            <tr>
                                                                <td class="heading"><?php echo esc_html($attribute_name); ?></td>
                                                                <td class="value"><?php echo esc_html($attribute_value); ?></td>
                                                            </tr>
                                                            <?php
                                                        }
                                                        
                                                        
                                                        ?>
                                                        
                                                    </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <span class="title-info">Опис</span>
                                                <div class="product-item-dscription" itemprop="description">
                                                    <?php

// Получаем описание товара
$product_description = $product->get_description();

// Проверяем, что описание не пусто и является строкой JSON
if (!empty($product_description) && is_string($product_description)) {
    // Декодируем JSON в массив
    $description_data = json_decode($product_description, true);

    // Проверяем успешность декодирования
    if ($description_data !== null) {
        // Выводим данные из массива
       echo  $description_data[0]['text'];
    } else {
        // Обработка ошибки декодирования
        echo $product->get_description();
    }
} else {
    // Описание пусто или не является строкой JSON
    // echo "<p>Нет данных для отображения.</p>";
}



                                                    ?>

                                                </div>
                                            </div>
                                        </div>
                                        </div>

<?php
$upsells = $product->get_upsell_ids();
if(!empty($upsells)):
?>
<div class="catalog-section bx-blue upsale" data-entity="container-1">
   <div class="slider-block recommendations">
      <p class="h2-title">З цим<span class="color-orange"> т</span>оваром зазв<span class="color-blue">и</span>чай купують</p>
      <div class="slider slick-slider">
      <?php
            
            if ( ! empty( $upsells ) ):
            foreach ( $upsells as $cnt => $item ): 
            echo so_render_product($item);
            endforeach;
            endif;

        ?>  	

   </div>
   </div>
</div>
<?php
endif;
endwhile;
wp_reset_query();

 
$args = array(
    'post_type' => array('product'),
    'tax_query' => array(
        'relation' => 'OR',
        array(
            'taxonomy' => 'pa_book-series', 
            'field' => 'slug',             
            'terms' => array($series),        
            'operator' => 'IN',
        )
    )
);

$q = new WP_Query( $args );

if( $q->have_posts() ) :

        echo '<div class="catalog-section bx-blue upsale" data-entity="container-1">
   <div class="slider-block series">
    <p class="h2-title">Кни<span class="color-orange">г</span>и з цієї <span class="color-blue">с</span>ерії</p>
    <div class="slider slick-slider">
    ';

	while( $q->have_posts() ) : $q->the_post();

      
    echo so_render_product($post->ID) ;
  
	endwhile; 
    ?>
    </div>
   </div>
</div>

<?php
endif;
wp_reset_query();

$viewed_products = viewed_products();
if($viewed_products){
echo '<div class="catalog-section bx-blue upsale" data-entity="container-1">
<div class="slider-block viewed_products">
<p class="h2-title">Пере<span class="color-orange">г</span>лянуті то<span class="color-blue">в</span>ари</p>
 <div class="slider slick-slider">
 ';
 
foreach ($viewed_products as $item) {
    echo so_render_product($item);
}
 
echo ' </div>
</div>
</div>';
}
?>

                                    





                                
                            </div>
                            </div>
                            
                            <?php

								// If comments are open or we have at least one comment, load up the comment template.
								if ( comments_open() || get_comments_number() ) :
									comments_template();
								endif;
							 // End of the loop.
							?>
						</div>
					</div>					
				</div>
			</div>
	</div>

	</main><!-- #main -->
    <div class="modal fade" id="preorder" tabindex="-1" aria-labelledby="preorderLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <h3 class="modal-title text-center" id="preorderModalLabel">
              ВІДПРАВЛЕННЯ ОЧІКУЄТЬСЯ З <br><span><?php echo date('d.m.Y', strtotime($custom_preorder_countdown)) ?></span>
          </h3>
        <!-- Форма -->
          <div class="mt-2 product-item-detail-info-container d-flex align-items-center justify-content-center">
              <a class="product-preorder ga_buy_btn_detail btn btn-primary product-item-detail-buy-button add_to_cart_ajx"
                 id="<?php echo $post->ID ?>"
                 href="">
                  <span>ЗАМОВИТИ</span>
              </a>
          </div>
      </div>
    </div>
  </div>
</div>
<?php
get_sidebar();
get_footer();
