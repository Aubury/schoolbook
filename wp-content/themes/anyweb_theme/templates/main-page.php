<?php
/*
Template Name: front page
*/
// set_new_flag_after_60_days();
get_header();
?>

<div class="banner-wrap">
    <div class="banner">
        <div class="container">
            <div class="promo-slider-wrap">
                <div class="promo-slider-holder">
                <div class="promo-slider">
                    <?php
                        $first_slider = carbon_get_post_meta( $post->ID, 'first_slider' );
                        if ( ! empty( $first_slider ) ):
                            foreach ( $first_slider as $cnt => $item ):

                                echo '<div class="item">
                                        <a href="'.$item['link'].'">'.
                                                wp_get_attachment_image($item['img'], 'post-thumbnail', 'true', array( 'class' => 'desktop__img')).
                                                '<span class="mobile__img" 
                                                style="background-image: url(' . wp_get_attachment_image_url( $item['img'], 'full' ) .')"></span>
                                        </a>
    
                             </div>';

                            endforeach;
                        endif;
                    ?>
                </div>
            </div>
            </div>
        </div>
    </div>
    <div class="bottom-line-cell"></div>
</div>

<main id="primary" class="site-main main-page">

<!--////////////////////// Предзамовлення /////////////////////////-->

    <?php
        $slider = get_products_with_preorder_date();
        $length = count($slider);
        $preparing_class_slider = '';
        $length > 1 ? $preparing_class_slider = 'preparing-slider' : $preparing_class_slider = 'preparing-wrap';
        $length === 2 ? $preparing_fox = 'fox-display' : $preparing_fox = '';
        if ( !empty($slider) ) :
    ?>
    <div class="section">
        <div class="container <?php echo $preparing_class_slider . ' ' . $preparing_fox ?>">
            <div class="recommendations preparing">
                    <h2>Пер<span class="color-blue">е</span>дзамо<span class="color-orange">в</span>лення</h2>

                    <?php
                    $length = count($slider);
                    if ( $length === 1 ) :
                        $productID = $slider[0];?>
                         <div class="flex-two-column">
                             <div class="column">
                                 <?php
                                    echo so_render_product($productID);
                                ?>
                                 <div class="arrow-point-right"></div>
                             </div>
                             <div class="column-auto">
                                 <?php
                                 $custom_royalty = get_post_meta($productID, '_custom_royalty', true);
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
                                    echo '<div class="preorder-red-stroke">
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
                                                <span class="color-blue">Акція діє до: </span>'
                                                . date("d.m.Y", strtotime($custom_preorder_countdown)) .
                                           '</h3>
                                                
                                           <div class="preparing-timer">
                                                До закінчення акціі залишилось:
                                                <span id="countdown" class="color-white"></span>
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

                        }
                                 ?>

                                 <div class="arrow-preparing-up"></div>
                             </div>

                         </div>
                    <?php else: ?>
                       <div class="slider slick-slider">
                           <?php
                           foreach ( $slider as $cnt => $item ):
                               echo so_render_product($item);
                           endforeach;
                           ?>
                       </div><!-- end .slider -->
                    <?php endif; ?>

                </div><!-- end .recommendations preparing -->
            <div class="bg-preparing-wrap"></div>
        </div><!-- end .container -->
    </div>
   <?php endif; ?>

