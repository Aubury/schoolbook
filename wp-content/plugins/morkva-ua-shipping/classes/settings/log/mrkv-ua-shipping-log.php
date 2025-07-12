<?php
# Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 

# Check if class exist
if (!class_exists('MRKV_UA_SHIPPING_LOG'))
{
	/**
	 * Class for setup dedug log
	 */
	class MRKV_UA_SHIPPING_LOG
	{
		/**
		 * @var string Slug
		 * */
		private $method_slug;

		/**
		 * @var string Datetime
		 * */
		private $datetime_log;

		/**
		 * @var string Is active log
		 * */
		private $active_log;

		/**
		 * @var string Is active log request
		 * */
		private $active_log_request;

		/**
		 * Constructor for dedug log
		 * */
		function __construct($method_slug, $active_log = false, $active_log_request = false)
		{
			# Set variable
			$this->method_slug = $method_slug;
			$this->datetime_log = gmdate("Y-m-d h:i:sa");
			$this->active_log = $active_log;
			$this->active_log_request = $active_log_request;

			# Change Timezone
			date_default_timezone_set("Europe/Kiev");
		}

		/**
		 * @var string Error
		 * */
		public function add_data($error)
		{
			if($this->active_log)
			{
				# Generate error message
				$error_message = "[error] [" . $this->datetime_log . '] ' . print_r($error, 1) . "\r\n";

				# Write text to degug.log file
				file_put_contents( MRKV_UA_SHIPPING_PLUGIN_PATH . 'logs/' . $this->method_slug . '/debug-' . $this->method_slug . '.log', $error_message, FILE_APPEND );
			}
		}

		/**
		 * @var string Request
		 * */
		public function add_data_request($request)
		{
			if($this->active_log_request)
			{
				# Generate request message
				$request_message = "[request] [" . $this->datetime_log . '] ' . print_r($request, 1) . "\r\n";

				# Write text to degug.log file
				file_put_contents( MRKV_UA_SHIPPING_PLUGIN_PATH . 'logs/' . $this->method_slug . '/debug-' . $this->method_slug . '.log', $request_message, FILE_APPEND );
			}
		}
	}
}