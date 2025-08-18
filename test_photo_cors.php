<?php
// Test photo access from different origins
echo "=== TESTING PHOTO ACCESS AND CORS ===" . PHP_EOL;

// Test direct photo access
$photoUrl = 'http://localhost/serverpengaduan/uploads/pengaduan/1755529155_0d60abc0c6ea75e8be00.jpg';
echo "Testing photo URL: " . $photoUrl . PHP_EOL;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $photoUrl);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Origin: http://localhost:3000', // Simulate Flutter origin
    'Access-Control-Request-Method: GET'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: " . $httpCode . PHP_EOL;
echo "Response Headers:" . PHP_EOL;
echo $response . PHP_EOL;

// Test API pengaduan response 
echo PHP_EOL . "=== TESTING API PENGADUAN RESPONSE ===" . PHP_EOL;

// Login first
$loginCh = curl_init();
curl_setopt($loginCh, CURLOPT_URL, 'http://localhost/serverpengaduan/api/login');
curl_setopt($loginCh, CURLOPT_POST, true);
curl_setopt($loginCh, CURLOPT_POSTFIELDS, json_encode([
    'email_or_phone' => 'john.doe@gmail.com',
    'password' => 'user123'
]));
curl_setopt($loginCh, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($loginCh, CURLOPT_RETURNTRANSFER, true);

$loginResponse = curl_exec($loginCh);
$loginResult = json_decode($loginResponse, true);
curl_close($loginCh);

if (isset($loginResult['data']['token'])) {
    $token = $loginResult['data']['token'];
    echo "Login successful, testing pengaduan API..." . PHP_EOL;
    
    // Get latest pengaduan with photos
    $apiCh = curl_init();
    curl_setopt($apiCh, CURLOPT_URL, 'http://localhost/serverpengaduan/api/pengaduan/25'); // Latest ID
    curl_setopt($apiCh, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Accept: application/json',
        'Origin: http://localhost:3000' // Simulate Flutter origin
    ]);
    curl_setopt($apiCh, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($apiCh, CURLOPT_HEADER, true);
    
    $apiResponse = curl_exec($apiCh);
    $httpCode = curl_getinfo($apiCh, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($apiCh, CURLINFO_HEADER_SIZE);
    
    $headers = substr($apiResponse, 0, $headerSize);
    $body = substr($apiResponse, $headerSize);
    
    curl_close($apiCh);
    
    echo "API HTTP Status: " . $httpCode . PHP_EOL;
    echo "API Response Headers:" . PHP_EOL;
    echo $headers . PHP_EOL;
    
    if ($httpCode === 200) {
        $result = json_decode($body, true);
        if (isset($result['data']['pengaduan']['foto_bukti'])) {
            echo "Photo URLs in API response:" . PHP_EOL;
            foreach ($result['data']['pengaduan']['foto_bukti'] as $photo) {
                echo "  - " . $photo . PHP_EOL;
            }
        }
    }
    
} else {
    echo "Login failed: " . $loginResponse . PHP_EOL;
}

echo "=== TEST COMPLETE ===" . PHP_EOL;
?>
