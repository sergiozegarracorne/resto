<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TablesAndOrders extends Migration
{
    public function up()
    {
        // 1. Tabla Pisos
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 5, 'unsigned' => true, 'auto_increment' => true],
            'nombre' => ['type' => 'VARCHAR', 'constraint' => 100],
            'orden' => ['type' => 'INT', 'constraint' => 5],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('pisos');

        // 2. Tabla Mesas
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 5, 'unsigned' => true, 'auto_increment' => true],
            'nombre' => ['type' => 'VARCHAR', 'constraint' => 100],
            'id_piso' => ['type' => 'INT', 'constraint' => 5, 'unsigned' => true],
            'x' => ['type' => 'INT', 'constraint' => 5, 'default' => 0],
            'y' => ['type' => 'INT', 'constraint' => 5, 'default' => 0],
            'estado' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'libre'], // libre, ocupada
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_piso', 'pisos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('mesas');

        // 3. Tabla Pedidos
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_mesa' => ['type' => 'INT', 'constraint' => 5, 'unsigned' => true, 'null' => true],
            'id_usuario' => ['type' => 'INT', 'constraint' => 5, 'unsigned' => true],
            'total' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'estado' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pendiente'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('pedidos');

        // 4. Tabla Pedido Detalles
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_pedido' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_producto' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'nombre_producto' => ['type' => 'VARCHAR', 'constraint' => 255],
            'cantidad' => ['type' => 'INT', 'constraint' => 5, 'default' => 1],
            'precio' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_pedido', 'pedidos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('pedido_detalles');

        // Seeds
        // Pisos
        $this->db->table('pisos')->insertBatch([
            ['nombre' => 'Piso 1', 'orden' => 1],
            ['nombre' => 'Piso 2', 'orden' => 2],
            ['nombre' => 'Piso 3', 'orden' => 3],
        ]);

        // Mesas (5 por piso como ejemplo)
        $mesas = [];
        for ($i = 1; $i <= 3; $i++) {
            for ($j = 1; $j <= 6; $j++) {
                $mesas[] = [
                    'nombre' => "Mesa $j",
                    'id_piso' => $i,
                    'x' => ($j - 1) * 120 + 20, // Posicionamiento simple horizontal
                    'y' => 50,
                    'estado' => 'libre'
                ];
            }
        }
        $this->db->table('mesas')->insertBatch($mesas);
    }

    public function down()
    {
        $this->forge->dropTable('pedido_detalles');
        $this->forge->dropTable('pedidos');
        $this->forge->dropTable('mesas');
        $this->forge->dropTable('pisos');
    }
}
