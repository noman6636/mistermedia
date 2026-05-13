<?php 

$clientId = 'Smartfut-EcomsTra-PRD-0a70f81c3-5683cf7c';
$clientSecret = 'PRD-a70f81c32c7a-830b-4fae-8b0d-e55a';
$apiEndpoint = 'https://api.ebay.com/developer/analytics/v1_beta/public/rate_limit';

// === Get Access Token (Client Credentials) ===
function getEbayAppToken($clientId, $clientSecret) {
    $credentials = base64_encode("$clientId:$clientSecret");

    $ch = curl_init('https://api.ebay.com/identity/v1/oauth2/token');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'grant_type' => 'client_credentials',
            'scope' => 'https://api.ebay.com/oauth/api_scope'
        ]),
        CURLOPT_HTTPHEADER => [
            "Authorization: Basic $credentials",
            "Content-Type: application/x-www-form-urlencoded"
        ]
    ]);

    $response = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($response, true);

    if (isset($result['access_token'])) {
        return $result['access_token'];
    } else {
        throw new Exception("Failed to get token: " . json_encode($result));
    }
}

// === Call Developer Analytics API ===
function fetchRateLimits($accessToken) {
    $ch = curl_init('https://api.ebay.com/developer/analytics/v1_beta/rate_limit/');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPGET => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $accessToken",
            "Accept: application/json"
        ]
    ]);

    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode === 200) {
        return json_decode($response, true);
    } else {
        throw new Exception("Failed to fetch rate limits: $response");
    }
}

try {
    echo "Fetching Developer Analytics...\n";
    $token = getEbayAppToken($clientId, $clientSecret);
    
    $limits = fetchRateLimits($token);

    echo "Rate Limits:\n";
    echo 'Data: <pre>' .print_r($limits,true). '</pre>'; die;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

