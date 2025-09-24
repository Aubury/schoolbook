<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
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
				<div class="blog-wrapper">
					<div class="blog-container">
							<?php
							if ( have_posts() ) :

								if ( is_home() && ! is_front_page() ) :
									?>
									<header>
										<h1 class="h2-title"><?php single_post_title(); ?></h1>
									</header>
									<div class="blog-items anim-img">
									<?php
								endif;

										/* Start the Loop */
										while ( have_posts() ) :
											the_post();
											$views = get_post_meta( $post->ID, 'views', true );
											echo '
											<div class="item" id="bx_3485106786_16271">
													<div class="img">
														<a href="' . get_the_permalink()  . '">'.
														get_the_post_thumbnail('','slider_post',array('class' => 'post_image') ) .
														'</a>
													</div>
													<div class="text">
														<div class="date-row">
															<span class="date">'.get_the_date().'</span>';
															if($views){echo '<span class="watching">'.get_post_meta( $post->ID, 'views', true ).'</span>';}
														echo '</div>
														<span class="name"><a href="' . get_the_permalink()  . '">' . get_the_title() . '</a></span>
													</div>
											</div>';


										endwhile;
								?>
									</div>
								<?php
								the_posts_navigation();

							else :

								get_template_part( 'template-parts/content', 'none' );

							endif;
							?>
					</div>
					<div class="blog-sidebar">
						<div class="blog-menu-holder">
							<span class="blog-menu-title">Рубрики</span>

							<?php
										$taxonomy = 'category'; // Замените на нужный таксономию, если это не категории записей.

										$categories = get_terms(array(
											'taxonomy' => $taxonomy,
											'hide_empty' => false,
										));

										echo '<ul class="blog-menu">';
										foreach ($categories as $category) {
											if ($category->slug !== 'blog' && $category->slug !== 'dflt') {
												$category_link = get_term_link($category);
												echo '<li><a href="' . esc_url($category_link) . '">' . $category->name . '</a></li>';
											}
										}
										echo '</ul>';
										?>

									<!-- <ul class="blog-menu"><li><a href=""></a></li><li><a href="">Анонси</a></li><li class="active"><a href="">Цікаво знати</a></li><li><a href="">Огляди</a></li><li><a href="">Відео</a></li></ul> -->
							<span class="menu-decoration-girl"></span>
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
