<?php

namespace App\Controllers\Api;

use App\Models\InsumoModel;
use App\Models\CompraModel;
use App\Models\CompraDetalleModel;

class AlmacenApi extends BaseApiController
{
    public function get_insumos()
    {
        $model = new InsumoModel();
        return $this->respond($model->orderBy('nombre', 'ASC')->findAll());
    }

    public function save_insumo()
    {
        $json = $this->request->getJSON(true);

        if (empty($json['nombre']) || empty($json['unidad'])) {
            return $this->fail('Nombre y unidad son obligatorios');
        }

        $model = new InsumoModel();

        if (!empty($json['id'])) {
            $model->update($json['id'], [
                'nombre'       => $json['nombre'],
                'unidad'       => $json['unidad'],
                'stock_minimo' => $json['stock_minimo'] ?? 0,
            ]);
            return $this->respond(['success' => true, 'message' => 'Insumo actualizado']);
        }

        $id = $model->insert([
            'nombre'       => $json['nombre'],
            'unidad'       => $json['unidad'],
            'stock_actual' => 0,
            'stock_minimo' => $json['stock_minimo'] ?? 0,
        ]);

        return $this->respond(['success' => true, 'id' => $id, 'message' => 'Insumo creado']);
    }

    public function save_compra()
    {
        $json = $this->request->getJSON(true);

        if (empty($json['items']) || !is_array($json['items'])) {
            return $this->fail('Debes agregar al menos un producto a la compra');
        }

        $compraModel  = new CompraModel();
        $detalleModel = new CompraDetalleModel();
        $insumoModel  = new InsumoModel();
        $dbOp         = \Config\Database::connect('operaciones');

        $dbOp->transStart();

        $idCompra = $compraModel->insert([
            'proveedor' => $json['proveedor'] ?? null,
            'total'     => $json['total']     ?? 0,
            'notas'     => $json['notas']     ?? null,
        ]);

        foreach ($json['items'] as $item) {
            $detalleModel->insert([
                'id_compra'       => $idCompra,
                'id_insumo'       => $item['id_insumo'],
                'nombre_insumo'   => $item['nombre'],
                'unidad'          => $item['unidad'],
                'cantidad'        => $item['cantidad'],
                'precio_unitario' => $item['precio_unitario'],
                'subtotal'        => $item['cantidad'] * $item['precio_unitario'],
            ]);

            $insumoModel->sumarStock((int) $item['id_insumo'], (float) $item['cantidad']);
        }

        $dbOp->transComplete();

        if ($dbOp->transStatus() === false) {
            return $this->failServerError('Error al registrar la compra');
        }

        return $this->respond([
            'success'   => true,
            'message'   => 'Compra registrada correctamente',
            'id_compra' => $idCompra,
        ]);
    }
}
