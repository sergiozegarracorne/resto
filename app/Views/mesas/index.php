<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Mesas y Pisos<?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="flex flex-col h-full bg-gray-50">

    <!-- HEADER -->
    <header class="h-14 bg-gray-800 flex items-center justify-between px-4 shrink-0">
        <div class="flex items-center gap-3">
            <a href="<?= base_url('panel') ?>"
               class="h-9 px-3 flex items-center gap-1.5 bg-gray-700 hover:bg-gray-600 text-gray-200 text-sm font-semibold rounded transition">
                ← Panel
            </a>
            <span class="text-white font-bold text-base">🪑 Mesas y Pisos</span>
        </div>
        <button onclick="abrirModalPiso()"
                class="h-9 px-4 bg-teal-500 hover:bg-teal-600 text-white text-sm font-bold rounded transition">
            + Nuevo Piso
        </button>
    </header>

    <!-- CONTENIDO -->
    <main class="flex-1 overflow-y-auto p-5 space-y-4" id="contenedor-pisos">
        <div class="flex items-center justify-center py-20">
            <div class="w-8 h-8 border-4 border-teal-500 border-t-transparent rounded-full animate-spin"></div>
        </div>
    </main>

</div>

<!-- ══ MODAL PISO ══════════════════════════════════════════════════════════ -->
<div id="modal-piso" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black bg-opacity-50" onclick="cerrarModalPiso()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
            <div class="bg-teal-600 px-5 py-4 text-white flex items-center justify-between">
                <h2 class="font-bold text-lg" id="modal-piso-titulo">Nuevo Piso</h2>
                <button onclick="cerrarModalPiso()" class="text-teal-200 hover:text-white text-xl leading-none">✕</button>
            </div>
            <div class="p-5">
                <input type="hidden" id="piso-id">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nombre del piso</label>
                <input id="piso-nombre" type="text" placeholder="Ej: Salón Principal, Terraza, 2do Piso…"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400"
                       onkeydown="if(event.key==='Enter') guardarPiso()">
                <p id="piso-error" class="text-red-500 text-xs mt-1" style="display:none"></p>
            </div>
            <div class="px-5 pb-5 flex gap-2">
                <button onclick="cerrarModalPiso()"
                        class="flex-1 h-10 rounded-lg border border-gray-200 text-gray-600 text-sm font-semibold hover:bg-gray-50 transition">
                    Cancelar
                </button>
                <button id="btn-guardar-piso" onclick="guardarPiso()"
                        class="flex-1 h-10 rounded-lg bg-teal-500 hover:bg-teal-600 text-white text-sm font-bold transition">
                    Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══ MODAL MESA ══════════════════════════════════════════════════════════ -->
<div id="modal-mesa" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black bg-opacity-50" onclick="cerrarModalMesa()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
            <div class="bg-indigo-600 px-5 py-4 text-white flex items-center justify-between">
                <h2 class="font-bold text-lg" id="modal-mesa-titulo">Nueva Mesa</h2>
                <button onclick="cerrarModalMesa()" class="text-indigo-200 hover:text-white text-xl leading-none">✕</button>
            </div>
            <div class="p-5 space-y-3">
                <input type="hidden" id="mesa-id">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nombre de la mesa</label>
                    <input id="mesa-nombre" type="text" placeholder="Ej: Mesa 1, Barra 3, VIP…"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                           onkeydown="if(event.key==='Enter') guardarMesa()">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Piso</label>
                    <select id="mesa-piso" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                    </select>
                </div>
                <p id="mesa-error" class="text-red-500 text-xs" style="display:none"></p>
            </div>
            <div class="px-5 pb-5 flex gap-2">
                <button onclick="cerrarModalMesa()"
                        class="flex-1 h-10 rounded-lg border border-gray-200 text-gray-600 text-sm font-semibold hover:bg-gray-50 transition">
                    Cancelar
                </button>
                <button id="btn-guardar-mesa" onclick="guardarMesa()"
                        class="flex-1 h-10 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold transition">
                    Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══ MODAL CONFIRMAR ELIMINAR ════════════════════════════════════════════ -->
