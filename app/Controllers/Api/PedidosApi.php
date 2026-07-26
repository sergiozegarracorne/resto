<?php

namespace App\Controllers\Api;

use App\Models\PedidoOperacionesModel;
use App\Models\PedidoDetalleOperacionesModel;
use App\Models\MesaModel;
use App\Libraries\ImpresoraService;

class PedidosApi extends BaseApiController
{
    public function get_mesa_pedido($idMesa)
    {
        $pedidoModel  = new PedidoOperacionesModel();
        $detalleModel = new PedidoDetalleOperacionesModel();

        $pedido = $pedidoModel->where('id_mesa', $idMesa)->where('estado', 'pendiente')->first();

        if (!$pedido) {
            return $this->respond(['success' => false, 'message' => 'Mesa libre']);
        }

        $detalles = $detalleModel->where('id_pedido', $pedido['id'])->findAll();

        $items = array_map(fn($d) => [
            'id'       => $d['id_producto'],
            'nombre'   => $d['nombre_producto'],
            'precio'   => (float) $d['precio'],
            'cantidad' => (int)   $d['cantidad'],
            'subtotal' => (float) $d['precio'] * (int) $d['cantidad'],
        ], $detalles);

        return $this->respond(['success' => true, 'pedido' => $pedido, 'items' => $items]);
    }

    public function save_pedido()
    {
        $json = $this->request->getJSON();

        if (!isset($json->id_mesa) || !isset($json->productos)) {
            return $this->failValidationError('Datos incompletos');
        }

        $idMesa    = $json->id_mesa;
        $productos = $json->productos;
        $idUsuario = session('usuario_turno')['id'] ?? 1;

        $pedidoModel  = new PedidoOperacionesModel();
        $detalleModel = new PedidoDetalleOperacionesModel();
        $mesaModel    = new MesaModel();

        $db = \Config\Database::connect('operaciones');
        $db->transStart();

        $pedido    = $pedidoModel->where('id_mesa', $idMesa)->where('estado', 'pendiente')->first();
        $idPedido  = null;
        $esPedidoNuevo = !$pedido; // solo enviamos a cocina en pedidos nuevos

        if (!$pedido) {
            $idPedido = $pedidoModel->insert([
                'id_mesa'    => $idMesa,
                'id_usuario' => $idUsuario,
                'total'      => 0,
                'estado'     => 'pendiente',
            ]);
            $mesaModel->update($idMesa, ['estado' => 'ocupada']);
        } else {
            $idPedido = $pedido['id'];
            $detalleModel->where('id_pedido', $idPedido)->delete();
        }

        $total     = 0;
        $itemsPrint = [];
        foreach ($productos as $prod) {
            $prod = (object) $prod;
            if (!isset($prod->id)) continue;

            $subtotal = $prod->cantidad * $prod->precio;
            $total   += $subtotal;

            $detalleModel->insert([
                'id_pedido'       => $idPedido,
                'id_producto'     => $prod->id,
                'nombre_producto' => $prod->nombre,
                'cantidad'        => $prod->cantidad,
                'precio'          => $prod->precio,
            ]);

            $itemsPrint[] = [
                'id_producto'     => $prod->id,
                'nombre_producto' => $prod->nombre,
                'cantidad'        => $prod->cantidad,
            ];
        }

        $pedidoModel->update($idPedido, ['total' => $total]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->failServerError('Error al guardar el pedido');
        }

        // Enviar a impresoras de cocina/bar/horno (solo pedidos nuevos, sin bloquear la respuesta)
        if ($esPedidoNuevo && !empty($itemsPrint)) {
            try {
                (new ImpresoraService())->routearPedido(
                    ['id' => $idPedido, 'mesa' => $idMesa],
                    $itemsPrint
                );
            } catch (\Throwable $e) {
                log_message('error', '[PedidosApi] Error enviando a impresoras: ' . $e->getMessage());
            }
        }

        return $this->respond(['success' => true, 'id_pedido' => $idPedido]);
    }

    public function cancelar_pedido()
    {
        if (!$this->puedeAdmin()) {
            return $this->failForbidden('Solo admin puede cancelar pedidos');
        }

        $json = $this->request->getJSON();

        if (!isset($json->id_mesa)) {
            return $this->failValidationError('Falta ID de mesa');
        }

        $idMesa       = $json->id_mesa;
        $pedidoModel  = new PedidoOperacionesModel();
        $detalleModel = new PedidoDetalleOperacionesModel();
        $mesaModel    = new MesaModel();

        $db = \Config\Database::connect('operaciones');
        $db->transStart();

        $pedido = $pedidoModel->where('id_mesa', $idMesa)->where('estado', 'pendiente')->first();

        if ($pedido) {
            $detalleModel->where('id_pedido', $pedido['id'])->delete();
            $pedidoModel->delete($pedido['id']);
        }

        $mesaModel->update($idMesa, ['estado' => 'libre']);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->failServerError('Error al cancelar pedido');
        }

        return $this->respond(['success' => true, 'message' => 'Pedido cancelado y mesa liberada']);
    }
}
