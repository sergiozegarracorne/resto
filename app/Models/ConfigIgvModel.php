<?php

namespace App\Models;

use CodeIgniter\Model;

class ConfigIgvModel extends Model
{
    protected $table         = 'config_igv';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['porcentaje', 'fecha_inicio', 'fecha_fin'];
    protected $useTimestamps = false;

    public function vigente(): array|null
    {
        return $this->where('fecha_fin IS NULL', null, false)
                    ->orderBy('fecha_inicio', 'DESC')
                    ->first();
    }

    public function enFecha(string $fecha): array|null
    {
        return $this->where('fecha_inicio <=', $fecha)
                    ->groupStart()
                        ->where('fecha_fin IS NULL', null, false)
                        ->orWhere('fecha_fin >=', $fecha)
                    ->groupEnd()
                    ->orderBy('fecha_inicio', 'DESC')
                    ->first();
    }
}
