<?php

namespace App\Controllers;

class Venta extends BaseController
{
    public function index()
    {
        helper('components');

        $categoriaModel = new \App\Models\CategoriaModel();
        $productoModel = new \App\Models\ProductoModel();

        // Cargar Categorias
        $categorias = $categoriaModel->where('estado', 1)->findAll();

        // Cargar Productos (Inicialmente todos o los de la primera categoría)
        // Por ahora cargamos todos para ver que funciona, el filtro será JS luego
        $productos = $productoModel->findAll();

        $data = [
            'categorias' => $categorias,
            'productos' => $productos // Ahora vienen de la BD
        ];

        return view('venta/index', $data);
    }
}
