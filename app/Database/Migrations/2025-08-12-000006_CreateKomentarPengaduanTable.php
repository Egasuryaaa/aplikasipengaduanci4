<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKomentarPengaduanTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'SERIAL',
                'auto_increment' => true,
            ],
            'pengaduan_id' => [
                'type' => 'INT',
            ],
            'user_id' => [
                'type' => 'INT',
            ],
            'komentar' => [
                'type' => 'TEXT',
            ],
            'is_internal' => [
                'type'    => 'BOOLEAN',
                'default' => false,
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
        $this->forge->addKey('pengaduan_id');
        $this->forge->addForeignKey('pengaduan_id', 'pengaduan', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('komentar_pengaduan');
    }

    public function down()
    {
        $this->forge->dropTable('komentar_pengaduan');
    }
}
