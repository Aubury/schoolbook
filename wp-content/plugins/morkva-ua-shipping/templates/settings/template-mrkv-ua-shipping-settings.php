<div class="admin_mrkv_ua_shipping_page">
	<div class="admin_mrkv_ua_shipping_page__header">
		<?php 
			include MRKV_UA_SHIPPING_PLUGIN_PATH_TEMP . '/elements/template-mrkv-ua-shipping-header.php';
		?>
	</div>
	<div class="admin_mrkv_ua_shipping_page__inner">
		<div class="admin_mrkv_ua_shipping__block col-mrkv-10">
			<div class="admin_mrkv_ua_shipping__info">
				<?php settings_errors(); ?>
			</div>
		</div>
		<div class="admin_mrkv_ua_shipping__block col-mrkv-7">
			<div class="admin_mrkv_ua_shipping__settings mrkv_block_rounded">
				<h2><img src="<?php echo MRKV_UA_SHIPPING_ASSETS_URL . '/images/global/settings-icon.svg'; ?>" alt="Shipping methods" title="Shipping methods"><?php echo __('Shipping methods', 'mrkv-ua-shipping'); ?></h2>
				<p><?php echo __('Activate a shipping method of your choice from the list below. Then go to settings and set them up.', 'mrkv-ua-shipping'); ?></p>
				<form method="post" action="options.php">
					<?php settings_fields('mrkv-ua-shipping-settings-group'); ?>

					<div class="admin_mrkv_ua_shipping__list">
						<?php

							$m_ua_active_plugins = get_option('m_ua_active_plugins');

							foreach(MRKV_UA_SHIPPING_LIST as $slug => $shipping)
							{
								$enabled = '';

								if($m_ua_active_plugins && isset($m_ua_active_plugins[$slug]['enabled']) && $m_ua_active_plugins[$slug]['enabled'] == 'on')
								{
									$enabled = 'checked';
								}

								?>
									<div class="admin_mrkv_ua_shipping__list__li">
										<input id="m_ua_active_plugins_<?php echo $slug; ?>" type="checkbox" name="m_ua_active_plugins[<?php echo $slug; ?>][enabled]" <?php echo $enabled; ?>>
										<label for="m_ua_active_plugins_<?php echo $slug; ?>">
											<div class="admin_mrkv_ua_shipping__checkbox__input">
				                                <span class="admin_mrkv_ua_shipping_slider"></span>
				                            </div>
										</label>
										<img src="<?php echo MRKV_UA_SHIPPING_IMG_URL . '/' . $slug . '/logo-settings.svg' ?>" alt="<?php echo $shipping['name']; ?>" title="<?php echo $shipping['name']; ?>">
										<p>
											<span class="admin_mrkv_ua_shipping__list__li__name"><?php echo $shipping['name']; ?></span>
											<span class="admin_mrkv_ua_shipping__list__li__desc"><?php echo $shipping['description']; ?></span>
										</p>
									</div>
								<?php
							}
						?>
					</div>

					<?php echo submit_button(__('Save', 'mrkv-ua-shipping')); ?>
				</form>
			</div>
		</div>
		<div class="admin_mrkv_ua_shipping__block col-mrkv-3">
			<div class="admin_mrkv_ua_shipping__plugin-info mrkv_block_rounded">
				<?php 
					include MRKV_UA_SHIPPING_PLUGIN_PATH_TEMP . '/elements/template-mrkv-ua-shipping-support.php';
				?>
			</div>
		</div>
	</div>
</div>