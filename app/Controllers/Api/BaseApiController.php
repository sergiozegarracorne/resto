<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class BaseApiController extends BaseController
{
    use ResponseTrait;

    protected function rolActual(): string
    {
        return session('usuario_turno')['rol'] ?? 'mozo';
    }

    // caja, administrador y sudo pueden cobrar/cancelar/gestionar
    protected function puedeAdmin(): bool
    {
        return in_array($this->rolActual(), ['caja', 'administrador', 'sudo'], true);
    }

    // solo administrador y sudo pueden gestionar usuarios y ver caja
    protected function puedeGestionar(): bool
    {
        return in_array($this->rolActual(), ['administrador', 'sudo'], true);
    }
}
