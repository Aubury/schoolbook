<?php
# Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 

# Check if class exist
if (!class_exists('MRKV_UA_SHIPPING_CALCULATE_NOVA_POSHTA'))
{
	/**
	 * Class for setup nova poshta api calculate
	 */
	class MRKV_UA_SHIPPING_CALCULATE_NOVA_POSHTA
	{
		/**
		 * @param object MRKV_UA_SHIPPING_API_NOVA_POSHTA
		 * */
		private $nova_poshta_api;

		/**
		 * Constructor for nova poshta api sender
		 * */
		function __construct($nova_poshta_api)
		{
			# Set fields
			$this->nova_poshta_api = $nova_poshta_api;
		}

		/**
		 * Get all sender contacts list
		 * @return 
		 * */
		public function calculate_shipping_cost($city_sender, $city_recipient, $weight, $service_type, $cost, $cargo_type) 
	    {
	    	# Set arguments
	        $args = array(
	            "apiKey" => $this->nova_poshta_api->get_api_key(),
	            "modelName" => "InternetDocumentGeneral",
	            "calledMethod" => "getDocumentPrice",
	            "methodProperties" => array(
	                "CitySender" => $city_sender,
	                "CityRecipient" => $city_recipient,
	                "Weight" => $weight,
	                "ServiceType" => $service_type,
	                "Cost" => $cost,
	                "CargoType" => $cargo_type,
	                "SeatsAmount" => "1"
	            ),
	        );

	        # Send request
	        $obj = $this->nova_poshta_api->send_post_request( $args );

	        if(isset($obj['data'][0]))
	        {
	        	# Return object
	        	return $obj['data'][0];
	        }
	        else
	        {
	        	return '';
	        }
	    }
	}
}