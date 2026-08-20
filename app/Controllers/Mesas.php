<?php

namespace App\Controllers;

class Mesas extends BaseController
{
    public function index()
    {
        $rol = session('usuario_turno')['rol'] ?? '';
        if (!in_array($rol, ['administrador', 'sudo'], true)) {
            return redirect()->to(base_url('panel'));
        }
        return view('mesas/index');
    }
}
