<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    public function run()
    {
        $model = model('CategoriaModel');

        // Limpiar tabla antes de seedear (Desactivando FK checks temporalmente)
        $this->db->query('SET FOREIGN_KEY_CHECKS=0;');
        $this->db->table('categorias')->truncate();
        $this->db->query('SET FOREIGN_KEY_CHECKS=1;');

        $categorias = [
            ['nombre' => 'Hamburguesas', 'icono' => '🍔'],
            ['nombre' => 'Pizzas', 'icono' => '🍕'],
            ['nombre' => 'Bebidas', 'icono' => '🥤'],
            ['nombre' => 'Postres', 'icono' => '🍰'],
            ['nombre' => 'Pollos', 'icono' => '🍗'],
            ['nombre' => 'Entradas', 'icono' => '🍟'],
            ['nombre' => 'Ensaladas', 'icono' => '🥗'],
            ['nombre' => 'Cafetería', 'icono' => '☕'],
            ['nombre' => 'Helados', 'icono' => '🍦'],
            ['nombre' => 'Carnes', 'icono' => '🥩'],
            ['nombre' => 'Pastas', 'icono' => '🍝'],
            ['nombre' => 'Sopas', 'icono' => '🍜'],
            ['nombre' => 'Desayunos', 'icono' => '🥞'],
            ['nombre' => 'Vinos', 'icono' => '🍷'],
            ['nombre' => 'Cervezas', 'icono' => '🍺'],
            ['nombre' => 'Promociones', 'icono' => '⭐'],
            ['nombre' => 'Sandwiches', 'icono' => '🥪'],
            ['nombre' => 'Mariscos', 'icono' => '🦐'],
        ];

        foreach ($categorias as $cat) {
            $model->insert($cat);
        }
    }
}