<div id="modal-confirm" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black bg-opacity-50"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xs overflow-hidden">
            <div class="p-6 text-center">
                <div class="text-4xl mb-3" id="confirm-icon">🗑️</div>
                <h3 class="font-bold text-gray-800 mb-1" id="confirm-titulo">¿Eliminar?</h3>
                <p class="text-sm text-gray-500" id="confirm-desc"></p>
            </div>
            <div class="px-5 pb-5 flex gap-2">
                <button onclick="cerrarConfirm()"
                        class="flex-1 h-10 rounded-lg border border-gray-200 text-gray-600 text-sm font-semibold hover:bg-gray-50 transition">
                    Cancelar
                </button>
                <button id="btn-confirm-ok"
                        class="flex-1 h-10 rounded-lg bg-red-500 hover:bg-red-600 text-white text-sm font-bold transition">
                    Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let pisos = [];

// ── Carga inicial ──────────────────────────────────────────────────────────

async function cargar() {
    try {
        const res  = await fetch('<?= base_url('api/get_pisos_mesas') ?>');
        pisos = await res.json();
        renderPisos();
    } catch(e) {
        document.getElementById('contenedor-pisos').innerHTML =
            '<p class="text-center text-red-500 py-20">Error al cargar datos. Recarga la página.</p>';
    }
}

function renderPisos() {
    const c = document.getElementById('contenedor-pisos');
    if (pisos.length === 0) {
        c.innerHTML = `
        <div class="flex flex-col items-center justify-center py-24 text-center">
            <div class="text-6xl mb-4">🏠</div>
            <p class="text-gray-600 font-bold text-lg">Sin pisos creados</p>
            <p class="text-gray-400 text-sm mt-1 mb-5">Crea un piso para empezar a agregar mesas</p>
            <button onclick="abrirModalPiso()"
                    class="px-6 h-10 bg-teal-500 hover:bg-teal-600 text-white text-sm font-bold rounded-lg transition">
                + Crear primer piso
            </button>
        </div>`;
        return;
    }

    c.innerHTML = pisos.map(piso => {
        const mesas = piso.mesas || [];
        const mesasHtml = mesas.map(m => {
            const colorEstado = m.estado === 'libre'
                ? 'border-gray-200 bg-white text-gray-700'
                : m.estado === 'pre_cuenta'
                    ? 'border-amber-300 bg-amber-50 text-amber-700'
                    : 'border-indigo-200 bg-indigo-50 text-indigo-700';
            const badgeEstado = m.estado !== 'libre'
                ? `<span style="font-size:9px;padding:1px 5px" class="rounded-full ${m.estado === 'pre_cuenta' ? 'bg-amber-100 text-amber-600' : 'bg-indigo-100 text-indigo-600'} font-bold">${m.estado === 'pre_cuenta' ? '🙋 Pre-cuenta' : '🍽️ Ocupada'}</span>`
                : '';
            return `
            <div class="border-2 ${colorEstado} rounded-xl p-3 flex flex-col gap-1.5 min-w-[120px]">
                <div class="font-bold text-sm truncate">${esc(m.nombre)}</div>
                ${badgeEstado}
                <div class="flex gap-1 mt-1">
                    <button onclick="abrirModalMesaEditar(${m.id})"
                            title="Renombrar"
                            class="flex-1 h-7 rounded-lg text-xs font-semibold bg-gray-100 hover:bg-indigo-100 hover:text-indigo-700 text-gray-500 transition">
                        ✏️
                    </button>
                    <button onclick="confirmarEliminarMesa(${m.id}, '${esc(m.nombre).replace(/'/g, "\\'")}', '${m.estado}')"
                            title="Eliminar"
                            class="flex-1 h-7 rounded-lg text-xs font-semibold bg-gray-100 hover:bg-red-100 hover:text-red-600 text-gray-500 transition">
                        🗑️
                    </button>
                </div>
            </div>`;
        }).join('');

        return `
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <!-- Cabecera del piso -->
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100" style="background:#f8fafc">
                <div class="flex items-center gap-2">
                    <span class="text-lg">🏠</span>
                    <span class="font-bold text-gray-800">${esc(piso.nombre)}</span>
                    <span class="text-xs text-gray-400 font-medium">${mesas.length} ${mesas.length === 1 ? 'mesa' : 'mesas'}</span>
                </div>
                <div class="flex gap-1.5">
                    <button onclick="abrirModalPisoEditar(${piso.id}, '${esc(piso.nombre).replace(/'/g, "\\'")}')"
                            class="h-8 px-3 rounded-lg text-xs font-semibold bg-gray-100 hover:bg-teal-100 hover:text-teal-700 text-gray-500 transition">
                        ✏️ Renombrar
                    </button>
                    <button onclick="confirmarEliminarPiso(${piso.id}, '${esc(piso.nombre).replace(/'/g, "\\'")}')"
                            class="h-8 px-3 rounded-lg text-xs font-semibold bg-gray-100 hover:bg-red-100 hover:text-red-600 text-gray-500 transition">
                        🗑️
                    </button>
                </div>
            </div>
            <!-- Grid de mesas -->
            <div class="p-4">
                ${mesas.length === 0
                    ? `<p class="text-gray-400 text-sm text-center py-4">Sin mesas — agrega la primera</p>`
                    : `<div class="grid gap-3" style="grid-template-columns:repeat(auto-fill,minmax(130px,1fr))">${mesasHtml}</div>`
                }
                <button onclick="abrirModalMesaNueva(${piso.id})"
                        class="mt-3 w-full h-9 rounded-xl border-2 border-dashed border-teal-200 hover:border-teal-400 text-teal-500 hover:text-teal-700 text-sm font-semibold transition flex items-center justify-center gap-1">
                    ＋ Nueva mesa en este piso
                </button>
            </div>
        </div>`;
    }).join('');
}

