<?php
/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package anyweb
 */

get_header();

// function display_search_results_count() {
//     if ( is_search() ) {
//         global $wp_query;
//         $count = $wp_query->found_posts;
//         echo '<div class="search-results-count">Найдено: ' . esc_html( $count ) . ' товарів</div>';
//     }
// }

$is_aws = isset($_GET['type_aws']) && $_GET['type_aws'] === 'true';
$is_product = isset($_GET['post_type']) && $_GET['post_type'] === 'product';

// Выводим счетчик на странице поиска (может потребоваться найти подходящее место в шаблоне)
add_action( 'woocommerce_before_shop_loop', 'display_search_results_count', 10 );
function display_search_results_count() {
    if ( is_search() ) {
        global $wp_query;
        $count = $wp_query->found_posts;
        echo '<div class="search-results-count">Найдено: ' . esc_html( $count ) . ' товаров</div>';
    }
}

?>

	<main id="primary" class="site-main search-page">
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
                        if ($is_aws && $is_product) {

                            if (have_posts()) {

//                                 do_action('woocommerce_before_main_content');
//                                 do_action('woocommerce_before_shop_loop');
                                woocommerce_product_loop_start();

                                while (have_posts()) {
                                    the_post();
                                    wc_get_template_part('content', 'product');
                                }

                                woocommerce_product_loop_end();

                                the_posts_pagination([
                                    'mid_size'  => 2,
                                    'prev_text' => '⯇',
                                    'next_text' => '⯈',
                                ]);

                                do_action('woocommerce_after_shop_loop');
                                do_action('woocommerce_after_main_content');

                            } else {
                                do_action('woocommerce_no_products_found');
                            }

                            do_action('woocommerce_after_main_content');

                        } else {



                        if ( have_posts() ) : ?>

                                <header class="page-header">
                                    <h1 class="page-title">
                                        <?php
                                        /* translators: %s: search query. */
                                        printf( esc_html__( 'Search Results for: %s', 'anyweb' ), '<span>' . get_search_query() . '</span>' );
                                        ?>
                                    </h1>
                                </header><!-- .page-header -->

                                <?php
                                /* Start the Loop */
                                while ( have_posts() ) :
                                    the_post();

                                    /**
                                     * Run the loop for the search to output the results.
                                     * If you want to overload this in a child theme then include a file
                                     * called content-search.php and that will be used instead.
                                     */
                                    get_template_part( 'template-parts/content', 'search' );

                                endwhile;

                                the_posts_navigation();

                            else :

                                get_template_part( 'template-parts/content', 'none' );

                            endif;
                        }
                    ?>
                </div>
            </div>
        </div>
	</main><!-- #main -->

<?php
get_sidebar();
get_footer();

