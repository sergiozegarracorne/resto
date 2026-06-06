<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Usuarios<?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="flex flex-col h-full bg-gray-50">

    <!-- HEADER -->
    <header class="h-14 bg-gray-800 flex items-center justify-between px-4 shrink-0">
        <div class="flex items-center gap-3">
            <a href="<?= base_url('panel') ?>"
               class="h-9 px-3 flex items-center gap-1.5 bg-gray-700 hover:bg-gray-600 text-gray-200 text-sm font-semibold rounded transition">
                ← Panel
            </a>
            <span class="text-white font-bold text-base">Usuarios del Sistema</span>
        </div>
        <button onclick="abrirModal()"
                class="h-9 px-4 bg-orange-500 hover:bg-orange-700 text-white text-sm font-bold rounded transition">
            + Nuevo usuario
        </button>
    </header>

    <!-- TOOLBAR -->
    <div class="bg-white border-b border-gray-200 px-4 h-14 flex items-center gap-3 shrink-0">
        <div class="relative" style="flex:0 0 220px">
           
            <input id="input-buscar" type="search" placeholder="Buscar..."
                   oninput="renderTabla()"
                   class="w-full pl-9 pr-3 h-9 border border-gray-200 rounded-lg text-sm text-gray-700
                          focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
        </div>

        <div class="flex items-center gap-1.5 overflow-x-auto flex-1">
            <button onclick="filtrar(null)" data-f="todos"
                    class="filtro-btn h-8 px-4 rounded-lg border font-semibold text-xs whitespace-nowrap transition
                           border-orange-400 bg-orange-100 text-orange-600">
                Todos
            </button>
            <button onclick="filtrar('vendedor')" data-f="vendedor"
                    class="filtro-btn h-8 px-4 rounded-lg border font-semibold text-xs whitespace-nowrap transition
                           border-gray-200 bg-white text-gray-500 hover:border-orange-400 hover:bg-orange-50">
                Vendedores
            </button>
            <button onclick="filtrar('supervisor')" data-f="supervisor"
                    class="filtro-btn h-8 px-4 rounded-lg border font-semibold text-xs whitespace-nowrap transition
                           border-gray-200 bg-white text-gray-500 hover:border-orange-400 hover:bg-orange-50">
                Supervisores
            </button>
            <button onclick="filtrar('admin')" data-f="admin"
                    class="filtro-btn h-8 px-4 rounded-lg border font-semibold text-xs whitespace-nowrap transition
                           border-gray-200 bg-white text-gray-500 hover:border-orange-400 hover:bg-orange-50">
                Admins
            </button>
            <button onclick="filtrar('sudo')" data-f="sudo"
                    class="filtro-btn h-8 px-4 rounded-lg border font-semibold text-xs whitespace-nowrap transition
                           border-gray-200 bg-white text-gray-500 hover:border-orange-400 hover:bg-orange-50">
                Sudo
            </button>
        </div>

        <span id="contador" class="text-xs text-gray-400 font-medium shrink-0"></span>
    </div>

    <!-- TABLA -->
    <main class="flex-1 overflow-auto p-4">

        <!-- Error state -->
        <div id="msg-error" style="display:none" class="flex flex-col items-center justify-center py-20 text-center">
            <div class="w-14 h-14 rounded-2xl bg-red-100 flex items-center justify-center text-3xl mb-4">⚠️</div>
            <p class="text-gray-700 font-semibold">Error al cargar usuarios</p>
            <p id="msg-error-txt" class="text-gray-400 text-sm mt-1"></p>
            <button onclick="cargar()" class="mt-4 px-4 h-9 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold transition">
                Reintentar
            </button>
        </div>

        <!-- Empty state -->
        <div id="msg-vacio" style="display:none" class="flex flex-col items-center justify-center py-20 text-center">
            <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center text-3xl mb-4">👤</div>
            <p class="text-gray-500 font-semibold">No hay usuarios en este grupo</p>
            <p class="text-gray-400 text-sm mt-1">Usa el botón <strong>+ Nuevo usuario</strong> para agregar uno</p>
        </div>

        <!-- Card contenedor de la tabla -->
        <div id="card-tabla" class="bg-white rounded-xl shadow-sm border border-gray-100" style="display:none">
            <table id="tabla" class="w-full text-sm border-collapse">
                <thead class="sticky top-4 z-10 bg-white shadow-sm">
                    <tr class="border-b-2 border-gray-100">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wide" style="width:110px">Código</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wide">Usuario</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wide" style="width:130px">Rol</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wide" style="width:100px">Estado</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wide" style="width:130px">Desde</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-400 uppercase tracking-wide" style="width:190px">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-body"></tbody>
            </table>
        </div>

    </main>
