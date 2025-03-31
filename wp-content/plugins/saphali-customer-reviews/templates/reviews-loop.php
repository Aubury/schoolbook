<?php
/**
 * Review Comments Template
 *
 * Closing li is left out on purpose!
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
global $post, $s_reviews, $comment, $fild, $_name;
if( $comment->comment_approved || is_super_admin() ) :
extract($args, EXTR_SKIP);
if(!is_object($s_reviews)){
	$s_reviews = new Reviews_Saphali();
}
if(!isset($_name) && is_array($s_reviews->comment_filds)) {
	$_name = array();
	foreach($s_reviews->comment_filds as $k => $value){
		if($value['type_fild'] == 'foto' || $value['type_fild'] == 'photo') {$_name[$k] = $value['name']; } elseif($value['name'] != 'phone' && $value['name'] != 'mail' && $value['name'] != 'e-mail' && $value['name'] != 'email' && $value['name'] != 'age')
		$fild[$k] = $value['name'];
	}
}


$c_ID  = $comment->comment_ID;
if(!empty($_name)) {
	$url_photo = array();
	foreach($_name as $n)
	$url_photo[] = get_comment_meta($c_ID, $n, true);
}
if(is_array($fild))
foreach($fild as $_k => $_v) {
	if($comment_meta = get_comment_meta($c_ID, $_v, true))
	$_fild[$_v] = '<strong>' . $comment_meta. '</strong>';
}

?>
<li itemprop="reviews" itemscope itemtype="http://schema.org/Review" <?php comment_class(); ?> id="li-comment-<?php echo $comment->comment_ID ?>">
	<div id="comment-<?php echo $comment->comment_ID; ?>" class="comment_container">

		<?php 
		if(isset($url_photo)) {
			$url_ = array_shift($url_photo);
			if( !empty($url_) ) echo '<img width="120" height="120" class="avatar avatar-120 photo" src="' . $url_ . '" alt="">';
			else echo get_avatar( $GLOBALS['comment'], $size='120' );
		} else echo get_avatar( $GLOBALS['comment'], $size='120' ); ?>

		<div class="comment-text">
			<?php 
			if(isset($url_photo)) {
				foreach($url_photo as $url) if( !empty($url) ) echo '<img width="120" height="120" class="avatar avatar-120 photo" src="' . $url . '" alt="">';
			}
			?>
			<?php if ($GLOBALS['comment']->comment_approved == '0') : ?>
				<p class="meta"><em><?php _e( 'Your comment is awaiting approval', 'woocommerce' ); ?></em></p>
			<?php endif; ?>
				<div itemprop="description" class="description"><?php remove_filter("comment_text", array($s_reviews, "comment_text") ); comment_text(); add_filter("comment_text", array($s_reviews, "comment_text") ); ?></div>
				<div class="clear"></div>
				<p class="extra_filds">
				<?php if(isset($_fild['author']) || isset($_fild['name'])) echo implode(', ', $_fild  ); elseif(is_array($_fild)) echo implode(', ', array_merge( array( '<strong>'.get_comment_author( $c_ID ).'</strong>') , $_fild )  ); else  { echo '<strong itemprop="author">'; comment_author(); echo '</strong>'; } ?>
				</p>
				<div class="reply">
					<?php comment_reply_link(array_merge( $args, array('depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
				</div>
			</div>
		<div class="clear">&nbsp;</div>
	</div>
<?php endif; ?>