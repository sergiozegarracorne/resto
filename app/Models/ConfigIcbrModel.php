<?php

namespace App\Models;

use CodeIgniter\Model;

class ConfigIcbrModel extends Model
{
    protected $table         = 'config_icbr';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['monto', 'fecha_inicio', 'fecha_fin'];
    protected $useTimestamps = false;

    public function vigente(): array|null
    {
        return $this->where('fecha_fin IS NULL', null, false)
                    ->orderBy('fecha_inicio', 'DESC')
                    ->first();
    }
}
