<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
Roles y Permisos
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<header class="bg-slate-900 text-white px-5 shadow-md flex justify-between items-center h-16 shrink-0">
    <h1 class="text-xl font-bold flex items-center gap-2"><span>🔐</span> Roles y Permisos</h1>
    <a href="<?= base_url('panel') ?>" class="text-slate-300 hover:text-white text-sm transition-colors">← Panel</a>
</header>

<main class="flex-1 overflow-y-auto">

    <!-- ══ PESTAÑAS ══════════════════════════════════════════════════════════ -->
    <div class="flex border-b border-slate-200 bg-white sticky top-0 z-10">
        <button onclick="mostrarTab('roles')"    id="tab-roles"    class="tab-btn px-6 py-3 text-sm font-semibold border-b-2 transition-colors">👥 Roles</button>
        <button onclick="mostrarTab('rutas')"    id="tab-rutas"    class="tab-btn px-6 py-3 text-sm font-semibold border-b-2 transition-colors">📋 Rutas</button>
        <button onclick="mostrarTab('permisos')" id="tab-permisos" class="tab-btn px-6 py-3 text-sm font-semibold border-b-2 transition-colors">🔑 Permisos</button>
        <button onclick="mostrarTab('botones')"  id="tab-botones"  class="tab-btn px-6 py-3 text-sm font-semibold border-b-2 transition-colors">⚡ Botones</button>
    </div>

    <!-- ══ TAB: ROLES ════════════════════════════════════════════════════════ -->
    <div id="panel-roles" class="tab-panel p-5 flex flex-col gap-5" style="display:none">
        <div class="flex items-start justify-between">
            <div>
                <h2 class="font-bold text-slate-800">Catálogo de roles</h2>
                <p class="text-xs text-slate-400 mt-0.5">Los roles definen el nivel de acceso de cada usuario. Al agregar un rol se asignan sus permisos en la pestaña Permisos.</p>
            </div>
            <button onclick="abrirFormRol()"
                class="flex-shrink-0 flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-lg shadow transition-colors active:scale-95">
                ＋ Agregar rol
            </button>
        </div>
        <div id="loading-roles" class="text-center text-slate-400 py-10 text-sm">Cargando…</div>
        <div id="wrapper-roles" style="display:none" class="overflow-x-auto rounded-xl shadow-sm border border-slate-200">
            <table class="w-full text-sm">
                <thead class="bg-slate-800 text-white">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold w-12">#</th>
                        <th class="text-left px-4 py-3 font-semibold">Nombre</th>
                        <th class="px-4 py-3 w-20"></th>
                    </tr>
                </thead>
                <tbody id="tbody-roles"></tbody>
            </table>
        </div>
        <div id="empty-roles" class="text-center text-slate-400 py-10 text-sm" style="display:none">
            No hay roles registrados.
        </div>
    </div>

    <!-- ══ TAB: RUTAS ════════════════════════════════════════════════════════ -->
    <div id="panel-rutas" class="tab-panel p-5 flex flex-col gap-5">
        <div class="flex items-start justify-between">
            <div>
                <h2 class="font-bold text-slate-800">Catálogo de rutas</h2>
                <p class="text-xs text-slate-400 mt-0.5">Registra aquí las páginas del sistema. En la pestaña Permisos defines quién puede acceder.</p>
            </div>
            <button onclick="abrirFormRuta()"
                class="flex-shrink-0 flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-lg shadow transition-colors active:scale-95">
                ＋ Agregar ruta
            </button>
        </div>

        <div id="loading-rutas" class="text-center text-slate-400 py-10 text-sm">Cargando…</div>
        <div id="wrapper-rutas" style="display:none" class="overflow-x-auto rounded-xl shadow-sm border border-slate-200">
            <table class="w-full text-sm">
                <thead class="bg-slate-800 text-white">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold">Nombre</th>
                        <th class="text-left px-4 py-3 font-semibold">Alias</th>
                        <th class="text-left px-4 py-3 font-semibold font-mono">Ruta</th>
                        <th class="px-4 py-3 font-semibold text-center w-20">Activo</th>
                        <th class="px-4 py-3 w-20"></th>
                    </tr>
                </thead>
                <tbody id="tbody-rutas"></tbody>
            </table>
        </div>
        <div id="empty-rutas" class="text-center text-slate-400 py-10 text-sm" style="display:none">
            No hay rutas registradas. Agrega una con el botón de arriba.
        </div>
    </div>

    <!-- ══ TAB: PERMISOS ═════════════════════════════════════════════════════ -->
    <div id="panel-permisos" class="tab-panel p-5 flex flex-col gap-5" style="display:none">
        <div>
            <h2 class="font-bold text-slate-800">Permisos por ruta y rol</h2>
            <p class="text-xs text-slate-400 mt-0.5">Controla qué operaciones puede hacer cada rol en cada página. Las rutas se crean en la pestaña Rutas.</p>
        </div>
        <div id="loading-permisos" class="text-center text-slate-400 py-10 text-sm">Cargando…</div>
        <div id="wrapper-permisos" style="display:none" class="overflow-x-auto rounded-xl shadow-sm border border-slate-200">
            <table class="w-full text-xs">
                <thead id="thead-permisos"></thead>
                <tbody id="tbody-permisos-rutas"></tbody>
            </table>
        </div>
        <div id="empty-permisos" class="text-center text-slate-400 py-10 text-sm" style="display:none">
            No hay rutas registradas aún. Agrégalas en la pestaña Rutas.
        </div>
    </div>

    <!-- ══ TAB: BOTONES ══════════════════════════════════════════════════════ -->
    <div id="panel-botones" class="tab-panel p-5 flex flex-col gap-5" style="display:none">

        <div class="flex items-start justify-between">
            <div>
                <h2 class="font-bold text-slate-800">Catálogo de botones</h2>
                <p class="text-xs text-slate-400 mt-0.5">Cada botón aparece en la barra de acciones de la pantalla de ventas.</p>
            </div>
            <button onclick="abrirFormBoton()"
                class="flex-shrink-0 flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-lg shadow transition-colors active:scale-95">
                ＋ Nuevo botón
            </button>
        </div>

        <div id="loading-botones" class="text-center text-slate-400 py-8 text-sm">Cargando…</div>
        <div id="lista-botones" class="grid gap-3" style="display:none;grid-template-columns:repeat(auto-fill,minmax(210px,1fr))"></div>

    </div>

