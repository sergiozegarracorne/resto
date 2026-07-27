<?php

namespace App\Controllers\Api;

use App\Models\ProductoModel;
use App\Models\CategoriaModel;

class ProductosApi extends BaseApiController
{
    public function get_all()
    {
        $productos  = (new ProductoModel())->orderBy('nombre', 'ASC')->findAll();
        $categorias = (new CategoriaModel())->where('estado', 1)->orderBy('nombre', 'ASC')->findAll();

        return $this->respond(['productos' => $productos, 'categorias' => $categorias]);
    }

    public function save()
    {
        $json = $this->request->getJSON(true);

        if (empty($json['nombre']) || !isset($json['precio'])) {
            return $this->fail('Nombre y precio son obligatorios');
        }

        $model = new ProductoModel();

        $campos = [
            'categoria_id'     => $json['categoria_id']     ?? null,
            'nombre'           => trim($json['nombre']),
            'precio'           => (float) $json['precio'],
            'precio_costo'     => isset($json['precio_costo']) && $json['precio_costo'] !== '' ? (float) $json['precio_costo'] : null,
            'tipo_igv'         => $json['tipo_igv']         ?? 'gravado',
            'aplica_icbr'      => !empty($json['aplica_icbr'])  ? 1 : 0,
            'es_combo'         => !empty($json['es_combo'])     ? 1 : 0,
            'activo'           => isset($json['activo'])    ? (int) $json['activo']    : 1,
            'stock_minimo'     => isset($json['stock_minimo'])  ? (float) $json['stock_minimo']  : 0,
            'stock_maximo'     => isset($json['stock_maximo']) && $json['stock_maximo'] !== '' && $json['stock_maximo'] !== null ? (float) $json['stock_maximo'] : null,
            'fecha_vencimiento'=> ($json['fecha_vencimiento'] ?? '') ?: null,
            'descripcion'      => ($json['descripcion']      ?? '') ?: null,
            'imagen'           => ($json['imagen']           ?? '') ?: null,
            'ingredientes'     => ($json['ingredientes']     ?? '') ?: null,
        ];

        if (!empty($json['id'])) {
            $model->update($json['id'], $campos);
            return $this->respond(['success' => true, 'message' => 'Producto actualizado']);
        }

        $id = $model->insert($campos);
        return $this->respond(['success' => true, 'id' => $id, 'message' => 'Producto creado']);
    }

    public function delete()
    {
        $json = $this->request->getJSON(true);

        if (empty($json['id'])) {
            return $this->fail('ID requerido');
        }

        (new ProductoModel())->delete($json['id']);
        return $this->respond(['success' => true, 'message' => 'Producto eliminado']);
    }
}
