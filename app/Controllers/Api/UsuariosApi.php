<?php

namespace App\Controllers\Api;

use App\Models\UsuarioModel;

class UsuariosApi extends BaseApiController
{
    public function get_all()
    {
        if (!$this->puedeGestionar()) {
            return $this->failForbidden('Acceso restringido');
        }
        try {
            $users = (new UsuarioModel())
                ->select('id_usuario, codigo, nombre, rol, imagen, estado, created_at')
                ->orderBy('nombre', 'ASC')
                ->findAll();
        } catch (\Exception $e) {
            // La columna 'codigo' aún no existe — usar instancia nueva para no acumular el select
            $users = (new UsuarioModel())
                ->select('id_usuario, nombre, rol, imagen, estado, created_at')
                ->orderBy('nombre', 'ASC')
                ->findAll();
            $users = array_map(fn($u) => ['codigo' => null] + $u, $users);
        }

        return $this->respond($users);
    }

    public function save()
    {
        if (!$this->puedeGestionar()) {
            return $this->failForbidden('Acceso restringido');
        }

        $json = $this->request->getJSON(true);

        if (empty($json['nombre'])) {
            return $this->fail('El nombre es obligatorio');
        }

        $model  = new UsuarioModel();
        $esNuevo = empty($json['id']);

        if ($esNuevo && empty($json['clave'])) {
            return $this->fail('La contraseña es obligatoria para nuevos usuarios');
        }

        $campos = [
            'codigo' => !empty($json['codigo']) ? strtoupper(trim($json['codigo'])) : null,
            'nombre' => trim($json['nombre']),
            'rol'    => $json['rol'] ?? 'mozo',
            'estado' => isset($json['estado']) ? (int) $json['estado'] : 1,
        ];

        if (!empty($json['clave'])) {
            $campos['clave'] = password_hash($json['clave'], PASSWORD_DEFAULT);
        }

        if ($esNuevo) {
            try {
                $id = $model->insert($campos);
            } catch (\Exception $e) {
                // Columna 'codigo' aún no existe en DB
                unset($campos['codigo']);
                $id = (new UsuarioModel())->insert($campos);
            }
            return $this->respond(['success' => true, 'id' => $id, 'message' => 'Usuario creado']);
        }

        // Verificar que no se quite el rol sudo si es el último
        if (isset($json['rol']) && $json['rol'] !== 'sudo') {
            $sudos  = $model->where('rol', 'sudo')->where('estado', 1)->where('id_usuario !=', $json['id'])->countAllResults();
            $actual = $model->find($json['id']);
            if ($actual && $actual['rol'] === 'sudo' && $sudos === 0) {
                return $this->fail('Debe existir al menos un usuario sudo activo');
            }
        }

        // Sincronizar id_rol con el nombre del rol
        $roleRow = db_connect()->table('roles')->where('nombre', $campos['rol'])->get()->getRowArray();
        if ($roleRow) {
            $campos['id_rol'] = $roleRow['id'];
        }

        try {
            $model->update($json['id'], $campos);
        } catch (\Exception $e) {
            // Columna 'codigo' aún no existe en DB
            unset($campos['codigo']);
            (new UsuarioModel())->update($json['id'], $campos);
        }
        return $this->respond(['success' => true, 'message' => 'Usuario actualizado']);
    }

    public function delete()
    {
        if (!$this->puedeGestionar()) {
            return $this->failForbidden('Acceso restringido');
        }

        $json = $this->request->getJSON(true);

        if (empty($json['id'])) {
            return $this->fail('ID requerido');
        }

        $model   = new UsuarioModel();
        $usuario = $model->find($json['id']);

        if (!$usuario) {
            return $this->failNotFound('Usuario no encontrado');
        }

        if (($usuario['rol'] ?? '') === 'sudo') {
            $otros = $model->where('rol', 'sudo')->where('estado', 1)->where('id_usuario !=', $json['id'])->countAllResults();
            if ($otros === 0) {
                return $this->fail('No puedes eliminar el único usuario sudo');
            }
        }

        $model->update($json['id'], ['estado' => 0]);
        return $this->respond(['success' => true, 'message' => 'Usuario desactivado']);
    }
}