</main>

<!-- ══ MODAL: Nuevo rol ══════════════════════════════════════════════════════════ -->
<div id="modal-rol" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:500;align-items:center;justify-content:center">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xs mx-4 overflow-hidden">
        <div class="bg-slate-800 text-white px-5 py-4 flex justify-between items-center">
            <h3 class="font-bold text-base">Nuevo rol</h3>
            <button onclick="cerrarModalRol()" class="text-slate-400 hover:text-white text-xl">✕</button>
        </div>
        <div class="p-5 flex flex-col gap-4">
            <div>
                <label class="text-xs font-semibold text-slate-600 block mb-1">Nombre <span class="text-red-400">*</span></label>
                <input id="f-rol-nombre" type="text" placeholder="ej: supervisor"
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none"
                    onkeydown="if(event.key==='Enter') guardarRol()">
                <p class="text-xs text-slate-400 mt-1">Minúsculas sin espacios (se normalizará automáticamente).</p>
            </div>
        </div>
        <div class="px-5 pb-5 flex gap-3 justify-end">
            <button onclick="cerrarModalRol()" class="px-4 py-2 text-sm text-slate-500 hover:text-slate-700">Cancelar</button>
            <button onclick="guardarRol()"
                class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow transition-colors active:scale-95">
                Guardar
            </button>
        </div>
    </div>
</div>

<!-- ══ MODAL: Agregar / Editar ruta ════════════════════════════════════════════ -->
<div id="modal-ruta" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:500;align-items:center;justify-content:center">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden">
        <div class="bg-slate-800 text-white px-5 py-4 flex justify-between items-center">
            <h3 id="modal-ruta-titulo" class="font-bold text-base">Agregar ruta</h3>
            <button onclick="cerrarModalRuta()" class="text-slate-400 hover:text-white text-xl">✕</button>
        </div>
        <div class="p-5 flex flex-col gap-4">
            <input type="hidden" id="f-ruta-id">
            <div>
                <label class="text-xs font-semibold text-slate-600 block mb-1">Nombre <span class="text-red-400">*</span></label>
                <input id="f-ruta-nombre" type="text" placeholder="ej: Usuarios"
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none">
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-600 block mb-1">Alias</label>
                <input id="f-ruta-alias" type="text" placeholder="ej: 👥 o USR"
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none">
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-600 block mb-1">Ruta <span class="text-red-400">*</span></label>
                <select id="f-ruta-select" onchange="if(this.value) document.getElementById('f-ruta-path').value=this.value"
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none font-mono bg-white mb-2">
                    <option value="">— Seleccionar del sistema —</option>
                </select>
                <input id="f-ruta-path" type="text" placeholder="ej: usuarios  ó  almacen/compras"
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none font-mono">
            </div>
        </div>
        <div class="px-5 pb-5 flex gap-3 justify-end">
            <button onclick="cerrarModalRuta()" class="px-4 py-2 text-sm text-slate-500 hover:text-slate-700">Cancelar</button>
            <button onclick="guardarRuta()"
                class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow transition-colors active:scale-95">
                Guardar
            </button>
        </div>
    </div>
