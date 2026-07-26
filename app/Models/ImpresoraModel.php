<?php

namespace App\Models;

use CodeIgniter\Model;

class ImpresoraModel extends Model
{
    protected $table         = 'impresoras';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['nombre', 'ip', 'puerto', 'activo'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
