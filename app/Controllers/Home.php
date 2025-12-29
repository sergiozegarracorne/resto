<?php

namespace App\Controllers;

use App\Models\UsuarioModel;

class Home extends BaseController
{
    public function index(): string
    {
        helper('components');
        $usuarioModel = new UsuarioModel();
        $data['usuarios'] = $usuarioModel->where('estado', 1)->findAll();

        return view('home/index', $data);
    }

    public function componentes(): string
    {
        return view('components/action_button');
    }
}
