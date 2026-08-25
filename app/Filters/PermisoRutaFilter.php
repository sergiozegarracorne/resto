<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class PermisoRutaFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $sesion = session('usuario_turno');
        $rol    = $sesion['rol'] ?? null;

        if (!$rol) return; // sin sesión activa, no bloquear

        $uri = ltrim($request->getUri()->getPath(), '/');
        if ($uri === '') $uri = '/'; // home nunca se bloquea desde la tabla

        try {
            $db      = db_connect();
            $rutaReg = $db->table('rutas')->where('ruta', $uri)->get()->getRowArray();
            if (!$rutaReg || (int)$rutaReg['activo'] === 0) return; // no registrada o inactiva → acceso libre

            $permiso = $db->table('rutas_permisos pr')
                ->join('roles r', 'r.id = pr.id_rol')
                ->where('r.nombre', $rol)
                ->where('pr.id_ruta', $rutaReg['id'])
                ->get()->getRowArray();

            if ($permiso && (int)$permiso['puede_ver'] === 0) {
                return redirect()->to(base_url('/'));
            }
        } catch (\Throwable $e) {
            // tabla aún no existe: no bloquear nada
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}
