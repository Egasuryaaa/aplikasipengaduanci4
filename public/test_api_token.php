<?php

// Test script untuk memverifikasi API endpoint
$baseUrl = 'http://localhost/serverpengaduan/api';

// Test 1: Login
echo "Testing login...\n";
$loginData = [
    'email_or_phone' => 'john.doe@gmail.com',
    'password' => 'user123'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/login');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Login Response Code: $httpCode\n";
$loginResponse = json_decode($response, true);
print_r($loginResponse);

if ($loginResponse && $loginResponse['status'] && isset($loginResponse['data']['token'])) {
    $token = $loginResponse['data']['token'];
    echo "\nToken: $token\n\n";
    
    // Test 2: Get Pengaduan List dengan Token
    echo "Testing get pengaduan list with token...\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/pengaduan');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $token
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Pengaduan List Response Code: $httpCode\n";
    $pengaduanResponse = json_decode($response, true);
    print_r($pengaduanResponse);
    
    // Test 3: Get Pengaduan Detail dengan Token
    echo "\nTesting get pengaduan detail with token...\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/pengaduan/14');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $token
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Pengaduan Detail Response Code: $httpCode\n";
    $detailResponse = json_decode($response, true);
    print_r($detailResponse);
} else {
    echo "Login failed, cannot test authenticated endpoints\n";
}
