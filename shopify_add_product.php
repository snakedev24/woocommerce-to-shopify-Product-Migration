<?php
// Shopify API endpoint URL for adding a product
$url = 'https://amittes.myshopify.com/admin/api/2024-01/products.json';

// Access Token
$access_token = 'shpua_c5fe00c7fd72bafd0f395aac002e6e30';

// Product data
$product_data = array(
    'product' => array(
        'title' => 'Burton Custom Freestyle 151',
        'body_html' => '<strong>Good snowboard!</strong>',
        'vendor' => 'Burton',
        'product_type' => 'Snowboard',
        'images' => array(
            array(
                'attachment' => 'R0lGODlhAQABAIAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=='
            )
        )
    )
);

// Initialize cURL session to add product
$curl_add_product = curl_init();

// Set cURL options to add product
curl_setopt_array($curl_add_product, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($product_data),
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'X-Shopify-Access-Token: ' . $access_token
    )
));

// Execute cURL request to add product
$response = curl_exec($curl_add_product);

// Check for errors
if($response === false) {
    echo 'cURL error: ' . curl_error($curl_add_product);
} else {
    // Get HTTP status code
    $http_status = curl_getinfo($curl_add_product, CURLINFO_HTTP_CODE);
    
    // Print the response
    if ($http_status == 401) {
        echo 'Authentication Error: ' . $response;
    } else {
        // Output JSON response
        header('Content-Type: application/json');
        echo $response;
    }
}

// Close cURL session for adding product
curl_close($curl_add_product);
?>
