<?php

namespace App\Controllers;

use App\Models\InsumoModel;

class Almacen extends BaseController
{
    public function compras()
    {
        $insumoModel = new InsumoModel();

        $data = [
            'insumos' => $insumoModel->orderBy('nombre', 'ASC')->findAll(),
        ];

        return view('almacen/compras', $data);
    }

    public function insumos()
    {
        $insumoModel = new InsumoModel();

        $data = [
            'insumos' => $insumoModel->orderBy('nombre', 'ASC')->findAll(),
        ];

        return view('almacen/insumos', $data);
    }
}
