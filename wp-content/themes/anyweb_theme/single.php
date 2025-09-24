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
				<div class="blog-wrapper">
					<div class="blog-container">
						<div class="article__block">
							<?php
							while ( have_posts() ) :
								the_post();
								get_template_part( 'template-parts/content', get_post_type() );

								// If comments are open or we have at least one comment, load up the comment template.
								if ( comments_open() || get_comments_number() ) :
									comments_template();
								endif;
							endwhile; // End of the loop.
							?>
						</div>
					</div>
					<!-- ----------------------------------- -->
														<div class="blog-sidebar">
											<div class="blog-menu-holder">
												<span class="blog-menu-title">Рубрики</span>
													<ul class="blog-menu">


													<?php
													$all_categories = get_categories( array( 'parent' => 117, 'hide_empty' => 0 ));
													$category_link_array = array();
													foreach( $all_categories as $single_cat ){
														if($single_cat->cat_ID != 1 ){
															echo'<li><a href="' . get_category_link($single_cat->term_id) . '">' . $single_cat->name . '</a></li>';}
													}

													?>

															</ul>




												<span class="menu-decoration-girl"></span>
											</div>
											<div class="subs-block">
												<div class="subs-title">
													<span>Що нового?</span>
												</div>
												<div class="subscription">
													<div class="bx-subscribe">
														<div class="bx-block-title">Підписатися на наші новини</div>
														<div class="bx-subscribe" id="sender-subscribe">
									<!--'start_frame_cache_sender-subscribe'-->	
											<form id="bx_subscribe_subform_sljzMT" role="form" method="post" action="/blog/detail/ta-de-zh-tse-my-ye-retsenziya-na-novynku-pokynutyy-budynok-novy-gill/">
											<input type="hidden" name="sessid" id="sessid" value="330c38efe65b655854a26ca887492e07">		<input type="hidden" name="sender_subscription" value="add">

											<div class="bx-input-group">
												<input class="bx-form-control input" type="email" name="SENDER_SUBSCRIBE_EMAIL" value="" title="Введіть ваш e-mail" placeholder="Введіть ваш e-mail">
											</div>

											<div style="">
																	</div>

											
											<div class="bx_subscribe_submit_container">
												<button class="sender-btn btn-subscribe" id="bx_subscribe_btn_sljzMT">
												<span>OK</span>
												<!-- <span>Підписатися</span> -->
												</button>
											</div>
										</form>
									<!--'end_frame_cache_sender-subscribe'--></div>				</div>
												</div>
											</div>
										</div>
					<!-- ----------------------------------- -->
				</div>
			</div>
	</div>
</div>
	</main><!-- #main -->

<?php
get_sidebar();
get_footer();
