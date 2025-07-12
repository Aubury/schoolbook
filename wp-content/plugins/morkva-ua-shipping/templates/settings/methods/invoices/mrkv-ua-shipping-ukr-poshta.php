<div class="admin_mrkv_ua_shipping__invoices mrkv_block_rounded">
	<h2>
		<img src="<?php echo MRKV_UA_SHIPPING_ASSETS_URL . '/images/global/invoices-icon.svg'; ?>" alt="My invoices" title="My invoices">
		<?php echo __('My shipments', 'mrkv-ua-shipping'); ?>
		<img class="mrkv-shipping-logo" src="<?php echo MRKV_UA_SHIPPING_IMG_URL . '/' . SETTINGS_MRKV_UA_SHIPPING_SLUG . '/logo-settings.svg' ?>" alt="<?php echo MRKV_UA_SHIPPING_LIST[SETTINGS_MRKV_UA_SHIPPING_SLUG]['name']; ?>" title="<?php echo MRKV_UA_SHIPPING_LIST[SETTINGS_MRKV_UA_SHIPPING_SLUG]['name']; ?>">
	</h2>
	<p><?php echo __('Use the list of invoices to get the status, print your orders', 'mrkv-ua-shipping'); ?></p>
	<hr class="mrkv-ua-ship__hr">
	<div class="admin_mrkv_ua_shipping__invoices__table__actions">
		<div class="admin_ua_ship_morkva_settings_row">
			<div class="admin_ua_ship_morkva_settings_line col-mrkv-7">
				<label><?php echo __('Group actions', 'mrkv-ua-shipping'); $mrkv_ua_ship_invoice = 'morkva'; ?></label>
				<div class="admin_mrkv_ua_shipping_groups">
					<div class="mrkv_invoices__table_data_val_all">
						<input id="mrkv_ua_main_checkbox_invoice" type="checkbox" disabled>
						<label class="mrkv-checkbox-line" for="mrkv_ua_main_checkbox_invoice">
							<div class="admin_mrkv_ua_shipping__checkbox__input">
			                    <span class="admin_mrkv_ua_shipping_slider"></span>
			                </div>
			            </label>
					</div>
					<a>
						<div class="form-ukr-poshta-ttn">
							<button><img src="<?php echo MRKV_UA_SHIPPING_IMG_URL . '/global'; ?>/printer-icon.svg" alt="<?php echo __('Print invoice', 'mrkv-ua-shipping'); ?>" title="<?php echo __('Print invoice', 'mrkv-ua-shipping'); ?>"><?php echo __('Print', 'mrkv-ua-shipping'); ?></button>
						</div>
					</a>
					<a class="mrkv_ua_ship_send_remove_ttn_all">
						<img src="<?php echo MRKV_UA_SHIPPING_IMG_URL . '/global'; ?>/trash-icon.svg" alt="<?php echo __('Remove ttn', 'mrkv-ua-shipping'); ?>" title="<?php echo __('Remove ttn', 'mrkv-ua-shipping'); ?>">
						<?php echo __('Remove', 'mrkv-ua-shipping'); ?>
						<div class="mrkv_ua_ship_create_invoice__loader"></div>
					</a>
				</div>
			</div>
			<div class="admin_ua_ship_morkva_settings_line col-mrkv-3">
				<label><?php echo __('Search', 'mrkv-ua-shipping'); ?></label>
				<div class="mrkv_ua_ship_search_form">
					<input type="text" name="mrkv_search" placeholder="<?php echo __('invoice number', 'mrkv-ua-shipping'); ?>" readonly>
					<button><img src="<?php echo MRKV_UA_SHIPPING_IMG_URL . '/global'; ?>/magnifer-icon.svg" alt="<?php echo __('Search', 'mrkv-ua-shipping'); ?>" title="<?php echo __('Search', 'mrkv-ua-shipping'); ?>" disabled></button>
				</div>
			</div>
		</div>
	</div>
	<div class="admin_mrkv_ua_shipping__invoices__table">
		<div class="mrkv_invoices__table__body mrkv-field-disabled">
			<?php
				$orders = array();
				if(is_array($orders) && !empty($orders))
				{
					
				}
				else{
					?>
						<?php echo '<span class="mrkv-ua-ship-only-pro">' . __('Only in the Pro version', 'mrkv-ua-shipping') . '</span>'; ?>
					<?php
				}
			?>
		</div>		
	</div>
	<div class="mrkv_invoices__table__footer">
		
	</div>
</div>