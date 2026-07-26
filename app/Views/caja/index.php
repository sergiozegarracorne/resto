<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Caja / Cuadre<?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="flex flex-col h-full bg-gray-50">

    <!-- HEADER -->
    <header class="h-14 bg-gray-800 flex items-center justify-between px-4 shrink-0">
        <div class="flex items-center gap-3">
            <a href="<?= base_url('panel') ?>"
               class="h-9 px-3 flex items-center gap-1.5 bg-gray-700 hover:bg-gray-600 text-gray-200 text-sm font-semibold rounded transition">
                ← Panel
            </a>
            <span class="text-white font-bold text-base">💵 Caja / Cuadre</span>
        </div>
    </header>

    <!-- BARRA DE FILTROS -->
    <div class="bg-white border-b border-gray-200 px-4 py-3 flex flex-wrap items-end gap-3 shrink-0">
        <div class="flex items-end gap-2">
            <div>
                <label class="block text-[10px] text-gray-400 uppercase font-bold mb-1">Desde</label>
                <input type="date" id="f-desde"
                       class="h-9 px-3 border border-gray-200 rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <span class="text-gray-400 pb-2">→</span>
            <div>
                <label class="block text-[10px] text-gray-400 uppercase font-bold mb-1">Hasta</label>
                <input type="date" id="f-hasta"
                       class="h-9 px-3 border border-gray-200 rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
        </div>

        <div>
            <label class="block text-[10px] text-gray-400 uppercase font-bold mb-1">Cajero</label>
            <select id="f-cajero"
                    class="h-9 px-3 border border-gray-200 rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                <option value="">Todos</option>
            </select>
        </div>

        <div>
            <label class="block text-[10px] text-gray-400 uppercase font-bold mb-1">Método de pago</label>
            <select id="f-metodo"
                    class="h-9 px-3 border border-gray-200 rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                <option value="">Todos</option>
                <option value="efectivo">💵 Efectivo</option>
                <option value="tarjeta">💳 Tarjeta</option>
                <option value="monedero">📱 Digital</option>
            </select>
        </div>

        <button onclick="cargar()"
                class="h-9 px-5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg transition active:scale-95 flex items-center gap-2">
            🔍 Filtrar
        </button>

        <div id="lbl-estado" class="text-xs text-gray-400 self-center"></div>
    </div>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="flex-1 overflow-y-auto p-4 space-y-4">

        <!-- CARDS RESUMEN POR MÉTODO -->
        <div id="cards-metodos" class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <?php for ($i = 0; $i < 4; $i++): ?>
            <div class="bg-white rounded-xl border border-gray-100 p-4 animate-pulse h-24"></div>
            <?php endfor; ?>
        </div>

        <!-- TOTALES FISCALES -->
        <div id="row-fiscal" class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <?php for ($i = 0; $i < 4; $i++): ?>
            <div class="bg-white rounded-xl border border-gray-100 p-4 animate-pulse h-20"></div>
            <?php endfor; ?>
        </div>

        <!-- TABLA DE VENTAS -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <span class="font-bold text-gray-700">Detalle de Ventas</span>
                <span id="lbl-count" class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded-full"></span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-2 text-left">#</th>
                            <th class="px-4 py-2 text-left">Hora</th>
                            <th class="px-4 py-2 text-left">Mesa</th>
                            <th class="px-4 py-2 text-left">Cajero</th>
                            <th class="px-4 py-2 text-left">Método</th>
                            <th class="px-4 py-2 text-left">Productos</th>
                            <th class="px-4 py-2 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-ventas">
                        <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Cargando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

<script>
const BASE = '<?= base_url() ?>';

const METODO_ICON  = { efectivo: '💵', tarjeta: '💳', monedero: '📱', digital: '📱' };
const METODO_LABEL = { efectivo: '💵 Efectivo', tarjeta: '💳 Tarjeta', monedero: '📱 Digital', digital: '📱 Digital' };
const METODO_COLOR = {
    efectivo: 'border-emerald-400 text-emerald-700 bg-emerald-50',
    tarjeta:  'border-blue-400   text-blue-700   bg-blue-50',
    monedero: 'border-purple-400 text-purple-700 bg-purple-50',
    digital:  'border-purple-400 text-purple-700 bg-purple-50',
};

function fmt(v) { return 'S/ ' + parseFloat(v || 0).toFixed(2); }

// Inicializar fechas con hoy
(function() {
    const hoy = new Date().toISOString().slice(0, 10);
    document.getElementById('f-desde').value = hoy;
    document.getElementById('f-hasta').value = hoy;
})();

async function cargar() {
    const desde  = document.getElementById('f-desde').value;
    const hasta  = document.getElementById('f-hasta').value;
    const cajero = document.getElementById('f-cajero').value;
    const metodo = document.getElementById('f-metodo').value;

    document.getElementById('lbl-estado').innerText = 'Cargando...';

    const params = new URLSearchParams({ fecha_desde: desde, fecha_hasta: hasta });
    if (cajero) params.set('cajero', cajero);
    if (metodo) params.set('metodo', metodo);

    try {
        const res  = await fetch(`${BASE}api/caja/get_data?${params}`);
        const data = await res.json();

        if (!data.success) {
            document.getElementById('lbl-estado').innerText = 'Error al cargar';
            return;
        }

        renderCards(data);
        renderFiscal(data);
        renderVentas(data);
        actualizarCajeros(data.cajeros || []);

        const n = (data.ventas || []).length;
        document.getElementById('lbl-estado').innerText = `${n} venta${n !== 1 ? 's' : ''}`;
    } catch(e) {
        console.error(e);
        document.getElementById('lbl-estado').innerText = 'Error de conexión';
    }
}

