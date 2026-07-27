<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
Ventas - Resta
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Header -->
<header class="bg-slate-900 text-white px-4 shadow-md flex items-center justify-between h-16 shrink-0 z-10">
    <div class="flex items-center gap-3">
        <a href="<?= base_url('panel') ?>"
            class="px-3 py-1.5 rounded-lg text-sm font-bold border border-slate-600 hover:bg-slate-700 flex items-center gap-1 transition-colors">
            ← Panel
        </a>
        <h1 class="text-xl font-bold flex items-center gap-2">
            <span class="text-2xl">📈</span> Reporte de Ventas
        </h1>
    </div>
    <div class="text-sm opacity-60 capitalize"><?= esc($rol) ?></div>
</header>

<main class="flex-1 overflow-y-auto p-4 flex flex-col gap-4">

    <!-- Filtros de fecha -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 px-5 py-4 flex flex-wrap items-end gap-4">
        <div class="flex flex-col gap-1">
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Desde</label>
            <input type="date" id="filtro-desde"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                value="<?= date('Y-m-d') ?>">
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Hasta</label>
            <input type="date" id="filtro-hasta"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                value="<?= date('Y-m-d') ?>">
        </div>
        <button onclick="cargarVentas()"
            class="px-5 py-2 bg-indigo-600 text-white text-sm font-bold rounded-lg hover:bg-indigo-700 active:scale-95 transition-all">
            Buscar
        </button>
        <button onclick="setRango('hoy')"
            class="px-4 py-2 text-sm font-semibold rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
            Hoy
        </button>
        <button onclick="setRango('semana')"
            class="px-4 py-2 text-sm font-semibold rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
            Esta semana
        </button>
        <button onclick="setRango('mes')"
            class="px-4 py-2 text-sm font-semibold rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
            Este mes
        </button>
    </div>

    <!-- Tarjetas resumen -->
    <div class="grid grid-cols-2 gap-4" style="grid-template-columns: repeat(4, minmax(0, 1fr));">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex flex-col gap-1">
            <div class="text-xs font-semibold text-gray-400 uppercase tracking-wide">N° Ventas</div>
            <div id="sum-n" class="text-3xl font-black text-gray-800">—</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex flex-col gap-1">
            <div class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Total Cobrado</div>
            <div id="sum-total" class="text-3xl font-black text-indigo-700">—</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex flex-col gap-1">
            <div class="text-xs font-semibold text-gray-400 uppercase tracking-wide">IGV Total</div>
            <div id="sum-igv" class="text-3xl font-black text-orange-600">—</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex flex-col gap-1">
            <div class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Neto sin IGV</div>
            <div id="sum-neto" class="text-3xl font-black text-green-700">—</div>
        </div>
    </div>

    <!-- Tabla de ventas -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 flex-1 overflow-hidden flex flex-col">
        <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
            <span class="font-bold text-gray-700">Listado de ventas</span>
            <span id="tabla-badge" class="text-xs text-gray-400"></span>
        </div>
        <div class="overflow-x-auto flex-1">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                        <th class="px-4 py-3 text-left font-semibold">#</th>
                        <th class="px-4 py-3 text-left font-semibold">Fecha / Hora</th>
                        <th class="px-4 py-3 text-left font-semibold">Mesa</th>
                        <th class="px-4 py-3 text-left font-semibold">Cajero</th>
                        <th class="px-4 py-3 text-left font-semibold">Método</th>
                        <th class="px-4 py-3 text-right font-semibold">Items</th>
                        <th class="px-4 py-3 text-right font-semibold">Total</th>
                        <th class="px-4 py-3 text-center font-semibold">Detalle</th>
                    </tr>
                </thead>
                <tbody id="tabla-body">
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-gray-400 text-sm">
                            Selecciona un rango de fechas y presiona Buscar
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</main>

