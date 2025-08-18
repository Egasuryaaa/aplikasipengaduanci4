<?php
require_once 'vendor/autoload.php';
$db = \Config\Database::connect();

echo 'Recent pengaduan entries with photos:' . PHP_EOL;
$query = $db->query('SELECT id, nomor_pengaduan, foto_bukti, created_at FROM pengaduan ORDER BY id DESC LIMIT 3');
$results = $query->getResultArray();

foreach ($results as $row) {
    echo 'ID: ' . $row['id'] . ', Nomor: ' . $row['nomor_pengaduan'] . ', Created: ' . $row['created_at'] . PHP_EOL;
    echo 'Foto: ' . $row['foto_bukti'] . PHP_EOL;
    
    // Check if photos exist
    $photos = json_decode($row['foto_bukti'], true);
    if (is_array($photos) && !empty($photos)) {
        foreach ($photos as $filename) {
            $filePath = 'uploads/pengaduan/' . $filename;
            echo 'Photo file: ' . $filename . ' - Exists: ' . (file_exists($filePath) ? 'YES' : 'NO') . PHP_EOL;
        }
    }
    echo '---' . PHP_EOL;
}
?>
