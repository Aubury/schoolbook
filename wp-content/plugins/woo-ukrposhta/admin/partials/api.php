<?php

class UkrposhtaApi
{
    protected $bearer;
    protected $tbearer;
    protected $token;
    protected $throwErrors = TRUE;
    /**
     * @var string $format Format of returned data - array
     */
    protected $format = 'array';
    /**
     * @var string $url Link to ukrposhtaApi
     */
    protected $url = 'https://www.ukrposhta.ua/';
    /**
     * @var string $apiVersion version for url
     */
    protected $apiVersion = '/0.0.1/';
    /**
     * @var string $responseTime waiting for response from server, sec.
     */
    protected $responseTime = '30';
    /**Default constructor
     * UkrposhtaApi constructor.
     * @param $bearer
     * @param bool $token
     * @param bool $throwErrors
     */
    public $httpCode401 = '';
    public $httpCode403 = '';
    public $httpCode404 = '';

    function __construct($bearer, $token = FALSE, $tbearer = FALSE, $throwErrors = FALSE)
    {
        $this->throwErrors = $throwErrors;
        return $this
            ->setBearer($bearer)
            ->setToken($token)
            ->setTbearer($tbearer);
    }
    /**Setter for bearer property
     * @param $bearer
     * @return $this
     */
    public function setBearer($bearer)
    {
        $this->bearer = $bearer;
        return $this;
    }

    public function setTbearer($tbearer)
    {
        $this->$tbearer = $tbearer;
        return $this;
    }
    /**Getter for bearer property
     * @return string
     */
    public function getBearer()
    {
        return $this->bearer;
    }

    public function getTbearer()
    {
        return $this->$tbearer;
    }
    /**Setter for token property
     * @param $token
     * @return $this
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }
    /**Getter for token property
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }
    /**Setter for format property
     * @param $format
     * @return $this
     */
    public function setFormat($format)
    {
        $this->format = $format;
        return $this;
    }
    /**Getter for format property
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }
    /**Setter for property responseTime
     * @param $responseTime
     * @return $this
     */
    public function setResponseTime($responseTime)
    {
        if (is_numeric($responseTime)) $this->responseTime = $responseTime;
        return $this;
    }
    /**Getter for property responceTime
     * @return string
     */
    public function getResponseTime()
    {
        return $this->responseTime;
    }
    /**Prepare data before return
     * @param $data
     * @return array|mixed
     */
    private function prepare($data)
    {
        //Returns array
        if ($this->format == 'array') {
            $result = is_array($data) ? $data : json_decode($data, 1);
            return $result;
        }
        // Returns json or raw data
        return $data;
    }
    /**Request function for model Adress
     * @param $model
     * @param string $method
     * @param null $params
     * @param string $add
     * @return array|mixed
     * @throws \Exception
     */
    private function request($model, $method = 'GET', $params = NULL, $add = '')
    {
        // Get the required URL
        $url = $this->url . 'ecom' . $this->apiVersion . $model . $add;

        // Convert data to the necessary format (if applicable)
        $post = wp_json_encode($params);

        // Set the headers for the request
        $headers = array(
            'Content-Type'  => 'application/json',  // Content type is JSON
            'Authorization' => 'Bearer ' . $this->bearer, // Bearer token for authorization
        );

        // Prepare arguments for the request
        $args = array(
            'method'    => $method,               // HTTP method (GET/POST/etc.)
            'headers'   => $headers,              // Include headers
            'body'      => $post,                 // Include body if applicable (POST/PUT)
            'timeout'   => $this->responseTime,   // Set timeout for the request
            'sslverify' => false,                 // Disable SSL verification (should be enabled in production)
        );

        // Perform the request using wp_remote_request
        $response = wp_remote_request($url, $args);

        // Check for errors in the request
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            if ($this->throwErrors) {
                throw new \Exception(esc_html($error_message)); // Throw exception if configured to do so
            }
            return null; // Return null if there's an error and errors are not thrown
        }

