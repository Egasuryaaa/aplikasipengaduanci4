<?php
// Test script untuk create pengaduan API

$baseUrl = 'http://localhost/serverpengaduan/api';

// 1. Login first to get token (using credentials from previous successful test)
echo "Testing login to get token...\n";
$loginData = [
    'email_or_phone' => 'john.doe@gmail.com',
    'password' => 'user123'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$loginResponse = curl_exec($ch);
$loginCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Login Response Code: $loginCode\n";

if ($loginCode == 200) {
    $loginData = json_decode($loginResponse, true);
    if ($loginData['status'] && isset($loginData['data']['token'])) {
        $token = $loginData['data']['token'];
        echo "Token received: " . substr($token, 0, 50) . "...\n\n";
        
        // 2. Test create pengaduan
        echo "Testing create pengaduan with token...\n";
        
        $pengaduanData = [
            'deskripsi' => 'Pengaduan test dari script PHP - ' . date('Y-m-d H:i:s'),
            'kategori_id' => '4'  // Assuming kategori 4 exists
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $baseUrl . '/pengaduan');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($pengaduanData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Accept: application/json'
        ]);
        
        $createResponse = curl_exec($ch);
        $createCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "Create Pengaduan Response Code: $createCode\n";
        echo "Response: \n";
        print_r(json_decode($createResponse, true));
        
    } else {
        echo "Failed to get token from login response\n";
        print_r($loginData);
    }
} else {
    echo "Login failed with code: $loginCode\n";
    echo "Response: $loginResponse\n";
}
