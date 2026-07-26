<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Impresoras de Red<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="flex flex-col h-full">

    <!-- ── HEADER ───────────────────────────────────────────────── -->
    <header class="bg-white border-b border-gray-200 px-5 py-3 flex items-center justify-between shrink-0">
        <h1 class="text-lg font-bold text-gray-800">🖨️ Impresoras de Red</h1>
        <div class="flex gap-2">
            <button onclick="abrirScan()"
                class="px-3 py-1.5 rounded-lg text-sm font-bold border border-gray-300 hover:bg-gray-50">
                📡 Escanear
            </button>
            <button onclick="guardarTodo()"
                class="px-4 py-1.5 rounded-lg text-sm font-bold bg-blue-600 text-white hover:bg-blue-700">
                💾 Guardar
            </button>
        </div>
    </header>

    <!-- ── BARRA DE IMPRESORAS ───────────────────────────────────── -->
    <div class="bg-gray-50 border-b border-gray-200 px-5 py-2 flex items-center gap-3 flex-wrap shrink-0">
        <span class="text-xs font-bold text-gray-400 uppercase">Dispositivos:</span>
        <div id="chips-impresoras" class="flex gap-2 flex-wrap flex-1 items-center">
            <span class="text-xs text-gray-400">Cargando...</span>
        </div>
        <button onclick="abrirFormImpresora()"
            class="text-xs bg-white border border-gray-300 px-3 py-1 rounded-full font-bold hover:bg-gray-100 shrink-0">
            + Nueva
        </button>
    </div>

    <!-- ── CUERPO: 3 COLUMNAS ────────────────────────────────────── -->
    <div class="flex flex-1 overflow-hidden">

        <!-- COLUMNA 1: Categorías -->
        <div class="w-48 shrink-0 border-r border-gray-200 bg-gray-50 flex flex-col">
            <div class="px-3 py-2 border-b border-gray-200">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Categorías</p>
            </div>
            <div id="lista-categorias" class="flex-1 overflow-y-auto"></div>
        </div>

        <!-- COLUMNA 2: Detalle de categoría -->
        <div class="flex-1 overflow-y-auto p-5">

            <!-- Placeholder -->
            <div id="panel-vacio" class="flex flex-col items-center justify-center h-full text-gray-300 gap-3">
                <span class="text-5xl">👈</span>
                <p class="text-sm font-medium">Selecciona una categoría</p>
            </div>

            <!-- Detalle -->
            <div id="panel-categoria" class="hidden space-y-5">

                <!-- Impresora por defecto -->
                <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
                    <p class="text-xs font-bold text-gray-400 uppercase mb-1">
                        Impresora para <span id="cat-titulo" class="text-gray-700"></span>
                    </p>
                    <select id="sel-cat-impresora"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-semibold text-gray-700 mt-1">
                    </select>
                    <p class="text-xs text-gray-400 mt-2">
                        Todos los productos de esta categoría se enviarán a esta impresora,
                        salvo que tengan una excepción configurada.
                    </p>
                </div>

                <!-- Lista de productos -->
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase mb-2">
                        Productos (<span id="cat-prod-count">0</span>)
                    </p>
                    <div id="lista-prods-cat" class="space-y-1"></div>
                </div>

            </div>
        </div>

    </div>
</div>

<!-- ════════════════════════════════════════════════════════
     MODAL: Agregar / Editar Impresora
════════════════════════════════════════════════════════ -->
<div id="modal-impresora" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-sm rounded-2xl shadow-xl">
        <div class="p-5 border-b flex justify-between items-center">
            <h2 id="modal-imp-titulo" class="font-bold text-lg">Nueva Impresora</h2>
            <button onclick="cerrarFormImpresora()" class="text-2xl text-gray-400 leading-none">&times;</button>
        </div>
        <form onsubmit="guardarImpresora(event)" class="p-5 space-y-4">
            <input type="hidden" id="imp-id">
            <div>
                <label class="text-xs font-bold text-gray-500 uppercase">Nombre</label>
                <input type="text" id="imp-nombre" placeholder="Cocina, Bar, Horno..."
                    class="w-full border rounded-lg px-3 py-2 mt-1 text-sm" required>
            </div>
            <div>
                <label class="text-xs font-bold text-gray-500 uppercase">Dirección IP</label>
                <input type="text" id="imp-ip" placeholder="192.168.10.10"
                    pattern="\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}"
                    class="w-full border rounded-lg px-3 py-2 mt-1 text-sm font-mono" required>
            </div>
            <div>
                <label class="text-xs font-bold text-gray-500 uppercase">Puerto</label>
                <input type="number" id="imp-puerto" value="9100"
                    class="w-full border rounded-lg px-3 py-2 mt-1 text-sm" required>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="cerrarFormImpresora()"
                    class="flex-1 py-2 rounded-lg bg-gray-100 font-bold text-sm">Cancelar</button>
                <button type="submit"
                    class="flex-1 py-2 rounded-lg bg-blue-600 text-white font-bold text-sm">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════
     MODAL: Escanear Red
