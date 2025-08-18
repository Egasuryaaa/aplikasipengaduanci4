<?php
// Direct database check without CodeIgniter
$host = 'localhost';
$dbname = 'db_pengaduan';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo 'Recent pengaduan entries with photos:' . PHP_EOL;
    
    $stmt = $pdo->query('SELECT id, nomor_pengaduan, foto_bukti, created_at FROM pengaduan ORDER BY id DESC LIMIT 3');
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as $row) {
        echo 'ID: ' . $row['id'] . ', Nomor: ' . $row['nomor_pengaduan'] . ', Created: ' . $row['created_at'] . PHP_EOL;
        echo 'Foto: ' . $row['foto_bukti'] . PHP_EOL;
        
        // Check if photos exist
        $photos = json_decode($row['foto_bukti'], true);
        if (is_array($photos) && !empty($photos)) {
            echo 'Found ' . count($photos) . ' photo(s):' . PHP_EOL;
            foreach ($photos as $filename) {
                $filePath = 'uploads/pengaduan/' . $filename;
                echo '  - ' . $filename . ' - Exists: ' . (file_exists($filePath) ? 'YES' : 'NO') . PHP_EOL;
            }
        } else {
            echo 'No photos or empty photo array' . PHP_EOL;
        }
        echo '---' . PHP_EOL;
    }
    
} catch (PDOException $e) {
    echo 'Database error: ' . $e->getMessage() . PHP_EOL;
}
?>
