<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create master user
        $masterData = [
            'uuid' => $this->generateUuid(),
            'name' => 'Master Admin',
            'email' => 'master@kominfo-gunungkidul.go.id',
            'phone' => '081234567890',
            'password' => password_hash('master123', PASSWORD_DEFAULT),
            'role' => 'master',
            'is_active' => true,
            'email_verified_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->table('users')->insert($masterData);

        // Create admin users
        $adminData = [
            [
                'uuid' => $this->generateUuid(),
                'name' => 'Admin 1',
                'email' => 'admin1@kominfo-gunungkidul.go.id',
                'phone' => '081234567891',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'uuid' => $this->generateUuid(),
                'name' => 'Admin 2',
                'email' => 'admin2@kominfo-gunungkidul.go.id',
                'phone' => '081234567892',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        $this->db->table('users')->insertBatch($adminData);

        // Create sample users
        $userData = [
            [
                'uuid' => $this->generateUuid(),
                'name' => 'John Doe',
                'email' => 'john.doe@gmail.com',
                'phone' => '081234567893',
                'password' => password_hash('user123', PASSWORD_DEFAULT),
                'instansi_id' => 1,
                'role' => 'user',
                'is_active' => true,
                'email_verified_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'uuid' => $this->generateUuid(),
                'name' => 'Jane Smith',
                'email' => 'jane.smith@gmail.com',
                'phone' => '081234567894',
                'password' => password_hash('user123', PASSWORD_DEFAULT),
                'instansi_id' => 2,
                'role' => 'user',
                'is_active' => true,
                'email_verified_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        $this->db->table('users')->insertBatch($userData);

        echo "Users seeded successfully!\n";
        echo "Master: master@kominfo-gunungkidul.go.id / master123\n";
        echo "Admin1: admin1@kominfo-gunungkidul.go.id / admin123\n";
        echo "Admin2: admin2@kominfo-gunungkidul.go.id / admin123\n";
        echo "User1: john.doe@gmail.com / user123\n";
        echo "User2: jane.smith@gmail.com / user123\n";
    }

    /**
     * Generate a simple UUID v4
     */
    private function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
