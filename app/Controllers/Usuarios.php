<?php

namespace App\Controllers;

class Usuarios extends BaseController
{
    public function index()
    {
        return view('usuarios/index');
    }
}