<!-- Modal detalle de venta -->
<div id="modal-detalle" style="display:none;z-index:60"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg flex flex-col" style="max-height:90vh">

        <!-- Header modal -->
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
            <div>
                <h2 class="text-lg font-bold text-gray-800" id="det-titulo">Detalle de venta</h2>
                <div class="text-xs text-gray-400" id="det-fecha"></div>
            </div>
            <button onclick="cerrarDetalle()"
                class="w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-500 text-lg">✕</button>
        </div>

        <!-- Info rápida -->
        <div class="px-6 py-3 bg-gray-50 grid grid-cols-3 gap-3 shrink-0 text-center">
            <div>
                <div class="text-xs text-gray-400 font-semibold">Cajero</div>
                <div id="det-cajero" class="text-sm font-bold text-gray-700">—</div>
            </div>
            <div>
                <div class="text-xs text-gray-400 font-semibold">Método</div>
                <div id="det-metodo" class="text-sm font-bold text-gray-700">—</div>
            </div>
            <div>
                <div class="text-xs text-gray-400 font-semibold">Mesa</div>
                <div id="det-mesa" class="text-sm font-bold text-gray-700">—</div>
            </div>
        </div>

        <!-- Tabla items -->
        <div class="flex-1 overflow-y-auto">
            <table class="w-full text-sm">
                <thead class="sticky top-0 bg-white">
                    <tr class="text-xs text-gray-400 uppercase border-b border-gray-100">
                        <th class="px-5 py-2 text-left font-semibold">Producto</th>
                        <th class="px-5 py-2 text-right font-semibold">Cant.</th>
                        <th class="px-5 py-2 text-right font-semibold">P.Unit</th>
                        <th class="px-5 py-2 text-right font-semibold">Subtotal</th>
                    </tr>
                </thead>
                <tbody id="det-items"></tbody>
            </table>
        </div>

        <!-- Totales pie -->
        <div class="px-6 py-4 border-t border-gray-100 shrink-0 space-y-1">
            <div class="flex justify-between text-sm text-gray-500">
                <span>Neto</span>
                <span id="det-neto" class="font-semibold"></span>
            </div>
            <div class="flex justify-between text-sm text-gray-500">
                <span>IGV</span>
                <span id="det-igv" class="font-semibold"></span>
            </div>
            <div class="flex justify-between text-base font-black text-gray-800 pt-2 border-t border-gray-200">
                <span>TOTAL</span>
                <span id="det-total"></span>
            </div>
            <div class="flex justify-between text-sm text-gray-400">
                <span>Recibido</span>
                <span id="det-recibido"></span>
            </div>
            <div class="flex justify-between text-sm text-gray-400">
                <span>Vuelto</span>
                <span id="det-vuelto"></span>
            </div>
        </div>

        <!-- Footer: botón imprimir -->
        <div class="px-6 py-3 border-t border-gray-100 shrink-0 flex items-center gap-3">
            <div class="relative flex-1">
                <button onclick="toggleImpresoras()"
                    id="btn-imprimir"
                    class="w-full px-4 py-2 bg-gray-800 text-white text-sm font-bold rounded-lg hover:bg-gray-700 active:scale-95 transition-all flex items-center justify-center gap-2">
                    🖨️ <span>Imprimir boleta</span>
                </button>
                <!-- Dropdown impresoras -->
                <div id="panel-impresoras" style="display:none;z-index:70"
                    class="absolute bottom-full mb-2 left-0 right-0 bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden">
                    <div class="px-4 py-2 text-xs font-bold text-gray-400 uppercase border-b border-gray-100">
                        Seleccionar impresora
                    </div>
                    <div id="lista-impresoras" class="max-h-48 overflow-y-auto">
                        <div class="px-4 py-3 text-sm text-gray-400">Cargando...</div>
                    </div>
                </div>
            </div>
            <button onclick="cerrarDetalle()"
                class="px-4 py-2 text-sm font-semibold rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                Cerrar
            </button>
        </div>

    </div>
</div>

<script>
const BASE = '<?= base_url() ?>';

function sol(n) {
    return 'S/ ' + parseFloat(n || 0).toFixed(2);
}

function fmt(dt) {
    if (!dt) return '—';
    const d = new Date(dt);
    return d.toLocaleDateString('es-PE', {day:'2-digit', month:'2-digit', year:'numeric'})
        + ' ' + d.toLocaleTimeString('es-PE', {hour:'2-digit', minute:'2-digit'});
}

function metodoBadge(m) {
    const map = {
        efectivo: ['bg-green-100 text-green-700', 'Efectivo'],
        yape:     ['bg-purple-100 text-purple-700', 'Yape'],
        plin:     ['bg-teal-100 text-teal-700', 'Plin'],
        tarjeta:  ['bg-blue-100 text-blue-700', 'Tarjeta'],
        transferencia: ['bg-indigo-100 text-indigo-700', 'Transferencia'],
    };
    const [cls, label] = map[m] ?? ['bg-gray-100 text-gray-700', m ?? '—'];
    return `<span class="px-2 py-0.5 rounded-full text-xs font-bold ${cls}">${label}</span>`;
}

