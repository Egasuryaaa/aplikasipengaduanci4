<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class KategoriSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'nama' => 'Infrastruktur TI',
                'deskripsi' => 'Pengaduan terkait infrastruktur teknologi informasi, jaringan, server, dan perangkat keras',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama' => 'Aplikasi dan Sistem',
                'deskripsi' => 'Pengaduan terkait aplikasi, sistem informasi, dan software',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama' => 'Website dan Portal',
                'deskripsi' => 'Pengaduan terkait website resmi, portal, dan layanan online',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama' => 'Keamanan Informasi',
                'deskripsi' => 'Pengaduan terkait keamanan data, privasi, dan cyber security',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama' => 'Layanan Publik Digital',
                'deskripsi' => 'Pengaduan terkait layanan publik berbasis digital dan e-government',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama' => 'Komunikasi dan Media',
                'deskripsi' => 'Pengaduan terkait komunikasi publik, media sosial, dan informasi',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama' => 'Pelatihan dan Literasi Digital',
                'deskripsi' => 'Pengaduan terkait pelatihan TI dan program literasi digital',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama' => 'Data dan Statistik',
                'deskripsi' => 'Pengaduan terkait pengelolaan data, statistik, dan basis data',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama' => 'Smart City',
                'deskripsi' => 'Pengaduan terkait program smart city dan inovasi kota cerdas',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama' => 'Lainnya',
                'deskripsi' => 'Pengaduan lainnya yang tidak termasuk dalam kategori di atas',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        $this->db->table('kategori_pengaduan')->insertBatch($data);

        echo "Kategori pengaduan seeded successfully! " . count($data) . " kategori added.\n";
    }
}
