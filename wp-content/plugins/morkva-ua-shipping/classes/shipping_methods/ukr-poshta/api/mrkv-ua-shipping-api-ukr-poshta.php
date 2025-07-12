<?php
# Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 

# Check if class exist
if (!class_exists('MRKV_UA_SHIPPING_API_UKR_POSHTA'))
{
	/**
	 * Class for shipping ukr poshta api
	 */
	class MRKV_UA_SHIPPING_API_UKR_POSHTA
	{
		/**
		 * @var string API URL
		 * */
		private $api_url = 'https://www.ukrposhta.ua/';

	    /**
	     * @var string $responseTime waiting for response from server, sec.
	     */
	    private $response_time = '30';

		/**
		 * @param array Settings
		 * */
		private $settings_method;

		/**
		 * @param object Log
		 * */
		public $debug_log;

		/**
		 * @var mixed API Active
		 * */
		public $active_api;

		/**
		 * @var mixed API Active
		 * */
		public $active_api_test;

		/**
		 * @var string Method Slug
		 * */
		public $slug_method = 'ukr-poshta';

		/**
		 * Constructor for shipping ukr poshta api
		 * */
		function __construct($settings)
		{
			# Set data
			$this->settings_method = $settings;
			$this->debug_log = new MRKV_UA_SHIPPING_LOG($this->slug_method, $this->get_debug_enabled(), $this->get_debug_request_enabled());
			$this->active_api = $this->get_api_key_active();
			$this->active_api_test = $this->get_api_key_active_test();
		}

		/**
		 * Send general request
		 * @param array Params query
		 * 
		 * @return mixed Answer
		 * */
		public function send_post_request($model, $method = 'POST', $params = array(), $add = '', $api_token_type = '') 
	    {
	    	# Get required URL
	        $url = $this->api_url . $model . $add;

	        $args = array(
	        	'timeout' => 30,
				'redirection' => 10,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array( 
					"Сontent-type" => "application/json, charset=utf-8",
					'Accept' => 'application/json',
		      		'Authorization' => 'Bearer ' . $this->get_production_bearer_ecom($api_token_type),
				),
				'cookies' => array(),
				'sslverify' => true,
	        );

	        if(!empty($params))
	        {
	        	$args['body'] = \wp_json_encode( $params );
	        	
	        	# Save to log
				$this->debug_log->add_data_request(\wp_json_encode( $params ));
	        }

	        if($method == 'POST')
	        {
	        	# Send request
				$response = wp_remote_post( $url, $args );
	        }
	        else
	        {
	        	# Send request
				$response = wp_remote_get( $url, $args );
	        }

			# Check answer
			if ( is_wp_error( $response ) ) 
			{
				# Get error
				$error_message = $response->get_error_message();

				# Save to log
				$this->debug_log->add_data($error_message);

				# Return error string
				return $error_message;
			} 
			else 
			{
				# Get body
				$body = wp_remote_retrieve_body( $response );

				# Decode json
				$obj = json_decode($body, true);

				# Return array
				return $obj;
			}
	    }

	    /**
		 * Send general request
		 * @param array Params query
		 * 
		 * @return mixed Answer
		 * */
		public function send_post_request_curl($model, $method = 'POST', $params = array(), $add = '', $api_token_type = '') 
	    {
	    	# Get required URL
	        $url = $this->api_url . $model;

	        if($add == 'token')
	        {
	        	$url .= '?token=' . $this->get_production_cp_token();
	        }

	        # Save to log
			$this->debug_log->add_data_request(\wp_json_encode( $params ));

		    $authorization = "Authorization: Bearer " . $this->get_production_bearer_ecom($api_token_type);

		    $header = array('Content-Type: application/json' , $authorization );

			if($add == 'tracking'){
				$header[] = "Tracking: Bearer " . $this->get_production_bearer_status_tracking();
			}

		    $ch = curl_init($url);

		    if($method == 'POST')
		    {
		    	curl_setopt($ch, CURLOPT_POSTFIELDS, \wp_json_encode( $params ));
		    }
		    else
		    {
		        curl_setopt($ch, CURLOPT_HEADER, 0);
		        curl_setopt($ch, CURLOPT_HTTPGET, 1);
		        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		    }
		    
		    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true);
		    curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
		    $response = curl_exec( $ch );
		    curl_close ( $ch );

		    $obj = json_decode($response, true);

			# Check answer
			if ( isset($obj['message']) ) 
			{
				# Save to log
				$this->debug_log->add_data($obj['message']);

				# Return error string
				return $obj['message'];
			} 
			else 
			{
				# Return array
				return $obj;
			}
	    }

	    /**
	     * Check api key correct
	     * @return mixed Api status
	     * */
		private function get_api_key_active()
	    {
	    	if(isset($this->settings_method['production_bearer_ecom']) && $this->settings_method['production_bearer_ecom'])
	    	{
	    		$obj = $this->send_post_request('address-classifier-ws/get_city_by_region_id_and_district_id_and_city_ua?region_id=&district_id=&city_ua=дні&fuzzy=1', 'GET', '', '', 'main');

		    	if(isset($obj['fault']))
		    	{
		    		if(isset($obj['fault']['description']))
		    		{
		    			$error_message = $obj['fault']['description'];
		    		}
		    		else
		    		{
		    			$error_message = __('PRODUCTION BEARER eCom incorrect', 'mrkv-ua-shipping');
		    		}

	    			# Save to log
					$this->debug_log->add_data($error_message);

					return __('Token incorrect', 'mrkv-ua-shipping');
		    	}
		    	else{
		    		return true;
		    	}
	    	}
	    	else
	    	{
	    		return false;
	    	}
	    }

	    /**
	     * Check api key correct
	     * @return mixed Api status
	     * */
		private function get_api_key_active_test()
	    {
	    	if(isset($this->settings_method['test_production_bearer_ecom']) && $this->settings_method['test_production_bearer_ecom'] && isset($this->settings_method['test_mode']) && $this->settings_method['test_mode'] == 'on')
	    	{
	    		$obj = $this->send_post_request('address-classifier-ws/get_city_by_region_id_and_district_id_and_city_ua?region_id=&district_id=&city_ua=дні&fuzzy=1', 'GET', '', '', 'test_mode');

		    	if(isset($obj['fault']))
		    	{
		    		if(isset($obj['fault']['description']))
		    		{
		    			$error_message = $obj['fault']['description'];
		    		}
		    		else
		    		{
		    			$error_message = __('PRODUCTION BEARER eCom incorrect', 'mrkv-ua-shipping');
		    		}

	    			# Save to log
					$this->debug_log->add_data($error_message);

					return __('Token incorrect', 'mrkv-ua-shipping');
		    	}
		    	else{
		    		return true;
		    	}
	    	}
	    	else
	    	{
	    		return false;
	    	}
	    }
	    

	    /**
	     * Get PRODUCTION BEARER eCom
	     * @return string PRODUCTION BEARER eCom
	     * */
	    public function get_production_bearer_ecom($api_token_type = '')
	    {
	    	if(isset($this->settings_method['test_mode']) && $this->settings_method['test_mode'] == 'on' && $api_token_type != 'main')
	    	{
	    		if(isset($this->settings_method['test_production_bearer_ecom']) && $this->settings_method['test_production_bearer_ecom'])
		    	{
		    		return $this->settings_method['test_production_bearer_ecom'];	
		    	}
	    	}
	    	else
	    	{
	    		if(isset($this->settings_method['production_bearer_ecom']) && $this->settings_method['production_bearer_ecom'])
		    	{
		    		return $this->settings_method['production_bearer_ecom'];	
		    	}
	    	}

	    	return '';
	    }

	    /**
	     * Get PRODUCTION BEARER StatusTracking
	     * @return string PRODUCTION BEARER StatusTracking
	     * */
	    public function get_production_bearer_status_tracking()
	    {
	    	if(isset($this->settings_method['test_mode']) && $this->settings_method['test_mode'] == 'on')
	    	{
	    		if(isset($this->settings_method['test_production_bearer_status_tracking']) && $this->settings_method['test_production_bearer_status_tracking'])
		    	{
		    		return $this->settings_method['test_production_bearer_status_tracking'];	
		    	}
	    	}
	    	else
	    	{
	    		if(isset($this->settings_method['production_bearer_status_tracking']) && $this->settings_method['production_bearer_status_tracking'])
		    	{
		    		return $this->settings_method['production_bearer_status_tracking'];	
		    	}
	    	}

	    	return '';
	    }

	    /**
	     * Get PROD_COUNTERPARTY TOKEN
	     * @return string PROD_COUNTERPARTY TOKEN
	     * */
	    public function get_production_cp_token()
	    {
	    	if(isset($this->settings_method['test_mode']) && $this->settings_method['test_mode'] == 'on')
	    	{
	    		if(isset($this->settings_method['test_production_cp_token']) && $this->settings_method['test_production_cp_token'])
		    	{
		    		return $this->settings_method['test_production_cp_token'];	
		    	}
	    	}
	    	else
	    	{
	    		if(isset($this->settings_method['production_cp_token']) && $this->settings_method['production_cp_token'])
		    	{
		    		return $this->settings_method['production_cp_token'];	
		    	}
	    	}

	    	return '';
	    }

	    /**
	     * Get Debug enabled
	     * @return boolean Debug
	     * */
	    public function get_debug_enabled()
	    {
	    	if(isset($this->settings_method['debug']['log']) && $this->settings_method['debug']['log'] == 'on')
	    	{
	    		return true;	
	    	}
	    	else
	    	{
	    		return false;	
	    	}
	    }

	    /**
	     * Get Debug request enabled
	     * @return boolean Debug
	     * */
	    public function get_debug_request_enabled()
	    {
	    	if(isset($this->settings_method['debug']['query']) && $this->settings_method['debug']['query'] == 'on')
	    	{
	    		return true;	
	    	}
	    	else
	    	{
	    		return false;	
	    	}
	    }

	    public function get_status_documents($invoice)
	    {
	    	if($this->get_production_bearer_ecom() && $this->get_production_bearer_status_tracking())
	    	{
	    		$obj = $this->send_post_request_curl('ecom/0.0.1/shipments/' . $invoice . '/lifecycle', 'GET', array(), 'token');

	    		if(isset($obj['status']) && $obj['status'])
	    		{
	    			return isset($obj) ? $obj : array();;
	    		}
	    	}
	    	
	    	# Return false
			return array();
	    }
	}
}