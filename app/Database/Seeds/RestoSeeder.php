<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RestoSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        // 1. Insertar Categorías
        $catData = [
            ['nombre' => 'Pollos'],
            ['nombre' => 'Parrillas'],
            ['nombre' => 'Bebidas'],
            ['nombre' => 'Menú'],
        ];
        $db->table('categorias')->insertBatch($catData);

        // 2. Insertar Productos Base (Simples)
        $prodData = [
            ['categoria_id' => 1, 'nombre' => '1/4 de Pollo', 'precio' => 15.00, 'es_combo' => false],
            ['categoria_id' => 3, 'nombre' => 'Chicha Morada', 'precio' => 5.00, 'es_combo' => false],
            ['categoria_id' => 3, 'nombre' => 'Gaseosa Inka Cola', 'precio' => 4.00, 'es_combo' => false],
            ['categoria_id' => 4, 'nombre' => 'Entrada: Sopa de Morón', 'precio' => 0.00, 'es_combo' => false],
            ['categoria_id' => 4, 'nombre' => 'Entrada: Ensalada Rusa', 'precio' => 0.00, 'es_combo' => false],
        ];
        $db->table('productos')->insertBatch($prodData);

        // 3. Insertar el Producto Combo (Menú del Día)
        $db->table('productos')->insert([
            'categoria_id' => 4,
            'nombre' => 'Menú Ejecutivo',
            'precio' => 12.00,
            'es_combo' => true
        ]);
        $menuId = $db->insertID();

        // 4. Vincular los Componentes al Menú Ejecutivo
        // El menú incluye: (Sopa o Ensalada) + (1/4 de Pollo) + (Chicha o Gaseosa)
        $componentes = [
            // El plato de fondo es fijo en este ejemplo de menú
            ['producto_padre_id' => $menuId, 'producto_hijo_id' => 1, 'es_opcional' => false, 'grupo_opcion' => 'fondo'],

            // Entradas opcionales
            ['producto_padre_id' => $menuId, 'producto_hijo_id' => 4, 'es_opcional' => true, 'grupo_opcion' => 'entrada'],
            ['producto_padre_id' => $menuId, 'producto_hijo_id' => 5, 'es_opcional' => true, 'grupo_opcion' => 'entrada'],

            // Bebidas opcionales
            ['producto_padre_id' => $menuId, 'producto_hijo_id' => 2, 'es_opcional' => true, 'grupo_opcion' => 'bebida'],
            ['producto_padre_id' => $menuId, 'producto_hijo_id' => 3, 'es_opcional' => true, 'grupo_opcion' => 'bebida'],
        ];
        $db->table('producto_componentes')->insertBatch($componentes);
    }
}