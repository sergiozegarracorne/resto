<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIconoToCategorias extends Migration
{
    public function up()
    {
        // Check if column exists first to be safe, or just add it (forge usually fails if exists, so we trust it's missing based on error)
        $fields = [
            'icono' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
                'after' => 'nombre',
            ],
        ];
        $this->forge->addColumn('categorias', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('categorias', 'icono');
    }
}
