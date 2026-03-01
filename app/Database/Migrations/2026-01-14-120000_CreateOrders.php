<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOrders extends Migration
{
    protected $DBGroup = 'operaciones';

    public function up()
    {
        // 3. Tabla Pedidos
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_mesa' => ['type' => 'INT', 'constraint' => 5, 'unsigned' => true, 'null' => true],
            'id_usuario' => ['type' => 'INT', 'constraint' => 5, 'unsigned' => true],
            'total' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'estado' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pendiente'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            // 'uuid' => ['type' => 'VARCHAR', 'constraint' => 36, 'null' => true], // UUID para sincronización futura si es necesario
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
    }

    public function down()
    {
        $this->forge->dropTable('pedido_detalles');
        $this->forge->dropTable('pedidos');
    }
}
