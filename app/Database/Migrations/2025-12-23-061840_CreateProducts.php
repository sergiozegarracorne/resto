<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProducts extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'categoria_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'nombre' => ['type' => 'VARCHAR', 'constraint' => '200'],
            'precio' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'es_combo' => ['type' => 'BOOLEAN', 'default' => false], // true para el "Menú" o "Parrillada"
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('categoria_id', 'categorias', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('productos');
    }
    public function down()
    {
        $this->forge->dropTable('productos');
    }
}
