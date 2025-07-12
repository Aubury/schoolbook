<section id="basic_settings" class="mrkv_up_ship_shipping_tab_block active">
	<h2><img src="<?php echo MRKV_UA_SHIPPING_ASSETS_URL . '/images/global/settings-icon.svg'; ?>" alt="Basic settings" title="Basic settings"><?php echo __('Basic settings', 'mrkv-ua-shipping'); ?></h2>
	<hr class="mrkv-ua-ship__hr">
	<div class="admin_ua_ship_morkva_settings_line">
		<?php 
			$data = isset(MRKV_SHIPPING_SETTINGS['api_key']) ? MRKV_SHIPPING_SETTINGS['api_key'] : '';
			$label = __('API Key', 'mrkv-ua-shipping');
			global $mrkv_global_option_generator;
			global $mrkv_global_shipping_object;

			if(is_string($mrkv_global_shipping_object->active_api))
			{
				$label .= '<div class="admin_ua_ship_morkva__notification mrkv-notification-red">' . $mrkv_global_shipping_object->active_api . '</div>';
			}
			elseif($mrkv_global_shipping_object->active_api)
			{
				$label .= '<div class="admin_ua_ship_morkva__notification mrkv-notification-green">' . __('API key correct','mrkv-ua-shipping') . '</div>';
			}

			$description = __('Not sure where to get the key? Take a look', 'mrkv-ua-shipping') . ' <a target="blanc" href="http://my.novaposhta.ua/settings/index#apikeys">' . __('this video', 'mrkv-ua-shipping') . '</a>';

			echo $mrkv_global_option_generator->get_input_text($label, MRKV_OPTION_OBJECT_NAME . '[api_key]', $data, MRKV_OPTION_OBJECT_NAME. '_api_key' , '', __('Enter the key...', 'mrkv-ua-shipping'), $description);
		?>
	</div>