function esc(str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(str));
    return d.innerHTML;
}

// ── Modal Piso ─────────────────────────────────────────────────────────────

function abrirModalPiso() {
    document.getElementById('modal-piso-titulo').innerText = 'Nuevo Piso';
    document.getElementById('piso-id').value = '';
    document.getElementById('piso-nombre').value = '';
    document.getElementById('piso-error').style.display = 'none';
    document.getElementById('modal-piso').classList.remove('hidden');
    setTimeout(() => document.getElementById('piso-nombre').focus(), 50);
}

function abrirModalPisoEditar(id, nombre) {
    document.getElementById('modal-piso-titulo').innerText = 'Renombrar Piso';
    document.getElementById('piso-id').value = id;
    document.getElementById('piso-nombre').value = nombre;
    document.getElementById('piso-error').style.display = 'none';
    document.getElementById('modal-piso').classList.remove('hidden');
    setTimeout(() => document.getElementById('piso-nombre').focus(), 50);
}

function cerrarModalPiso() {
    document.getElementById('modal-piso').classList.add('hidden');
}

async function guardarPiso() {
    const id     = document.getElementById('piso-id').value;
    const nombre = document.getElementById('piso-nombre').value.trim();
    const errEl  = document.getElementById('piso-error');
    const btn    = document.getElementById('btn-guardar-piso');

    if (!nombre) { errEl.innerText = 'Ingresa un nombre'; errEl.style.display = 'block'; return; }
    errEl.style.display = 'none';
    btn.disabled = true; btn.innerHTML = '⏳';

    try {
        const res  = await fetch('<?= base_url('api/pisos/save') ?>', {
            method: 'POST', headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ id: id || undefined, nombre })
        });
        const data = await res.json();
        if (data.success) { cerrarModalPiso(); await cargar(); }
        else { errEl.innerText = data.messages?.error || data.message || 'Error'; errEl.style.display = 'block'; }
    } catch(e) { errEl.innerText = 'Error de conexión'; errEl.style.display = 'block'; }

    btn.disabled = false; btn.innerHTML = 'Guardar';
}

// ── Modal Mesa ─────────────────────────────────────────────────────────────

function _poblarSelectPiso(seleccionadoId) {
    const sel = document.getElementById('mesa-piso');
    sel.innerHTML = pisos.map(p =>
        `<option value="${p.id}" ${p.id == seleccionadoId ? 'selected' : ''}>${esc(p.nombre)}</option>`
    ).join('');
}

function abrirModalMesaNueva(idPiso) {
    document.getElementById('modal-mesa-titulo').innerText = 'Nueva Mesa';
    document.getElementById('mesa-id').value = '';
    document.getElementById('mesa-nombre').value = '';
    document.getElementById('mesa-error').style.display = 'none';
    _poblarSelectPiso(idPiso);
    document.getElementById('modal-mesa').classList.remove('hidden');
    setTimeout(() => document.getElementById('mesa-nombre').focus(), 50);
}