function actualizarCajeros(cajeros) {
    const sel    = document.getElementById('f-cajero');
    const actual = sel.value;
    sel.innerHTML = '<option value="">Todos</option>' +
        cajeros.map(c => `<option value="${c}" ${c === actual ? 'selected' : ''}>${c}</option>`).join('');
}

function renderCards(data) {
    const por    = data.por_metodo || [];
    const totales = data.totales   || {};
    const conocidos = ['efectivo', 'tarjeta', 'monedero'];
    const mapa   = Object.fromEntries(por.map(m => [m.metodo, m]));

    let html = conocidos.map(key => {
        const m     = mapa[key] || { cantidad: 0, total: 0 };
        const color = METODO_COLOR[key] || 'border-gray-300 text-gray-700 bg-gray-50';
        const label = key.charAt(0).toUpperCase() + key.slice(1);
        return `
        <div class="bg-white rounded-xl border-l-4 ${color} p-4 flex flex-col gap-1 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wide opacity-60">${label}</span>
                <span class="text-2xl">${METODO_ICON[key] || '💰'}</span>
            </div>
            <p class="text-2xl font-black">${fmt(m.total)}</p>
            <p class="text-xs opacity-50">${m.cantidad || 0} venta${m.cantidad != 1 ? 's' : ''}</p>
        </div>`;
    }).join('');

    html += `
    <div class="bg-gray-800 rounded-xl shadow-sm p-4 flex flex-col gap-1 text-white">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wide opacity-60">Total General</span>
            <span class="text-2xl">🧾</span>
        </div>
        <p class="text-2xl font-black">${fmt(totales.total_bruto)}</p>
        <p class="text-xs opacity-50">${totales.cantidad || 0} venta${totales.cantidad != 1 ? 's' : ''}</p>
    </div>`;

    document.getElementById('cards-metodos').innerHTML = html;
}

function renderFiscal(data) {
    const t = data.totales || {};
    const items = [
        { label: 'Total Bruto', value: t.total_bruto, icon: '🧾', color: 'border-gray-300' },
        { label: 'IGV (18%)',   value: t.total_igv,   icon: '📋', color: 'border-amber-400' },
        { label: 'ICBR',        value: t.total_icbr,  icon: '♻️',  color: 'border-orange-400' },
        { label: 'Total Neto',  value: t.total_neto,  icon: '✅',  color: 'border-emerald-400' },
    ];
    document.getElementById('row-fiscal').innerHTML = items.map(i => `
        <div class="bg-white rounded-xl border-l-4 ${i.color} px-4 py-3 flex items-center justify-between shadow-sm">
            <div>
                <p class="text-xs text-gray-400 uppercase font-bold">${i.label}</p>
                <p class="text-xl font-black text-gray-800">${fmt(i.value)}</p>
            </div>
            <span class="text-2xl opacity-70">${i.icon}</span>
        </div>
    `).join('');
}

function renderVentas(data) {
    const ventas = data.ventas || [];
    document.getElementById('lbl-count').innerText = `${ventas.length} registro${ventas.length !== 1 ? 's' : ''}`;

    if (ventas.length === 0) {
        document.getElementById('tabla-ventas').innerHTML =
            '<tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">Sin ventas para el período seleccionado</td></tr>';
        return;
    }

    document.getElementById('tabla-ventas').innerHTML = ventas.map(v => {
        const hora   = v.fecha_registro ? v.fecha_registro.slice(11, 16) : '--:--';
        const fecha  = v.fecha_registro ? v.fecha_registro.slice(0, 10)  : '';
        const metodo = METODO_LABEL[v.metodo_pago] || (v.metodo_pago || '—');
        const cajero = v.nombre_cajero || '—';
        return `
        <tr class="border-t border-gray-50 hover:bg-gray-50 transition-colors">
            <td class="px-4 py-3 text-gray-400 text-xs">#${v.id_venta}</td>
            <td class="px-4 py-3 font-mono text-gray-600 text-xs">
                <span class="block">${hora}</span>
                <span class="text-gray-400">${fecha}</span>
            </td>
            <td class="px-4 py-3 font-semibold text-gray-700">${v.mesa_nombre || '—'}</td>
            <td class="px-4 py-3 text-gray-600 text-xs">${cajero}</td>
            <td class="px-4 py-3 text-gray-600 text-xs">${metodo}</td>
            <td class="px-4 py-3 text-gray-500 text-xs max-w-xs truncate" title="${v.items_resumen || ''}">${v.items_resumen || '—'}</td>
            <td class="px-4 py-3 text-right font-bold text-gray-800">${fmt(v.total)}</td>
        </tr>`;
    }).join('');
}

// Carga inicial
cargar();
</script>

<?= $this->endSection() ?>
