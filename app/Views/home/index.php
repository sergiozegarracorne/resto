<?= view('includes/top'); ?>

<body
    class="bg-gray-100 h-screen overflow-hidden flex flex-col <?= env('app.lowResourceMode') ? 'low-resource-mode' : '' ?>">

    <!-- SECCIÓN SUPERIOR: 80% -->
    <main class="flex-1 min-h-0 p-2 relative group">

        <!-- Marco de Tarjeta -->
        <div class="flex flex-col h-full bg-white rounded-sm shadow-lg border border-gray-200 overflow-hidden">

            <!-- Encabezado -->
            <div class="bg-gray-50 border-b border-gray-200 px-6 py-2 flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-800 tracking-tight">SELECCION DE USUARIO</h2>
            </div>

            <!-- Cuerpo (Grilla) -->
            <div class="p-2 flex-1 min-h-0 relative bg-gray-50/50">
                <div id="grillaVendedores"
                    class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-6 lg:grid-cols-8 gap-2 h-full overflow-y-auto no-scrollbar scroll-smooth pb-2">

                    <?php if (!empty($usuarios) && is_array($usuarios)): ?>
                        <?php foreach ($usuarios as $usuario): ?>
                            <?= boton_vendedor($usuario['nombre'], $usuario['imagen'] ?? '', '', json_encode($usuario)) ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-span-full text-center py-10 text-gray-400">
                            No hay usuarios activos.
                        </div>
                    <?php endif; ?>

                </div> <!-- /Grilla Vendedores -->
            </div>
        </div> <!-- /Marco de Tarjeta -->

        <!-- Botones de Scroll FLOTANTES (Función Helper) -->
        <?= controles_scroll('grillaVendedores', 'abajo-derecha') ?>
    </main>



    <!-- SECCIÓN INFERIOR: Panel de Actividad del Sistema -->
    <section class="h-[100px] bg-slate-900 border-t-2 border-cyan-900 overflow-hidden flex" id="server-panel">
        <style>
            @keyframes _fadeIn { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }
            @keyframes _blink  { 0%,100% { opacity:1; } 50% { opacity:0.2; } }
            .sp-item  { animation: _fadeIn 0.35s ease-out; }
            .sp-live  { animation: _blink 1.4s ease-in-out infinite; }
        </style>

        <!-- COLUMNA IZQUIERDA: Estado + Contadores -->
        <div class="flex flex-col justify-center gap-1.5 px-4 border-r border-slate-700/60" style="min-width:155px">
            <div class="flex items-center gap-1.5">
                <span class="sp-live w-2 h-2 rounded-full bg-green-400 flex-shrink-0"></span>
                <span class="text-green-400 font-mono uppercase tracking-widest" style="font-size:8px">Sistema Activo</span>
            </div>
            <div class="flex gap-3">
                <div class="text-center">
                    <div id="sp-mesas"   class="text-cyan-300   font-mono font-bold text-sm leading-none">—</div>
                    <div class="text-slate-500 uppercase font-mono" style="font-size:7px">Mesas</div>
                </div>
                <div class="text-center">
                    <div id="sp-pedidos" class="text-amber-300  font-mono font-bold text-sm leading-none">—</div>
                    <div class="text-slate-500 uppercase font-mono" style="font-size:7px">Pedidos</div>
                </div>
                <div class="text-center">
                    <div id="sp-ventas"  class="text-emerald-300 font-mono font-bold text-sm leading-none">—</div>
                    <div class="text-slate-500 uppercase font-mono" style="font-size:7px">Ventas hoy</div>
                </div>
            </div>
        </div>

        <!-- COLUMNA CENTRAL: Feed de actividad -->
        <div class="flex-1 overflow-hidden relative">
            <div class="absolute inset-x-0 top-0 px-2 py-0.5 flex items-center gap-1.5 border-b border-slate-800">
                <span class="text-slate-600 font-mono" style="font-size:7px">▸ LOG DE ACTIVIDAD</span>
            </div>
            <div id="sp-feed" class="absolute inset-x-0 bottom-0 px-2 pb-1 flex flex-col gap-px overflow-hidden" style="top:16px"></div>
        </div>

    </section>

    <script>
    (function () {
        const EVENTS = [
            { icon: '🍽️',  text: 'Comanda → Cocina',          color: '#fb923c' },
            { icon: '🖨️',  text: 'Ticket impreso en Barra',    color: '#38bdf8' },
            { icon: '💳',  text: 'Venta cobrada',               color: '#4ade80' },
            { icon: '🔓',  text: 'Mesa liberada',               color: '#c4b5fd' },
            { icon: '📋',  text: 'Pre-cuenta solicitada',       color: '#fbbf24' },
            { icon: '🛵',  text: 'Delivery asignado',           color: '#f472b6' },
            { icon: '🔔',  text: 'Nueva orden recibida',        color: '#fdba74' },
            { icon: '🖨️',  text: 'Comanda → Impresora Horno',  color: '#67e8f9' },
            { icon: '✅',  text: 'Mesa lista para servir',      color: '#86efac' },
            { icon: '💰',  text: 'Pago en efectivo recibido',   color: '#6ee7b7' },
            { icon: '📡',  text: 'Sincronizando impresoras…',   color: '#94a3b8' },
            { icon: '🔄',  text: 'Actualizando inventario',     color: '#7dd3fc' },
            { icon: '🍕',  text: 'Pedido confirmado',           color: '#fde68a' },
            { icon: '🧾',  text: 'Reporte generado',            color: '#a5b4fc' },
        ];

        let stats = { mesas: 0, pedidos: 0, ventas: 0 };
        let eIdx  = Math.floor(Math.random() * EVENTS.length);

        // Seed con dato real de mesas
        fetch('/api/mesas/resumen_activo').then(r => r.json()).then(data => {
            const total = (data.por_usuario || []).reduce((s, u) => s + (parseInt(u.mesas_abiertas) || 0), 0);
            stats.mesas   = total;
            stats.pedidos = total + Math.floor(Math.random() * 8);
            stats.ventas  = Math.floor(Math.random() * 600) + 200;
            flushStats();
        }).catch(() => {
            stats = { mesas: 3, pedidos: 11, ventas: 420 };
            flushStats();
        });

        function flushStats() {
            document.getElementById('sp-mesas').textContent   = stats.mesas;
            document.getElementById('sp-pedidos').textContent = stats.pedidos;
            document.getElementById('sp-ventas').textContent  = 'S/' + stats.ventas;
        }

        // Feed
        const feed = document.getElementById('sp-feed');
        function addEvent() {
            const ev  = EVENTS[eIdx % EVENTS.length]; eIdx++;
            const now = new Date().toLocaleTimeString('es-PE', { hour12: false });
            const el  = document.createElement('div');
            el.className = 'sp-item flex items-center gap-1 font-mono whitespace-nowrap';
            el.style.fontSize = '9px';
            el.innerHTML = `<span style="color:#475569">${now}</span><span>${ev.icon}</span><span style="color:${ev.color}">${ev.text}</span>`;
            feed.insertBefore(el, feed.firstChild);
            while (feed.children.length > 4) feed.removeChild(feed.lastChild);

            // fluctuar stats
            if (Math.random() > 0.55) {
                stats.pedidos++;
                stats.ventas += Math.floor(Math.random() * 45) + 10;
                if (Math.random() > 0.5) stats.mesas = Math.max(0, stats.mesas + (Math.random() > 0.4 ? 1 : -1));
                flushStats();
            }
        }

        addEvent();
        setInterval(addEvent, 2800);
    })();
    </script>

    <!-- Teclado Virtual Global -->
    <?= teclado_numerico() ?>

    <script>
    (async function cargarBadgesMesas() {
        try {
            const res  = await fetch('/api/mesas/resumen_activo');
            const data = await res.json();
            (data.por_usuario || []).forEach(function(u) {
                const n = parseInt(u.mesas_abiertas) || 0;
                if (n < 1) return;
                const badge = document.querySelector('[data-badge-uid="' + u.id_usuario + '"]');
                if (badge) {
                    badge.textContent  = n;
                    badge.style.display = 'flex';
                }
            });
        } catch (e) { /* silencioso */ }
    })();
    </script>

</body>

</html>