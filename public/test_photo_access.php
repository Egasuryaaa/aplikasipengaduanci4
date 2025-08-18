<?php
// Test mengakses foto yang diupload
$photoUrl = 'http://localhost/serverpengaduan/uploads/pengaduan/1755527434_d9c715094a27a9fa0638.png';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $photoUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);
curl_setopt($ch, CURLOPT_HEADER, true);

$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Photo URL: $photoUrl\n";
echo "HTTP Code: $httpCode\n";
echo "Headers: \n$result\n";

if ($httpCode == 200) {
    echo "✅ Photo accessible!\n";
} else {
    echo "❌ Photo not accessible\n";
}
?>