</div>

<!-- ══ MODAL CREAR / EDITAR ══════════════════════════════ -->
<div id="modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background:rgba(15,23,42,.6)">

    <div class="bg-white w-full rounded-2xl shadow-2xl flex flex-col sm:max-w-lg"
         style="max-height:92vh;animation:slideUp .2s ease">

        <!-- Banda de color -->
        <div id="modal-topbar" class="h-1.5 rounded-t-2xl shrink-0" style="background:#6366f1"></div>

        <!-- Encabezado -->
        <div class="px-6 pt-5 pb-4 flex items-center gap-4 border-b border-gray-100 shrink-0">
            <div id="modal-avatar"
                 class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl font-black text-white bg-indigo-500 shadow-md shrink-0 select-none">
                ?
            </div>
            <div class="flex-1 min-w-0">
                <p id="modal-nombre" class="font-black text-gray-900 text-lg leading-tight truncate">Nuevo usuario</p>
                <p id="modal-rol"    class="text-sm text-gray-400 mt-0.5">Sin rol asignado</p>
            </div>
            <button onclick="cerrarModal()"
                    class="w-9 h-9 rounded-full flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-700 transition text-lg font-bold">
                ✕
            </button>
        </div>

        <!-- Formulario -->
        <form id="form-usuario" class="flex-1 overflow-y-auto" onsubmit="guardarUsuario(event)">
            <input type="hidden" id="u-id">

            <div class="px-6 py-5 space-y-5">

                <!-- Nombre + Código -->
                <div class="grid gap-3" style="grid-template-columns:1fr 100px">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-widest mb-1.5">
                            Nombre completo <span class="text-red-500">*</span>
                        </label>
                        <input id="u-nombre" type="text" required autocomplete="off"
                               placeholder="Ej: María García"
                               oninput="actualizarPreview()"
                               class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5 text-gray-900 text-sm
                                      focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-widest mb-1.5">Código</label>
                        <input id="u-codigo" type="text" maxlength="20" autocomplete="off"
                               placeholder="V001"
                               style="text-transform:uppercase"
                               class="w-full border-2 border-gray-200 rounded-xl px-3 py-2.5 text-gray-900 text-sm font-mono
                                      focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 transition-all">
                    </div>
                </div>

                <!-- Rol -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-widest mb-2">Rol</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" onclick="seleccionarRol('vendedor')" data-rol="vendedor"
                                class="rol-btn flex items-center gap-3 px-3 py-3 rounded-xl border-2 text-left transition-all">
                            <span class="w-9 h-9 rounded-lg bg-indigo-500 flex items-center justify-center text-white text-base shrink-0">🛒</span>
                            <div><p class="font-bold text-gray-800 text-sm leading-none">Vendedor</p><p class="text-xs text-gray-400 mt-0.5">Opera el POS</p></div>
                        </button>
                        <button type="button" onclick="seleccionarRol('supervisor')" data-rol="supervisor"
                                class="rol-btn flex items-center gap-3 px-3 py-3 rounded-xl border-2 text-left transition-all">
                            <span class="w-9 h-9 rounded-lg bg-blue-500 flex items-center justify-center text-white text-base shrink-0">📊</span>
                            <div><p class="font-bold text-gray-800 text-sm leading-none">Supervisor</p><p class="text-xs text-gray-400 mt-0.5">Reportes</p></div>
                        </button>
                        <button type="button" onclick="seleccionarRol('admin')" data-rol="admin"
                                class="rol-btn flex items-center gap-3 px-3 py-3 rounded-xl border-2 text-left transition-all">
                            <span class="w-9 h-9 rounded-lg bg-orange-500 flex items-center justify-center text-white text-base shrink-0">⚙️</span>
                            <div><p class="font-bold text-gray-800 text-sm leading-none">Admin</p><p class="text-xs text-gray-400 mt-0.5">Configuración</p></div>
                        </button>
                        <button type="button" onclick="seleccionarRol('sudo')" data-rol="sudo"
                                class="rol-btn flex items-center gap-3 px-3 py-3 rounded-xl border-2 text-left transition-all">
                            <span class="w-9 h-9 rounded-lg bg-red-500 flex items-center justify-center text-white text-base shrink-0">🔑</span>
                            <div><p class="font-bold text-gray-800 text-sm leading-none">Sudo</p><p class="text-xs text-gray-400 mt-0.5">Acceso total</p></div>
                        </button>
                    </div>
                    <input type="hidden" id="u-rol" value="vendedor">
                </div>

                <!-- Contraseña -->
                <div>
                    <div class="flex items-baseline justify-between mb-1.5">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Contraseña</label>
                        <span id="clave-hint" class="text-xs text-red-500 font-medium">obligatoria</span>
                    </div>
                    <div class="relative">
                        <input id="u-clave" type="password" autocomplete="new-password"
                               placeholder="Mínimo 4 caracteres"
                               class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5 text-gray-900 text-sm pr-12
                                      focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 transition-all">
                        <button type="button" onclick="toggleClave(this)"
                                class="absolute right-3 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    <p class="text-xs text-gray-400 mt-1.5">En edición, déjala vacía para no cambiarla</p>
                </div>

                <!-- Estado -->
                <div class="flex items-center justify-between rounded-xl bg-gray-50 border-2 border-gray-100 px-4 py-3 cursor-pointer select-none"
                     onclick="toggleEstado()">
                    <div>
                        <p class="text-sm font-bold text-gray-800">Usuario activo</p>
                        <p class="text-xs text-gray-400 mt-0.5">Los inactivos no pueden ingresar</p>
                    </div>
                    <div id="toggle-estado" class="w-12 h-6 rounded-full relative shrink-0 ml-4" style="background:#6366f1">
                        <span id="toggle-thumb" class="absolute top-0.5 right-0.5 w-5 h-5 bg-white rounded-full shadow-md block" style="transition:left .2s,right .2s"></span>
                    </div>
                    <input type="hidden" id="u-estado" value="1">
                </div>

                <!-- Error -->
                <div id="u-error" style="display:none"
                     class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-600 text-sm rounded-xl px-4 py-3">
                    <span class="shrink-0 mt-0.5">⚠</span>
                    <span id="u-error-msg"></span>
                </div>

            </div>
        </form>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-gray-100 flex gap-3 shrink-0 bg-gray-50" style="border-radius:0 0 1rem 1rem">
            <button type="button" onclick="cerrarModal()"
                    class="flex-1 h-11 rounded-xl border-2 border-gray-200 text-gray-600 font-semibold text-sm hover:bg-white transition-all">
                Cancelar
            </button>
            <button type="submit" form="form-usuario" id="btn-guardar"
                    class="flex-1 h-11 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-md shadow-indigo-200 transition-all flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Guardar usuario
            </button>
        </div>
    </div>
