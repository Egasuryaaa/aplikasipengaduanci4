<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PengaduanSeeder extends Seeder
{
    public function run()
    {
        // Get UUIDs
        if (!function_exists('generateUuid')) {
            function generateUuid() {
                return sprintf(
                    '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                    mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                    mt_rand(0, 0xffff),
                    mt_rand(0, 0x0fff) | 0x4000,
                    mt_rand(0, 0x3fff) | 0x8000,
                    mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
                );
            }
        }

        // Get existing users, instansi, and categories
        $users = $this->db->table('users')->select('id')->get()->getResult('array');
        $instansi = $this->db->table('instansi')->select('id')->get()->getResult('array');
        $categories = $this->db->table('kategori_pengaduan')->select('id')->get()->getResult('array');

        if (empty($users) || empty($instansi) || empty($categories)) {
            echo "Please run UserSeeder, InstansiSeeder, and KategoriSeeder first\n";
            return;
        }

        $data = [];
        $statuses = ['pending', 'diproses', 'selesai', 'ditolak'];

        for ($i = 1; $i <= 15; $i++) {
            $data[] = [
                'uuid' => generateUuid(),
                'nomor_pengaduan' => 'ADU' . date('Ymd') . str_pad($i, 4, '0', STR_PAD_LEFT),
                'user_id' => $users[array_rand($users)]['id'],
                'instansi_id' => $instansi[array_rand($instansi)]['id'],
                'kategori_id' => $categories[array_rand($categories)]['id'],
                'deskripsi' => 'Pengaduan nomor ' . $i . ': Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                'foto_bukti' => null,
                'status' => $statuses[array_rand($statuses)],
                'tanggal_selesai' => null,
                'keterangan_admin' => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 30) . ' days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 30) . ' days'))
            ];
        }

        $this->db->table('pengaduan')->insertBatch($data);
        echo "Inserted " . count($data) . " pengaduan records\n";
    }
}
