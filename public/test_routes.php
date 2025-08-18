<?php
// Test route files directly
echo "Testing file route access...\n\n";

$testUrls = [
    'http://localhost/serverpengaduan/',
    'http://localhost/serverpengaduan/files',
    'http://localhost/serverpengaduan/files/pengaduan',
    'http://localhost/serverpengaduan/files/pengaduan/1755527094_f89ec96ed7502cdeb3cb.png',
];

foreach ($testUrls as $url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "$url -> HTTP $httpCode\n";
}
?>
