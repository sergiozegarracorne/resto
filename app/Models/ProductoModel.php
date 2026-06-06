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
}
