<?php 
	$shipping_slug = 'nova-poshta';
	$shipping_slug_option = $shipping_slug . '_m_ua_settings';
	$mrk_ua_ship_nova_settings = get_option($shipping_slug_option);
?>
<form data-ship="<?php echo $shipping_slug; ?>">
	<input type="hidden" name="order_id" value="">
	<h3>
		<img src="<?php echo MRKV_UA_SHIPPING_IMG_URL . '/global'; ?>/user-icon.svg" alt="<?php echo __('Sender', 'mrkv-ua-shipping'); ?>" title="<?php echo __('Sender', 'mrkv-ua-shipping'); ?>">
		<span><?php echo __('Sender\'s data', 'mrkv-ua-shipping'); ?></span>
	</h3>
	<div class="admin_ua_ship_morkva_settings_row">
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line">
				<label>
					<?php echo __('Sender contact person', 'mrkv-ua-shipping'); ?>
					<?php
						$data = isset($mrk_ua_ship_nova_settings['sender']['ref']) ? $mrk_ua_ship_nova_settings['sender']['ref'] : '';

						if(!$data)
						{
							?>
								<div class="admin_ua_ship_morkva__notification mrkv-notification-red"><?php echo __('Sender Ref Incorrect', 'mrkv-ua-shipping'); ?></div>
							<?php
						}
					?>
				</label>
				<?php
					if(isset($mrk_ua_ship_nova_settings['sender']['list']))
					{
						$senders = json_decode(base64_decode($mrk_ua_ship_nova_settings['sender']['list']), true);

						echo $mrkv_global_option_generator->get_select_tag('', 'mrkv_ua_ship_invoice_sender_ref', $senders, $data, $shipping_slug_option . '_mrkv_ua_ship_invoice_sender_ref' , __('Choose a sender', 'mrkv-ua-shipping'));

						if(isset($senders[0]['attr']) && !empty($senders))
						{
							foreach($senders[0]['attr'] as $sender_data_key => $sender_data_val)
							{
								if($sender_data_key == 'ref')
								{
									continue;
								}
								$data = isset($mrk_ua_ship_nova_settings['sender'][$sender_data_key]) ? $mrk_ua_ship_nova_settings['sender'][$sender_data_key] : '';
								echo $mrkv_global_option_generator->get_input_hidden('mrkv_ua_ship_invoice_sender_' . $sender_data_key, $data, $shipping_slug_option . '_mrkv_ua_ship_invoice_sender_' . $sender_data_key);
							}
						}
					}
				?>
			</div>
		</div>
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line">
				<label>
					<?php echo __('Address from', 'mrkv-ua-shipping'); ?>
					<?php
					$full_address = '';
					$full_address .= isset($mrk_ua_ship_nova_settings['sender']['city']['name']) ? $mrk_ua_ship_nova_settings['sender']['city']['name'] . ', ' : '';
					$data_error = '';

					$data = isset($mrk_ua_ship_nova_settings['sender']['address_type']) ? $mrk_ua_ship_nova_settings['sender']['address_type'] : '';

					if($data == 'W')
					{
						$full_address .= isset($mrk_ua_ship_nova_settings['sender']['warehouse']['name']) ? $mrk_ua_ship_nova_settings['sender']['warehouse']['name'] . ' ' : '';
						$data_error = isset($mrk_ua_ship_nova_settings['sender']['warehouse']['ref']) ? $mrk_ua_ship_nova_settings['sender']['warehouse']['ref'] : '';

						if(!$data_error)
						{
							?>
								<div class="admin_ua_ship_morkva__notification mrkv-notification-red"><?php echo __('Warehouse Ref Incorrect', 'mrkv-ua-shipping'); ?></div>
							<?php
						}
					}
					elseif($data == 'D')
					{
						$full_address .= (isset($mrk_ua_ship_nova_settings['sender']['street']['name']) && $mrk_ua_ship_nova_settings['sender']['street']['name']) ? $mrk_ua_ship_nova_settings['sender']['street']['name'] . ' ' : '';
						$full_address .= (isset($mrk_ua_ship_nova_settings['sender']['street']['house']) && $mrk_ua_ship_nova_settings['sender']['street']['house']) ? $mrk_ua_ship_nova_settings['sender']['street']['house'] . ', ' : '';
						$full_address .= (isset($mrk_ua_ship_nova_settings['sender']['street']['flat']) && $mrk_ua_ship_nova_settings['sender']['street']['flat']) ? __('flat/office', 'mrkv-ua-shipping') . ' ' . $mrk_ua_ship_nova_settings['sender']['street']['flat'] . ' ' : '';
					}
					else
					{
						$full_address .= __('Empty data', 'mrkv-ua-shipping');
					}
				?>
				</label>
				<p><?php echo $full_address; ?></p>
			</div>
		</div>
	</div>
	<hr class="mrkv-ua-ship__hr">
	<h3>
		<img src="<?php echo MRKV_UA_SHIPPING_IMG_URL . '/global'; ?>/user-icon.svg" alt="<?php echo __('Recipient', 'mrkv-ua-shipping'); ?>" title="<?php echo __('Recipient', 'mrkv-ua-shipping'); ?>">
		<span><?php echo __('Recipient\'s data', 'mrkv-ua-shipping'); ?></span>
	</h3>
	<div class="admin_ua_ship_morkva_settings_row">
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_row admin_ua_ship_morkva_settings_row_mb-0">
				<div class="col-mrkv-5">
					<div class="admin_ua_ship_morkva_settings_line">
						<?php
							echo $mrkv_global_option_generator->get_input_text(__('Firstname', 'mrkv-ua-shipping'), 'mrkv_ua_ship_invoice_first_name', '', $shipping_slug_option . '_mrkv_ua_ship_invoice_first_name' , '', __('Enter the Firstname', 'mrkv-ua-shipping'), '');
						?>
					</div>	
				</div>
				<div class="col-mrkv-5">
					<div class="admin_ua_ship_morkva_settings_line">
						<?php
							echo $mrkv_global_option_generator->get_input_text(__('Lastname', 'mrkv-ua-shipping'), 'mrkv_ua_ship_invoice_last_name', '', $shipping_slug_option . '_mrkv_ua_ship_invoice_last_name' , '', __('Enter the Lastname', 'mrkv-ua-shipping'), '');
						?>
					</div>	
				</div>
			</div>
			<div class="admin_ua_ship_morkva_settings_row admin_ua_ship_morkva_settings_row_mb-0">
				<div class="col-mrkv-5">
					<div class="admin_ua_ship_morkva_settings_line">
						<?php
							echo $mrkv_global_option_generator->get_input_text(__('Patronymic', 'mrkv-ua-shipping'), 'mrkv_ua_ship_invoice_patronymic', '', $shipping_slug_option . '_mrkv_ua_ship_invoice_patronymic' , '', __('Enter the Patronymic', 'mrkv-ua-shipping'), '');
						?>
					</div>	
				</div>
				<div class="col-mrkv-5">
					<div class="admin_ua_ship_morkva_settings_line">
						<?php
							echo $mrkv_global_option_generator->get_input_text(__('Phone', 'mrkv-ua-shipping'), 'mrkv_ua_ship_invoice_phone', '', $shipping_slug_option . '_mrkv_ua_ship_invoice_phone' , '', __('Enter the Phone', 'mrkv-ua-shipping'), '');
						?>
					</div>	
				</div>
			</div>
		</div>
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line">
				<label>
					<?php echo __('Address to', 'mrkv-ua-shipping'); ?>
				</label>
				<p class="mrkv_ua_ship_invoice_address"></p>
			</div>
		</div>
	</div>
	<hr class="mrkv-ua-ship__hr">
	<h3>
		<img src="<?php echo MRKV_UA_SHIPPING_IMG_URL . '/global'; ?>/tuning-icon.svg" alt="<?php echo __('Parameters of the shipment', 'mrkv-ua-shipping'); ?>" title="<?php echo __('Parameters of the shipment', 'mrkv-ua-shipping'); ?>">
		<span><?php echo __('Parameters of the shipment', 'mrkv-ua-shipping'); ?></span>
	</h3>
	<p><?php echo __('Check the correctness of the shipment data, or fill in if necessary', 'mrkv-ua-shipping'); ?></p>
	<div class="admin_ua_ship_morkva_settings_row admin_ua_ship_morkva_settings_row_mb-0">
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line">
				<label>
					<?php echo __('Type of shipment', 'mrkv-ua-shipping'); ?>
				</label>
				<div class="admin_ua_ship_morkva_settings_row">
					<?php
						$data = isset($mrk_ua_ship_nova_settings['shipment']['type']) ? $mrk_ua_ship_nova_settings['shipment']['type'] : '';
						echo $mrkv_global_option_generator->get_input_radio(__('Parcel', 'mrkv-ua-shipping'), 'mrkv_ua_ship_invoice_shipment_type', 'Parcel', $data, $shipping_slug_option . '_mrkv_ua_ship_invoice_shipment_type_parcel', 'Parcel');
						echo $mrkv_global_option_generator->get_input_radio(__('Pallet', 'mrkv-ua-shipping'), 'mrkv_ua_ship_invoice_shipment_type', 'Pallet', $data, $shipping_slug_option . '_mrkv_ua_ship_invoice_shipment_type_pallet', 'Parcel');
					?>
				</div>
			</div>
		</div>
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line">
				<label>
					<?php echo __('Payer of delivery', 'mrkv-ua-shipping'); ?>
				</label>
				<div class="admin_ua_ship_morkva_settings_row">
					<?php
						$data = isset($mrk_ua_ship_nova_settings['payer']['delivery']) ? $mrk_ua_ship_nova_settings['payer']['delivery'] : '';
						echo $mrkv_global_option_generator->get_input_radio(__('Recipient', 'mrkv-ua-shipping'), 'mrkv_ua_ship_invoice_payer_delivery', 'Recipient', $data, $shipping_slug_option . '_mrkv_ua_ship_invoice_payer_delivery_recipient', 'Recipient');
						echo $mrkv_global_option_generator->get_input_radio(__('Sender', 'mrkv-ua-shipping'), 'mrkv_ua_ship_invoice_payer_delivery', 'Sender', $data, $shipping_slug_option . '_mrkv_ua_ship_invoice_payer_delivery_sender', 'Recipient');
					?>
				</div>
			</div>
		</div>
	</div>
	<div class="admin_ua_ship_morkva_settings_row admin_ua_ship_morkva_settings_row_mb-0">
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line">
				<label>
					<?php echo __('Weight of the shipment, kg', 'mrkv-ua-shipping'); ?>
				</label>
				<?php 
					$data = isset($mrk_ua_ship_nova_settings['shipment']['weight']) ? $mrk_ua_ship_nova_settings['shipment']['weight'] : '';

					echo $mrkv_global_option_generator->get_input_number('', 'mrkv_ua_ship_invoice_shipment_weight', $data, $shipping_slug_option . '_mrkv_ua_ship_invoice_shipment_weight' , '', '', '');
				?>
			</div>
		</div>
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line">
				<label>
					<?php echo __('Dimensions of the shipment, cm', 'mrkv-ua-shipping'); ?>
				</label>
				<div class="adm_morkva_row_size">
					<div class="adm_morkva_row_size__col">
						<span><?php echo __('Length', 'mrkv-ua-shipping'); ?></span>
						<?php 
							$data = isset($mrk_ua_ship_nova_settings['shipment']['length']) ? $mrk_ua_ship_nova_settings['shipment']['length'] : '';
							echo $mrkv_global_option_generator->get_input_number('', 'mrkv_ua_ship_invoice_shipment_length', $data, $shipping_slug_option. '_mrkv_ua_ship_invoice_shipment_length' , '', '', '');
						?>
					</div>
					<span>
						<svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M8 18C5.17157 18 3.75736 18 2.87868 17.1213C2 16.2426 2 14.8284 2 12C2 9.17157 2 7.75736 2.87868 6.87868C3.75736 6 5.17157 6 8 6C10.8284 6 12.2426 6 13.1213 6.87868C14 7.75736 14 9.17157 14 12" stroke="#ed6230" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M10 12C10 14.8284 10 16.2426 10.8787 17.1213C11.7574 18 13.1716 18 16 18C18.8284 18 20.2426 18 21.1213 17.1213C21.4211 16.8215 21.6186 16.4594 21.7487 16M22 12C22 9.17157 22 7.75736 21.1213 6.87868C20.2426 6 18.8284 6 16 6" stroke="#ed6230" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
					</span>
					<div class="adm_morkva_row_size__col">
						<span><?php echo __('Width', 'mrkv-ua-shipping'); ?></span>
						<?php 
							$data = isset($mrk_ua_ship_nova_settings['shipment']['width']) ? $mrk_ua_ship_nova_settings['shipment']['width'] : '';
							echo $mrkv_global_option_generator->get_input_number('', 'mrkv_ua_ship_invoice_shipment_width', $data, $shipping_slug_option. '_mrkv_ua_ship_invoice_shipment_width' , '', '', '');
						?>
					</div>
					<span>
						<svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M8 18C5.17157 18 3.75736 18 2.87868 17.1213C2 16.2426 2 14.8284 2 12C2 9.17157 2 7.75736 2.87868 6.87868C3.75736 6 5.17157 6 8 6C10.8284 6 12.2426 6 13.1213 6.87868C14 7.75736 14 9.17157 14 12" stroke="#ed6230" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M10 12C10 14.8284 10 16.2426 10.8787 17.1213C11.7574 18 13.1716 18 16 18C18.8284 18 20.2426 18 21.1213 17.1213C21.4211 16.8215 21.6186 16.4594 21.7487 16M22 12C22 9.17157 22 7.75736 21.1213 6.87868C20.2426 6 18.8284 6 16 6" stroke="#ed6230" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
					</span>
					<div class="adm_morkva_row_size__col">
						<span><?php echo __('Height', 'mrkv-ua-shipping'); ?></span>
						<?php 
							$data = isset($mrk_ua_ship_nova_settings['shipment']['height']) ? $mrk_ua_ship_nova_settings['shipment']['height'] : '';
							echo $mrkv_global_option_generator->get_input_number('', 'mrkv_ua_ship_invoice_shipment_height', $data, $shipping_slug_option. '_mrkv_ua_ship_invoice_shipment_height' , '', '', '');
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="admin_ua_ship_morkva_settings_row admin_ua_ship_morkva_settings_row_mb-0">
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line">
				<label>
					<?php echo __('The total weight of the shipment', 'mrkv-ua-shipping'); ?>
				</label>
				<?php 
					$data = isset($mrk_ua_ship_nova_settings['shipment']['volume']) ? $mrk_ua_ship_nova_settings['shipment']['volume'] : '';

					echo $mrkv_global_option_generator->get_input_number('', 'mrkv_ua_ship_invoice_shipment_volume', $data, $shipping_slug_option . '_mrkv_ua_ship_invoice_shipment_volume' , '', '', '', 'readonly');
				?>
			</div>
		</div>
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line">
				<label>
					<?php echo __('Number of seats', 'mrkv-ua-shipping'); ?>
				</label>
				<?php 
					echo $mrkv_global_option_generator->get_input_number('', 'mrkv_ua_ship_invoice_shipment_seats', 1, $shipping_slug_option . '_mrkv_ua_ship_invoice_shipment_seats' , '', '', '', '', '1.00');
				?>
			</div>
		</div>
	</div>
	<div class="admin_ua_ship_morkva_settings_line">
		<?php
			$data = isset($mrk_ua_ship_nova_settings['shipment']['description']) ? $mrk_ua_ship_nova_settings['shipment']['description'] : '';
			$description = __('Maximum number of characters:', 'mrkv-ua-shipping') . ' 100';

			echo $mrkv_global_option_generator->get_textarea(__('Description of the shipment', 'mrkv-ua-shipping'), 'mrkv_ua_ship_invoice_shipment_description', $data, $shipping_slug_option . 'mrkv_ua_ship_invoice_shipment_description' , '', __('For example, products for children...', 'mrkv-ua-shipping'), $description);
		?>
	</div>
	<hr class="mrkv-ua-ship__hr">
	<h3>
		<img src="<?php echo MRKV_UA_SHIPPING_IMG_URL . '/global'; ?>/box-icon.svg" alt="<?php echo __('Additional services', 'mrkv-ua-shipping'); ?>" title="<?php echo __('Additional services', 'mrkv-ua-shipping'); ?>">
		<span><?php echo __('Additional services', 'mrkv-ua-shipping'); ?></span>
	</h3>
	<p><?php echo __('Use additional services as needed', 'mrkv-ua-shipping'); ?></p>
	<div class="admin_ua_ship_morkva_settings_row mrkv-addittional-row">
		<div class="col-mrkv-10">
			<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
				<?php
					echo $mrkv_global_option_generator->get_input_checkbox(__('Money transfer', 'mrkv-ua-shipping'), '', '', $shipping_slug_option . 'mrkv_ua_ship_invoice_money_transfer', '', 'disabled');
				?>
				<?php echo '<span class="mrkv-ua-ship-only-pro">' . __('Only in the Pro version', 'mrkv-ua-shipping') . '</span>'; ?>
			</div>
		</div>
	</div>
</form>