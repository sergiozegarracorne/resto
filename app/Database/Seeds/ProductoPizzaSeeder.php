<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\CategoriaModel;
use App\Models\ProductoModel;

class ProductoPizzaSeeder extends Seeder
{
    public function run()
    {
        $categoriaModel = new CategoriaModel();
        $productoModel = new ProductoModel();

        // Buscar categoria Pizzas
        $pizzaCat = $categoriaModel->where('nombre', 'Pizzas')->first();

        if (!$pizzaCat) {
            // Si no existe, crearla (fallback)
            $catId = $categoriaModel->insert(['nombre' => 'Pizzas', 'icono' => '🍕']);
        } else {
            $catId = $pizzaCat['id'];
        }

        // Productos de Pizza para sembrar
        $pizzas = [
            [
                'nombre' => 'Pizza Americana',
                'precio' => 45.00,
                'imagen' => 'https://cdn-icons-png.flaticon.com/512/3132/3132693.png'
            ],
            [
                'nombre' => 'Pizza Pepperoni',
                'precio' => 48.00,
                'imagen' => 'https://cdn-icons-png.flaticon.com/512/3595/3595455.png'
            ],
            [
                'nombre' => 'Pizza Hawaiana',
                'precio' => 42.00,
                'imagen' => 'https://cdn-icons-png.flaticon.com/512/3595/3595453.png'
            ],
            [
                'nombre' => 'Pizza Suprema',
                'precio' => 55.00,
                'imagen' => 'https://cdn-icons-png.flaticon.com/512/6978/6978255.png'
            ],
            [
                'nombre' => 'Pizza Vegetariana',
                'precio' => 40.00,
                'imagen' => 'https://cdn-icons-png.flaticon.com/512/1404/1404945.png'
            ],
            [
                'nombre' => 'Pizza Carnivora',
                'precio' => 58.00,
                'imagen' => 'https://cdn-icons-png.flaticon.com/512/706/706934.png'
            ],
            [
                'nombre' => 'Pizza 4 Quesos',
                'precio' => 50.00,
                'imagen' => 'https://cdn-icons-png.flaticon.com/512/3595/3595458.png'
            ],
            [
                'nombre' => 'Pizza Continental',
                'precio' => 47.00,
                'imagen' => 'https://cdn-icons-png.flaticon.com/512/6978/6978255.png'
            ],
        ];

        foreach ($pizzas as $pizza) {
            // Verificar si ya existe para no duplicar masivamente si se corre varias veces
            $existing = $productoModel->where('nombre', $pizza['nombre'])->first();

            if (!$existing) {
                $productoModel->insert([
                    'categoria_id' => $catId,
                    'nombre' => $pizza['nombre'],
                    'precio' => $pizza['precio'],
                    'imagen' => $pizza['imagen'],
                    'es_combo' => false
                ]);
            }
        }

        // --- CREAR UN COMBO DE EJEMPLO (Menu Pizza) ---
        // 1. Crear Productos "Hijos" (Bebida) si no existe
        $bebidaCat = $categoriaModel->where('nombre', 'Bebidas')->first();
        if (!$bebidaCat) {
            $bebidaCatId = $categoriaModel->insert(['nombre' => 'Bebidas', 'icono' => '🥤']);
        } else {
            $bebidaCatId = $bebidaCat['id']; // Usar 'id' correcto
        }

        $coca = $productoModel->where('nombre', 'Coca Cola 1.5L')->first();
        if (!$coca) {
            $cocaId = $productoModel->insert([
                'categoria_id' => $bebidaCatId,
                'nombre' => 'Coca Cola 1.5L',
                'precio' => 10.00,
                'imagen' => 'https://cdn-icons-png.flaticon.com/512/2722/2722527.png',
                'es_combo' => false
            ]);
        } else {
            $cocaId = $coca['id'];
        }

        // 2. Crear el Producto Padre (El Combo)
        $comboNombre = 'Menu Pizza Familiar';
        $combo = $productoModel->where('nombre', $comboNombre)->first();

        if (!$combo) {
            $comboId = $productoModel->insert([
                'categoria_id' => $catId, // En categoría Pizzas o Promociones
                'nombre' => $comboNombre,
                'precio' => 60.00, // Precio especial
                'imagen' => 'https://cdn-icons-png.flaticon.com/512/3075/3075977.png',
                'es_combo' => true
            ]);

            // 3. Vincular componentes
            $componenteModel = new \App\Models\ProductoComponenteModel();

            // Incluye 1 Pizza Americana (Busquemos la ID de arriba)
            $pizzaAmericana = $productoModel->where('nombre', 'Pizza Americana')->first();
            if ($pizzaAmericana) {
                $componenteModel->insert([
                    'producto_padre_id' => $comboId,
                    'producto_hijo_id' => $pizzaAmericana['id'],
                    'cantidad' => 1
                ]);
            }

            // Incluye 1 Coca Cola
            $componenteModel->insert([
                'producto_padre_id' => $comboId,
                'producto_hijo_id' => $cocaId,
                'cantidad' => 1
            ]);
        }
    }
}
