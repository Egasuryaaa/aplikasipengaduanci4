<?php
// Test the API response for pengaduan with photos
echo "=== TESTING PENGADUAN API WITH PHOTOS ===" . PHP_EOL;

// Login first
$ch = curl_init();
$loginData = [
    'email_or_phone' => 'john.doe@gmail.com',
    'password' => 'user123'
];

curl_setopt($ch, CURLOPT_URL, 'http://localhost/serverpengaduan/api/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$loginResponse = curl_exec($ch);
$loginResult = json_decode($loginResponse, true);

if (isset($loginResult['data']['token'])) {
    $token = $loginResult['data']['token'];
    echo "Login successful" . PHP_EOL;
    
    // Get pengaduan list
    curl_setopt($ch, CURLOPT_URL, 'http://localhost/serverpengaduan/api/pengaduan');
    curl_setopt($ch, CURLOPT_POST, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $result = json_decode($response, true);
    
    if ($result && isset($result['data']['items'])) {
        echo "Found " . count($result['data']['items']) . " pengaduan(s)" . PHP_EOL;
        
        foreach ($result['data']['items'] as $item) {
            echo "ID: " . $item['id'] . ", Nomor: " . $item['nomor_pengaduan'] . PHP_EOL;
            echo "Foto count: " . (isset($item['foto_bukti']) ? count($item['foto_bukti']) : 0) . PHP_EOL;
            
            if (isset($item['foto_bukti']) && is_array($item['foto_bukti']) && !empty($item['foto_bukti'])) {
                echo "Photo URLs:" . PHP_EOL;
                foreach ($item['foto_bukti'] as $photoUrl) {
                    echo "  - " . $photoUrl . PHP_EOL;
                    
                    // Test if photo URL is accessible
                    $photoCheck = curl_init();
                    curl_setopt($photoCheck, CURLOPT_URL, $photoUrl);
                    curl_setopt($photoCheck, CURLOPT_NOBODY, true);
                    curl_setopt($photoCheck, CURLOPT_RETURNTRANSFER, true);
                    curl_exec($photoCheck);
                    $photoHttpCode = curl_getinfo($photoCheck, CURLINFO_HTTP_CODE);
                    curl_close($photoCheck);
                    
                    echo "    Status: " . $photoHttpCode . ($photoHttpCode == 200 ? ' (OK)' : ' (ERROR)') . PHP_EOL;
                }
            } else {
                echo "No photos" . PHP_EOL;
            }
            echo "---" . PHP_EOL;
        }
    }
} else {
    echo "Login failed" . PHP_EOL;
}

curl_close($ch);
echo "=== TEST COMPLETE ===" . PHP_EOL;
?>
