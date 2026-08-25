<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BotonesCatalogo extends Migration
{
    protected $DBGroup = 'default';

    public function up()
    {
        $this->forge->addField([
            'id'        => ['type' => 'TINYINT', 'unsigned' => true, 'auto_increment' => true],
            'boton_key' => ['type' => 'VARCHAR', 'constraint' => 40, 'unique' => true],
            'label'     => ['type' => 'VARCHAR', 'constraint' => 50],
            'icon'      => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => '🔘'],
            'tipo'      => ['type' => 'ENUM',    'constraint' => ["button", "link"], 'default' => 'link'],
            'onclick'   => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'href'      => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'color'     => ['type' => 'VARCHAR', 'constraint' => 20,  'default' => 'indigo'],
            'orden'     => ['type' => 'TINYINT', 'unsigned' => true,  'default' => 99],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('botones_catalogo');

        $this->db->table('botones_catalogo')->insertBatch([
            ['boton_key' => 'corregir',   'label' => 'Corregir',   'icon' => '❌', 'tipo' => 'button', 'onclick' => 'undoUltimaAccion()',    'href' => null,    'color' => 'red',     'orden' => 1],
            ['boton_key' => 'mesas',      'label' => 'Mesas',      'icon' => '🍽️', 'tipo' => 'button', 'onclick' => 'toggleMesasOverlay()', 'href' => null,    'color' => 'indigo',  'orden' => 2],
            ['boton_key' => 'comandas',   'label' => 'Comandas',   'icon' => '📋', 'tipo' => 'button', 'onclick' => 'showComandaModal()',   'href' => null,    'color' => 'amber',   'orden' => 3],
            ['boton_key' => 'gabeta',     'label' => 'Gabeta',     'icon' => '🎁', 'tipo' => 'button', 'onclick' => '',                     'href' => null,    'color' => 'emerald', 'orden' => 4],
            ['boton_key' => 'vendedores', 'label' => 'Vendedores', 'icon' => '👥', 'tipo' => 'link',   'onclick' => null,                   'href' => '/',     'color' => 'orange',  'orden' => 5],
            ['boton_key' => 'opciones',   'label' => 'Opciones',   'icon' => '⚙️', 'tipo' => 'link',   'onclick' => null,                   'href' => 'panel', 'color' => 'slate',   'orden' => 6],
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('botones_catalogo', true);
    }
}
