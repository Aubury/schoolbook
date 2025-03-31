<?php 
/*
Plugin Name: Saphali Customer Reviews
Plugin URI: http://saphali.com
Description: Позволяет оставлять пользователям отзывы на сайте
Version: 1.0.4
Author: Saphali
Author URI: http://saphali.com
*/
if(!defined('SAPHALI_PLUGIN_VERSION_REVIEWS'))
define('SAPHALI_PLUGIN_VERSION_REVIEWS', '1.0.4');
class Reviews_Saphali {
	var $menu_id;
	var $plugin_url;
	var $plugin_dir;
	var $comment_filds;

	function __construct() {
		
		$this->plugin_url = plugins_url( basename( plugin_dir_path(__FILE__) ), basename( __FILE__ ) );
		$this->plugin_dir = plugin_dir_path(__FILE__);
		add_action('comment_form_after_fields', array($this, 'comment_form') );
		add_action('comment_form_logged_in_after', array($this, 'comment_form') );
		add_action('admin_menu', array($this, 'adminMenu') );
		add_action('admin_init', array($this, 'seve_option') );
		
		add_action("manage_comments_custom_column", array($this, "my_custom_columns") , 10, 2);
		add_filter("manage_edit-comments_columns", array($this, "my_website_columns") );
		add_filter("comment_text", array($this, "comment_text") );
		add_action('comment_post', array($this, 'comment_post'), 0 );
		add_action('widgets_init',  array($this, 'woocommerce_unregister_widgets') );
		$this->comment_filds = get_option("saphali_reviews_option", array());
		add_action( 'init', array($this,'reviews_load_textdomain' ));
		add_shortcode('all_reviews_saphali', array($this, 'shortcode') );		
		add_action('set_comment_cookies',  array($this, 'set_comment_cookies'), 10, 2 );
		add_action('comment_submit_after_reviews',  array($this, 'comment_submit_after_reviews') );

		// add_action( 'wp_enqueue_scripts', array($this, 'frontend_scripts') );
	}
	
 	function set_comment_cookies($comment, $user) {
		if(!session_id()) {
			@session_start();
		}
		if( isset( $_POST['saphali_reviews'] ) ) {
			$_SESSION['comment_submit'] = 1;
		}
	}

 	function comment_submit_after_reviews() {
		if(!session_id()) {
			@session_start();
		}
		if( isset( $_SESSION['comment_submit'] ) ) {
			echo '<div class="woocommerce"><div class="woocommerce-message">'. 'Ваше сообщение было отправлено на рассмотрение.' . '</div></div>';
			unset( $_SESSION['comment_submit'] );
		}
	}
	
