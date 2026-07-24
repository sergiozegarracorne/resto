<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductoModel extends Model
{
    protected $table         = 'productos';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'categoria_id', 'nombre', 'codigo_rapido', 'descripcion',
        'precio', 'precio_costo', 'precio_libre', 'precio_max',
        'imagen', 'es_combo', 'por_peso',
        'tipo_igv', 'aplica_icbr',
        'fecha_vencimiento', 'stock_minimo', 'stock_maximo',
        'activo', 'orden',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function proximosAVencer(int $dias = 30): array
    {
        $limite = date('Y-m-d', strtotime("+{$dias} days"));
        return $this->where('activo', 1)
                    ->where('fecha_vencimiento IS NOT NULL', null, false)
                    ->where('fecha_vencimiento <=', $limite)
                    ->orderBy('fecha_vencimiento', 'ASC')
                    ->findAll();
    }
 
 ## GUARDAR COMBO
 
 public function guardarCombo(array $data, array $productos): int|false
    {
        $db = $this->db;
    
        $db->transStart();
    
        // Guardar producto (combo)
        $this->insert([
            'categoria_id' => $data['categoria_id'],
            'nombre'       => $data['nombre'],
            'codigo_rapido'=> $data['codigo_rapido'],
            'descripcion'  => $data['descripcion'],
            'precio'       => $data['precio'],
            'es_combo'     => 1,
            'activo'       => 1
        ]);
    
        $idCombo = $this->getInsertID();
    
        $detalleModel = new ProductoComboDetalleModel();
    
        foreach ($productos as $prod) {
    
            $detalleModel->insert([
                'id_producto_combo' => $idCombo,
                'id_producto'       => $prod->id,
                'cantidad'          => $prod->cantidad,
                'precio'            => $prod->precio
            ]);
        }
    
        $db->transComplete();
    
        if (!$db->transStatus()) {
            return false;
        }
    
        return $idCombo;
    } 
    
   
}
