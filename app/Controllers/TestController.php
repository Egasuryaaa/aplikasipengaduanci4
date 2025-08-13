<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class TestController extends BaseController
{
    public function database()
    {
        try {
            $db = \Config\Database::connect();
            
            // Test connection
            $result = $db->query("SELECT version()")->getRow();
            echo "PostgreSQL Version: " . $result->version . "<br>";
            
            // Test users table
            $userCount = $db->query("SELECT COUNT(*) as count FROM users")->getRow();
            echo "Users in database: " . $userCount->count . "<br>";
            
            // Test specific user
            $testUser = $db->query("SELECT * FROM users WHERE email = 'master@kominfo-gunungkidul.go.id'")->getRow();
            if ($testUser) {
                echo "Master user found: " . $testUser->name . " (" . $testUser->email . ")<br>";
                echo "Password hash: " . substr($testUser->password, 0, 20) . "...<br>";
                echo "Role: " . $testUser->role . "<br>";
                echo "Active: " . ($testUser->is_active ? 'Yes' : 'No') . "<br>";
            } else {
                echo "Master user NOT found!<br>";
            }
            
        } catch (\Exception $e) {
            echo "Database Error: " . $e->getMessage();
        }
    }
    
    public function testPassword()
    {
        $password = 'master123';
        $hash = password_hash($password, PASSWORD_DEFAULT);
        echo "Original password: " . $password . "<br>";
        echo "Generated hash: " . $hash . "<br>";
        echo "Verification: " . (password_verify($password, $hash) ? 'PASS' : 'FAIL');
    }
}
