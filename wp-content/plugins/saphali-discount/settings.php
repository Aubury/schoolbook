<?php
	if($_POST) {
		$settings['schedule_fixed_total_shop'] = $_POST['schedule_fixed_total_shop'];
		$settings['button_set'] = $_POST['button_set'];
		$settings['button_position'] = $_POST['button_position'];
		$settings['thumbnail_id'] = $_POST['icons_thumbnail_id'];
		$settings['opacity'] = $_POST['opacity'];
		update_option( 'saphali_global_discount_settings', $settings );
		$this->settings = $settings;
		echo '<div class="updated" style="padding: 7px;">' . __('Saved') . '</div>';
	}
	global $wp_version;
	
	$image 			= plugin_dir_url( __FILE__ ) . 'img/discount.gif';
	$thumbnail_id 	= isset( $this->settings['thumbnail_id'] ) ? $this->settings['thumbnail_id'] : '';
	if ($thumbnail_id) :
		$image = wp_get_attachment_url( $thumbnail_id );
	endif;
?>
<form method='post'>
<div class="options_group fixed_total_shop">
	<h2 style="margin: 0px;"><?php  _e('Ограничения накопительной по магазину', 'saphali-discount');?></h2>
	<p class="form-field schedule_fixed_total_shop  ">
		<label for="schedule_fixed_total_shop"><?php  _e('Период выборки заказов для накопительной скидки <strong>по магазину</strong>, в днях', 'saphali-discount');?></label>
		<input type="number" min='0' value="<?php  echo $this->settings['schedule_fixed_total_shop']; ?>"  name="schedule_fixed_total_shop" id="schedule_fixed_total_shop">
		<span class="description"><?php  _e('Период, на протяжении которого будут учитываться покупки в накопительной системе в промежутке от столько-то дней до текущего момента.', 'saphali-discount');?></span>
	</p>
</div>
<div class="options_group button_set">
	<h2 style="margin: 0px;"><?php  _e('Плавающая кнопка', 'saphali-discount');?></h2>
	<p class="form-field button_set">
		<label for="button_set">Включить</label> <input type="checkbox" name="button_set" id="button_set" <?php if( isset($this->settings['button_set']) && $this->settings['button_set'] ) echo 'checked';  ?> value='1' />
	</p>
	<div class="button_block">
	<p class="form-field button_position">
		<label for="button_position">Положение кнопки</label> 
		<select name="button_position" id="button_position">
			<option value='lt' <?php if(isset($this->settings['button_position']) && $this->settings['button_position'] == 'lt' ) echo 'selected="selected"'; ?> >Слева вверху</option>
			<option value='lb' <?php if(isset($this->settings['button_position']) && $this->settings['button_position'] == 'lb' ) echo 'selected="selected"'; ?> >Слева внизу</option>
			<option value='lc' <?php if(isset($this->settings['button_position']) && $this->settings['button_position'] == 'lc' ) echo 'selected="selected"'; ?> >Слева по центру</option>
			<option value='rt' <?php if(isset($this->settings['button_position']) && $this->settings['button_position'] == 'rt' ) echo 'selected="selected"'; ?> >Справа вверху</option>
			<option value='rb' <?php if(isset($this->settings['button_position']) && $this->settings['button_position'] == 'rb' ) echo 'selected="selected"'; ?> >Справа внизу</option>
			<option value='rc' <?php if(isset($this->settings['button_position']) && $this->settings['button_position'] == 'rc' ) echo 'selected="selected"'; ?> >Справа по центру</option>
			<option value='ct' <?php if(isset($this->settings['button_position']) && $this->settings['button_position'] == 'ct' ) echo 'selected="selected"'; ?> >По центру вверху</option>
			<option value='cb' <?php if(isset($this->settings['button_position']) && $this->settings['button_position'] == 'cb' ) echo 'selected="selected"'; ?> >По центру внизу</option>
		</select>
	</p>
	<h3>Вид кнопки:</h3>
	<table>
	<tr class="form-field">
		<td scope="row" valign="top"><label><?php _e('Иконка', 'saphali-discount'); ?></label></td>
		<td>
			<div id="icons_thumbnail" style="float:left;margin-right:10px;"><img src="<?php echo $image; ?>" width="60px" height="60px" /></div>
			<div style="line-height:60px;">
				<input type="hidden" id="icons_thumbnail_id" name="icons_thumbnail_id" value="<?php echo $thumbnail_id; ?>" />
				<button type="submit" class="upload_image_button button"><?php _e('Назначить свою кнопку', 'saphali-discount'); ?></button>
				<button type="submit" class="remove_image_button button"><?php _e('Установить по умолчанию', 'saphali-discount'); ?></button>
			</div>
			<div class="clear"></div>
		</td>
	</tr>
	<tr><td>Прозрачность</td><td><span class="opacity"><?php echo $this->settings['opacity'] ? (1-$this->settings['opacity']) * 100 : 0; ?></span>%<div id="slider"></div><input type="hidden" name="opacity" id = 'opacity' value='<?php echo $this->settings['opacity'] ? $this->settings['opacity'] : 1;?>' /></td></tr>
	</table>
	</div>