</div>

<!-- ══ MODAL: Nuevo / Editar botón ════════════════════════════════════════════ -->
<div id="modal-boton" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:500;align-items:center;justify-content:center">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        <div class="bg-slate-800 text-white px-5 py-4 flex justify-between items-center">
            <h3 id="modal-titulo" class="font-bold text-base">Nuevo botón</h3>
            <button onclick="cerrarModalBoton()" class="text-slate-400 hover:text-white text-xl">✕</button>
        </div>
        <div class="p-5 flex flex-col gap-4">
            <div class="flex items-center gap-4 bg-slate-50 rounded-xl p-3 border border-slate-200">
                <span id="prev-icon" class="text-3xl">🔘</span>
                <div>
                    <div id="prev-label" class="font-bold text-slate-700 text-sm">Nombre del botón</div>
                    <div id="prev-key"   class="text-xs text-slate-400 font-mono">clave-boton</div>
                </div>
                <span id="prev-color-dot" style="margin-left:auto;width:12px;height:12px;border-radius:50%;background:#6366f1;flex-shrink:0"></span>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-semibold text-slate-600 block mb-1">Clave única <span class="text-red-400">*</span></label>
                    <input id="f-key" type="text" placeholder="ej: reportes"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none font-mono"
                        oninput="actualizarPreview()">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-600 block mb-1">Ícono (emoji)</label>
                    <input id="f-icon" type="text" placeholder="🔘" maxlength="4" value="🔘"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xl focus:border-indigo-400 focus:outline-none text-center"
                        oninput="actualizarPreview()">
                </div>
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-600 block mb-1">Nombre <span class="text-red-400">*</span></label>
                <input id="f-label" type="text" placeholder="ej: Reportes"
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none"
                    oninput="actualizarPreview()">
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-600 block mb-1">Tipo</label>
                <div class="flex gap-2">
                    <button id="tipo-link"   onclick="setTipo('link')"   class="flex-1 py-2 rounded-lg text-sm font-semibold border transition-colors">🔗 Link</button>
                    <button id="tipo-button" onclick="setTipo('button')" class="flex-1 py-2 rounded-lg text-sm font-semibold border transition-colors">⚡ Botón JS</button>
                </div>
            </div>
            <div id="campo-href">
                <label class="text-xs font-semibold text-slate-600 block mb-1">Ruta del sistema</label>
                <select id="f-href-select" onchange="document.getElementById('f-href').value=this.value"
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none font-mono bg-white mb-2">
                    <option value="">— O escribir abajo —</option>
                </select>
                <input id="f-href" type="text" placeholder="ej: ventas  ó  /mesas"
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none font-mono">
            </div>
            <div id="campo-onclick" style="display:none">
                <label class="text-xs font-semibold text-slate-600 block mb-1">Función JavaScript</label>
                <input id="f-onclick" type="text" placeholder="ej: abrirReportes()"
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none font-mono">
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-600 block mb-2">Color</label>
                <div class="flex gap-2">
                    <?php foreach (['red' => '#ef4444','indigo' => '#6366f1','emerald' => '#10b981','orange' => '#f97316','amber' => '#f59e0b','slate' => '#334155'] as $c => $hex): ?>
                    <button onclick="setColor('<?= $c ?>')" id="color-<?= $c ?>"
                        class="color-btn w-8 h-8 rounded-full border-2 border-white shadow transition-transform active:scale-95"
                        style="background-color:<?= $hex ?>"></button>
                    <?php endforeach; ?>
                </div>
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-600 block mb-1">Orden</label>
                <input id="f-orden" type="number" value="99" min="1" max="99"
                    class="w-24 border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none">
            </div>
        </div>
        <div class="px-5 pb-5 flex gap-3 justify-end">
            <button onclick="cerrarModalBoton()" class="px-4 py-2 text-sm text-slate-500 hover:text-slate-700">Cancelar</button>
            <button onclick="guardarBoton()"
                class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow transition-colors active:scale-95">
                Guardar
            </button>
        </div>
    </div>
</div>

<!-- Toast -->
<div id="toast" style="display:none;position:fixed;bottom:24px;right:24px;z-index:9999;padding:12px 20px;border-radius:8px;font-size:14px;font-weight:600;box-shadow:0 4px 16px rgba(0,0,0,.15);transition:opacity .3s"></div>

