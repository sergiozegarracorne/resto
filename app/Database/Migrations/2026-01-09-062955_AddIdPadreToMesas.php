<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIdPadreToMesas extends Migration
{
    public function up()
    {
        $this->forge->addColumn('mesas', [
            'id_padre' => [
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => true,
                'null' => true,
                'default' => null,
            ],
        ]);
        $this->forge->addForeignKey('id_padre', 'mesas', 'id', 'SET NULL', 'CASCADE', 'fk_mesas_mesas_id_padre');
        $this->forge->processIndexes('mesas');
    }

    public function down()
    {
        $this->forge->dropForeignKey('mesas', 'fk_mesas_mesas_id_padre');
        $this->forge->dropColumn('mesas', 'id_padre');
    }
}
