<?php

namespace App\Controllers;

class Venta extends BaseController
{
    public function index($idUsuario = 0)
    {
        helper('components');

        $categoriaModel = new \App\Models\CategoriaModel();
        $productoModel = new \App\Models\ProductoModel();
        $usuarioModel = new \App\Models\UsuarioModel();

        // Si viene un ID en la URL, loguear a ese usuario (Cambio rápido de turno)
        if ($idUsuario > 0) {
            $usuario = $usuarioModel->where('id_usuario', $idUsuario)->first();
            if ($usuario) {
                session()->set('usuario_turno', [
                    'id' => $usuario['id_usuario'],
                    'nombre' => $usuario['nombre'],
                    'rol' => $usuario['rol'] ?? 'Mesero'
                ]);
            }
        }

        // Cargar Categorias
        $categorias = $categoriaModel->where('estado', 1)->findAll();

        // Cargar Productos
        $productos = $productoModel->findAll();

        // Cargar Usuarios
        // Podríamos usar getActiveUsers() si queremos seguridad aquí también
        $usuarios = $usuarioModel->getActiveUsers();

        $data = [
            'categorias' => $categorias,
            'productos' => $productos,
            'usuarios' => $usuarios
        ];

        // Cachear también la vista de ventas
        //$this->cachePage(3600);
        return view('venta/index', $data);
    }
}