<style>
.toggle-track { position:relative; display:inline-flex; align-items:center; width:36px; height:20px; border-radius:9999px; cursor:pointer; transition:background-color .2s; border:none; outline:none; }
.toggle-track:disabled { opacity:.4; cursor:default; }
.toggle-thumb { position:absolute; width:14px; height:14px; border-radius:50%; background:white; box-shadow:0 1px 3px rgba(0,0,0,.25); transition:transform .2s; left:3px; }
.toggle-track[data-activo="1"] { background-color:#22c55e; }
.toggle-track[data-activo="0"] { background-color:#cbd5e1; }
.toggle-track[data-activo="1"] .toggle-thumb { transform:translateX(16px); }
.toggle-track[data-activo="0"] .toggle-thumb { transform:translateX(0); }
.color-btn.activo { border-color:#1e293b !important; transform:scale(1.2); }
.tab-btn.activo       { border-color:#6366f1; color:#6366f1; }
.tab-btn:not(.activo) { border-color:transparent; color:#94a3b8; }
</style>

<script>
const BASE_URL  = '<?= base_url() ?>';
const ROL_COLOR = { mozo:'#6366f1', caja:'#3b82f6', administrador:'#f97316', sudo:'#ef4444' };
const COLOR_HEX = { red:'#ef4444', indigo:'#6366f1', emerald:'#10b981', orange:'#f97316', amber:'#f59e0b', slate:'#334155' };
const ACCIONES  = [
    { campo:'puede_ver',    icon:'👁️', title:'Ver'    },
    { campo:'puede_editar', icon:'✏️', title:'Editar' },
];

let _roles = [], _botones = [];
let _rutasCatalogo = [], _permisosRutas = {};
let _rutasSistema  = [];
let _tipoActual = 'link', _colorActual = 'indigo', _editKey = null;
let _permisosLoaded = false;

// ── Pestañas ──────────────────────────────────────────────────────────────────
function mostrarTab(tab) {
    document.querySelectorAll('.tab-panel').forEach(p => p.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('activo'));
    document.getElementById('panel-' + tab).style.display = 'flex';
    document.getElementById('tab-' + tab).classList.add('activo');
    if (tab === 'permisos' && !_permisosLoaded) {
        _permisosLoaded = true;
        cargarPermisosRutas();
    }
}

// ── Init ──────────────────────────────────────────────────────────────────────
async function cargar() {
    await Promise.all([cargarRolesCatalogo(), cargarRutasCatalogo(), cargarRutasSistema(), cargarBotones()]);
}

// ══ TAB 0: Catálogo de roles ══════════════════════════════════════════════════

async function cargarRolesCatalogo() {
    try {
        const data = await fetch(BASE_URL + 'api/roles/catalogo').then(r => r.json());
        _roles = data.roles || [];
        renderCatalogoRoles();
    } catch {
        document.getElementById('loading-roles').innerHTML = '<span style="color:#ef4444">Error al cargar</span>';
    }
}

function renderCatalogoRoles() {
    document.getElementById('loading-roles').style.display = 'none';
    if (!_roles.length) {
        document.getElementById('empty-roles').style.display = 'block';
        document.getElementById('wrapper-roles').style.display = 'none';
        return;
    }
    document.getElementById('empty-roles').style.display = 'none';
    const tbody = document.getElementById('tbody-roles');
    tbody.innerHTML = '';
    _roles.forEach((r, idx) => {
        const tr = document.createElement('tr');
        tr.className = idx % 2 === 0 ? 'bg-white' : 'bg-slate-50';
        const color = ROL_COLOR[r.nombre] || '#94a3b8';
        tr.innerHTML = `
            <td class="px-4 py-3 text-slate-400 text-xs">${r.id}</td>
            <td class="px-4 py-3">
                <span class="inline-flex items-center gap-2">
                    <span style="width:9px;height:9px;border-radius:50%;background:${color};flex-shrink:0"></span>
                    <span class="font-semibold text-slate-700">${r.nombre}</span>
                </span>
            </td>
            <td class="px-4 py-3 text-right">
                <button onclick="eliminarRol(${r.id},'${r.nombre.replace(/'/g,"\\'")}')"
                    class="text-xs px-3 py-1 border border-red-200 text-red-500 rounded-lg hover:bg-red-50">🗑️</button>
            </td>`;
        tbody.appendChild(tr);
    });
    document.getElementById('wrapper-roles').style.display = 'block';
}

function abrirFormRol() {
    document.getElementById('f-rol-nombre').value = '';
    document.getElementById('modal-rol').style.display = 'flex';
    setTimeout(() => document.getElementById('f-rol-nombre').focus(), 50);
}
function cerrarModalRol() { document.getElementById('modal-rol').style.display = 'none'; }

async function guardarRol() {
    const nombre = document.getElementById('f-rol-nombre').value.trim().toLowerCase().replace(/\s+/g, '_');
    if (!nombre) { showToast('El nombre es obligatorio', 'error'); return; }
    try {
        const res = await fetch(BASE_URL + 'api/roles/save_rol', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nombre }),
        });
        const json = await res.json();
        if (!res.ok) throw new Error(json.error || 'Error');
        cerrarModalRol();
        showToast('✓ Rol agregado', 'ok');
        _permisosLoaded = false;
        await cargarRolesCatalogo();
    } catch(e) { showToast(e.message || 'Error al guardar', 'error'); }
}

async function eliminarRol(id, nombre) {
    if (!confirm(`¿Eliminar el rol "${nombre}"?\nSe eliminarán también todos sus permisos asignados.`)) return;
    try {
        const res = await fetch(BASE_URL + 'api/roles/delete_rol', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id }),
        });
        if (!res.ok) throw new Error();
        showToast('✓ Rol eliminado', 'off');
        _permisosLoaded = false;
        await cargarRolesCatalogo();
    } catch { showToast('Error', 'error'); }
}

