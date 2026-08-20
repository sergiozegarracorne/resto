<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RolesYPermisosBotones extends Migration
{
    protected $DBGroup = 'default';

    public function up()
    {
        // 1. Tabla maestra de roles
        $this->forge->addField([
            'id'             => ['type' => 'TINYINT', 'unsigned' => true, 'auto_increment' => true],
            'nombre'         => ['type' => 'VARCHAR',  'constraint' => 20,  'unique' => true],
            'nombre_display' => ['type' => 'VARCHAR',  'constraint' => 30],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('roles');

        $this->db->table('roles')->insertBatch([
            ['nombre' => 'mozo',          'nombre_display' => 'MOZO'],
            ['nombre' => 'caja',          'nombre_display' => 'CAJA'],
            ['nombre' => 'administrador', 'nombre_display' => 'ADMINISTRADOR'],
            ['nombre' => 'sudo',          'nombre_display' => 'SUDO'],
        ]);

        // 2. Tabla de permisos de botones de acción por rol
        $this->forge->addField([
            'id'        => ['type' => 'INT',     'unsigned' => true, 'auto_increment' => true],
            'id_rol'    => ['type' => 'TINYINT', 'unsigned' => true],
            'boton_key' => ['type' => 'VARCHAR', 'constraint' => 40],
            'activo'    => ['type' => 'TINYINT', 'constraint' => 1,  'default' => 1],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['id_rol', 'boton_key']);
        $this->forge->addForeignKey('id_rol', 'roles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('permisos_botones_accion');

        // Permisos por defecto
        $permisos = [];
        $botones  = ['corregir', 'mesas', 'comandas', 'gabeta', 'vendedores', 'opciones'];
        // mozo=1: sin gabeta ni opciones | caja=2,administrador=3,sudo=4: acceso completo
        $reglas = [
            1 => ['corregir' => 1, 'mesas' => 1, 'comandas' => 1, 'gabeta' => 0, 'vendedores' => 1, 'opciones' => 0],
            2 => ['corregir' => 1, 'mesas' => 1, 'comandas' => 1, 'gabeta' => 1, 'vendedores' => 1, 'opciones' => 1],
            3 => ['corregir' => 1, 'mesas' => 1, 'comandas' => 1, 'gabeta' => 1, 'vendedores' => 1, 'opciones' => 1],
            4 => ['corregir' => 1, 'mesas' => 1, 'comandas' => 1, 'gabeta' => 1, 'vendedores' => 1, 'opciones' => 1],
        ];
        foreach ($reglas as $idRol => $btns) {
            foreach ($btns as $key => $activo) {
                $permisos[] = ['id_rol' => $idRol, 'boton_key' => $key, 'activo' => $activo];
            }
        }
        $this->db->table('permisos_botones_accion')->insertBatch($permisos);

        // 3. Migrar columna rol en usuarios: ENUM → VARCHAR con nuevos nombres
        $this->db->query("ALTER TABLE usuarios MODIFY COLUMN rol VARCHAR(20) NOT NULL DEFAULT 'mozo'");
        $this->db->query("UPDATE usuarios SET rol = 'mozo'          WHERE rol = 'vendedor'");
        $this->db->query("UPDATE usuarios SET rol = 'caja'          WHERE rol = 'supervisor'");
        $this->db->query("UPDATE usuarios SET rol = 'administrador' WHERE rol = 'admin'");

        // 4. Agregar FK id_rol a usuarios
        $this->forge->addColumn('usuarios', [
            'id_rol' => ['type' => 'TINYINT', 'unsigned' => true, 'null' => true, 'after' => 'rol'],
        ]);
        $this->db->query('UPDATE usuarios u JOIN roles r ON r.nombre = u.rol SET u.id_rol = r.id');
        $this->db->query("ALTER TABLE usuarios MODIFY COLUMN id_rol TINYINT UNSIGNED NOT NULL DEFAULT 1");
        $this->db->query('ALTER TABLE usuarios ADD CONSTRAINT fk_usuarios_rol FOREIGN KEY (id_rol) REFERENCES roles(id)');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE usuarios DROP FOREIGN KEY fk_usuarios_rol');
        $this->db->query('ALTER TABLE usuarios DROP COLUMN id_rol');
        $this->db->query("ALTER TABLE usuarios MODIFY COLUMN rol ENUM('sudo','admin','supervisor','vendedor') NOT NULL DEFAULT 'vendedor'");
        $this->db->query("UPDATE usuarios SET rol = 'vendedor'   WHERE rol = 'mozo'");
        $this->db->query("UPDATE usuarios SET rol = 'supervisor' WHERE rol = 'caja'");
        $this->db->query("UPDATE usuarios SET rol = 'admin'      WHERE rol = 'administrador'");

        $this->forge->dropTable('permisos_botones_accion', true);
        $this->forge->dropTable('roles', true);
    }
}
