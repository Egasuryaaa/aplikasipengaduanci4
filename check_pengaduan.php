<?php
// Simple database connection to check pengaduan status
try {
    $pdo = new PDO('pgsql:host=localhost;dbname=pengaduan_db', 'postgres', '');
    $stmt = $pdo->prepare('SELECT id, status, created_at FROM pengaduan WHERE id = ?');
    $stmt->execute([26]);
    $pengaduan = $stmt->fetch(PDO::FETCH_OBJ);
    
    if ($pengaduan) {
        echo "Pengaduan ID: " . $pengaduan->id . "\n";
        echo "Status: " . $pengaduan->status . "\n";
        echo "Created: " . $pengaduan->created_at . "\n";
    } else {
        echo "Pengaduan not found\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
