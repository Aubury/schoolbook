<?php 
	$shipping_slug = 'ukr-poshta';
	$shipping_slug_option = $shipping_slug . '_m_ua_settings';
	$mrk_ua_ship_ukr_settings = get_option($shipping_slug_option);
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
						$has_empty = false;	
						$sender_type = isset($mrk_ua_ship_ukr_settings['sender']['type']) ? $mrk_ua_ship_ukr_settings['sender']['type'] : '';
						$fullname = '';

						if($sender_type == 'INDIVIDUAL')
						{
							$lastname = isset($mrk_ua_ship_ukr_settings['sender']['individual']['lastname']) ? $mrk_ua_ship_ukr_settings['sender']['individual']['lastname'] . ' ' : '';
							$name = isset($mrk_ua_ship_ukr_settings['sender']['individual']['name']) ? $mrk_ua_ship_ukr_settings['sender']['individual']['name'] . ' ' : '';
							$middlename = isset($mrk_ua_ship_ukr_settings['sender']['individual']['middlename']) ? $mrk_ua_ship_ukr_settings['sender']['individual']['middlename'] . ' ' : '';
							$phone = isset($mrk_ua_ship_ukr_settings['sender']['individual']['phone']) ? $mrk_ua_ship_ukr_settings['sender']['individual']['phone'] . ' ' : '';
						}
						elseif($sender_type && $sender_type != 'INDIVIDUAL')
						{
							$lastname = (isset($mrk_ua_ship_ukr_settings['sender']['company']['lastname']) && $mrk_ua_ship_ukr_settings['sender']['company']['lastname']) ? $mrk_ua_ship_ukr_settings['sender']['company']['lastname'] : $lastname;

							if(!$lastname){
								$lastname = (isset($mrk_ua_ship_ukr_settings['sender']['private']['lastname']) && $mrk_ua_ship_ukr_settings['sender']['private']['lastname']) ? $mrk_ua_ship_ukr_settings['sender']['private']['lastname'] : $lastname;
							}

							$name = (isset($mrk_ua_ship_ukr_settings['sender']['company']['name']) && $mrk_ua_ship_ukr_settings['sender']['company']['name']) ? $mrk_ua_ship_ukr_settings['sender']['company']['name'] : $name;

							if(!$name)
							{
								$name = (isset($mrk_ua_ship_ukr_settings['sender']['private']['name']) && $mrk_ua_ship_ukr_settings['sender']['private']['name']) ? $mrk_ua_ship_ukr_settings['sender']['private']['name'] : $name;
							}

							$middlename = (isset($mrk_ua_ship_ukr_settings['sender']['private']['middlename']) && $mrk_ua_ship_ukr_settings['sender']['private']['middlename']) ? $mrk_ua_ship_ukr_settings['sender']['private']['middlename'] : $middlename;

							$phone = (isset($mrk_ua_ship_ukr_settings['sender']['company']['phone']) && $mrk_ua_ship_ukr_settings['sender']['company']['phone']) ? $mrk_ua_ship_ukr_settings['sender']['company']['phone'] : $phone;

							if(!$phone)
							{
								$phone = (isset($mrk_ua_ship_ukr_settings['sender']['private']['phone']) && $mrk_ua_ship_ukr_settings['sender']['private']['phone']) ? $mrk_ua_ship_ukr_settings['sender']['private']['phone'] : $phone;
							}
						}
						else
						{
							$has_empty = true;	
						}

						$fullname = $lastname . $name . $middlename;

						if(!$lastname && !$name)
						{
							$has_empty = true;
						}

						if($has_empty)
						{
							?>
								<div class="admin_ua_ship_morkva__notification mrkv-notification-red"><?php echo __('Sender Data Incorrect', 'mrkv-ua-shipping'); ?></div>
							<?php
						}
					?>
				</label>
				<p><?php echo $fullname; ?></p>
			</div>
			<div class="admin_ua_ship_morkva_settings_line">
				<label>
					<?php 
						echo __('Phone', 'mrkv-ua-shipping');

						if(!$phone)
						{
							?>
								<div class="admin_ua_ship_morkva__notification mrkv-notification-red"><?php echo __('Sender Phone Incorrect', 'mrkv-ua-shipping'); ?></div>
							<?php
						}
					?>
				</label>
				<p><?php echo $phone; ?></p>
			</div>
		</div>
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line">
				<label>
					<?php echo __('Address from', 'mrkv-ua-shipping'); ?>
					<?php
					$full_address = isset($mrk_ua_ship_ukr_settings['sender']['warehouse']['name']) ? $mrk_ua_ship_ukr_settings['sender']['warehouse']['name'] : '';
					$full_address_id = isset($mrk_ua_ship_ukr_settings['sender']['warehouse']['id']) ? $mrk_ua_ship_ukr_settings['sender']['warehouse']['id'] : '';

					if(!$full_address || !$full_address_id)
					{
						?>
							<div class="admin_ua_ship_morkva__notification mrkv-notification-red"><?php echo __('Address Ref Incorrect', 'mrkv-ua-shipping'); ?></div>
						<?php
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
						$data = isset($mrk_ua_ship_ukr_settings['shipment']['type']) ? $mrk_ua_ship_ukr_settings['shipment']['type'] : '';
						echo $mrkv_global_option_generator->get_input_radio(__('STANDARD', 'mrkv-ua-shipping'), 'mrkv_ua_ship_invoice_shipment_type', 'STANDARD', $data, $shipping_slug_option . '_mrkv_ua_ship_invoice_shipment_type_standart', 'STANDARD');
						echo $mrkv_global_option_generator->get_input_radio(__('EXPRESS', 'mrkv-ua-shipping'), 'mrkv_ua_ship_invoice_shipment_type', 'EXPRESS', $data, $shipping_slug_option . '_mrkv_ua_ship_invoice_shipment_type_express', 'STANDARD');
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
						$data = isset($mrk_ua_ship_ukr_settings['payer']['delivery']) ? $mrk_ua_ship_ukr_settings['payer']['delivery'] : '';
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
					<?php echo __('Weight of the shipment, g', 'mrkv-ua-shipping'); ?>
				</label>
				<?php 
					$data = isset($mrk_ua_ship_ukr_settings['shipment']['weight']) ? $mrk_ua_ship_ukr_settings['shipment']['weight'] : '';

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
							$data = isset($mrk_ua_ship_ukr_settings['shipment']['length']) ? $mrk_ua_ship_ukr_settings['shipment']['length'] : '';
							echo $mrkv_global_option_generator->get_input_number('', 'mrkv_ua_ship_invoice_shipment_length', $data, $shipping_slug_option. '_mrkv_ua_ship_invoice_shipment_length' , '', '', '');
						?>
					</div>
					<span>
						<svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M8 18C5.17157 18 3.75736 18 2.87868 17.1213C2 16.2426 2 14.8284 2 12C2 9.17157 2 7.75736 2.87868 6.87868C3.75736 6 5.17157 6 8 6C10.8284 6 12.2426 6 13.1213 6.87868C14 7.75736 14 9.17157 14 12" stroke="#ed6230" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M10 12C10 14.8284 10 16.2426 10.8787 17.1213C11.7574 18 13.1716 18 16 18C18.8284 18 20.2426 18 21.1213 17.1213C21.4211 16.8215 21.6186 16.4594 21.7487 16M22 12C22 9.17157 22 7.75736 21.1213 6.87868C20.2426 6 18.8284 6 16 6" stroke="#ed6230" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
					</span>
					<div class="adm_morkva_row_size__col">
						<span><?php echo __('Width', 'mrkv-ua-shipping'); ?></span>
						<?php 
							$data = isset($mrk_ua_ship_ukr_settings['shipment']['width']) ? $mrk_ua_ship_ukr_settings['shipment']['width'] : '';
							echo $mrkv_global_option_generator->get_input_number('', 'mrkv_ua_ship_invoice_shipment_width', $data, $shipping_slug_option. '_mrkv_ua_ship_invoice_shipment_width' , '', '', '');
						?>
					</div>
					<span>
						<svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M8 18C5.17157 18 3.75736 18 2.87868 17.1213C2 16.2426 2 14.8284 2 12C2 9.17157 2 7.75736 2.87868 6.87868C3.75736 6 5.17157 6 8 6C10.8284 6 12.2426 6 13.1213 6.87868C14 7.75736 14 9.17157 14 12" stroke="#ed6230" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M10 12C10 14.8284 10 16.2426 10.8787 17.1213C11.7574 18 13.1716 18 16 18C18.8284 18 20.2426 18 21.1213 17.1213C21.4211 16.8215 21.6186 16.4594 21.7487 16M22 12C22 9.17157 22 7.75736 21.1213 6.87868C20.2426 6 18.8284 6 16 6" stroke="#ed6230" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
					</span>
					<div class="adm_morkva_row_size__col">
						<span><?php echo __('Height', 'mrkv-ua-shipping'); ?></span>
						<?php 
							$data = isset($mrk_ua_ship_ukr_settings['shipment']['height']) ? $mrk_ua_ship_ukr_settings['shipment']['height'] : '';
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
					<?php echo __('Declared cost', 'mrkv-ua-shipping'); ?>
				</label>
				<?php 
					echo $mrkv_global_option_generator->get_input_number('', 'mrkv_ua_ship_invoice_cost', 0, $shipping_slug_option . '_mrkv_ua_ship_invoice_cost' , '', '', '');
				?>
			</div>
		</div>
		<div class="col-mrkv-5">
			<div class="admin_ua_ship_morkva_settings_line">
				<label>
					<?php echo __('Cash on delivery, UAH', 'mrkv-ua-shipping'); ?>
				</label>
				<?php 
					echo $mrkv_global_option_generator->get_input_number('', 'mrkv_ua_ship_invoice_cost_back', 0, $shipping_slug_option . '_mrkv_ua_ship_invoice_cost_back' , '', '', '');
				?>
			</div>
		</div>
	</div>
	<div class="admin_ua_ship_morkva_settings_line">
		<?php
			$data = isset($mrk_ua_ship_ukr_settings['shipment']['description']) ? $mrk_ua_ship_ukr_settings['shipment']['description'] : '';
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
			<div class="admin_ua_ship_morkva_settings_line">
				<label>
					<?php echo __('In case of failure to deliver', 'mrkv-ua-shipping'); ?>
				</label>
				<div class="admin_ua_ship_morkva_settings_row admin_ua_ship_morkva_settings_row-col">
					<?php
						$data = 'RETURN_AFTER_7_DAYS';
						echo $mrkv_global_option_generator->get_input_radio(__('Return to the sender in 14 calendar days', 'mrkv-ua-shipping'), 'mrkv_ua_ship_invoice_return', 'RETURN', $data, $shipping_slug_option . '_mrkv_ua_ship_invoice_return_return', 'RETURN_AFTER_7_DAYS');
						echo $mrkv_global_option_generator->get_input_radio(__('Return the shipment after the expiration of the free storage period (5 working days)', 'mrkv-ua-shipping'), 'mrkv_ua_ship_invoice_return', 'RETURN_AFTER_7_DAYS', $data, $shipping_slug_option . '_mrkv_ua_ship_invoice_return_seven', 'RETURN_AFTER_7_DAYS');
						echo $mrkv_global_option_generator->get_input_radio(__('Destroy the shipment', 'mrkv-ua-shipping'), 'mrkv_ua_ship_invoice_return', 'PROCESS_AS_REFUSAL', $data, $shipping_slug_option . '_mrkv_ua_ship_invoice_return_process', 'RETURN_AFTER_7_DAYS');
					?>
				</div>
			</div>
		</div>
	</div>
</form>
<?php
	if (isset($_GET["post"]) || isset($_GET["id"])) 
    {
    	$order_id = '';
        if(isset($_GET["post"])){
            $order_id = $_GET["post"];    
        }
        else{
            $order_id = $_GET["id"];
        }

        $order = wc_get_order($order_id);

        $mrkv_ua_ship_invoice = $order->get_meta('mrkv_ua_ship_invoice_number');

        if($mrkv_ua_ship_invoice)
        {
        	$sticker_default = isset($mrk_ua_ship_ukr_settings['shipment']['sticker']) ? $mrk_ua_ship_ukr_settings['shipment']['sticker'] : '';
        	$production_bearer_ecom = isset($mrk_ua_ship_ukr_settings['production_bearer_ecom']) ? $mrk_ua_ship_ukr_settings['production_bearer_ecom'] : '';
        	$production_cp_token = isset($mrk_ua_ship_ukr_settings['production_cp_token']) ? $mrk_ua_ship_ukr_settings['production_cp_token'] : '';
        	$sticker_default_inter = isset($mrk_ua_ship_ukr_settings['international']['sticker']) ? $mrk_ua_ship_ukr_settings['international']['sticker'] : '';
        	?>
        		<form class="form-ukr-poshta-ttn" action="<?php echo MRKV_UA_SHIPPING_PLUGIN_DIR . 'templates/orders/mrkv-ua-ship-ukr-poshta-pdf.php'; ?>" method="post" target="_blank" style="display: none;">
					<input type="hidden" name="invoice_number" value="<?php echo $mrkv_ua_ship_invoice; ?>">
					<input type="hidden" name="type" value="<?php echo $sticker_default; ?>">
					<input type="hidden" name="bearer" value="<?php echo $production_bearer_ecom; ?>">
					<input type="hidden" name="cp_token" value="<?php echo $production_cp_token; ?>">
					<input type="submit">
				</form>
				<form class="form-ukr-poshta-ttn-international" action="<?php echo MRKV_UA_SHIPPING_PLUGIN_DIR . 'templates/orders/mrkv-ua-ship-ukr-poshta-pdf.php'; ?>" method="post" target="_blank" style="display: none;">
					<input type="hidden" name="invoice_number" value="<?php echo $mrkv_ua_ship_invoice; ?>">
					<input type="hidden" name="fs1" value="<?php echo $sticker_default_inter; ?>">
					<input type="hidden" name="bearer" value="<?php echo $production_bearer_ecom; ?>">
					<input type="hidden" name="cp_token" value="<?php echo $production_cp_token; ?>">
					<input type="hidden" name="type" value="<?php echo $sticker_default; ?>">
					<input type="submit">
				</form>
        	<?php
        }
    }
?>
