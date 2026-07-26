<?php

namespace App\Controllers;

class Panel extends BaseController
{
    public function index()
    {
        // Cachear la vista del Panel por 1 hora (3600 segundos)
        // Esto hace que la respuesta sea instantánea desde el disco
        $turno = session('usuario_turno');
        $rol   = $turno['rol'] ?? 'vendedor';
        return view('panel/index', ['rol' => $rol]);
    } 
}