function setRango(tipo) {
    const hoy = new Date();
    const pad = n => String(n).padStart(2,'0');
    const iso = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
    let desde, hasta;
    if (tipo === 'hoy') {
        desde = hasta = iso(hoy);
    } else if (tipo === 'semana') {
        const lunes = new Date(hoy);
        lunes.setDate(hoy.getDate() - ((hoy.getDay() + 6) % 7));
        desde = iso(lunes);
        hasta = iso(hoy);
    } else if (tipo === 'mes') {
        desde = `${hoy.getFullYear()}-${pad(hoy.getMonth()+1)}-01`;
        hasta = iso(hoy);
    }
    document.getElementById('filtro-desde').value = desde;
    document.getElementById('filtro-hasta').value = hasta;
    cargarVentas();
}

async function cargarVentas() {
    const desde = document.getElementById('filtro-desde').value;
    const hasta = document.getElementById('filtro-hasta').value;

    const tbody = document.getElementById('tabla-body');
    tbody.innerHTML = `<tr><td colspan="8" class="px-4 py-10 text-center text-gray-400">Cargando...</td></tr>`;

    document.getElementById('sum-n').textContent     = '—';
    document.getElementById('sum-total').textContent = '—';
    document.getElementById('sum-igv').textContent   = '—';
    document.getElementById('sum-neto').textContent  = '—';

    try {
        const res  = await fetch(`${BASE}api/ventas/get_lista?desde=${desde}&hasta=${hasta}`);
        const data = await res.json();

        const t = data.totales || {};
        document.getElementById('sum-n').textContent     = t.n_ventas    || 0;
        document.getElementById('sum-total').textContent = sol(t.sum_total);
        document.getElementById('sum-igv').textContent   = sol(t.sum_igv);
        document.getElementById('sum-neto').textContent  = sol(t.sum_neto);
        document.getElementById('tabla-badge').textContent = `${t.n_ventas || 0} registros`;

        const ventas = data.ventas || [];
        if (!ventas.length) {
            tbody.innerHTML = `<tr><td colspan="8" class="px-4 py-10 text-center text-gray-400">Sin ventas en el rango seleccionado</td></tr>`;
            return;
        }

        tbody.innerHTML = ventas.map((v, i) => `
            <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                <td class="px-4 py-3 text-gray-400 text-xs">${v.id_venta}</td>
                <td class="px-4 py-3 text-gray-700 whitespace-nowrap">${fmt(v.fecha_registro)}</td>
                <td class="px-4 py-3 text-gray-600">Mesa ${v.id_mesa}</td>
                <td class="px-4 py-3 text-gray-600">${v.nombre_cajero || '—'}</td>
                <td class="px-4 py-3">${metodoBadge(v.metodo_pago)}</td>
                <td class="px-4 py-3 text-right text-gray-500">${v.n_items}</td>
                <td class="px-4 py-3 text-right font-bold text-gray-800">${sol(v.total)}</td>
                <td class="px-4 py-3 text-center">
                    <button onclick="verDetalle(${v.id_venta})"
                        class="px-3 py-1 text-xs font-bold bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 transition-colors">
                        Ver
                    </button>
                </td>
            </tr>`).join('');

    } catch (e) {
        tbody.innerHTML = `<tr><td colspan="8" class="px-4 py-10 text-center text-red-400">Error al cargar las ventas</td></tr>`;
    }
}

