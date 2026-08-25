<?php

namespace App\Controllers\Api;

class RolesApi extends BaseApiController
{
    // ── Catálogo de roles ─────────────────────────────────────────────────────

    public function get_roles()
    {
        if (!$this->puedeGestionar()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Sin acceso']);
        }
        $roles = db_connect()->table('roles')->orderBy('id')->get()->getResultArray();
        return $this->response->setJSON(['roles' => $roles]);
    }

    public function save_rol()
    {
        if (!$this->puedeGestionar()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Sin acceso']);
        }
        $data   = $this->request->getJSON(true);
        $nombre = trim($data['nombre'] ?? '');
        if (!$nombre) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Nombre requerido']);
        }
        $db     = db_connect();
        $existe = $db->table('roles')->where('nombre', $nombre)->get()->getRowArray();
        if ($existe) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Ya existe un rol con ese nombre']);
        }
        $db->table('roles')->insert(['nombre' => $nombre]);
        return $this->response->setJSON(['ok' => true, 'id' => $db->insertID()]);
    }

    public function delete_rol()
    {
        if (!$this->puedeGestionar()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Sin acceso']);
        }
        $data = $this->request->getJSON(true);
        $id   = (int)($data['id'] ?? 0);
        if (!$id) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'ID requerido']);
        }
        db_connect()->table('roles')->where('id', $id)->delete();
        return $this->response->setJSON(['ok' => true]);
    }

    // ── Catálogo de botones ───────────────────────────────────────────────────

    public function get_botones()
    {
        if (!$this->puedeGestionar()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Sin acceso']);
        }

        try {
            $botones = db_connect()->table('botones_catalogo')->orderBy('orden')->get()->getResultArray();
        } catch (\Throwable $e) {
            $botones = [];
        }

        return $this->response->setJSON(['botones' => $botones]);
    }

    // ── Rutas del sistema (desde Config/Routes.php) ───────────────────────────

    public function get_rutas_sistema()
    {
        if (!$this->puedeGestionar()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Sin acceso']);
        }

        $rutas  = [];
        $source = @file_get_contents(APPPATH . 'Config/Routes.php');

        if ($source) {
            // Solo captura rutas GET con patrón que empieza con '/' (vistas, no API)
            preg_match_all(
                '/\$routes->get\s*\(\s*\'(\/[^\']*)\'\s*,\s*\'([^:]+)::/',
                $source,
                $matches,
                PREG_SET_ORDER
            );

            foreach ($matches as $m) {
                $patron = trim($m[1], '/');
                if (str_contains($patron, '(:') || str_contains($patron, '$1')) continue;

                $ruta    = $patron === '' ? '/' : $patron;
                $rutas[] = ['ruta' => $ruta, 'controlador' => trim($m[2])];
            }
        }

        usort($rutas, fn($a, $b) => strcmp($a['ruta'], $b['ruta']));
        return $this->response->setJSON(['rutas' => $rutas]);
    }

    // ── Catálogo de rutas (tabla `rutas`) ─────────────────────────────────────

    public function get_rutas()
    {
        if (!$this->puedeGestionar()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Sin acceso']);
        }

        try {
            $rutas = db_connect()->table('rutas')->orderBy('nombre')->get()->getResultArray();
        } catch (\Throwable $e) {
            $rutas = [];
        }

        return $this->response->setJSON(['rutas' => $rutas]);
    }

    public function save_ruta()
    {
        if (!$this->puedeGestionar()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Sin acceso']);
        }

        $data   = $this->request->getJSON(true);
        $id     = (int)($data['id']     ?? 0);
        $nombre = trim($data['nombre']  ?? '');
        $alias  = trim($data['alias']   ?? '');
        $ruta   = trim($data['ruta']    ?? '', "/ \t\n\r");

        if (!$nombre || !$ruta) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Nombre y ruta son obligatorios']);
        }

        $db    = db_connect();
        $tabla = $db->table('rutas');

        if ($id) {
            $tabla->where('id', $id)->update(['nombre' => $nombre, 'alias' => $alias, 'ruta' => $ruta]);
        } else {
            $existe = $tabla->where('ruta', $ruta)->get()->getRowArray();
            if ($existe) {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'Ya existe una ruta con ese path']);
            }
            $tabla->insert(['nombre' => $nombre, 'alias' => $alias, 'ruta' => $ruta, 'activo' => 1]);
            $id_ruta = $db->insertID();

            $roles = $db->table('roles')->get()->getResultArray();
            $batch = [];
            foreach ($roles as $r) {
                $batch[] = [
                    'id_rol'       => $r['id'],
                    'id_ruta'      => $id_ruta,
                    'puede_ver'    => 1,
                    'puede_editar' => 1,
                ];
            }
            if ($batch) {
                $db->table('rutas_permisos')->insertBatch($batch);
            }
        }

        return $this->response->setJSON(['ok' => true]);
    }

    public function toggle_ruta()
    {
        if (!$this->puedeGestionar()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Sin acceso']);
        }

        $data   = $this->request->getJSON(true);
        $id     = (int)($data['id']     ?? 0);
        $activo = (int)($data['activo'] ?? 1);

        if (!$id) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'ID requerido']);
        }

        db_connect()->table('rutas')->where('id', $id)->update(['activo' => $activo]);
        return $this->response->setJSON(['ok' => true]);
    }

    public function delete_ruta()
    {
        if (!$this->puedeGestionar()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Sin acceso']);
        }

        $data = $this->request->getJSON(true);
        $id   = (int)($data['id'] ?? 0);

        if (!$id) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'ID requerido']);
        }

        db_connect()->table('rutas')->where('id', $id)->delete();
        // FK CASCADE elimina los permisos asociados en rutas_permisos

        return $this->response->setJSON(['ok' => true]);
    }

    // ── Permisos de rutas ─────────────────────────────────────────────────────

    public function get_permisos_rutas()
    {
        if (!$this->puedeGestionar()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Sin acceso']);
        }

        $db    = db_connect();
        $roles = $db->table('roles')->orderBy('id')->get()->getResultArray();

        try {
            $rutasCat = $db->table('rutas')->orderBy('nombre')->get()->getResultArray();
            $filas    = $db->table('rutas_permisos')->get()->getResultArray();
        } catch (\Throwable $e) {
            return $this->response->setJSON(['roles' => $roles, 'rutas' => [], 'permisos' => []]);
        }

        // Mapa: id_ruta → id_rol → {ver, editar}
        $mapa = [];
        foreach ($filas as $f) {
            $mapa[(int)$f['id_ruta']][(int)$f['id_rol']] = [
                'ver'    => (int)$f['puede_ver'],
                'editar' => (int)($f['puede_editar'] ?? 1),
            ];
        }

        return $this->response->setJSON([
            'roles'    => $roles,
            'rutas'    => $rutasCat,
            'permisos' => $mapa,
        ]);
    }

    public function update_permiso_ruta()
    {
        if (!$this->puedeGestionar()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Sin acceso']);
        }

        $data    = $this->request->getJSON(true);
        $id_ruta = (int)($data['id_ruta'] ?? 0);
        $id_rol  = (int)($data['id_rol']  ?? 0);
        $campo   = trim($data['campo']    ?? '');
        $valor   = (int)($data['valor']   ?? 0);

        $camposValidos = ['puede_ver', 'puede_editar'];
        if (!$id_ruta || !$id_rol || !\in_array($campo, $camposValidos, true)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Datos inválidos']);
        }

        $tabla  = db_connect()->table('rutas_permisos');
        $existe = $tabla->where('id_rol', $id_rol)->where('id_ruta', $id_ruta)->get()->getRowArray();

        if ($existe) {
            $tabla->where('id_rol', $id_rol)->where('id_ruta', $id_ruta)->update([$campo => $valor]);
        } else {
            $fila = ['id_rol' => $id_rol, 'id_ruta' => $id_ruta, 'puede_ver' => 1, 'puede_editar' => 1];
            $fila[$campo] = $valor;
            $tabla->insert($fila);
        }

        return $this->response->setJSON(['ok' => true]);
    }

    // ── Catálogo de botones de la barra ───────────────────────────────────────

    public function save_boton()
    {
        if (!$this->puedeGestionar()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Sin acceso']);
        }

        $data = $this->request->getJSON(true);

        $botonKey = trim($data['boton_key'] ?? '');
        $label    = trim($data['label']     ?? '');
        $icon     = trim($data['icon']      ?? '🔘');
        $tipo     = \in_array($data['tipo'] ?? '', ['button', 'link']) ? $data['tipo'] : 'link';
        $onclick  = trim($data['onclick']   ?? '');
        $href     = trim($data['href']      ?? '');
        $color    = trim($data['color']     ?? 'indigo');
        $orden    = (int)($data['orden']    ?? 99);

        if (!$botonKey || !$label) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Clave y nombre son obligatorios']);
        }

        $colores = ['red', 'indigo', 'emerald', 'orange', 'amber', 'slate'];
        if (!\in_array($color, $colores, true)) $color = 'indigo';

        $db    = db_connect();
        $tabla = $db->table('botones_catalogo');
        $existe = $tabla->where('boton_key', $botonKey)->get()->getRowArray();

        $campos = [
            'label'   => $label,
            'icon'    => $icon,
            'tipo'    => $tipo,
            'onclick' => $tipo === 'button' ? $onclick : null,
            'href'    => $tipo === 'link'   ? $href    : null,
            'color'   => $color,
            'orden'   => $orden,
        ];

        if ($existe) {
            $tabla->where('boton_key', $botonKey)->update($campos);
        } else {
            $campos['boton_key'] = $botonKey;
            $tabla->insert($campos);
        }

        return $this->response->setJSON(['ok' => true]);
    }

    public function delete_boton()
    {
        if (!$this->puedeGestionar()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Sin acceso']);
        }

        $data     = $this->request->getJSON(true);
        $botonKey = trim($data['boton_key'] ?? '');
        if (!$botonKey) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Clave requerida']);
        }

        db_connect()->table('botones_catalogo')->where('boton_key', $botonKey)->delete();

        return $this->response->setJSON(['ok' => true]);
    }
}
