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

</main><!-- #main -->

<?php
get_sidebar();
get_footer();
