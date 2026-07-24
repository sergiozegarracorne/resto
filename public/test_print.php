<?php
/**
 * Script de prueba para enviar trabajos a las impresoras virtuales.
 *
 * Uso (modo dev):
 *   php test_print.php
 *
 * Este mismo código (con las IPs reales) es lo que usará el backend PHP
 * en producción para enviar comandas a cada área.
 */

// En modo dev, las impresoras virtuales escuchan en localhost con puertos distintos
$impresoras = [
    'cocina' => ['host' => '127.0.0.1', 'port' => 9101, 'ip_real' => '192.168.10.10'],
    'horno'  => ['host' => '127.0.0.1', 'port' => 9102, 'ip_real' => '192.168.10.20'],
    'bar'    => ['host' => '127.0.0.1', 'port' => 9103, 'ip_real' => '192.168.10.30'],
];

// En producción, usar las IPs reales:
// $impresoras = [
//     'cocina' => ['host' => '192.168.10.10', 'port' => 9100],
//     'horno'  => ['host' => '192.168.10.20', 'port' => 9100],
//     'bar'    => ['host' => '192.168.10.30', 'port' => 9100],
// ];

function enviar_a_impresora(string $host, int $port, string $texto): bool
{
    $fp = @fsockopen($host, $port, $errno, $errstr, 5);
    if (!$fp) {
        echo "  ❌  No se pudo conectar a $host:$port — $errstr\n";
        return false;
    }
    fwrite($fp, $texto);
    fclose($fp);
    return true;
}

function linea(int $largo = 32): string
{
    return str_repeat('-', $largo) . "\n";
}

// ─── Ticket de prueba para COCINA ────────────────────────────────────────────
$ticket_cocina = implode('', [
    "\n",
    "================================\n",
    "         ** COCINA **           \n",
    "================================\n",
    "Mesa: 5            #Comanda: 42 \n",
    linea(),
    "  2x  Lomo Saltado             \n",
    "  1x  Arroz con Leche          \n",
    "  3x  Papa a la Huancaina      \n",
    linea(),
    "Mozo: Juan Vendedor            \n",
    "Hora: " . date('H:i:s') . "                  \n",
    "================================\n",
    "\n\n",
]);

// ─── Ticket de prueba para HORNO ─────────────────────────────────────────────
$ticket_horno = implode('', [
    "\n",
    "================================\n",
    "          ** HORNO **           \n",
    "================================\n",
    "Mesa: 5            #Comanda: 42 \n",
    linea(),
    "  1x  Pizza Napolitana         \n",
    "  2x  Pan de Ajo               \n",
    linea(),
    "Mozo: Juan Vendedor            \n",
    "Hora: " . date('H:i:s') . "                  \n",
    "================================\n",
    "\n\n",
]);

// ─── Ticket de prueba para BAR ────────────────────────────────────────────────
$ticket_bar = implode('', [
    "\n",
    "================================\n",
    "           ** BAR **            \n",
    "================================\n",
    "Mesa: 5            #Comanda: 42 \n",
    linea(),
    "  2x  Coca Cola                \n",
    "  1x  Pisco Sour               \n",
    "  1x  Agua sin gas             \n",
    linea(),
    "Mozo: Juan Vendedor            \n",
    "Hora: " . date('H:i:s') . "                  \n",
    "================================\n",
    "\n\n",
]);

// ─── Enviar ───────────────────────────────────────────────────────────────────
$envios = [
    'cocina' => $ticket_cocina,
    'horno'  => $ticket_horno,
    'bar'    => $ticket_bar,
];

echo "\n=== Test de impresoras virtuales ===\n\n";

foreach ($envios as $nombre => $ticket) {
    $cfg = $impresoras[$nombre];
    echo "📤  Enviando a $nombre ({$cfg['host']}:{$cfg['port']})...\n";
    $ok = enviar_a_impresora($cfg['host'], $cfg['port'], $ticket);
    if ($ok) echo "  ✅  Enviado correctamente\n";
    usleep(200_000); // 200ms entre envíos
}

echo "\nRevisa la UI en http://localhost:3000\n\n";
