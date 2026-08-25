<?php

namespace App\Controllers;

class Roles extends BaseController
{
    public function index()
    {
        $sesion = session('usuario_turno');
        $rol    = $sesion['rol'] ?? 'mozo';

        if (!in_array($rol, ['administrador', 'sudo'], true)) {
            return redirect()->to('panel');
        }

        return view('roles/index', ['rol' => $rol]);
    }
}