function abrirModalMesaEditar(idMesa) {
    // Buscar la mesa en los datos cargados
    let mesa = null, pisoDeMesa = null;
    for (const p of pisos) {
        const m = (p.mesas || []).find(m => m.id == idMesa);
        if (m) { mesa = m; pisoDeMesa = p; break; }
    }
    if (!mesa) return;

    document.getElementById('modal-mesa-titulo').innerText = 'Editar Mesa';
    document.getElementById('mesa-id').value = mesa.id;
    document.getElementById('mesa-nombre').value = mesa.nombre;
    document.getElementById('mesa-error').style.display = 'none';
    _poblarSelectPiso(pisoDeMesa.id);
    document.getElementById('modal-mesa').classList.remove('hidden');
    setTimeout(() => document.getElementById('mesa-nombre').focus(), 50);
}

function cerrarModalMesa() {
    document.getElementById('modal-mesa').classList.add('hidden');
}

async function guardarMesa() {
    const id      = document.getElementById('mesa-id').value;
    const nombre  = document.getElementById('mesa-nombre').value.trim();
    const id_piso = document.getElementById('mesa-piso').value;
    const errEl   = document.getElementById('mesa-error');
    const btn     = document.getElementById('btn-guardar-mesa');

    if (!nombre) { errEl.innerText = 'Ingresa un nombre'; errEl.style.display = 'block'; return; }
    errEl.style.display = 'none';
    btn.disabled = true; btn.innerHTML = '⏳';

    try {
        const res  = await fetch('<?= base_url('api/mesas/save') ?>', {
            method: 'POST', headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ id: id || undefined, nombre, id_piso })
        });
        const data = await res.json();
        if (data.success) { cerrarModalMesa(); await cargar(); }
        else { errEl.innerText = data.messages?.error || data.message || 'Error'; errEl.style.display = 'block'; }
    } catch(e) { errEl.innerText = 'Error de conexión'; errEl.style.display = 'block'; }

    btn.disabled = false; btn.innerHTML = 'Guardar';
}

// ── Modal Confirmar Eliminar ────────────────────────────────────────────────

let _confirmCallback = null;

function abrirConfirm(icon, titulo, desc, cb) {
    document.getElementById('confirm-icon').innerText = icon;
    document.getElementById('confirm-titulo').innerText = titulo;
    document.getElementById('confirm-desc').innerText = desc;
    _confirmCallback = cb;
    document.getElementById('btn-confirm-ok').onclick = async () => {
        document.getElementById('btn-confirm-ok').disabled = true;
        document.getElementById('btn-confirm-ok').innerHTML = '⏳';
        await cb();
        document.getElementById('btn-confirm-ok').disabled = false;
        document.getElementById('btn-confirm-ok').innerHTML = 'Eliminar';
        cerrarConfirm();
    };
    document.getElementById('modal-confirm').classList.remove('hidden');
}

function cerrarConfirm() {
    document.getElementById('modal-confirm').classList.add('hidden');
}

function confirmarEliminarPiso(id, nombre) {
    abrirConfirm('🏠', `¿Eliminar "${nombre}"?`,
        'Esto solo es posible si el piso no tiene mesas.',
        async () => {
            const res  = await fetch('<?= base_url('api/pisos/delete') ?>', {
                method: 'POST', headers: {'Content-Type':'application/json'},
                body: JSON.stringify({ id })
            });
            const data = await res.json();
            if (data.success) { await cargar(); }
            else { alert(data.messages?.error || data.message || 'No se pudo eliminar'); }
        }
    );
}

function confirmarEliminarMesa(id, nombre, estado) {
    if (estado !== 'libre') {
        alert('No se puede eliminar: la mesa tiene un pedido activo.');
        return;
    }
    abrirConfirm('🪑', `¿Eliminar "${nombre}"?`,
        'La mesa se eliminará permanentemente del sistema.',
        async () => {
            const res  = await fetch('<?= base_url('api/mesas/delete') ?>', {
                method: 'POST', headers: {'Content-Type':'application/json'},
                body: JSON.stringify({ id })
            });
            const data = await res.json();
            if (data.success) { await cargar(); }
            else { alert(data.messages?.error || data.message || 'No se pudo eliminar'); }
        }
    );
}

// Inicio
cargar();
</script>

<?= $this->endSection() ?>
