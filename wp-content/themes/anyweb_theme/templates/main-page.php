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

<main id="primary" class="site-main">
    <div class="anm-lady"></div>
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
<!--/////////////////////////////////////////////////////////////////////    -->

<!--///////////////////////// Наші рекомендації /////////////////////////    -->

    <?php
    $slider = carbon_get_post_meta( $post->ID, 'rcmnd_slider' );
    if ( ! empty( $slider ) ): ?>
        <div class="section">
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

<!--/////////////////////////////////////////////////////////////////////-->

    <div class="container">
        <?php
            $slider = get_products_with_preorder_date();
            if ( !empty($slider) ) :?>
            <div class="recommendations preparing">
                <p class="h2-title">Пер<span class="color-blue">е</span>дзамо<span class="color-orange">в</span>лення</p>
                <div class="slider slick-slider">
                    <?php
                        foreach ( $slider as $cnt => $item ):
                            echo so_render_product($item);
                        endforeach;
                    ?>
                </div><!-- end .slider -->
            </div><!-- end .recommendations preparing -->
        <?php endif; ?>
    </div><!-- end .container -->

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

    <div class="container">
        <div class="row">
             <div class="bx-content ">
                <div class="desc-section">
                    <p class="desc-title">
                        <?php
                            $btm_title = carbon_get_post_meta( $post->ID, 'btm_title' );
                            if ( ! empty( $btm_title ) ):
                                echo  $btm_title;
                            endif;
                        ?>
                    </p>
                    <div class="desc-text-holder">
                            <div class="desc-text">
                                <?php
                                    $btm_txt = carbon_get_post_meta( $post->ID, 'btm_txt' );
                                    if ( ! empty( $btm_txt ) ):
                                        echo  $btm_txt;
                                    endif;
                                 ?>
                            </div><!-- end .desc-text -->

                            <div class="expanded">
                                <?php
                                    $btm_txt_hide = carbon_get_post_meta( $post->ID, 'btm_txt_hide' );
                                    if ( ! empty( $btm_txt_hide ) ):
                                        echo  $btm_txt_hide;
                                    endif;
                                ?>
                            </div><!-- end .expanded -->

                            <?php
                                if ( $btm_txt_hide ) :
                                    echo '<a href="javascript:void(0)" class="more-text"><span>Детальніше</span><i class="icon-arrow"></i></a>';
                                endif;
                            ?>
                        </div><!-- end .desc-text-holder -->
                </div><!-- end .desc-section -->
                <br>
		     </div><!-- end .bx-content-->
		</div><!-- end .row-->
	</div><!-- end .container-->

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
</main> <!-- #main -->

<?php
get_sidebar();
get_footer();
