<div class="admin_mrkv_ua_shipping__plugin-info mrkv_block_rounded">
	<h2><img src="<?php echo MRKV_UA_SHIPPING_ASSETS_URL . '/images/global/chart-icon.svg'; ?>" alt="Statistics of shipments" title="Statistics of shipments"><?php echo __('Statistics of shipments', 'mrkv-ua-shipping'); ?></h2>
	<ul class="mrkv_list_invoices_total">
		<li>
			<span><?php echo __('Total invoices', 'mrkv-ua-shipping'); ?></span>
			<span><?php echo (isset($total_statistic)) ? $total_statistic : 0; ?></span>
		</li>
	</ul>
</div>
<!--<div class="admin_mrkv_ua_shipping__plugin-info mrkv_block_rounded">
	<h2><img src="<?php echo MRKV_UA_SHIPPING_ASSETS_URL . '/images/global/alarm-icon.svg'; ?>" alt="Statistics of shipments" title="Statistics of shipments"><?php echo __('Last status update', 'mrkv-ua-shipping'); ?></h2>
	<p><?php echo __('2022-05-17 06:50:02 (UTC)', 'mrkv-ua-shipping'); ?></p>
	<button class="button button-primary mrkv_invoices_update_statuses"><?php echo __('Update now', 'mrkv-ua-shipping'); ?></button>
</div>-->