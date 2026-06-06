<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Registrar Compra<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="flex w-full h-full overflow-hidden">

    <!-- COLUMNA IZQUIERDA: TICKET DE COMPRA -->
    <aside class="w-[280px] h-full bg-white border-r border-gray-300 flex flex-col shadow-xl z-10 shrink-0">

        <!-- Header -->
        <div class="bg-green-600 text-white px-4 py-3 flex items-center justify-between shrink-0">
            <div>
                <p class="text-xs opacity-80">Almacén</p>
                <h1 class="font-bold text-lg leading-tight">Registrar Compra</h1>
            </div>
            <a href="<?= base_url('/almacen/insumos') ?>"
                class="text-white opacity-70 hover:opacity-100 transition text-sm underline">
                Ver stock
            </a>
        </div>

        <!-- Proveedor -->
        <div class="px-3 pt-3 shrink-0">
            <label class="text-xs text-gray-500 font-semibold uppercase tracking-wide">¿De dónde compraste?</label>
            <input id="inp-proveedor" type="text" placeholder="Nombre del proveedor (opcional)"
                class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-green-500">
        </div>

        <!-- Lista de items -->
        <div class="flex-1 overflow-y-auto px-3 py-2" id="lista-compra">
            <p class="text-gray-400 text-sm text-center mt-8" id="msg-vacio">
                👈 Elige un ingrediente de la derecha para agregar
            </p>
        </div>

        <!-- Total -->
        <div class="border-t border-gray-200 px-4 py-3 shrink-0">
            <div class="flex justify-between items-center text-lg font-bold text-gray-800 mb-1">
                <span>Total pagado:</span>
                <span id="total-compra">S/ 0.00</span>
            </div>
            <textarea id="inp-notas" placeholder="Notas (opcional)" rows="2"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm resize-none focus:outline-none focus:border-green-400 mb-2"></textarea>
            <button onclick="registrarCompra()"
                class="w-full bg-green-600 hover:bg-green-700 active:bg-green-800 text-white font-bold py-3 rounded-xl text-base transition-colors shadow">
                ✅ Registrar Compra
            </button>
        </div>
    </aside>

    <!-- COLUMNA DERECHA: LISTA DE INGREDIENTES -->
    <main class="flex-1 h-full flex flex-col bg-gray-100 min-w-0">

        <!-- Buscador -->
        <div class="px-4 py-3 bg-white border-b border-gray-200 flex items-center gap-3 shrink-0">
            <a href="<?= base_url('/') ?>" class="text-gray-400 hover:text-gray-600 transition shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div class="relative flex-1">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">🔍</span>
                <input id="buscador" type="text" placeholder="Buscar ingrediente..."
                    oninput="buscarInsumo(this.value)"
                    class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-green-500 bg-gray-50">
            </div>
            <button onclick="abrirModalNuevoInsumo()"
                class="shrink-0 bg-green-100 hover:bg-green-200 text-green-700 font-semibold px-4 py-2 rounded-xl text-sm transition">
                + Nuevo
            </button>
        </div>

        <!-- Grid de ingredientes -->
        <div class="flex-1 overflow-y-auto p-4">
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3" id="grid-insumos">
                <?php foreach ($insumos as $insumo): ?>
                    <button
                        class="insumo-card bg-white border-2 border-gray-200 hover:border-green-400 active:bg-green-50 rounded-xl p-3 flex flex-col items-center gap-1 transition-all cursor-pointer text-center"
                        data-id="<?= $insumo['id'] ?>"
                        data-nombre="<?= esc($insumo['nombre']) ?>"
                        data-unidad="<?= esc($insumo['unidad']) ?>"
                        data-stock="<?= $insumo['stock_actual'] ?>"
                        onclick="seleccionarInsumo(this)">
                        <span class="text-3xl">📦</span>
                        <span class="font-semibold text-gray-700 text-sm leading-tight"><?= esc($insumo['nombre']) ?></span>
                        <span class="text-xs text-gray-400">Stock: <?= number_format($insumo['stock_actual'], 1) ?> <?= esc($insumo['unidad']) ?></span>
                        <?php if ($insumo['stock_actual'] <= $insumo['stock_minimo'] && $insumo['stock_minimo'] > 0): ?>
                            <span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full font-semibold">¡Stock bajo!</span>
                        <?php endif; ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <?php if (empty($insumos)): ?>
                <p class="text-center text-gray-400 mt-16">No hay ingredientes registrados.<br>Crea el primero con el botón <strong>+ Nuevo</strong>.</p>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- MODAL: Cantidad y precio -->