</div>
<br />
<input type='submit' class="button-primary" value="<?php _e('Save'); ?>" /> 
</form>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<?php if ( !version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) || !version_compare( $wp_version, '3.5.1', '<' ) ) { wp_enqueue_media(); } ?>		
<script type="text/javascript">
<?php if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) || version_compare( $wp_version, '3.5.1', '<' ) ) { ?>
				window.send_to_termmeta = function(html) {

					jQuery('body').append('<div id="temp_image">' + html + '</div>');

					var img = jQuery('#temp_image').find('img');

					imgurl 		= img.attr('src');
					imgclass 	= img.attr('class');
					imgid		= parseInt(imgclass.replace(/\D/g, ''), 10);

					jQuery('#icons_thumbnail_id').val(imgid);
					jQuery('#icons_thumbnail img').attr('src', imgurl);
					jQuery('#temp_image').remove();

					tb_remove();
				}

				jQuery('.upload_image_button').live('click', function(){
					var post_id = 0;

					window.send_to_editor = window.send_to_termmeta;

					tb_show('', 'media-upload.php?post_id=' + post_id + '&amp;type=image&amp;TB_iframe=true');
					return false;
				});

				jQuery('.remove_image_button').live('click', function(){
					jQuery('#icons_thumbnail img').attr('src', '<?php echo plugin_dir_url( __FILE__ ) . 'img/discount.gif'; ?>');
					jQuery('#icons_thumbnail_id').val('');
					return false;
				});
	<?php } else { ?>

	// Uploading files
	var file_frame;
	jQuery(document).on( 'click', '.upload_image_button', function( event ){
		event.preventDefault();
		// If the media frame already exists, reopen it.
		if ( file_frame ) {
			file_frame.open();
			return;
		}
		// Create the media frame.
		file_frame = wp.media.frames.downloadable_file = wp.media({
			title: '<?php _e('Upload/Add image', 'woocommerce'); ?>',
			button: {
				text: 'Использовать изображение',
			},
			multiple: false
		});
		// When an image is selected, run a callback.
		file_frame.on( 'select', function() {
			attachment = file_frame.state().get('selection').first().toJSON();
			jQuery('#icons_thumbnail_id').val( attachment.id );
			jQuery('#icons_thumbnail img').attr('src', attachment.url );
			jQuery('.remove_image_button').show();
		});
		// Finally, open the modal.
		file_frame.open();
	}); 
			jQuery(document).on( 'click', '.remove_image_button', function( event ){
				jQuery('#icons_thumbnail img').attr('src', '<?php echo plugin_dir_url( __FILE__ ) . 'img/discount.gif'; ?>');
				jQuery('#icons_thumbnail_id').val('');
				jQuery('.remove_image_button').hide();
				return false;
			}); 
<?php } ?>
jQuery( "#slider" ).slider({value: <?php echo $this->settings['opacity'] ? (1 - $this->settings['opacity']) * 100 : 0; ?>, change: function( event, ui ) {
	console.log( ui ); 
	jQuery( "#opacity" ).val( 1 - ui.value / 100 );
	jQuery( "span.opacity" ).text( ui.value  );
	jQuery( "#icons_thumbnail img" ).css( 'opacity', 1 - ui.value / 100 );
}});
jQuery( "#icons_thumbnail img" ).css( 'opacity', <?php echo $this->settings['opacity'] ? $this->settings['opacity'] : 1; ?> );
jQuery( 'body' ).delegate("#button_set", 'click', function(){
	if( !jQuery( this ).is(':checked') ){
		jQuery( ".button_block" ).hide('slow');
	} else {
		jQuery( ".button_block" ).show('slow');
	}
});
if( !jQuery( "#button_set" ).is(':checked') ){
	jQuery( ".button_block" ).hide();
}
</script>
<style>
.fixed_total_shop p.schedule_fixed_total_shop input {
    display: block;
    width: auto;
}
#icons_thumbnail > img {
    border-radius: 29px;
    box-shadow: 1px 0 10px;
}
.fixed_total_shop p.schedule_fixed_total_shop {margin: 5px 0;}
div.options_group {
    border: 1px solid #ccc;
    margin: 8px 0 0;
    padding: 0 7px;
}
</style>