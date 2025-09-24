<?php
/*
Template Name: two column template
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
   <div class="bx-content ">
      <div class="content-page">
         <div class="sidebar">
            <?php
                    wp_nav_menu(
                        array(
                            'theme_location' => 'two_column_menu',
                            'menu_id'        => 'bx_hma_one_lvl',
                            'echo'           => true,
                            'container'      => 'nav',
                            'container_class' => 'nav-menu nav-menu__content-page'

                        )
                    );
            ?>

         </div>
         <div class="content content-aboutus">
         <h1 class="h2-title">
             <?php $h1_title = carbon_get_post_meta( $post->ID, 'h1_title' );
                if ( ! empty( $h1_title ) ):
                echo  wpautop($h1_title) ;
                endif;
            ?>
         </h1>

                <?php
            while ( have_posts() ) :
                the_post();

if($post->post_name == 'about' || $post->post_name == 'contacts' || $post->post_name == 'magics'){
    wp_nav_menu(
        array(
            'theme_location' => 'top_two_column_menu',
            'menu_id'        => 'top_two_column_menu',
            'echo'           => true,
            'container'      => 'nav',
            'container_class' => 'nav-menu sub-menu__about'

        )
    );
}
echo '<div class="container-fluid content-about">';
                get_template_part( 'template-parts/content', 'page' );


                echo '</div>';
	    	endwhile; 	
        ?>
            
         </div>
      </div>
   </div>
</div>





</div>
</div>
	</main><!-- #main -->
<?php
get_sidebar();
get_footer();
