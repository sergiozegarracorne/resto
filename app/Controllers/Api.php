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
    public function verificar_vendedor()
    {
        $json = $this->request->getJSON();

        if (!$json || !isset($json->id_usuario) || !isset($json->clave)) {
            return $this->failValidationError('Datos incompletos');
        }

        $model = new \App\Models\UsuarioModel();
        $usuario = $model->find($json->id_usuario);

        if ($usuario && password_verify($json->clave, $usuario['clave'])) {
            // Login exitoso: Establecer sesión
            session()->set('usuario_turno', [
                'id' => $usuario['id_usuario'],
                'nombre' => $usuario['nombre'],
                'rol' => $usuario['rol'] ?? 'Mesero'
            ]);

            return $this->respond([
                'success' => true,
                'message' => 'Acceso autorizado'
            ]);
        }

        return $this->respond([
            'success' => false,
            'message' => 'Clave incorrecta'
        ]);
    }
}
