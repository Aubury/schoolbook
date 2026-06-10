<?php
# Check user access
defined( 'ABSPATH' ) || exit;

# Check if class exist
if (!class_exists('MRKV_UA_SHIPPING_NOVA_POSHTA_INTER_ADDRESS'))
{
    /**
     * Add new delivery method
     * */
    class MRKV_UA_SHIPPING_NOVA_POSHTA_INTER_ADDRESS extends WC_Shipping_Method 
    {
        /**
         * Constructor new shipping method
         * */
        public function __construct($instance_id = 0) 
        {
            $this->instance_id = absint( $instance_id );
            parent::__construct( $instance_id );

            # These title description are display on the configuration page
            $this->id = 'mrkv_ua_shipping_nova-poshta_inter_address';
            $this->method_title = __('Nova Poshta Address International', 'mrkv-ua-shipping');
            $this->method_description = '';

            # Add support zones
            $this->supports = array(
                'shipping-zones',
                'instance-settings',
                'instance-settings-modal',
            );
            
            # Run the initial method
            $this->init();

            # Set title
            $this->title = $this->get_option( 'title' );

            # Enabled method
            $this->enabled = $this->get_option('enabled');
            if (empty($this->enabled)) {
                $this->enabled = 'yes';
            }
            
        }

        /**
         * Load the settings API
         * */
        public function init() 
        {
            # Load the settings API
            $this->init_settings();

            # Add the form fields
            $this->init_form_fields();

            # Save settings in admin if you have any defined
            add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
        }

        /**
         * Initialize all shipping fields
         * */
        public function init_form_fields() 
        {
             $this->instance_form_fields = array(
                'title' => array(
                    'title' => $this->method_title,
                    'type' => 'text',
                    'description' => $this->method_description,
                    'default' => $this->method_title
                ),
                'enable_cost' => array(
                    'title' => __('Enable Price for Delivery', 'mrkv-ua-shipping'),
                    'label' => __('If checked, shipping price will be add for delivery', 'mrkv-ua-shipping'),
                    'type' => 'checkbox',
                    'default' => 'no',
                    'description' => '',
                ),
                'enable_fix_cost' => array(
                    'title' => __('Enable Fixed Price for Delivery', 'mrkv-ua-shipping'),
                    'label' => __('If checked, fixed price will be set for delivery', 'mrkv-ua-shipping'),
                    'type' => 'checkbox',
                    'default' => 'no',
                    'description' => '',
                ),
                'fix_cost_total' => array(
                    'title' => __('Fixed shipping price', 'mrkv-ua-shipping'),
                    'type' => 'text',
                    'placeholder' => __('Enter the amount in numbers', 'mrkv-ua-shipping'),
                    'description' => '',
                    'default' => 0.00
                ),
                'enable_minimum_cost' => array(
                    'title' => __('Enable Minimum amount for free shipping', 'mrkv-ua-shipping'),
                    'label' => __('If checked, Minimum amount for free shipping will be set for delivery', 'mrkv-ua-shipping'),
                    'type' => 'checkbox',
                    'default' => 'no',
                    'description' => '',
                ),
                'minimum_cost_total' => array(
                    'title' => __('Minimum amount for free shipping', 'mrkv-ua-shipping'),
                    'type' => 'text',
                    'placeholder' => __('Enter the amount in numbers', 'mrkv-ua-shipping'),
                    'description' => '',
                    'default' => 0.00
                ),
                'free_shipping_text' => array(
                    'title' => __('Text with free delivery', 'mrkv-ua-shipping'),
                    'type' => 'text',
                    'placeholder' => __('FREE to Nova Post', 'mrkv-ua-shipping'),
                    'description' => '',
                )
            );
        }

        /**
         * Add rate to delivery
         * @param array Package
         * */
        public function calculate_shipping( $package = array() ) 
        {
            # Create rate
            $rate = array(
                'id' => $this->id,
                'label' => $this->title,
                'cost' => 0.00,
                'calc_tax' => 'per_item'
            );
            if($this->get_option('enable_cost') && $this->get_option('enable_cost') == 'yes' && $this->get_option('enable_fix_cost') != 'yes')
            {
                $settings_method = get_option('nova-poshta_m_ua_settings');

                $payer_type = isset($settings_method['inter']['payer']) ? $settings_method['inter']['payer'] : 'Recipient';
                $sender_division_id = isset($settings_method['inter']['division_id']) ? $settings_method['inter']['division_id'] : '';
                $country = '';
                $recipient_postcode = '';
                $weight = 0.00;
                $length = 0.00;
                $cost = WC()->cart->get_subtotal();
                $cargo = isset($settings_method['inter']['shipment_type']) ? $settings_method['inter']['shipment_type'] : 'Parcel';
                $sender_country = 'UA';

                if(isset( $_POST['post_data'] ))
                {
                    parse_str( $_POST['post_data'], $post_data );
                    
                    if(isset($post_data['billing_country']) && $post_data['billing_country'])
                    {
                        $country = $post_data['billing_country'];
                    }

                    if(isset($post_data[$this->id . '_postcode']) && $post_data[$this->id . '_postcode'])
                    {
                        $recipient_postcode = $post_data[$this->id . '_postcode'];
                    }
                }

                if(!$recipient_postcode && isset($_POST[$this->id . '_postcode']))
                {
                    $recipient_postcode = $_POST[$this->id . '_postcode'];
                }

                if(!$country && isset($_POST['billing_country']))
                {
                    $country = $_POST['billing_country'];
                }

                if($country && $recipient_postcode)
                {
                    if(isset($settings_method['inter']['cart_total']) && $settings_method['inter']['cart_total'] == 'total')
                    {
                        $cost = WC()->cart->cart_contents_total;
                    }

                    $volume_weight = 0.00;
                    $dimension_unit = get_option( 'woocommerce_dimension_unit' );
                    $cargo_item_length = 0.00;
                    $cargo_item_width = 0.00;
                    $cargo_item_height = 0.00;
                    foreach(WC()->cart->get_cart() as $cart_item => $cart_value)
                    {
                        $item_length = ( null !== $cart_value['data']->get_length() && $cart_value['data']->get_length()) ? wc_get_dimension( $cart_value['data']->get_length(), 'cm', $dimension_unit ) : 0.00;
                        $item_width = ( null !== $cart_value['data']->get_width() && $cart_value['data']->get_width()) ? wc_get_dimension( $cart_value['data']->get_width(), 'cm', $dimension_unit ) : 0.00;
                        $item_height = ( null !== $cart_value['data']->get_height() && $cart_value['data']->get_height()) ? wc_get_dimension( $cart_value['data']->get_height(), 'cm', $dimension_unit ) : 0.00;

                        $volume_weight += $item_length * $item_width * $item_height / 4000;
                        $cargo_item_length = max( $cargo_item_length, $item_length );
                        $cargo_item_width = max( $cargo_item_width, $item_width );
                        $cargo_item_height = max( $cargo_item_height, $item_height );
                    }

                    if((!$volume_weight) && isset($settings_method['inter']['volume']) && $settings_method['inter']['volume'])
                    {
                        $volume_weight = floatval($settings_method['inter']['volume']);
                    }

                    $weight_coef = $this->convert_weight_unit();
                    $actual_weight = ( WC()->cart->cart_contents_weight > 0 ) ? WC()->cart->cart_contents_weight * $weight_coef : 0.00;

                    if((!$actual_weight) && isset($settings_method['inter']['weight']) && $settings_method['inter']['weight'])
                    {
                        $actual_weight = floatval($settings_method['inter']['weight']);
                    }

                    $args = [
                        "payerType" => $payer_type,
                        "parcels" => [
                            [ 
                                "cargoCategory" => strtolower($cargo),
                                "insuranceCost" => $cost,
                                "rowNumber" => 1,
                                "width" => $cargo_item_width * 10,
                                "length" => $cargo_item_length * 10,
                                "height" => $cargo_item_height * 10,
                                "actualWeight" => $actual_weight * 1000,
                                "volumetricWeight" => $volume_weight
                            ]
                        ],
                        "sender" => [
                            "countryCode" => $sender_country,
                            "divisionId" => $sender_division_id,
                            "addressParts" => new stdClass()
                        ],
                        "recipient" => [
                            "countryCode" => $country,
                            "addressParts" => [
                                'postCode' => $recipient_postcode
                            ]
                        ]
                    ];

                    require_once MRKV_UA_SHIPPING_PLUGIN_PATH . 'classes/shipping_methods/nova-poshta/api/mrkv-ua-shipping-api-nova-post.php';
                    $api_internal = new MRKV_UA_SHIPPING_API_NOVA_POST($settings_method);

                    $data = $api_internal->send_post_request($args, 'shipments/calculations', 'POST');

                    if(isset($data['services'][0]['price']))
                    {
                        $cost_time = $data['services'][0]['price'];
                        $cost_currncy = $data['services'][0]['currencyCode'];
                        $wp_timezone = wp_timezone();
                        $dt = new DateTime( 'now', $wp_timezone );
                        $dt->setTime( 0, 0, 0, 0 );
                        $dt->setTimezone( new DateTimeZone( 'UTC' ) );

                        $exchange_args = [
                            'amount' => $cost_time,
                            'countryCode' => 'UA',
                            'currencyCode' => $cost_currncy,
                            'date' => $dt->format( 'Y-m-d\T00:00:00.000000\Z' )
                        ];

                        $data = $api_internal->send_post_request($exchange_args, 'exchange-rates/conversion', 'POST');

                        $target_currency = get_woocommerce_currency();
                        $amount = 0;

                        if (isset($data['convertedCurrencies']) && is_array($data['convertedCurrencies'])) 
                        {
                            foreach ($data['convertedCurrencies'] as $currency) {
                                if ($currency['currencyCode'] === $target_currency) {
                                    $amount = $currency['amount'];
                                    break;
                                }
                            }
                        }

                        $rate['cost'] = $amount;
                    }
                }
            }

            if($this->get_option('enable_fix_cost') && $this->get_option('enable_fix_cost') == 'yes')
            {
                $rate['cost'] = $this->get_option('fix_cost_total');
            }

            if($this->get_option('enable_minimum_cost') && $this->get_option('enable_minimum_cost') == 'yes')
            {
                $woo_cart_total = WC()->cart->get_subtotal();

                if($woo_cart_total >= $this->get_option('minimum_cost_total'))
                {
                    $rate['cost'] = 0.00;

                    if($this->get_option('free_shipping_text'))
                    {
                        $rate['label'] = $this->get_option('free_shipping_text');
                    }
                }
            }

            # Set rate
            $this->add_rate($rate);
        }

        /**
         * Is this method available?
         * @param array $package
         * @return bool
         */
        public function is_available($package)
        {
            # Check shipping enabled
            return $this->is_enabled();
        }

        public function convert_weight_unit() {

            $weight_unit = get_option('woocommerce_weight_unit');

            if ( 'g' == $weight_unit ) return 0.001;
            if ( 'kg' == $weight_unit ) return 1;
            if ( 'lbs' == $weight_unit ) return 0.45359;
            if ( 'oz' == $weight_unit ) return 0.02834;
        }
    }
}