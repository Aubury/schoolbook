<?php
/**
 * Class WC_Gateway_Morkva_Liqpay file.
 *
 * @package WooCommerce\Gateways
 * 
 */

# This prevents a public user from directly accessing your .php files
if (!defined('ABSPATH')) 
{
    # Exit if accessed directly
    exit; 
}

use Automattic\WooCommerce\Utilities\OrderUtil;

/**
 * WC_Gateway_Morkva_Liqpay Class
 * 
 */
class WC_Gateway_Morkva_Liqpay extends WC_Payment_Gateway
{
    public string $lang;
    public array $enable_for_methods;
    public bool $enable_for_virtual;
    
    /**
     * Constructor for the gateway
     */
    public function __construct()
    {
        # Setup general properties
        $this->setup_properties();

        # Load the settings
        $this->init_form_fields();
        $this->init_settings();

        # Get settings
        # Save Gateway title
        $this->title = $this->get_option('title');

        # Save Gateway description
        $this->description = $this->get_option('description');

        # Save Gateway default language
        $this->lang = $this->get_option('lang', 'uk');

        # Save Gateway enabled method switcher
        $this->enable_for_methods = $this->get_option('enable_for_methods', array());

        # Save Gateway enabled virtual
        $this->enable_for_virtual = $this->get_option('enable_for_virtual', 'yes') === 'yes';

        # Save type taxonomies support
        $this->supports = array(
                'products',
                'refunds',
        );

        # Check woo plugin version
        if (version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>=')) 
        {
            # Version Woocommerce 2.0.0 
            add_action('woocommerce_api_' . strtolower(get_class($this)), array($this, 'check_response'));
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        } 
        else 
        {
            # Version Woocommerce 1.6.6
            add_action('init', array(&$this, 'check_response'));
            add_action('woocommerce_update_options_payment_gateways', array(&$this, 'process_admin_options'));
        }

        # Add payment image
        add_filter( 'woocommerce_gateway_icon', array( $this, 'morkva_liqpay_gateway_icon' ), 100, 2 );
        add_filter('woocommerce_available_payment_gateways', array($this, 'mrkv_filter_gateway_by_admin_test_mode'), 10, 1);
    }

    /**
     * Setup general properties for the gateway
     * 
     */
    protected function setup_properties()
    {
        # Save slug of Morkva Liqpay
        $this->id = 'morkva-liqpay';
        $this->icon = $this->get_admin_icon_url();
        $this->method_title = __('morkva LiqPay ', 'mrkv-liqpay-extended');
        $this->method_description = __('A payment service that allows you to make instant payments on the Internet and with Visa and MasterCard payment cards worldwide.', 'mrkv-liqpay-extended');
        $this->has_fields = false;
    }

    public function get_admin_icon_url()
    {
        return plugins_url( '../img/morkva-liqpay-logo.svg', __FILE__ );
    }

    public function admin_options() {
        $back_link = admin_url( 'admin.php?page=wc-settings&tab=checkout' );
        ?>
        <div class="morkva-settings-wrapper" style="display: flex; justify-content: space-between; align-items: flex-start; margin-top: 20px;">
            
            <div class="morkva-settings-main" style="flex: 3;">
                <h2 class="wc-admin-header">
                    <small>
                        <a href="<?php echo esc_url( $back_link ); ?>" aria-label="<?php esc_attr_e( 'Return to payments', 'mrkv-liqpay-extended' ); ?>">
                            <span class="dashicons dashicons-arrow-left-alt2" aria-hidden="true"></span>
                        </a>
                    </small>
                    <?php echo esc_html( $this->method_title ); ?>
                </h2>

                <?php echo wp_kses_post( wpautop( $this->method_description ) ); ?>
                
                <table class="form-table">
                    <?php $this->generate_settings_html(); ?>
                </table>
            </div>

            <?php do_action('mrkv_liqpay_settings_sidebar'); ?>
        </div>
        <?php
    }

    /**
     * Initialise Gateway Settings Form Fields
     * 
     */
    public function init_form_fields()
    {
        $all_order_statuses = wc_get_order_statuses();
        $correct_order_statuses = array();

        foreach($all_order_statuses as $k => $v)
        {
            $k = str_replace('wc-', '', $k);
            $correct_order_statuses[$k] = $v;
        }

        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable/disable', 'mrkv-liqpay-extended'),
                'label' => __('Enable', 'mrkv-liqpay-extended'),
                'type' => 'checkbox',
                'description' => '',
                'default' => 'no',
            ),
            'title' => array(
                'title' => __('Title', 'mrkv-liqpay-extended'),
                'type' => 'text',
                'description' => __('morkva LiqPay - Instant payments around the world', 'mrkv-liqpay-extended'),
                'default' => '',
                'desc_tip' => true,
            ),
            'description' => array(
                'title' => __('Description', 'mrkv-liqpay-extended'),
                'type' => 'textarea',
                'description' => __('The text that will be visible on the checkout page if you choose the payment method Liqpay', 'mrkv-liqpay-extended'),
                'default' => '',
                'desc_tip' => false,
            ),
            'payment_description_template' => array(
                'title'       => __( 'Payment destination', 'mrkv-liqpay-extended' ),
                'type'        => 'text',
                'description' => __( 'Template for the "description" field sent to LiqPay (shown to customer as payment purpose). Available shortcodes: {order_id}, {billing_first_name}, {billing_last_name}. Leave empty to keep default behavior.', 'mrkv-liqpay-extended' ) . '<br>' . __( 'Shortcodes:', 'mrkv-liqpay-extended' ) . '<br>' . __( '{billing_first_name} - The buyer\'s name as listed in the order\'s payment details,', 'mrkv-liqpay-extended' ) . '<br>' . __( '{billing_last_name} - the buyer\'s last name as listed in the order\'s billing information,', 'mrkv-liqpay-extended' ) . '<br>' . __( '{order_id} - Order ID.', 'mrkv-liqpay-extended' ),
                'placeholder' => __( 'Оплата за замовлення №{order_id} від {billing_last_name} {billing_first_name}', 'mrkv-liqpay-extended' ),
                'default'     => '',
                'desc_tip'    => false,
            ),
            'public_key' => array(
                'title' => __('API public_key', 'mrkv-liqpay-extended'),
                'type' => 'text',
                'description' => '',
                'default' => '',
                'desc_tip' => true,
                'placeholder' => '',
            ),
            'private_key' => array(
                'title' => __('API private_key', 'mrkv-liqpay-extended'),
                'type' => 'text',
                'description' => '',
                'default' => '',
                'desc_tip' => true,
                'placeholder' => '',
            ),
            'liqpay_order_status' => array(
                'title' => __( 'Status of completed payment', 'mrkv-liqpay-extended' ),
                'type' => 'select',
                'description' => __( 'Select the status to which the order status will change after successful payment', 'mrkv-liqpay-extended' ),
                'label' => '',
                'options' => $correct_order_statuses,
                'default' => 'processing',
            ),
            'use_holds' => array(
                'title' => __('Holds', 'mrkv-liqpay-extended'),
                'label' => '<span>' . __( 'Enable', 'mrkv-liqpay-extended' )  . '</span>',
                'type' => 'checkbox',
                'default' => 'no',
                'description' => __( 'The payment is held for 30 days. After this period, the payment is automatically finalized. You can finalize it manually from the order page, or it will be finalized automatically when the status changes.', 'mrkv-liqpay-extended' ),
            ),
            'hold_cancel_status' => array(
                'title' => __( 'Automatic cancellation of holding when the order status changes', 'mrkv-liqpay-extended' ),
                'type' => 'select',
                'description' => '<br>',
                'label' => '',
                'options' => $correct_order_statuses,
                'default' => 'cancelled',
            ),
            'title_method_image' => array(
                'title' => __( 'Image Settings', 'mrkv-liqpay-extended' ),
                'type' => 'title',
                'description' => __( 'Configure the display of the payment method logo on the checkout page', 'mrkv-liqpay-extended' ),
            ),
            'liqpay_image_type_black' => array(
                'title' => __( 'Image style', 'mrkv-liqpay-extended' ),
                'type' => 'checkbox',
                'label' => __( 'For white background', 'mrkv-liqpay-extended' ) . '<span></span><p style="padding: 20px;"><img style="width: 200px;" src="' . LIQPAY_PLUGIN_URL . 'img/logo_liqpay_for_white.svg"></p>',
                'default' => 'yes'
            ),
            'liqpay_image_type_white' => array(
                'title' => '',
                'type' => 'checkbox',
                'label' => __( 'For black background', 'mrkv-liqpay-extended' ) . '<span></span><p style="background: #676767; padding: 20px; border-radius: 10px;"><img style="width: 200px;" src="' . LIQPAY_PLUGIN_URL . 'img/logo_liqpay_for_black.svg"></p>',
                'default' => 'no'
            ),
            'liqpay_image_type_mini' => array(
                'title' => '',
                'type' => 'checkbox',
                'label' => __( 'Only icon', 'mrkv-liqpay-extended' ) . '<span></span><p style="padding: 20px;"><img style="width: 100px;" src="' . LIQPAY_PLUGIN_URL . 'img/morkva-liqpay-logo.svg"></p>',
                'default' => 'no'
            ),
            'liqpay_image_height' => array(
                'title' => __( 'Image height(px)', 'mrkv-liqpay-extended' ),
                'type' => 'number',
                'label' => '',
                'default' => ''
            ),
            'hide_image' => array(
                'title' => __( 'Hide logo', 'mrkv-liqpay-extended' ),
                'type' => 'checkbox',
                'label' => '<span>' . __( 'If checked, Liqpay logo or custom logo will not be displayed by the payment method title', 'mrkv-liqpay-extended' ) . '</span>',
                'default' => 'no'
            ),
            'url_liqpay_img' => array(
                'title'       => __( 'Custom logo url', 'mrkv-liqpay-extended' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => __( 'Enter full url to image', 'mrkv-liqpay-extended' ),
                'default'     => '',
            ),
            'liqpay_national_cashback' => array(
                'title' => __( 'National cashback', 'mrkv-liqpay-extended' ),
                'type' => 'title',
                'description' => __( 'Request Merchant ID and Terminal ID from LiqPay support', 'mrkv-liqpay-extended' ),
            ),
            'national_cashback_merchant_id' => array(
                'title'       => __( 'Merchant ID', 'mrkv-liqpay-extended' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => __( 'Enter Merchant ID', 'mrkv-liqpay-extended' ),
                'default'     => '',
            ),
            'national_cashback_terminal_id' => array(
                'title'       => __( 'Terminal ID', 'mrkv-liqpay-extended' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => __( 'Enter Terminal ID', 'mrkv-liqpay-extended' ),
                'default'     => '',
            ),
            'title_test_mode' => array(
                'title' => __( 'Test mode Settings', 'mrkv-liqpay-extended' ),
                'type' => 'title',
                'description' => __( 'To test the method\'s functionality, use test mode', 'mrkv-liqpay-extended' ),
            ),
            'test_enabled' => array(
                'title' => __('Test mode', 'mrkv-liqpay-extended'),
                'label' => __('Enable', 'mrkv-liqpay-extended'),
                'type' => 'checkbox',
                'description' => '',
                'default' => 'no',
            ),
            'test_enabled_admin' => array(
                'title' => __('Test mode for the administrator', 'mrkv-liqpay-extended'),
                'label' => __('Enable', 'mrkv-liqpay-extended'),
                'type' => 'checkbox',
                'description' => '',
                'default' => 'no',
            ),
            'test_public_key' => array(
                'title' => __('Test API public_key', 'mrkv-liqpay-extended'),
                'type' => 'text',
                'description' => '',
                'default' => '',
                'desc_tip' => true,
                'placeholder' => '',
            ),
            'test_private_key' => array(
                'title' => __('Test API private_key', 'mrkv-liqpay-extended'),
                'type' => 'text',
                'description' => '',
                'default' => '',
                'desc_tip' => true,
                'placeholder' => '',
            )
        );
    }

    /**
     * Add custom gateway icon
     * 
     * @var string Icon
     * @var string Payment id
     * */
    function morkva_liqpay_gateway_icon( $icon, $id ) 
    {
        if ( $id === 'morkva-liqpay' && !is_admin() ) 
        {
            if($this->get_option( 'hide_image' ) == 'no')
            {
                $height_btn = '';

                if($this->get_option( 'liqpay_image_height' )  != 'no' && $this->get_option( 'liqpay_image_height' )  != '')
                {
                    $height_btn = 'style="width: auto; height: ' . $this->get_option( 'liqpay_image_height' ) . 'px; padding-top: 0.6%;"';
                }
                else
                {
                    $height_btn = 'style="width: 100%; max-width: 100px; padding-top: 0"';
                }

                if($this->get_option( 'url_liqpay_img' ))
                {
                    return '<img ' . $height_btn . ' src="' . $this->get_option( 'url_liqpay_img' ) . '" > '; 
                }
                else
                {
                    if($this->get_option( 'liqpay_image_type_black' ) != 'no')
                    {
                        return '<img ' . $height_btn . ' src="' . plugins_url( '../img/logo_liqpay_for_white.svg', __FILE__ ) . '" > '; 
                    }
                    elseif($this->get_option( 'liqpay_image_type_mini' ) != 'no')
                    {
                        return '<img ' . $height_btn . ' src="' . plugins_url( '../img/morkva-liqpay-logo.svg', __FILE__ ) . '" > '; 
                    }
                    else
                    {
                        return '<img ' . $height_btn . ' src="' . plugins_url( '../img/logo_liqpay_for_black.svg', __FILE__ ) . '" > '; 
                    }
                }
            }
        }
        elseif($id === 'morkva-liqpay' && is_admin())
        {
            $admin_url = $this->get_admin_icon_url();
            return '<img src="' . esc_url( $admin_url ) . '" alt="' . esc_attr( $this->title ) . '" style="max-width: 100px; height: auto;" />';
        }
        
        return $icon;
    }

    public function get_icon_url()
    {
        if($this->get_option( 'hide_image' ) == 'no')
        {
            if($this->get_option( 'url_liqpay_img' ))
            {
                return $this->get_option( 'url_liqpay_img' ); 
            }
            else
            {
                if($this->get_option( 'liqpay_image_type_black' ) != 'no')
                {
                    return plugins_url( '../img/logo_liqpay_for_white.svg', __FILE__ ); 
                }
                elseif($this->get_option( 'liqpay_image_type_mini' ) != 'no')
                {
                    return plugins_url( '../img/morkva-liqpay-logo.svg', __FILE__ ); 
                }
                else
                {
                    return plugins_url( '../img/logo_liqpay_for_black.svg', __FILE__ ); 
                }
            }
        }
    }

    /**
     * Return description of order
     * 
     * @param integer $order_id Order id in Woo
     * @return string
     */
    private function getDescription($order_id)
    {
        $default = __( 'Payment for order № ', 'mrkv-liqpay-extended' ) . $order_id;
        $template = (string) $this->get_option( 'payment_description_template', '' );
        $template = trim( $template );
        if ( $template === '' ) {
            return $default;
        }
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return $default;
        }
        $replacements = array(
            '{order_id}'           => (string) $order->get_id(),
            '{billing_first_name}' => (string) $order->get_billing_first_name(),
            '{billing_last_name}'  => (string) $order->get_billing_last_name(),
        );
        return strtr( $template, $replacements );
    }

    /**
     * Process the payment and return the result.
     *
     * @param int $order_id Order ID
     * @return array
     */
    public function process_payment($order_id)
    {
        # Get order data by id
        $order = wc_get_order($order_id);

        # Check order total 
        if ($order->get_total() > 0) 
        {
            # Send email notification
            $this->pending_new_order_notification($order->get_id());
        } 

        # Include Api Morkva liqpay
        require_once(__DIR__ . '/classes/MorkvaLiqPay.php');

        # Check test mode
        if($this->get_option( 'test_enabled_admin' ) == 'yes' && ( current_user_can('editor') || current_user_can('administrator') ))
        {
            # Use test keys
            $morkva_liqPay = new MorkvaLiqPay($this->get_option('test_public_key'), $this->get_option('test_private_key'));
        }
        elseif($this->get_option( 'test_enabled' ) == 'yes'  && $this->get_option( 'test_enabled_admin' ) != 'yes')
        {
            # Use test keys
            $morkva_liqPay = new MorkvaLiqPay($this->get_option('test_public_key'), $this->get_option('test_private_key'));
        }
        else
        {   
            # Use main keys
            $morkva_liqPay = new MorkvaLiqPay($this->get_option('public_key'), $this->get_option('private_key'));
        }

        $action_pay = ($this->get_option( 'use_holds' ) == 'yes') ? 'hold' : 'pay';
        
        # Create argument of query
        $arrayData = array(
            'version' => '3',
            'action' => $action_pay,
            'amount' => $order->get_total(),
            'currency' => $order->get_currency(),
            'description' => $this->getDescription($order->get_id()),
            'order_id' => $order->get_id(),
            'result_url' => $this->get_return_url($order),
            'language' => 'uk',
            'server_url' => WC()->api_request_url( 'WC_Gateway_Morkva_Liqpay' )
        );

        update_post_meta($order->get_id(), 'mrkv_liqpay_accuiring_action', $action_pay);
        $order->update_meta_data( 'mrkv_liqpay_accuiring_action',  $action_pay);
        $order->save();

        # Create result link
        $url = $morkva_liqPay->cnb_link($arrayData);
        
        $logger = wc_get_logger();
        $context = array( 'source' => 'mrkv-liqpay-extended' );

        $log_message = "--- Liqpay Request ---\n";
        $log_message .= "Body: " . wp_json_encode($arrayData, JSON_UNESCAPED_UNICODE) . "\n";
        
        $log_message .= "Answer: " . wp_json_encode($url, JSON_UNESCAPED_UNICODE) . "\n";
        $log_message .= "------------------------";

        $logger->debug( $log_message, $context );
        

        # Return result 
        return array( 
            'result' => 'success',
            'redirect' => $url,
        );
    }

    /**
     * Output for the order received page
     */
    public function thankyou_page()
    {
        # Stop job to five seconds
        sleep(5);

        # Get order data 
        $order = wc_get_order($order_id);

        # Check order status
        if (!$order->has_status($this->status) && $this->cancel_pay) 
        {
            # Show info by payment
            echo wp_kses_post(wpautop(wptexturize($this->cancel_pay)));
        }
    }

    /**
     * Add content to the WC emails.
     *
     * @access public
     * @param WC_Order $order Order object.
     * @param bool $sent_to_admin Sent to admin.
     * @param bool $plain_text Email format: plain text or HTML.
     */
    public function email_instructions($order, $sent_to_admin, $plain_text = false)
    {
        
    }

    /**
     * New order notification function
     * 
     * @param $order_id Order id
     */
    private function pending_new_order_notification($order_id)
    {
        # Get order data 
        $order = wc_get_order($order_id);

        # Only for "pending" order status
        if (!$order->has_status('pending')) return;

        # Get an instance of the WC_Email_New_Order object
        $wc_email = WC()->mailer()->get_emails()['WC_Email_New_Order'];

        # Create email data
        $wc_email->settings['subject'] = '{site_title} - ' . __('New order', 'mrkv-liqpay-extended') . ' ({order_number}) - {order_date}';
        $wc_email->settings['heading'] = __('New order', 'mrkv-liqpay-extended');

        # Send email
        $wc_email->trigger($order_id);
    }

    public function get_keys_access()
    {
        $keys_access = array();

        # Check test mode
        if($this->get_option( 'test_enabled_admin' ) == 'yes' && ( current_user_can('editor') || current_user_can('administrator') ))
        {
            $keys_access['public_key'] = $this->get_option('test_public_key');
            $keys_access['private_key'] = $this->get_option('test_private_key');
        }
        
        elseif($this->get_option( 'test_enabled' ) == 'yes' && $this->get_option( 'test_enabled_admin' ) != 'yes')
        {
            $keys_access['public_key'] = $this->get_option('test_public_key');
            $keys_access['private_key'] = $this->get_option('test_private_key');
        }
        else
        {   
            $keys_access['public_key'] = $this->get_option('public_key');
            $keys_access['private_key'] = $this->get_option('private_key');
        }

        return $keys_access;
    }

    /**
     * Check response from LiqPay
     * 
     * @param $inputData All data
     * @return mixed|string|void
     */
    public function check_response($inputData)
    {
        # Get Woo global data
        global $woocommerce;

        // phpcs:disable WordPress.Security.NonceVerification.Missing 
        $success = isset($_POST['data']) && isset($_POST['signature']);
        
        $logger = wc_get_logger();
        $context = array( 'source' => 'mrkv-liqpay-extended' );

        $log_message = "--- Liqpay Callback ---\n";
        $log_message .= "Body: " . wp_json_encode($_POST, JSON_UNESCAPED_UNICODE) . "\n";
        $log_message .= "------------------------";

        $logger->debug( $log_message, $context );
        

        # If payment success
        if ($success) 
        {
            $data = '';
            $received_signature = '';

            // phpcs:disable WordPress.Security.NonceVerification.Missing 
            if(isset($_POST['data'])){
                // phpcs:disable WordPress.Security.NonceVerification.Missing 
                $data = sanitize_text_field(wp_unslash($_POST['data']));
            }
            // phpcs:disable WordPress.Security.NonceVerification.Missing 
            if(isset($_POST['signature'])){
                // phpcs:disable WordPress.Security.NonceVerification.Missing 
                $received_signature = sanitize_text_field(wp_unslash($_POST['signature'])); 
            }

            # Parse JSON data
            $parsed_data = json_decode(base64_decode($data)); 

            $log_message = "--- Liqpay Callback Parsed ---\n";
            $log_message .= "Body: " . wp_json_encode($parsed_data, JSON_UNESCAPED_UNICODE) . "\n";
            $log_message .= "------------------------";

            $logger->debug( $log_message, $context );
            
            # Save main data response
            $received_public_key = $parsed_data->public_key ?? '';
            $order_id     = $parsed_data->order_id ?? '';
            $status              = $parsed_data->status ?? ''; 
            $sender_phone        = $parsed_data->sender_phone ?? '';
            $amount              = $parsed_data->amount ?? '';
            $currency            = $parsed_data->currency ?? '';
            $transaction_id      = $parsed_data->transaction_id ?? '';

            # Get order data
            $order = wc_get_order($order_id);

            if(!$order_id)
            {
                return;
            }

            if(!$order)
            {
                return;
            }

            if($order && $status)
            {
                $order->update_meta_data( 'mrkv_liqpay_payment_status', $status );
                update_post_meta( $order_id, 'mrkv_liqpay_payment_status', $status );
                $order->save();
            }

            # Check status response 
            if ($status == 'success' || $status == 'sandbox' || $status == 'hold_wait' || $status == 'wait_secure') 
            {
                if(!$order->has_status('processing'))
                {
                    $message = '';

                    if(isset($parsed_data) && isset($parsed_data->sender_card_mask2) && !$order->get_meta('_mrkv_liqpay_sender_card_mask2'))
                    {
                        $order->update_meta_data( '_mrkv_liqpay_sender_card_mask2', $parsed_data->sender_card_mask2 );
                        update_post_meta( $order_id, '_mrkv_liqpay_sender_card_mask2', $parsed_data->sender_card_mask2 );


                        $message .= ' ' . __('sender_card_mask2:  ', 'mrkv-liqpay-extended') . $parsed_data->sender_card_mask2 . '<br>';
                    }

                    if(isset($parsed_data) && isset($parsed_data->sender_card_type) && !$order->get_meta('_mrkv_liqpay_sender_card_type'))
                    {
                        $order->update_meta_data( '_mrkv_liqpay_sender_card_type', $parsed_data->sender_card_type );
                        update_post_meta( $order_id, '_mrkv_liqpay_sender_card_type', $parsed_data->sender_card_type );

                        $message .= ' ' . __('sender_card_type:  ', 'mrkv-liqpay-extended') . $parsed_data->sender_card_type . '<br>';
                    }

                    if($this->get_option('national_cashback_merchant_id'))
                    {
                        $order->update_meta_data( '_mrkv_liqpay_acq_id', $this->get_option('national_cashback_merchant_id') );
                        update_post_meta( $order_id, '_mrkv_liqpay_acq_id', $this->get_option('national_cashback_merchant_id') );

                        $message .= ' ' . __('acq_id:  ', 'mrkv-liqpay-extended') . $this->get_option('national_cashback_merchant_id') . '<br>';
                    }
                    elseif(isset($parsed_data) && isset($parsed_data->acq_id)  && !$order->get_meta('_mrkv_liqpay_acq_id'))
                    {
                        $order->update_meta_data( '_mrkv_liqpay_acq_id', $parsed_data->acq_id );
                        update_post_meta( $order_id, '_mrkv_liqpay_acq_id', $parsed_data->acq_id );

                        $message .= ' ' . __('acq_id:  ', 'mrkv-liqpay-extended') . $parsed_data->acq_id . '<br>';
                    }

                    if($this->get_option('national_cashback_terminal_id'))
                    {
                        $order->update_meta_data( '_mrkv_liqpay_terminal_id', $this->get_option('national_cashback_terminal_id') );
                        update_post_meta( $order_id, '_mrkv_liqpay_terminal_id', $this->get_option('national_cashback_terminal_id') );

                        $message .= ' ' . __('terminal_id:  ', 'mrkv-liqpay-extended') . $this->get_option('national_cashback_terminal_id') . '<br>';
                    }

                    if(isset($parsed_data) && isset($parsed_data->agent_commission) && !$order->get_meta('_mrkv_liqpay_agent_commission'))
                    {
                        $order->update_meta_data( '_mrkv_liqpay_agent_commission', $parsed_data->agent_commission );
                        update_post_meta( $order_id, '_mrkv_liqpay_agent_commission', $parsed_data->agent_commission );

                        $message .= ' ' . __('agent_commission:  ', 'mrkv-liqpay-extended') . $parsed_data->agent_commission . '<br>';
                    }

                    if(isset($parsed_data) && isset($parsed_data->liqpay_order_id) && !$order->get_meta('_mrkv_liqpay_liqpay_order_id'))
                    {
                        $order->update_meta_data( '_mrkv_liqpay_liqpay_order_id', $parsed_data->liqpay_order_id );
                        update_post_meta( $order_id, '_mrkv_liqpay_liqpay_order_id', $parsed_data->liqpay_order_id );

                        $message .= ' ' . __('liqpay_order_id:  ', 'mrkv-liqpay-extended') . $parsed_data->liqpay_order_id . '<br>';
                    }

                    if(isset($parsed_data) && isset($parsed_data->receiver_commission) && !$order->get_meta('_mrkv_liqpay_receiver_commission'))
                    {
                        $order->update_meta_data( '_mrkv_liqpay_receiver_commission', $parsed_data->receiver_commission );
                        update_post_meta( $order_id, '_mrkv_liqpay_receiver_commission', $parsed_data->receiver_commission );

                        $message .= ' ' . __('receiver_commission:  ', 'mrkv-liqpay-extended') . $parsed_data->receiver_commission . '<br>';
                    }
                    
                    if(isset($parsed_data) && isset($parsed_data->commission_credit) && !$order->get_meta('_mrkv_liqpay_commission_credit'))
                    {
                        $order->update_meta_data( '_mrkv_liqpay_commission_credit', $parsed_data->commission_credit );
                        update_post_meta( $order_id, '_mrkv_liqpay_commission_credit', $parsed_data->commission_credit );

                        $message .= ' ' . __('commission_credit:  ', 'mrkv-liqpay-extended') . $parsed_data->commission_credit . '<br>';
                    }

                    if(isset($parsed_data) && isset($parsed_data->rrn_debit) && !$order->get_meta('_mrkv_liqpay_rrn_debit'))
                    {
                       $order->update_meta_data( '_mrkv_liqpay_rrn_debit', $parsed_data->rrn_debit );
                       update_post_meta( $order_id, '_mrkv_liqpay_rrn_debit', $parsed_data->rrn_debit );

                       $message .= ' ' . __('rrn_debit:  ', 'mrkv-liqpay-extended') . $parsed_data->rrn_debit . '<br>';
                    }

                    if(isset($parsed_data) && isset($parsed_data->authcode_debit) && !$order->get_meta('_mrkv_liqpay_authcode_debit'))
                    {
                       $order->update_meta_data( '_mrkv_liqpay_authcode_debit', $parsed_data->authcode_debit );
                       update_post_meta( $order_id, '_mrkv_liqpay_authcode_debit', $parsed_data->authcode_debit );

                       $message .= ' ' . __('authcode_debit:  ', 'mrkv-liqpay-extended') . $parsed_data->authcode_debit . '<br>';
                    }

                    // Save the order.
                    $order->save();

                    if($status == 'hold_wait')
                    {
                        # Add to order note payment status
                        $order->add_order_note(__('LiqPay payment hold has been completed successfully.<br/>LiqPay payment identifier:  ', 'mrkv-liqpay-extended') . $parsed_data->liqpay_order_id );
                    }
                    elseif($status == 'wait_secure')
                    {
                        # Add to order note payment status
                        $order->add_order_note(__('LiqPay payment has been completed, but requires additional approval by LiqPay. Transaction status: wait_secure.<br/>LiqPay payment identifier:  ', 'mrkv-liqpay-extended') . $parsed_data->liqpay_order_id );
                    }
                    else
                    {
                        # Add to order note payment status
                        $order->add_order_note(__('LiqPay payment has been completed successfully.<br/>LiqPay payment identifier:  ', 'mrkv-liqpay-extended') . $parsed_data->liqpay_order_id );
                    } 

                    if(isset($parsed_data) && isset($parsed_data->amount_debit) && isset($order_id)){                 
                        $order->update_meta_data( '_mrkv_liqpay_total_amount', $parsed_data->amount_debit );
                        // Save amount uah
                        update_post_meta( $order_id, '_mrkv_liqpay_total_amount', $parsed_data->amount_debit );

                        // Save the order.
                        $order->save();
                    }

                    $new_order_status = ($this->get_option( 'liqpay_order_status' ) && $this->get_option( 'liqpay_order_status' ) != '') ? $this->get_option( 'liqpay_order_status' ) : 'processing';

                    if($status == 'hold_wait')
                    {
                        # Update order status
                        $order->update_status('on-hold');
                    }
                    else
                    {
                        # Update order status
                        $order->update_status($new_order_status);
                        # Switch payment to complete
                        $order->payment_complete();
                    } 

                    $order->save();
                }
            } 
            else 
            {
                # Update status to failed
                $order->update_status('failed', __('Error during payment', 'mrkv-liqpay-extended'));

                # Stop server work
                exit;
            }
        } 
        else 
        {
            # Stop Wordpress job
            wp_die('IPN Request Failure');
        }
    }

    /**
     * Hide payment method if "Test mode for administrator" is enabled and user is not an admin.
     * 
     * @param array $available_gateways
     * @return array
     */
    public function mrkv_filter_gateway_by_admin_test_mode($available_gateways)
    {
        if (is_admin() || !isset($available_gateways[$this->id])) {
            return $available_gateways;
        }

        $test_admin_only = $this->get_option('test_enabled_admin');

        if ($test_admin_only === 'yes') {
            if (!current_user_can('manage_options') && !current_user_can('administrator')) {
                unset($available_gateways[$this->id]);
            }
        }

        return $available_gateways;
    }

    /**
     * Return hold status cancel
     * @return string hold status cancel
     * */
    public function get_mrkv_liqpay_hold_cancel_status()
    {
        if($this->get_option( 'hold_cancel_status' ))
        {
            return $this->get_option( 'hold_cancel_status' );
        }
        else
        {
            return '';
        }
    }

    /**
     * Return hold enabled
     * @return string hold enabled
     * */
    public function get_mrkv_liqpay_hold_enabled()
    {
        if($this->get_option( 'use_holds' ) && $this->get_option( 'use_holds' ) == 'yes')
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}
