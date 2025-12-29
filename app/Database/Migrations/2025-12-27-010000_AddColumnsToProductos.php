<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddColumnsToProductos extends Migration
{
    public function up()
    {
        $fields = [];

        if (!$this->db->fieldExists('imagen', 'productos')) {
            $fields['imagen'] = [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
                'after' => 'precio'
            ];
        }

        if (!$this->db->fieldExists('updated_at', 'productos')) {
            $fields['updated_at'] = ['type' => 'DATETIME', 'null' => true];
        }

        if (!$this->db->fieldExists('deleted_at', 'productos')) {
            $fields['deleted_at'] = ['type' => 'DATETIME', 'null' => true];
        }

        if (!empty($fields)) {
            $this->forge->addColumn('productos', $fields);
        }
    }

    public function down()
    {
        // Dropping columns if they exist
    }
}