</div>

<!-- ══ MODAL CONFIRMAR DESACTIVAR ════════════════════════ -->
<div id="modal-delete" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background:rgba(15,23,42,.6)">
    <div class="bg-white rounded-2xl shadow-2xl w-full overflow-hidden" style="max-width:380px;animation:slideUp .2s ease">
        <div class="bg-red-50 px-6 pt-6 pb-5 flex flex-col items-center text-center border-b border-red-100">
            <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center text-3xl mb-3">⚠️</div>
            <p class="font-black text-gray-900 text-lg">¿Desactivar usuario?</p>
            <p id="delete-nombre" class="text-red-600 font-semibold text-sm mt-1"></p>
        </div>
        <div class="px-6 py-4">
            <p class="text-sm text-gray-500 text-center">No podrá ingresar, pero sus datos se conservan.</p>
        </div>
        <div class="px-6 pb-5 flex gap-3">
            <button onclick="cerrarDelete()"
                    class="flex-1 h-11 rounded-xl border-2 border-gray-200 text-gray-600 font-semibold text-sm hover:bg-gray-50 transition">
                Cancelar
            </button>
            <button id="btn-confirmar-delete"
                    class="flex-1 h-11 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-sm shadow-md transition">
                Desactivar
            </button>
        </div>
    </div>
