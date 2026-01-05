<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

class Api extends BaseController
{
    use ResponseTrait;

    public function time()
    {
        // Retorna timestamp en milisegundos y fecha formateada
        return $this->respond([
            'timestamp' => time() * 1000, // JS usa milisegundos
            'datetime' => date('Y-m-d H:i:s')
        ]);
    }
}