<!--/////////////// Нові надходження ////////////////////////////////-->
        <?php
            function get_products_with_new_checkbox() {
                global $wpdb;
                // Получаем ID всех товаров с метаполем '_is_new' установленным в '1'
                $product_ids = $wpdb->get_col("
                                        SELECT post_id 
                                        FROM $wpdb->postmeta 
                                        WHERE meta_key = '_is_new' 
                                        AND meta_value = '1'
                                    ");

                return $product_ids;
            }

            // Использование функции для получения массива ID товаров
            $product_ids_with_new_checkbox = get_products_with_new_checkbox();

            if ( ! empty( $product_ids_with_new_checkbox ) ):

            ?>
            <div class="section">
                <div class="container">
                    <div class="new-product">
                        <h2>Но<span class="color-blue">в</span>і надход<span class="color-orange">ж</span>ення</h2>
                        <div class="slider slick-slider">
                            <?php

                            foreach ( $product_ids_with_new_checkbox as $cnt => $item ):
                                echo so_render_product($item);
                            endforeach;


                            ?>
                        </div>
                    </div> <!-- end .new-product -->
                </div> <!-- end .container -->
            </div> <!-- end .section -->

            <?php endif; ?>
<!--///////////////////////// Ваші улюблені категорії /////////////////////////    -->
    <?php
    $favorite_categories = carbon_get_post_meta( $post->ID, 'crb_your_favorite_categories' );
    $favorite_categories_title = carbon_get_post_meta( $post->ID, 'crb_your_favorite_categories_title' );
    if ( ! empty( $favorite_categories ) ): ?>
        <div class="section">
            <div class="container">
                <div class="favorite_categories">
                    <?php
                    if ( ! empty( $favorite_categories_title ) ):
                        echo wpautop( $favorite_categories_title );
                    endif; ?>

                    <div class="flex-evenly-start-align">
                        <?php
                        foreach ( $favorite_categories as $cnt => $item ): ?>
                            <div class="product-card">
                                <div class="tow-image">
                                    <div class="first-image">
                                        <?php echo wp_get_attachment_image( $item['crb_image_first'], 'full' ); ?>
                                    </div>
                                    <div class="second-image">
                                        <?php echo wp_get_attachment_image( $item['crb_image_second'], 'full' ); ?>
                                    </div>
                                </div>
                                <div class="product-title">
                                    <h3>
                                        <?php echo $item['crb_title']; ?>
                                    </h3>
                                    <p><?php echo $item['crb_age']; ?></p>
                                </div>
                                <div class="button-to-buy">
                                    <?php $link = home_url() .  $item['crb_link']; ?>
                                    <a href="<?php echo esc_url($link);?>">
                                        <div class="text-block">
                                            Переглянути
                                            <span class="arrow-button-to-buy"></span>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

<!--///////////////////////// Наші рекомендації /////////////////////////    -->

    <?php
    $slider = carbon_get_post_meta( $post->ID, 'rcmnd_slider' );
    if ( ! empty( $slider ) ): ?>
        <div class="section">
            <div class="anm-lady"></div>
            <div class="container">
                <div class="recommendations">
                    <h2>На<span class="color-blue">ш</span>і рекоменд<span class="color-orange">а</span>ції</h2>
                    <div class="slider slick-slider">
                        <?php
                            foreach ( $slider as $cnt => $item ):
                                echo so_render_product($item['rcmnd_product_id']);
                            endforeach;
                        ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

<!-- ///////////////////////////////////////   -->
    <?php
		$visible_blog = carbon_get_post_meta( $post->ID, 'visible_blog');
        if ( $visible_blog ) : ?>
            <div class="blog-bckgr">
                <div class="container">
                    <div class="row">
                        <div class="bx-content ">
                             <div class="blog-section">
                                <p class="h2-title">
                                    Жи
                                    <span class="color-orange">в</span>
                                    е спілкув
                                    <span class="color-blue">а</span>
                                    ння/б
                                    <span class="color-orange">л</span>
                                    ог
                                </p>

                                 <div class="blog-items anim-img">
                                    <?php
                                    query_posts( 'year=' . date('Y') . '&posts_per_page=4&category_name=blog' );
                                    while (have_posts()): the_post();
                                        $views = get_post_meta( $post->ID, 'views', true );
                                        echo '<div class="item" id="bx_3485106786_16271">
                                                <div class="img">
                                                    <a href="' . get_the_permalink()  . '">'.
                                                        get_the_post_thumbnail('','slider_post',array('class' => 'post_image') ) .
                                                    '</a>
                                                    </div>
                                                    <div class="text">
                                                        <div class="date-row">
                                                            <span class="date">'.get_the_date().'</span>';
                                                                if ( $views ) :
                                                                    echo '<span class="watching">'.get_post_meta( $post->ID, 'views', true ).'</span>';
                                                                endif;
                                                    echo '</div>
                                                          <span class="name"><a href="' . get_the_permalink()  . '">' . get_the_title() . '</a></span>
                                                    </div>
                                                </div>';
                                            endwhile;
                                        wp_reset_query();
                                    ?>
                                </div><!-- end .blog-items anim-img -->

                                <div class="link-holder">
                                <a href="/blog/" class="link-more">
                                    <span>Дивитись всі публікації</span>
                                    <i class="icon-arrow"></i>
                                </a>
                            </div><!-- end .link-holder -->

                            </div><!-- end .link-holder -->
                        </div><!-- end .bx-content -->
                    </div><!-- end .bx-content -->
                </div><!-- end .row -->
            </div><!-- end .blog-bckgr-->
    <?php endif ?>


    <?php if (have_posts()) : ?>
        <div class="container">
            <?php
                while ( have_posts() ) :
                    the_post();

                    get_template_part( 'template-parts/content', 'page' );

                    // If comments are open or we have at least one comment, load up the comment template.
                    if ( comments_open() || get_comments_number() ) :
                        comments_template();
                    endif;

                endwhile; // End of the loop.
                ?>
        </div><!-- end .container -->
    <?php endif; ?>
</main> <!-- #main -->

<?php


get_sidebar();
get_footer();