</div>

<style>
@keyframes slideUp {
    from { opacity:0; transform:translateY(10px) }
    to   { opacity:1; transform:translateY(0) }
}
</style>

<script>
const BASE = '<?= base_url() ?>';

// colores confirmados en el CSS compilado
const ROL = {
    vendedor:   { label:'Vendedor',   icon:'🛒', avatarCls:'bg-indigo-500', topbar:'#6366f1', chipBorder:'#6366f1', chipBg:'#e0e7ff' },
    supervisor: { label:'Supervisor', icon:'📊', avatarCls:'bg-blue-500',   topbar:'#3b82f6', chipBorder:'#3b82f6', chipBg:'#dbeafe' },
    admin:      { label:'Admin',      icon:'⚙️', avatarCls:'bg-orange-500', topbar:'#f97316', chipBorder:'#f97316', chipBg:'#ffedd5' },
    sudo:       { label:'Sudo',       icon:'🔑', avatarCls:'bg-red-500',    topbar:'#ef4444', chipBorder:'#ef4444', chipBg:'#fee2e2' },
};
// badge html por rol (inline styles para no depender de clases extra)
function badgeHtml(rol) {
    const c = { vendedor:'#4338ca;background:#e0e7ff', supervisor:'#1d4ed8;background:#dbeafe', admin:'#c2410c;background:#ffedd5', sudo:'#dc2626;background:#fee2e2' };
    const r = ROL[rol] || ROL.vendedor;
    return `<span style="color:${c[rol] || c.vendedor};font-size:11px;font-weight:700;padding:2px 10px;border-radius:999px;display:inline-flex;align-items:center;gap:4px">${r.icon} ${r.label}</span>`;
}

let usuarios = [], rolActivo = null;

function esc(s) { const d = document.createElement('div'); d.textContent = String(s ?? ''); return d.innerHTML; }
function iniciales(n) { return n.trim().split(/\s+/).slice(0,2).map(w=>w[0].toUpperCase()).join(''); }
function formatFecha(s) {
    if (!s) return '—';
    try { return new Date(s).toLocaleDateString('es-PE',{day:'2-digit',month:'short',year:'numeric'}); }
    catch { return s.slice(0,10); }
}

// ── Carga ─────────────────────────────────────────────────
async function cargar() {
    document.getElementById('msg-error').style.display   = 'none';
    document.getElementById('msg-vacio').style.display   = 'none';
    document.getElementById('card-tabla').style.display  = 'none';
    try {
        const res = await fetch(BASE + 'api/usuarios/get_all');
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();
        if (!Array.isArray(data)) throw new Error('Respuesta inesperada del servidor');
        usuarios = data;
        renderTabla();
    } catch (err) {
        document.getElementById('msg-error').style.display    = 'flex';
        document.getElementById('msg-error-txt').textContent  = err.message;
    }
}

// ── Filtro ────────────────────────────────────────────────
function filtrar(rol) {
    rolActivo = rol;
    document.querySelectorAll('.filtro-btn').forEach(b => {
        const activo = (rol === null && b.dataset.f === 'todos') || b.dataset.f === rol;
        // reset a inactivo
        b.classList.remove('border-orange-400','bg-orange-100','text-orange-600');
        b.classList.add('border-gray-200','bg-white','text-gray-500');
        if (activo) {
            b.classList.remove('border-gray-200','bg-white','text-gray-500');
            b.classList.add('border-orange-400','bg-orange-100','text-orange-600');
        }
    });
    renderTabla();
}

