<?php
if ( ! defined( 'ABSPATH' ) ) exit;
use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;
use Automattic\WooCommerce\Utilities\OrderUtil;

# Check if class exist
if (!class_exists('MRKV_LIQPAY_ORDERS'))
{
	/**
	 * Class for setup woo settings
	 */
	class MRKV_LIQPAY_ORDERS
	{
		/**
		 * Constructor for woo settings
		 * */
		function __construct()
		{
			# Add metabox to order edit
			add_action('add_meta_boxes', array( $this, 'mrkv_liqpay_add_meta_boxes' ), 10, 2);

			add_action( 'wp_ajax_mrkv_liqpay_cancel_payment_hold', array( $this, 'mrkv_liqpay_cancel_payment_hold_func' ) );
			add_action( 'wp_ajax_nopriv_mrkv_liqpay_cancel_payment_hold', array( $this, 'mrkv_liqpay_cancel_payment_hold_func' ) );

			add_action( 'wp_ajax_mrkv_liqpay_final_payment_hold', array( $this, 'mrkv_liqpay_final_payment_hold_func' ) );
			add_action( 'wp_ajax_nopriv_mrkv_liqpay_final_payment_hold', array( $this, 'mrkv_liqpay_final_payment_hold_func' ) );

			add_action('mrkv_liqpay_settings_sidebar', [$this, 'mrkv_liqpay_settings_sidebar_func']);
			add_action('woocommerce_order_status_changed', [$this, 'mrkv_liqpay_finalize_hold'], 10, 4);
			add_action( 'init', [$this, 'mrkv_liqpay_schedule_log_cleanup'] );
			add_action( 'mrkv_liqpay_delete_old_logs_event', [$this, 'mrkv_liqpay_clean_logs'] );
		}

		public function mrkv_liqpay_schedule_log_cleanup() {
			if ( ! wp_next_scheduled( 'mrkv_liqpay_delete_old_logs_event' ) ) {
				wp_schedule_event( time(), 'twicedaily', 'mrkv_liqpay_delete_old_logs_event' );
			}
		}

		public function mrkv_liqpay_clean_logs() {
			$log_dir = defined( 'WC_LOG_DIR' ) ? WC_LOG_DIR : WP_CONTENT_DIR . '/uploads/wc-logs/';
			$source  = 'mrkv-liqpay-extended';
			$files = glob( $log_dir . $source . '*.log' );

			if ( ! empty( $files ) && is_array( $files ) ) {
				$three_days_ago = time() - ( 3 * DAY_IN_SECONDS ); 

				foreach ( $files as $file ) {
					if ( file_exists( $file ) ) {
						$file_modified_time = filemtime( $file );

						if ( $file_modified_time < $three_days_ago ) {
							@unlink( $file );
						}
					}
				}
			}
		}

		public function mrkv_liqpay_finalize_hold($order_id, $old_status, $new_status, $order)
		{
            $payment_method = $order->get_payment_method();

            if('morkva-liqpay' == $payment_method)
            {
				$wc_gateways      = WC()->payment_gateways();
	    		$payment_gateways = $wc_gateways->get_available_payment_gateways();

	    		if ( !isset( $payment_gateways['morkva-liqpay'] ) ) {
	    			return;
	    		}

	    		$liqpay_payment_gateway = $payment_gateways['morkva-liqpay'];

	    		if($liqpay_payment_gateway && $liqpay_payment_gateway->get_mrkv_liqpay_hold_enabled())
	    		{
					$hold_cancel_status = $liqpay_payment_gateway->get_mrkv_liqpay_hold_cancel_status();
					$is_cancelled_hold = false;

	    			if($status_hold)
	    			{
	    				if ($new_status == $status_hold) 
	    				{
					        $is_cancelled_hold = true;
					    }
	    			}
	    			elseif($new_status == 'cancelled')
	    			{
	    				$is_cancelled_hold = true;
	    			}

	    			if($is_cancelled_hold)
	    			{
						require_once(__DIR__ . '/classes/MorkvaLiqPay.php');
						$mrkv_liqpay_token = $liqpay_payment_gateway->get_keys_access();

						$mrkv_liqpay_payment = new MorkvaLiqPay($mrkv_liqpay_token['public_key'], $mrkv_liqpay_token['private_key']);

						$mrkv_liqpay_payment->mrkv_liqpay_hold_cancel($order_id, $order->get_total());

						$order->add_order_note(__('Hold canceled', 'mrkv-liqpay-extended'));
					}
				}
			}
		}

		public function mrkv_liqpay_settings_sidebar_func()
		{
			?>
				<div class="morkva-settings-sidebar" style="flex: 1; min-width: 250px;">
					<div class="morkva-settings-sidebar_inner" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px;margin-bottom:15px;">
						<h3 style="margin-top: 0;"><?php echo esc_html__( 'Like this plugin?', 'mrkv-liqpay-extended' ); ?></h3>
						<p>
							<?php echo esc_html__( 'Support our efforts with a', 'mrkv-liqpay-extended' ); ?>
							<img src="<?php echo esc_url(plugins_url( '../img/star.svg', __FILE__ )); ?>" alt="Star" alt="Star">
							<img src="<?php echo esc_url(plugins_url( '../img/star.svg', __FILE__ )); ?>" alt="Star" alt="Star">
							<img src="<?php echo esc_url(plugins_url( '../img/star.svg', __FILE__ )); ?>" alt="Star" alt="Star">
							<img src="<?php echo esc_url(plugins_url( '../img/star.svg', __FILE__ )); ?>" alt="Star" alt="Star">
							<img src="<?php echo esc_url(plugins_url( '../img/star.svg', __FILE__ )); ?>" alt="Star" alt="Star">
							<?php echo esc_html__( 'review at', 'mrkv-liqpay-extended' ); ?> <a href="https://wordpress.org/plugins/mrkv-liqpay-extended/" target="blanc">WordPress.org</a>
						</p>
						<a class="button button-primary" href="https://wordpress.org/plugins/mrkv-liqpay-extended/" target="blanc">
							<?php echo esc_html__( 'Leave', 'mrkv-liqpay-extended' ) . ' '; ?>
							<img src="<?php echo esc_url(plugins_url( '../img/star.svg', __FILE__ )); ?>" alt="Star" alt="Star">
							<img src="<?php echo esc_url(plugins_url( '../img/star.svg', __FILE__ )); ?>" alt="Star" alt="Star">
							<img src="<?php echo esc_url(plugins_url( '../img/star.svg', __FILE__ )); ?>" alt="Star" alt="Star">
							<img src="<?php echo esc_url(plugins_url( '../img/star.svg', __FILE__ )); ?>" alt="Star" alt="Star">
							<img src="<?php echo esc_url(plugins_url( '../img/star.svg', __FILE__ )); ?>" alt="Star" alt="Star">
						</a>
						<p>
							<?php echo esc_html__( 'Isn’t good enough for a 5', 'mrkv-liqpay-extended' ); ?> 
							<img src="<?php echo esc_url(plugins_url( '../img/star.svg', __FILE__ )); ?>" alt="Star" alt="Star">? 
							<?php echo esc_html__( 'Contact us via the widget on our website, or check out', 'mrkv-liqpay-extended' ); ?> <a href="https://docs.morkva.co.ua/uk?utm_source=plugin&utm_medium=sidebar&utm_campaign=liqpay_free" target="blanc"> <?php echo esc_html__( 'documantation', 'mrkv-liqpay-extended' ); ?></a>
						</p>
						<div class="mrkv-btns-line-sidebar" style="display: flex;gap: 4px;">
							<a class="button button-primary" href="https://morkva.co.ua/?utm_source=plugin&utm_medium=sidebar&utm_campaign=liqpay_free" target="blanc">
								<?php echo esc_html__( 'Go to the website', 'mrkv-liqpay-extended' ); ?>
							</a>
							<a class="button" href="https://docs.morkva.co.ua/uk?utm_source=plugin&utm_medium=sidebar&utm_campaign=liqpay_free" target="blanc">
								<?php echo esc_html__( 'Documantation', 'mrkv-liqpay-extended' ); ?>
							</a>
						</div>
					</div>
					<div class="morkva-settings-sidebar_inner" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px;margin-bottom:15px;">
						<h3 style="margin-top: 0;"><?php echo esc_html__( 'Check out pro-version', 'mrkv-liqpay-extended' ); ?></h3>
						<ul>
							<li>
								<img src="<?php echo esc_url(plugins_url( '../img/check.svg', __FILE__ )); ?>" alt="Check" alt="Check">
								<?php echo esc_html__( 'Pay by Parts', 'mrkv-liqpay-extended' ); ?>
							</li>
							<li>
								<img src="<?php echo esc_url(plugins_url( '../img/check.svg', __FILE__ )); ?>" alt="Check" alt="Check">
								<?php echo esc_html__( 'Payment status validation', 'mrkv-liqpay-extended' ); ?>
							</li>
							<li>
								<img src="<?php echo esc_url(plugins_url( '../img/check.svg', __FILE__ )); ?>" alt="Check" alt="Check">
								<?php echo esc_html__( 'Prepay', 'mrkv-liqpay-extended' ); ?>
							</li>
							<li><?php echo esc_html__( 'and more', 'mrkv-liqpay-extended' ); ?></li>
						</ul>
						<a class="button button-primary" href="https://morkva.co.ua/shop/woocommerce-liqpay-extended-pro/?utm_source=plugin&utm_medium=sidebar&utm_campaign=liqpay_free" target="blanc">
							<?php echo esc_html__( 'Buy Pro-version', 'mrkv-liqpay-extended' ); ?>
						</a>
					</div>
					<div class="morkva-settings-sidebar_inner" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px;margin-bottom:15px;">
						<h3 style="margin-top: 0;"><?php echo esc_html__( 'Other free plugins', 'mrkv-liqpay-extended' ); ?></h3>
						<p><?php echo esc_html__( 'All our plugins are cross-compatible', 'mrkv-liqpay-extended' ); ?></p>
						<?php
							$response = wp_remote_get( 'https://morkva.co.ua/wp-json/pluginManagement/v2', array(
								'headers' => array(
								),
								'timeout' => 30,
								'redirection' => 5,
								'httpversion' => '1.1',
								'sslverify' => true
							));

							$mrkv_mono_response_data = $response['body'] ? json_decode( $response['body'], true ) : null;
							$mrkv_mono_plugins = $mrkv_mono_response_data['plugins'] ?? [];

							if(!empty($mrkv_mono_plugins))
							{
								?>
									<ul style="list-style: disc;padding-left: 17px;">
										<?php
											foreach($mrkv_mono_plugins as $plugin_slug => $plugin_data)
											{
												if($plugin_slug == 'mrkv-liqpay-extended'){ continue; }
												?>
													<li>
														<a style="margin-bottom:5px;" href="<?php echo esc_attr($plugin_data['url'] ?? ''); ?>?utm_source=plugin&utm_medium=sidebar&utm_campaign=liqpay_free" target="blanc" class="plugin_line"><?php echo esc_attr($plugin_data['label'] ?? ''); ?></a>
														<span>- 
														<?php 
															$current_desc = (strpos(get_user_locale(), 'uk') === 0) 
																? ($plugin_data['description'] ?? '') 
																: ($plugin_data['description_en'] ?? '');
																
															echo esc_attr($current_desc); 
														?>
														</span>
													</li>
												<?php
											}
										?>
									</ul>
								<?php
							}
						?>
					</div>
				</div>
			<?php
		}

		/**
	     * Generating meta boxes
	     *
	     * @since 1.0.0
	     */
	    public function mrkv_liqpay_add_meta_boxes($post_type, $post)
	    {
	    	# Check hpos
	        if(class_exists( CustomOrdersTableController::class )){
	            $screen = wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled()
	            ? wc_get_page_screen_id( 'shop-order' )
	            : 'shop_order';
	        }
	        else{
	            $screen = 'shop_order';
	        }

	        if ( $post instanceof \WP_Post ) {
                $order_id = $post->ID;
            } elseif ( $post instanceof \WC_Order ) {
                $order_id = $post->get_id();
            } else {
                $order_id = false;
            }

	        # Check order id
	    	if ($order_id) 
	    	{
	            # Get order by id
	            $order = wc_get_order($order_id);

            	if($order)
            	{
            		# Get payment method
		            $payment_method = $order->get_payment_method();

		            # Check liqpaypay method
		            if('morkva-liqpay' == $payment_method && $order->get_meta('mrkv_liqpay_accuiring_action') == 'hold')
		            {
		            	# Add metabox
		         		add_meta_box('mrkv_liqpay_hold', __('Liqpay Hold', 'mrkv-liqpay-extended'), array( $this, 'mrkv_liqpay_add_plugin_meta_box' ), $screen, 'side', 'core');   
		            }
            	}
	    	}
	    }

	    public function mrkv_liqpay_add_plugin_meta_box($post)
	    {
	    	if ( $post instanceof \WP_Post ) {
                $order_id = $post->ID;
            } elseif ( $post instanceof \WC_Order ) {
                $order_id = $post->get_id();
            } else {
                $order_id = false;
            }

	    	# Check order id
	    	if ($order_id) 
	    	{
	            # Get order by id
	            $order = wc_get_order($order_id);

	            if($order)
	            {
            		$order_status = $order->get_status();
			        if ($order_status == 'on-hold') 
			        {
			            $finalize_text = __('Finalize', 'mrkv-liqpay-extended');
				        $cancel_hold_text = __('Cancel hold', 'mrkv-liqpay-extended');
				        $enter_amount_text = __('Enter amount', 'mrkv-liqpay-extended');
				        $cancel_text = __('Cancel', 'mrkv-liqpay-extended');
				        $payment_amount = $order->get_total();
						$hold_nonce = wp_create_nonce( 'mrkv_liqpay_hold_action' );
		        		?>
				            <div id="hold_form_container">
				                <label for="liqpay_amount" class="label-on-top">
				                    <?php echo esc_attr($enter_amount_text); ?>
				                </label>
				                <div class="col-sm">
				                    <div class="input-group">
				                        <input type="text" id="liqpay_amount" name="finalization_amount" required="required"
				                               value="<?php echo esc_attr($payment_amount); ?>"/>
				                    </div>
				                </div>
				                <br/>
				                <div class="text-left">
				                    <a class="button button-danger" onclick="jQuery.ajax({
				                            url: '<?php echo esc_url(admin_url( "admin-ajax.php" )); ?>',
				                            type: 'POST',
				                            data: {
				                            	'action' : 'mrkv_liqpay_cancel_payment_hold',
				                                'order_id': '<?php echo esc_attr($order_id); ?>',
												'nonce': '<?php echo esc_js( $hold_nonce ); ?>'
				                            },
				                            success: function (response) {
				                                window.location.reload();
				                            },
				                        })"><?php echo esc_attr($cancel_hold_text); ?></a>                                     
				                
				                    <a class="button button-primary" onclick="jQuery.ajax({
				                            url: '<?php echo esc_url(admin_url( "admin-ajax.php" )); ?>',
				                            type: 'POST',
				                            data: {
				                            	'action' : 'mrkv_liqpay_final_payment_hold',
				                                'order_id': '<?php echo esc_attr($order_id); ?>',
				                                'finalization_amount': document.getElementById('liqpay_amount').value,
												'nonce': '<?php echo esc_js( $hold_nonce ); ?>'
				                            },
				                            success: function (response) {
				                                window.location.reload();
				                            },
				                        })"><?php echo esc_attr($finalize_text); ?></a>
				                </div>
				                <div>
				                	<?php echo esc_html__('Holding period is 30 days','mrkv-liqpay-extended'); ?></div>
				            </div>
		        		<?php
			        }
			        else
			        {
			        	echo esc_html__('Hold is not applied to the order.','mrkv-liqpay-extended');
			        }
	            }
	        }
	    }

	    public function mrkv_liqpay_cancel_payment_hold_func()
		{
			if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['nonce'])), 'mrkv_liqpay_hold_action')) {
		        wp_send_json_error(__('Invalid nonce.', 'mrkv-liqpay-extended'), 403);
		        wp_die();
		    }
			
			if (!current_user_can('edit_shop_orders')) {
				wp_send_json_error(__('You do not have permission to edit orders.', 'mrkv-liqpay-extended'), 403);
			}

			$order_id = isset( $_POST['order_id'] ) ? absint( wp_unslash( $_POST['order_id'] ) ) : 0;

			if (!$order_id) {
	            return;
	        }

	        $order = wc_get_order($order_id);
	        if ($order) 
	        {
	            $order_status = $order->get_status();
		        if ($order_status == 'on-hold') 
		        {
		        	# Get token by mono gateway
		    		$wc_gateways      = new WC_Payment_Gateways();
		    		$payment_gateways = $wc_gateways->get_available_payment_gateways();
		    		$liqpay_payment_gateway = $payment_gateways['morkva-liqpay'];

		    		# Include Api morkva liqpay
        			require_once(__DIR__ . '/classes/MorkvaLiqPay.php');
		    		$mrkv_liqpay_token = $liqpay_payment_gateway->get_keys_access();

		    		$mrkv_liqpay_payment = new MorkvaLiqPay($mrkv_liqpay_token['public_key'], $mrkv_liqpay_token['private_key']);

		    		$mrkv_liqpay_payment->mrkv_liqpay_hold_cancel($order_id, $order->get_total());

		    		$order->add_order_note(__('Hold canceled', 'mrkv-liqpay-extended'));
		        }
	        }

	        wp_die();
		}

		public function mrkv_liqpay_final_payment_hold_func()
		{
			if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['nonce'])), 'mrkv_liqpay_hold_action')) {
		        wp_send_json_error(__('Invalid nonce.', 'mrkv-liqpay-extended'), 403);
		        wp_die();
		    }
			
			if (!current_user_can('edit_shop_orders')) {
				wp_send_json_error(__('You do not have permission to edit orders.', 'mrkv-liqpay-extended'), 403);
			}

			$order_id = isset( $_POST['order_id'] ) ? absint( wp_unslash( $_POST['order_id'] ) ) : 0;

			if (!$order_id) {
	            return;
	        }

	        $order = wc_get_order($order_id);
	        if ($order) 
	        {
	            $order_status = $order->get_status();
		        if ($order_status == 'on-hold') 
		        {
		        	# Get token by mono gateway
		    		$wc_gateways      = new WC_Payment_Gateways();
		    		$payment_gateways = $wc_gateways->get_available_payment_gateways();
		    		$liqpay_payment_gateway = $payment_gateways['morkva-liqpay'];
		    		$mrkv_liqpay_token = $liqpay_payment_gateway->get_keys_access();

		    		# Include Api Morkva liqpay
        			require_once(__DIR__ . '/classes/MorkvaLiqPay.php');
        			$mrkv_liqpay_payment = new MorkvaLiqPay($mrkv_liqpay_token['public_key'], $mrkv_liqpay_token['private_key']);

        			$result = $mrkv_liqpay_payment->mrkv_liqpay_hold_final($order_id, $order->get_total());

        			if(isset($result['error']))
        			{
        				$order->add_order_note('Error: ' . wp_json_encode($result['error'], JSON_UNESCAPED_UNICODE));
        			}
        			else
        			{
        				$order->add_order_note(__('Hold finalized', 'mrkv-liqpay-extended'));
		            	$order->payment_complete();
		            	$order->save();
        			}
		        }
	        }

	        wp_die();
		}
	}
}