	function reviews_load_textdomain() {
	  load_plugin_textdomain( 'saphali-customer-reviews', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
	}
 	function shortcode() {
		global $comments;
		
		return do_shortcode($this->saphali_action_shortcode());
	}
	function comment_text($content) {
		global $s_reviews, $comment;
		$_name = array();
		if(!is_object($s_reviews)){
			$s_reviews = new Reviews_Saphali();
		}
		if(!isset($fild) && is_array($s_reviews->comment_filds) )
		foreach($s_reviews->comment_filds as $k => $value){
			if($value['type_fild'] == 'foto') {$_name[$k] = $value['name']; } elseif($value['name'] != 'phone' && $value['name'] != 'mail' && $value['name'] != 'e-mail' && $value['name'] != 'email' && $value['name'] != 'age')
			$fild[$k] = $value['name'];
		}

		$c_ID  = $comment->comment_ID;
		if(!empty($_name)) {
			$url_photo = array();
			foreach($_name as $n)
			$url_photo[] = get_comment_meta($c_ID, $n, true);
		}
		
		if(isset($fild) && is_array($fild))
		foreach($fild as $_k => $_v) {
			if($comment_meta = get_comment_meta($c_ID, $_v, true))
			$_fild[$_v] = '<strong>' . $s_reviews->comment_filds[$_k]['title'] . ':</strong> ' . $comment_meta. '';
		}
		if(isset($url_photo)) {
			foreach($url_photo as $url) if( !empty($url) ) echo '<img width="120" height="120" class="avatar avatar-120 photo" src="' . $url . '" alt="">';
		}
		if(isset($_fild) && is_array($_fild))
		$content .=  '<br />' . @implode('<br /> ',  $_fild   );
		return $content;
	}
	function saphali_action_shortcode() {
		remove_all_actions ( '__before_content' );
		global $post, $wp_query, $comments;
		ob_start();
		$this->get_template_part('reviews', 'showall');
		query_posts(array('post_name'=> -1, 'post_type' => 1));
		?><style type="text/css">nav.woocommerce-pagination ul.page-numbers li a {background: none repeat scroll 0 0 #FFFFFF;color: black;padding: 3px;}nav.woocommerce-pagination ul li a, nav.woocommerce-pagination ul.page-numbers li a, nav.woocommerce-pagination ul li span {display: block;font-size: 1em;font-weight: normal;line-height: 1em;margin: 0;min-width: 1em;padding: 0.5em;text-decoration: none;}nav.woocommerce-pagination ul li {border-right: 1px solid #2C88B4;display: inline;float: left;margin: 0;overflow: hidden;padding: 0;}nav.woocommerce-pagination ul.page-numbers li a:focus, nav.woocommerce-pagination ul.page-numbers li span.current, nav.woocommerce-pagination ul.page-numbers li a:hover {background: none repeat scroll 0 0 #64ADDD;color: white;}nav.woocommerce-pagination ul.page-numbers li {border-right: medium none;height: auto;margin-right: 2px;}nav.woocommerce-pagination {border-top: 1px solid #469DD3;text-align: left;}.pers-list .text-list {border-bottom: 1px solid #AEAEAF;list-style: none outside none;padding: 0;}.pers-list .even {background: none repeat scroll 0 0 #F5F5F5;}.text-list li {padding: 7px !important; list-style: none outside none !important;float: none !important;}li.comment img.avatar { position: static; height: auto;float: left;padding: 0;width: 130px; margin: 0 10px 5px 0;}.reviews p {font-style: italic;}.comment-text p strong {font-family: arial,Trebuchet MS;font-style: normal;}.comment-text div.description, .comment-text div.description p {clear: none;} p.extra_filds {text-align: right; padding-right: 10px;}.pers-list li.odd {background: none repeat scroll 0 0 #fff;border-bottom:1px dashed #D2D1D1;border-top:1px dashed #D2D1D1;}.pers-list li:last-child {border-bottom: medium none !important;}</style><?php
		$content = ob_get_clean();
		//add_action('loop_end', array($this, 'fix_comment_loop'), 0);
		return $content;
		
	}
	function fix_comment_loop() {
		wp_reset_query();
	}
	function woocommerce_comments_s_reviews( $comment, $args, $depth ) {
		global $s_reviews;
		$GLOBALS['comment'] = $comment;
		include(plugin_dir_path(__FILE__) . "templates/reviews-loop.php");
	}
	function get_template_part( $slug, $name = '' ) {
		global $woocommerce;
		$template = '';

		// Look in yourtheme/slug-name.php and yourtheme/saphali_reviews/slug-name.php
		if ( $name )
			$template = locate_template( array ( "{$slug}-{$name}.php", "saphali_reviews/{$slug}-{$name}.php" ) );

		// Get default slug-name.php
		if ( !$template && $name && file_exists( $this->plugin_dir . "templates/{$slug}-{$name}.php" ) )
			$template = $this->plugin_dir . "templates/{$slug}-{$name}.php";

		// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/saphali_reviews/slug.php
		if ( !$template )
			$template = locate_template( array ( "{$slug}.php", "saphali_reviews/{$slug}.php" ) );

		if ( $template )
			load_template( $template, false );
	}
	function woocommerce_unregister_widgets() {
		register_widget('Reviews_saphali_widget');
	}
	function my_custom_columns($column_name , $comment )
	{
		
		foreach($this->comment_filds as $value) {
			if($column_name != $value["name"]) continue;
			if ($value['type_fild'] == 'foto') {
				?> <img src="<?php echo get_comment_meta($comment, $value["name"], true); ?>" style="max-width: 107px; max-height: 107px;" /> <?php
			} elseif ($value['type_fild'] == 'checkbox') {
				$r = get_comment_meta($comment, $value["name"], true);
				if ( $r == 1 ) echo 'Да'; elseif($r == 2) echo 'Нет';
			} elseif($value['type_fild'] == 'text') {
				echo get_comment_meta($comment, $value["name"], true);
			}
		}
	}

	function my_website_columns($columns)
	{
		foreach($this->comment_filds as $value) {
			if($value["see_comment"])
				$columns[$value["name"]] = $value['title'];
		}
		return $columns;
	}
	function comment_post($comment_id) {
		$this->check_required_fields();
		if(is_array($_POST['saphali_reviews']))
		foreach ($_POST['saphali_reviews'] as $_k => $field_name) {
			foreach ($field_name as $k => $_field_name) {
				if( !empty($_field_name) ) {
					if(!update_comment_meta($comment_id, $k, $_field_name))
						add_comment_meta($comment_id, $k, $_field_name);
				}
			}
		}
	}
	function check_required_fields() {
		$error = array();
		if(is_array($_POST['saphali_reviews']))
		foreach ($_POST['saphali_reviews'] as $_k => $field_name) {
			foreach ($field_name as $k => $_field_name) {
				if( $this->comment_filds[$_k]['r'] && empty($_field_name) ) { $error[] = $this->comment_filds[$_k]['title']; }
			}
		}
		if(sizeof($error) != 0){
			if(sizeof($error) == 1) $e = __('Обязательное поле:','saphali-customer-reviews'); else $e = __('Обязательные поля:','saphali-customer-reviews');
			$error_desc = __('Ошибка! <br /> ','saphali-customer-reviews').$e.' '.implode(", ", $error).'.';
			wp_die($error_desc);
		}

	}
	function seve_option() {
		if ( ! current_user_can( 'manage_options' ) )
			return;
		
		if(isset($_POST['saphali_reviews_option'])) {
			if(empty($_POST['fildcomment']) && get_option("saphali_reviews_option", false) ) {
				delete_option("saphali_reviews_option");
			} else {
				foreach($_POST['fildcomment']["name"] as $key => $value) {
					if(empty($value)) continue;
					
					foreach($_POST['fildcomment'] as $_key => $_value) {
						if(empty($_POST['fildcomment']["post_type"][$key])) {
						  $_POST['fildcomment']["post_type"][$key] = array('all'); $_POST['fildcomment']["page_id"][$key] = '';
						} elseif( !in_array('page', $_POST['fildcomment']["post_type"][$key]) || sizeof($_POST['fildcomment']["post_type"][$key]) > 1 ) {
							$_POST['fildcomment']["page_id"][$key] = '';
						}
						
						$_data[$_POST['fildcomment']["order"][$key]] = array(
							'name'      => $_POST['fildcomment']["name"][$key],
							'title'     => $_POST['fildcomment']["title"][$key],
							'post_type' => $_POST['fildcomment']["post_type"][$key],
							'type_fild' => $_POST['fildcomment']["type_fild"][$key],
							'r'   => $_POST['fildcomment']["r"][$key],
							'see_comment'   => $_POST['fildcomment']["see_comment"][$key],
							'page_id'   => $_POST['fildcomment']["page_id"][$key],
						);
						
					}
				}
			}
			if(!update_option("saphali_reviews_option", $_data)) add_option("saphali_reviews_option", $_data);
		}
	}
    function frontend_scripts() {
		
	}
    function admin_scripts() {
		wp_enqueue_script( 's-chosen', $this->plugin_url . '/chosen/chosen.jquery.min.js', array( 'jquery' ), SAPHALI_PLUGIN_VERSION_REVIEWS, true );
		wp_enqueue_script( 's-chosen', $this->plugin_url . '/chosen/chosen.proto.min.js', array( 'jquery' ), SAPHALI_PLUGIN_VERSION_REVIEWS, true );
		wp_enqueue_script( 's-reviews-tb', $this->plugin_url . '/js/jquery.tablednd.0.5.js', array( 'jquery' ), SAPHALI_PLUGIN_VERSION_REVIEWS, true );
		wp_enqueue_style( 's-chosen', $this->plugin_url . '/chosen/chosen.css' );
	}
    function adminMenu() {
		if ( function_exists('add_menu_page') ) {
			$this->menu_id = add_menu_page( __('Отзывы','saphali-customer-reviews'), __('Отзывы - Настройка','saphali-customer-reviews'), 'manage_options' ,'comment-config', array($this,'configPage') , plugins_url('saphali-customer-reviews/images/menu.png'));
			add_action( 'admin_print_scripts-' . $this->menu_id, array($this, 'admin_scripts') );
		}
	}
	function configPage () {
		$args=array(
		 // 'public'   => true,
		  '_builtin' => true
		);
		$output = 'names'; // names or objects, note names is the default
		$operator = 'and'; // 'and' or 'or'
		?>
		<h3><?php _e('Отзывы - Настройка','saphali-customer-reviews'); ?></h3>
		<form method="post" action="">
		<table class="wp-list-table widefat filds_comment">
			<thead>
			<tr>
				<th><?php _e('Наименование поля','saphali-customer-reviews'); ?><br />(<span class="description"><?php _e('только латиница, цифры, _,-','saphali-customer-reviews'); ?></span>)</th>
				<th><?php _e('Заголовок','saphali-customer-reviews'); ?></th>
				<th><?php _e('Выводить в следующих типах записей','saphali-customer-reviews'); ?></th>
				<th><?php _e('Тип поля','saphali-customer-reviews'); ?></th>
				<th class="on_page_comment"><?php _e('Для страницы','saphali-customer-reviews'); ?></th>
				<th><?php _e('Обязательно','saphali-customer-reviews'); ?></th>
				<th><?php _e('Отображать в комментариях','saphali-customer-reviews'); ?></th>
				<th><?php _e('Удалить','saphali-customer-reviews'); ?></th>
			</tr>
			</thead>
			<tbody id="the-list" class="myTable">
			<?php 
			$this->comment_filds = get_option("saphali_reviews_option", array());
			if(!empty($this->comment_filds)) { foreach($this->comment_filds as $key => $value) {
				$_pages = str_replace("name='page_id'", 'name="fildcomment[page_id]['.$key.']" class="chzn-select"', wp_dropdown_pages( array('echo' => 0, 'selected' => $value['page_id'] ) ));
				$_pages = str_replace("id='page_id'>",  'data-placeholder="' . __('Выбрать страницу','saphali-customer-reviews'). '..." ><option value="">' . __('Выбрать страницу','saphali-customer-reviews'). '</option>', $_pages);	
			?>
			<tr>
				<td><input value="<?php echo $value['name'];?>" type="text" id="fild_name_comment" name="fildcomment[name][<?php echo $key; ?>]" /></td>
				<td><input value='<?php echo $value['title'];?>' type="text" name="fildcomment[title][<?php echo $key; ?>]" /></td>
				<td>
					<select data-placeholder="Во всех..." style="width:150px;" name="fildcomment[post_type][<?php echo $key; ?>][]" multiple="multiple" class="chzn-select post_type"><?php 
					$post_types=get_post_types('',$output); 
					foreach ($post_types as $post_type ) {
						if('attachment' == $post_type) continue;
						elseif('revision' == $post_type) continue;
						elseif('nav_menu_item' == $post_type) continue;
						if(in_array($post_type, $value['post_type'])) $select = ' selected="selected" '; else $select = '';
					  echo '<option '.$select.' value="'.$post_type.'">'. $post_type. '</option>';
					} $count = $key;
					?></select>
					<input id="order_count" rel="sort_order" type="hidden" name="fildcomment[order][<?php echo $key; ?>]" value="<?php echo $count?>" />
					
				</td>
				<td>
				<select data-placeholder="Выбрать тип..." style="width:100px;" name="fildcomment[type_fild][<?php echo $key; ?>]" class="chzn-select"><?php
					$type_fild = array('text', 'foto', /*'file', 'select', 'radio',*/ 'checkbox');
					foreach ($type_fild as $type ) {
					  if($value['type_fild'] == $type) $select = ' selected="selected" '; else $select = '';
					  echo '<option '.$select.' value="'.$type.'">'. $type. '</option>';
					}
				?></select></td>
				<td class="on_page_comment">
					<?php echo $_pages; ?>
				</td>
				<td><input value='1' <?php checked($value['r'], 1); ?> type="checkbox" name="fildcomment[r][<?php echo $key; ?>]" /></td>
				<td><input value='1' <?php checked($value['see_comment'], 1); ?> type="checkbox" name="fildcomment[see_comment][<?php echo $key; ?>]" /></td>
				<td><button class='delete'> <?php _e('Удалить','saphali-customer-reviews'); ?> </button></td>
			</tr>
			<?php }
			} else {
				$pages = str_replace("name='page_id'", 'name="fildcomment[page_id][0]" class="chzn-select"', wp_dropdown_pages( array('echo' => 0) ));
				$pages = str_replace("id='page_id'>",  'data-placeholder="' . __('Выбрать страницу','saphali-customer-reviews'). '..." ><option value="">' . __('Выбрать страницу','saphali-customer-reviews'). '</option>', $pages);			
?>
			<tr>
				<td><input value="" type="text" id="fild_name_comment" name="fildcomment[name][0]" /></td>
				<td><input value='' type="text" name="fildcomment[title][0]" /></td>
				<td>
					<select data-placeholder="<?php _e('Выбрать типы','saphali-customer-reviews'); ?>..." style="width:150px;" name="fildcomment[post_type][0][]" multiple="multiple" class="chzn-select post_type"><?php 
					$post_types=get_post_types('',$output); 
					foreach ($post_types as $post_type ) {
						if('attachment' == $post_type) continue;
						elseif('revision' == $post_type) continue;
						elseif('nav_menu_item' == $post_type) continue;
					  echo '<option value="'.$post_type.'">'. $post_type. '</option>';
					} $count = 0;
					?></select>
					<input id="order_count" rel="sort_order" type="hidden" name="fildcomment[order][0]" value="<?php echo $count?>" />
					
				</td>
				<td>
				<select data-placeholder="Выбрать типы..." style="width:100px;" name="fildcomment[type_fild][0]" class="chzn-select"><?php
					$type_fild = array('text', 'foto', /*'file', 'select', 'radio',*/ 'checkbox');
					foreach ($type_fild as $type ) {
					  echo '<option value="'.$type.'">'. $type. '</option>';
					}
				?></select></td>
				<td class="on_page_comment">
					<?php echo $pages; ?>
				</td>
				<td><input value='1' type="checkbox" name="fildcomment[r][0]" /></td>
				<td><input value='1' type="checkbox" checked="checked" name="fildcomment[see_comment][0]" /></td>
				<td><button class='delete'><?php _e('Удалить','saphali-customer-reviews'); ?> </button></td>
			</tr>
			<?php
			} 
			$pages = str_replace("name='page_id'", 'name="fildcomment[page_id][]" class="chzn-select"', wp_dropdown_pages( array('echo' => 0) ));
			$pages = str_replace("id='page_id'>",  'data-placeholder="' . __('Выбрать страницу','saphali-customer-reviews'). '..." ><option value=""> ' . __('Выбрать страницу','saphali-customer-reviews'). '</option>', $pages);
			?>
			</tbody>
			<tfoot>
			<tr>
				<td colspan="6">
					<div id="add" class="button-primary"><?php _e('Добавить','saphali-customer-reviews'); ?></div>
					<input type="hidden" name="saphali_reviews_option" value="1" />
				</td>
			</tr>
			</tfoot>
		</table>
		<div class="clear"></div>
		<p><button class="button-primary"><?php _e('Сохранить','saphali-customer-reviews'); ?></button></p>
		</form>
		<script type="text/javascript">
		<?php 	
		$pages = str_replace("'", '"', $pages );
		$pages = str_replace("\n", "\\\n", $pages ); 
		?>
		jQuery(function($){
			$('button.button-primary').click(function() {$(this).submit();});
			$('body').on("click", '.delete', function(event) {
				event.preventDefault();
				if(confirm("Вы действительно желаете удалить?"))
				$(this).parent().parent().remove();
			});
			$("input#fild_name_comment").keypress(function(event) {
				if ( event.which == 13 ) {
					event.preventDefault();
				}
				var text = $(this).val();
				var h = jQuery(this).val();
				var	f = text.replace(/[^a-zA-Z0-9_-]+/, '');
				$(this).val(f);
			});
			$("input#fild_name_comment").blur(function(event) {
				var text = $(this).val();
				var h = jQuery(this).val();
				var	f = text.replace(/[^a-zA-Z0-9_-]+/, '');
				$(this).val(f);
			});
			var on_change = true;
			$("select.chzn-select.post_type").change(function(){
				on_change = true;
				$("select.post_type").each(function(i,e) {change_comm($(this));});
				if(on_change) {
					$(".on_page_comment").each( function(i,e){
						$(this).hide('slow').find('div, select').css("visibility", 'visible'); 
					}); 
				}
			});
			function change_comm(obj){
				if( obj.val() != 'page' ) {
					$(".on_page_comment").each(function(i,e){
						//if($(this).parent().find('td select.post_type').val() != 'page' || $(this).parent().find('th.on_page_comment').attr('class') == 'on_page_comment')
						if($(this).parent().find('th.on_page_comment').css('display') != 'table-cell' && $(this).css('display') != 'table-cell')
						$(this).hide('slow').find('div, select').css("visibility", 'visible');
					});
				} else {
					if(on_change)
					on_change = false;
					$(".on_page_comment").each(function(i,e){
						if($(this).parent().find('td select.post_type').val() == 'page' || ($(this).parent().find('th.on_page_comment').attr('class') == 'on_page_comment'))
						{
							$(this).show('slow').find('div, select').css("visibility", 'visible');
						}
						else {
							if($(this).parent().find('th.on_page_comment').css('display') != 'table-cell')
							$(this).show('slow').find('div, select').css("visibility", 'hidden');
						}
					});
				}
			}
			$('body').on('click', ".chzn-choices", function(){$(this).trigger('change');});
			$('body').on("click", '#add', function() {
				var obj = $(this).parent().parent().parent();
				$(".filds_comment tbody tr:last").after('\
				<tr>\
					<td><input value="" type="text" id="fild_name_comment" name="fildcomment[name]['+obj.parent().find('tbody tr td input#order_count').length+']" /></td>\
					<td><input value="" type="text" name="fildcomment[title]['+obj.parent().find('tbody tr td input#order_count').length+']" /></td>\
					<td>\
						<select data-placeholder="Выбрать типы..." style="width:150px;" name="fildcomment[post_type]['+obj.parent().find('tbody tr td input#order_count').length+'][]" multiple="multiple" class="chzn-select post_type">\<?php 
						$post_types=get_post_types('',$output); 
						foreach ($post_types as $post_type ) {
							if('attachment' == $post_type) continue;
							elseif('revision' == $post_type) continue;
							elseif('nav_menu_item' == $post_type) continue;
						  echo '<option value="'.$post_type.'">'. $post_type. '</option>\\';
						}
						?></select>\
						<input id="order_count" rel="sort_order" type="hidden" name="fildcomment[order]['+obj.parent().find('tbody tr td input#order_count').length+']" value="'+(parseInt(obj.parent().find('tbody tr td input#order_count:last').val(),10)+1)+'" />\
					</td>\
					<td>\
					<select data-placeholder="Выбрать типы..." style="width:100px;" name="fildcomment[type_fild]['+obj.parent().find('tbody tr td input#order_count').length+']" class="chzn-select">\<?php
						$type_fild = array('text', 'foto', /*'file', 'select', 'radio',*/ 'checkbox');
						foreach ($type_fild as $type ) {
						  echo '<option value="'.$type.'">'. $type. '</option>\\';
						}
					?></select></td>\
					<td class="on_page_comment">\
					<?php
					 echo $pages; 
					?></td>\
					<td><input value="1" type="checkbox" name="fildcomment[r]['+obj.parent().find('tbody tr td input#order_count').length+']" /></td>\
					<td><input value="1" type="checkbox" checked="checked" name="fildcomment[see_comment]['+obj.parent().find('tbody tr td input#order_count').length+']" /></td>\
					<td><button class="delete">Удалить </button></td>\
				</tr>\
				');
				$("input#fild_name_comment").keypress(function(event) {
					if ( event.which == 13 ) {
						event.preventDefault();
					}
					var text = $(this).val();
					var h = jQuery(this).val();
					var	f = text.replace(/[^a-zA-Z0-9_-]+/, '');
					$(this).val(f);
				});
				$("input#fild_name_comment").blur(function(event) {
					var text = $(this).val();
					var h = jQuery(this).val();
					var	f = text.replace(/[^a-zA-Z0-9_-]+/, '');
					$(this).val(f);
				});
				jQuery("select.chzn-select").chosen();
				
				$("select.chzn-select.post_type").each(function(i,e) {$(this).trigger('change');});
				
				jQuery(".myTable").tableDnD({
					onDragClass: "sorthelper",
					onDrop: function(table, row) {
						var data = new Object();
						data.data = new Object();
						data.key = jQuery(table).find("tr td input").attr("rel");
						jQuery(row).fadeOut("fast").fadeIn("slow");   
						jQuery(table).find("tr").each(function(i, e){
							var id = jQuery(e).find("td input#order_count").attr("id");
							data.data[i] = id;
							jQuery(e).find("td input#order_count").val(i);
						});
					}
				});
				
			});
			$(".chzn-select").chosen();
			jQuery(".myTable").tableDnD({
				onDragClass: "sorthelper",
				onDrop: function(table, row) {
					var data = new Object();
					data.data = new Object();
					data.key = jQuery(table).find("tr td input").attr("rel");
					jQuery(row).fadeOut("fast").fadeIn("slow");   
					jQuery(table).find("tr").each(function(i, e){
						var id = jQuery(e).find("td input#order_count").attr("id");
						data.data[i] = id;
						jQuery(e).find("td input#order_count").val(i);
					});
				}
			});
			$("select.chzn-select.post_type").each(function(i,e) {$(this).trigger('change');});
			
		});
		</script>
		<?php 
	}
	function comment_form() {
		global $post;

		$post_type = get_post_type( $post->ID );
		if(isset($this->comment_filds) && is_array($this->comment_filds))
		foreach ($this->comment_filds as $key => $field) {
			if( empty($field['page_id']) ) {
				$is_page = true;
			} else {
				if(is_page($field['page_id'])) $is_page = true; else $is_page = false;
			}
			if(!(in_array($post_type, $field['post_type']) || $field['post_type'][0] == 'all') || !$is_page) continue;
			if($field['r'] == 1) 
				$required = '<span class="required"> *</span> ';
			else
				$required = '';
			if($field['type_fild'] == 'text') {
				$fild = '<input type="text" size="30" value="" name="saphali_reviews['.$key.']['.$field['name'].']" id="'.$field['name'].'">';
				echo '<p class="p_'.$field['name'].'"><label for="'.$field['name'].'">'.__($field['title']).$required.'</label>'.$fild.'</p>';
			} elseif($field['type_fild'] == 'select') {

			} elseif($field['type_fild'] == 'checkbox') {
				$fild = '<input type="checkbox" size="30" value="1" name="saphali_reviews['.$key.']['.$field['name'] . 2 . ']" id="'.$field['name'].'2">';
				$fild .= '<input type="hidden" size="30" value="2" name="saphali_reviews['.$key.']['.$field['name'].']" id="'.$field['name'].'">';
				echo '<p class="p_'.$field['name'].'">'.$fild.' <label for="'.$field['name'].'">'.__($field['title']).$required.'</label>'.'</p>
				<script>
				jQuery("body").delegate("#'.$field['name'].'2, label[for=\\"'.$field['name'].'\\"]", "click", function(){
					if( jQuery("#'.$field['name'].'2").is(":checked") ) {
						jQuery("#'.$field['name'].'").val(1);
					} else {
						jQuery("#'.$field['name'].'").val(2);
					}
				});
				</script>
				
				';
			} elseif($field['type_fild'] == 'foto') {
				if( isset($index) ) $index++; else $index = 0;
				echo '<p class="p_'.$field['name'].'"><label for="'.$field['name'].'">'.__($field['title']).$required.'</label>'. '</p>	<div id="foto_div' . $index . '" class="iframe" >
				<input type="hidden" id="foto'. $index.'" name="saphali_reviews['.$key.']['.$field['name'].']" value="" /><input type="hidden" name="act" value="comment" />
					<iframe name="saphali_reviews['.$key.']['.$field['name'].']" id="'.$field['name'].'" style="border: none; width:355px; height: 45px;" frameborder="0" marginheight="0" marginwidth="0" scrolling="no" src="'.$this->plugin_url. '/upload/index.php?img=foto' . $index . '&nonce=mktnonce" ></iframe>
				</div>';
			} else {
				$fild = '<input type="text" size="30" value="" name="saphali_reviews['.$key.']['.$field['name'].']" id="'.$field['name'].'">';
				echo '<p class="p_'.$field['name'].'"><label for="'.$field['name'].'">'.__($field['title']).$required.'</label>'.$fild.'</p>';
			}
		}
	}
	static function install() {
		$f = "[all_reviews_saphali]";
		$taxCheck = get_option('all_reviews_saphali', false);
		if (! is_page($taxCheck) ) {
			$page_data = array(
				'post_status' => 'publish',
				'post_type' => 'page',
				'post_author' => 1,
				'post_name' => 'all-reviews',
				'post_title' => 'Отзывы клиентов',
				'post_content' => '[all_reviews_saphali]',
				'comment_status' => 'open'
			);
			$publicwishlistpage_id = wp_insert_post($page_data);
			update_option("all_reviews_saphali", $publicwishlistpage_id);
		}
		
	}
	
}
add_action('plugins_loaded', 'saphali_s_reviews');
global $s_reviews;
function saphali_s_reviews() {
	global $s_reviews;
	$s_reviews = new Reviews_Saphali();
}
register_activation_hook( __FILE__, array('Reviews_Saphali', 'install') );
class Reviews_saphali_widget extends WP_Widget {
	var $woo_widget_cssclass;
	var $woo_widget_description;
	var $woo_widget_idbase;
	var $woo_widget_name;
	var $comment_filds;
	var $is_comment;
	function __construct() {
		global $s_reviews;
		$this->is_comment = false;
		/* Widget variable settings. */
		$this->woo_widget_cssclass = 'widget_saphali_comment';//widget_product_search
		$this->woo_widget_description = __( 'Отобразить отзывы.', 'saphali-customer-reviews' );
		$this->woo_widget_idbase = 'saphali_comment';
		$this->woo_widget_name = __('Saphali отзывы', 'saphali-customer-reviews' );
		$this->comment_filds = $s_reviews->comment_filds;
		/* Widget settings. */
		$widget_ops = array( 'classname' => $this->woo_widget_cssclass, 'description' => $this->woo_widget_description );

		/* Create the widget. */
		parent::__construct('saphali_comment', $this->woo_widget_name, $widget_ops);
	}
	function widget( $args, $instance ) {
		extract($args);
		global $s_reviews;
		$title = apply_filters('widget_title', empty($instance['title']) ? __('Отзывы', 'saphali-customer-reviews' ) : __($instance['title']), $instance, $this->id_base);
		$comment_pr = $instance['comment_pr'];
		$comment_pr = apply_filters('widget_comment_pr', $comment_pr, $instance, $this->id_base);
		$number = $instance['count'];
		$length = $instance['length'];
		$name_pos = isset( $instance['name_pos'] ) ? $instance['name_pos'] : 0;
		$page = isset($instance['page']) ? $instance['page'] : get_option('all_reviews_saphali', 0);

		if(empty($page)) $is_page = false; else $is_page = is_page($page);
		if($is_page) return;

		echo $before_widget;
		if ( $title ) echo $before_title . $title . $after_title;

		?>
	<style type="text/css">
	.cloud {
		border: 1px solid #AFB0B0;
		border-radius: 10px 10px 10px 10px;
		margin: 0 !important;
		padding: 10px !important;
		position: relative;
		background: none repeat scroll 0 0 #FFFFFF;
	}

	.cloud .cloud-element {
		background: url("<?php echo $s_reviews->plugin_url;?>/images/cloud-element.png") no-repeat scroll 0 0 transparent;
		bottom: -22px;
		display: block;
		height: 23px;
		left: 20px;
		position: absolute;
		width: 22px;
	}
	.cloud .question, .cloud .answer {
		color: #252422;
		font-size: 14px;
		font-style: italic;
		margin: 0 0 0;
	}

	.question {
		min-height: 50px;
		overflow: hidden;
	}

	.clrfix:after {
		clear: both;
		content: "";
		display: block;
	}

	.cloud .account {
		background: none repeat scroll 0 0 #EEEEEE;
		padding: 5px!important;
		margin: 0 0 12px!important;
	}
	.cloud .account .name {
		font-weight: bold;
		letter-spacing: 0;
		text-align: left;
	}

	.cloud .account span {
		display: block;
		font-size: 12px;
		line-height: 12px;
	}
	.read-comment {
		color: #3B3736 !important;
		display: block;
		margin: 3px 0 15px 60px;
	}
	.cloud li {
		background: none !important;
		list-style: none outside none;
		margin: 0 !important;
		padding: 0 !important;
		float: none !important;
	}
	.cloud .question p {
		margin: 0;
		padding: 0;
	}
	.cloud ul {
		margin: 0 !important;
		padding: 0 !important;
		float: none !important;
	}
	</style>
<div class="cloud">
<?php
$instance['slider_animation'] = isset($instance['slider_animation']) ? $instance['slider_animation'] : 0;
$instance['slider_an_ratio'] = isset($instance['slider_an_ratio']) ? $instance['slider_an_ratio'] : 5000;
$instance['slider_page'] = isset($instance['slider_page']) ? $instance['slider_page'] : 0;
$instance['slider_page_p_n'] = isset($instance['slider_page_p_n']) ? $instance['slider_page_p_n'] : 0;
$instance['slider_height'] = isset($instance['slider_height']) ? $instance['slider_height'] : 600;

$is_slider = isset( $instance['slider'] ) ? $instance['slider'] : 0;
if($is_slider) {
	$slider = array(
		'slider_animation' => $instance['slider_animation'],
		'slider_an_ratio' => $instance['slider_an_ratio'],
		'slider_height' => $instance['slider_height'],
		'slider_page' => $instance['slider_page'],
		'slider_page_p_n' => $instance['slider_page_p_n']
	);
	wp_enqueue_script( 's-sly', plugins_url( basename( plugin_dir_path(__FILE__) ), basename( __FILE__ ) ) . '/js/sly.js', array( 'jquery' ), SAPHALI_PLUGIN_VERSION_REVIEWS, true );
	wp_enqueue_style( 's-sly-css', plugins_url( basename( plugin_dir_path(__FILE__) ), basename( __FILE__ ) ) . '/js/vertical.css' );
} else {
	$slider = array();
}

$this->recent_comments_remak ($number, $length, $comment_pr, $page, $name_pos, $slider);
?>
</div>
<?php 
$page_url = get_permalink($page);
if($this->is_comment) { ?>
<a class="read-comment" href="<?php echo $page_url; ?>#reviews"><?php _e('Читать все отзывы','saphali-customer-reviews'); ?> →</a>
<script type="text/javascript">
jQuery('.widget_saphali_comment a.read-comment').click(function(){
  event.preventDefault();
  window.location.href = "<?php echo $page_url; ?>#reviews";
});
</script>
		<?php 
		} else { 
?>
<a class="read-comment" href="<?php echo $page_url; ?>#reviews"><?php _e('Оставить отзыв','saphali-customer-reviews'); ?> →</a>
<script type="text/javascript">
jQuery('.widget_saphali_comment a.read-comment').click(function(){
  event.preventDefault();
  window.location.href = "<?php echo $page_url; ?>#reviews";
});
</script>
		<?php 
		}
	

		echo $after_widget;
	}
/**
* $number - количество выводимых комментариев,
* $length - количество символов для обрезки текста комментариев
*/
	function recent_comments_remak ($number=5, $length=150, $comment_pr, $page, $name_pos = 0, $slider = array() ) {
		$args = array (
			'number' => $number,
            'status' => 'approve',
			'post_id' => $page, 
		);
		$comments = get_comments($args);
		if($slider) {
			if($slider['slider_page_p_n']) {
				$page_p = '<div class="controls center"><button class="btn prevPage"><i class="icon-arrow-left"></i> Предыдущая страница </button></div>';
			} else $page_p = '';
			echo $page_p . '
		<div class="scrollbar">
				<div class="handle">
					<div class="mousearea"></div>
				</div>
			</div>
			
			<div class="frame smart" id="smart_comment">';
		}
		echo '<ul class="items">';
		foreach($comments as $comment) :
            $comment_content_short = strip_tags($comment->comment_content);
			$this->is_comment = true;
			if( !empty($length) && strrpos($comment_content_short, ' ') && mb_strlen($comment_content_short, 'utf-8') > $length ) {
				$comment_content_short = mb_substr($comment_content_short, 0, $length, 'utf-8');
				if(strrpos($comment_content_short, ' '))
				$comment_content_short = mb_substr($comment_content_short, 0, strrpos($comment_content_short, ' '), 'utf-8'). '...';			
			}
			$img = '';
			
			if($name = get_comment_meta($comment->comment_ID, 'name', true)) $span = "<span class=\"name\">" . $name . "</span>\n"; 
			else
			$span = "<span class=\"name\">" . get_comment_author( $comment->comment_ID ). "</span>\n";
			if(is_array($comment_pr))
			foreach($comment_pr as $value) {
				if($this->comment_filds[$value]['name'] == 'name') continue;
				$span_content = get_comment_meta($comment->comment_ID, $this->comment_filds[$value]['name'], true);
				if( !empty($span_content) && is_string($span_content) ) {
					if($this->comment_filds[$value]['type_fild'] == 'foto') {$img = "<img src='".$span_content."' style=\"height: auto; width: 75px; float: left; margin: 0 10px 10px 0;\" />"; continue;}
					$span .= "<span class=\"{$this->comment_filds[$value]['name']}\">".$span_content."</span>\n";
				}
			}
			$name_block = '<div class="account clrfix">
				'.$span.'
				</div>'."\n";
			if($name_pos)
			echo "<li>$name_block<div class=\"question\"><p>" . $img .$comment_content_short . "</p></div>\n<div class=\"clrfix\"></div></li>";
			else
			echo "<li><div class=\"question\"><p>" . $img .$comment_content_short . "</p></div>\n$name_block</li>";
			
			
		endforeach;
		echo '</ul>';
		if($slider) echo '</div>';
		?>
		
		
		<?php 
		if($slider['slider_page']) echo '<ul class="pages"></ul>';
		
		if($slider['slider_page_p_n']) {
				?><div class="controls center"><button class="btn nextPage">Следующая страница <i class="icon-arrow-right"></i></button></div><?php
			}
		if($slider['slider_height']) {
			echo '<style>';
			echo 'body .cloud .scrollbar, body .cloud .frame { height: ' . $slider['slider_height'] . 'px; }';
			echo '</style>';
		}
	if($slider) { ?>
		<script>
		jQuery(function($){
			'use strict';
			(function () {
				var $frame  = $('#smart_comment');
				var $slidee = $frame.children('ul').eq(0);
				var $wrap   = $frame.parent();

				$frame.sly({
					itemNav: 'basic',
					smart: 1,
					activateOn: 'click',
					mouseDragging: 1,
					touchDragging: 1,
					releaseSwing: 1,
					startAt: 0,
					scrollBar: $wrap.find('.scrollbar'),
					scrollBy: 1,
					pagesBar: <?php if($slider['slider_page']) { echo "\$wrap.find('.pages')"; } else echo 'null';?>,
					activatePageOn: 'click',
					speed: 300,
					elasticBounds: 1,
					dragHandle: 1,
					dynamicHandle: 1,
					clickBar: 1,

					/* // Buttons */
					forward: $wrap.find('.forward'),
					backward: $wrap.find('.backward'),
					prevPage: $wrap.find('.prevPage'),
					nextPage: $wrap.find('.nextPage'),
					
					/* // Automated cycling */
					cycleBy:       'pages',  /* // Enable automatic cycling by 'items' or 'pages'. */
					cycleInterval: <?php echo $slider['slider_an_ratio'];?>,  /* // Delay between cycles in milliseconds. */
					pauseOnHover:  1, /* // Pause cycling when mouse hovers over the FRAME. */
					startPaused:   <?php if($slider['slider_animation']) { echo 0; } else echo 1; ?>, /* // Whether to start in paused sate. */
				});
			}());
		});
		</script>
	<?php }
		echo '<span class="cloud-element"></span>';
	}	
	function update( $new_instance, $old_instance ) {
		$instance['comment_pr'] = $new_instance['comment_pr'];
		$instance['count'] = $new_instance['count'];
		$instance['length'] = $new_instance['length'];
		$instance['name_pos'] = $new_instance['name_pos'];
		$instance['slider'] = $new_instance['slider'];
		$instance['slider_animation'] = $new_instance['slider_animation'];
		$instance['slider_an_ratio'] = $new_instance['slider_an_ratio'];
		$instance['slider_height'] = $new_instance['slider_height'];
		$instance['slider_page'] = $new_instance['slider_page'];
		$instance['slider_page_p_n'] = $new_instance['slider_page_p_n'];
		$instance['page'] = $new_instance['page'];
		$instance['title'] = strip_tags($new_instance['title']);
		
		return $instance;
	}
	function form( $instance ) {
		global $wpdb;
		// echo '<pre>'; var_dump($this); echo '</pre>';
		foreach($this->comment_filds as $key => $value) {
				$array[$key] = $value['title'];
				if(!empty($value['page_id'])) $page_id[] = $value['page_id'];
				$instance['count'] = isset($instance['count']) ? $instance['count'] : 5;
				$instance['length'] = isset($instance['length']) ? $instance['length'] : 150;
				$instance['name_pos'] = isset($instance['name_pos']) ? $instance['name_pos'] : 0;
				$instance['slider'] = isset($instance['slider']) ? $instance['slider'] : 0;
				
				$instance['slider_animation'] = isset($instance['slider_animation']) ? $instance['slider_animation'] : 0;
				$instance['slider_an_ratio'] = isset($instance['slider_an_ratio']) ? $instance['slider_an_ratio'] : 5000;
				$instance['slider_height'] = isset($instance['slider_height']) ? $instance['slider_height'] : 600;
				$instance['slider_page'] = isset($instance['slider_page']) ? $instance['slider_page'] : 0;
				$instance['slider_page_p_n'] = isset($instance['slider_page_p_n']) ? $instance['slider_page_p_n'] : 0;
				$instance['page'] = isset($instance['page']) ? $instance['page'] : get_option('all_reviews_saphali', 0);
		}
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:', 'woocommerce' ); ?></label>
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>
		<p>
			<label for="<?php echo $this->get_field_id('comment_pr'); ?>"><?php _e('Приоритетные поля:', 'saphali-customer-reviews') ?></label>
			<select data-placeholder="Выбрать..." style="width:150px;" name="<?php echo esc_attr( $this->get_field_name('comment_pr') ); ?>[]" multiple="multiple" id="<?php echo esc_attr( $this->get_field_id('comment_pr') ); ?>">
			<?php foreach($array as $key => $value) { ?>
				<option <?php if(@in_array($key, $instance['comment_pr'])) echo 'selected="selected"'; ?> value="<?php echo $key;?>"><?php echo $value;?></option>
			<?php } ?>
			</select>
		</p>
		<p><label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('Количество выводимых комментариев:', 'saphali-customer-reviews') ?></label>
			<input type="number" step="1" class="widefat" id="<?php echo esc_attr( $this->get_field_id('count') ); ?>" name="<?php echo esc_attr( $this->get_field_name('count') ); ?>" value="<?php if (isset ( $instance['count'])) {echo esc_attr( $instance['count'] );} ?>" /></p>
		<p><label for="<?php echo $this->get_field_id('length'); ?>"><?php _e('Количество символов для обрезки текста комментариев:', 'saphali-customer-reviews') ?></label>
			<input type="number" step="1" class="widefat" id="<?php echo esc_attr( $this->get_field_id('length') ); ?>" name="<?php echo esc_attr( $this->get_field_name('length') ); ?>" value="<?php if (isset ( $instance['length'])) {echo esc_attr( $instance['length'] );} ?>" /></p>
		<p><label for="<?php echo $this->get_field_id('name_pos'); ?>"><?php _e('Расположить имя автора вверху', 'saphali-customer-reviews') ?></label>
			<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id('name_pos') ); ?>" name="<?php echo esc_attr( $this->get_field_name('name_pos') ); ?>" value="1" <?php checked($instance['name_pos'], 1); ?> /></p>
		<p class='slider'><label for="<?php echo $this->get_field_id('slider'); ?>"><?php _e('Вывести как слайдер', 'saphali-customer-reviews') ?>
			<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id('slider') ); ?>" name="<?php echo esc_attr( $this->get_field_name('slider') ); ?>" value="1" <?php checked($instance['slider'], 1); ?> /></label><br />
			<span class='description'>Можно будет перелистывать комментарии как слайды</span>
		</p>
		<div class="slider slider-settings-<?php echo $this->number; ?>">
			<fieldset style="border: 1px solid #ccc;padding: 5px;"> <legend>Анимация</legend><p><label for="<?php echo $this->get_field_id('slider_animation'); ?>"><?php _e('Использовать анимацию', 'saphali-customer-reviews') ?>
				<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id('slider_animation') ); ?>" name="<?php echo esc_attr( $this->get_field_name('slider_animation') ); ?>" value="1" <?php checked($instance['slider_animation'], 1); ?> /></label><br />
				<span class='description'>Будет применена автоматическая прокрутка</span>
			</p>
			<p><label for="<?php echo $this->get_field_id('slider_an_ratio'); ?>"><?php _e('Задержка между циклами анимации (прокрутки), миллисекунд', 'saphali-customer-reviews') ?>
				<input type="text" id="<?php echo esc_attr( $this->get_field_id('slider_an_ratio') ); ?>" name="<?php echo esc_attr( $this->get_field_name('slider_an_ratio') ); ?>" value="<?php echo isset($instance['slider_an_ratio']) && $instance['slider_an_ratio'] ? $instance['slider_an_ratio'] : 5000; ?>"  /></label><br />
				<span class='description'>Время между циклами автоматического перелистывания комментариев</span>
			</p>
			</fieldset>
			<p><label for="<?php echo $this->get_field_id('slider_page'); ?>"><?php _e('Вывести панель страниц', 'saphali-customer-reviews') ?>
				<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id('slider_page') ); ?>" name="<?php echo esc_attr( $this->get_field_name('slider_page') ); ?>" value="1" <?php checked($instance['slider_page'], 1); ?>  /></label><br />
				<span class='description'>Внизу выводятся страницы для перелистывания комментариев</span>
			</p>
			<p><label for="<?php echo $this->get_field_id('slider_page_p_n'); ?>"><?php _e('Вывести кнопки для ручного перелистывания', 'saphali-customer-reviews') ?>
				<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id('slider_page_p_n') ); ?>" name="<?php echo esc_attr( $this->get_field_name('slider_page_p_n') ); ?>" value="1" <?php checked($instance['slider_page_p_n'], 1); ?>  /></label><br />
				<span class='description'>Внизу и вверху выводятся кнопки для перелистывания комментариев вперед и назад</span>
			</p>
			<p><label for="<?php echo $this->get_field_id('slider_height'); ?>"><?php _e('Высота блока с комментариями, px', 'saphali-customer-reviews') ?>
				<input type="text" id="<?php echo esc_attr( $this->get_field_id('slider_height') ); ?>" name="<?php echo esc_attr( $this->get_field_name('slider_height') ); ?>" value="<?php echo isset($instance['slider_height']) && $instance['slider_height'] ? $instance['slider_height'] : 600; ?>"  /></label><br />
				<span class='description'>Укажите высоту блока с комментариями. Под эту высоту будут определено количество страниц для прокрутки в слайдере, и эта высота займет пространство на странице по вертикали.</span>
			</p>
		</div>
		<p><label for="<?php echo $this->get_field_id('page'); ?>"><?php _e('Страница отзывов:', 'saphali-customer-reviews') ?></label>
			<?php $_pages = str_replace("name='page_id'", 'name="'.esc_attr( $this->get_field_name('page') ). '"', wp_dropdown_pages( array('echo' => 0, 'selected' => $instance['page'] , 'include' => $page_id) ) );
				echo $_pages;
			?>
		</p>
		<style>
		.widget-content .slider {
			border-left: #70a95e solid;
			padding-left: 5px;
		}
		</style>
		<script>
		jQuery(function($){
			$('div[id*="<?php echo $this->id; ?>"]').each(function(i,e){
				if($(this).attr('id').search('saphali_comment-__i__') == -1)
				{
					var $id = $(this).attr('id');
					if( !$('#widget-saphali_comment-<?php echo $this->number; ?>-slider').is(':checked') ) {
						$('.slider-settings-<?php echo $this->number; ?>').hide();
						$('.widget-content .slider').addClass('slider_hide').removeClass('slider');
					}
					$(document.body).on('click', '#widget-saphali_comment-<?php echo $this->number; ?>-slider', function(){
						if( $(this).is(':checked') ) {
							$('.slider-settings-<?php echo $this->number; ?>').show('slow');
							$('.widget-content .slider_hide').addClass('slider').removeClass('slider_hide');
						} else {
							$('.slider-settings-<?php echo $this->number; ?>').hide('slow');
							$('.widget-content .slider').addClass('slider_hide').removeClass('slider');
						}
					});
				}
			});
		});
		</script>
		<?php
	}
}