// ── Tabla ─────────────────────────────────────────────────
function renderTabla() {
    const buscar = (document.getElementById('input-buscar').value || '').toLowerCase().trim();
    let lista = rolActivo ? usuarios.filter(u => u.rol === rolActivo) : usuarios;
    if (buscar) lista = lista.filter(u =>
        u.nombre.toLowerCase().includes(buscar) ||
        (u.codigo && u.codigo.toLowerCase().includes(buscar))
    );

    document.getElementById('contador').textContent = lista.length + ' usuario' + (lista.length !== 1 ? 's' : '');

    if (!lista.length) {
        document.getElementById('card-tabla').style.display = 'none';
        document.getElementById('msg-vacio').style.display  = 'flex';
        return;
    }
    document.getElementById('msg-vacio').style.display  = 'none';
    document.getElementById('card-tabla').style.display = 'block';

    document.getElementById('tabla-body').innerHTML = lista.map(u => {
        const r    = ROL[u.rol] || ROL.vendedor;
        const inac = u.estado == 0;
        const opac = inac ? 'opacity-50' : '';
        return `
        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors ${opac}">
            <td class="px-5 py-3.5">
                ${u.codigo
                    ? `<span class="font-mono text-xs font-bold bg-gray-100 text-gray-600 px-2.5 py-1 rounded-lg">${esc(u.codigo)}</span>`
                    : `<span class="text-gray-300 text-xs">—</span>`}
            </td>
            <td class="px-5 py-3.5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl ${r.avatarCls} flex items-center justify-center text-white text-sm font-black shrink-0 shadow-sm">
                        ${iniciales(u.nombre)}
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800 text-sm leading-tight">${esc(u.nombre)}</p>
                        <p class="text-xs text-gray-400 mt-0.5">#${u.id_usuario}</p>
                    </div>
                </div>
            </td>
            <td class="px-5 py-3.5">${badgeHtml(u.rol)}</td>
            <td class="px-5 py-3.5">
                ${inac
                    ? `<span class="flex items-center gap-1.5 text-xs font-semibold text-gray-400"><span class="w-2 h-2 rounded-full bg-gray-300 inline-block"></span>Inactivo</span>`
                    : `<span class="flex items-center gap-1.5 text-xs font-semibold text-emerald-600"><span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span>Activo</span>`}
            </td>
            <td class="px-5 py-3.5 text-xs text-gray-400 whitespace-nowrap">${formatFecha(u.created_at)}</td>
            <td class="px-5 py-3.5">
                <div class="flex items-center justify-end gap-1">
                    <button onclick="editar(${u.id_usuario})"
                            class="px-3 py-1.5 text-xs font-semibold text-indigo-600 hover:bg-indigo-50 rounded-lg transition border border-transparent hover:border-indigo-200 whitespace-nowrap">
                        ✏ Editar
                    </button>
                    <button onclick="${inac ? `reactivar(${u.id_usuario})` : `confirmarDelete(${u.id_usuario},${JSON.stringify(u.nombre)})`}"
                            class="px-3 py-1.5 text-xs font-semibold rounded-lg transition border border-transparent whitespace-nowrap
                                   ${inac ? 'text-emerald-600 hover:bg-emerald-50 hover:border-emerald-200'
                                          : 'text-red-500 hover:bg-red-50 hover:border-red-200'}">
                        ${inac ? '✓ Activar' : '✕ Desactivar'}
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

// ── Preview modal ─────────────────────────────────────────
function actualizarPreview() {
    const n = document.getElementById('u-nombre').value.trim();
    document.getElementById('modal-nombre').textContent = n || 'Nuevo usuario';
    document.getElementById('modal-avatar').textContent = n ? iniciales(n) : '?';
}

// ── Seleccionar rol ───────────────────────────────────────
function seleccionarRol(rol) {
    const r = ROL[rol] || ROL.vendedor;
    document.getElementById('u-rol').value = rol;
    document.getElementById('modal-rol').textContent = r.label;

    // topbar + avatar color
    document.getElementById('modal-topbar').style.background = r.topbar;
    const av = document.getElementById('modal-avatar');
    av.className = `w-14 h-14 rounded-2xl flex items-center justify-center text-2xl font-black text-white shadow-md shrink-0 select-none ${r.avatarCls}`;

    // chips de rol
    document.querySelectorAll('.rol-btn').forEach(b => {
        const activo = b.dataset.rol === rol;
        b.style.borderColor      = activo ? r.chipBorder : '#e5e7eb';
        b.style.backgroundColor  = activo ? r.chipBg     : '#ffffff';
    });
}

// ── Toggle estado ─────────────────────────────────────────
function toggleEstado() { setEstado(document.getElementById('u-estado').value !== '1'); }
function setEstado(on) {
    document.getElementById('u-estado').value = on ? '1' : '0';
    document.getElementById('toggle-estado').style.background = on ? '#6366f1' : '#d1d5db';
    const t = document.getElementById('toggle-thumb');
    t.style.right = on ? '2px' : ''; t.style.left = on ? '' : '2px';
}

// ── Toggle password ───────────────────────────────────────
function toggleClave(btn) {
    const i = document.getElementById('u-clave');
    i.type = i.type === 'password' ? 'text' : 'password';
}

// ── Mostrar / ocultar error ───────────────────────────────
function mostrarError(msg) {
    const el = document.getElementById('u-error');
    el.style.display = msg ? 'flex' : 'none';
    document.getElementById('u-error-msg').textContent = msg || '';
}

// ── Abrir modal ───────────────────────────────────────────
function abrirModal(u = null) {
    const esEdicion = !!u;
    document.getElementById('u-id').value     = u?.id_usuario || '';
    document.getElementById('u-nombre').value = u?.nombre     || '';
    document.getElementById('u-codigo').value = u?.codigo     || '';
    document.getElementById('u-clave').value  = '';
    document.getElementById('clave-hint').textContent = esEdicion ? 'dejar vacío para no cambiar' : 'obligatoria';
    seleccionarRol(u?.rol || 'vendedor');
    setEstado(u ? u.estado == 1 : true);
    actualizarPreview();
    mostrarError(null);
    document.getElementById('modal').classList.remove('hidden');
    setTimeout(() => document.getElementById('u-nombre').focus(), 60);
}
function cerrarModal() { document.getElementById('modal').classList.add('hidden'); }
function editar(id)    { abrirModal(usuarios.find(u => u.id_usuario == id)); }

// ── Guardar ───────────────────────────────────────────────
async function guardarUsuario(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-guardar');
    btn.disabled = true;
    btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-50" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg> Guardando...';
    try {
        const res  = await fetch(BASE + 'api/usuarios/save', {
            method:  'POST',
            headers: {'Content-Type':'application/json'},
            body:    JSON.stringify({
                id:     document.getElementById('u-id').value     || null,
                codigo: document.getElementById('u-codigo').value.trim() || null,
                nombre: document.getElementById('u-nombre').value,
                rol:    document.getElementById('u-rol').value,
                clave:  document.getElementById('u-clave').value  || null,
                estado: document.getElementById('u-estado').value,
            })
        });
        const data = await res.json();
        if (data.success) { cerrarModal(); cargar(); }
        else { mostrarError(data.message || 'Error al guardar'); }
    } catch { mostrarError('Error de conexión'); }
    finally {
        btn.disabled = false;
        btn.innerHTML = '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Guardar usuario';
    }
}

// ── Desactivar ────────────────────────────────────────────
let deleteId = null;
function confirmarDelete(id, nombre) {
    deleteId = id;
    document.getElementById('delete-nombre').textContent = nombre;
    document.getElementById('modal-delete').classList.remove('hidden');
}
function cerrarDelete() { document.getElementById('modal-delete').classList.add('hidden'); deleteId = null; }

document.getElementById('btn-confirmar-delete').addEventListener('click', async () => {
    if (!deleteId) return;
    const res = await fetch(BASE + 'api/usuarios/delete', {
        method: 'POST', headers: {'Content-Type':'application/json'},
        body: JSON.stringify({id: deleteId})
    });
    const d = await res.json();
    cerrarDelete();
    if (d.success) cargar(); else alert(d.message);
});

async function reactivar(id) {
    const u = usuarios.find(u => u.id_usuario == id);
    if (!u) return;
    const res = await fetch(BASE + 'api/usuarios/save', {
        method: 'POST', headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ id, codigo: u.codigo || null, nombre: u.nombre, rol: u.rol, estado: 1 })
    });
    const d = await res.json();
    if (d.success) cargar(); else alert(d.message);
}

// Cerrar modal al clic en fondo
['modal','modal-delete'].forEach(id => {
    document.getElementById(id).addEventListener('click', e => {
        if (e.target === e.currentTarget) e.currentTarget.classList.add('hidden');
    });
});

cargar();
</script>

<?= $this->endSection() ?>
