<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTimestampsToCategorias extends Migration
{
    public function up()
    {
        $fieldsToAdd = [];

        if (!$this->db->fieldExists('created_at', 'categorias')) {
            $fieldsToAdd['created_at'] = ['type' => 'DATETIME', 'null' => true];
        }
        if (!$this->db->fieldExists('updated_at', 'categorias')) {
            $fieldsToAdd['updated_at'] = ['type' => 'DATETIME', 'null' => true];
        }
        if (!$this->db->fieldExists('deleted_at', 'categorias')) {
            $fieldsToAdd['deleted_at'] = ['type' => 'DATETIME', 'null' => true];
        }

        if (!empty($fieldsToAdd)) {
            $this->forge->addColumn('categorias', $fieldsToAdd);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('categorias', ['created_at', 'updated_at', 'deleted_at']);
    }
}