// ══ TAB 1: Catálogo de rutas ══════════════════════════════════════════════════

async function cargarRutasCatalogo() {
    try {
        const data = await fetch(BASE_URL + 'api/roles/rutas').then(r => r.json());
        _rutasCatalogo = data.rutas || [];
        renderCatalogoRutas();
        poblarSelectHref();
    } catch {
        document.getElementById('loading-rutas').innerHTML = '<span style="color:#ef4444">Error al cargar</span>';
    }
}

function renderCatalogoRutas() {
    document.getElementById('loading-rutas').style.display = 'none';
    if (!_rutasCatalogo.length) {
        document.getElementById('empty-rutas').style.display = 'block';
        document.getElementById('wrapper-rutas').style.display = 'none';
        return;
    }
    document.getElementById('empty-rutas').style.display = 'none';
    const tbody = document.getElementById('tbody-rutas');
    tbody.innerHTML = '';
    _rutasCatalogo.forEach((r, idx) => {
        const tr = document.createElement('tr');
        tr.className = idx % 2 === 0 ? 'bg-white' : 'bg-slate-50';
        const activo = parseInt(r.activo ?? 1);
        tr.innerHTML = `
            <td class="px-4 py-3 font-semibold text-slate-700 text-sm">${r.nombre}</td>
            <td class="px-4 py-3 text-lg">${r.alias || '—'}</td>
            <td class="px-4 py-3 font-mono text-xs text-indigo-600">/${r.ruta}</td>
            <td class="px-4 py-3 text-center">
                <button class="toggle-track" data-activo="${activo}"
                    onclick="toggleActivoRuta(${r.id},this)">
                    <span class="toggle-thumb"></span></button>
            </td>
            <td class="px-4 py-3 text-right whitespace-nowrap">
                <button onclick='editarRuta(${JSON.stringify(r)})'
                    class="text-xs px-3 py-1 border border-red-200 text-red-500 rounded-lg hover:bg-red-50 mr-1">✏️</button>
                <button onclick="eliminarRuta(${r.id},'${r.nombre.replace(/'/g,"\\'")}' )"
                    class="text-xs px-3 py-1 border border-red-200 text-red-500 rounded-lg hover:bg-red-50">🗑️</button>
            </td>`;
        tbody.appendChild(tr);
    });
    document.getElementById('wrapper-rutas').style.display = 'block';
}

async function cargarRutasSistema() {
    try {
        const data = await fetch(BASE_URL + 'api/roles/rutas_sistema').then(r => r.json());
        _rutasSistema = data.rutas || [];
        const sel = document.getElementById('f-ruta-select');
        if (sel) {
            sel.innerHTML = '<option value="">— Seleccionar —</option>';
            _rutasSistema.forEach(r => {
                const opt = document.createElement('option');
                opt.value = r.ruta;
                opt.textContent = `${r.ruta}  (${r.controlador})`;
                sel.appendChild(opt);
            });
        }
    } catch { /* silencioso */ }
}

function poblarSelectHref() {
    const sel = document.getElementById('f-href-select');
    if (!sel) return;
    sel.innerHTML = '<option value="">— Seleccionar —</option>';
    _rutasCatalogo.forEach(r => {
        const opt = document.createElement('option');
        opt.value = r.ruta;
        opt.textContent = `${r.nombre}  (/${r.ruta})`;
        sel.appendChild(opt);
    });
}

function abrirFormRuta() {
    document.getElementById('modal-ruta-titulo').textContent = 'Agregar ruta';
    document.getElementById('f-ruta-id').value     = '';
    document.getElementById('f-ruta-nombre').value = '';
    document.getElementById('f-ruta-alias').value  = '';
    document.getElementById('f-ruta-path').value   = '';
    document.getElementById('f-ruta-select').value = '';
    document.getElementById('modal-ruta').style.display = 'flex';
}
function editarRuta(r) {
    document.getElementById('modal-ruta-titulo').textContent = 'Editar ruta';
    document.getElementById('f-ruta-id').value     = r.id;
    document.getElementById('f-ruta-nombre').value = r.nombre;
    document.getElementById('f-ruta-alias').value  = r.alias || '';
    document.getElementById('f-ruta-path').value   = r.ruta;
    document.getElementById('f-ruta-select').value = r.ruta;
    document.getElementById('modal-ruta').style.display = 'flex';
}
function cerrarModalRuta() { document.getElementById('modal-ruta').style.display = 'none'; }

