<?php

namespace App\Libraries;

use App\Models\ImpresoraModel;

/**
 * Servicio de impresión en red.
 * Resuelve qué impresora recibe cada ítem del pedido según:
 *   1. Excepción por producto (tiene prioridad)
 *   2. Regla por categoría del producto
 *   Si id_impresora es NULL en la regla → no imprimir ese ítem.
 */
class ImpresoraService
{
    /**
     * Enruta los ítems del pedido a las impresoras correspondientes.
     *
     * @param array $pedidoInfo  ['id' => ..., 'mesa' => ...]
     * @param array $items       [['id_producto'=>1,'nombre_producto'=>'X','cantidad'=>2], ...]
     */
    public function routearPedido(array $pedidoInfo, array $items): void
    {
        if (empty($items)) return;

        $db         = \Config\Database::connect();
        $productIds = array_column($items, 'id_producto');

        // 1. Cargar excepciones por producto
        $excepciones = [];
        if (!empty($productIds)) {
            $rows = $db->table('impresora_producto_excepcion')
                       ->whereIn('id_producto', $productIds)
                       ->get()->getResultArray();
            foreach ($rows as $r) {
                $excepciones[(int)$r['id_producto']] = $r['id_impresora']; // puede ser null
            }
        }

        // 2. Cargar categoría de cada producto
        $catMap = [];
        $productos = $db->table('productos')
                        ->select('id, categoria_id')
                        ->whereIn('id', $productIds)
                        ->get()->getResultArray();
        foreach ($productos as $p) {
            $catMap[(int)$p['id']] = (int)$p['categoria_id'];
        }

        // 3. Cargar reglas por categoría
        $catIds    = array_unique(array_values($catMap));
        $catRouting = [];
        if (!empty($catIds)) {
            $rows = $db->table('impresora_categoria')
                       ->whereIn('id_categoria', $catIds)
                       ->get()->getResultArray();
            foreach ($rows as $r) {
                $catRouting[(int)$r['id_categoria']] = $r['id_impresora']; // puede ser null
            }
        }

        // 4. Agrupar ítems por impresora destino
        $porImpresora = [];
        foreach ($items as $item) {
            $prodId = (int)$item['id_producto'];

            if (array_key_exists($prodId, $excepciones)) {
                $impresoraId = $excepciones[$prodId]; // excepción de producto
            } else {
                $catId       = $catMap[$prodId] ?? null;
                $impresoraId = ($catId && isset($catRouting[$catId])) ? $catRouting[$catId] : null;
            }

            if ($impresoraId === null) continue; // sin destino o "no imprimir"

            $porImpresora[(int)$impresoraId][] = $item;
        }

        if (empty($porImpresora)) return;

        // 5. Enviar a cada impresora
        $impresoraModel = new ImpresoraModel();
        $impresoraIds   = array_keys($porImpresora);
        $impresoras     = $impresoraModel
            ->whereIn('id', $impresoraIds)
            ->where('activo', 1)
            ->findAll();

        foreach ($impresoras as $imp) {
            $itms = $porImpresora[$imp['id']] ?? [];
            if (empty($itms)) continue;

            $texto = $this->formatearTicket($pedidoInfo, $itms, $imp['nombre']);
            $this->enviarAImpresora($imp['ip'], (int)$imp['puerto'], $texto);
        }
    }

    public function enviarAImpresora(string $ip, int $puerto, string $texto): bool
    {
        $fp = @fsockopen($ip, $puerto, $errno, $errstr, 3);
        if (!$fp) {
            log_message('error', "[ImpresoraService] No se pudo conectar a {$ip}:{$puerto} — {$errstr}");
            return false;
        }
        fwrite($fp, $texto);
        fclose($fp);
        return true;
    }

    private function formatearTicket(array $pedido, array $items, string $nombreImpresora): string
    {
        $sep = str_repeat('-', 32) . "\n";

        $t  = "\n";
        $t .= str_repeat('=', 32) . "\n";
        $t .= str_pad(strtoupper($nombreImpresora), 32, ' ', STR_PAD_BOTH) . "\n";
        $t .= str_repeat('=', 32) . "\n";
        $t .= "Mesa   : " . ($pedido['mesa'] ?? '-') . "\n";
        $t .= "Pedido : #" . ($pedido['id'] ?? '-') . "\n";
        $t .= "Hora   : " . date('H:i:s') . "\n";
        $t .= $sep;

        foreach ($items as $item) {
            $cant   = $item['cantidad'] ?? 1;
            $nombre = $item['nombre_producto'] ?? $item['nombre'] ?? '';
            $t .= "  {$cant}x  {$nombre}\n";
        }

        $t .= $sep;
        $t .= "\n\n\n";

        return $t;
    }
}
