<?php

namespace App\Controllers;

class Ventas extends BaseController
{
    public function index()
    {
        $turno = session('usuario_turno');
        $rol   = $turno['rol'] ?? 'vendedor';
        if (!in_array($rol, ['admin', 'sudo', 'supervisor'], true)) {
            return redirect()->to(base_url('panel'));
        }
        return view('ventas/index', ['rol' => $rol]);
    }
}
