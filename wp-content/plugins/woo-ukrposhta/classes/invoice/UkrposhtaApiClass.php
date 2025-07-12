<?php

namespace deliveryplugin\Ukrposhta\classes\invoice;

class UkrposhtaApiClass
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
    private function request($model, $method = 'HTTPGET', $params = NULL, $add = '')
    {
        /* Get required URL */
        $url = $this->url . 'ecom' . $this->apiVersion . $model . $add;

        /* Convert data to necessary format */
        $post = wp_json_encode($params);

        // Set the headers for the request
        $headers = array(
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $this->bearer,
        );

        // Set the arguments for the request
        $args = array(
            'method'    => $method, // HTTP method, e.g., GET, POST, PUT, etc.
            'body'      => $post, // The data to be sent in the request body (only for POST/PUT)
            'headers'   => $headers, // Set the headers
            'timeout'   => $this->responseTime, // Set the timeout
            'sslverify' => false, // Disable SSL verification (set to true in production)
        );

        // Send the request using wp_remote_request
        $response = wp_remote_request($url, $args);

        // Check if there was an error with the request
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            // Handle the error (e.g., log it, show a message)
        } else {
            // Retrieve the response code
            $http_code = wp_remote_retrieve_response_code($response);

            // Retrieve the body of the response
            $result = wp_remote_retrieve_body($response);

            // Handle the specific error codes
            if ($http_code == 401) {
                $this->httpCode401 = '<br>Error 401: Invalid Credentials Access failure for API. <br>Така помилка виникає у випадку використання некоректного token' . '<br>Request: ' . $url . ' Time: ' . $response['response']['total_time'];
            }

            if ($http_code == 403) {
                $this->httpCode403 = '<br>Error 403: Invalid Credentials Access failure for API. Введено неправильний bearer.' . '<br>Request: ' . $url . ' Time: ' . $response['response']['total_time'];
            }

            if ($http_code == 404) {
                $this->httpCode404 = '<br>Error 404: Data was not found in the Ukrposhta database. <br>Така помилка може виникати, якщо об’єкт було створено в SandBox або особистому кабінеті, або взагалі не було створено.' . '<br>Request: ' . $url . ' Time: ' . $response['response']['total_time'];
            }

            // Return the prepared result
            return $this->prepare($result);
        }
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
    private function requestToken($model, $method = 'HTTPGET', $params = NULL, $add = '', $file = false)
    {
        /* Get required URL */
        $url = $this->url . 'ecom' . $this->apiVersion . $model . $add . '?token=' . $this->token;

        /* Convert data to necessary format */
        $post = wp_json_encode($params);

        // Set the headers for the request
        $headers = array(
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $this->bearer,
        );

        // Set the arguments for the request
        $args = array(
            'method'    => $method, // HTTP method, e.g., GET, POST, PUT, etc.
            'body'      => $post, // The data to be sent in the request body (only for POST/PUT)
            'headers'   => $headers, // Set the headers
            'timeout'   => $this->responseTime, // Set the timeout
            'sslverify' => false, // Disable SSL verification (set to true in production)
        );

        // Send the request using wp_remote_request
        $response = wp_remote_request($url, $args);

        // Check if there was an error with the request
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            // Handle the error (e.g., log it, show a message)
        } else {
            // Retrieve the response code
            $http_code = wp_remote_retrieve_response_code($response);

            // Retrieve the body of the response
            $result = wp_remote_retrieve_body($response);

            if ($file) {
                // Handle file download using WP_Filesystem
                global $wp_filesystem;

                // Initialize the filesystem API
                if (false === ($creds = request_filesystem_credentials('', '', false, false, null))) {
                    // Handle credentials failure
                    return false;
                }

                if (!WP_Filesystem($creds)) {
                    // Handle filesystem initialization failure
                    return false;
                }

                // Define the path to save the file
                $upload_dir = wp_upload_dir();
                $downloadPath = trailingslashit($upload_dir['path']) . 'flower10.jpg';

                // Write the file using WP_Filesystem
                if ($wp_filesystem->put_contents($downloadPath, $result, FS_CHMOD_FILE)) {
                    return $result;
                } else {
                    // Handle file write failure
                    return false;
                }
            } else {
                // Process the response
                return $this->prepare($result);
            }
        }
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
        /* Get required URL */
        $url = $this->url . 'ecom' . $this->apiVersion . $model . $add . '?token=' . $this->token;

        /* Convert data to necessary format */
        $post = wp_json_encode($params);

        // Set the headers for the request
        $headers = array(
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $this->bearer,
        );

        // Set the arguments for the PUT request
        $args = array(
            'method'    => 'PUT', // HTTP method for the request
            'body'      => $post, // The data to be sent in the request body
            'headers'   => $headers, // Set the headers
            'timeout'   => $this->responseTime, // Set the timeout
            'sslverify' => false, // Disable SSL verification (set to true in production)
        );

        // Send the PUT request using wp_remote_request
        $response = wp_remote_request($url, $args);

        // Check if there was an error with the request
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            // Handle the error (e.g., log it, show a message)
        } else {
            // Retrieve the response code
            $http_code = wp_remote_retrieve_response_code($response);

            // Retrieve the body of the response
            $result = wp_remote_retrieve_body($response);

            // Prepare the result
            $ret = $this->prepare($result);

            // Decode the result JSON
            $rr = json_decode($result);
            if (!empty($rr->message)) {
                // Handle any specific message
            }

            return $ret;
        }
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
        /* Get required URL */
        $url = $this->url . 'status-tracking' . $this->apiVersion . $model . $add;

        /* Convert data to necessary format */
        $post = wp_json_encode($params);

        // Set the headers for the request
        $headers = array(
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $this->bearer,
            'Tracking'      => 'Bearer ' . $this->tbearer
        );

        // Set the arguments for the GET request
        $args = array(
            'method'    => 'GET', // HTTP method for the request
            'headers'   => $headers, // Set the headers
            'timeout'   => $this->responseTime, // Set the timeout
            'sslverify' => false, // Disable SSL verification (set to true in production)
        );

        // Send the GET request using wp_remote_get
        $response = wp_remote_get($url, $args);

        // Check if there was an error with the request
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            // Handle the error (e.g., log it, show a message)
        } else {
            // Retrieve the response code
            $http_code = wp_remote_retrieve_response_code($response);

            // Retrieve the body of the response
            $result = wp_remote_retrieve_body($response);

            // Return the result after preparing it
            return $this->prepare($result);
        }
    }

    public function RequestDelShipping($id)
    {
        // Construct the URL for the request
        $url = $this->url . 'ecom' . $this->apiVersion . 'shipments/' . $id . '?token=' . $this->token;

        // Set the headers for the request
        $headers = array(
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $this->bearer,
            'Tracking'      => 'Bearer ' . $this->tbearer
        );

        // Set the arguments for the DELETE request
        $args = array(
            'method'    => 'DELETE', // HTTP method for the request
            'headers'   => $headers, // Set the headers
            'timeout'   => 15,       // Timeout in seconds
            'sslverify' => false,    // Disable SSL verification (set to true in production)
        );

        // Send the DELETE request using wp_remote_request
        $response = wp_remote_request($url, $args);

        // Check if there was an error with the request
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            // Handle the error (e.g., log it, show a message)
        } else {
            // Retrieve the response code
            $http_code = wp_remote_retrieve_response_code($response);

            // Retrieve the body of the response
            $result = wp_remote_retrieve_body($response);

            // Return the result
            return $result;
        }
    }

    public function GetInfo($id)
    {
        // Construct the URL for the request
        $url = 'https://www.ukrposhta.ua/ecom/0.0.1/shipments/barcode/' . $id . '?token=' . $this->token;

        // Authorization header with Bearer token
        $authorization = "Bearer " . $this->bearer;

        // Set the arguments for the GET request
        $args = array(
            'method'    => 'GET', // HTTP method for the request
            'headers'   => array(
                'Content-Type'  => 'application/json', // Set content type to JSON
                'Authorization' => $authorization,     // Inject the token into the header
            ),
            'timeout'   => 15, // Timeout in seconds for the request
            'sslverify' => false, // Disable SSL verification (set to true in production)
        );

        // Send the GET request using wp_remote_get
        $response = wp_remote_get($url, $args);

        // Check if there was an error with the request
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            // Handle the error (e.g., log it, show a message)
        } else {
            // Retrieve the body of the response
            $html = wp_remote_retrieve_body($response);

            // Decode the JSON response
            return json_decode($html, true);
        }
    }

    public function GetInfoUuid($uuid)
    {
        // Construct the URL for the request
        $url = 'https://www.ukrposhta.ua/ecom/0.0.1/shipments/' . $uuid . '?token=' . $this->token;

        // Authorization header with Bearer token
        $authorization = "Bearer " . $this->bearer;

        // Set the arguments for the GET request
        $args = array(
            'method'    => 'GET', // HTTP method for the request
            'headers'   => array(
                'Content-Type'  => 'application/json', // Set content type to JSON
                'Authorization' => $authorization,     // Inject the token into the header
            ),
            'timeout'   => 15, // Timeout in seconds for the request
            'sslverify' => false, // Disable SSL verification (set to true in production)
        );

        // Send the GET request using wp_remote_get
        $response = wp_remote_get($url, $args);

        // Check if there was an error with the request
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            // Handle the error (e.g., log it, show a message)
        } else {
            // Retrieve the body of the response
            $html = wp_remote_retrieve_body($response);

            // Decode the JSON response
            return json_decode($html, true);
        }
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

    public function howcosts( $params )
    {
        // Encode the parameters to JSON format
        $post = wp_json_encode($params);

        // URL to send the request
        $url = 'https://www.ukrposhta.ua/ecom/0.0.1/international/delivery-price';

        // Authorization header with Bearer token
        $authorization = "Bearer " . $this->getBearer();

        // Set the arguments for the POST request
        $args = array(
            'method'    => 'POST', // HTTP method for the request
            'body'      => $post,   // JSON-encoded parameters
            'headers'   => array(
                'Content-Type'  => 'application/json', // Set content type to JSON
                'Authorization' => $authorization,     // Inject the token into the header
            ),
            'timeout'   => 15, // Timeout in seconds for the request
            'sslverify' => false, // Disable SSL verification (set to true in production)
        );

        // Send the POST request using wp_remote_post
        $response = wp_remote_post($url, $args);

        // Check if there was an error with the request
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            // Handle the error (e.g., log it, show a message)
        } else {
            // Retrieve the body of the response
            $html = wp_remote_retrieve_body($response);

            // Decode the JSON response
            return json_decode($html, true);
        }
    }

    public function howcostsua( $params )
    {
        $post = wp_json_encode($params);
        $url = 'https://www.ukrposhta.ua/ecom/0.0.1/domestic/delivery-price';
        $authorization = "Bearer ".$this->getBearer();
      
        $args = array(
            'method'    => 'POST',
            'body'      => $post,
            'headers'   => array(
                'Content-Type'  => 'application/json',
                'Authorization' => $authorization,
            ),
            'timeout'   => 15, 
            'sslverify' => false, 
        );

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            return '';
        } else {
            $html = wp_remote_retrieve_body($response);
            return  json_decode($html, true);
        }
    }

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
