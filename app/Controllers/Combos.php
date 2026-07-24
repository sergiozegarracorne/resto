<?php

namespace App\Controllers;

class Combos extends BaseController
{
    
    protected $categoriaModel;
    protected $productosModel;
    protected $usuarioModel;
    
    
    protected function init(){
       $this->categoriaModel = new \App\Models\CategoriaModel();
       $this->productosModel = new \App\Models\ProductoModel();
       $this->usuarioModel   = new \App\Models\UsuarioModel();
    }
    
    public function index()
    {
        helper('components');
 
        
        return view('combos/index');
    }
    
}
