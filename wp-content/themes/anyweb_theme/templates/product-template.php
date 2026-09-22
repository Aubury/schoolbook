<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package anyweb
 */
global $product;
get_header();
?>

    <main id="primary" class="site-main product-template">

        <div class="container">

            <div id="navigation" class="flex-row-start" itemprop="http://schema.org/breadcrumb" itemscope="" itemtype="http://schema.org/BreadcrumbList">
                <?php if( function_exists('kama_breadcrumbs') ) kama_breadcrumbs(''); ?>
            </div>

            <div class="page-content">

                <?php
                $series = "";

                while ( have_posts() ) :
                    the_post();

                    $product = wc_get_product($post->ID);

                    $attributes = $product->get_attributes();

                    if (isset($attributes["pa_book-series"]) && is_array($attributes["pa_book-series"])) :
                        $pa_color = $attributes["pa_book-series"];

                        if ( !empty($pa_color->get_slugs()) ) :
                            $series = $pa_color->get_slugs()[0];
                        endif;
                     endif;

                    $productID = $post->ID;
                    $single_image_id = intval($product->get_image_id("woocommerce_single"));
                    $data = (object) [
                        "permalink" => esc_url($product->get_permalink()),
                        "gallery" => array_merge(array($single_image_id), $product->get_gallery_image_ids())
                    ];

                    ?>

                    <div class="product-content-section">

                        <div class="product-item-detail-slider-container">

                               <div class="product-gallery-container">
                                   <div class="product-item-detail-slider-controls-block slick-slider slick-vertical">
                                       <?php
                                           foreach ($data->gallery as $key => $item) :
                                               echo   '<div class="product-item-detail-slider-controls-image'; if ( $key == 0 ) { echo ' active'; }
                                               echo '"data-entity="slider-control">' .  wp_get_attachment_image($item, 'post-thumbnail', 'true', array()) . '</div>';
                                           endforeach;
                                       ?>
                                   </div> <!-- .product-item-detail-slider-controls-image -->
                               </div>

                               <?php
                                   $is_new = get_post_meta($productID, '_is_new', true);
                                   $custom_preorder = get_post_meta($productID, '_custom_preorder', true);

                                   $current_date = date('Y-m-d');

                                   $custom_preorder > $current_date
                                       ? $preorder = '<span class="preorder-book"></span>'
                                       : $preorder = '';

                                   $is_new
                                       ? $is_new = '<span class="new_book"></span>'
                                       : $is_new = '';

                                   $is_sale = '';
                                   if ($product->is_on_sale()) {
                                       $regular_price = $product->get_regular_price();
                                       $sale_price = $product->get_sale_price();
                                       $discount_percentage = round((($regular_price - $sale_price) / $regular_price) * 100);
                                       $is_sale = '<span class="sale_book">-' . $discount_percentage . '<span class="percent">%</span></span>';
                                   }


                               ?>

                               <div class="product-item-detail-slider-block" data-entity="images-slider-block">
                                   <div id="products-gallery"
                                        class="product-item-detail-slider-images-container "
                                        data-entity="images-container"
                                        style="cursor: zoom-in;">

                                       <?php

                                                foreach ($data->gallery as $key => $item) :

                                                    $full_image = wp_get_attachment_image_src($item, 'full'); // Получаем полный размер
                                                    $thumb = wp_get_attachment_image_src($item, 'post-thumbnail'); // эскиз
                                                    echo '<div class="product-item-detail-slider-image' . ($key === 0 ? ' active firstImage' : '') . '" data-entity="image">';


                                                        // Вставляем <img> с нужными data-атрибутами
                                                        echo '<img src="' . esc_url($thumb[0]) . '" 
                                                                   data-pswp-src="' . esc_url($full_image[0]) . '" 
                                                                   data-pswp-width="' . esc_attr($full_image[1]) . '" 
                                                                   data-pswp-height="' . esc_attr($full_image[2]) . '" 
                                                                   class="zoomable-image"
                                                                   alt="img" />';

                                                    echo '</div>'; // .product-item-detail-slider-image

                                               endforeach;
                                        ?>

                                   </div> <!-- .product-item-detail-slider-images-container -->

                                   <div class="pr-info-new">
                                       <?php
                                       echo $preorder;
                                       echo $is_new;
                                       echo $is_sale;
                                       ?>
                                   </div>
                               </div> <!-- .product-item-detail-slider-block -->


                           </div> <!-- .product-item-detail-slider-container -->

                        <div class="product-info-section">
                            <?php
                                 $custom_royalty = get_post_meta($productID, '_custom_royalty', true);

                                // Если флажок "роялти" установлен, выводим текст из поля "статус"
                                if ($custom_royalty === 'yes' && !$product->is_on_backorder()) :
                                    $custom_status = get_post_meta($productID, '_custom_status', true);

                                    // Выводим текст статуса
                                    echo '<span class="available"> ' . esc_html($custom_status) . '</span>';

                                elseif (!$product->is_in_stock()) :
                                    echo '<span class="available soldout">Немає в наявності</span>';
                                else :

                                    if ($product->get_stock_status() == 'onbackorder') :
                                        echo '<span class="available pre-order">На замовлення</span>';
                                    else :
                                        echo '<span class="available">Є в наявності</span>';
                                    endif;
                                endif;

                            ?>

                            <div class="product-content-title">

                                <?php

                                    the_title( '<h1 class="product-page-title">', '</h1>' );

                                ?>

                            </div> <!-- end of .product-content-title -->

                            <div class="product-description-price-section">
                                <div class="product-description-section">

                                    <div class="description-text-holder">
                                        <div class="text-holder">

                                            <?php

                                            $attribute_slug = 'pa_korotkij-opis';
                                            $product        = wc_get_product( $post->ID );

                                            if ( $product ) {
                                                $attribute_value = $product->get_attribute(
                                                    $attribute_slug
                                                );

                                                $product_description = $product->get_description();

                                                if ( ! empty( $attribute_value ) ) :
                                                    echo esc_html(
                                                        schoolbook_trim_text(
                                                            $attribute_value,
                                                            34
                                                        )
                                                    );
                                                else :
                                                    echo esc_html(
                                                        schoolbook_trim_text(
                                                            $product_description,
                                                            34
                                                        )
                                                    );
                                                endif;
                                            }
                                            ?>

                                        </div> <!-- end of .text-holder -->

                                        <a href="#title-info" class="more-link" style="display: inline;">докладний опис</a>
                                    </div>

                                    <div class="author-info">
                                        <span class="row-title">Автор:</span>
                                        <span  class="row-value name"><?php echo $product->get_attribute('author-book') ?></span>
                                    </div> <!-- end .book-info -->

                                    <div class="age-info">
                                        <?php
                                        $age = implode(', ', wc_get_product_terms(@$productID, 'pa_vik', ['fields' => 'names']));
                                        ?>

                                        <span class="row-title">Вік:</span>
                                        <span  class="row-value name"><?php echo $age; ?></span>
                                    </div>

                                </div> <!-- end of .product-description-section -->

                                <?php
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

                                    echo '<div class="column-auto">
                                            <div class="preorder-red-stroke">
                                                <div class="animation-stroke">
                                                    <div class="preorder-text">
                                                        <span class="color-orange">Унікальна</span>
                                                         <span class="color-blue">можливість</span>
                                                         <span class="color-orange">придбати книгу</span>
                                                          до офіційного  початку розпродажу 
                                                    </div>
                                                </div>
                                            </div>
                                               
                                          
                                           <h3>
                                                <span class="color-blue">Акція <span class="text-normal">діє до:</span> </span>'
                                        . date("d.m.Y", strtotime($custom_preorder_countdown)) .
                                        '</h3>
                                                
                                           <div class="preparing-timer">
                                                До початку офіційного продажу:
                                                <span id="countdown" class="color-white"></span>
                                           </div></div>
                                           
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
                                }

                                ?>

                                <div class="product-price-basket-section">
                                    <div class="product-item-detail-price-current">
                                        <?php

                                        $price = floatval($product->get_price());
                                        $regular_price = floatval($product->get_regular_price());

                                        if ($price != $regular_price) : ?>
                                            <span class="product-item-price-current">
                                            <del aria-hidden="true">
                                                <span class="woocommerce-Price-amount amount">
                                                    <bdi><?php echo $regular_price ?> грн</bdi>
                                                </span>
                                            </del>
                                            <ins>
                                                <span class="woocommerce-Price-amount amount">
                                                    <bdi><?php echo $price ?><span class="currency"> грн</span></bdi>
                                                </span>
                                            </ins>
                                        </span>
                                        <?php
                                        else :  ?>
                                            <span class="woocommerce-Price-amount amount">
                                            <bdi><?php echo $price ?><span class="currency"> грн</span></bdi>
                                        </span>
                                        <?php
                                        endif; ?>

                                    </div> <!-- .product-item-detail-price-current -->

                                    <?php
                                        $in_cart = WC()->cart &&
                                            WC()->cart->find_product_in_cart(
                                                WC()->cart->generate_cart_id($productID)
                                            );

                                        $basketHTML= '<span class="text-block">До кошика</span>';

                                        if ($in_cart) :
                                            $basketHTML= '<span class="text-block">У кошику</span>
                                                             <span class="icon-block icon-red-backed">                                    
                                                            </span>';
                                        elseif ($custom_preorder_countdown > $current_date )  :
                                            $basketHTML= '<span class="text-block">Замовити</span><span class="icon-block"></span>';
                                        else :
                                            $basketHTML= '<span class="text-block">До кошика</span><span class="icon-block"></span>';
                                        endif;
                                    ?>

                                    <div class="product-item-detail-info-container">
                                        <div class="favorite-box">
                                             <span id="favorite_17177" class="favorites-link bx-catalog-subscribe-button " style="">
                                                 <i class="icon-favorite">
                                                     <?php
                                                     echo do_shortcode( '[woosw id='.$post->ID.']' );
                                                     ?>
                                                 </i>
                                             </span>
                                        </div>

                                        <?php
                                            if ($custom_preorder_countdown > $current_date ) : ?>
                                                <button type="button" class="product-item-detail-buy-button" data-bs-toggle="modal" data-bs-target="#preorder">
                                                    <?php echo $basketHTML; ?>
                                                </button>
                                            <?php
                                            else : ?>
                                                <a class="product-item-detail-buy-button ga_buy_btn_detail add_to_cart_ajx" id="<?php echo $post->ID; ?>" href="">
                                                    <?php echo $basketHTML; ?>
                                                </a>
                                            <?php
                                            endif;
                                        ?>

                                    </div> <!-- end .product-item-detail-info-container  -->
                                </div>

                            </div> <!-- end of .product-description-price-section -->

                            <?php

                            if ($custom_preorder_countdown < $current_date ) :
                                if ( !$product->is_on_backorder() ) : ?>
                                    <div class="product-video-section">
                                        <a class="vidosik" href="http://<?php echo get_post_meta($post->ID, '_video_link', true)?>">Відео-огляд на книгу</a>
                                    </div>

                                    <?php
                                endif;
                            endif; ?>


                        </div> <!-- end of .product-info-section -->

                        <div class="description-and-info"  id='title-info'>

                            <div class="description-section">
                                <span class="title-info">Опис</span>
                                <div class="product-item-description" itemprop="description">
                                    <?php

                                    // Получаем описание товара
                                    $product_description = $product->get_description();

                                    // Проверяем, что описание не пусто и является строкой JSON
                                    if (!empty($product_description) && is_string($product_description)) :
                                        // Декодируем JSON в массив
                                        $description_data = json_decode($product_description, true);

                                        // Проверяем успешность декодирования
                                        if ($description_data !== null) :
                                            // Выводим данные из массива
                                            echo  $description_data;
                                        else :
                                            // Обработка ошибки декодирования
                                            echo $product->get_description();
                                        endif;
                                    endif;

                                    ?>

                                </div> <!-- end of .product-item-description -->
                            </div> <!-- end of .description-section -->

                            <div class="description-section">
                                    <span class="title-info">Детальна інформація</span>
                                    <div class="table-info">
                                        <table>
                                            <tbody>

                                            <?php

                                            $attributes = $product->get_attributes();

                                            $linkable_taxonomies = [
                                                'pa_avtori',
                                                'pa_vik',
                                                'pa_seria-knigi'
                                            ];

                                            $attribute_map = [
                                                'pa_avtori',
                                                'pa_seria-knigi',
                                                'pa_vik',
                                                'pa_mova-vidanna',
                                                'pa_rik-vidanna',
                                                'ISBN',
                                                'pa_obkladinka',
                                                'pa_rozmir',
                                                'pa_kilkist-strinok',
                                                'pa_kod-prajsa',
                                                'Вага',
                                                'pa_standart-packi'
                                            ];

                                            foreach ($attribute_map as $item) :

                                                $attribute_slug = $item;
                                                $value_html      = '';

                                                if ( $attribute_slug === 'ISBN' ) :

                                                    $attribute_name  = 'ISBN';
                                                    $value_html      = $product->get_sku();

                                                elseif ( $attribute_slug === 'Вага' ) :

                                                    $attribute_name  = 'Вага';
                                                    $value_html      = $product->get_weight();

                                                else :
                                                    // Проверка, что слаг атрибута не равен "korotkij-opis"
                                                    if ($attribute_slug === 'pa_korotkij-opis'
                                                        || strpos($attribute_slug, 'pa_kt') === 0
                                                        || $attribute_slug === 'pa_author-book') :
                                                        continue; // Пропускаем вывод для этого атрибута
                                                    endif;

                                                    $attribute_name  = wc_attribute_label($attribute_slug, $product);
                                                    $raw_value       = $product->get_attribute($attribute_slug); // строка значений через запятую

                                                    if (taxonomy_is_product_attribute($attribute_slug) && in_array($attribute_slug, $linkable_taxonomies, true)) :

                                                        $terms = wc_get_product_terms($product->get_id(), $attribute_slug, ['fields' => 'all']);

                                                        if ( !is_wp_error($terms) && $terms ) :

                                                            $links = array_map(function($t) {
                                                                $url = get_term_link($t);

                                                                if (is_wp_error($url)) :
                                                                    return esc_html($t->name);
                                                                endif;

                                                                return '<a href="' . esc_url($url) . '">' . esc_html($t->name) . '</a>';
                                                            }, $terms);

                                                            $value_html = implode(', ', $links);
                                                        endif;

                                                    endif;

                                                    // Если не таксономия (или не удалось получить термины) — показываем как есть
                                                    if ($value_html === '') :
                                                        // Фолбэк: если это один из «кликабельных», но он кастомный текст — сделаем ссылку на поиск
                                                        if (in_array($attribute_slug, $linkable_taxonomies, true) && !empty($raw_value)) :

                                                            $search_url = add_query_arg(
                                                                ['s' => $raw_value, 'post_type' => 'product'],
                                                                home_url('/')
                                                            );
                                                            $value_html = '<a href="' . esc_url($search_url) . '">' . esc_html($raw_value) . '</a>';

                                                        else :
                                                            $value_html = esc_html($raw_value);
                                                        endif;
                                                    endif;

                                                endif;

                                                if ($value_html !== '') : ?>
                                                    <tr>
                                                        <td class="heading"><?php echo esc_html($attribute_name); ?></td>
                                                        <td class="value"><?php echo wp_kses_post($value_html); ?></td>
                                                    </tr>

                                                <?php
                                                endif;
                                            endforeach;

                                            ?>

                                            </tbody>
                                        </table>
                                    </div> <!-- end of .table-info -->
                                </div> <!-- end of .col-md-6 -->

                        </div> <!-- end of .description-and-info -->

                    </div>  <!-- end of .product-content-section -->
                <?php
                    endwhile;  ?>

                    <!--///////////////////////// Наші рекомендації /////////////////////////    -->

                <?php
                    $front_page_id = (int) get_option('page_on_front');
                    $slider = carbon_get_post_meta( $front_page_id, 'rcmnd_slider' );
                    if ( ! empty( $slider ) ): ?>
                        <div class="section">
                            <div class="container">
                                <div class="recommendations-product-page js-slick-slider">
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
                        </div>
                    <?php endif;



                $viewed_products = viewed_products();
                $viewed_products_length = count($viewed_products);
                $viewed_products_length > 4
                    ? $viewed_class_slider = 'has-more-four-slides'
                    : $viewed_class_slider = 'has-less-four-slides';

                if( $viewed_products ) : ?>
                    <div class="viewed-catalog-section js-slick-slider <?php echo $viewed_class_slider; ?>" data-entity="container-1">
                        <h2>Р<span class="color-blue">а</span>ніше пере<span class="color-orange">г</span>лянуті</h2>

                           <div class="slider slick-slider">
                               <?php

                                foreach ($viewed_products as $item) :
                                    echo so_render_product($item);
                                endforeach;

                                ?>
                           </div>

                    </div>
            <?php endif; ?>



               <?php

                // If comments are open or we have at least one comment, load up the comment template.
                if ( comments_open() || get_comments_number() ) :
                    comments_template();
                endif;
                // End of the loop.
                ?>

            </div>  <!-- end of .page-content -->

        </div>  <!-- end of .container -->

    </main><!-- #main -->

    <div class="modal fade" id="preorder" tabindex="-1" aria-labelledby="preorderLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body product-description-price-section">
                    <h3 class="text-center" id="preorderModalLabel">
                        <span class="color-blue">ВІДПРАВЛЕННЯ</span>
                        ОЧІКУЄТЬСЯ З <br>
                        <span class="color-blue"><?php echo date('d.m.Y', strtotime($custom_preorder_countdown)) ?></span>
                    </h3>
                    <!-- Форма -->
                    <div class="product-item-detail-info-container d-flex align-items-center justify-content-center">
                        <a class="product-preorder ga_buy_btn_detail product-item-detail-buy-button add_to_cart_ajx"
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