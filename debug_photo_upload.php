<?php
// Debug script for current pengaduan creation with photo upload
echo "=== DEBUGGING PENGADUAN PHOTO UPLOAD ISSUE ===" . PHP_EOL;

// Simulate exactly what Flutter is sending
$_POST = array(
    'deskripsi' => 'Test pengaduan with photo debug',
    'kategori_id' => '1'
);

// Check if files are being received correctly
echo "POST data received:" . PHP_EOL;
var_dump($_POST);

echo PHP_EOL . "FILES array status:" . PHP_EOL;
if (isset($_FILES)) {
    echo "FILES superglobal exists" . PHP_EOL;
    if (isset($_FILES['foto_bukti'])) {
        echo "foto_bukti files found:" . PHP_EOL;
        var_dump($_FILES['foto_bukti']);
    } else {
        echo "No 'foto_bukti' in FILES array" . PHP_EOL;
        echo "Available FILE keys: " . json_encode(array_keys($_FILES)) . PHP_EOL;
    }
} else {
    echo "No FILES superglobal found" . PHP_EOL;
}

echo PHP_EOL . "=== Testing getFileMultiple method ===" . PHP_EOL;
// Include CodeIgniter framework to test the actual method being used
require_once 'vendor/autoload.php';

// Check if we can access CodeIgniter request methods
try {
    $config = new \Config\App();
    $request = \Config\Services::request();
    
    echo "Request service loaded successfully" . PHP_EOL;
    echo "Request method: " . $request->getMethod() . PHP_EOL;
    
    // Test getFileMultiple method
    $files = $request->getFileMultiple('foto_bukti');
    echo "getFileMultiple result: " . PHP_EOL;
    var_dump($files);
    
} catch (Exception $e) {
    echo "Error loading CodeIgniter: " . $e->getMessage() . PHP_EOL;
}
?>
