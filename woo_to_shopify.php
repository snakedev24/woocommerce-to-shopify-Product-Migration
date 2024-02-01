<?php
// API endpoint URL
$url = 'https://localhost/wordpress_23jan/wp-json/wc/v3/products';

// Consumer Key and Consumer Secret
$consumer_key = 'ck_1b8dac952f5fd06c1dad13a70460f0dccd838344';
$consumer_secret = 'cs_b0a51cb6b59d7207b1eff518b34b72939407c6da';

// Set parameters for fetching all products
$params = array(
    'per_page' => 100, // Set per_page parameter to a high number to retrieve all products
);

// Append parameters to the URL
$url .= '?' . http_build_query($params);

// Initialize cURL session
$curl = curl_init();

// Set cURL options
curl_setopt($curl, CURLOPT_URL, $url); // Set the URL with parameters
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // Return response as a string
curl_setopt($curl, CURLOPT_USERPWD, $consumer_key . ':' . $consumer_secret); // Set the authentication credentials
curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json')); // Set content type and accept JSON

// Disable SSL verification (not recommended for production)
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

// Execute cURL request
$response = curl_exec($curl);

// Check for errors
if($response === false) {
    echo 'cURL error: ' . curl_error($curl);
} else {
    // Get HTTP status code
    $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
    // Print the response
    if ($http_status == 401) {
        echo 'Authentication Error: ' . $response;
    } else {
        // Output JSON response
        header('Content-Type: application/json');
        echo $response;
    }
}

// Close cURL session
curl_close($curl);
?>
