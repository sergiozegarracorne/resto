<?php

namespace App\Models;

use CodeIgniter\Model;

class VentaModel extends Model
{
    protected $DBGroup = 'operaciones';

    public function guardarVenta($data)
    {
        $db = \Config\Database::connect('operaciones');

        $db->transStart();

        // CABECERA
        $cabecera = [
            'id_mesa'      => $data['id_mesa'],
            'metodo_pago'  => $data['metodo'],
            'total'        => $data['total'],
            'recibido'     => $data['recibido'],
            'vuelto'       => $data['recibido'] - $data['total'],
            'fecha_reg'    => date('Y-m-d H:i:s')
        ];

        $db->table('venta')->insert($cabecera);

        $idVenta = $db->insertID();

        // DETALLE
        foreach ($data['items'] as $item) {

            $detalle = [
                'id_venta'    => $idVenta,
                'id_producto' => $item['id'],
                'descripcion' => $item['nombre'],
                'cantidad'    => $item['cantidad'],
                'precio'      => $item['precio'],
                'subtotal'    => $item['cantidad'] * $item['precio']
            ];

            $db->table('venta_detalle')->insert($detalle);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            throw new \Exception('Error al registrar la venta');
        }

        return $idVenta;
    }
}