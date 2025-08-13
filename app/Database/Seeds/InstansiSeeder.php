<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InstansiSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'nama' => 'Dinas Komunikasi dan Informatika',
                'alamat' => 'Jl. Brigjen Katamso No. 1, Wonosari, Gunung Kidul',
                'telepon' => '0274-391037',
                'email' => 'kominfo@gunungkidulkab.go.id',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama' => 'Dinas Pendidikan, Pemuda dan Olahraga',
                'alamat' => 'Jl. Pemuda No. 32, Wonosari, Gunung Kidul',
                'telepon' => '0274-391038',
                'email' => 'dikpora@gunungkidulkab.go.id',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama' => 'Dinas Kesehatan',
                'alamat' => 'Jl. Dr. Sutomo No. 15, Wonosari, Gunung Kidul',
                'telepon' => '0274-391039',
                'email' => 'dinkes@gunungkidulkab.go.id',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama' => 'Dinas Pekerjaan Umum dan Perumahan Rakyat',
                'alamat' => 'Jl. Veteran No. 28, Wonosari, Gunung Kidul',
                'telepon' => '0274-391040',
                'email' => 'pupr@gunungkidulkab.go.id',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama' => 'Dinas Sosial',
                'alamat' => 'Jl. Raya Yogya-Wonosari Km. 31, Wonosari',
                'telepon' => '0274-391041',
                'email' => 'dinsos@gunungkidulkab.go.id',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama' => 'Dinas Pariwisata',
                'alamat' => 'Jl. Baron No. 5, Wonosari, Gunung Kidul',
                'telepon' => '0274-391042',
                'email' => 'dispar@gunungkidulkab.go.id',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama' => 'Dinas Perhubungan',
                'alamat' => 'Jl. Nasional III No. 12, Wonosari, Gunung Kidul',
                'telepon' => '0274-391043',
                'email' => 'dishub@gunungkidulkab.go.id',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama' => 'Dinas Lingkungan Hidup',
                'alamat' => 'Jl. Tentara Pelajar No. 8, Wonosari, Gunung Kidul',
                'telepon' => '0274-391044',
                'email' => 'dlh@gunungkidulkab.go.id',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama' => 'Dinas Pertanian dan Pangan',
                'alamat' => 'Jl. Ngalau No. 20, Wonosari, Gunung Kidul',
                'telepon' => '0274-391045',
                'email' => 'distanpang@gunungkidulkab.go.id',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama' => 'Dinas Perdagangan dan Perindustrian',
                'alamat' => 'Jl. Pahlawan No. 14, Wonosari, Gunung Kidul',
                'telepon' => '0274-391046',
                'email' => 'disperindag@gunungkidulkab.go.id',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        $this->db->table('instansi')->insertBatch($data);

        echo "Instansi seeded successfully! " . count($data) . " instansi added.\n";
    }
}
