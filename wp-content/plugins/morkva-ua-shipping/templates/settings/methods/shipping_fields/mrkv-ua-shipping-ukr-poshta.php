<section id="basic_settings" class="mrkv_up_ship_shipping_tab_block active">
	<h2><img src="<?php echo MRKV_UA_SHIPPING_ASSETS_URL . '/images/global/settings-icon.svg'; ?>" alt="Basic settings" title="Basic settings"><?php echo __('Basic settings', 'mrkv-ua-shipping'); ?></h2>
	<p><?php echo __('For the shipping method to work, you need to get API keys from Ukrposhta (conclude an agreement at the branch). Details:', 'mrkv-ua-shipping'); ?> <a target="blanc" href="https://ukrposhta.ua/ukrposhta-dlya-biznesu.html">ukrposhta.ua</a></p>
	<hr class="mrkv-ua-ship__hr">
	<div class="admin_ua_ship_morkva_settings_row">
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line">
				<?php 
					$data = isset(MRKV_SHIPPING_SETTINGS['production_bearer_ecom']) ? MRKV_SHIPPING_SETTINGS['production_bearer_ecom'] : '';
					$description = __('Find it in an email from your Ukrposhta customer manager', 'mrkv-ua-shipping');
					$label = __('PRODUCTION BEARER eCom', 'mrkv-ua-shipping');
					global $mrkv_global_option_generator;
					global $mrkv_global_shipping_object;

					echo $mrkv_global_option_generator->get_input_text($label, MRKV_OPTION_OBJECT_NAME . '[production_bearer_ecom]', $data, MRKV_OPTION_OBJECT_NAME. '_production_bearer_ecom' , '', __('Enter the key...', 'mrkv-ua-shipping'), $description);
				?>
			</div>
		</div>
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line">
				<?php 
					$data = isset(MRKV_SHIPPING_SETTINGS['production_bearer_status_tracking']) ? MRKV_SHIPPING_SETTINGS['production_bearer_status_tracking'] : '';
					$description = __('Find it in an email from your Ukrposhta customer manager', 'mrkv-ua-shipping');
					echo $mrkv_global_option_generator->get_input_text(__('PRODUCTION BEARER StatusTracking', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[production_bearer_status_tracking]', $data, MRKV_OPTION_OBJECT_NAME. '_production_bearer_status_tracking' , '', __('Enter the key...', 'mrkv-ua-shipping'), $description);
				?>
			</div>
		</div>
	</div>
	<div class="admin_ua_ship_morkva_settings_row">
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line">
				<?php 
					$data = isset(MRKV_SHIPPING_SETTINGS['production_cp_token']) ? MRKV_SHIPPING_SETTINGS['production_cp_token'] : '';
					$description = __('Find it in an email from your Ukrposhta customer manager', 'mrkv-ua-shipping');
					echo $mrkv_global_option_generator->get_input_text(__('PROD_COUNTERPARTY TOKEN', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[production_cp_token]', $data, MRKV_OPTION_OBJECT_NAME. '_production_cp_token' , '', __('Enter the key...', 'mrkv-ua-shipping'), $description);
				?>
			</div>
		</div>
		<div class="col-mrkv-5"></div>
	</div>
	<div class="admin_ua_ship_morkva_settings_line">
		<?php
			$data = isset(MRKV_SHIPPING_SETTINGS['test_mode']) ? MRKV_SHIPPING_SETTINGS['test_mode'] : '';
			echo $mrkv_global_option_generator->get_input_checkbox(__('Test mode', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[test_mode]', $data, MRKV_OPTION_OBJECT_NAME . '_test_mode', );
		?>
		<div class="admin_ua_ship_morkva_settings_line__inner">
			<div class="admin_ua_ship_morkva_settings_row">
				<div class="col-mrkv-5">
					<?php 
						$data = isset(MRKV_SHIPPING_SETTINGS['test_production_bearer_ecom']) ? MRKV_SHIPPING_SETTINGS['test_production_bearer_ecom'] : '';
						$description = __('Find it in an email from your Ukrposhta customer manager', 'mrkv-ua-shipping');
						$label = __('SANDBOX BEARER eCom', 'mrkv-ua-shipping');

						echo $mrkv_global_option_generator->get_input_text($label, MRKV_OPTION_OBJECT_NAME . '[test_production_bearer_ecom]', $data, MRKV_OPTION_OBJECT_NAME. '_test_production_bearer_ecom' , '', __('Enter the key...', 'mrkv-ua-shipping'), $description);
					?>
				</div>
				<div class="col-mrkv-5">
					<?php 
					$data = isset(MRKV_SHIPPING_SETTINGS['test_production_bearer_status_tracking']) ? MRKV_SHIPPING_SETTINGS['test_production_bearer_status_tracking'] : '';
					$description = __('Find it in an email from your Ukrposhta customer manager', 'mrkv-ua-shipping');
					echo $mrkv_global_option_generator->get_input_text(__('SANDBOX BEARER StatusTracking', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[test_production_bearer_status_tracking]', $data, MRKV_OPTION_OBJECT_NAME. '_test_production_bearer_status_tracking' , '', __('Enter the key...', 'mrkv-ua-shipping'), $description);
				?>
				</div>
			</div>
			<div class="admin_ua_ship_morkva_settings_row">
				<div class="col-mrkv-5">
					<?php 
						$data = isset(MRKV_SHIPPING_SETTINGS['test_production_cp_token']) ? MRKV_SHIPPING_SETTINGS['test_production_cp_token'] : '';
						$description = __('Find it in an email from your Ukrposhta customer manager', 'mrkv-ua-shipping');
						echo $mrkv_global_option_generator->get_input_text(__('SAND_COUNTERPARTY TOKEN', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[test_production_cp_token]', $data, MRKV_OPTION_OBJECT_NAME. '_test_production_cp_token' , '', __('Enter the key...', 'mrkv-ua-shipping'), $description);
					?>
				</div>
			</div>
		</div>
	</div>
</section>
<section id="domestic_settings" class="mrkv_up_ship_shipping_tab_block">
	<h2><img src="<?php echo MRKV_UA_SHIPPING_ASSETS_URL . '/images/global/box-icon.svg'; ?>" alt="Domestic settings" title="Domestic settings"><?php echo __('Domestic settings', 'mrkv-ua-shipping'); ?></h2>
	<p><?php echo __('Data is used in case of missing values when creating a shipment', 'mrkv-ua-shipping'); ?></p>
	<hr class="mrkv-ua-ship__hr">
	<h3><img src="<?php echo MRKV_UA_SHIPPING_ASSETS_URL . '/images/global/user-icon.svg'; ?>" alt="Sender settings" title="Sender settings"><?php echo __('Sender settings', 'mrkv-ua-shipping'); ?></h3>
	<div class="admin_ua_ship_morkva_settings_line">
		<div class="admin_ua_ship_morkva_settings_row mrkv_row_reverse">
			<div class="col-mrkv-5">
				<?php 
					$data_sender_type = isset(MRKV_SHIPPING_SETTINGS['sender']['type']) ? MRKV_SHIPPING_SETTINGS['sender']['type'] : '';
					$sender_lastname = isset(MRKV_SHIPPING_SETTINGS['sender']['individual']['lastname']) ? MRKV_SHIPPING_SETTINGS['sender']['individual']['lastname'] : '';
					$sender_firstname = isset(MRKV_SHIPPING_SETTINGS['sender']['individual']['name']) ? MRKV_SHIPPING_SETTINGS['sender']['individual']['name'] : '';
					$sender_middlename = isset(MRKV_SHIPPING_SETTINGS['sender']['individual']['middlename']) ? MRKV_SHIPPING_SETTINGS['sender']['individual']['middlename'] : '';
					$sender_phone = isset(MRKV_SHIPPING_SETTINGS['sender']['individual']['phone']) ? MRKV_SHIPPING_SETTINGS['sender']['individual']['phone'] : '';

					if($data_sender_type && $data_sender_type != 'INDIVIDUAL')
					{
						$data_sender_type = 'INDIVIDUAL';
						$sender_lastname = (isset(MRKV_SHIPPING_SETTINGS['sender']['company']['lastname']) && MRKV_SHIPPING_SETTINGS['sender']['company']['lastname']) ? MRKV_SHIPPING_SETTINGS['sender']['company']['lastname'] : $sender_lastname;

						if(!$sender_lastname){
							$sender_lastname = (isset(MRKV_SHIPPING_SETTINGS['sender']['private']['lastname']) && MRKV_SHIPPING_SETTINGS['sender']['private']['lastname']) ? MRKV_SHIPPING_SETTINGS['sender']['private']['lastname'] : $sender_lastname;
						}

						$sender_firstname = (isset(MRKV_SHIPPING_SETTINGS['sender']['company']['name']) && MRKV_SHIPPING_SETTINGS['sender']['company']['name']) ? MRKV_SHIPPING_SETTINGS['sender']['company']['name'] : $sender_firstname;

						if(!$sender_firstname)
						{
							$sender_firstname = (isset(MRKV_SHIPPING_SETTINGS['sender']['private']['name']) && MRKV_SHIPPING_SETTINGS['sender']['private']['name']) ? MRKV_SHIPPING_SETTINGS['sender']['private']['name'] : $sender_firstname;
						}

						$sender_middlename = (isset(MRKV_SHIPPING_SETTINGS['sender']['private']['middlename']) && MRKV_SHIPPING_SETTINGS['sender']['private']['middlename']) ? MRKV_SHIPPING_SETTINGS['sender']['private']['middlename'] : $sender_middlename;

						$sender_phone = (isset(MRKV_SHIPPING_SETTINGS['sender']['company']['phone']) && MRKV_SHIPPING_SETTINGS['sender']['company']['phone']) ? MRKV_SHIPPING_SETTINGS['sender']['company']['phone'] : $sender_phone;

						if(!$sender_phone)
						{
							$sender_phone = (isset(MRKV_SHIPPING_SETTINGS['sender']['private']['phone']) && MRKV_SHIPPING_SETTINGS['sender']['private']['phone']) ? MRKV_SHIPPING_SETTINGS['sender']['private']['phone'] : $sender_phone;
						}
					}

					$senders_type_list = array(
						'INDIVIDUAL' => __('An individual', 'mrkv-ua-shipping')
					);

					$description = __('The choice affects the fields that need to be filled in in the setup', 'mrkv-ua-shipping');

					echo $mrkv_global_option_generator->get_select_simple(__('The sender represents', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[sender][type]', $senders_type_list, $data_sender_type, MRKV_OPTION_OBJECT_NAME . '_sender_type' , __('Choose a type', 'mrkv-ua-shipping'), $description);
				?>
			</div>
			<div class="col-mrkv-5">
				<?php 
					$data = isset(MRKV_SHIPPING_SETTINGS['sender']['warehouse']['name']) ? MRKV_SHIPPING_SETTINGS['sender']['warehouse']['name'] : '';
					$description = __('The index consists of five numbers', 'mrkv-ua-shipping');
					echo $mrkv_global_option_generator->get_input_text(__('Postal code', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[sender][warehouse][name]', $data, MRKV_OPTION_OBJECT_NAME. '_sender_warehouse_name' , '', __('Enter the postal index...', 'mrkv-ua-shipping'), $description);
					$data = isset(MRKV_SHIPPING_SETTINGS['sender']['warehouse']['id']) ? MRKV_SHIPPING_SETTINGS['sender']['warehouse']['id'] : '';
					echo $mrkv_global_option_generator->get_input_hidden(MRKV_OPTION_OBJECT_NAME . '[sender][warehouse][id]', $data, MRKV_OPTION_OBJECT_NAME . '_sender_warehouse_id');
				?>
			</div>
		</div>
		<div class="admin_ua_ship_morkva_settings_line__inner active" id="<?php echo MRKV_OPTION_OBJECT_NAME; ?>_sender_type_list">
			<div data-sender="DEFAULT" class="<?php echo MRKV_OPTION_OBJECT_NAME; ?>_sender_type_block <?php if(!$data_sender_type){ echo 'active'; } ?>">
				<div class="admin_ua_ship_morkva_settings_row">
					<div class="col-mrkv-5">
						<p style="display: flex; align-items: center; gap: 7px;">
							<svg width="30px" height="30px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<g id="SVGRepo_bgCarrier" stroke-width="0"/>
							<g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"/>
							<g id="SVGRepo_iconCarrier"> <path d="M12 4.5L7 9.5M12 4.5L17 9.5M12 4.5L12 11M12 14.5C12 16.1667 13 19.5 17 19.5" stroke="#ed6230" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> </g>
							</svg>
							<?php echo __('Select Sender represents to continue filling in the data', 'mrkv-ua-shipping'); ?>
						</p>
					</div>
				</div>
			</div>
			<div data-sender="INDIVIDUAL" class="<?php echo MRKV_OPTION_OBJECT_NAME; ?>_sender_type_block <?php if($data_sender_type == 'INDIVIDUAL'){ echo 'active'; } ?>">
				<div class="admin_ua_ship_morkva_settings_row">
					<div class="col-mrkv-5">
						<?php 
							$data = $sender_lastname;
							echo $mrkv_global_option_generator->get_input_text(__('Lastname', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[sender][individual][lastname]', $data, MRKV_OPTION_OBJECT_NAME. '_sender_individual_lastname' , '', __('Enter the lastname...', 'mrkv-ua-shipping'), '');
						?>
						<p></p>
					</div>
					<div class="col-mrkv-5">
						<?php 
							$data = $sender_firstname;
							echo $mrkv_global_option_generator->get_input_text(__('Name', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[sender][individual][name]', $data, MRKV_OPTION_OBJECT_NAME. '_sender_individual_name' , '', __('Enter the name...', 'mrkv-ua-shipping'), '');
						?>
						<p></p>
					</div>
				</div>
				<div class="admin_ua_ship_morkva_settings_row">
					<div class="col-mrkv-5">
						<?php 
							$data = $sender_middlename;
							echo $mrkv_global_option_generator->get_input_text(__('Middlename', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[sender][individual][middlename]', $data, MRKV_OPTION_OBJECT_NAME. '_sender_individual_middlename' , '', __('Enter the middlename...', 'mrkv-ua-shipping'), '');
						?>
						<p></p>
					</div>
					<div class="col-mrkv-5">
						<?php 
							$data = $sender_phone;
							$description = __('Hint: the main format is 0987654321 (without +38)', 'mrkv-ua-shipping');
							echo $mrkv_global_option_generator->get_input_text(__('Phone', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[sender][individual][phone]', $data, MRKV_OPTION_OBJECT_NAME. '_sender_individual_phone' , '', __('Enter the phone...', 'mrkv-ua-shipping'), $description);
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<h3><img src="<?php echo MRKV_UA_SHIPPING_ASSETS_URL . '/images/global/wallet-icon.svg'; ?>" alt="Shipment" title="Shipment"><?php echo __('Payer', 'mrkv-ua-shipping'); ?></h3>
	<p><?php echo __('Check the default shipping payer for the shipment', 'mrkv-ua-shipping'); ?></p>
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
	<h3><img src="<?php echo MRKV_UA_SHIPPING_ASSETS_URL . '/images/global/tuning-icon.svg'; ?>" alt="Shipment" title="Shipment"><?php echo __('Shipment', 'mrkv-ua-shipping'); ?></h3>
	<p><?php echo __('Fill in the default shipping data for the shipment', 'mrkv-ua-shipping'); ?></p>
	<hr class="mrkv-ua-ship__hr">
	<div class="admin_ua_ship_morkva_settings_row">
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line">
				<label><?php echo __('Type of shipment (Default value)', 'mrkv-ua-shipping'); ?></label>
				<div class="admin_ua_ship_morkva_settings_row">
					<?php
						$data = isset(MRKV_SHIPPING_SETTINGS['shipment']['type']) ? MRKV_SHIPPING_SETTINGS['shipment']['type'] : '';
						echo $mrkv_global_option_generator->get_input_radio(__('STANDARD', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[shipment][type]', 'STANDARD', $data, MRKV_OPTION_OBJECT_NAME . '_shipment_type_standart', 'STANDARD');
						echo $mrkv_global_option_generator->get_input_radio(__('EXPRESS', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[shipment][type]', 'EXPRESS', $data, MRKV_OPTION_OBJECT_NAME . '_shipment_type_express', 'STANDARD');
					?>
				</div>
			</div>
		</div>
		<div class="col-mrkv-5">
		</div>
	</div>
	<div class="admin_ua_ship_morkva_settings_row">
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line">
				<h4><?php echo __('Weight of the shipment, g', 'mrkv-ua-shipping'); ?></h4>
				<?php 
					$data = isset(MRKV_SHIPPING_SETTINGS['shipment']['weight']) ? MRKV_SHIPPING_SETTINGS['shipment']['weight'] : '';
					echo $mrkv_global_option_generator->get_input_number('', MRKV_OPTION_OBJECT_NAME . '[shipment][weight]', $data, MRKV_OPTION_OBJECT_NAME. '_shipment_weight' , '', '', '');
					echo '<p class="mrkv-ua-ship-description">' . __('The value entered here will be used to calculate the cost of all shipments. The weight must be specified in grams.', 'mrkv-ua-shipping') . '</p>';
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
							echo $mrkv_global_option_generator->get_input_number('', MRKV_OPTION_OBJECT_NAME . '[shipment][length]', $data, MRKV_OPTION_OBJECT_NAME. '_shipment_length' , '', '', '', '', '0.01', '120');
						?>
					</div>
					<span>
						<svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M8 18C5.17157 18 3.75736 18 2.87868 17.1213C2 16.2426 2 14.8284 2 12C2 9.17157 2 7.75736 2.87868 6.87868C3.75736 6 5.17157 6 8 6C10.8284 6 12.2426 6 13.1213 6.87868C14 7.75736 14 9.17157 14 12" stroke="#ed6230" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M10 12C10 14.8284 10 16.2426 10.8787 17.1213C11.7574 18 13.1716 18 16 18C18.8284 18 20.2426 18 21.1213 17.1213C21.4211 16.8215 21.6186 16.4594 21.7487 16M22 12C22 9.17157 22 7.75736 21.1213 6.87868C20.2426 6 18.8284 6 16 6" stroke="#ed6230" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
					</span>
					<div class="adm_morkva_row_size__col">
						<span><?php echo __('Width', 'mrkv-ua-shipping'); ?></span>
						<?php 
							$data = isset(MRKV_SHIPPING_SETTINGS['shipment']['width']) ? MRKV_SHIPPING_SETTINGS['shipment']['width'] : '';
							echo $mrkv_global_option_generator->get_input_number('', MRKV_OPTION_OBJECT_NAME . '[shipment][width]', $data, MRKV_OPTION_OBJECT_NAME. '_shipment_width' , '', '', '', '', '0.01', '70');
						?>
					</div>
					<span>
						<svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M8 18C5.17157 18 3.75736 18 2.87868 17.1213C2 16.2426 2 14.8284 2 12C2 9.17157 2 7.75736 2.87868 6.87868C3.75736 6 5.17157 6 8 6C10.8284 6 12.2426 6 13.1213 6.87868C14 7.75736 14 9.17157 14 12" stroke="#ed6230" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M10 12C10 14.8284 10 16.2426 10.8787 17.1213C11.7574 18 13.1716 18 16 18C18.8284 18 20.2426 18 21.1213 17.1213C21.4211 16.8215 21.6186 16.4594 21.7487 16M22 12C22 9.17157 22 7.75736 21.1213 6.87868C20.2426 6 18.8284 6 16 6" stroke="#ed6230" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
					</span>
					<div class="adm_morkva_row_size__col">
						<span><?php echo __('Height', 'mrkv-ua-shipping'); ?></span>
						<?php 
							$data = isset(MRKV_SHIPPING_SETTINGS['shipment']['height']) ? MRKV_SHIPPING_SETTINGS['shipment']['height'] : '';
							echo $mrkv_global_option_generator->get_input_number('', MRKV_OPTION_OBJECT_NAME . '[shipment][height]', $data, MRKV_OPTION_OBJECT_NAME. '_shipment_height' , '', '', '', '', '0.01', '70');
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="admin_ua_ship_morkva_settings_line">
		<h4><?php echo __('Sticker format', 'mrkv-ua-shipping'); ?></h4>
		<div class="admin_ua_ship_morkva_settings_row">
			<?php
				$data = isset(MRKV_SHIPPING_SETTINGS['shipment']['sticker']) ? MRKV_SHIPPING_SETTINGS['shipment']['sticker'] : '';
				echo $mrkv_global_option_generator->get_input_radio(__('100*100 mm', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[shipment][sticker]', '100*100', $data, MRKV_OPTION_OBJECT_NAME . '_shipment_sticker_hxh', '100*100');
				echo $mrkv_global_option_generator->get_input_radio(__('100*100 mm for printing on A4 size', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[shipment][sticker]', '100*100A4', $data, MRKV_OPTION_OBJECT_NAME . '_shipment_sticker_standart', '');
				echo $mrkv_global_option_generator->get_input_radio(__('100*100 mm for printing on A5 size', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[shipment][sticker]', '100*100A5', $data, MRKV_OPTION_OBJECT_NAME . '_shipment_sticker_standart', '');
			?>
		</div>
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
			$description = '';

			echo $mrkv_global_option_generator->get_textarea(__('Description of the shipment', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[shipment][description]', $data, MRKV_OPTION_OBJECT_NAME. '_shipment_description' , '', __('For example, products for children...', 'mrkv-ua-shipping'), $description);
		?>
		<?php echo '<p class="mrkv-ua-ship-description">' . __('Values of shortcodes that are added to the end of the text field when the buttons are pressed', 'mrkv-ua-shipping') . '</p>'; ?>
		<div class="admin_ua_ship_morkva_settings_row admin_ua_ship_morkva_settings_row_btns">
			<h4><?php echo __('Insert the shortcode:', 'mrkv-ua-shipping'); ?></h4>
			<div data-added="[order_id]" class="button button-primary adm-textarea-btn"><?php echo __('Order number', 'mrkv-ua-shipping'); ?></div>
			<div data-added="[product_list]" class="button button-primary adm-textarea-btn"><?php echo __('List of products', 'mrkv-ua-shipping'); ?></div>
			<div data-added="[product_list_qa]" class="button button-primary adm-textarea-btn"><?php echo __('List of goods (with articles and quantity)', 'mrkv-ua-shipping'); ?></div>
			<div data-added="[product_list_a]" class="button button-primary adm-textarea-btn"><?php echo __('List of articles with quantity', 'mrkv-ua-shipping'); ?></div>
			<div data-added="[quantity]" class="button button-primary adm-textarea-btn"><?php echo __('Quantity of goods', 'mrkv-ua-shipping'); ?></div>
			<div data-added="[quantity_p]" class="button button-primary adm-textarea-btn"><?php echo __('Number of positions', 'mrkv-ua-shipping'); ?></div>
			<div data-added="[cost]" class="button button-primary adm-textarea-btn"><?php echo __('Cost', 'mrkv-ua-shipping'); ?></div>
		</div>
	</div>
</section>
<section id="international_settings" class="mrkv_up_ship_shipping_tab_block">
	<h2><img src="<?php echo MRKV_UA_SHIPPING_ASSETS_URL . '/images/global/map-icon.svg'; ?>" alt="International shipping settings" title="International shipping settings"><?php echo __('International shipping settings', 'mrkv-ua-shipping'); ?></h2>
	<p><?php echo __('Customize international shipping to your needs', 'mrkv-ua-shipping'); ?></p>
	<hr class="mrkv-ua-ship__hr">
	<h3><img src="<?php echo MRKV_UA_SHIPPING_ASSETS_URL . '/images/global/user-icon.svg'; ?>" alt="Sender settings" title="Sender settings"><?php echo __('Sender settings', 'mrkv-ua-shipping'); ?></h3>
	<div class="admin_ua_ship_morkva_settings_row">
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
				<?php 
					$data = '';
					$description = '<span class="mrkv-ua-ship-only-pro">' . __('Only in the Pro version', 'mrkv-ua-shipping') . '</span>' . __('The field must be filled in in Latin', 'mrkv-ua-shipping');
					echo $mrkv_global_option_generator->get_input_text(__('Name', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[international][name]', $data, MRKV_OPTION_OBJECT_NAME. '_international_name' , '', __('Enter the name...', 'mrkv-ua-shipping'), $description, 'readonly');
				?>
			</div>
		</div>
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
				<?php 
					$data = '';
					$description = '<span class="mrkv-ua-ship-only-pro">' . __('Only in the Pro version', 'mrkv-ua-shipping') . '</span>' . __('The field must be filled in in Latin', 'mrkv-ua-shipping');
					echo $mrkv_global_option_generator->get_input_text(__('Lastname', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[international][lastname]', $data, MRKV_OPTION_OBJECT_NAME. '_international_lastname' , '', __('Enter the lastname...', 'mrkv-ua-shipping'), $description, 'readonly');
				?>
			</div>
		</div>
	</div>
	<div class="admin_ua_ship_morkva_settings_row">
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
				<?php 
					$data = '';
					$description = '<span class="mrkv-ua-ship-only-pro">' . __('Only in the Pro version', 'mrkv-ua-shipping') . '</span>' . __('The field must be filled in in Latin', 'mrkv-ua-shipping');
					echo $mrkv_global_option_generator->get_input_text(__('City', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[international][city]', $data, MRKV_OPTION_OBJECT_NAME. '_international_city' , '', __('Enter the city...', 'mrkv-ua-shipping'), $description, 'readonly');
				?>
			</div>
		</div>
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
				<?php 
					$data = '';
					$description = '<span class="mrkv-ua-ship-only-pro">' . __('Only in the Pro version', 'mrkv-ua-shipping') . '</span>' . __('The field must be filled in in Latin', 'mrkv-ua-shipping');
					echo $mrkv_global_option_generator->get_input_text(__('Street', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[international][street]', $data, MRKV_OPTION_OBJECT_NAME. '_international_street' , '', __('Enter the street...', 'mrkv-ua-shipping'), $description, 'readonly');
				?>
			</div>
		</div>
	</div>
	<div class="admin_ua_ship_morkva_settings_row">
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
				<?php 
					$data = '';
					$description = '<span class="mrkv-ua-ship-only-pro">' . __('Only in the Pro version', 'mrkv-ua-shipping') . '</span>' . __('The field must be filled in in Latin', 'mrkv-ua-shipping');
					echo $mrkv_global_option_generator->get_input_text(__('House number', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[international][house]', $data, MRKV_OPTION_OBJECT_NAME. '_international_house' , '', __('Enter the house number...', 'mrkv-ua-shipping'), $description, 'readonly');
				?>
			</div>
		</div>
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
				<?php 
					$data = '';
					$description = '<span class="mrkv-ua-ship-only-pro">' . __('Only in the Pro version', 'mrkv-ua-shipping') . '</span>';
					echo $mrkv_global_option_generator->get_input_text(__('Index', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[international][index]', $data, MRKV_OPTION_OBJECT_NAME. '_international_index' , '', __('Enter the index...', 'mrkv-ua-shipping'), $description, 'readonly');
				?>
			</div>
		</div>
	</div>
	<div class="admin_ua_ship_morkva_settings_row">
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
				<?php 
					$data = '';
					$description = '<span class="mrkv-ua-ship-only-pro">' . __('Only in the Pro version', 'mrkv-ua-shipping') . '</span>';
					echo $mrkv_global_option_generator->get_input_text(__('Phone', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[international][phone]', $data, MRKV_OPTION_OBJECT_NAME. '_international_phone' , '', __('Enter the phone...', 'mrkv-ua-shipping'), $description, 'readonly');
				?>
			</div>
		</div>
		<div class="col-mrkv-5">
		</div>
	</div>
	<h3><img src="<?php echo MRKV_UA_SHIPPING_ASSETS_URL . '/images/global/wallet-icon.svg'; ?>" alt="Shipment" title="Shipment"><?php echo __('Payer', 'mrkv-ua-shipping'); ?></h3>
	<p><?php echo __('Check the default shipping payer for the shipment', 'mrkv-ua-shipping'); ?></p>
	<hr class="mrkv-ua-ship__hr">
	<div class="admin_ua_ship_morkva_settings_row">
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
				<h4><?php echo __('Payer of delivery', 'mrkv-ua-shipping'); ?></h4>
				<p class="mrkv-ua-ship-only-pro"><?php echo __('Only in the Pro version', 'mrkv-ua-shipping'); ?></p>
				<div class="admin_ua_ship_morkva_settings_row">
					<?php
						$data = '';
						echo $mrkv_global_option_generator->get_input_radio(__('Recipient', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[international][payer][delivery]', 'Recipient', $data, MRKV_OPTION_OBJECT_NAME . '_international_payer_delivery_recipient', 'Recipient', 'disabled');
						echo $mrkv_global_option_generator->get_input_radio(__('Sender', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[international][payer][delivery]', 'Sender', $data, MRKV_OPTION_OBJECT_NAME . '_international_payer_delivery_sender', 'Recipient', 'disabled');
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
						echo $mrkv_global_option_generator->get_input_radio(__('Recipient', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[international][payer][cash]', 'Recipient', $data, MRKV_OPTION_OBJECT_NAME . '_international_payer_cash_recipient', 'Recipient', 'disabled');
						echo $mrkv_global_option_generator->get_input_radio(__('Sender', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[international][payer][cash]', 'Sender', $data, MRKV_OPTION_OBJECT_NAME . '_international_payer_cash_sender', 'Recipient', 'disabled');
					?>
				</div>
			</div>
		</div>
	</div>
	<h3><img src="<?php echo MRKV_UA_SHIPPING_ASSETS_URL . '/images/global/tuning-icon.svg'; ?>" alt="Shipment" title="Shipment"><?php echo __('Shipment', 'mrkv-ua-shipping'); ?></h3>
	<p><?php echo __('Fill in the default shipping data for the international invoice', 'mrkv-ua-shipping'); ?></p>
	<hr class="mrkv-ua-ship__hr">
	<div class="admin_ua_ship_morkva_settings_row">
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
				<?php 
					$data = '';
					$senders_type_list = array(
					);

					$description = '<span class="mrkv-ua-ship-only-pro">' . __('Only in the Pro version', 'mrkv-ua-shipping') . '</span>';

					echo $mrkv_global_option_generator->get_select_simple(__('Type of international shipment', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[international][type]', $senders_type_list, $data, MRKV_OPTION_OBJECT_NAME . '_international_type' , __('Choose a type', 'mrkv-ua-shipping'), $description, '', 'disabled');
				?>
			</div>
		</div>
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
				<?php 
					$data = '';
					$senders_type_list = array(
					);

					$description = '<span class="mrkv-ua-ship-only-pro">' . __('Only in the Pro version', 'mrkv-ua-shipping') . '</span>';

					echo $mrkv_global_option_generator->get_select_simple(__('Category of shipment', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[international][category]', $senders_type_list, $data, MRKV_OPTION_OBJECT_NAME . '_international_category' , __('Choose a category', 'mrkv-ua-shipping'), $description, '', 'disabled');
				?>
			</div>
		</div>
	</div>
	<div class="admin_ua_ship_morkva_settings_row">
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
				<h4><?php echo __('Weight of the shipment, g', 'mrkv-ua-shipping'); ?></h4>
				<p class="mrkv-ua-ship-only-pro"><?php echo __('Only in the Pro version', 'mrkv-ua-shipping'); ?></p>
				<?php 
					$data = '';
					echo $mrkv_global_option_generator->get_input_number('', MRKV_OPTION_OBJECT_NAME . '[international][weight]', $data, MRKV_OPTION_OBJECT_NAME. '_international_weight' , '', '', '', 'readonly');
					echo '<p class="mrkv-ua-ship-description">' . __('The value entered here will be used to calculate the cost of all shipments. The weight must be specified in grams.', 'mrkv-ua-shipping') . '</p>';
				?>
			</div>
		</div>
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
				<h4><?php echo __('Length of shipment, cm', 'mrkv-ua-shipping'); ?></h4>
				<p class="mrkv-ua-ship-only-pro"><?php echo __('Only in the Pro version', 'mrkv-ua-shipping'); ?></p>
				<?php 
					$data = '';
					echo $mrkv_global_option_generator->get_input_number('', MRKV_OPTION_OBJECT_NAME . '[international][length]', $data, MRKV_OPTION_OBJECT_NAME. '_international_length' , '', '', '', 'readonly');
					echo '<p class="mrkv-ua-ship-description">' . __('The value entered here will be used to calculate the cost of all shipments. The length must be specified in centimeters.', 'mrkv-ua-shipping') . '</p>';
				?>
			</div>
		</div>
	</div>
	<div class="admin_ua_ship_morkva_settings_row">
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
				<?php 
					$data = '';
					$description = '<span class="mrkv-ua-ship-only-pro">' . __('Only in the Pro version', 'mrkv-ua-shipping') . '</span>';
					echo $mrkv_global_option_generator->get_input_text(__('Global HS code', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[international][global_hscode]', $data, MRKV_OPTION_OBJECT_NAME. '_international_global_hscode' , '', __('Enter the HS code for all products...', 'mrkv-ua-shipping'), $description, 'readonly');
				?>
				<p><?php echo __('The code of the commodity invoice of foreign economic activity (CN VED) must contain only numbers (from 6 to 10 digits).', 'mrkv-ua-shipping'); ?></p>
			</div>
		</div>
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
				<?php 
					$data = '';

					$attributes = array();

					$description = '<span class="mrkv-ua-ship-only-pro">' . __('Only in the Pro version', 'mrkv-ua-shipping') . '</span>';

					echo $mrkv_global_option_generator->get_select_simple(__('HS codes in attributes', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[international][hscode_attr]', $attributes, $data, MRKV_OPTION_OBJECT_NAME . '_international_hscode_attr' , __('Choose an attribute', 'mrkv-ua-shipping'), $description, 'disabled');
				?>
			</div>
		</div>
	</div>
	<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
		<?php
			$data = '';
			echo $mrkv_global_option_generator->get_input_checkbox(__('Track a Banderole?', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[international][track]', $data, MRKV_OPTION_OBJECT_NAME . '_international_track', '', 'disabled');
		?>
		<?php echo '<span class="mrkv-ua-ship-only-pro">' . __('Only in the Pro version', 'mrkv-ua-shipping') . '</span>'; ?>
		<?php echo '<p class="mrkv-ua-ship-description">' . __('Turn on if you need to track a Banderole in international delivery', 'mrkv-ua-shipping') . '</p>'; ?>
	</div>
	<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
		<h4><?php echo __('Parameters of the shipment', 'mrkv-ua-shipping'); ?></h4>
		<p class="mrkv-ua-ship-only-pro"><?php echo __('Only in the Pro version', 'mrkv-ua-shipping'); ?></p>
		<div class="admin_ua_ship_morkva_settings_row">
			<div class="col-mrkv-5">
				<?php
					$data = '';
					echo $mrkv_global_option_generator->get_input_checkbox(__('Bulky parcel', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[international][bulky]', $data, MRKV_OPTION_OBJECT_NAME . '_international_bulky', '', 'disabled' );
				?>
			</div>
			<div class="col-mrkv-5">
				<?php
					$data = '';
					echo $mrkv_global_option_generator->get_input_checkbox(__('Air delivery', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[international][air]', $data, MRKV_OPTION_OBJECT_NAME . '_international_air', '', 'disabled' );
				?>
			</div>
		</div>
		<div class="admin_ua_ship_morkva_settings_row">
			<div class="col-mrkv-5">
				<?php
					$data = '';
					echo $mrkv_global_option_generator->get_input_checkbox(__('Courier delivery', 'mrkv-ua-shipping') . ' <span class="mrkv-checkbox-cancel">' . __('(temporarily unavailable)', 'mrkv-ua-shipping') .'</span>', MRKV_OPTION_OBJECT_NAME . '[international][courier]', $data, MRKV_OPTION_OBJECT_NAME . '_international_courier', '', 'disabled' );
				?>
			</div>
			<div class="col-mrkv-5">
				<?php
					$data = '';
					echo $mrkv_global_option_generator->get_input_checkbox(__('SMS notification upon arrival', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[international][sms]', $data, MRKV_OPTION_OBJECT_NAME . '_international_sms', '', 'disabled' );
				?>
			</div>
		</div>
		<?php echo '<p class="mrkv-ua-ship-description">' . __('Options affect the shipping cost. Delivery is calculated in the currency specified ', 'mrkv-ua-shipping') . '<a href="admin.php?page=wc-settings&tab=general" target="blanc">' . __('in the Woocommerce settings', 'mrkv-ua-shipping') . '</a>' . '</p>'; ?>
	</div>
	<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
		<h4><?php echo __('Sticker format', 'mrkv-ua-shipping'); ?></h4>
		<p class="mrkv-ua-ship-only-pro"><?php echo __('Only in the Pro version', 'mrkv-ua-shipping'); ?></p>
		<div class="admin_ua_ship_morkva_settings_row admin_ua_ship_morkva_settings_row_wrap">
			<?php
				$data = '';
				echo $mrkv_global_option_generator->get_input_radio(__('Covering address form (cp71)', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[international][sticker]', 'cp71', $data, MRKV_OPTION_OBJECT_NAME . '_international_sticker_cp', 'cp71', 'disabled');
				echo $mrkv_global_option_generator->get_input_radio(__('Customs declaration form (cn22)', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[international][sticker]', 'cn22', $data, MRKV_OPTION_OBJECT_NAME . '_international_sticker_cn', '', 'disabled');
				echo $mrkv_global_option_generator->get_input_radio(__('Form for an envelope in the format (c6)', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[international][sticker]', 'c6', $data, MRKV_OPTION_OBJECT_NAME . '_international_sticker_c', '', 'disabled');
				echo $mrkv_global_option_generator->get_input_radio(__('Form for an envelope in (dl) format', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[international][sticker]', 'dl', $data, MRKV_OPTION_OBJECT_NAME . '_international_sticker_dl', '', 'disabled');
				echo $mrkv_global_option_generator->get_input_radio(__('Post-it note (100mm x 100mm)', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[international][sticker]', 'forms', $data, MRKV_OPTION_OBJECT_NAME . '_international_sticker_forms', '', 'disabled');
				echo $mrkv_global_option_generator->get_input_radio(__('Form for sending cash on delivery (tfp3)', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[international][sticker]', 'tfp3', $data, MRKV_OPTION_OBJECT_NAME . '_international_sticker_tfp3', '', 'disabled');
			?>
		</div>
	</div>
	<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
		<?php
			$data = '';
			$description = '<span class="mrkv-ua-ship-only-pro">' . __('Only in the Pro version', 'mrkv-ua-shipping') . '</span>';

			echo $mrkv_global_option_generator->get_textarea(__('Description of an international shipment (Latin)', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[international][description]', $data, MRKV_OPTION_OBJECT_NAME. '_international_description' , '', __('For example, products for children...', 'mrkv-ua-shipping'), $description, 'readonly');
		?>
	</div>
	<div class="admin_ua_ship_morkva_settings_line admin_ua_ship_morkva_settings_line_p_info">
		<?php echo '<p><img src="' . MRKV_UA_SHIPPING_ASSETS_URL . '/images/global/info-icon.svg' .'" >' . __('The following words are not allowed in the name of the shipment in English:
                BRYUKI, ACCESSEROISE, ACCESSORIES, GIFT, GIFT BOX, GIFTS, HANDMADE GIFT, KOSTYUM, KURTKA,
                ODEZHDA, PODAROK, PRESENT, PREZENT, SHAPKA, SOLDATYKY, SOUVENIR, SOUVENIR SET, SOUVENIRS,
                SUVENIER, SUVENIR, TAINA, XYDI, Other, item, cadeau, or only numbers (for example, 963258).
                The length should not exceed 32 characters.', 'mrkv-ua-shipping') . '</p>'; ?>
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
	<div class="admin_ua_ship_morkva_settings_row">
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
				<?php
					$data = '';
					echo $mrkv_global_option_generator->get_input_checkbox(__('Auto-creation of a shipment', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[automation][autocreate][enabled]', $data, MRKV_OPTION_OBJECT_NAME . '_automation_autocreate_enabled', '', 'disabled');
				?>
				<?php echo '<span class="mrkv-ua-ship-only-pro">' . __('Only in the Pro version', 'mrkv-ua-shipping') . '</span>'; ?>
				<?php echo '<p class="mrkv-ua-ship-description">' . __('A shipment will be created after the order is placed', 'mrkv-ua-shipping') . '</p>'; ?>
			</div>
		</div>
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
					echo $mrkv_global_option_generator->get_input_checkbox(__('Enable patronymic (Warehouse)', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[checkout][middlename][enabled]', $data, MRKV_OPTION_OBJECT_NAME . '_checkout_middlename_enabled', );
				?>
				<?php echo '<p class="mrkv-ua-ship-description">' . __('Remove the patronymic field from the checkout page', 'mrkv-ua-shipping') . '</p>'; ?>
			</div>
		</div>
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line">
				<?php
					$data = isset(MRKV_SHIPPING_SETTINGS['checkout']['middlename']['required']) ? MRKV_SHIPPING_SETTINGS['checkout']['middlename']['required'] : '';
					echo $mrkv_global_option_generator->get_input_checkbox(__('Patronymic is required (Warehouse)', 'mrkv-ua-shipping'), MRKV_OPTION_OBJECT_NAME . '[checkout][middlename][required]', $data, MRKV_OPTION_OBJECT_NAME . '_checkout_middlename_required', );
				?>
				<?php echo '<p class="mrkv-ua-ship-description">' . __('The patronymic is required if the sender is an individual. For sole proprietors and LLCs, this field is not required when creating a waybill.', 'mrkv-ua-shipping') . '</p>'; ?>
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