async function guardarRuta() {
    const nombre = document.getElementById('f-ruta-nombre').value.trim();
    const alias  = document.getElementById('f-ruta-alias').value.trim();
    const ruta   = document.getElementById('f-ruta-path').value.trim().replace(/^\//, '');
    const id     = document.getElementById('f-ruta-id').value;
    if (!nombre || !ruta) { showToast('Nombre y ruta son obligatorios', 'error'); return; }
    try {
        const res = await fetch(BASE_URL + 'api/roles/save_ruta', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id ? parseInt(id) : 0, nombre, alias, ruta }),
        });
        const json = await res.json();
        if (!res.ok) throw new Error(json.error || 'Error');
        cerrarModalRuta();
        showToast(id ? '✓ Ruta actualizada' : '✓ Ruta agregada', 'ok');
        _permisosLoaded = false;
        await cargarRutasCatalogo();
    } catch(e) { showToast(e.message || 'Error al guardar', 'error'); }
}

async function eliminarRuta(id, nombre) {
    if (!confirm(`¿Eliminar "${nombre}" del catálogo?\nSe eliminarán también todos sus permisos asignados.`)) return;
    try {
        await fetch(BASE_URL + 'api/roles/delete_ruta', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id }),
        });
        showToast('✓ Ruta eliminada', 'off');
        _permisosLoaded = false;
        await cargarRutasCatalogo();
    } catch { showToast('Error', 'error'); }
}

async function toggleActivoRuta(id, btn) {
    const nuevo = btn.dataset.activo === '1' ? 0 : 1;
    btn.disabled = true;
    try {
        const res = await fetch(BASE_URL + 'api/roles/toggle_ruta', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, activo: nuevo }),
        });
        if (!res.ok) throw new Error();
        btn.dataset.activo = nuevo;
        showToast(nuevo ? '✓ Ruta activada' : '✗ Ruta desactivada', nuevo ? 'ok' : 'off');
    } catch { showToast('Error', 'error'); }
    finally { btn.disabled = false; }
}

// ══ TAB 2: Permisos por ruta ══════════════════════════════════════════════════

async function cargarPermisosRutas() {
    document.getElementById('loading-permisos').style.display = 'block';
    try {
        const data = await fetch(BASE_URL + 'api/roles/permisos_rutas').then(r => r.json());
        if (!_roles.length) _roles = data.roles || [];
        _permisosRutas = data.permisos || {};
        renderMatrizPermisos(data.rutas || []);
    } catch {
        document.getElementById('loading-permisos').innerHTML = '<span style="color:#ef4444">Error al cargar</span>';
    }
}

function renderMatrizPermisos(rutas) {
    document.getElementById('loading-permisos').style.display = 'none';
    if (!rutas.length) {
        document.getElementById('empty-permisos').style.display = 'block';
        return;
    }
    document.getElementById('empty-permisos').style.display = 'none';

    const thead = document.getElementById('thead-permisos');
    thead.innerHTML = '';

    const tr1 = document.createElement('tr');
    tr1.className = 'bg-slate-800 text-white';
    const thR = document.createElement('th');
    thR.className = 'text-left px-4 py-3 font-semibold';
    thR.textContent = 'Ruta';
    tr1.appendChild(thR);
    _roles.forEach(r => {
        const th = document.createElement('th');
        th.colSpan = ACCIONES.length;
        th.className = 'px-2 py-3 text-center font-semibold border-l border-slate-700';
        th.style.color = ROL_COLOR[r.nombre] || '#94a3b8';
        th.textContent = r.nombre_display || r.nombre;
        tr1.appendChild(th);
    });
    thead.appendChild(tr1);

    const tr2 = document.createElement('tr');
    tr2.className = 'bg-slate-700 text-slate-300 text-xs';
    tr2.appendChild(document.createElement('th'));
    _roles.forEach(() => {
        ACCIONES.forEach(a => {
            const th = document.createElement('th');
            th.className = 'px-1 py-1 text-center';
            th.title = a.title;
            th.textContent = a.icon;
            tr2.appendChild(th);
        });
    });
    thead.appendChild(tr2);

    const tbody = document.getElementById('tbody-permisos-rutas');
    tbody.innerHTML = '';
    rutas.forEach((r, idx) => {
        const idRuta = parseInt(r.id);
        const tr = document.createElement('tr');
        tr.className = idx % 2 === 0 ? 'bg-white' : 'bg-slate-50';

        const tdRuta = document.createElement('td');
        tdRuta.className = 'px-4 py-2';
        tdRuta.style.minWidth = '20%';
        tdRuta.innerHTML = `
            <div class="flex items-center gap-2">
                <span class="text-base w-[250px]">${r.nombre}</span>
                <div>
                    <div class="font-semibold text-slate-700 text-xs">${r.alias || ''}</div>
                    <div class="font-mono text-xs text-slate-400">/${r.ruta}</div>
                </div>
            </div>`;
        tr.appendChild(tdRuta);

        _roles.forEach(rol => {
            const idRol   = parseInt(rol.id);
            const permiso = _permisosRutas[idRuta]?.[idRol] ?? { ver:1, editar:1 };
            ACCIONES.forEach(a => {
                const accionKey = a.campo.replace('puede_', '');
                const activo    = permiso[accionKey] ?? 1;
                const td = document.createElement('td');
                td.className = 'px-1 py-2 text-center';
                td.innerHTML = `<button class="toggle-track" data-activo="${activo}"
                    onclick="togglePermisoRuta(${idRuta},${idRol},'${a.campo}',this)">
                    <span class="toggle-thumb"></span></button>`;
                tr.appendChild(td);
            });
        });

        tbody.appendChild(tr);
    });

    document.getElementById('wrapper-permisos').style.display = 'block';
}