════════════════════════════════════════════════════════ -->
<div id="modal-scan" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-xl">
        <div class="p-5 border-b flex justify-between items-center">
            <h2 class="font-bold text-lg">📡 Escanear Red</h2>
            <button onclick="cerrarScan()" class="text-2xl text-gray-400 leading-none">&times;</button>
        </div>
        <div class="p-5 space-y-4">
            <div class="flex gap-2 items-end">
                <div class="flex-1">
                    <label class="text-xs font-bold text-gray-500 uppercase">Subred (3 octetos)</label>
                    <input type="text" id="scan-subnet" value="192.168.10"
                        class="w-full border rounded-lg px-3 py-2 mt-1 text-sm font-mono">
                </div>
                <button onclick="ejecutarScan()" id="btn-scan"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg font-bold text-sm">
                    Buscar
                </button>
            </div>
            <div id="scan-resultado" class="min-h-20 border rounded-xl p-3 bg-gray-50 text-center">
                <p class="text-xs text-gray-400 py-3">Ingresa la subred y presiona Buscar.</p>
            </div>
        </div>
    </div>
</div>

<!-- TOAST -->
<div id="toast"
    class="hidden fixed bottom-6 right-6 z-[100] px-5 py-3 rounded-xl shadow-xl text-white text-sm font-bold"
    style="min-width:200px"></div>

<!-- ════════════════════════════════════════════════════════
     SCRIPT
════════════════════════════════════════════════════════ -->
<script>
const BASE = '<?= base_url() ?>';

let impresoras  = [];   // [{id, nombre, ip, puerto}]
let categorias  = [];   // [{id, nombre, id_impresora}]
let excepciones = [];   // [{id_producto, nombre, id_impresora}]
let productos   = [];   // todos los productos
let catSelId    = null; // categoría activa

// ────────────────────────────────────────────────────────
// INIT
// ────────────────────────────────────────────────────────
async function cargar() {
    const [r1, r2, r3] = await Promise.all([
        fetch(BASE + 'api/impresoras/get_all').then(r => r.json()),
        fetch(BASE + 'api/impresoras/get_routing').then(r => r.json()),
        fetch(BASE + 'api/productos/get_all').then(r => r.json()),
    ]);

    // Normalizar TODOS los IDs a enteros (MySQLi devuelve números como strings)
    impresoras = (r1.impresoras || []).map(i => ({
        ...i,
        id:     parseInt(i.id),
        puerto: parseInt(i.puerto),
    }));

    categorias = (r2.categorias || []).map(c => ({
        ...c,
        id:           parseInt(c.id),
        id_impresora: c.id_impresora ? parseInt(c.id_impresora) : null,
    }));

    excepciones = (r2.excepciones || []).map(e => ({
        ...e,
        id_producto:  parseInt(e.id_producto),
        id_impresora: e.id_impresora ? parseInt(e.id_impresora) : null,
    }));

    productos = (r3.productos || [])
        .filter(p => p.activo == 1)
        .map(p => ({
            ...p,
            id:           parseInt(p.id),
            categoria_id: parseInt(p.categoria_id),
        }));

    renderChips();
    renderCategorias();
    if (catSelId) selectCategoria(catSelId);
}