        // Retrieve the response body
        $result = wp_remote_retrieve_body($response);

        // Handle HTTP response codes
        $http_code = wp_remote_retrieve_response_code($response);

        if ($http_code == 401) {
            $this->httpCode401 = '<br>Error 401: Invalid Credentials Access failure for API. <br>Така помилка виникає у випадку використання некоректного token' . '<br>Request: ' . $url . ' Time: ' . wp_remote_retrieve_header($response, 'X-Response-Time');
        }

        if ($http_code == 403) {
            $this->httpCode403 = '<br>Error 403: Invalid Credentials Access failure for API. Введено неправильний bearer.' . '<br>Request: ' . $url . ' Time: ' . wp_remote_retrieve_header($response, 'X-Response-Time');
        }

        if ($http_code == 404) {
            $this->httpCode404 = '<br>Error 404: Data was not found in the Ukrposhta database. <br>Така помилка може виникати, якщо об’єкт було створено в SandBox або особистому кабінеті, або взагалі не було створено.' . '<br>Request: ' . $url . ' Time: ' . wp_remote_retrieve_header($response, 'X-Response-Time');
        }

        return $this->prepare($result);
    }
    /**Request for model client, smartbox, print with token
     * @param $model
     * @param string $method
     * @param null $params
     * @param string $add
     * @param bool $file
     * @return array|mixed
     * @throws \Exception
     */
    private function requestToken($model, $method = 'GET', $params = NULL, $add = '', $file = false)
    {
        // Get the required URL
        $url = $this->url . 'ecom' . $this->apiVersion . $model . $add . '?token=' . $this->token;

        // Convert data to the necessary format (if applicable)
        $post = wp_json_encode($params);

        // Set the headers for the request
        $headers = array(
            'Content-Type'  => 'application/json',  // Content type is JSON
            'Authorization' => 'Bearer ' . $this->bearer, // Bearer token for authorization
        );

        // Prepare arguments for the request
        $args = array(
            'method'    => $method,               // HTTP method (GET/POST/etc.)
            'headers'   => $headers,              // Include headers
            'body'      => $post,                 // Include body if applicable (POST/PUT)
            'timeout'   => $this->responseTime,   // Set timeout for the request
            'sslverify' => false,                 // Disable SSL verification (should be enabled in production)
        );

        // Perform the request using wp_remote_request
        $response = wp_remote_request($url, $args);

        // Check for errors in the request
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            if ($this->throwErrors) {
                throw new \Exception(esc_html($error_message)); // Throw exception if configured to do so
            }
            return null; // Return null if there's an error and errors are not thrown
        }

        // Retrieve the response body
        $result = wp_remote_retrieve_body($response);

        // If the response is a file, save it to the server
        if ($file) {
            // Ensure that WP_Filesystem is initialized
            if (empty($wp_filesystem)) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
                WP_Filesystem();
            }

            // Set the download path
            $downloadPath = WP_CONTENT_DIR . '/uploads/flower10.jpg'; // Adjust path as needed

            // Write the result to the file using WP_Filesystem
            $wp_filesystem->put_contents($downloadPath, $result, FS_CHMOD_FILE);

            // Return the file path
            return $downloadPath;
        }

        // Process and return the result using the prepare method
        return $this->prepare($result);
    }


    /**Similar function to requestToken, but only for PUT request
     * @param $model
     * @param null $params
     * @param string $add
     * @return array|mixed
     * @throws \Exception
     */


    private function requestTokenPut($model, $params = NULL, $add = '')
    {
        // Get the required URL
        $url = $this->url . 'ecom' . $this->apiVersion . $model . $add . '?token=' . $this->token;

        // Convert data to the necessary format
        $post = wp_json_encode($params);

        // Set the headers for the request
        $headers = array(
            'Content-Type'  => 'application/json', // Content type is JSON
            'Authorization' => 'Bearer ' . $this->bearer, // Bearer token for authorization
        );

        // Prepare the arguments for the PUT request
        $args = array(
            'method'    => 'PUT',               // HTTP method: PUT
            'headers'   => $headers,            // Include headers
            'body'      => $post,               // Include the post body with the parameters
            'timeout'   => $this->responseTime, // Set timeout for the request
            'sslverify' => false,               // Disable SSL verification (risky, should be enabled in production)
        );

        // Perform the PUT request using wp_remote_request
        $response = wp_remote_request($url, $args);

        // Check for errors in the request
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            if ($this->throwErrors) {
                throw new \Exception(esc_html($error_message)); // Throw exception if configured to do so
            }
            return null; // Return null if there's an error and errors are not thrown
        }

        // Retrieve the response body
        $result = wp_remote_retrieve_body($response);

        // Process and return the result using the prepare method
        $ret = $this->prepare($result);

        // Decode the JSON response
        $rr = json_decode($result);
        if (!empty($rr->message)) {
            // Handle the response message if necessary
        }

        return $ret;
    }
    /**Request token for tracking barcode
     * @param $model
     * @param null $params
     * @param string $add
     * @return array|mixed
     * @throws \Exception
     */
    private function requestTracking($model, $params = NULL, $add = '')
    {
        // Get required URL
        $url = $this->url . 'status-tracking' . $this->apiVersion . $model . $add;

        // Convert data to the necessary format
        $query_string = '';
        if (!empty($params)) {
            $query_string = '?' . http_build_query($params); // Append query parameters if any
        }
        $full_url = $url . $query_string;

        // Set headers for the request
        $headers = array(
            'Content-Type'  => 'application/json', // Set content type to JSON
            'Authorization' => 'Bearer ' . $this->bearer, // Bearer token for authorization
            'Tracking'      => 'Bearer ' . $this->tbearer, // Tracking Bearer token
        );

        // Prepare the arguments for the HTTP GET request
        $args = array(
            'method'  => 'GET',             // HTTP method: GET
            'headers' => $headers,          // Include headers
            'timeout' => $this->responseTime, // Set timeout for the request
            'sslverify' => false,            // Disable SSL verification (can be risky, consider enabling in production)
        );

        // Perform the GET request using wp_remote_get
        $response = wp_remote_get($full_url, $args);

        // Check for errors in the request
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            if ($this->throwErrors) {
                throw new \Exception(esc_html($error_message)); // Throw exception if configured to do so
            }
            return null; // Return null if there's an error and errors are not thrown
        }

        // Retrieve the response body
        $result = wp_remote_retrieve_body($response);

        // Process and return the result using the prepare method
        return $this->prepare($result);
    }

    public function RequestDelShipping($id)
    {
        // Define the URL for the DELETE request, appending the shipment ID and token as query parameters
        $url = $this->url . 'ecom' . $this->apiVersion . 'shipments/' . $id . '?token=' . $this->token;

        // Prepare the headers, including both the Bearer token for authorization and the Tracking Bearer token
        $headers = array(
            'Content-Type'  => 'application/json',  // Set the content type to JSON
            'Authorization' => 'Bearer ' . $this->bearer,  // Bearer token for authorization
            'Tracking'      => 'Bearer ' . $this->tbearer, // Additional Tracking Bearer token
        );

        // Set up the arguments for the HTTP DELETE request
        $args = array(
            'method'  => 'DELETE',      // Use the HTTP DELETE method
            'headers' => $headers,      // Include the authorization headers
            'timeout' => 20,            // Set a timeout for the request
        );

        // Perform the DELETE request using wp_remote_request()
        $response = wp_remote_request($url, $args);

        // Check for errors in the request
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            // Handle the error (e.g., log it or return an error message)
            return array('error' => 'Request failed: ' . $error_message);
        }

        // Retrieve the response body from the DELETE request
        $result = wp_remote_retrieve_body($response);

        // Return the result
        return $result;
    }

      public function GetInfo($id) {
        // Define the API endpoint URL, appending the shipment barcode ID and token as query parameters
        $url = 'https://www.ukrposhta.ua/ecom/0.0.1/shipments/barcode/' . $id . '?token=' . $this->token;

        // Prepare the authorization headers with the Bearer token
        $headers = array(
            'Content-Type'  => 'application/json',  // Set the content type to JSON
            'Authorization' => 'Bearer ' . $this->bearer,  // Add the Bearer token for authorization
        );

        // Set up the arguments for the HTTP GET request
        $args = array(
            'headers' => $headers,  // Include the headers with the authorization token
            'method'  => 'GET',     // Use the HTTP GET method
            'timeout' => 20,         // Set a timeout for the request
        );

        // Perform the HTTP GET request using WordPress's wp_remote_get()
        $response = wp_remote_get($url, $args);

        // Check for errors in the request
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            // Handle the error (e.g., log it or return an error message)
            return array('error' => 'Request failed: ' . $error_message);
        }

        // Retrieve the response body from the HTTP GET request
        $html = wp_remote_retrieve_body($response);

        // Decode the JSON response and return it as an associative array
        return json_decode($html, true);
    }

      public function GetInfoUuid($uuid) {
        // Define the API endpoint URL, appending the UUID and token as query parameters
        $url = 'https://www.ukrposhta.ua/ecom/0.0.1/shipments/' . $uuid . '?token=' . $this->token;

        // Prepare the authorization headers with the Bearer token
        $headers = array(
            'Content-Type'  => 'application/json',  // Set the content type to JSON
            'Authorization' => 'Bearer ' . $this->bearer,  // Add the Bearer token for authorization
        );

        // Set up the arguments for the HTTP GET request
        $args = array(
            'headers' => $headers,  // Include the headers with authorization token
            'method'  => 'GET',     // Use the HTTP GET method
            'timeout' => 20,         // Set a timeout for the request
        );

        // Perform the HTTP GET request using WordPress's wp_remote_get()
        $response = wp_remote_get($url, $args);

        // Check for errors in the request
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            // Handle the error (e.g., log it or return an error message)
            return array('error' => 'Request failed: ' . $error_message);
        }

        // Retrieve the response body from the HTTP GET request
        $html = wp_remote_retrieve_body($response);

        // Decode the JSON response and return it as an associative array
        return json_decode($html, true);
    }



    /**Get created address by id
     * @param $id int
     * @return array|mixed
     */
    public function modelAdressGet($id)
    {
        return $this->request('addresses', 'HTTPGET', NULL, '/' . $id);
    }
    /**Create address. For example:
     * @param $data array
     * @return array|mixed
     */
    public function modelAdressPost($data)
    {
        return $this->request('addresses', 'POST', $data);
    }
    /**Creating new client
     * @param $data array
     * @return array|mixed
     */
    public function modelClientsPost($data)
    {
        return $this->requestToken('clients', 'POST', $data);
    }
    /**Change data to existing client
     * @param $id int
     * @param $data array
     * @return array|mixed
     */
    public function modelClientsPut($id, $data)
    {
        return $this->requestToken('clients', 'PUT', $data, '/' . $id);
    }
    /**Get created clients by external-id
     * @param $id int
     * @return array|mixed
     */
    public function modelClientsGet($id)
    {
        return $this->requestToken('clients', 'HTTPGET', NULL, '/external-id/' . $id);
    }
    /**Creating shipment
     * @param $data array
     * @return array|mixed
     */
    public function modelShipmentsPost($data)
    {
        return $this->requestToken('shipments', 'POST', $data);
    }

    public function howcosts( $params ) {

        // Convert the input parameters to JSON format
        $post = wp_json_encode($params);

        // Define the API endpoint URL
        $url = 'https://www.ukrposhta.ua/ecom/0.0.1/international/delivery-price';

        // Prepare the headers, including the authorization token
        $headers = array(
            'Content-Type'  => 'application/json',  // Set the content type to JSON
            'Authorization' => 'Bearer ' . $this->getBearer(),  // Include the Bearer token for authorization
        );

        // Set up the arguments for the HTTP request
        $args = array(
            'body'    => $post,       // Include the JSON payload
            'headers' => $headers,    // Include the headers with authorization token
            'method'  => 'POST',      // Specify the HTTP method (POST)
            'timeout' => 20,          // Set a timeout for the request (in seconds)
        );

        // Perform the HTTP POST request using WordPress functions
        $response = wp_remote_post($url, $args);

        // Check if there was an error with the request
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            // Handle the error (you can log it or return an error message)
            return array('error' => 'Request failed: ' . $error_message);
        }

        // Retrieve the response body (the actual result of the API request)
        $html = wp_remote_retrieve_body($response);

        // Decode the JSON response and return it as an associative array
        return json_decode($html, true);
    }

    public function howcostsua( $params ) {
        // Convert parameters to JSON format
        $post = wp_json_encode($params);

        // Set the API endpoint URL
        $url = 'https://www.ukrposhta.ua/ecom/0.0.1/domestic/delivery-price';

        // Prepare the headers, including the authorization token
        $headers = array(
            'Content-Type'  => 'application/json', // Specify JSON content type
            'Authorization' => 'Bearer ' . $this->getBearer(), // Add the authorization token
        );

        // Set up the request arguments
        $args = array(
            'body'    => $post,       // Include the JSON payload
            'headers' => $headers,    // Include the headers
            'method'  => 'POST',      // Specify the HTTP method
            'timeout' => 20,          // Set a timeout for the request
        );

        // Make the HTTP POST request using WordPress functions
        $response = wp_remote_post($url, $args);

        // Check for errors in the response
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            // Handle the error, e.g., log it or return an error message
            return array('error' => 'Request failed: ' . $error_message);
        }

        // Retrieve the response body
        $html = wp_remote_retrieve_body($response);

        // Decode the JSON response into an array
        return json_decode($html, true);
    }

