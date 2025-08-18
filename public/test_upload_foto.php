<?php
// Test upload foto dalam create pengaduan

$baseUrl = 'http://localhost/serverpengaduan/api';

// Login first
echo "Testing login...\n";
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

if ($loginCode != 200) {
    echo "Login failed: $loginCode\n";
    exit;
}

$loginData = json_decode($loginResponse, true);
$token = $loginData['data']['token'];
echo "Login successful, token: " . substr($token, 0, 30) . "...\n\n";

// Create a test image file
$testImageContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
$testImagePath = sys_get_temp_dir() . '/test_image.png';
file_put_contents($testImagePath, $testImageContent);

// Test create pengaduan with foto
echo "Testing create pengaduan with foto...\n";

$postData = [
    'deskripsi' => 'Test pengaduan dengan foto - ' . date('Y-m-d H:i:s'),
    'kategori_id' => '4'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/pengaduan');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'deskripsi' => $postData['deskripsi'],
    'kategori_id' => $postData['kategori_id'],
    'foto_bukti[]' => new CURLFile($testImagePath, 'image/png', 'test.png')
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
]);

$createResponse = curl_exec($ch);
$createCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "Create Pengaduan Response Code: $createCode\n";
if ($curlError) {
    echo "CURL Error: $curlError\n";
}

$responseData = json_decode($createResponse, true);
if ($responseData) {
    echo "Response:\n";
    print_r($responseData);
    
    if (isset($responseData['data']['pengaduan']['foto_bukti'])) {
        echo "\nFoto URLs:\n";
        foreach ($responseData['data']['pengaduan']['foto_bukti'] as $foto) {
            echo "- $foto\n";
        }
    }
} else {
    echo "Raw response: $createResponse\n";
}

// Cleanup
unlink($testImagePath);
?>
