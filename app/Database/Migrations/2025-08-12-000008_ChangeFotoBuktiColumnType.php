<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ChangeFotoBuktiColumnType extends Migration
{
    public function up()
    {
        // Change foto_bukti column from JSON to VARCHAR(255)
        $this->forge->modifyColumn('pengaduan', [
            'foto_bukti' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        // Revert back to JSON type
        $this->forge->modifyColumn('pengaduan', [
            'foto_bukti' => [
                'type' => 'JSON',
                'null' => true,
            ],
        ]);
    }
}