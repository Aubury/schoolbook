<?php 
	$header_pre_link = '/wp-admin/admin.php?page=';

	$current_page = $_GET['page'];
?>
<div class="admin_mrkv_ua_shipping__header mrkv_block_rounded">
	<div class="admin_mrkv_ua_shipping__header__content">
		<a class="admin_mrkv_ua_shipping__header_img" href="<?php echo esc_url($header_pre_link); ?>mrkv_ua_shipping_settings">
			<img src="<?php echo esc_url(MRKV_UA_SHIPPING_IMG_URL . '/global/delivery-icon.svg'); ?>" alt="MRKV UA Shipping" title="MRKV UA Shipping">
		</a>
		<a class="<?php if($current_page == 'mrkv_ua_shipping_settings'){ echo 'active'; } ?>" href="<?php echo esc_url($header_pre_link); ?>mrkv_ua_shipping_settings"><?php echo esc_html__('Global', 'mrkv-ua-shipping'); ?></a>
		<?php 
			foreach(MRKV_UA_SHIPPING_LIST as $slug => $shipping)
			{
				?>
					<a class="<?php if(defined('SETTINGS_MRKV_UA_SHIPPING_SLUG') && SETTINGS_MRKV_UA_SHIPPING_SLUG == $slug){ echo 'active'; } ?>" href="<?php echo esc_url($header_pre_link . 'mrkv_ua_shipping_' . $slug); ?>"><?php echo esc_html($shipping['name']); ?></a>
				<?php
			}
		?>
		<a class="<?php if($current_page == 'mrkv_ua_shipping_about_us'){ echo 'active'; } ?>" href="<?php echo esc_url($header_pre_link); ?>mrkv_ua_shipping_about_us"><?php echo esc_html__('About us', 'mrkv-ua-shipping'); ?></a>
		<a class="admin_mrkv_ua_shipping_morkva-logo" href="https://morkva.co.ua/" target="blanc">
			<img src="<?php echo esc_url(MRKV_UA_SHIPPING_IMG_URL . '/global/morkva-logo.svg'); ?>" alt="Morkva" title="Morkva">
		</a>
	</div>
</div>