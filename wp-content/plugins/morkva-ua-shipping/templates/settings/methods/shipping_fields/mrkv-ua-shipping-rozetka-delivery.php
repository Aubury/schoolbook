<?php global $mrkv_global_option_generator; global $mrkv_global_shipping_object; ?>
<section id="basic_settings" class="mrkv_up_ship_shipping_tab_block active">
	<h2><img src="<?php echo MRKV_UA_SHIPPING_ASSETS_URL . '/images/global/settings-icon.svg'; ?>" alt="Basic settings" title="Basic settings"><?php echo __('Basic settings', 'mrkv-ua-shipping'); ?></h2>
	<p><?php echo __('There are currently no settings. The delivery method works without entering API keys.', 'mrkv-ua-shipping'); ?></p>
</section>
<section id="checkout_settings" class="mrkv_up_ship_shipping_tab_block">
	<h2><img src="<?php echo MRKV_UA_SHIPPING_ASSETS_URL . '/images/global/clapperboard-edit-icon.svg'; ?>" alt="Sending email from TTN" title="Sending email from TTN"><?php echo __('Checkout settings', 'mrkv-ua-shipping'); ?></h2>
	<p><?php echo __('Customize the output of the plugin fields in relation to your theme', 'mrkv-ua-shipping'); ?></p>
	<hr class="mrkv-ua-ship__hr">
	<div class="admin_ua_ship_morkva_settings_row">
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line">
				<?php 
					$data = isset(MRKV_SHIPPING_SETTINGS['checkout']['position']) ? MRKV_SHIPPING_SETTINGS['checkout']['position'] : '';
					$senders_type_list = array(
						'woocommerce_after_checkout_billing_form' => __('After Payment data', 'mrkv-ua-shipping'),
						'woocommerce_before_order_notes' => __('Before Notes to orders', 'mrkv-ua-shipping'),
					);

					$description = __('Select the position of the delivery method fields on the checkout page', 'mrkv-ua-shipping');

					echo wp_kses($mrkv_global_option_generator->get_select_simple(__('Position of plugin fields in Checkout', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[checkout][position]', $senders_type_list, $data, MRKV_OPTION_OBJECT_NAME . '_checkout_position' , __('Choose a position', 'mrkv-ua-shipping'), $description), MRKV_UA_SHIPPING_ALLOW_TAGS);
				?>
			</div>
		</div>
	</div>
	<div class="admin_ua_ship_morkva_settings_row">
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line">
				<?php
					$data = isset(MRKV_SHIPPING_SETTINGS['checkout']['hide_saving_data']) ? MRKV_SHIPPING_SETTINGS['checkout']['hide_saving_data'] : '';
					echo wp_kses($mrkv_global_option_generator->get_input_checkbox(__('Save customer selected fields', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[checkout][hide_saving_data]', $data, MRKV_OPTION_OBJECT_NAME . '_checkout_hide_saving_data', ), MRKV_UA_SHIPPING_ALLOW_TAGS);
				?>
				<?php echo '<p class="mrkv-ua-ship-description">' . __('Enable to store selected delivery city and warehouse/postamat in session cookies (may not work if privacy settings enabled in user’s browser)', 'mrkv-ua-shipping') . '</p>'; ?>
			</div>
		</div>
		<div class="col-mrkv-5">
		</div>
	</div>
</section>
<section id="log_settings" class="mrkv_up_ship_shipping_tab_block">
	<h2><img src="<?php echo MRKV_UA_SHIPPING_ASSETS_URL . '/images/global/clipboard-list-icon.svg'; ?>" alt="Debug Log" title="Debug Log"><?php echo __('Debug Log', 'mrkv-ua-shipping'); ?></h2>
	<p><?php echo __('To keep an error log, enable it in the settings', 'mrkv-ua-shipping'); ?></p>
	<hr class="mrkv-ua-ship__hr">
	<div class="admin_ua_ship_morkva_settings_row">
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line">
				<?php
					$data = isset(MRKV_SHIPPING_SETTINGS['debug']['log']) ? MRKV_SHIPPING_SETTINGS['debug']['log'] : '';
					echo wp_kses($mrkv_global_option_generator->get_input_checkbox(__('Enable debug log', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[debug][log]', $data, MRKV_OPTION_OBJECT_NAME . '_debug_log', ), MRKV_UA_SHIPPING_ALLOW_TAGS);
				?>
				<?php echo '<p class="mrkv-ua-ship-description">' . __('Enable to receive request error logs', 'mrkv-ua-shipping') . '</p>'; ?>
			</div>
		</div>
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line">
				<?php
					$data = isset(MRKV_SHIPPING_SETTINGS['debug']['query']) ? MRKV_SHIPPING_SETTINGS['debug']['query'] : '';
					echo wp_kses($mrkv_global_option_generator->get_input_checkbox(__('Enable debug query', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[debug][query]', $data, MRKV_OPTION_OBJECT_NAME . '_debug_query', ), MRKV_UA_SHIPPING_ALLOW_TAGS);
				?>
				<?php echo '<p class="mrkv-ua-ship-description">' . __('Enable to receive request logs in order notes and log file', 'mrkv-ua-shipping') . '</p>'; ?>
			</div>
		</div>
	</div>
	<div class="admin_ua_ship_morkva_settings_line">
		<h3><?php echo __('Log', 'mrkv-ua-shipping'); ?></h3>
		<pre class="mrkv_log_file_content">
			<?php echo file_get_contents(MRKV_UA_SHIPPING_PLUGIN_DIR . 'logs/' . SETTINGS_MRKV_UA_SHIPPING_SLUG . '/debug-' . SETTINGS_MRKV_UA_SHIPPING_SLUG . '.log'); ?>
		</pre>
		<div class="mrkv_btn_log_clean"><img src="<?php echo MRKV_UA_SHIPPING_ASSETS_URL . '/images/global/trash-icon.svg'; ?>" alt="Debug Log" title="Debug Log"><?php echo __('Clear log', 'mrkv-ua-shipping'); ?></div>
	</div>
</section>