<div id="modal-cantidad" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-1" id="modal-nombre">Harina</h2>
        <p class="text-sm text-gray-500 mb-5" id="modal-unidad">Unidad: kg</p>

        <div class="mb-4">
            <label class="text-sm font-semibold text-gray-600 block mb-1">¿Cuánto compraste?</label>
            <div class="flex items-center gap-2">
                <button onclick="ajustarCantidad(-1)"
                    class="w-11 h-11 bg-gray-100 hover:bg-gray-200 rounded-xl text-xl font-bold text-gray-600 transition">−</button>
                <input id="inp-cantidad" type="number" min="0.1" step="0.5" value="1"
                    class="flex-1 border border-gray-300 rounded-xl text-center text-xl font-bold py-2 focus:outline-none focus:border-green-500">
                <button onclick="ajustarCantidad(1)"
                    class="w-11 h-11 bg-gray-100 hover:bg-gray-200 rounded-xl text-xl font-bold text-gray-600 transition">+</button>
            </div>
        </div>

        <div class="mb-6">
            <label class="text-sm font-semibold text-gray-600 block mb-1">¿Cuánto pagaste en total por esto?</label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">S/</span>
                <input id="inp-precio" type="number" min="0" step="0.50" value="0" placeholder="0.00"
                    class="w-full border border-gray-300 rounded-xl pl-9 pr-4 py-3 text-lg font-bold focus:outline-none focus:border-green-500">
            </div>
        </div>

        <div class="flex gap-3">
            <button onclick="cerrarModal()"
                class="flex-1 py-3 border border-gray-300 rounded-xl text-gray-600 hover:bg-gray-50 font-semibold transition">
                Cancelar
            </button>
            <button onclick="agregarAlTicket()"
                class="flex-1 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl font-bold transition shadow">
                Agregar ✓
            </button>
        </div>
    </div>
</div>

