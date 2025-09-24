<?php
/**
 * The template for displaying comments
 *
 * This is the template that displays the area of the page that contains both the current comments
 * and the comment form.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package anyweb
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() ) {
	return;
}

$product = wc_get_product($post->ID);
?>

<div id="comments" class="reviews-section">
<div class="row">
	<div class="col-md-12">
	<div class="reviews-column">
		<div class="reviews-wrap">
			<?php
			// You can start editing here -- including this comment!
			if ( have_comments() ) :
				?>
			<div class="review-rate-title">Середня оцiнка <?php echo  $rating_count = $product->get_rating_count() ?></div>
				<?php the_comments_navigation(); ?>
				<div class="reviews-body">
						<?php
						wp_list_comments(
							array(
								'style'      => 'div',
								'short_ping' => true,
								'avatar_size' => false,
								'calback' => 'mytheme_comment',
							)
						);
						?>
				</div><!-- .comment-list -->
				<?php
				the_comments_navigation();
				// If comments are closed and there are comments, let's leave a little note, shall we?
				if ( ! comments_open() ) :
					?>
					<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'anyweb' ); ?></p>
					<?php
				endif;
			endif; // Check for have_comments().
			?>
		</div>
	</div>

<div class="review-form-column">
	<?php
	comment_form();
	?>
</div>
</div>
</div>
</div><!-- #comments -->
