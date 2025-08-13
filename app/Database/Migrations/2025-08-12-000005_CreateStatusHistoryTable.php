<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStatusHistoryTable extends Migration
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
            'status_old' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'null'       => true,
            ],
            'status_new' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
            ],
            'keterangan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'updated_by' => [
                'type' => 'INT',
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('pengaduan_id');
        $this->forge->addForeignKey('pengaduan_id', 'pengaduan', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('updated_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('status_history');
    }

    public function down()
    {
        $this->forge->dropTable('status_history');
    }
}