<!-- MODAL: Nuevo insumo -->
<div id="modal-nuevo" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-5">Nuevo ingrediente</h2>

        <div class="mb-4">
            <label class="text-sm font-semibold text-gray-600 block mb-1">Nombre del ingrediente</label>
            <input id="nuevo-nombre" type="text" placeholder="Ej: Harina, Tomate, Aceite..."
                class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:border-green-500">
        </div>

        <div class="mb-4">
            <label class="text-sm font-semibold text-gray-600 block mb-2">¿En qué se mide?</label>
            <div class="grid grid-cols-3 gap-2" id="unidades-grid">
                <?php foreach (['kg', 'litros', 'unidades', 'caja', 'bolsa', 'gramos'] as $u): ?>
                    <button onclick="selectUnidad(this, '<?= $u ?>')"
                        class="unidad-btn border-2 border-gray-200 rounded-xl py-2 text-sm font-semibold text-gray-600 hover:border-green-400 hover:text-green-600 transition">
                        <?= $u ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <input id="nuevo-unidad" type="hidden" value="">
        </div>

        <div class="mb-6">
            <label class="text-sm font-semibold text-gray-600 block mb-1">Aviso cuando quede menos de... (opcional)</label>
            <div class="flex items-center gap-2">
                <input id="nuevo-stock-min" type="number" min="0" step="1" value="0" placeholder="0"
                    class="flex-1 border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:border-green-500">
                <span id="nuevo-unidad-label" class="text-sm text-gray-500 w-16">unidades</span>
            </div>
        </div>

        <div class="flex gap-3">
            <button onclick="cerrarModalNuevo()"
                class="flex-1 py-3 border border-gray-300 rounded-xl text-gray-600 hover:bg-gray-50 font-semibold transition">
                Cancelar
            </button>
            <button onclick="guardarNuevoInsumo()"
                class="flex-1 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl font-bold transition shadow">
                Guardar ✓
            </button>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const items = {};
    let insumoActivo = null;

    // ── Seleccionar insumo ──────────────────────────────────────────
    function seleccionarInsumo(btn) {
        insumoActivo = {
            id:     btn.dataset.id,
            nombre: btn.dataset.nombre,
            unidad: btn.dataset.unidad,
        };
        document.getElementById('modal-nombre').textContent = insumoActivo.nombre;
        document.getElementById('modal-unidad').textContent = 'Se mide en: ' + insumoActivo.unidad;
        document.getElementById('inp-cantidad').value = 1;
        document.getElementById('inp-precio').value = '';
        document.getElementById('modal-cantidad').classList.remove('hidden');
        setTimeout(() => document.getElementById('inp-cantidad').select(), 100);
    }

    function ajustarCantidad(delta) {
        const inp = document.getElementById('inp-cantidad');
        const val = parseFloat(inp.value) || 0;
        inp.value = Math.max(0.5, val + delta).toFixed(delta === 1 ? 0 : 1).replace(/\.0$/, '');
    }

    function cerrarModal() {
        document.getElementById('modal-cantidad').classList.add('hidden');
        insumoActivo = null;
    }

    // ── Agregar al ticket ───────────────────────────────────────────
    function agregarAlTicket() {
        const cantidad      = parseFloat(document.getElementById('inp-cantidad').value) || 0;
        const precioTotal   = parseFloat(document.getElementById('inp-precio').value) || 0;

        if (cantidad <= 0) { alert('Ingresa una cantidad mayor a 0'); return; }

        const id = insumoActivo.id;
        items[id] = {
            id_insumo:       id,
            nombre:          insumoActivo.nombre,
            unidad:          insumoActivo.unidad,
            cantidad:        cantidad,
            precio_unitario: precioTotal,
        };

        cerrarModal();
        renderTicket();
    }

    // ── Renderizar ticket ───────────────────────────────────────────
    function renderTicket() {
        const lista   = document.getElementById('lista-compra');
        const msgVacio = document.getElementById('msg-vacio');
        const keys    = Object.keys(items);

        if (keys.length === 0) {
            lista.innerHTML = '';
            msgVacio.classList.remove('hidden');
            document.getElementById('total-compra').textContent = 'S/ 0.00';
            return;
        }

        msgVacio.classList.add('hidden');
        let total = 0;
        let html = '';

        keys.forEach(id => {
            const it = items[id];
            total += it.precio_unitario;
            html += `
                <div class="flex items-center justify-between py-2 border-b border-gray-100 gap-2">
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-sm text-gray-800 truncate">${it.nombre}</p>
                        <p class="text-xs text-gray-500">${it.cantidad} ${it.unidad} · S/ ${it.precio_unitario.toFixed(2)}</p>
                    </div>
                    <button onclick="quitarItem('${id}')"
                        class="text-red-400 hover:text-red-600 shrink-0 p-1 transition text-lg leading-none">✕</button>
                </div>`;
        });

        lista.innerHTML = html;
        document.getElementById('total-compra').textContent = 'S/ ' + total.toFixed(2);
    }

    function quitarItem(id) {
        delete items[id];
        renderTicket();
    }

    // ── Buscar insumo ───────────────────────────────────────────────
    function buscarInsumo(q) {
        const texto = q.toLowerCase();
        document.querySelectorAll('.insumo-card').forEach(card => {
            const nombre = card.dataset.nombre.toLowerCase();
            card.closest ? card.style.display = nombre.includes(texto) ? '' : 'none' : null;
            card.style.display = nombre.includes(texto) ? '' : 'none';
        });
    }

    // ── Registrar compra ────────────────────────────────────────────
    async function registrarCompra() {
        const keys = Object.keys(items);
        if (keys.length === 0) {
            alert('Agrega al menos un ingrediente antes de registrar');
            return;
        }

        const total = Object.values(items).reduce((s, it) => s + it.precio_unitario, 0);

        const payload = {
            proveedor: document.getElementById('inp-proveedor').value.trim() || null,
            notas:     document.getElementById('inp-notas').value.trim() || null,
            total:     total,
            items:     Object.values(items),
        };

        const btn = event.currentTarget;
        btn.disabled = true;
        btn.textContent = 'Guardando...';

        try {
            const res  = await fetch('<?= base_url('/api/save_compra') ?>', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify(payload),
            });
            const data = await res.json();

            if (data.success) {
                mostrarExito();
            } else {
                alert('Error: ' + (data.message || 'No se pudo guardar'));
                btn.disabled = false;
                btn.textContent = '✅ Registrar Compra';
            }
        } catch (e) {
            alert('Error de conexión. Intenta nuevamente.');
            btn.disabled = false;
            btn.textContent = '✅ Registrar Compra';
        }
    }

    function mostrarExito() {
        Object.keys(items).forEach(k => delete items[k]);
        renderTicket();
        document.getElementById('inp-proveedor').value = '';
        document.getElementById('inp-notas').value = '';

        // Recargar página para actualizar stock
        const banner = document.createElement('div');
        banner.className = 'fixed top-4 left-1/2 -translate-x-1/2 bg-green-600 text-white px-6 py-3 rounded-2xl shadow-xl font-bold text-base z-50 transition-all';
        banner.textContent = '✅ ¡Compra registrada! El stock fue actualizado.';
        document.body.appendChild(banner);
        setTimeout(() => location.reload(), 2000);
    }

    // ── Nuevo insumo ────────────────────────────────────────────────
    function abrirModalNuevoInsumo() {
        document.getElementById('nuevo-nombre').value = '';
        document.getElementById('nuevo-unidad').value = '';
        document.getElementById('nuevo-stock-min').value = 0;
        document.getElementById('nuevo-unidad-label').textContent = 'unidades';
        document.querySelectorAll('.unidad-btn').forEach(b => b.classList.remove('border-green-500', 'text-green-600', 'bg-green-50'));
        document.getElementById('modal-nuevo').classList.remove('hidden');
        setTimeout(() => document.getElementById('nuevo-nombre').focus(), 100);
    }

    function cerrarModalNuevo() {
        document.getElementById('modal-nuevo').classList.add('hidden');
    }

    function selectUnidad(btn, valor) {
        document.querySelectorAll('.unidad-btn').forEach(b => b.classList.remove('border-green-500', 'text-green-600', 'bg-green-50'));
        btn.classList.add('border-green-500', 'text-green-600', 'bg-green-50');
        document.getElementById('nuevo-unidad').value = valor;
        document.getElementById('nuevo-unidad-label').textContent = valor;
    }

    async function guardarNuevoInsumo() {
        const nombre = document.getElementById('nuevo-nombre').value.trim();
        const unidad = document.getElementById('nuevo-unidad').value;
        const stockMin = parseFloat(document.getElementById('nuevo-stock-min').value) || 0;

        if (!nombre)  { alert('Escribe el nombre del ingrediente'); return; }
        if (!unidad)  { alert('Elige cómo se mide'); return; }

        const res  = await fetch('<?= base_url('/api/save_insumo') ?>', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ nombre, unidad, stock_minimo: stockMin }),
        });
        const data = await res.json();

        if (data.success) {
            cerrarModalNuevo();
            location.reload();
        } else {
            alert('Error al guardar: ' + (data.message || ''));
        }
    }

    // Cerrar modales con Escape
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            cerrarModal();
            cerrarModalNuevo();
        }
    });
</script>
<?= $this->endSection() ?>
