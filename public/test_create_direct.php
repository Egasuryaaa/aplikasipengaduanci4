<?php
// Test create pengaduan directly with known token from previous test

$baseUrl = 'http://localhost/serverpengaduan/api';

// Use token from previous successful test
$token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6IjQiLCJlbWFpbCI6ImpvaG4uZG9lQGdtYWlsLmNvbSIsImV4cCI6MTc1NTYxMTk4NH0.skTDwB-w2nPokyRtZixfsLa2TGXmph-ORYu3EqoEXU0';

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
$curlError = curl_error($ch);
curl_close($ch);

echo "Create Pengaduan Response Code: $createCode\n";
if ($curlError) {
    echo "CURL Error: $curlError\n";
}
echo "Response: \n";
$responseData = json_decode($createResponse, true);
if ($responseData) {
    print_r($responseData);
} else {
    echo "Raw response: $createResponse\n";
}
?>
