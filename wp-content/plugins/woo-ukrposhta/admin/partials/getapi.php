<?php

function get_info($token, $url) {
    // Set the headers for authorization
    $args = array(
        'method'    => 'GET', // HTTP method
        'headers'   => array(
            'Content-Type'  => 'application/json', // Set the content type
            'Authorization' => 'Bearer ' . $token, // Include the token in the authorization header
        ),
        'sslverify' => false, // Disable SSL verification (not recommended in production)
        'timeout'   => 20,    // Set a timeout for the request
    );

    // Execute the HTTP GET request
    $response = wp_remote_get($url, $args);

    // Check for errors in the request
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        // Handle the error (e.g., log it or return an error message)
        return 'Error: ' . $error_message;
    }

    // Retrieve the response body
    $result = wp_remote_retrieve_body($response);

    // Decode the JSON response
    $jd = json_decode($result);

    // Check if the response contains an error with code 1020
    if (isset($jd->errors->code) && $jd->errors->code == 1020) {
        autorization(); // Call the authorization function
        static $retry_count = 0; // Static variable to limit retry attempts
        $retry_count++;
        if ($retry_count < 2) { // Retry only once
            return get_info($token, $url);
        }
    }

    // Return the result
    return $result;
}

header("Content-type: application/pdf");
header("Content-Disposition: inline; filename=" . $file_name);

// Decide which URL to call based on the presence of the 'international' GET parameter
echo esc_html(get_info(
    "1a14715b-4341-3b36-8130-e439b493773e",
    "https://www.ukrposhta.ua/ecom/0.0.1/doc"
));

?>
