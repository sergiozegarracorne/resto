<?php

namespace App\Controllers\Api;

use App\Models\ImpresoraModel;
use App\Libraries\ImpresoraService;

class ImpresorasApi extends BaseApiController
{
    // ─── CRUD ────────────────────────────────────────────────────────────────────

    public function get_all()
    {
        $model      = new ImpresoraModel();
        $impresoras = $model->where('activo', 1)->orderBy('nombre')->findAll();

        return $this->respond(['success' => true, 'impresoras' => $impresoras]);
    }

    public function save()
    {
        $json = $this->request->getJSON();

        if (empty($json->nombre) || empty($json->ip)) {
            return $this->failValidationError('Nombre e IP son requeridos');
        }

        $data = [
            'nombre' => trim($json->nombre),
            'ip'     => trim($json->ip),
            'puerto' => (int)($json->puerto ?? 9100),
            'activo' => 1,
        ];

        $model = new ImpresoraModel();

        if (!empty($json->id)) {
            $model->update((int)$json->id, $data);
            $id = (int)$json->id;
        } else {
            $id = $model->insert($data);
        }

        return $this->respond(['success' => true, 'id' => $id]);
    }

    public function delete($id)
    {
        $model = new ImpresoraModel();
        $model->update((int)$id, ['activo' => 0]);

        return $this->respond(['success' => true]);
    }

    // ─── PRUEBA DE IMPRESIÓN ─────────────────────────────────────────────────────

    public function test_print($id)
    {
        $model     = new ImpresoraModel();
        $impresora = $model->find((int)$id);

        if (!$impresora) {
            return $this->failNotFound('Impresora no encontrada');
        }

        $texto  = "\n";
        $texto .= str_repeat('=', 32) . "\n";
        $texto .= str_pad('PRUEBA DE IMPRESION', 32, ' ', STR_PAD_BOTH) . "\n";
        $texto .= str_repeat('=', 32) . "\n";
        $texto .= "Impresora : " . $impresora['nombre'] . "\n";
        $texto .= "IP        : " . $impresora['ip'] . "\n";
        $texto .= "Puerto    : " . $impresora['puerto'] . "\n";
        $texto .= "Fecha     : " . date('d/m/Y H:i:s') . "\n";
        $texto .= str_repeat('-', 32) . "\n";
        $texto .= "   Sistema de Pedidos - Resto\n";
        $texto .= str_repeat('=', 32) . "\n";
        $texto .= "\n\n\n";

        $service = new ImpresoraService();
        $ok      = $service->enviarAImpresora($impresora['ip'], (int)$impresora['puerto'], $texto);

        if (!$ok) {
            return $this->failServerError(
                "Sin conexión con {$impresora['nombre']} ({$impresora['ip']}:{$impresora['puerto']})"
            );
        }

        return $this->respond([
            'success' => true,
            'message' => "Prueba enviada a {$impresora['nombre']}",
        ]);
    }

    // ─── ESCANEO DE RED ──────────────────────────────────────────────────────────

    public function scan_red()
    {
        $subnet = $this->request->getGet('subnet') ?? '192.168.10';
        $port   = 9100;

        // Sanitize subnet (solo 3 octetos)
        if (!preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}$/', $subnet)) {
            return $this->failValidationError('Subnet inválida. Formato esperado: 192.168.10');
        }

        set_time_limit(15);

        // Intentar con nmap (Linux/Mac con nmap instalado)
        $found = $this->scanConNmap($subnet, $port);

        // Fallback: sockets async
        if ($found === null) {
            $found = $this->scanConSockets($subnet, $port);
        }

        // Filtrar IPs ya configuradas
        $model       = new ImpresoraModel();
        $existentes  = array_column($model->where('activo', 1)->findAll(), 'ip');
        $found       = array_values(array_filter($found, fn($ip) => !in_array($ip, $existentes)));

        return $this->respond(['success' => true, 'ips' => $found, 'subnet' => $subnet]);
    }

    private function scanConNmap(string $subnet, int $port): ?array
    {
        $nmap = trim((string) shell_exec('which nmap 2>/dev/null'));
        if (!$nmap) return null;

        $output = (string) shell_exec("{$nmap} -p {$port} --open -T4 {$subnet}.0/24 2>/dev/null");
        if (!$output) return [];

        preg_match_all('/Nmap scan report for (?:[\w.-]+ )?\(?([\d.]+)\)?/', $output, $m);
        return $m[1] ?? [];
    }

    private function scanConSockets(string $subnet, int $port): array
    {
        // Abrir 254 conexiones async simultáneamente
        $sockets = [];
        for ($i = 1; $i <= 254; $i++) {
            $ip = "{$subnet}.{$i}";
            $fp = @stream_socket_client(
                "tcp://{$ip}:{$port}",
                $errno, $errstr, 0,
                STREAM_CLIENT_ASYNC_CONNECT | STREAM_CLIENT_CONNECT
            );
            if ($fp !== false) {
                stream_set_blocking($fp, false);
                $sockets[$ip] = $fp;
            }
        }

        // Esperar que se establezcan conexiones (máx 1.5s)
        usleep(1500000);

        $found = [];
        foreach ($sockets as $ip => $fp) {
            $write  = [$fp];
            $read   = null;
            $except = null;
            $n      = @stream_select($read, $write, $except, 0, 0);
            if ($n > 0 && !empty($write)) {
                $found[] = $ip;
            }
            @fclose($fp);
        }

        return $found;
    }

    // ─── ENRUTAMIENTO ────────────────────────────────────────────────────────────

    public function get_routing()
    {
        $db = \Config\Database::connect();

        $categorias = $db->query("
            SELECT c.id, c.nombre, ic.id_impresora,
                   IF(ic.id IS NOT NULL, 1, 0) AS tiene_regla
            FROM categorias c
            LEFT JOIN impresora_categoria ic ON ic.id_categoria = c.id
            WHERE c.deleted_at IS NULL
            ORDER BY c.nombre ASC
        ")->getResultArray();

        $excepciones = $db->query("
            SELECT p.id AS id_producto, p.nombre, ipe.id_impresora
            FROM impresora_producto_excepcion ipe
            INNER JOIN productos p ON p.id = ipe.id_producto
            WHERE p.deleted_at IS NULL
            ORDER BY p.nombre ASC
        ")->getResultArray();

        return $this->respond([
            'success'     => true,
            'categorias'  => $categorias,
            'excepciones' => $excepciones,
        ]);
    }

    public function save_routing()
    {
        $json = $this->request->getJSON();
        $db   = \Config\Database::connect();

        $db->transStart();

        // Categorías: borra todo y re-inserta solo las que tienen impresora asignada
        $db->query('DELETE FROM impresora_categoria');
        foreach (($json->categorias ?? []) as $cat) {
            if (empty($cat->id_categoria) || empty($cat->id_impresora)) continue;
            $db->table('impresora_categoria')->insert([
                'id_categoria' => (int)$cat->id_categoria,
                'id_impresora' => (int)$cat->id_impresora,
            ]);
        }

        // Excepciones: borra todo y re-inserta (null = no imprimir ese producto)
        $db->query('DELETE FROM impresora_producto_excepcion');
        foreach (($json->excepciones ?? []) as $exc) {
            if (empty($exc->id_producto)) continue;
            $db->table('impresora_producto_excepcion')->insert([
                'id_producto'  => (int)$exc->id_producto,
                'id_impresora' => !empty($exc->id_impresora) ? (int)$exc->id_impresora : null,
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->failServerError('Error al guardar la configuración');
        }

        return $this->respond(['success' => true, 'message' => 'Configuración guardada']);
    }
}
