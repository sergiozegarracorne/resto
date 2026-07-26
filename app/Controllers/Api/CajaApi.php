<?php

namespace App\Controllers\Api;

class CajaApi extends BaseApiController
{
    public function get_data()
    {
        if (!$this->puedeGestionar()) {
            return $this->failForbidden('Acceso restringido');
        }

        $hoy        = date('Y-m-d');
        $fechaDesde = $this->validarFecha($this->request->getGet('fecha_desde'), $hoy);
        $fechaHasta = $this->validarFecha($this->request->getGet('fecha_hasta'), $hoy);
        $cajero     = $this->request->getGet('cajero');   // nombre_cajero o vacío
        $metodo     = $this->request->getGet('metodo');   // efectivo|tarjeta|monedero o vacío

        $db = \Config\Database::connect('operaciones');

        // ─── Condiciones WHERE reutilizables ─────────────────────────────────────
        $where  = 'DATE(fecha_registro) BETWEEN ? AND ?';
        $params = [$fechaDesde, $fechaHasta];

        if (!empty($cajero)) {
            $where   .= ' AND nombre_cajero = ?';
            $params[] = $cajero;
        }
        if (!empty($metodo)) {
            $where   .= ' AND metodo_pago = ?';
            $params[] = $metodo;
        }

        // ─── Resumen general ─────────────────────────────────────────────────────
        $totales = $db->query("
            SELECT
                COUNT(*)        AS cantidad,
                SUM(total)      AS total_bruto,
                SUM(total_neto) AS total_neto,
                SUM(total_igv)  AS total_igv,
                SUM(total_icbr) AS total_icbr
            FROM rest_venta WHERE {$where}
        ", $params)->getRowArray();

        // ─── Por método de pago ───────────────────────────────────────────────────
        $porMetodo = $db->query("
            SELECT
                COALESCE(NULLIF(TRIM(metodo_pago), ''), 'efectivo') AS metodo,
                COUNT(*)   AS cantidad,
                SUM(total) AS total
            FROM rest_venta WHERE {$where}
            GROUP BY metodo ORDER BY total DESC
        ", $params)->getResultArray();

        // ─── Ventas detalladas ────────────────────────────────────────────────────
        $ventas = $db->query("
            SELECT
                v.id_venta, v.id_mesa, v.id_usuario, v.nombre_cajero,
                v.metodo_pago, v.total, v.recibido,
                v.total_neto, v.total_igv, v.total_icbr,
                v.fecha_registro,
                GROUP_CONCAT(
                    CONCAT(vd.cantidad, 'x ', vd.descripcion)
                    ORDER BY vd.id_detalle SEPARATOR ' · '
                ) AS items_resumen
            FROM rest_venta v
            LEFT JOIN rest_venta_detalle vd ON vd.id_venta = v.id_venta
            WHERE {$where}
            GROUP BY v.id_venta
            ORDER BY v.fecha_registro DESC
        ", $params)->getResultArray();

        // ─── Enriquecer con nombres de mesas ─────────────────────────────────────
        if (!empty($ventas)) {
            $ids          = array_unique(array_column($ventas, 'id_mesa'));
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $mesas        = \Config\Database::connect()
                ->query("SELECT id, nombre FROM mesas WHERE id IN ({$placeholders})", $ids)
                ->getResultArray();
            $mesaMap = array_column($mesas, 'nombre', 'id');

            foreach ($ventas as &$v) {
                $v['mesa_nombre'] = $mesaMap[$v['id_mesa']] ?? "Mesa #{$v['id_mesa']}";
            }
        }

        // ─── Lista de cajeros disponibles (para el filtro) ────────────────────────
        $cajeros = $db->query("
            SELECT DISTINCT nombre_cajero
            FROM rest_venta
            WHERE DATE(fecha_registro) BETWEEN ? AND ?
              AND nombre_cajero IS NOT NULL AND nombre_cajero != ''
            ORDER BY nombre_cajero ASC
        ", [$fechaDesde, $fechaHasta])->getResultArray();

        return $this->respond([
            'success'    => true,
            'filtros'    => [
                'fecha_desde' => $fechaDesde,
                'fecha_hasta' => $fechaHasta,
                'cajero'      => $cajero,
                'metodo'      => $metodo,
            ],
            'totales'    => $totales,
            'por_metodo' => $porMetodo,
            'ventas'     => $ventas,
            'cajeros'    => array_column($cajeros, 'nombre_cajero'),
        ]);
    }

    private function validarFecha(?string $fecha, string $default): string
    {
        if ($fecha && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            return $fecha;
        }
        return $default;
    }
}
