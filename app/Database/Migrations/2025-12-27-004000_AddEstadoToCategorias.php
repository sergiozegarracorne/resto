<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEstadoToCategorias extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('estado', 'categorias')) {
            $fields = [
                'estado' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 1,
                    'after' => 'icono',
                ],
            ];
            $this->forge->addColumn('categorias', $fields);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('estado', 'categorias')) {
            $this->forge->dropColumn('categorias', 'estado');
        }
    }
}