//function requestToken($model, $method = 'HTTPGET', $params = NULL, $add = '', $file = false)
//requestTokenPut($model, $params = NULL, $add = '')

    public function modelShipmentsPut($data, $uuid)
    {

      $th = $this->requestTokenPut('shipments/'.$uuid, $data);
      return $th;
    }


        public function modelShipmentsPutInter($data, $uuid)
        {

          logg('tut1');
          $th = $this->requestTokenPut('shipment-groups/'.$uuid, $data);
          return $th;
        }
    /**Get file for print
     * @param $id string
     * @return array|mixed
     */
    public function modelPrint($id)
    {



        return $this->requestToken('shipments', 'HTTPGET', NULL, '/' . $id . '/label', TRUE);


    }
    /**Request for use smartbox
     * @param $smartboxcode string
     * @param $clientuuid string
     * @return array|mixed
     */
    public function modelSmartBoxPost($smartboxcode, $clientuuid)
    {
        return $this->requestToken('smart-boxes', 'POST', NULL, '/' . $smartboxcode . '/use-with-sender/' . $clientuuid);
    }
    /**Initialization smartbox shipment
     * @param $smartboxcode string
     * @return array|mixed
     */
    public function modelSmartBoxGet($smartboxcode)
    {
        return $this->requestToken('smart-boxes', 'HTTPGET', NULL, '/' . $smartboxcode . '/shipments/next');
    }
    /**Creating smartbox shipment
     * @param $smartboxshipmentuuid string
     * @param $data array
     * @return array|mixed
     */
    public function modelSmartBoxPut($smartboxshipmentuuid, $data)
    {
        return $this->requestTokenPut('shipments', $data, '/' . $smartboxshipmentuuid);
    }
    /**Getting last status of barcode
     * @param $barcode string
     * @return array|mixed
     */
    public function modelStatuses($barcode)
    {
        return $this->requestTracking('statuses/last', null, '?barcode=' . $barcode);
    }
}