// ────────────────────────────────────────────────────────
// CHIPS DE IMPRESORAS (barra superior)
// ────────────────────────────────────────────────────────
function renderChips() {
    const cont = document.getElementById('chips-impresoras');
    if (!impresoras.length) {
        cont.innerHTML = '<span class="text-xs text-gray-400">Sin impresoras — agrega una con + Nueva.</span>';
        return;
    }
    cont.innerHTML = impresoras.map(imp => `
        <span class="inline-flex items-center gap-1.5 bg-white border border-gray-200 rounded-full px-3 py-1 text-xs shadow-sm">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0"></span>
            <span class="font-bold text-gray-700">${imp.nombre}</span>
            <span class="text-gray-400 font-mono">${imp.ip}</span>
            <button onclick="testImpresora(${imp.id})" title="Prueba de impresión"
                class="text-gray-400 hover:text-emerald-600 transition-colors ml-0.5">🖨</button>
            <button onclick="editarImpresora(${imp.id})" title="Editar"
                class="text-gray-400 hover:text-blue-600 transition-colors">✏</button>
            <button onclick="eliminarImpresora(${imp.id})" title="Eliminar"
                class="text-gray-400 hover:text-red-500 transition-colors font-bold">×</button>
        </span>
    `).join('');
}

// ────────────────────────────────────────────────────────
// COLUMNA 1: Lista de categorías
// ────────────────────────────────────────────────────────
function renderCategorias() {
    const cont = document.getElementById('lista-categorias');
    if (!categorias.length) {
        cont.innerHTML = '<p class="text-xs text-gray-400 text-center py-6">Sin categorías.</p>';
        return;
    }

    cont.innerHTML = categorias.map(cat => {
        const imp    = impresoras.find(i => i.id == cat.id_impresora);
        const activa = catSelId == cat.id;

        return `
        <button onclick="selectCategoria(${cat.id})"
            id="cat-btn-${cat.id}"
            class="w-full text-left px-3 py-3 border-b border-gray-100 hover:bg-white transition-all flex items-center justify-between gap-2
                   ${activa ? 'bg-white border-l-4 border-l-blue-500 shadow-sm' : 'border-l-4 border-l-transparent'}">
            <span class="text-sm font-semibold text-gray-700 truncate">${cat.nombre}</span>
            <span class="cat-ind-${cat.id} shrink-0 text-xs ${imp ? 'text-blue-500' : 'text-gray-300'}">
                ${imp ? '🖨' : '—'}
            </span>
        </button>`;
    }).join('');
}

function actualizarIndicadorCat(catId) {
    const cat = categorias.find(c => c.id == catId);
    const el  = document.querySelector(`.cat-ind-${catId}`);
    if (!el || !cat) return;
    const imp = impresoras.find(i => i.id == cat.id_impresora);
    el.textContent = imp ? '🖨' : '—';
    el.className   = `cat-ind-${catId} shrink-0 text-xs ${imp ? 'text-blue-500' : 'text-gray-300'}`;
}

