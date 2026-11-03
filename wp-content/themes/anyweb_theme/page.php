<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package anyweb
 */

get_header();
$is_order_page = function_exists( 'is_cart' )
    && function_exists( 'is_checkout' )
    && ( is_cart() || is_checkout() );

$is_order_page ? $class = 'is_checkout' : $class = '';

?>

<main id="primary" class="site-main  <?php echo esc_attr( $class ); ?>">

    <div class="container">
        <?php
            if ( $is_order_page ) :
                $home_url = function_exists( 'pll_home_url' )
                    ? pll_home_url()
                    : home_url( '/' );
            ?>
                <div class="breadcrumb-back">
                    <a class="breadcrumb-item" href="<?php echo esc_url( $home_url ); ?>">
                         <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 11 11" fill="none">
                              <path d="M8 1L3 5.5L8 10" stroke-width="2" stroke-linecap="round" stroke="#333333"/>
                            </svg>
                         </span>
                        <span>На головну</span>
                    </a>
                </div>
            <?php

            else:

            ?>
                <div id="navigation" class="flex-row-start" itemprop="http://schema.org/breadcrumb" itemscope="" itemtype="http://schema.org/BreadcrumbList">
                    <?php if( function_exists('kama_breadcrumbs') ) kama_breadcrumbs(''); ?>
                </div>
            <?php

            endif;

            ?>

        <div class="row">
            <div class="bx-content">
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
            </div>
        </div>
    </div>
</main><!-- #main -->

<?php
get_sidebar();
get_footer();
