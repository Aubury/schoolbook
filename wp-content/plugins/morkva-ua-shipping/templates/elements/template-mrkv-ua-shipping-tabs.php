<?php 
	if(MRKV_OPTION_TABS)
	{
		?>
			<div class="admin_mrkv_ua_shipping__tabs_main mrkv_block_rounded">
				<h2>
					<?php echo __('Settings', 'mrkv-ua-shipping') . ' ' . MRKV_UA_SHIPPING_LIST[SETTINGS_MRKV_UA_SHIPPING_SLUG]['name']; ?>
						<img src="<?php echo MRKV_UA_SHIPPING_IMG_URL . '/' . SETTINGS_MRKV_UA_SHIPPING_SLUG . '/logo-settings.svg' ?>" alt="<?php echo MRKV_UA_SHIPPING_LIST[SETTINGS_MRKV_UA_SHIPPING_SLUG]['name']; ?>" title="<?php echo MRKV_UA_SHIPPING_LIST[SETTINGS_MRKV_UA_SHIPPING_SLUG]['name']; ?>">
					</h2>
				<div class="admin_mrkv_ua_shipping__tabs_main__inner">
					<?php 
						$counter = 0;
						foreach(MRKV_OPTION_TABS as $id => $name)
						{
							?>
								<a href="#<?php echo $id; ?>-mrkv" data-tab="<?php echo $id; ?>" class="mrkv_up_ship_tab_btn <?php if($counter == 0){echo 'active'; } ?>"><?php echo $name; ?></a>
							<?php

							++$counter;
						}
					?>
				</div>
			</div>
		<?php
	}
?>