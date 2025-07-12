<div class="admin_mrkv_ua_shipping_page">
	<div class="admin_mrkv_ua_shipping_page__header">
		<?php 
			include MRKV_UA_SHIPPING_PLUGIN_PATH_TEMP . '/elements/template-mrkv-ua-shipping-header.php';
		?>
	</div>
	<div class="admin_mrkv_ua_shipping_page__links">
		<?php 
			include MRKV_UA_SHIPPING_PLUGIN_PATH_TEMP . '/elements/template-mrkv-ua-shipping-links.php';
		?>
	</div>
	<div class="admin_mrkv_ua_shipping_page__inner">
		<div class="admin_mrkv_ua_shipping__block col-mrkv-7">
			<?php 
				include MRKV_UA_SHIPPING_PLUGIN_PATH_TEMP . '/settings/methods/' . SETTINGS_MRKV_UA_PAGE_SLUG . '/mrkv-ua-shipping-' . SETTINGS_MRKV_UA_SHIPPING_SLUG . '.php';
			?>
		</div>
		<div class="admin_mrkv_ua_shipping__block col-mrkv-3">
			<?php 
				include MRKV_UA_SHIPPING_PLUGIN_PATH_TEMP . '/settings/methods/' . SETTINGS_MRKV_UA_PAGE_SLUG . '/mrkv-ua-shipping-sidebar.php';
			?>
			<div class="admin_mrkv_ua_shipping__plugin-info mrkv_block_rounded">
				<?php
					include MRKV_UA_SHIPPING_PLUGIN_PATH_TEMP . '/elements/template-mrkv-ua-shipping-support.php';
				?>
			</div>
		</div>
	</div>
</div>