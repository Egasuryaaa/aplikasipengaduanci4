<?php
// Test photo upload functionality
echo "=== TESTING PENGADUAN PHOTO UPLOAD ===" . PHP_EOL;

// Create a small test image in memory
$imageData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
$tempFile = tempnam(sys_get_temp_dir(), 'test_img') . '.png';
file_put_contents($tempFile, $imageData);

// Set up the POST data and FILES array as if coming from a multipart form
$_POST = [
    'deskripsi' => 'Test pengaduan dengan foto upload dari PHP script',
    'kategori_id' => '1'
];

$_FILES = [
    'foto_bukti' => [
        'name' => ['test_photo.png'],
        'type' => ['image/png'],
        'tmp_name' => [$tempFile],
        'error' => [0],
        'size' => [filesize($tempFile)]
    ]
];

echo "POST data: " . json_encode($_POST) . PHP_EOL;
echo "FILES data structure: " . PHP_EOL;
var_dump($_FILES);

// Prepare curl request to test the actual API
$ch = curl_init();

// Get JWT token first
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
    echo "Login successful, token obtained" . PHP_EOL;
    
    // Now test pengaduan creation with photo
    $postfields = [
        'deskripsi' => 'Test pengaduan dengan foto upload dari PHP script',
        'kategori_id' => '1',
        'foto_bukti' => new CURLFile($tempFile, 'image/png', 'test_photo.png')
    ];
    
    curl_setopt($ch, CURLOPT_URL, 'http://localhost/serverpengaduan/api/pengaduan');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
        // Don't set Content-Type for multipart/form-data, curl will set it automatically
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    echo "HTTP Code: " . $httpCode . PHP_EOL;
    echo "Response: " . $response . PHP_EOL;
    
    $result = json_decode($response, true);
    if ($result && isset($result['data']['pengaduan']['foto_bukti'])) {
        echo "Photo URLs in response: " . PHP_EOL;
        var_dump($result['data']['pengaduan']['foto_bukti']);
    }
    
} else {
    echo "Login failed: " . $loginResponse . PHP_EOL;
}

curl_close($ch);

// Clean up
unlink($tempFile);

echo "=== TEST COMPLETE ===" . PHP_EOL;
?>
