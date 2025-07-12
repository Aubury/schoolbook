<div class="admin_mrkv_ua_shipping__links_main mrkv_block_rounded">
	<div class="mrkv_ua_shipping__links_main__menu">
		<div class="mrkv-links-head">
			<img src="<?php echo MRKV_UA_SHIPPING_ASSETS_URL . '/images/global/library-icon.svg'; ?>" alt="Morkva menu shipping" title="Morkva menu shipping">
			<?php echo __('Pages:', 'mrkv-ua-shipping'); ?>
		</div>
		<a class="<?php echo !defined('SETTINGS_MRKV_UA_PAGE_SLUG') ? 'active' : ''; ?>" href="/wp-admin/admin.php?page=mrkv_ua_shipping_<?php echo SETTINGS_MRKV_UA_SHIPPING_SLUG;?>"><?php echo __('Settings', 'mrkv-ua-shipping'); ?></a>
		<?php 
			if(MRKV_UA_SHIPPING_LIST[SETTINGS_MRKV_UA_SHIPPING_SLUG]['pages'] && is_array(MRKV_UA_SHIPPING_LIST[SETTINGS_MRKV_UA_SHIPPING_SLUG]['pages']))
			{
				foreach(MRKV_UA_SHIPPING_LIST[SETTINGS_MRKV_UA_SHIPPING_SLUG]['pages'] as $page_slug => $page_name)
				{
					$is_active = (defined('SETTINGS_MRKV_UA_PAGE_SLUG') && SETTINGS_MRKV_UA_PAGE_SLUG == $page_slug) ? 'active' : '';

					?>
						<a class="<?php echo $is_active; ?>" href="/wp-admin/admin.php?page=mrkv_ua_shipping_<?php echo SETTINGS_MRKV_UA_SHIPPING_SLUG . '_' . $page_slug;?>"><?php echo $page_name; ?></a>
					<?php
				}
			}
		?>
	</div>
</div>