// ────────────────────────────────────────────────────────
// COLUMNA 2: Seleccionar categoría
// ────────────────────────────────────────────────────────
function selectCategoria(catId) {
    catSelId = catId;

    // Resaltar botón activo en columna 1
    document.querySelectorAll('[id^="cat-btn-"]').forEach(b => {
        b.classList.remove('bg-white', 'border-l-blue-500', 'shadow-sm');
        b.classList.add('border-l-transparent');
    });
    const btn = document.getElementById(`cat-btn-${catId}`);
    if (btn) {
        btn.classList.add('bg-white', 'border-l-blue-500', 'shadow-sm');
        btn.classList.remove('border-l-transparent');
    }

    const cat = categorias.find(c => c.id == catId);
    if (!cat) return;

    document.getElementById('panel-vacio').classList.add('hidden');
    document.getElementById('panel-categoria').classList.remove('hidden');

    // Título
    document.getElementById('cat-titulo').textContent = `"${cat.nombre}"`;

    // Select de impresora
    const sel = document.getElementById('sel-cat-impresora');
    sel.innerHTML =
        `<option value="">— Sin impresora (no imprime) —</option>` +
        impresoras.map(imp =>
            `<option value="${imp.id}" ${cat.id_impresora === imp.id ? 'selected' : ''}>
                🖨 ${imp.nombre} — ${imp.ip}
            </option>`
        ).join('');

    sel.onchange = () => {
        cat.id_impresora = sel.value ? parseInt(sel.value) : null;
        actualizarIndicadorCat(catId);
    };

    // Productos de la categoría
    const prods = productos.filter(p => p.categoria_id == catId);
    document.getElementById('cat-prod-count').textContent = prods.length;

    const cont = document.getElementById('lista-prods-cat');

    if (!prods.length) {
        cont.innerHTML = '<p class="text-xs text-gray-400 text-center py-4">Sin productos en esta categoría.</p>';
        return;
    }

    cont.innerHTML = prods.map(p => {
        const exc    = excepciones.find(e => e.id_producto == p.id);
        const excImp = exc ? impresoras.find(i => i.id == exc.id_impresora) : null;

        if (exc) {
            return `
            <div class="flex items-center justify-between px-3 py-2 rounded-lg bg-amber-50 border border-amber-200 gap-2">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="text-amber-500 shrink-0 text-xs font-bold">⚡</span>
                    <span class="text-sm font-semibold text-gray-700 truncate">${p.nombre}</span>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <select onchange="cambiarImpresoraExcProd(${p.id}, this.value)"
                        class="text-xs border border-amber-300 bg-white rounded-lg px-2 py-1">
                        <option value="">🚫 No imprimir</option>
                        ${impresoras.map(imp => `
                            <option value="${imp.id}" ${exc.id_impresora == imp.id ? 'selected' : ''}>
                                🖨 ${imp.nombre}
                            </option>`).join('')}
                    </select>
                    <button onclick="quitarExcepcionProd(${p.id}, ${catId})"
                        class="text-gray-400 hover:text-red-500 text-xl leading-none font-bold transition-colors">
                        ×
                    </button>
                </div>
            </div>`;
        }

        return `
        <div class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-50 border border-transparent hover:border-gray-200 transition-all gap-2">
            <span class="text-sm text-gray-700 truncate">${p.nombre}</span>
            <button onclick="agregarExcepcion(${p.id}, '${p.nombre.replace(/'/g,"\\'")}', ${catId})"
                class="text-xs text-blue-600 border border-blue-200 hover:bg-blue-50 px-2 py-0.5 rounded font-bold shrink-0">
                + Excepción
            </button>
        </div>`;
    }).join('');
}

function agregarExcepcion(prodId, nombre, catId) {
    if (excepciones.some(e => e.id_producto == prodId)) return;
    const cat    = categorias.find(c => c.id == catId);
    const defImp = cat?.id_impresora ?? (impresoras[0]?.id ?? null);
    excepciones.push({ id_producto: prodId, nombre, id_impresora: defImp });
    selectCategoria(catId);
}

function quitarExcepcionProd(prodId, catId) {
    excepciones = excepciones.filter(e => e.id_producto != prodId);
    selectCategoria(catId);
}

function cambiarImpresoraExcProd(prodId, val) {
    const exc = excepciones.find(e => e.id_producto == prodId);
    if (exc) exc.id_impresora = val ? parseInt(val) : null;
}

// ────────────────────────────────────────────────────────
// GUARDAR CONFIGURACIÓN
// ────────────────────────────────────────────────────────
async function guardarTodo() {
    const catData = categorias
        .filter(c => c.id_impresora)
        .map(c => ({ id_categoria: c.id, id_impresora: c.id_impresora }));

    const excData = excepciones.map(e => ({
        id_producto:  e.id_producto,
        id_impresora: e.id_impresora || null,
    }));

    const res  = await fetch(BASE + 'api/impresoras/save_routing', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ categorias: catData, excepciones: excData }),
    });
    const data = await res.json();
    data.success ? toast('Configuración guardada ✓', 'ok') : toast('Error al guardar', 'err');
}

// ────────────────────────────────────────────────────────
// CRUD IMPRESORAS
// ────────────────────────────────────────────────────────
function abrirFormImpresora(imp = null) {
    document.getElementById('imp-id').value     = imp?.id     ?? '';
    document.getElementById('imp-nombre').value = imp?.nombre ?? '';
    document.getElementById('imp-ip').value     = imp?.ip     ?? '';
    document.getElementById('imp-puerto').value = imp?.puerto ?? 9100;
    document.getElementById('modal-imp-titulo').textContent = imp ? 'Editar Impresora' : 'Nueva Impresora';
    document.getElementById('modal-impresora').classList.remove('hidden');
    document.getElementById('imp-nombre').focus();
}

function cerrarFormImpresora() {
    document.getElementById('modal-impresora').classList.add('hidden');
}

