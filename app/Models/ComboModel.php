<?php

namespace App\Models;

use CodeIgniter\Model;

class ComboModel extends Model
{
 
    public function deleteCombo(int $idCombo): bool
    {
        $db = $this->db;
    
        $db->transBegin();
    
        try {
    
            $detalleModel = new ProductoComboDetalleModel();
            $productoModel = new ProductoModel();
 

            // Desactivar detalles
             $detalleModel
                ->where('id_producto_combo', $idCombo)
                ->set(['estado' => 'DESACTIVADO'])
                ->update();
    
            // Desactivar el combo
            $productoModel
                ->where('id',$idCombo)
                ->set(['activo'=>0])
                ->update();
            
            
            if ($db->transStatus() === false) {
                $db->transRollback();
                return false;
            }
    
            $db->transCommit();
    
            return true;
    
        } catch (\Throwable $e) {
    
            $db->transRollback();
    
            log_message('error', 'Error al eliminar combo: ' . $e->getMessage());
    
            return false;
        }
    }

 ## UPDATE COMBO
   public function updateCombo($data){
    
//    echo "<pre>";
//    print_r($data->id);
//    echo "</pre>";exit;
       $db = $this->db;
        
            $db->transBegin();
        
            try {
        
                //$detalleModel = new ProductoComboDetalleModel();
                $productoModel = new ProductoModel();
                $idCombo = $data->id;
    
                // Desactivar detalles
                 $productoModel
                    ->where('id', $idCombo)
                    ->set(['nombre' => $data->nombre])
                    ->set(['precio' => $data->precio])
                    ->update();
        
               
                if ($db->transStatus() === false) {
                    $db->transRollback();
                    return false;
                }
        
                $db->transCommit();
        
                return true;
        
            } catch (\Throwable $e) {
        
                $db->transRollback();
        
                log_message('error', 'Error al actualizar combo: ' . $e->getMessage());
        
                return false;
            } 
    
    
   }
  

}

?>