async function togglePermisoRuta(idRuta, idRol, campo, btn) {
    const nuevo = btn.dataset.activo === '1' ? 0 : 1;
    btn.disabled = true;
    try {
        const res = await fetch(BASE_URL + 'api/roles/update_permiso_ruta', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_ruta: idRuta, id_rol: idRol, campo, valor: nuevo }),
        });
        if (!res.ok) throw new Error();
        btn.dataset.activo = nuevo;
        if (!_permisosRutas[idRuta]) _permisosRutas[idRuta] = {};
        if (!_permisosRutas[idRuta][idRol]) _permisosRutas[idRuta][idRol] = {};
        _permisosRutas[idRuta][idRol][campo.replace('puede_','')] = nuevo;
        showToast(nuevo ? '✓ Permiso activado' : '✗ Permiso desactivado', nuevo ? 'ok' : 'off');
    } catch { showToast('Error al guardar', 'error'); }
    finally { btn.disabled = false; }
}

// ══ TAB 3: Botones de la barra ════════════════════════════════════════════════

async function cargarBotones() {
    try {
        const data = await fetch(BASE_URL + 'api/roles/botones').then(r => r.json());
        _botones = data.botones || [];
        renderCatalogoBotones();
    } catch {
        document.getElementById('loading-botones').innerHTML = '<span style="color:#ef4444">Error al cargar</span>';
    }
}

function renderCatalogoBotones() {
    const wrap = document.getElementById('lista-botones');
    wrap.innerHTML = '';
    if (!_botones.length) {
        wrap.innerHTML = '<p class="text-slate-400 text-sm col-span-full">No hay botones en el catálogo.</p>';
        document.getElementById('loading-botones').style.display = 'none';
        wrap.style.display = 'grid';
        return;
    }
    _botones.forEach(b => {
        const hex = COLOR_HEX[b.color] || '#6366f1';
        const tipoLabel = b.tipo === 'link' ? `🔗 ${b.href || '/'}` : `⚡ ${b.onclick || '—'}`;
        const div = document.createElement('div');
        div.className = 'bg-white border border-slate-200 rounded-xl p-4 flex flex-col gap-2 shadow-sm';
        div.innerHTML = `
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span class="text-2xl">${b.icon}</span>
                    <div>
                        <div class="font-bold text-slate-800 text-sm">${b.label}</div>
                        <div class="text-xs text-slate-400 font-mono">${b.boton_key}</div>
                    </div>
                </div>
                <span style="width:10px;height:10px;border-radius:50%;background:${hex};flex-shrink:0"></span>
            </div>
            <div class="text-xs text-slate-400 truncate">${tipoLabel}</div>
            <div class="flex gap-2 mt-1">
                <button onclick='editarBoton(${JSON.stringify(b)})'
                    class="flex-1 text-xs py-1.5 border border-indigo-200 text-indigo-600 rounded-lg hover:bg-indigo-50">✏️ Editar</button>
                <button onclick="eliminarBoton('${b.boton_key}','${b.label}')"
                    class="flex-1 text-xs py-1.5 border border-red-200 text-red-500 rounded-lg hover:bg-red-50">🗑️ Eliminar</button>
            </div>`;
        wrap.appendChild(div);
    });
    document.getElementById('loading-botones').style.display = 'none';
    wrap.style.display = 'grid';
}