function editarImpresora(id) {
    abrirFormImpresora(impresoras.find(x => x.id == id));
}

async function guardarImpresora(e) {
    e.preventDefault();
    const body = {
        id:     document.getElementById('imp-id').value || null,
        nombre: document.getElementById('imp-nombre').value,
        ip:     document.getElementById('imp-ip').value,
        puerto: parseInt(document.getElementById('imp-puerto').value),
    };
    const res  = await fetch(BASE + 'api/impresoras/save', {
        method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body),
    });
    const data = await res.json();
    if (data.success) { cerrarFormImpresora(); await cargar(); toast('Guardado ✓', 'ok'); }
    else toast('Error al guardar', 'err');
}

async function eliminarImpresora(id) {
    const imp = impresoras.find(x => x.id == id);
    if (!confirm(`¿Eliminar "${imp?.nombre}"?`)) return;
    await fetch(BASE + 'api/impresoras/delete/' + id, { method: 'POST' });
    await cargar();
    toast('Impresora eliminada', 'ok');
}

async function testImpresora(id) {
    const imp = impresoras.find(x => x.id == id);
    toast(`Enviando prueba a ${imp?.nombre}...`, 'info');
    const res  = await fetch(BASE + 'api/impresoras/test/' + id, { method: 'POST' });
    const data = await res.json();
    toast(data.message || (data.success ? 'Enviado ✓' : 'Sin conexión'), data.success ? 'ok' : 'err');
}

// ────────────────────────────────────────────────────────
// ESCANEAR RED
// ────────────────────────────────────────────────────────
function abrirScan() {
    document.getElementById('scan-resultado').innerHTML =
        '<p class="text-xs text-gray-400 py-3">Ingresa la subred y presiona Buscar.</p>';
    document.getElementById('modal-scan').classList.remove('hidden');
}

function cerrarScan() {
    document.getElementById('modal-scan').classList.add('hidden');
}

async function ejecutarScan() {
    const subnet = document.getElementById('scan-subnet').value.trim();
    const btn    = document.getElementById('btn-scan');
    const res_el = document.getElementById('scan-resultado');

    btn.disabled = true; btn.textContent = '...';
    res_el.innerHTML = `
        <div class="flex items-center justify-center gap-2 py-4">
            <div class="w-4 h-4 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
            <span class="text-xs text-gray-500">Escaneando ${subnet}.1-254 :9100...</span>
        </div>`;

    try {
        const res  = await fetch(`${BASE}api/impresoras/scan_red?subnet=${encodeURIComponent(subnet)}`);
        const data = await res.json();
        const ips  = data.ips || [];

        res_el.innerHTML = ips.length
            ? ips.map(ip => `
                <div class="flex justify-between items-center bg-white border rounded-lg px-3 py-2 mb-1">
                    <span class="text-sm font-mono">${ip}</span>
                    <button onclick="agregarIpEncontrada('${ip}')"
                        class="text-xs bg-blue-600 text-white px-3 py-1 rounded-lg font-bold">
                        + Configurar
                    </button>
                </div>`).join('')
            : '<p class="text-xs text-gray-400 py-3">No se encontraron impresoras en ese segmento.</p>';
    } catch {
        res_el.innerHTML = '<p class="text-xs text-red-500 py-3">Error de conexión.</p>';
    } finally {
        btn.disabled = false; btn.textContent = 'Buscar';
    }
}

function agregarIpEncontrada(ip) {
    cerrarScan();
    abrirFormImpresora({ ip, puerto: 9100 });
}

// ────────────────────────────────────────────────────────
// TOAST
// ────────────────────────────────────────────────────────
let _toastT;
function toast(msg, type = 'ok') {
    const el = document.getElementById('toast');
    el.textContent = msg;
    el.style.background = type === 'ok' ? '#16a34a' : type === 'err' ? '#dc2626' : '#2563eb';
    el.classList.remove('hidden');
    clearTimeout(_toastT);
    _toastT = setTimeout(() => el.classList.add('hidden'), 3000);
}

// Cerrar modales al click en fondo oscuro
['modal-impresora', 'modal-scan'].forEach(id => {
    document.getElementById(id).addEventListener('click', e => {
        if (e.target === e.currentTarget) e.currentTarget.classList.add('hidden');
    });
});

cargar();
</script>

<?= $this->endSection() ?>
