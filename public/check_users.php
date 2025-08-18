<?php
require 'vendor/autoload.php';

$db = \Config\Database::connect();
$users = $db->table('users')->get()->getResultArray();

echo "Available users:\n";
foreach($users as $user) {
    echo 'ID: ' . $user['id'] . ', Email: ' . $user['email'] . ', Phone: ' . $user['phone'] . ', Name: ' . $user['name'] . "\n";
}
?>