</section>
<section id="sender_settings" class="mrkv_up_ship_shipping_tab_block">
	<h2><img src="<?php echo MRKV_UA_SHIPPING_ASSETS_URL . '/images/global/user-icon.svg'; ?>" alt="Sender settings" title="Sender settings"><?php echo __('Sender Settings', 'mrkv-ua-shipping'); ?></h2>
	<p><?php echo __('Fill in the data for the sender, which will be used to create shipment', 'mrkv-ua-shipping'); ?></p>
	<hr class="mrkv-ua-ship__hr">
	<div class="admin_ua_ship_morkva_settings_line">
		<?php
			$data = isset(MRKV_SHIPPING_SETTINGS['sender']['ref']) ? MRKV_SHIPPING_SETTINGS['sender']['ref'] : '';
			$label = __('Sender', 'mrkv-ua-shipping');

			if(empty(MRKV_OPTION_NOVA_SENDER))
			{
				$label .= '<div class="admin_ua_ship_morkva__notification mrkv-notification-red">' . __('Sender list empty', 'mrkv-ua-shipping') . '</div>';
			}
			elseif(isset(MRKV_OPTION_NOVA_SENDER[0]))
			{
				$label .= '<div class="admin_ua_ship_morkva__notification mrkv-notification-green">' . __('Sender list loaded','mrkv-ua-shipping') . '</div>';
			}

			echo $mrkv_global_option_generator->get_select_tag($label, MRKV_OPTION_OBJECT_NAME . '[sender][ref]', MRKV_OPTION_NOVA_SENDER, $data, MRKV_OPTION_OBJECT_NAME . '_sender_ref' , __('Choose a sender', 'mrkv-ua-shipping'));

			if(isset(MRKV_OPTION_NOVA_SENDER[0]['attr']) && !empty(MRKV_OPTION_NOVA_SENDER))
			{
				foreach(MRKV_OPTION_NOVA_SENDER[0]['attr'] as $sender_data_key => $sender_data_val)
				{
					if($sender_data_key == 'ref')
					{
						continue;
					}
					$data = isset(MRKV_SHIPPING_SETTINGS['sender'][$sender_data_key]) ? MRKV_SHIPPING_SETTINGS['sender'][$sender_data_key] : '';
					echo $mrkv_global_option_generator->get_input_hidden(MRKV_OPTION_OBJECT_NAME . '[sender][' . $sender_data_key . ']', $data, MRKV_OPTION_OBJECT_NAME . '_sender_' . $sender_data_key);
				}
			}
			$data = isset(MRKV_SHIPPING_SETTINGS['sender']['list']) ? MRKV_SHIPPING_SETTINGS['sender']['list'] : '';
			echo $mrkv_global_option_generator->get_input_hidden(MRKV_OPTION_OBJECT_NAME . '[sender][list]', base64_encode(wp_json_encode(MRKV_OPTION_NOVA_SENDER)), MRKV_OPTION_OBJECT_NAME . '_sender_list');
		?>
	</div>
	<h3><img src="<?php echo MRKV_UA_SHIPPING_ASSETS_URL . '/images/global/routing-icon.svg'; ?>" alt="Sender address" title="Sender address"><?php echo __('Sender address', 'mrkv-ua-shipping'); ?></h3>
	<p><?php echo __('Specify the sender\'s address from which the goods will be sent', 'mrkv-ua-shipping'); ?></p>
	<hr class="mrkv-ua-ship__hr">
	<div class="admin_ua_ship_morkva_settings_row">
		<div class="admin_ua_ship_morkva_settings_line col-mrkv-5">
			<?php 
				$data = isset(MRKV_SHIPPING_SETTINGS['sender']['city']['name']) ? MRKV_SHIPPING_SETTINGS['sender']['city']['name'] : '';
				$description = __('Enter the first 2-3 letters and wait for the data to load', 'mrkv-ua-shipping');

				echo $mrkv_global_option_generator->get_input_text(__('City', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[sender][city][name]', $data, MRKV_OPTION_OBJECT_NAME. '_sender_city_name' , '', __('Enter the city...', 'mrkv-ua-shipping'), $description);
				$data = isset(MRKV_SHIPPING_SETTINGS['sender']['city']['ref']) ? MRKV_SHIPPING_SETTINGS['sender']['city']['ref'] : '';
				echo $mrkv_global_option_generator->get_input_hidden(MRKV_OPTION_OBJECT_NAME . '[sender][city][ref]', $data, MRKV_OPTION_OBJECT_NAME . '_sender_city_ref');
			?>
		</div>
		<div class="admin_ua_ship_morkva_settings_line col-mrkv-5">
		</div>
	</div>
	<div class="admin_ua_ship_morkva_settings_row">
		<div class="admin_ua_ship_morkva_settings_line col-mrkv-5">
			<?php
				$data = isset(MRKV_SHIPPING_SETTINGS['sender']['address_type']) ? MRKV_SHIPPING_SETTINGS['sender']['address_type'] : '';
				echo $mrkv_global_option_generator->get_input_radio(__('Sending from a branch', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[sender][address_type]', 'W', $data, MRKV_OPTION_OBJECT_NAME . '_sender_address_type_w', 'W');
			?>
			<div class="admin_ua_ship_morkva_settings_line__inner">
				<?php 
				$data = isset(MRKV_SHIPPING_SETTINGS['sender']['warehouse']['name']) ? MRKV_SHIPPING_SETTINGS['sender']['warehouse']['name'] : '';
				$description = __('Enter the first 2-3 letters and wait for the data to load', 'mrkv-ua-shipping');

				echo $mrkv_global_option_generator->get_input_text(__('Warehouse', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[sender][warehouse][name]', $data, MRKV_OPTION_OBJECT_NAME. '_sender_warehouse_name' , '', __('Enter the warehouse...', 'mrkv-ua-shipping'), $description);
				$data = isset(MRKV_SHIPPING_SETTINGS['sender']['warehouse']['ref']) ? MRKV_SHIPPING_SETTINGS['sender']['warehouse']['ref'] : '';
				echo $mrkv_global_option_generator->get_input_hidden(MRKV_OPTION_OBJECT_NAME . '[sender][warehouse][ref]', $data, MRKV_OPTION_OBJECT_NAME . '_sender_warehouse_ref');
				$data = isset(MRKV_SHIPPING_SETTINGS['sender']['warehouse']['number']) ? MRKV_SHIPPING_SETTINGS['sender']['warehouse']['number'] : '';
				echo $mrkv_global_option_generator->get_input_hidden(MRKV_OPTION_OBJECT_NAME . '[sender][warehouse][number]', $data, MRKV_OPTION_OBJECT_NAME . '_sender_warehouse_number');
			?>
			</div>
		</div>
		<div class="admin_ua_ship_morkva_settings_line col-mrkv-5">
			<?php
				$data = isset(MRKV_SHIPPING_SETTINGS['sender']['address_type']) ? MRKV_SHIPPING_SETTINGS['sender']['address_type'] : '';
				echo $mrkv_global_option_generator->get_input_radio(__('Sending from the address', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[sender][address_type]', 'D', $data, MRKV_OPTION_OBJECT_NAME . '_sender_address_type_d', 'W');
			?>
			<div class="admin_ua_ship_morkva_settings_line__inner">
				<?php 
					$data = isset(MRKV_SHIPPING_SETTINGS['sender']['street']['name']) ? MRKV_SHIPPING_SETTINGS['sender']['street']['name'] : '';
					echo $mrkv_global_option_generator->get_input_text(__('Street', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[sender][street][name]', $data, MRKV_OPTION_OBJECT_NAME. '_sender_street_name' , '', __('Enter the street...', 'mrkv-ua-shipping'));
					$data = isset(MRKV_SHIPPING_SETTINGS['sender']['street']['ref']) ? MRKV_SHIPPING_SETTINGS['sender']['street']['ref'] : '';
					echo $mrkv_global_option_generator->get_input_hidden(MRKV_OPTION_OBJECT_NAME . '[sender][street][ref]', $data, MRKV_OPTION_OBJECT_NAME . '_sender_street_ref');
				?>
				<?php echo '<p class="mrkv-ua-ship-description">' . __('Enter the first 2-3 letters and wait for the data to load', 'mrkv-ua-shipping') . '</p>'; ?>
				<div class="admin_ua_ship_morkva_settings_row">
					<div class="admin_ua_ship_morkva_settings_line col-mrkv-5">
						<?php 
							$data = isset(MRKV_SHIPPING_SETTINGS['sender']['street']['house']) ? MRKV_SHIPPING_SETTINGS['sender']['street']['house'] : '';
							echo $mrkv_global_option_generator->get_input_text('', MRKV_OPTION_OBJECT_NAME . '[sender][street][house]', $data, MRKV_OPTION_OBJECT_NAME. '_sender_street_house' , '', __('Enter the house...', 'mrkv-ua-shipping'));
						?>
					</div>
					<div class="admin_ua_ship_morkva_settings_line col-mrkv-5">
						<?php
							$data = isset(MRKV_SHIPPING_SETTINGS['sender']['street']['flat']) ? MRKV_SHIPPING_SETTINGS['sender']['street']['flat'] : '';
							echo $mrkv_global_option_generator->get_input_text('', MRKV_OPTION_OBJECT_NAME . '[sender][street][flat]', $data, MRKV_OPTION_OBJECT_NAME. '_sender_street_flat' , '', __('Apartment / Office', 'mrkv-ua-shipping'));
						?>
					</div>
				</div>
				<?php 
					$data = isset(MRKV_SHIPPING_SETTINGS['sender']['address']['ref']) ? MRKV_SHIPPING_SETTINGS['sender']['address']['ref'] : '';
					echo $mrkv_global_option_generator->get_input_hidden(MRKV_OPTION_OBJECT_NAME . '[sender][address][ref]', $data, MRKV_OPTION_OBJECT_NAME . '_sender_address_ref');
				?>
			</div>
		</div>
	</div>
</section>
<section id="default_settings" class="mrkv_up_ship_shipping_tab_block">
	<h2><img src="<?php echo MRKV_UA_SHIPPING_ASSETS_URL . '/images/global/box-icon.svg'; ?>" alt="Default values" title="Default values"><?php echo __('Default values', 'mrkv-ua-shipping'); ?></h2>
	<hr class="mrkv-ua-ship__hr">
	<div class="admin_ua_ship_morkva_settings_row">
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line">
				<h4><?php echo __('Payer of delivery', 'mrkv-ua-shipping'); ?></h4>
				<div class="admin_ua_ship_morkva_settings_row">
					<?php
						$data = isset(MRKV_SHIPPING_SETTINGS['payer']['delivery']) ? MRKV_SHIPPING_SETTINGS['payer']['delivery'] : '';
						echo $mrkv_global_option_generator->get_input_radio(__('Recipient', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[payer][delivery]', 'Recipient', $data, MRKV_OPTION_OBJECT_NAME . '_payer_delivery_recipient', 'Recipient');
						echo $mrkv_global_option_generator->get_input_radio(__('Sender', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[payer][delivery]', 'Sender', $data, MRKV_OPTION_OBJECT_NAME . '_payer_delivery_sender', 'Recipient');
					?>
				</div>
			</div>
		</div>
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
				<h4><?php echo __('Payer for the cash on delivery function', 'mrkv-ua-shipping'); ?></h4>
				<p class="mrkv-ua-ship-only-pro"><?php echo __('Only in the Pro version', 'mrkv-ua-shipping'); ?></p>
				<div class="admin_ua_ship_morkva_settings_row">
					<?php
						$data = '';
						echo $mrkv_global_option_generator->get_input_radio(__('Recipient', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[payer][cash]', 'Recipient', $data, MRKV_OPTION_OBJECT_NAME . '_payer_cash_recipient', 'Recipient', 'disabled');
						echo $mrkv_global_option_generator->get_input_radio(__('Sender', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[payer][cash]', 'Sender', $data, MRKV_OPTION_OBJECT_NAME . '_payer_cash_sender', 'Recipient', 'disabled');
					?>
				</div>
			</div>
		</div>
	</div>
	<div class="admin_ua_ship_morkva_settings_line admin_ua_ship_morkva_one_data mrkv-field-disabled">
		<?php 
			$data = '';
			$description = '<span class="mrkv-ua-ship-only-pro">' . __('Only in the Pro version', 'mrkv-ua-shipping') . '</span>' . __('If filled in, for cash on delivery, this amount will be deducted from the shipment value', 'mrkv-ua-shipping');

			echo $mrkv_global_option_generator->get_input_number(__('Prepayment', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[shipment][prepayment]', $data, MRKV_OPTION_OBJECT_NAME. '_shipment_prepayment' , '', '', $description, 'readonly');
		?>
	</div>
	<h3><img src="<?php echo MRKV_UA_SHIPPING_ASSETS_URL . '/images/global/tuning-icon.svg'; ?>" alt="Shipment" title="Shipment"><?php echo __('Shipment', 'mrkv-ua-shipping'); ?></h3>
	<p><?php echo __('Fill in the default shipping data for the shipment', 'mrkv-ua-shipping'); ?></p>
	<hr class="mrkv-ua-ship__hr">
	<div class="admin_ua_ship_morkva_settings_row">
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line">
				<h4><?php echo __('Type', 'mrkv-ua-shipping'); ?></h4>
				<div class="admin_ua_ship_morkva_settings_row">
					<?php
						$data = isset(MRKV_SHIPPING_SETTINGS['shipment']['type']) ? MRKV_SHIPPING_SETTINGS['shipment']['type'] : '';
						echo $mrkv_global_option_generator->get_input_radio(__('Parcel', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[shipment][type]', 'Parcel', $data, MRKV_OPTION_OBJECT_NAME . '_shipment_type_parcel', 'Parcel');
						echo $mrkv_global_option_generator->get_input_radio(__('Pallet', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[shipment][type]', 'Pallet', $data, MRKV_OPTION_OBJECT_NAME . '_shipment_type_pallet', 'Parcel');
					?>
				</div>
			</div>
		</div>
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line">
				<h4><?php echo __('Payment type (Sender-related)', 'mrkv-ua-shipping'); ?></h4>
				<div class="admin_ua_ship_morkva_settings_row">
					<?php
						$data = isset(MRKV_SHIPPING_SETTINGS['shipment']['payment']) ? MRKV_SHIPPING_SETTINGS['shipment']['payment'] : '';
						echo $mrkv_global_option_generator->get_input_radio(__('Cash', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[shipment][payment]', 'Cash', $data, MRKV_OPTION_OBJECT_NAME . '_shipment_payment_cash', 'Cash');

						$label = __('NonCash', 'mrkv-ua-shipping') . '<span class="mrkv-up-ship-tooltip"><img src="' . MRKV_UA_SHIPPING_ASSETS_URL . '/images/global/info-icon.svg' .'" ><div class="mrkv-up-ship-tooltip__data">' . __('Cashless payment for the sender is available only if the contract is signed.', 'mrkv-ua-shipping') . '</div></span>';
						echo $mrkv_global_option_generator->get_input_radio($label, MRKV_OPTION_OBJECT_NAME . '[shipment][payment]', 'NonCash', $data, MRKV_OPTION_OBJECT_NAME . '_shipment_payment_cashless', 'Cash');
					?>
				</div>
			</div>
		</div>
	</div>
	<div class="admin_ua_ship_morkva_settings_row">
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line">
				<h4><?php echo __('Weight, kg', 'mrkv-ua-shipping'); ?></h4>
				<?php 
					$data = isset(MRKV_SHIPPING_SETTINGS['shipment']['weight']) ? MRKV_SHIPPING_SETTINGS['shipment']['weight'] : '';

					echo $mrkv_global_option_generator->get_input_number('', MRKV_OPTION_OBJECT_NAME . '[shipment][weight]', $data, MRKV_OPTION_OBJECT_NAME. '_shipment_weight' , '', '', '');
				?>
			</div>
		</div>
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line">
				<h4><?php echo __('Dimensions, cm', 'mrkv-ua-shipping'); ?></h4>
				<div class="adm_morkva_row_size">
					<div class="adm_morkva_row_size__col">
						<span><?php echo __('Length', 'mrkv-ua-shipping'); ?></span>
						<?php 
							$data = isset(MRKV_SHIPPING_SETTINGS['shipment']['length']) ? MRKV_SHIPPING_SETTINGS['shipment']['length'] : '';
							echo $mrkv_global_option_generator->get_input_number('', MRKV_OPTION_OBJECT_NAME . '[shipment][length]', $data, MRKV_OPTION_OBJECT_NAME. '_shipment_length' , '', '', '');
						?>
					</div>
					<span>
						<svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M8 18C5.17157 18 3.75736 18 2.87868 17.1213C2 16.2426 2 14.8284 2 12C2 9.17157 2 7.75736 2.87868 6.87868C3.75736 6 5.17157 6 8 6C10.8284 6 12.2426 6 13.1213 6.87868C14 7.75736 14 9.17157 14 12" stroke="#ed6230" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M10 12C10 14.8284 10 16.2426 10.8787 17.1213C11.7574 18 13.1716 18 16 18C18.8284 18 20.2426 18 21.1213 17.1213C21.4211 16.8215 21.6186 16.4594 21.7487 16M22 12C22 9.17157 22 7.75736 21.1213 6.87868C20.2426 6 18.8284 6 16 6" stroke="#ed6230" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
					</span>
					<div class="adm_morkva_row_size__col">
						<span><?php echo __('Width', 'mrkv-ua-shipping'); ?></span>
						<?php 
							$data = isset(MRKV_SHIPPING_SETTINGS['shipment']['width']) ? MRKV_SHIPPING_SETTINGS['shipment']['width'] : '';
							echo $mrkv_global_option_generator->get_input_number('', MRKV_OPTION_OBJECT_NAME . '[shipment][width]', $data, MRKV_OPTION_OBJECT_NAME. '_shipment_width' , '', '', '');
						?>
					</div>
					<span>
						<svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M8 18C5.17157 18 3.75736 18 2.87868 17.1213C2 16.2426 2 14.8284 2 12C2 9.17157 2 7.75736 2.87868 6.87868C3.75736 6 5.17157 6 8 6C10.8284 6 12.2426 6 13.1213 6.87868C14 7.75736 14 9.17157 14 12" stroke="#ed6230" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M10 12C10 14.8284 10 16.2426 10.8787 17.1213C11.7574 18 13.1716 18 16 18C18.8284 18 20.2426 18 21.1213 17.1213C21.4211 16.8215 21.6186 16.4594 21.7487 16M22 12C22 9.17157 22 7.75736 21.1213 6.87868C20.2426 6 18.8284 6 16 6" stroke="#ed6230" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
					</span>
					<div class="adm_morkva_row_size__col">
						<span><?php echo __('Height', 'mrkv-ua-shipping'); ?></span>
						<?php 
							$data = isset(MRKV_SHIPPING_SETTINGS['shipment']['height']) ? MRKV_SHIPPING_SETTINGS['shipment']['height'] : '';
							echo $mrkv_global_option_generator->get_input_number('', MRKV_OPTION_OBJECT_NAME . '[shipment][height]', $data, MRKV_OPTION_OBJECT_NAME. '_shipment_height' , '', '', '');
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="admin_ua_ship_morkva_settings_line admin_ua_ship_morkva_one_data">
		<?php 
			$data = isset(MRKV_SHIPPING_SETTINGS['shipment']['volume']) ? MRKV_SHIPPING_SETTINGS['shipment']['volume'] : '';
			$description = __('It is calculated automatically according to the dimensions in the settings.', 'mrkv-ua-shipping');

			echo $mrkv_global_option_generator->get_input_number(__('Volumetric weight', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[shipment][volume]', $data, MRKV_OPTION_OBJECT_NAME. '_shipment_volume' , '', '', $description, 'readonly');
		?>
		<p><strong><?php echo __('These standard weight and dimensions apply when products do not have ones of their own', 'mrkv-ua-shipping'); ?></strong></p>
	</div>
	<div class="admin_ua_ship_morkva_settings_row">
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line">
				<?php 
					$data = isset(MRKV_SHIPPING_SETTINGS['shipment']['cart_total']) ? MRKV_SHIPPING_SETTINGS['shipment']['cart_total'] : '';
					$mrkv_ua_shipping_cart_total = array(
						'subtotal' => __('From the intermediate cost of the order (excluding promotional codes)', 'mrkv-ua-shipping'),
						'total' => __('Of the total cost of the order (including promotional codes)', 'mrkv-ua-shipping'),
					);

					$description = __('Choose how much the shipping cost will be calculated', 'mrkv-ua-shipping');

					echo $mrkv_global_option_generator->get_select_simple(__('Free shipping calculation', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[shipment][cart_total]', $mrkv_ua_shipping_cart_total, $data, MRKV_OPTION_OBJECT_NAME . '_shipment_cart_total' , __('Choose a cart cost', 'mrkv-ua-shipping'), $description);
				?>
			</div>
		</div>
		<div class="col-mrkv-5">
		</div>
	</div>
	<div class="admin_ua_ship_morkva_settings_line">
		<?php
			$data = isset(MRKV_SHIPPING_SETTINGS['shipment']['description']) ? MRKV_SHIPPING_SETTINGS['shipment']['description'] : '';
			$description = __('Maximum number of characters:', 'mrkv-ua-shipping') . ' 100';

			echo $mrkv_global_option_generator->get_textarea(__('Description of the shipment', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[shipment][description]', $data, MRKV_OPTION_OBJECT_NAME. '_shipment_description' , '', __('For example, products for children...', 'mrkv-ua-shipping'), $description);
		?>
		<div class="admin_ua_ship_morkva_settings_row admin_ua_ship_morkva_settings_row_btns">
			<h4><?php echo __('Click to insert the shortcode:', 'mrkv-ua-shipping'); ?></h4>
			<div data-added="[order_id]" class="button button-primary adm-textarea-btn"><?php echo __('Order number', 'mrkv-ua-shipping'); ?></div>
			<div data-added="[product_list]" class="button button-primary adm-textarea-btn"><?php echo __('List of products', 'mrkv-ua-shipping'); ?></div>
			<div data-added="[product_list_qa]" class="button button-primary adm-textarea-btn"><?php echo __('List of goods (with articles and quantity)', 'mrkv-ua-shipping'); ?></div>
			<div data-added="[product_list_a]" class="button button-primary adm-textarea-btn"><?php echo __('List of articles with quantity', 'mrkv-ua-shipping'); ?></div>
			<div data-added="[quantity]" class="button button-primary adm-textarea-btn"><?php echo __('Quantity of goods', 'mrkv-ua-shipping'); ?></div>
			<div data-added="[quantity_p]" class="button button-primary adm-textarea-btn"><?php echo __(' Number of positions', 'mrkv-ua-shipping'); ?></div>
		</div>
	</div>
</section>
<section id="email_settings" class="mrkv_up_ship_shipping_tab_block">
	<h2><img src="<?php echo MRKV_UA_SHIPPING_ASSETS_URL . '/images/global/mention-square-icon.svg'; ?>" alt="Sending email from TTN" title="Sending email from TTN"><?php echo __('Email settings', 'mrkv-ua-shipping'); ?></h2>
	<p><?php echo __('Create a custom message that will be sent after creating the shipment', 'mrkv-ua-shipping'); ?></p>
	<hr class="mrkv-ua-ship__hr">
	<div class="admin_ua_ship_morkva_settings_row">
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
				<?php 
					$data = '';
					$description = '<span class="mrkv-ua-ship-only-pro">' . __('Only in the Pro version', 'mrkv-ua-shipping') . '</span>';

					echo $mrkv_global_option_generator->get_input_text(__('Email subject', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[email][subject]', $data, MRKV_OPTION_OBJECT_NAME. '_email_subject' , '', __('Enter the subject...', 'mrkv-ua-shipping'), $description, 'readonly');
				?>
			</div>
		</div>
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
				<?php
					$data = '';
					echo $mrkv_global_option_generator->get_input_checkbox(__('Send automatically', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[email][auto]', $data, MRKV_OPTION_OBJECT_NAME . '_email_auto', '', 'disabled');
				?>
				<?php echo '<span class="mrkv-ua-ship-only-pro">' . __('Only in the Pro version', 'mrkv-ua-shipping') . '</span>'; ?>
				<?php echo '<p class="mrkv-ua-ship-description">' . __('Enable if you want to send an email automatically after a shipment is created', 'mrkv-ua-shipping') . '</p>'; ?>
			</div>
		</div>
	</div>
	<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
		<?php
			$data = '';
			$description = '<span class="mrkv-ua-ship-only-pro">' . __('Only in the Pro version', 'mrkv-ua-shipping') . '</span>';

			echo $mrkv_global_option_generator->get_textarea(__('Email text', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[email][content]', $data, MRKV_OPTION_OBJECT_NAME. '_email_content' , '', __('Enter the email...', 'mrkv-ua-shipping'), $description, 'readonly');
		?>
		<div class="admin_ua_ship_morkva_settings_row admin_ua_ship_morkva_settings_row_btns">
			<div class="button button-primary adm-textarea-btn"><?php echo __('Default email template', 'mrkv-ua-shipping'); ?></div>
		</div>
	</div>
</section>
<section id="automation_settings" class="mrkv_up_ship_shipping_tab_block">
	<h2><img src="<?php echo MRKV_UA_SHIPPING_ASSETS_URL . '/images/global/automation-icon.svg'; ?>" alt="Automation Settings" title="Automation Settings"><?php echo __('Automation Settings', 'mrkv-ua-shipping'); ?></h2>
	<p><?php echo __('Connect automation when working with shipments', 'mrkv-ua-shipping'); ?></p>
	<hr class="mrkv-ua-ship__hr">
	<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
		<?php
			$data = '';
			echo $mrkv_global_option_generator->get_input_checkbox(__('Payment control', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[automation][payment_control]', $data, MRKV_OPTION_OBJECT_NAME . '_automation_payment_control', '', 'disabled' );
		?>
		<?php echo '<span class="mrkv-ua-ship-only-pro">' . __('Only in the Pro version', 'mrkv-ua-shipping') . '</span>'; ?>
	</div>
	<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
		<?php
			$all_order_statuses = wc_get_order_statuses();
			$enabled_gateways = array_filter(WC()->payment_gateways->payment_gateways(), function ($gateway) {
	            return 'yes' === $gateway->enabled;
	        });
	        $payment_methods = array();

	        foreach($enabled_gateways as $id => $gateway)
	        {
	        	$payment_methods[$id] = $gateway->get_title();
	        }

			$data = '';
			echo $mrkv_global_option_generator->get_input_checkbox(__('Automatically create shipments', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[automation][autocreate][enabled]', $data, MRKV_OPTION_OBJECT_NAME . '_automation_autocreate_enabled', '', 'disabled' );
		?>
		<?php echo '<span class="mrkv-ua-ship-only-pro">' . __('Only in the Pro version', 'mrkv-ua-shipping') . '</span>'; ?>
	</div>
	<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
		<?php
			$data = '';
			echo $mrkv_global_option_generator->get_input_checkbox(__('Automatically change order status', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[automation][status][enabled]', $data, MRKV_OPTION_OBJECT_NAME . '_automation_status_enabled', '', 'disabled' );
		?>
		<?php echo '<span class="mrkv-ua-ship-only-pro">' . __('Only in the Pro version', 'mrkv-ua-shipping') . '</span>'; ?>
	</div>
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

					echo $mrkv_global_option_generator->get_select_simple(__('Position of plugin fields in Checkout', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[checkout][position]', $senders_type_list, $data, MRKV_OPTION_OBJECT_NAME . '_checkout_position' , __('Choose a position', 'mrkv-ua-shipping'), $description);
				?>
			</div>
		</div>
	</div>
	<div class="admin_ua_ship_morkva_settings_row">
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line">
				<?php
					$data = isset(MRKV_SHIPPING_SETTINGS['checkout']['middlename']['enabled']) ? MRKV_SHIPPING_SETTINGS['checkout']['middlename']['enabled'] : '';
					echo $mrkv_global_option_generator->get_input_checkbox(__('Enable patronymic', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[checkout][middlename][enabled]', $data, MRKV_OPTION_OBJECT_NAME . '_checkout_middlename_enabled', );
				?>
				<?php echo '<p class="mrkv-ua-ship-description">' . __('Remove the patronymic field from the checkout page', 'mrkv-ua-shipping') . '</p>'; ?>
			</div>
		</div>
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line">
				<?php
					$data = isset(MRKV_SHIPPING_SETTINGS['checkout']['middlename']['required']) ? MRKV_SHIPPING_SETTINGS['checkout']['middlename']['required'] : '';
					echo $mrkv_global_option_generator->get_input_checkbox(__('Patronymic is required', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[checkout][middlename][required]', $data, MRKV_OPTION_OBJECT_NAME . '_checkout_middlename_required', );
				?>
				<?php echo '<p class="mrkv-ua-ship-description">' . __('Make the middle name field mandatory', 'mrkv-ua-shipping') . '</p>'; ?>
			</div>
		</div>
	</div>
	<div class="admin_ua_ship_morkva_settings_row">
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line">
				<?php 
					$data = isset(MRKV_SHIPPING_SETTINGS['checkout']['middlename']['position']) ? MRKV_SHIPPING_SETTINGS['checkout']['middlename']['position'] : '';
					$middlename_position = array(
						'default' => __('Default', 'mrkv-ua-shipping'),
						'billing_last_name' => __('After the last name', 'mrkv-ua-shipping'),
						'billing_first_name' => __('After the first name', 'mrkv-ua-shipping'),
					);

					$description = __('Select the middlename field position on the checkout page', 'mrkv-ua-shipping');

					echo $mrkv_global_option_generator->get_select_simple(__('Position of middlename in Checkout', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[checkout][middlename][position]', $middlename_position, $data, MRKV_OPTION_OBJECT_NAME . '_checkout_middlename_position' , __('Choose a position', 'mrkv-ua-shipping'), $description);
				?>
			</div>
		</div>
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line">
				<?php
					$data = isset(MRKV_SHIPPING_SETTINGS['checkout']['hide_saving_data']) ? MRKV_SHIPPING_SETTINGS['checkout']['hide_saving_data'] : '';
					echo $mrkv_global_option_generator->get_input_checkbox(__('Save customer selected fields', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[checkout][hide_saving_data]', $data, MRKV_OPTION_OBJECT_NAME . '_checkout_hide_saving_data', );
				?>
				<?php echo '<p class="mrkv-ua-ship-description">' . __('Enable to store selected delivery city and warehouse/postamat in session cookies (may not work if privacy settings enabled in user’s browser)', 'mrkv-ua-shipping') . '</p>'; ?>
			</div>
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
					echo $mrkv_global_option_generator->get_input_checkbox(__('Enable debug log', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[debug][log]', $data, MRKV_OPTION_OBJECT_NAME . '_debug_log', );
				?>
				<?php echo '<p class="mrkv-ua-ship-description">' . __('Enable to receive request error logs', 'mrkv-ua-shipping') . '</p>'; ?>
			</div>
		</div>
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line">
				<?php
					$data = isset(MRKV_SHIPPING_SETTINGS['debug']['query']) ? MRKV_SHIPPING_SETTINGS['debug']['query'] : '';
					echo $mrkv_global_option_generator->get_input_checkbox(__('Enable debug query', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[debug][query]', $data, MRKV_OPTION_OBJECT_NAME . '_debug_query', );
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