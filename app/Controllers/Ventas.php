<?php

namespace App\Controllers;

class Ventas extends BaseController
{
    public function index()
    {
        $turno = session('usuario_turno');
        $rol   = $turno['rol'] ?? 'mozo';
        if (!in_array($rol, ['administrador', 'sudo', 'caja'], true)) {
            return redirect()->to(base_url('panel'));
        }
        return view('ventas/index', ['rol' => $rol]);
    }
}
