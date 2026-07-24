/**
 * Emulador de impresoras de red RAW (puerto 9100 / JetDirect).
 * Recibe trabajos de impresión por TCP y los muestra en una UI web vía WebSocket.
 *
 * Uso:
 *   node server.js            → modo desarrollo (puertos locales)
 *   NODE_ENV=production node server.js  → modo producción (IPs reales)
 */

const net      = require('net');
const http     = require('http');
const fs       = require('fs');
const path     = require('path');
const WebSocket = require('ws');
const config   = require('./config');

const isProd = process.env.NODE_ENV === 'production';

// ─── HTTP + WebSocket ────────────────────────────────────────────────────────

const httpServer = http.createServer((req, res) => {
    if (req.url === '/' || req.url === '/index.html') {
        const file = fs.readFileSync(path.join(__dirname, 'public', 'index.html'));
        res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
        return res.end(file);
    }
    // Servir el config como JSON para que la UI sepa las impresoras
    if (req.url === '/api/config') {
        res.writeHead(200, { 'Content-Type': 'application/json' });
        return res.end(JSON.stringify({
            printers: config.printers.map(p => ({
                id: p.id, name: p.name, emoji: p.emoji,
                ip: p.ip, port: isProd ? p.port : p.devPort,
                color: p.color, bg: p.bg,
                isProd,
            }))
        }));
    }
    res.writeHead(404); res.end('Not found');
});

const wss = new WebSocket.Server({ server: httpServer });

function broadcast(data) {
    const msg = JSON.stringify(data);
    wss.clients.forEach(client => {
        if (client.readyState === WebSocket.OPEN) client.send(msg);
    });
}

// ─── Servidores TCP de impresora ─────────────────────────────────────────────

const jobCounters = {};

config.printers.forEach(printer => {
    jobCounters[printer.id] = 0;

    const bindIp   = isProd ? printer.ip   : '0.0.0.0';
    const bindPort = isProd ? printer.port : printer.devPort;

    const tcpServer = net.createServer(socket => {
        const from = `${socket.remoteAddress}:${socket.remotePort}`;
        console.log(`  [${printer.emoji} ${printer.name}] Conexión desde ${from}`);

        broadcast({ type: 'connect', printerId: printer.id, client: from });

        let buffers = [];

        socket.on('data', chunk => buffers.push(chunk));

        socket.on('end', () => {
            if (buffers.length === 0) return;

            const raw  = Buffer.concat(buffers);
            const text = parseEscPos(raw);
            jobCounters[printer.id]++;

            const job = {
                type:      'job',
                printerId: printer.id,
                jobNum:    jobCounters[printer.id],
                timestamp: new Date().toISOString(),
                text,
                bytes:     raw.length,
                from,
            };

            console.log(`  [${printer.emoji} ${printer.name}] Job #${job.jobNum} — ${raw.length} bytes desde ${from}`);
            if (text) console.log(text.split('\n').map(l => '    ' + l).join('\n'));

            broadcast(job);
        });

        socket.on('error', err => {
            console.error(`  [${printer.emoji} ${printer.name}] Error: ${err.message}`);
            broadcast({ type: 'error', printerId: printer.id, message: err.message });
        });
    });

    tcpServer.on('error', err => {
        console.error(`❌  ${printer.emoji} ${printer.name} (${bindIp}:${bindPort}): ${err.message}`);
        if (err.code === 'EADDRNOTAVAIL') {
            console.error(`    → La IP ${printer.ip} no existe en este sistema. Usa modo dev o configura el alias de IP.`);
        }
    });

    tcpServer.listen(bindPort, bindIp, () => {
        const addr = isProd
            ? `${printer.ip}:${printer.port}`
            : `localhost:${bindPort}  (simula ${printer.ip}:${printer.port})`;
        console.log(`✅  ${printer.emoji} ${printer.name} — escuchando en ${addr}`);
    });
});

// ─── Iniciar web ──────────────────────────────────────────────────────────────

httpServer.listen(config.webPort, () => {
    console.log('\n────────────────────────────────────────────');
    console.log(`🖥️   UI:   http://localhost:${config.webPort}`);
    console.log(`📡  Modo: ${isProd ? 'PRODUCCIÓN (IPs reales)' : 'DESARROLLO (puertos locales)'}`);
    console.log('────────────────────────────────────────────\n');
});

// ─── Parser ESC/POS básico ───────────────────────────────────────────────────
// Elimina bytes de control ESC/POS y devuelve texto legible.

function parseEscPos(buf) {
    let out = '';
    let i   = 0;

    while (i < buf.length) {
        const b = buf[i];

        // ESC (0x1B) — secuencia de comando, saltar
        if (b === 0x1b) {
            i++;
            if (i >= buf.length) break;
            const cmd = buf[i];
            if (cmd === 0x40)                    { i++; continue; } // ESC @  init
            if (cmd === 0x61 || cmd === 0x45 ||
                cmd === 0x21 || cmd === 0x47 ||
                cmd === 0x4d || cmd === 0x4e)     { i += 2; continue; } // ESC X n  (1 parámetro)
            if (cmd === 0x64)                    { i += 2; continue; } // ESC d n  feed lines
            if (cmd === 0x5b && buf[i+1] === 0x40){ i += 8; continue; } // ESC [ @ (font)
            i++; continue;
        }

        // GS (0x1D) — comandos de impresora, saltar bloque
        if (b === 0x1d) {
            i++;
            if (i >= buf.length) break;
            const cmd = buf[i];
            if (cmd === 0x56)                    { i += 2; continue; } // GS V (corte)
            if (cmd === 0x21 || cmd === 0x42 ||
                cmd === 0x57 || cmd === 0x4c)     { i += 2; continue; } // GS X n
            if (cmd === 0x68 || cmd === 0x77 ||
                cmd === 0x48 || cmd === 0x66 ||
                cmd === 0x6b)                    { i += 2; continue; } // barcode params
            i++; continue;
        }

        // DLE, STX, ETX, CAN, etc. — ignorar
        if (b < 0x09) { i++; continue; }

        // LF y CR → salto de línea
        if (b === 0x0a) { out += '\n'; i++; continue; }
        if (b === 0x0d) { i++; continue; }

        // TAB
        if (b === 0x09) { out += '    '; i++; continue; }

        // Texto ASCII / Latin-1 imprimible
        if (b >= 0x20) { out += String.fromCharCode(b); }

        i++;
    }

    return out.replace(/\n{3,}/g, '\n\n').trim();
}
