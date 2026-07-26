<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class BaseApiController extends BaseController
{
    use ResponseTrait;

    protected function rolActual(): string
    {
        return session('usuario_turno')['rol'] ?? 'vendedor';
    }

    // supervisor, admin y sudo pueden cobrar/cancelar/gestionar
    protected function puedeAdmin(): bool
    {
        return in_array($this->rolActual(), ['supervisor', 'admin', 'sudo'], true);
    }

    // solo admin y sudo pueden gestionar usuarios y ver caja
    protected function puedeGestionar(): bool
    {
        return in_array($this->rolActual(), ['admin', 'sudo'], true);
    }
}
