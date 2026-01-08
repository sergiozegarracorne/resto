<?php

namespace App\Controllers;

use App\Models\UsuarioModel;

class Home extends BaseController
{
    public function index(int $id = 0): string
    {
        helper('components');
        $usuarioModel = new UsuarioModel();
        $data['usuarios'] = $usuarioModel->getActiveUsers();

        return view('home/index', $data);
    }

    public function ingress($id)
    {
        $usuarioModel = new UsuarioModel();
        // find() busca por primary key, CodeIgniter suele asumir 'id'. 
        // Si el modelo tiene $primaryKey='id_usuario' funcionará, si no, mejor usar where.
        $usuario = $usuarioModel->where('id_usuario', $id)->first();

        if ($usuario) {
            // Guardar en sesión
            session()->set('usuario_turno', [
                'id' => $usuario['id_usuario'],
                'nombre' => $usuario['nombre'],
                'rol' => $usuario['rol'] ?? 'Mesero'
            ]);

            // Redirigir a ventas
            return redirect()->to('/venta');
        }

        return redirect()->back();
    }
}
