<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePengaduanTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'SERIAL',
                'auto_increment' => true,
            ],
            'uuid' => [
                'type'       => 'VARCHAR',
                'constraint' => '36',
                'unique'     => true,
            ],
            'nomor_pengaduan' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'unique'     => true,
            ],
            'user_id' => [
                'type' => 'INT',
            ],
            'instansi_id' => [
                'type' => 'INT',
            ],
            'kategori_id' => [
                'type' => 'INT',
            ],
            'deskripsi' => [
                'type' => 'TEXT',
            ],
            'foto_bukti' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'default'    => 'pending',
            ],
            'tanggal_selesai' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'keterangan_admin' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('uuid');
        $this->forge->addKey('nomor_pengaduan');
        $this->forge->addKey('user_id');
        $this->forge->addKey('status');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('instansi_id', 'instansi', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('kategori_id', 'kategori_pengaduan', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('pengaduan');

        // Add check constraints
        $this->db->query("ALTER TABLE pengaduan ADD CONSTRAINT check_status CHECK (status IN ('pending', 'diproses', 'selesai', 'ditolak'))");
    }

    public function down()
    {
        $this->forge->dropTable('pengaduan');
    }
}
