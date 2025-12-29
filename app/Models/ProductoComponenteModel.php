<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductoComponenteModel extends Model
{
    protected $table = 'producto_componentes';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = ['producto_padre_id', 'producto_hijo_id', 'cantidad', 'es_opcional', 'grupo_opcion'];
}
