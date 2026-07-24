<?php

namespace App\Controllers\Api;

use App\Models\ComboModel;
use App\Models\ComboDetalleModel;
use App\Models\ProductoModel;

class CombosApi extends BaseApiController
{
    public function guardarCombo()
    {
        $json = $this->request->getJSON();
        $productoModel = new ProductoModel();

        $idCombo = $productoModel->guardarCombo(
            [
                'categoria_id' => 1,
                'nombre'       => $json->nombre,
                'codigo_rapido'=> 'COMB001',
                'descripcion'  => null,
                'precio'       => $json->precio
            ],
            $json->productos
        );
        
        if ($idCombo === false) {
            return $this->failServerError('Error al guardar el combo');
        }
        
        return $this->respond([
            'success' => true,
            'id_combo' => $idCombo
        ]);
    }


## ES PARA VER LOS COMBOS CREADOS    
    public function get_all()
        {
            $productoModel = new \App\Models\ProductoModel();
        
            $combos = $productoModel
                ->where('es_combo', 1)
                ->where('activo',1)                
                ->findAll();
        
            return $this->respond([
                'success' => true,
                'combos'  => $combos
            ]);
        }    
        
 

## ELIMINAR COMBO
   public function delete_combos($id)
    {
        $comboModel = new ComboModel();
        $comboModel->deleteCombo($id);
        
    
        return $this->response->setJSON([
            'success' => true
        ]); 
    }   
    

## UPDATE COMBO
    public function updateCombo()
    {
         $json = $this->request->getJSON();
         $comboModel = new ComboModel();
         $comboModel->updateCombo($json);
        
    
        return $this->response->setJSON([
            'success' => true
        ]);
        

    }      
    
        
    
}