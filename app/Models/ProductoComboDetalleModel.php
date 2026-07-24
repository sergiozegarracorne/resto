<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductoComboDetalleModel extends Model
{
    protected $table      = 'producto_combo_detalle';
    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $allowedFields = [
        'id_producto_combo',
        'id_producto',
        'cantidad',
        'precio',
        'estado'
    ];

    protected $useTimestamps = true;

    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}