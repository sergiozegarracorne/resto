<?php

namespace App\Controllers\Api;

use App\Models\CategoriaModel;

class CategoriasApi extends BaseApiController
{
    public function get_all()
    {
        return $this->respond((new CategoriaModel())->orderBy('nombre', 'ASC')->findAll());
    }

    public function save()
    {
        $json = $this->request->getJSON(true);

        if (empty($json['nombre'])) {
            return $this->fail('El nombre es obligatorio');
        }

        $model  = new CategoriaModel();
        $campos = [
            'nombre' => trim($json['nombre']),
            'icono'  => trim($json['icono'] ?? ''),
            'estado' => isset($json['estado']) ? (int) $json['estado'] : 1,
        ];

        if (!empty($json['id'])) {
            $model->update($json['id'], $campos);
            return $this->respond(['success' => true, 'message' => 'Categoría actualizada']);
        }

        $id = $model->insert($campos);
        return $this->respond(['success' => true, 'id' => $id, 'message' => 'Categoría creada']);
    }

    public function delete()
    {
        $json = $this->request->getJSON(true);
        if (empty($json['id'])) return $this->fail('ID requerido');

        (new CategoriaModel())->delete($json['id']);
        return $this->respond(['success' => true]);
    }
}
