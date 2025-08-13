<?php
// Test script to check if files are accessible
header('Content-Type: text/html');
echo "<h1>File Access Test</h1>";

// Path to the file
$file1 = __DIR__ . '/datatables/id.json';
$file2 = __DIR__ . '/../js/datatables/id.json';

echo "<h2>Testing file access:</h2>";
echo "<p>File 1: $file1</p>";
echo "<p>File 2: $file2</p>";

// Check if the file exists
if (file_exists($file1)) {
    echo "<p style='color:green'>File 1 exists!</p>";
    
    // Try to read the file
    $content = file_get_contents($file1);
    if ($content) {
        echo "<p style='color:green'>File 1 content can be read!</p>";
        echo "<pre>" . htmlspecialchars(substr($content, 0, 100)) . "...</pre>";
    } else {
        echo "<p style='color:red'>Cannot read File 1 content!</p>";
    }
} else {
    echo "<p style='color:red'>File 1 does not exist!</p>";
}

// Check if the file exists
if (file_exists($file2)) {
    echo "<p style='color:green'>File 2 exists!</p>";
    
    // Try to read the file
    $content = file_get_contents($file2);
    if ($content) {
        echo "<p style='color:green'>File 2 content can be read!</p>";
        echo "<pre>" . htmlspecialchars(substr($content, 0, 100)) . "...</pre>";
    } else {
        echo "<p style='color:red'>Cannot read File 2 content!</p>";
    }
} else {
    echo "<p style='color:red'>File 2 does not exist!</p>";
}

// Check web access
$url1 = 'http://localhost/serverpengaduan/public/js/datatables/id.json';
$url2 = 'http://localhost/serverpengaduan/js/datatables/id.json';

echo "<h2>Testing web access:</h2>";
echo "<p>URL 1: <a href='$url1' target='_blank'>$url1</a></p>";
echo "<p>URL 2: <a href='$url2' target='_blank'>$url2</a></p>";
?>
