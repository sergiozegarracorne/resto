<?php

namespace App\Models;

use CodeIgniter\Model;

class VentaModel extends Model
{
    protected $DBGroup       = 'operaciones';
    protected $table         = 'rest_venta';
    protected $primaryKey    = 'id_venta';
    protected $allowedFields = [
        'id_mesa', 'id_usuario', 'nombre_cajero',
        'cliente_doc_tipo', 'cliente_doc_num', 'cliente_nombre',
        'metodo_pago', 'total', 'recibido', 'vuelto', 'fecha_registro',
        'igv_porcentaje', 'total_neto', 'total_igv', 'total_icbr',
    ];

    public function guardarVenta(array $data): array
    {
        // Obtener tasas fiscales vigentes
        $igvConfig  = (new ConfigIgvModel())->vigente();
        $icbrConfig = (new ConfigIcbrModel())->vigente();

        $igvPct   = $igvConfig  ? (float) $igvConfig['porcentaje']  : 0;
        $icbrUnit = $icbrConfig ? (float) $icbrConfig['monto']       : 0;

        // Precarga de productos para saber tipo_igv y aplica_icbr
        $ids           = array_column($data['items'], 'id');
        $productoModel = new ProductoModel();
        $productosDB   = [];
        if ($ids) {
            foreach ($productoModel->whereIn('id', $ids)->findAll() as $p) {
                $productosDB[$p['id']] = $p;
            }
        }

        $totalNeto = 0;
        $totalIgv  = 0;
        $totalIcbr = 0;
        $detalles  = [];

        foreach ($data['items'] as $item) {
            $prod       = $productosDB[$item['id']] ?? null;
            $tipoIgv    = $prod['tipo_igv']    ?? 'gravado';
            $aplicaIcbr = $prod['aplica_icbr'] ?? 0;

            $cantidad = (int)   $item['cantidad'];
            $precio   = (float) $item['precio'];
            $subtotal = $precio * $cantidad;

            // IGV incluido en el precio → extraer
            $pct      = ($tipoIgv === 'gravado') ? $igvPct : 0;
            $divisor  = 1 + ($pct / 100);
            $neto     = round($subtotal / $divisor, 2);
            $igvMonto = round($subtotal - $neto, 2);

            // ICBR: monto fijo × cantidad
            $icbrMonto = ($aplicaIcbr && $icbrUnit > 0) ? round($icbrUnit * $cantidad, 2) : 0;

            $totalNeto += $neto;
            $totalIgv  += $igvMonto;
            $totalIcbr += $icbrMonto;

            $detalles[] = [
                'id_producto'    => $item['id'],
                'descripcion'    => $item['nombre'],
                'cantidad'       => $cantidad,
                'precio'         => $precio,
                'subtotal'       => $subtotal,
                'tipo_igv'       => $tipoIgv,
                'igv_porcentaje' => $pct,
                'igv_monto'      => $igvMonto,
                'aplica_icbr'    => $aplicaIcbr ? 1 : 0,
                'icbr_monto'     => $icbrMonto,
            ];
        }

        $this->db->transStart();

        $recibido = (float)($data['recibido'] ?? $data['total']);
        $vuelto   = max(0, $recibido - (float)$data['total']);

        $cliente = $data['cliente'] ?? null;

        $this->insert([
            'id_mesa'          => $data['id_mesa'],
            'id_usuario'       => $data['id_usuario']    ?? null,
            'nombre_cajero'    => $data['nombre_cajero'] ?? null,
            'cliente_doc_tipo' => $cliente['tipo']   ?? null,
            'cliente_doc_num'  => $cliente['doc']    ?? null,
            'cliente_nombre'   => $cliente['nombre'] ?? null,
            'metodo_pago'      => $data['metodo'],
            'total'            => $data['total'],
            'recibido'         => $recibido,
            'vuelto'           => $vuelto,
            'fecha_registro'   => date('Y-m-d H:i:s'),
            'igv_porcentaje'   => $igvPct,
            'total_neto'       => round($totalNeto, 2),
            'total_igv'        => round($totalIgv, 2),
            'total_icbr'       => round($totalIcbr, 2),
        ]);

        $idVenta      = $this->db->insertID();
        $detalleModel = new VentaDetalleModel();

        foreach ($detalles as $d) {
            $detalleModel->insert(array_merge(['id_venta' => $idVenta], $d));
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new \Exception('Error al guardar la venta');
        }

        return [
            'id_venta'   => $idVenta,
            'igv_pct'    => $igvPct,
            'total_neto' => round($totalNeto, 2),
            'total_igv'  => round($totalIgv, 2),
            'total_icbr' => round($totalIcbr, 2),
        ];
    }
}
