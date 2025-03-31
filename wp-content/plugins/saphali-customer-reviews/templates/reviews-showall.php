<?php // Do not delete these lines
global $post, $wp_query,$comments;
if ( post_password_required()  ) : ?>
<p class="nocomments">This post is password protected. Enter the password to view comments.</p>
<?php return;
endif;

		$args['post_id'] =  $post->ID;
		if(!is_super_admin())
		$args['status'] = 'approve';
		$args['orderby'] = 'comment_date';
		$args['order'] = 'DESC';
$comments = get_comments($args);
$per_page = 5;

?>
<?php
	/* This variable is for alternating comment background */
	$oddcomment = 'class="alt" ';
?> 
<!-- You can start editing here. -->
<?php if (!empty($comments)) { ?>
<div id="reviews"  class="reviews pers-list clrfix">
<?php do_action('comment_submit_after_reviews'); ?>
<div id="comments">
	<ul class="text-list">
	
	<?php 
	 wp_list_comments(array( 'callback' => array('Reviews_Saphali', 'woocommerce_comments_s_reviews'), 'per_page' => $per_page, 'page' => max( 1, get_query_var('paged') ), 'reply_text' => __('Ответить на отзыв', 'saphali-customer-reviews' ), 'reverse_top_level' => false, 'type' => 'comment' ), $comments);
	 $max_page = max( ceil( sizeof($comments) / $per_page), get_comment_pages_count() );
	?>
	</ul>
</div>
</div>
<style>#reviews .text-list {word-wrap: break-word;}</style>
 <?php } // this is displayed if there are no comments so far ?>

<div class="comments-paginate">

<?php global $wp_query; //echo '<pre>'; var_dump($wp_query);echo '</pre>';  
	if ( $max_page > 1 ) : ?>
<div class="clear"></div>
<nav class="woocommerce-pagination">
	<div class="navigation_set">	
			<?php
			$args = array(
			'base' 			=> str_replace( 999999999, '%#%', get_pagenum_link( 999999999 ) ),
			'format' 		=> '',
			'current' 		=> max( 1, get_query_var('paged') ),
			'total' 		=> $max_page,
			'prev_text' 	=> '&larr;',
			'next_text' 	=> '&rarr;',
			'type'			=> 'list',
			'end_size'		=> 3,
			'mid_size'		=> 3
		);
			echo paginate_links( $args );
			?>
	</div>
</nav>
<div class="clear"></div>
		<?php endif; ?></div>


<?php if ( comments_open() || true ) {
$comments_args = array(
        // change the title of send button 
        'label_submit'=>__('Оставить отзыв','saphali-customer-reviews'),
        // change the title of the reply section
        'title_reply'=>__('Оставить свой отзыв','saphali-customer-reviews'),
        // remove "Text or HTML to be displayed after the set of comment fields"
        'comment_notes_after' => '',
        // redefine your own textarea (the comment body)
        'comment_field' => '<p class="comment-form-comment"><label for="comment">' . _x( 'Текст отзыва', 'noun' ) . '</label><br /><textarea class="message" id="comment" name="comment" aria-required="true"  placeholder=' .__("Текст Вашего отзыва",'saphali-customer-reviews'). '></textarea></p>',
);
$req = get_option( 'require_name_email' );
$aria_req = ( $req ? " aria-required='true'" : '' );
$comments_args['fields'] = apply_filters( 'comment_form_default_fields', array(
		'author' => '<p class="comment-form-author">' . '<label for="author">' . __( 'Name' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label> ' . '<input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' placeholder="Ваше ' . __( 'Name' ) . '" /></p>',
		'email' => '<p class="comment-form-email"><label for="email">' . __( 'Email' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label> ' . '<input id="email" name="email" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . ' placeholder="Ваш ' . __( 'Email' ) . '" /></p>') );
 ?>

<div class="comment-form"><?php comment_form($comments_args); ?></div>

<?php } // if you delete this the sky will fall on your head ?>