// ── Modal botón ───────────────────────────────────────────────────────────────
function abrirFormBoton() {
    _editKey = null;
    document.getElementById('modal-titulo').textContent = 'Nuevo botón';
    document.getElementById('f-key').value     = ''; document.getElementById('f-key').disabled = false;
    document.getElementById('f-label').value   = '';
    document.getElementById('f-icon').value    = '🔘';
    document.getElementById('f-href').value    = '';
    document.getElementById('f-onclick').value = '';
    document.getElementById('f-orden').value   = 99;
    document.getElementById('f-href-select').value = '';
    setTipo('link'); setColor('indigo'); actualizarPreview();
    document.getElementById('modal-boton').style.display = 'flex';
}
function editarBoton(b) {
    _editKey = b.boton_key;
    document.getElementById('modal-titulo').textContent = 'Editar botón';
    document.getElementById('f-key').value     = b.boton_key; document.getElementById('f-key').disabled = true;
    document.getElementById('f-label').value   = b.label;
    document.getElementById('f-icon').value    = b.icon;
    document.getElementById('f-href').value    = b.href   || '';
    document.getElementById('f-onclick').value = b.onclick || '';
    document.getElementById('f-orden').value   = b.orden;
    setTipo(b.tipo); setColor(b.color); actualizarPreview();
    document.getElementById('modal-boton').style.display = 'flex';
}
function cerrarModalBoton() { document.getElementById('modal-boton').style.display = 'none'; }

function setTipo(tipo) {
    _tipoActual = tipo;
    const esLink = tipo === 'link';
    document.getElementById('campo-href').style.display    = esLink ? 'block' : 'none';
    document.getElementById('campo-onclick').style.display = esLink ? 'none'  : 'block';
    const cls    = 'flex-1 py-2 rounded-lg text-sm font-semibold border bg-indigo-600 text-white border-indigo-600 transition-colors';
    const clsOff = 'flex-1 py-2 rounded-lg text-sm font-semibold border bg-white text-slate-600 border-slate-300 transition-colors';
    document.getElementById('tipo-link').className   = esLink  ? cls : clsOff;
    document.getElementById('tipo-button').className = !esLink ? cls : clsOff;
}
function setColor(color) {
    _colorActual = color;
    document.querySelectorAll('.color-btn').forEach(b => b.classList.remove('activo'));
    const btn = document.getElementById('color-' + color);
    if (btn) btn.classList.add('activo');
    document.getElementById('prev-color-dot').style.background = COLOR_HEX[color] || '#6366f1';
}
function actualizarPreview() {
    document.getElementById('prev-icon').textContent  = document.getElementById('f-icon').value  || '🔘';
    document.getElementById('prev-label').textContent = document.getElementById('f-label').value || 'Nombre del botón';
    document.getElementById('prev-key').textContent   = document.getElementById('f-key').value   || 'clave-boton';
}

async function guardarBoton() {
    const key   = document.getElementById('f-key').value.trim();
    const label = document.getElementById('f-label').value.trim();
    if (!key || !label) { showToast('Clave y nombre son obligatorios', 'error'); return; }
    try {
        const res = await fetch(BASE_URL + 'api/roles/save_boton', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                boton_key: key, label,
                icon:    document.getElementById('f-icon').value.trim() || '🔘',
                tipo:    _tipoActual,
                onclick: document.getElementById('f-onclick').value.trim(),
                href:    document.getElementById('f-href').value.trim(),
                color:   _colorActual,
                orden:   parseInt(document.getElementById('f-orden').value) || 99,
            }),
        });
        if (!res.ok) throw new Error();
        cerrarModalBoton();
        showToast(_editKey ? '✓ Botón actualizado' : '✓ Botón agregado', 'ok');
        await cargarBotones();
    } catch { showToast('Error al guardar', 'error'); }
}

async function eliminarBoton(key, label) {
    if (!confirm(`¿Eliminar el botón "${label}"?`)) return;
    try {
        await fetch(BASE_URL + 'api/roles/delete_boton', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ boton_key: key }),
        });
        showToast('✓ Botón eliminado', 'off');
        await cargarBotones();
    } catch { showToast('Error', 'error'); }
}

// ── Toast ─────────────────────────────────────────────────────────────────────
let _toastTimer;
function showToast(msg, tipo = 'ok') {
    const t = document.getElementById('toast');
    const c = { ok:['#166534','#dcfce7'], off:['#1e293b','#e2e8f0'], error:['#991b1b','#fee2e2'] }[tipo] || ['#166534','#dcfce7'];
    t.textContent = msg; t.style.backgroundColor = c[0]; t.style.color = c[1];
    t.style.display = 'block'; t.style.opacity = '1';
    clearTimeout(_toastTimer);
    _toastTimer = setTimeout(() => { t.style.opacity = '0'; setTimeout(() => t.style.display = 'none', 300); }, 2400);
}

mostrarTab('roles');
cargar();
</script>

<?= $this->endSection() ?>
