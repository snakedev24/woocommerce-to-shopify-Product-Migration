<?php
// WooCommerce API endpoint URL
$woocommerce_url = 'https://localhost/wordpress_23jan/wp-json/wc/v3/products';

// WooCommerce Consumer Key and Consumer Secret
$woocommerce_consumer_key = 'ck_1b8dac952f5fd06c1dad13a70460f0dccd838344';
$woocommerce_consumer_secret = 'cs_b0a51cb6b59d7207b1eff518b34b72939407c6da';

// Set per_page parameter to fetch all products
$per_page = 100; // You can adjust this value as needed to fit the total number of products

// Append per_page parameter to the WooCommerce API URL
$woocommerce_url .= '?per_page=' . $per_page;

// Shopify API endpoint URL for adding products
$shopify_url = 'https://amittes.myshopify.com/admin/api/2024-01/products.json';

// Shopify Access Token
$shopify_access_token = 'shpua_c5fe00c7fd72bafd0f395aac002e6e30';

// Initialize cURL session for fetching WooCommerce products
$curl_woocommerce = curl_init();

// Set cURL options for fetching WooCommerce products
curl_setopt_array($curl_woocommerce, array(
    CURLOPT_URL => $woocommerce_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Accept: application/json'
    ),
    CURLOPT_USERPWD => $woocommerce_consumer_key . ':' . $woocommerce_consumer_secret,
    CURLOPT_SSL_VERIFYPEER => false, // Disable SSL verification (not recommended for production)
    CURLOPT_SSL_VERIFYHOST => false
));

// Execute cURL request to fetch WooCommerce products
$woocommerce_response = curl_exec($curl_woocommerce);

// Check for errors
if($woocommerce_response === false) {
    echo 'WooCommerce cURL error: ' . curl_error($curl_woocommerce);
} else {
    // Get HTTP status code
    $http_status = curl_getinfo($curl_woocommerce, CURLINFO_HTTP_CODE);
    
    // Check if request was successful
    if ($http_status == 200) {
        // Decode WooCommerce response
        $woocommerce_products = json_decode($woocommerce_response, true);
        
        // Loop through WooCommerce products and add them to Shopify
        foreach ($woocommerce_products as $woocommerce_product) {
            // Check if product with the same name and description already exists in Shopify
            $existing_product = checkExistingProductInShopify($shopify_url, $shopify_access_token, $woocommerce_product['name'], $woocommerce_product['description']);
            
            // If product exists, skip adding it
            if ($existing_product !== false) {
                echo 'Product "' . $woocommerce_product['name'] . '" with the same description already exists in Shopify. Skipping...';
                continue;
            }
            
            // Prepare product data for Shopify
            $product_data = array(
                'product' => array(
                    'title' => $woocommerce_product['name'],
                    'body_html' => $woocommerce_product['description'],
                    'vendor' => $woocommerce_product['attributes'][0]['options'][0], // Assuming the vendor is the first attribute option
                    'product_type' => $woocommerce_product['type'],
                    'images' => array()
                )
            );
            // print_r($woocommerce_product);die();

                // Fetch product images from WooCommerce and attach them to the product
            foreach ($woocommerce_product['images'] as $image) {
                // Check if the product has images
                if (!empty($image)) {
                    // Woocommerce product get path image
                    // $image_url = $image['src'];
                    $image_url = 'https://i.stack.imgur.com/zb2OL.png';
                    print_r("<br><br><br>Image URL: " . $image_url);
                    // Fetch image content
                    $image_content = @file_get_contents($image_url);
                } else {
                    // Use the default image URL for products without images
                    $image_url = 'https://i.stack.imgur.com/hgsRL.png';
                    print_r("<br><br><br>Default Image URL: " . $image_url);
                    // Fetch default image content
                    $image_content = @file_get_contents($image_url);
                }

                if ($image_content !== false) {
                    // Encode image content to base64
                    $image_data = base64_encode($image_content);
                    print_r("<br>Image Data: " . $image_data);
                    // Add image to product data
                    $product_data['product']['images'][] = array(
                        'attachment' => $image_data
                    );
                } else {
                    // Print error message if image content cannot be fetched
                    echo 'Failed to fetch image content: ' . $image_url;
                }
            }
            // Initialize cURL session for adding product to Shopify
            $curl_shopify = curl_init();
            
            // Set cURL options for adding product to Shopify
            curl_setopt_array($curl_shopify, array(
                CURLOPT_URL => $shopify_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($product_data),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'X-Shopify-Access-Token: ' . $shopify_access_token
                )
            ));
            
            // Execute cURL request to add product to Shopify
            $shopify_response = curl_exec($curl_shopify);
            
            // Check for errors
            if($shopify_response === false) {
                echo 'Shopify cURL error: ' . curl_error($curl_shopify);
            } else {
                // Get HTTP status code
                $http_status = curl_getinfo($curl_shopify, CURLINFO_HTTP_CODE);
                
                // Print the response
                if ($http_status == 401) {
                    echo 'Shopify Authentication Error: ' . $shopify_response;
                } else {
                    // Output JSON response
                    header('Content-Type: application/json');
                    echo $shopify_response;
                }
            }
            
            // Close cURL session for adding product to Shopify
            curl_close($curl_shopify);
        }
    } else {
        echo 'Failed to fetch WooCommerce products.';
    }
}

// Close cURL session for fetching WooCommerce products
curl_close($curl_woocommerce);


// Function to check if a product with the same name and description exists in Shopify
function checkExistingProductInShopify($shopify_url, $shopify_access_token, $product_name, $product_description) {
    $existing_product = false;

    // Initialize cURL session
    $curl_shopify_check = curl_init();

    // Set cURL options for checking product existence in Shopify
    curl_setopt_array($curl_shopify_check, array(
        CURLOPT_URL => $shopify_url . '?title=' . urlencode($product_name),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'X-Shopify-Access-Token: ' . $shopify_access_token
        )
    ));

    // Execute cURL request
    $shopify_check_response = curl_exec($curl_shopify_check);

    // Check for errors
    if ($shopify_check_response === false) {
        echo 'Shopify cURL error: ' . curl_error($curl_shopify_check);
    } else {
        // Get HTTP status code
        $http_status = curl_getinfo($curl_shopify_check, CURLINFO_HTTP_CODE);

        // If products with the same name are found, check if description matches
        if ($http_status == 200 && !empty(json_decode($shopify_check_response, true)['products'])) {
            $shopify_products = json_decode($shopify_check_response, true)['products'];
            foreach ($shopify_products as $product) {
                if ($product['body_html'] == $product_description) {
                    $existing_product = true;
                    break;
                }
            }
        }
    }

    // Close cURL session
    curl_close($curl_shopify_check);

    return $existing_product;
}
?>
