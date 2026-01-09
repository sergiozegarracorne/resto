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

    // -------------------------------------------------------------------------
    // MESAS Y PEDIDOS
    // -------------------------------------------------------------------------

    // 1. Obtener Pisos y Mesas
    public function get_pisos_mesas()
    {
        $pisoModel = new \App\Models\PisoModel();
        $mesaModel = new \App\Models\MesaModel();

        $pisos = $pisoModel->orderBy('orden', 'ASC')->findAll();

        foreach ($pisos as &$piso) {
            $piso['mesas'] = $mesaModel->where('id_piso', $piso['id'])->findAll();
        }

        return $this->respond($pisos);
    }

    // 2. Obtener Pedido Activo de una Mesa
    public function get_mesa_pedido($idMesa)
    {
        $pedidoModel = new \App\Models\PedidoModel();
        $detalleModel = new \App\Models\PedidoDetalleModel();

        // Buscar pedido pendiente
        $pedido = $pedidoModel->where('id_mesa', $idMesa)
            ->where('estado', 'pendiente')
            ->first();

        if ($pedido) {
            $detalles = $detalleModel->where('id_pedido', $pedido['id'])->findAll();

            // Mapear detalles para que coincidan con la estructura JS esperada
            $items = array_map(function ($d) {
                return [
                    'id' => $d['id_producto'], // ID del producto
                    'nombre' => $d['nombre_producto'],
                    'precio' => (float) $d['precio'],
                    'cantidad' => (int) $d['cantidad'],
                    'subtotal' => (float) $d['precio'] * (int) $d['cantidad']
                ];
            }, $detalles);

            return $this->respond([
                'success' => true,
                'pedido' => $pedido,
                'items' => $items
            ]);
        }

        return $this->respond(['success' => false, 'message' => 'Mesa libre']);
    }

    // 3. Guardar/Actualizar Pedido
    public function save_pedido()
    {
        $json = $this->request->getJSON();

        if (!isset($json->id_mesa) || !isset($json->productos)) {
            return $this->failValidationError('Datos incompletos');
        }

        $idMesa = $json->id_mesa;
        $productos = $json->productos;
        $idUsuario = session('usuario_turno')['id'] ?? 1;

        $pedidoModel = new \App\Models\PedidoModel();
        $detalleModel = new \App\Models\PedidoDetalleModel();
        $mesaModel = new \App\Models\MesaModel();

        $db = \Config\Database::connect();
        $db->transStart();

        // Buscar pedido existente
        $pedido = $pedidoModel->where('id_mesa', $idMesa)->where('estado', 'pendiente')->first();
        $idPedido = null;

        if (!$pedido) {
            // Crear nuevo pedido
            $idPedido = $pedidoModel->insert([
                'id_mesa' => $idMesa,
                'id_usuario' => $idUsuario,
                'total' => 0,
                'estado' => 'pendiente',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            // Marcar mesa como ocupada
            $mesaModel->update($idMesa, ['estado' => 'ocupada']);
        } else {
            $idPedido = $pedido['id'];
            // Borrar detalles anteriores para resincronizar (Estrategia simple)
            $detalleModel->where('id_pedido', $idPedido)->delete();
        }

        $total = 0;
        foreach ($productos as $prod) {
            $prod = (object) $prod; // Asegurar objeto
            if (!isset($prod->id))
                continue;

            $cantidad = $prod->cantidad;
            $precio = $prod->precio;
            $subtotal = $cantidad * $precio;
            $total += $subtotal;

            $detalleModel->insert([
                'id_pedido' => $idPedido,
                'id_producto' => $prod->id,
                'nombre_producto' => $prod->nombre,
                'cantidad' => $cantidad,
                'precio' => $precio
            ]);
        }

        // Actualizar total y timestamp
        $pedidoModel->update($idPedido, [
            'total' => $total,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->failServerError('Error al guardar el pedido');
        }

        return $this->respond(['success' => true, 'id_pedido' => $idPedido]);
    }
    // 4. Unir Mesas
    public function unir_mesas()
    {
        $json = $this->request->getJSON();

        if (!isset($json->id_principal) || !isset($json->ids_secundarias) || !is_array($json->ids_secundarias)) {
            return $this->failValidationError('Faltan parámetros');
        }

        $idPrincipal = $json->id_principal;
        $idsSecundarias = $json->ids_secundarias;

        $mesaModel = new \App\Models\MesaModel();

        // Validar que la mesa principal exista
        $principal = $mesaModel->find($idPrincipal);
        if (!$principal)
            return $this->failNotFound('Mesa principal no encontrada');

        // Validar que TODAS las mesas secundarias estén libres (como pidió el usuario)
        foreach ($idsSecundarias as $id) {
            $mesa = $mesaModel->find($id);
            if (!$mesa)
                return $this->failNotFound("Mesa $id no encontrada");

            if ($mesa['estado'] !== 'libre') {
                return $this->failValidationError("La mesa {$mesa['nombre']} no está libre y no se puede unir.");
            }
        }

        // Proceder a unir
        // Actualizamos id_padre en las secundarias
        $mesaModel->whereIn('id', $idsSecundarias)
            ->set(['id_padre' => $idPrincipal])
            ->update();

        return $this->respond(['success' => true, 'message' => 'Mesas unidas correctamente']);
    }

    // 5. Separar Mesas
    public function separar_mesas()
    {
        $json = $this->request->getJSON();

        if (!isset($json->ids_mesas) || !is_array($json->ids_mesas)) {
            return $this->failValidationError('Faltan parámetros');
        }

        $idsMesas = $json->ids_mesas;
        $mesaModel = new \App\Models\MesaModel();

        // Liberar mesas (id_padre = NULL)
        $mesaModel->whereIn('id', $idsMesas)
            ->set(['id_padre' => null])
            ->update();

        return $this->respond(['success' => true, 'message' => 'Mesas separadas correctamente']);
    }
}
