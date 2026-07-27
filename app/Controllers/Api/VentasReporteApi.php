<?php

namespace App\Controllers\Api;

use App\Models\ImpresoraModel;
use App\Libraries\ImpresoraService;

class VentasReporteApi extends BaseApiController
{
    private function db()
    {
        return \Config\Database::connect('operaciones');
    }

    public function get_lista()
    {
        $desde = $this->request->getGet('desde') ?: date('Y-m-d');
        $hasta = $this->request->getGet('hasta') ?: date('Y-m-d');

        $desde = $desde . ' 00:00:00';
        $hasta = $hasta . ' 23:59:59';

        $db = $this->db();

        $ventas = $db->query("
            SELECT
                v.id_venta,
                v.id_mesa,
                v.nombre_cajero,
                v.metodo_pago,
                v.total,
                v.total_neto,
                v.total_igv,
                v.total_icbr,
                v.recibido,
                v.vuelto,
                v.fecha_registro,
                COUNT(d.id_detalle) AS n_items
            FROM rest_venta v
            LEFT JOIN rest_venta_detalle d ON d.id_venta = v.id_venta
            WHERE v.fecha_registro BETWEEN ? AND ?
            GROUP BY v.id_venta
            ORDER BY v.fecha_registro DESC
        ", [$desde, $hasta])->getResultArray();

        $totales = $db->query("
            SELECT
                COUNT(*) AS n_ventas,
                COALESCE(SUM(total), 0) AS sum_total,
                COALESCE(SUM(total_neto), 0) AS sum_neto,
                COALESCE(SUM(total_igv), 0) AS sum_igv,
                COALESCE(SUM(total_icbr), 0) AS sum_icbr
            FROM rest_venta
            WHERE fecha_registro BETWEEN ? AND ?
        ", [$desde, $hasta])->getRowArray();

        return $this->respond([
            'success' => true,
            'ventas'  => $ventas,
            'totales' => $totales,
        ]);
    }

    public function get_detalle($id)
    {
        $db = $this->db();

        $venta = $db->query("
            SELECT * FROM rest_venta WHERE id_venta = ?
        ", [$id])->getRowArray();

        if (!$venta) {
            return $this->fail('Venta no encontrada', 404);
        }

        $detalle = $db->query("
            SELECT * FROM rest_venta_detalle WHERE id_venta = ? ORDER BY id_detalle ASC
        ", [$id])->getResultArray();

        return $this->respond([
            'success' => true,
            'venta'   => $venta,
            'detalle' => $detalle,
        ]);
    }

    public function imprimir()
    {
        $json = $this->request->getJSON(true);

        $idVenta     = (int)($json['id_venta']     ?? 0);
        $idImpresora = (int)($json['id_impresora'] ?? 0);

        if (!$idVenta || !$idImpresora) {
            return $this->fail('Faltan parámetros: id_venta e id_impresora son requeridos');
        }

        $db    = $this->db();
        $venta = $db->query("SELECT * FROM rest_venta WHERE id_venta = ?", [$idVenta])->getRowArray();

        if (!$venta) {
            return $this->fail('Venta no encontrada', 404);
        }

        $detalle = $db->query(
            "SELECT * FROM rest_venta_detalle WHERE id_venta = ? ORDER BY id_detalle ASC",
            [$idVenta]
        )->getResultArray();

        $impresora = (new ImpresoraModel())->where('activo', 1)->find($idImpresora);
        if (!$impresora) {
            return $this->fail('Impresora no encontrada o inactiva', 404);
        }

        $texto  = $this->formatearBoleta($venta, $detalle);
        $ok     = (new ImpresoraService())->enviarAImpresora($impresora['ip'], (int)$impresora['puerto'], $texto);

        if (!$ok) {
            return $this->failServerError(
                "No se pudo conectar con {$impresora['nombre']} ({$impresora['ip']}:{$impresora['puerto']})"
            );
        }

        return $this->respond(['success' => true, 'message' => "Enviado a {$impresora['nombre']}"]);
    }

    private function formatearBoleta(array $v, array $detalle): string
    {
        $W   = 40;
        $sep = str_repeat('-', $W) . "\n";
        $eq  = str_repeat('=', $W) . "\n";

        $fecha = $v['fecha_registro']
            ? date('d/m/Y H:i', strtotime($v['fecha_registro']))
            : date('d/m/Y H:i');

        $metodos = [
            'efectivo'      => 'Efectivo',
            'yape'          => 'Yape',
            'plin'          => 'Plin',
            'tarjeta'       => 'Tarjeta',
            'transferencia' => 'Transferencia',
        ];
        $metodo = $metodos[$v['metodo_pago']] ?? strtoupper($v['metodo_pago'] ?? '');

        $t  = "\n";
        $t .= $eq;
        $t .= str_pad('BOLETA DE VENTA', $W, ' ', STR_PAD_BOTH) . "\n";
        $t .= $eq;
        $t .= sprintf("%-12s: #%d\n", 'Venta', $v['id_venta']);
        $t .= sprintf("%-12s: Mesa %s\n", 'Mesa', $v['id_mesa']);
        $t .= sprintf("%-12s: %s\n", 'Cajero', $v['nombre_cajero'] ?? '-');
        $t .= sprintf("%-12s: %s\n", 'Fecha', $fecha);
        $t .= sprintf("%-12s: %s\n", 'Pago', $metodo);
        $t .= $sep;

        foreach ($detalle as $d) {
            $cant      = (int)$d['cantidad'];
            $desc      = $d['descripcion'] ?? '---';
            $subtotal  = number_format((float)$d['subtotal'], 2);
            $precio    = number_format((float)$d['precio'], 2);

            // Primera línea: cantidad x descripción
            $linea1 = "  {$cant}x  " . $desc;
            if (strlen($linea1) > $W - 10) {
                $linea1 = substr($linea1, 0, $W - 10) . '...';
            }
            // Alinear precio a la derecha
            $precioStr = "S/{$precio}";
            $padding   = $W - strlen($linea1) - strlen($precioStr);
            if ($padding > 0) {
                $t .= $linea1 . str_repeat(' ', $padding) . $precioStr . "\n";
            } else {
                $t .= str_pad($linea1, $W - strlen($precioStr)) . $precioStr . "\n";
            }

            // Segunda línea: subtotal si cantidad > 1
            if ($cant > 1) {
                $subStr  = "     Subtotal: S/{$subtotal}";
                $t .= str_pad($subStr, $W, ' ', STR_PAD_LEFT) . "\n";
            }
        }

        $t .= $sep;

        $neto  = number_format((float)$v['total_neto'], 2);
        $igv   = number_format((float)$v['total_igv'], 2);
        $total = number_format((float)$v['total'], 2);
        $rec   = number_format((float)$v['recibido'], 2);
        $vue   = number_format((float)$v['vuelto'], 2);

        $t .= str_pad('Neto',    $W - 10) . str_pad("S/{$neto}",   10, ' ', STR_PAD_LEFT) . "\n";
        $t .= str_pad('IGV',     $W - 10) . str_pad("S/{$igv}",    10, ' ', STR_PAD_LEFT) . "\n";
        $t .= $sep;
        $t .= str_pad('TOTAL',   $W - 10) . str_pad("S/{$total}",  10, ' ', STR_PAD_LEFT) . "\n";
        $t .= str_pad($metodo,   $W - 10) . str_pad("S/{$rec}",    10, ' ', STR_PAD_LEFT) . "\n";
        $t .= str_pad('Vuelto',  $W - 10) . str_pad("S/{$vue}",    10, ' ', STR_PAD_LEFT) . "\n";
        $t .= $eq;
        $t .= str_pad('Gracias por su visita', $W, ' ', STR_PAD_BOTH) . "\n";
        $t .= $eq;
        $t .= "\n\n\n";

        return $t;
    }
}
