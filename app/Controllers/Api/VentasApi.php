<?php

namespace App\Controllers\Api;

use App\Models\VentaModel;
use App\Models\PedidoOperacionesModel;
use App\Models\PedidoDetalleOperacionesModel;
use App\Models\MesaModel;

class VentasApi extends BaseApiController
{
    public function cobrar_pedido()
    {
        if (!$this->puedeAdmin()) {
            return $this->failForbidden('Solo admin puede cobrar pedidos');
        }

        $json = $this->request->getJSON(true);

        if (empty($json)) {
            return $this->fail('No se recibió información');
        }

        if (empty($json['items']) || !is_array($json['items'])) {
            return $this->fail('Debes agregar al menos un producto');
        }

        $ventaModel   = new VentaModel();
        $pedidoModel  = new PedidoOperacionesModel();
        $detalleModel = new PedidoDetalleOperacionesModel();
        $mesaModel    = new MesaModel();

        $turno = session('usuario_turno');
        $json['id_usuario']    = $turno['id']     ?? null;
        $json['nombre_cajero'] = $turno['nombre'] ?? null;

        $liberarMesa = $json['liberar_mesa'] ?? true;

        try {
            // 1. Guardar venta + detalles (transacción en operaciones)
            $resultado = $ventaModel->guardarVenta($json);

            // 2. Si es pago parcial (split), marcar ítems como pagados en pedido_detalles
            if (!$liberarMesa && !empty($json['items'])) {
                $pedido = $pedidoModel
                    ->where('id_mesa', $json['id_mesa'])
                    ->where('estado', 'pendiente')
                    ->first();

                if ($pedido) {
                    foreach ($json['items'] as $item) {
                        $idProducto = $item['id'] ?? null;
                        $qtyCobrada = (int) ($item['cantidad'] ?? 0);
                        if (!$idProducto || $qtyCobrada <= 0) continue;

                        $det = $detalleModel
                            ->where('id_pedido', $pedido['id'])
                            ->where('id_producto', $idProducto)
                            ->where('pagado', 0)
                            ->first();

                        if (!$det) continue;

                        if ($qtyCobrada >= (int) $det['cantidad']) {
                            $detalleModel->update($det['id'], ['pagado' => 1]);
                        } else {
                            // Pago parcial de cantidad: dividir la fila
                            $detalleModel->update($det['id'], ['cantidad' => (int) $det['cantidad'] - $qtyCobrada]);
                            $detalleModel->insert([
                                'id_pedido'       => $pedido['id'],
                                'id_producto'     => $idProducto,
                                'nombre_producto' => $det['nombre_producto'],
                                'cantidad'        => $qtyCobrada,
                                'precio'          => $det['precio'],
                                'pagado'          => 1,
                            ]);
                        }
                    }
                }
            }

            if ($liberarMesa) {
                // 2. Cerrar el pedido activo de la mesa
                $pedido = $pedidoModel
                    ->where('id_mesa', $json['id_mesa'])
                    ->where('estado', 'pendiente')
                    ->first();

                if ($pedido) {
                    $detalleModel->where('id_pedido', $pedido['id'])->delete();
                    $pedidoModel->delete($pedido['id']);
                }

                // 3. Liberar la mesa
                $mesaModel->update($json['id_mesa'], ['estado' => 'libre']);
            }

            return $this->respond([
                'success'  => true,
                'message'  => 'Venta registrada correctamente',
                'id_venta' => $resultado['id_venta'],
            ]);

        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }
}