async function verDetalle(id) {
    ventaActual = id;
    document.getElementById('panel-impresoras').style.display = 'none';
    const btn = document.getElementById('btn-imprimir');
    btn.innerHTML = '🖨️ <span>Imprimir boleta</span>';
    btn.classList.remove('bg-green-700','bg-red-700');
    btn.classList.add('bg-gray-800');
    btn.disabled = false;

    document.getElementById('det-items').innerHTML =
        `<tr><td colspan="4" class="px-5 py-6 text-center text-gray-400 text-sm">Cargando...</td></tr>`;
    document.getElementById('modal-detalle').style.display = 'flex';

    try {
        const res  = await fetch(`${BASE}api/ventas/get_detalle/${id}`);
        const data = await res.json();
        const v    = data.venta;
        const det  = data.detalle || [];

        document.getElementById('det-titulo').textContent  = `Venta #${v.id_venta}`;
        document.getElementById('det-fecha').textContent   = fmt(v.fecha_registro);
        document.getElementById('det-cajero').textContent  = v.nombre_cajero || '—';
        document.getElementById('det-metodo').textContent  = v.metodo_pago   || '—';
        document.getElementById('det-mesa').textContent    = `Mesa ${v.id_mesa}`;
        document.getElementById('det-neto').textContent    = sol(v.total_neto);
        document.getElementById('det-igv').textContent     = sol(v.total_igv);
        document.getElementById('det-total').textContent   = sol(v.total);
        document.getElementById('det-recibido').textContent = sol(v.recibido);
        document.getElementById('det-vuelto').textContent  = sol(v.vuelto);

        document.getElementById('det-items').innerHTML = det.map(d => `
            <tr class="border-b border-gray-50">
                <td class="px-5 py-2.5 text-gray-700">${d.descripcion || '—'}</td>
                <td class="px-5 py-2.5 text-right text-gray-600">${d.cantidad}</td>
                <td class="px-5 py-2.5 text-right text-gray-600">${sol(d.precio)}</td>
                <td class="px-5 py-2.5 text-right font-semibold text-gray-800">${sol(d.subtotal)}</td>
            </tr>`).join('');

    } catch (e) {
        document.getElementById('det-items').innerHTML =
            `<tr><td colspan="4" class="px-5 py-6 text-center text-red-400">Error al cargar el detalle</td></tr>`;
    }
}

function cerrarDetalle() {
    document.getElementById('modal-detalle').style.display = 'none';
    document.getElementById('panel-impresoras').style.display = 'none';
}

document.getElementById('modal-detalle').addEventListener('click', function(e) {
    if (e.target === this) cerrarDetalle();
});

// ── Impresoras ─────────────────────────────────────────────────────────────
let impresoras = [];
let ventaActual = null;

async function toggleImpresoras() {
    const panel = document.getElementById('panel-impresoras');
    if (panel.style.display !== 'none') {
        panel.style.display = 'none';
        return;
    }
    panel.style.display = 'block';

    if (!impresoras.length) {
        try {
            const res  = await fetch(`${BASE}api/impresoras/get_all`);
            const data = await res.json();
            impresoras = data.impresoras || [];
        } catch (e) {
            impresoras = [];
        }
    }

    const lista = document.getElementById('lista-impresoras');
    if (!impresoras.length) {
        lista.innerHTML = '<div class="px-4 py-3 text-sm text-gray-400">No hay impresoras configuradas</div>';
        return;
    }
    lista.innerHTML = impresoras.map(imp => `
        <button type="button" onclick="enviarImpresion(${imp.id},'${imp.nombre}')"
            class="w-full text-left px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50 flex items-center gap-2 border-b border-gray-50 last:border-0">
            🖨️ ${imp.nombre}
            <span class="ml-auto text-xs text-gray-400 font-normal">${imp.ip}</span>
        </button>`).join('');
}

async function enviarImpresion(idImpresora, nombreImpresora) {
    if (!ventaActual) return;

    document.getElementById('panel-impresoras').style.display = 'none';
    const btn = document.getElementById('btn-imprimir');
    btn.innerHTML = '⏳ <span>Enviando...</span>';
    btn.disabled = true;

    try {
        const res  = await fetch(`${BASE}api/ventas/imprimir`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id_venta: ventaActual, id_impresora: idImpresora}),
        });
        const data = await res.json();

        if (data.success) {
            btn.innerHTML = '✅ <span>Enviado a ' + nombreImpresora + '</span>';
            btn.classList.replace('bg-gray-800', 'bg-green-700');
            setTimeout(() => {
                btn.innerHTML = '🖨️ <span>Imprimir boleta</span>';
                btn.classList.replace('bg-green-700', 'bg-gray-800');
                btn.disabled = false;
            }, 3000);
        } else {
            throw new Error(data.messages?.error || 'Error al imprimir');
        }
    } catch (e) {
        btn.innerHTML = '❌ <span>' + e.message + '</span>';
        btn.classList.replace('bg-gray-800', 'bg-red-700');
        setTimeout(() => {
            btn.innerHTML = '🖨️ <span>Imprimir boleta</span>';
            btn.classList.replace('bg-red-700', 'bg-gray-800');
            btn.disabled = false;
        }, 4000);
    }
}

// Cargar hoy al entrar
cargarVentas();
</script>

<?= $this->endSection() ?>
