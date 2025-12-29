<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\UsuarioModel;

class UsuarioSeeder extends Seeder
{
    public function run()
    {
        $users = [
            [
                'nombre' => 'Super Admin',
                'clave' => password_hash('123456', PASSWORD_DEFAULT),
                'rol' => 'sudo',
                'imagen' => 'https://cdn-icons-png.flaticon.com/512/2922/2922510.png',
                'estado' => 1,
            ],
            [
                'nombre' => 'Administrador',
                'clave' => password_hash('123456', PASSWORD_DEFAULT),
                'rol' => 'admin',
                'imagen' => 'https://cdn-icons-png.flaticon.com/512/2922/2922506.png',
                'estado' => 1,
            ],
            [
                'nombre' => 'Supervisor Tienda',
                'clave' => password_hash('1234', PASSWORD_DEFAULT),
                'rol' => 'supervisor',
                'imagen' => 'https://cdn-icons-png.flaticon.com/512/2922/2922561.png',
                'estado' => 1,
            ],
            [
                'nombre' => 'Juan Vendedor',
                'clave' => password_hash('1234', PASSWORD_DEFAULT),
                'rol' => 'vendedor',
                'imagen' => 'https://cdn-icons-png.flaticon.com/512/4140/4140048.png',
                'estado' => 1,
            ],
            [
                'nombre' => 'Maria Vendedora',
                'clave' => password_hash('1234', PASSWORD_DEFAULT),
                'rol' => 'vendedor',
                'imagen' => 'https://cdn-icons-png.flaticon.com/512/4140/4140047.png',
                'estado' => 1,
            ],
            [
                'nombre' => 'Usuario Invitado',
                'clave' => password_hash('0000', PASSWORD_DEFAULT),
                'rol' => 'observador',
                'imagen' => 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png',
                'estado' => 1,
            ],
        ];

        // Add 13 random users for testing
        for ($i = 1; $i <= 13; $i++) {
            $users[] = [
                'nombre' => 'Vendedor Extra ' . $i,
                'clave' => password_hash('0000', PASSWORD_DEFAULT),
                'rol' => 'vendedor',
                'imagen' => 'https://cdn-icons-png.flaticon.com/512/847/847969.png',
                'estado' => 1,
            ];
        }

        // Using Model to insert ensuring validation if added later
        $model = new UsuarioModel();

        // Clear table to avoid duplicates when re-seeding
        $this->db->table('usuarios')->truncate();

        foreach ($users as $user) {
            $model->insert($user);
        }
    }
}
