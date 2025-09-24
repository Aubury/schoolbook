<?php
/*
Template Name: SiteMap
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
        <h1 class="h2-title">Мапа сайту</h1>
<div class="col-sm-12">
<?php


?>
	
    <div class="str_sitemap">
	<?php 
    // wp_list_pages('exclude=257, 22, 20, 18, 208' ); 

    $pages = get_pages( [
        'sort_order'   => 'ASC',
        'sort_column'  => 'post_title',
        'hierarchical' => 1,
        'exclude'      => '9, 257, 22, 20, 18, 208, 19, 45, 97',
        'include'      => '',
        'meta_key'     => '',
        'meta_value'   => '',
        'authors'      => '',
        'child_of'     => 0,
        'parent'       => -1,
        'exclude_tree' => '',
        'number'       => '',
        'offset'       => 0,
        'post_type'    => 'page',
        'post_status'  => 'publish',
    ] );
 
   
    foreach( $pages as $post ){

echo '
        <div class="str_sitemap_menu">
        <a href="' . esc_url(get_permalink($post)) . '">' . get_the_title($post) . '</a>
</div>';
    }
    wp_reset_postdata();
    
    $categories = get_categories();

foreach ($categories as $category) {
    $category_posts = get_posts(array(
        'post_type' => 'post',
        'numberposts' => -1,
        'category' => $category->term_id,
    ));

    $subcategories = get_categories(array(
        'parent' => $category->term_id,
    ));

    if ($category_posts || $subcategories) {



        // if ($subcategories) {
        //     echo '
        //             <div class="str_sitemap_menu">
        //             <a href="https://schoolbook.com.ua/blog/">Блог</a>
        //             <i class="fa show_but sub_dirs show_sitemap fa-minus-circle"></i>
        //             <div class="str_sitemap_hid_1" style="display: block;">
        //             ';
        //     foreach ($subcategories as $subcategory) {
        //         $subcategory_posts = get_posts(array(
        //             'post_type' => 'post',
        //             'numberposts' => -1,
        //             'category' => $subcategory->term_id,
        //         ));

        //         if ($subcategory_posts) {
        //             echo '
        //             <div class="str_sitemap_menu">
        //             <a href="' . esc_url(get_term_link($subcategory)) . '">' . esc_html($subcategory->name) . '</a>
        //             <i class="fa fa-plus-circle show_but show_but_1 sub_dirs show_sitemap"></i>'; 

        //             echo '<div class="str_sitemap_hid_2">';
        //             foreach ($subcategory_posts as $post) {
        //                 echo '
        //                 <div class="str_sitemap_menu">
        //                  <a href="' . esc_url(get_permalink($post)) . '">' . get_the_title($post) . '</a><br>
        //                 </div>';
        //             }
        //             echo '</div>
        //             </div>';
        //         }
        //     }
        //     echo ' 
        //      </div>
        //     </div>';
        // }
    }
}

    ?>
</div>





</div>
	</div>
</div>
</div>
	</main><!-- #main -->
    ';






<?php

get_sidebar();
get_footer();
