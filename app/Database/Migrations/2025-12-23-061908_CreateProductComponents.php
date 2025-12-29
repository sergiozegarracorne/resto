<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProductComponents extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'producto_padre_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true], // El Combo
            'producto_hijo_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true], // El ingrediente/plato
            'cantidad' => ['type' => 'INT', 'constraint' => 5, 'default' => 1],
            'es_opcional' => ['type' => 'BOOLEAN', 'default' => false], // true si es a elegir (ej: ensalada)
            'grupo_opcion' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true], // Ej: 'guarnicion'
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('producto_padre_id', 'productos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('producto_hijo_id', 'productos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('producto_componentes');
    }
    public function down()
    {
        $this->forge->dropTable('producto_componentes');
    }
}
