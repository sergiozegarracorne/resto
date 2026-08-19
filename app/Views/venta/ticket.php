<!-- Encabezado Ticket -->
<div class="p-3 border-b border-gray-200 bg-gray-50 space-y-2">
    <!-- Fila 1: Título y Reloj (Neon Style) -->
    <div class="flex justify-center items-center">
        <?= componente_reloj() ?>
    </div>

    <!-- Fila 2: Mesero y Estado -->
    <div class="flex justify-between items-center text-xs">
        <div class="flex items-center gap-1 text-gray-600">
            <span class="text-lg">💁</span>
            <span class="font-bold"><?= session('usuario_turno')['nombre'] ?? 'Sin Asignar' ?></span>
        </div>
        <div class="flex items-center gap-1">
            <?php if (in_array(session('usuario_turno')['rol'] ?? '', ['admin','sudo','supervisor'], true)): ?>
            <a href="<?= base_url('panel') ?>"
               title="Panel de Control"
               class="w-6 h-6 flex items-center justify-center rounded-full bg-gray-200 hover:bg-indigo-100 hover:text-indigo-700 transition-colors text-sm">
                ⚙️
            </a>
            <?php endif; ?>
            <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium text-[10px]">En Proceso</span>
        </div>
    </div>

    <!-- Fila 2.5: Mesa Activa -->
    <div id="mesa-info-display"
        class="hidden flex justify-between items-center bg-indigo-50 px-3 py-2jj rounded-lg border border-indigo-100 animate-pulse-once">
        <span class="font-bold text-indigo-700 flex items-center gap-1">
            🍽️ <span id="mesa-nombre-display">Mesa ?</span>
        </span>
        <span class="text-[10px] bg-white px-2 py-0.5 rounded shadow-sm text-indigo-600 font-bold uppercase"
            id="mesa-estado-display">...</span>
    </div>

    <!-- Fila 3: Tipo de Tarifa (Selector) -->
    <div class="bg-white border border-gray-200 rounded-lg p-1 flex items-center justify-between shadow-sm">
        <span class="text-[10px] text-gray-500 uppercase font-bold px-1">Tarifa:</span>
        <select class="text-xs font-bold text-indigo-700 bg-transparent outline-none cursor-pointer flex-1 text-right">
            <option value="general">CARTA GENERAL</option>
            <option value="promo">PROMOCIÓN</option>
            <option value="personal">PERSONAL</option>
            <option value="socio">SOCIO / VIP</option>
        </select>
    </div>
</div>



<!-- Lista de Items (Scrollable) -->
<div class="flex-1 relative min-h-0 group/scroll">
    <div class="h-full overflow-y-auto p-2 custom-scrollbar scroll-smooth" id="lista-ticket">
        <!-- JS renderizará aquí -->
        <p class="text-center text-gray-400 text-sm mt-10">Orden vacía</p>
    </div>

    <!-- Scroll Helper -->
    <?= controles_scroll('lista-ticket', 'abajo-izquierda') ?>
</div>

<!-- Totales y Acciones -->
<?php
$_rolActual  = session('usuario_turno')['rol'] ?? 'vendedor';
$_puedeAdmin = in_array($_rolActual, ['supervisor', 'admin', 'sudo'], true);
?>
<div class="p-4 bg-gray-50 border-t border-gray-200">
    <div class="flex justify-between items-center mb-4 text-xl font-bold text-gray-800">
        <span id="total-ticket-label">Total</span>
        <span id="total-ticket">S/ 0.00</span>
    </div>

    <?php if ($_puedeAdmin): ?>
    <div id="pre-cuenta-banner" class="hidden mb-3 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 text-amber-700 text-xs font-bold" style="display:none">
        <span>🙋💰</span><span>Pre-Cuenta solicitada — Mesa lista para cobrar</span>
    </div>
    <div class="grid grid-cols-2 gap-2">
        <button onclick="showCancelModal()"
            class="bg-red-50 text-red-600 font-bold py-3 rounded-lg hover:bg-red-100 transition-colors active:scale-95">
            Cancelar
        </button>
        <button onclick="showCheckoutModal()"
            class="bg-indigo-600 text-white font-bold py-3 rounded-lg hover:bg-indigo-700 shadow-lg hover:shadow-indigo-500/30 transition-all active:scale-95">
            Cobrar
        </button>
    </div>
    <?php else: ?>
    <button id="btn-comanda-vendor" onclick="showComandaModal()"
        class="w-full bg-blue-600 text-white font-bold py-3 rounded-lg hover:bg-blue-700 shadow-lg transition-all active:scale-95 text-base">
        🍽️ Confirmar Comanda
    </button>
    <?php endif; ?>
</div>

<script>
    // Estado del carrito y mesa
    let carrito = [];          // ítems pendientes de pago
    let carritoHistorial = []; // ítems ya cobrados en esta sesión (display only)
    let undoStack = [];        // snapshots del carrito para deshacer (máx 30)
    let currentMesa = null;
    let saveTimeout = null;
    let preCuentaMode = false;
    const esVendedor = <?= ($_rolActual === 'vendedor') ? 'true' : 'false' ?>;

    // Interface used by Mesas Overlay
    window.app = {
        selectMesa: async function (mesa) {
            currentMesa = mesa;
            updateMesaUI();
            await loadPedido(mesa.id);
        }
    };

    function updateMesaUI() {
        const ui = document.getElementById('mesa-info-display');
        if (currentMesa) {
            document.getElementById('mesa-nombre-display').innerText = currentMesa.nombre;
            document.getElementById('mesa-estado-display').innerText = currentMesa.estado.toUpperCase();
            ui.classList.remove('hidden');
        } else {
            ui.classList.add('hidden');
        }
    }

    async function loadPedido(idMesa) {
        try {
            const res = await fetch('<?= base_url('api/get_mesa_pedido') ?>/' + idMesa + '?t=' + Date.now());
            const data = await res.json();

            if (data.success) {
                const allItems = data.items.map(i => ({
                    id: i.id,
                    nombre: i.nombre,
                    precio: parseFloat(i.precio),
                    cantidad: parseInt(i.cantidad),
                    commanded_qty: parseInt(i.cantidad),
                    detalle: '',
                    pagado: !!i.pagado,
                }));
                carrito          = allItems.filter(i => !i.pagado);
                carritoHistorial = allItems.filter(i =>  i.pagado);
                // Preserve pre_cuenta estado; solo actualizar a 'ocupada' si no era pre_cuenta
                if (currentMesa && currentMesa.estado !== 'pre_cuenta') {
                    currentMesa.estado = 'ocupada';
                }
            } else {
                carrito = [];
                carritoHistorial = [];
                if (currentMesa) currentMesa.estado = 'libre';
            }

            preCuentaMode = currentMesa?.estado === 'pre_cuenta';
            updateMesaUI();
            _updatePreCuentaUI();
        } catch (e) {
            console.error('Error loading order', e);
            carrito = [];
            preCuentaMode = false;
        }
        renderizarTicket();

        // Si tiene productos y NO está en pre-cuenta, preguntar qué hacer
        if (carrito.length > 0 && !preCuentaMode) {
            _showMesaActionModal();
        }
    }

    // ── Undo ─────────────────────────────────────────────────────────────────────
    function pushUndo() {
        if (preCuentaMode) return; // no acumular historial en pre-cuenta
        undoStack.push(JSON.parse(JSON.stringify(carrito)));
        if (undoStack.length > 30) undoStack.shift();
        _updateUndoBtn();
    }

    function _updateUndoBtn() {
        const btn = document.getElementById('btn-corregir');
        if (!btn) return;
        const activo = undoStack.length > 0 && !preCuentaMode;
        btn.disabled = !activo;
        btn.style.opacity = activo ? '1' : '0.4';
    }

    window.undoUltimaAccion = function() {
        if (undoStack.length === 0 || preCuentaMode) return;
        carrito = undoStack.pop();
        _updateUndoBtn();
        renderizarTicket();
        autoSave();
    };

    // Función Global: Agregar Producto (Modificada con AutoSave)
    window.agregarProducto = function (id, nombre, precio) {
        if (preCuentaMode) return; // bloqueado en pre-cuenta
        pushUndo();
        const esPrimerProducto = carrito.length === 0;
        const existente = carrito.find(item => item.id == id);

        if (existente) {
            existente.cantidad++;
        } else {
            carrito.push({
                id: id,
                nombre: nombre,
                precio: parseFloat(precio),
                cantidad: 1,
                commanded_qty: 0,
                detalle: ''
            });
        }

        renderizarTicket();
        autoSave();

        // Para mozo: al primer producto sin mesa, abrir selector de mesas automáticamente
        if (esVendedor && esPrimerProducto && !currentMesa && window.toggleMesasOverlay) {
            window.toggleMesasOverlay();
        }
    };

    function autoSave() {
        if (!currentMesa) return;
        if (esVendedor) return; // vendedor usa confirmación explícita con "Confirmar Comanda"

        // Show saving indicator?
        const statusEl = document.getElementById('mesa-estado-display');
        if (statusEl) statusEl.innerText = 'GUARDANDO...';

        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(async () => {
            try {
                const res = await fetch('<?= base_url('api/save_pedido') ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id_mesa: currentMesa.id,
                        productos: carrito
                    })
                });
                const data = await res.json();
                if (data.success) {
                    carrito.forEach(i => { i.commanded_qty = i.cantidad; });
                    undoStack = []; _updateUndoBtn();
                    renderizarTicket();
                    if (statusEl) statusEl.innerText = 'GUARDADO';
                    setTimeout(() => { if (statusEl) statusEl.innerText = 'OCUPADA'; }, 1000);
                }
            } catch (e) {
                console.error('Save error', e);
                if (statusEl) statusEl.innerText = 'ERROR';
            }
        }, 800); // 800ms debounce
    }

    // Función Renderizar
    function renderizarTicket() {
        const contenedor   = document.getElementById('lista-ticket');
        const displayTotal = document.getElementById('total-ticket');
        const labelTotal   = document.getElementById('total-ticket-label');

        if (carrito.length === 0 && carritoHistorial.length === 0) {
            contenedor.innerHTML = '<p class="text-center text-gray-400 text-sm mt-10">Orden vacía</p>';
            displayTotal.innerText = 'S/ 0.00';
            if (labelTotal) labelTotal.innerText = 'Total';
            return;
        }

        let html  = '';
        let total = 0;
        const hayHistorial = carritoHistorial.length > 0;

        // ── Sección: ítems ya pagados (verde, solo lectura) ──────────────────
        if (hayHistorial) {
            html += `<div class="flex items-center gap-1.5 px-3 py-1.5" style="background:#f0fdf4">
                <span style="width:8px;height:8px;border-radius:50%;background:#22c55e;display:inline-block;flex-shrink:0"></span>
                <span style="font-size:10px" class="font-bold text-green-700 uppercase tracking-wide">Pagado</span>
            </div>`;
            carritoHistorial.forEach(item => {
                const sub = item.precio * item.cantidad;
                html += `
                <div class="flex justify-between items-center px-3 py-2 border-b" style="border-color:#f0fdf4;opacity:0.65">
                    <div class="flex items-center gap-2 min-w-0">
                        <span style="width:8px;height:8px;border-radius:50%;background:#22c55e;display:inline-block;flex-shrink:0"></span>
                        <p class="text-xs text-gray-400 truncate" style="text-decoration:line-through">
                            <span class="font-bold">${item.cantidad}×</span> ${item.nombre}
                        </p>
                    </div>
                    <span class="text-xs text-gray-400 shrink-0 ml-2">S/ ${sub.toFixed(2)}</span>
                </div>`;
            });
        }

        // ── Sección: ítems pendientes ─────────────────────────────────────────
        if (carrito.length > 0) {
            if (hayHistorial) {
                html += `<div class="flex items-center gap-1.5 px-3 py-1.5 mt-1" style="background:#fff1f2">
                    <span style="width:8px;height:8px;border-radius:50%;background:#ef4444;display:inline-block;flex-shrink:0"></span>
                    <span style="font-size:10px" class="font-bold text-red-600 uppercase tracking-wide">Pendiente</span>
                </div>`;
            }

            carrito.forEach((item) => {
                const subtotal = item.precio * item.cantidad;
                total += subtotal;

                const cq  = item.commanded_qty ?? 0;
                const nq  = item.cantidad - cq;
                let badge = '';
                if (cq === 0) {
                    badge = `<span style="font-size:10px;padding:1px 6px" class="inline-flex items-center rounded-full bg-green-100 text-green-700 font-bold">✨ Nuevo</span>`;
                } else if (nq <= 0) {
                    badge = `<span style="font-size:10px;padding:1px 6px" class="inline-flex items-center rounded-full bg-amber-100 text-amber-700 font-bold">🍳 En preparación</span>`;
                } else {
                    badge = `<span style="font-size:10px;padding:1px 6px" class="inline-flex items-center rounded-full bg-amber-100 text-amber-700 font-bold">🍳 ${cq} prep.</span> `
                          + `<span style="font-size:10px;padding:1px 6px" class="inline-flex items-center rounded-full bg-green-100 text-green-700 font-bold">+${nq} nuevo</span>`;
                }

                const dotRojo = hayHistorial
                    ? `<span style="width:8px;height:8px;border-radius:50%;background:#ef4444;display:inline-block;flex-shrink:0;margin-top:3px"></span>`
                    : '';

                html += `
                <div class="flex justify-between items-start p-3 hover:bg-gray-50 rounded-md cursor-pointer transition-colors border-b border-gray-100 last:border-0 animation-fade-in">
                    <div class="flex items-start gap-2 flex-1 min-w-0">
                        ${dotRojo}
                        <div class="flex-1 min-w-0">
                            <p class="font-normal text-xs text-gray-800">
                                <span class="font-bold text-indigo-600">${item.cantidad} x</span> ${item.nombre}
                            </p>
                            <div class="flex flex-wrap gap-1 mt-1">${badge}</div>
                            ${item.detalle ? `<p class="text-[10px] text-gray-400 mt-0.5">${item.detalle}</p>` : ''}
                        </div>
                    </div>
                    <div class="flex flex-col items-end gap-1 shrink-0 ml-1">
                        <span class="font-bold text-gray-700 text-xs">S/ ${subtotal.toFixed(2)}</span>
                        <button onclick="eliminarProducto(${item.id}); event.stopPropagation();" class="text-xs text-red-300 hover:text-red-500 p-1 hover:bg-red-50 rounded">
                            🗑️
                        </button>
                    </div>
                </div>`;
            });
        }

        contenedor.innerHTML = html;
        displayTotal.innerText = 'S/ ' + total.toFixed(2);
        if (labelTotal) labelTotal.innerText = hayHistorial ? 'Pendiente' : 'Total';
    }

    // Función Global: Eliminar Producto
    window.eliminarProducto = function (id) {
        const idx = carrito.findIndex(i => i.id == id);
        if (idx > -1) {
            pushUndo();
            // Si hay más de 1, restar. Si no, confirmar y borrar.
            if (carrito[idx].cantidad > 1) {
                carrito[idx].cantidad--;
            } else {
                carrito.splice(idx, 1);
            }
            renderizarTicket();
            autoSave();
        }
    };

    // --- Lógica de Comanda (vendedor) ---

    window.showComandaModal = function() {
        if (carrito.length === 0) {
            alert('Agregá productos a la comanda primero.');
            return;
        }
        if (!currentMesa) {
            alert('Seleccioná una mesa antes de confirmar la comanda.');
            return;
        }
        document.getElementById('comanda-mesa-nombre').innerText = currentMesa.nombre;
        document.getElementById('comanda-items-list').innerHTML = carrito.map(item => {
            const cq  = item.commanded_qty ?? 0;
            const nq  = item.cantidad - cq;
            let badge = '';
            if (cq === 0) {
                badge = `<span style="font-size:10px;padding:1px 6px" class="inline-flex items-center rounded-full bg-green-100 text-green-700 font-bold">✨ Nuevo</span>`;
            } else if (nq <= 0) {
                badge = `<span style="font-size:10px;padding:1px 6px" class="inline-flex items-center rounded-full bg-amber-100 text-amber-700 font-bold">🍳 En preparación</span>`;
            } else {
                badge = `<span style="font-size:10px;padding:1px 6px" class="inline-flex items-center rounded-full bg-amber-100 text-amber-700 font-bold">🍳 ${cq} prep.</span> `
                      + `<span style="font-size:10px;padding:1px 6px" class="inline-flex items-center rounded-full bg-green-100 text-green-700 font-bold">+${nq} nuevo</span>`;
            }
            return `
            <div class="flex justify-between items-center py-2.5 border-b border-gray-100 last:border-0 text-sm gap-2">
                <div class="flex-1 min-w-0">
                    <div><span class="font-bold text-emerald-700">${item.cantidad}×</span> ${item.nombre}</div>
                    <div class="flex flex-wrap gap-1 mt-0.5">${badge}</div>
                </div>
                <span class="text-gray-500 shrink-0">S/ ${(item.precio * item.cantidad).toFixed(2)}</span>
            </div>`;
        }).join('');
        document.getElementById('comanda-modal').classList.remove('hidden');
    }

    window.hideComandaModal = function() {
        document.getElementById('comanda-modal').classList.add('hidden');
    }

    window.confirmarComanda = async function() {
        const btn = document.getElementById('btn-confirm-comanda');
        btn.disabled = true;
        btn.innerHTML = '<span class="animate-spin inline-block mr-1">↻</span> Enviando...';

        try {
            const res = await fetch('<?= base_url('api/save_pedido') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_mesa: currentMesa.id, productos: carrito })
            });
            const data = await res.json();

            if (data.success) {
                carrito.forEach(i => { i.commanded_qty = i.cantidad; });
                renderizarTicket();
                hideComandaModal();
                if (currentMesa) {
                    currentMesa.estado = 'ocupada';
                    updateMesaUI();
                    document.getElementById('mesa-estado-display').innerText = 'OCUPADA';
                }
                mostrarToastComanda();
            } else {
                alert('Error: ' + (data.messages?.error || data.message || 'Error desconocido'));
            }
        } catch(e) {
            console.error(e);
            alert('Error de conexión');
        }

        btn.disabled = false;
        btn.innerHTML = '✓ Enviar a Cocina';
    }

    function mostrarToastComanda() {
        const toast = document.getElementById('toast-comanda');
        if (!toast) return;
        toast.classList.remove('hidden', 'opacity-0');
        toast.classList.add('opacity-100');
        setTimeout(() => {
            toast.classList.add('opacity-0');
            setTimeout(() => toast.classList.add('hidden'), 300);
        }, 3000);
    }

    // Estilo para animación simple
    const style = document.createElement('style');
    style.innerHTML = `
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
        .animation-fade-in { animation: fadeIn 0.2s ease-out; }
    `;
    document.head.appendChild(style);


    // --- Logic de Cancelación ---

    window.showCancelModal = function () {
        if (!currentMesa || carrito.length === 0) {
            // Si no hay mesa o pedido, tal vez solo limpiar local
            return;
        }
        document.getElementById('cancel-modal').classList.remove('hidden');
    }

    window.hideCancelModal = function () {
        document.getElementById('cancel-modal').classList.add('hidden');
    }

    window.confirmCancel = async function () {
        const btn = document.getElementById('btn-confirm-cancel');
        const originalText = btn.innerText;
        btn.disabled = true;
        btn.innerText = 'Cancelando...';

        try {
            const res = await fetch('<?= base_url('api/cancelar_pedido') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id_mesa: currentMesa.id
                })
            });
            const data = await res.json();

            if (data.success) {
                // Limpiar UI
                carrito = [];
                carritoHistorial = [];
                preCuentaMode = false;
                renderizarTicket();
                hideCancelModal();
                _updatePreCuentaUI();

                // Actualizar estado mesa localmente
                if (currentMesa) {
                    currentMesa.estado = 'libre';
                    updateMesaUI();
                }

                // Notificar al overlay si está abierto (para refrescar mapa)
                // O recargar mesas en overlay si es posible.
                // Lo ideal es que el overlay se refresque solo o le avisemos.
                if (window.toggleMesasOverlay && !document.getElementById('mesas-overlay').classList.contains('hidden')) {
                    if (window.loadMesas) window.loadMesas();
                }

            } else {
                alert('Error: ' + (data.messages?.error || data.message));
            }

        } catch (e) {
            console.error(e);
            alert('Error al conectar');
        }

        btn.disabled = false;
        btn.innerText = originalText;
    }

    // --- Pre-Cuenta ---

    function _updatePreCuentaUI() {
        const btnComanda = document.getElementById('btn-comanda-vendor');
        if (btnComanda) {
            if (preCuentaMode) {
                btnComanda.innerHTML = '🧾 Imprimir Pre-Cuenta';
                btnComanda.style.background = '#d97706';
                btnComanda.onclick = showPreCuentaModal;
            } else {
                btnComanda.innerHTML = '🍽️ Confirmar Comanda';
                btnComanda.style.background = '';
                btnComanda.onclick = showComandaModal;
            }
        }
        const banner = document.getElementById('pre-cuenta-banner');
        if (banner) banner.style.display = preCuentaMode ? 'flex' : 'none';
    }

    function _showMesaActionModal() {
        const modal = document.getElementById('mesa-action-modal');
        if (!modal) return;
        document.getElementById('action-mesa-nombre').innerText = currentMesa?.nombre || '';
        modal.classList.remove('hidden');
    }

    window.mesaActionAgregar = function() {
        document.getElementById('mesa-action-modal').classList.add('hidden');
    }

    window.mesaActionPreCuenta = async function() {
        const btn = document.getElementById('btn-action-pre-cuenta');
        btn.disabled = true; btn.innerHTML = '⏳ Procesando...';
        try {
            const res = await fetch('<?= base_url('api/mesas/set_pre_cuenta') ?>', {
                method: 'POST', headers: {'Content-Type':'application/json'},
                body: JSON.stringify({ id_mesa: currentMesa.id })
            });
            const data = await res.json();
            if (data.success) {
                document.getElementById('mesa-action-modal').classList.add('hidden');
                if (currentMesa) currentMesa.estado = 'pre_cuenta';
                preCuentaMode = true;
                undoStack = []; _updateUndoBtn(); // bloquear undo en pre-cuenta
                updateMesaUI();
                _updatePreCuentaUI();
                if (window.loadMesas) window.loadMesas();
            } else {
                alert('Error al activar pre-cuenta');
            }
        } catch(e) { alert('Error de conexión'); }
        btn.disabled = false; btn.innerHTML = '🧾 Solicitar Pre-Cuenta';
    }

    window.showPreCuentaModal = function() {
        const modal = document.getElementById('pre-cuenta-modal');
        if (!modal) return;
        document.getElementById('pre-cuenta-mesa').innerText = currentMesa?.nombre || '';
        const total = carrito.reduce((s, i) => s + i.precio * i.cantidad, 0);
        document.getElementById('pre-cuenta-total').innerText = 'S/ ' + total.toFixed(2);
        document.getElementById('pre-cuenta-items').innerHTML = carrito.map(i =>
            `<div class="flex justify-between py-2 border-b border-gray-100 last:border-0 text-sm">
                <span><span class="font-bold text-gray-700">${i.cantidad}×</span> ${i.nombre}</span>
                <span class="font-bold">S/ ${(i.precio * i.cantidad).toFixed(2)}</span>
            </div>`
        ).join('');
        modal.classList.remove('hidden');
    }

    window.hidePreCuentaModal = function() {
        document.getElementById('pre-cuenta-modal').classList.add('hidden');
    }

    // --- Logic de Cobro ---

    // ── Estado del checkout ────────────────────────────────────────────────────
    let checkoutState = {
        method: 'efectivo', received: 0, total: 0,
        cliente: { tipo: 'boleta', doc: '', nombre: '' },
    };

    let splitPending  = [];  // ítems pendientes de pago [{id,nombre,precio,cantidad}]
    let splitSel      = {};  // {item_id: qty} selección del cliente actual
    let splitPayState = { method:'efectivo', received:0, cliente:{tipo:'boleta',doc:'',nombre:''} };
    let splitIsActive = false;

    window.showCheckoutModal = function() {
        if (!currentMesa || carrito.length === 0) return;
        checkoutState.method   = 'efectivo';
        checkoutState.received = 0;
        checkoutState.total    = carrito.reduce((s, i) => s + i.precio * i.cantidad, 0);
        checkoutState.cliente  = { tipo: 'boleta', doc: '', nombre: '' };
        splitPending  = carrito.map(i => ({...i}));
        splitSel      = {};
        splitPayState = { method:'efectivo', received:0, cliente:{tipo:'boleta',doc:'',nombre:''} };
        document.getElementById('checkout-mesa-name').innerText = currentMesa.nombre || ('Mesa ' + currentMesa.id);
        document.getElementById('cliente-doc').value    = '';
        document.getElementById('cliente-nombre').value = '';
        renderCheckoutItems();
        updateCheckoutUI();
        _showSingleMode();
        document.getElementById('checkout-modal').classList.remove('hidden');
    }

    window.hideCheckoutModal = function() {
        document.getElementById('checkout-modal').classList.add('hidden');
    }

    function _showSingleMode() {
        splitIsActive = false;
        // Re-sync desde carrito actual por si se pagaron ítems en split
        checkoutState.total    = carrito.reduce((s, i) => s + i.precio * i.cantidad, 0);
        checkoutState.received = 0;
        checkoutState.cliente  = { tipo: checkoutState.cliente?.tipo || 'boleta', doc: '', nombre: '' };
        document.getElementById('checkout-single').style.display = 'flex';
        document.getElementById('checkout-split').style.display  = 'none';
        const btn = document.getElementById('btn-split-toggle');
        btn.className = 'flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold border-2 border-gray-300 text-gray-600 active:scale-95 transition-all';
        btn.innerHTML = '✂️ Dividir cuenta';
        renderCheckoutItems();
        updateCheckoutUI();
    }

    function _showSplitMode() {
        splitIsActive = true;
        document.getElementById('checkout-single').style.display = 'none';
        document.getElementById('checkout-split').style.display  = 'flex';
        const btn = document.getElementById('btn-split-toggle');
        btn.className = 'flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold border-2 border-orange-400 text-orange-600 bg-orange-50 active:scale-95 transition-all';
        btn.innerHTML = '← Pago único';
        renderSplitMode();
    }

    window.toggleSplitMode = function() {
        if (splitIsActive) _showSingleMode(); else _showSplitMode();
    }

    // ── Single mode ────────────────────────────────────────────────────────────

    function renderCheckoutItems() {
        const c = document.getElementById('checkout-items-list');
        c.innerHTML = carrito.map(item => `
            <div class="flex justify-between py-2.5 border-b border-gray-100 last:border-0 text-sm">
                <span><span class="font-bold text-gray-700">${item.cantidad}×</span> ${item.nombre}</span>
                <span class="font-bold text-gray-800">S/ ${(item.precio * item.cantidad).toFixed(2)}</span>
            </div>`).join('');
        document.getElementById('checkout-total-display').innerText = 'S/ ' + checkoutState.total.toFixed(2);
    }

    window.setPaymentMethod = function(method) {
        checkoutState.method = method; updateCheckoutUI();
    }
    window.addCash = function(amount) {
        checkoutState.received = amount === 'exact' ? checkoutState.total : checkoutState.received + amount;
        updateCheckoutUI();
    }
    window.clearCash = function() { checkoutState.received = 0; updateCheckoutUI(); }

    function updateCheckoutUI() {
        ['efectivo','tarjeta','monedero'].forEach(m => {
            const el = document.getElementById('tab-' + m); if (!el) return;
            el.className = (m === checkoutState.method)
                ? 'flex-1 py-3 rounded-xl font-bold transition-all active:scale-95 flex flex-col items-center gap-1 text-sm bg-blue-600 text-white shadow-md'
                : 'flex-1 py-3 rounded-xl font-bold transition-all active:scale-95 flex flex-col items-center gap-1 text-sm bg-white text-gray-600';
        });
        const isEfectivo = checkoutState.method === 'efectivo';
        document.getElementById('cash-controls').style.display = isEfectivo ? 'block' : 'none';
        const recv = document.getElementById('amount-received-display');
        const chng = document.getElementById('change-display');
        const btn  = document.getElementById('btn-confirm-checkout');
        if (isEfectivo) {
            recv.innerText = 'S/ ' + checkoutState.received.toFixed(2);
            const diff = checkoutState.received - checkoutState.total;
            if (diff >= 0) {
                chng.innerText = 'S/ ' + diff.toFixed(2);
                chng.className = 'text-3xl font-bold text-green-600';
                btn.disabled = false; btn.classList.remove('opacity-50','cursor-not-allowed');
            } else {
                chng.innerText = 'Faltan S/ ' + Math.abs(diff).toFixed(2);
                chng.className = 'text-3xl font-bold text-red-500';
                btn.disabled = true;  btn.classList.add('opacity-50','cursor-not-allowed');
            }
        } else {
            recv.innerText = 'S/ ' + checkoutState.total.toFixed(2);
            chng.innerText = 'S/ 0.00';
            chng.className = 'text-3xl font-bold text-green-600';
            btn.disabled = false; btn.classList.remove('opacity-50','cursor-not-allowed');
        }
    }

    window.setDocTipo = function(tipo) {
        checkoutState.cliente.tipo = tipo;
        document.getElementById('cliente-doc').placeholder = tipo === 'factura' ? 'RUC (11 dígitos)' : 'DNI (8 dígitos)';
        document.getElementById('btn-doc-boleta').className  = tipo === 'boleta'  ? 'flex-1 py-1.5 text-xs font-bold rounded-lg bg-blue-600 text-white' : 'flex-1 py-1.5 text-xs font-bold rounded-lg bg-white text-gray-500 border border-gray-200';
        document.getElementById('btn-doc-factura').className = tipo === 'factura' ? 'flex-1 py-1.5 text-xs font-bold rounded-lg bg-blue-600 text-white' : 'flex-1 py-1.5 text-xs font-bold rounded-lg bg-white text-gray-500 border border-gray-200';
    }

    window.processPayment = async function() {
        const btn = document.getElementById('btn-confirm-checkout');
        btn.innerHTML = '⏳ Procesando...'; btn.disabled = true;
        const cliente = (checkoutState.cliente.doc || checkoutState.cliente.nombre) ? checkoutState.cliente : null;
        try {
            const res = await fetch('<?= base_url('api/cobrar_pedido') ?>', {
                method: 'POST', headers: {'Content-Type':'application/json'},
                body: JSON.stringify({
                    id_mesa: currentMesa.id, metodo: checkoutState.method,
                    total: checkoutState.total,
                    recibido: checkoutState.method === 'efectivo' ? checkoutState.received : checkoutState.total,
                    items: carrito, liberar_mesa: true, cliente,
                })
            });
            const data = await res.json();
            if (data.success) {
                carrito = []; carritoHistorial = []; renderizarTicket(); hideCheckoutModal();
                if (currentMesa) { currentMesa.estado = 'libre'; updateMesaUI(); }
                if (window.loadMesas) window.loadMesas();
            } else { alert('Error: ' + (data.messages?.error || data.message)); }
        } catch(e) { alert('Error de conexión'); }
        btn.innerHTML = 'CONFIRMAR PAGO'; btn.disabled = false;
    }

    // ── Split mode — cobro secuencial por cliente ────────────────────────────

    function _splitSelTotal() {
        return Object.entries(splitSel).reduce((s, [id, qty]) => {
            const item = splitPending.find(i => i.id == id);
            return s + (item ? item.precio * qty : 0);
        }, 0);
    }

    function renderSplitMode() {
        renderSplitItems();
        renderSplitPayPanel();
    }

    function renderSplitItems() {
        const list     = document.getElementById('split-items-list');
        const selTotal = _splitSelTotal();
        list.innerHTML = (splitPending.length === 0 ? '<p class="p-4 text-center text-gray-400 text-sm">Sin ítems pendientes</p>' :
            splitPending.map(item => {
                const sel        = splitSel[item.id] || 0;
                const allSel     = sel === item.cantidad;
                const selSubtot  = (item.precio * sel).toFixed(2);
                return `<div class="p-3 border-b border-gray-100 last:border-0">
                    <div class="flex justify-between items-start">
                        <span class="text-sm font-semibold text-gray-800 flex-1 pr-2">${item.nombre}</span>
                        <span class="text-xs text-gray-400 shrink-0">S/${item.precio.toFixed(2)} c/u</span>
                    </div>
                    <div class="flex items-center gap-2 mt-2">
                        <button onclick="setSplitQty(${item.id},-1)"
                            class="w-8 h-8 bg-gray-100 rounded-lg font-bold text-gray-600 active:scale-95 text-lg leading-none"
                            ${sel<=0?'disabled style="opacity:0.3"':''}>−</button>
                        <div class="text-center min-w-[56px]">
                            <span class="font-black text-base ${sel>0?'text-blue-600':'text-gray-300'}">${sel}</span>
                            <span class="text-gray-400 text-sm"> / ${item.cantidad}</span>
                        </div>
                        <button onclick="setSplitQty(${item.id},+1)"
                            class="w-8 h-8 bg-gray-100 rounded-lg font-bold text-gray-600 active:scale-95 text-lg leading-none"
                            ${sel>=item.cantidad?'disabled style="opacity:0.3"':''}>+</button>
                        <button onclick="setSplitAll(${item.id})"
                            style="${allSel?'background:#2563EB;color:white':'background:#EFF6FF;color:#2563EB'}"
                            class="ml-auto px-3 py-1.5 text-xs font-bold rounded-lg active:scale-95">
                            ${allSel?'✓ Todo':'Todo'}</button>
                    </div>
                    ${sel>0?`<p class="text-xs text-blue-600 font-semibold mt-1">→ S/${selSubtot}</p>`:''}
                </div>`;
            }).join('')) +
        `<div class="sticky bottom-0 bg-white border-t-2 border-blue-100 p-3 flex justify-between items-center">
            <span class="text-sm font-bold text-gray-600">Total seleccionado</span>
            <span class="text-xl font-black ${selTotal>0?'text-blue-600':'text-gray-300'}">S/${selTotal.toFixed(2)}</span>
        </div>`;
    }

    function renderSplitPayPanel() {
        const panel    = document.getElementById('split-cuentas-panel');
        const selTotal = _splitSelTotal();
        const hasSel   = selTotal > 0;
        const isEf     = splitPayState.method === 'efectivo';
        const recv     = splitPayState.received;
        const diff     = recv - selTotal;
        const vuelto   = Math.max(0, diff);
        const ok       = !isEf || recv >= selTotal;

        const pendingQty = splitPending.reduce((s,i) => s+i.cantidad, 0);
        const selQty     = Object.values(splitSel).reduce((s,q) => s+q, 0);
        const remaining  = pendingQty - selQty;

        const methodBtns = ['efectivo','tarjeta','monedero'].map(m => {
            const icons  = {efectivo:'💵', tarjeta:'💳', monedero:'📱'};
            const labels = {efectivo:'Efectivo', tarjeta:'Tarjeta', monedero:'Monedero'};
            const active = splitPayState.method === m;
            return `<button onclick="setSplitMethod('${m}')"
                style="${active?'background:#2563EB;color:white':'background:white;color:#6B7280;border:1px solid #D1D5DB'}"
                class="flex-1 py-2 text-xs font-bold rounded-xl active:scale-95 flex flex-col items-center gap-0.5">
                ${icons[m]}<span>${labels[m]}</span></button>`;
        }).join('');

        const cashCtrl = isEf ? `
            <div class="flex flex-wrap gap-1.5">
                ${[10,20,50,100].map(a=>`<button onclick="addSplitCash(${a})" class="px-3 py-2 text-xs font-bold bg-white border border-gray-200 rounded-lg active:scale-95">S/${a}</button>`).join('')}
                <button onclick="addSplitCash('exact')" class="px-3 py-2 text-xs font-bold bg-blue-50 text-blue-600 rounded-lg border border-blue-200 active:scale-95">Exacto</button>
                <button onclick="clearSplitCash()" class="px-3 py-2 text-xs font-bold bg-red-50 text-red-500 rounded-lg active:scale-95">⌫</button>
            </div>
            <div class="bg-white rounded-xl p-3 border border-gray-100 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Recibido</span>
                    <span class="font-bold text-gray-800">S/${recv.toFixed(2)}</span>
                </div>
                <div class="flex justify-between mt-1">
                    <span class="text-gray-500">Vuelto</span>
                    <span class="font-bold ${diff>=0?'text-green-600':'text-red-500'}">${diff>=0?'S/'+vuelto.toFixed(2):'Faltan S/'+Math.abs(diff).toFixed(2)}</span>
                </div>
            </div>` : `
            <div class="bg-white rounded-xl p-3 border border-gray-100 text-sm flex justify-between">
                <span class="text-gray-500">Total</span>
                <span class="font-bold text-gray-800">S/${selTotal.toFixed(2)}</span>
            </div>`;

        panel.innerHTML = `<div class="p-4 flex flex-col gap-3">
            <div class="bg-blue-50 rounded-xl p-3">
                <p class="text-xs text-blue-500 font-bold uppercase tracking-wide">Este cobro</p>
                <p class="text-2xl font-black text-blue-700">S/${selTotal.toFixed(2)}</p>
                ${remaining>0
                    ? `<p class="text-xs text-gray-500 mt-0.5">Quedan ${remaining} ítem(s) pendiente(s) después</p>`
                    : hasSel ? `<p class="text-xs text-green-600 font-bold mt-0.5">✓ Último cobro — se liberará la mesa</p>` : ''}
            </div>
            <div class="flex gap-1">
                <button onclick="setSplitDocTipo('boleta')"
                    class="flex-1 py-1.5 text-xs font-bold rounded-lg ${splitPayState.cliente.tipo==='boleta'?'bg-blue-600 text-white':'bg-white text-gray-500 border border-gray-200'}">Boleta</button>
                <button onclick="setSplitDocTipo('factura')"
                    class="flex-1 py-1.5 text-xs font-bold rounded-lg ${splitPayState.cliente.tipo==='factura'?'bg-blue-600 text-white':'bg-white text-gray-500 border border-gray-200'}">Factura</button>
            </div>
            <input type="text" inputmode="numeric"
                placeholder="${splitPayState.cliente.tipo==='factura'?'RUC (11 dígitos)':'DNI (8 dígitos, opcional)'}" maxlength="11"
                class="w-full rounded-lg px-3 py-2 text-sm bg-white border border-gray-200 focus:outline-none focus:border-blue-400"
                value="${splitPayState.cliente.doc}" oninput="setSplitDoc(this.value)">
            <input type="text" placeholder="Nombre (opcional)"
                class="w-full rounded-lg px-3 py-2 text-sm bg-white border border-gray-200 focus:outline-none focus:border-blue-400"
                value="${splitPayState.cliente.nombre}" oninput="setSplitNombre(this.value)">
            <div class="flex gap-1">${methodBtns}</div>
            ${cashCtrl}
            <button onclick="pagarSplit()" id="btn-pagar-split"
                style="${(!hasSel||!ok)?'opacity:0.4;pointer-events:none':''}"
                class="w-full py-3 bg-blue-600 text-white font-bold rounded-xl active:scale-95 text-sm">
                COBRAR S/${selTotal.toFixed(2)}
            </button>
        </div>`;
    }

    window.setSplitQty = function(itemId, delta) {
        const item = splitPending.find(i => i.id == itemId); if (!item) return;
        const curr   = splitSel[itemId] || 0;
        const newQty = Math.max(0, Math.min(item.cantidad, curr + delta));
        if (newQty === 0) delete splitSel[itemId]; else splitSel[itemId] = newQty;
        renderSplitMode();
    }

    window.setSplitAll = function(itemId) {
        const item = splitPending.find(i => i.id == itemId); if (!item) return;
        if ((splitSel[itemId] || 0) === item.cantidad) delete splitSel[itemId];
        else splitSel[itemId] = item.cantidad;
        renderSplitMode();
    }

    window.setSplitMethod = function(m) {
        splitPayState.method = m; splitPayState.received = 0;
        renderSplitPayPanel();
    }

    window.setSplitDocTipo = function(tipo) {
        splitPayState.cliente.tipo = tipo; renderSplitPayPanel();
    }

    window.addSplitCash = function(amount) {
        const total = _splitSelTotal();
        splitPayState.received = amount === 'exact' ? total : splitPayState.received + amount;
        renderSplitPayPanel();
    }

    window.clearSplitCash = function() { splitPayState.received = 0; renderSplitPayPanel(); }

    window.setSplitDoc    = function(val) { splitPayState.cliente.doc    = val; }
    window.setSplitNombre = function(val) { splitPayState.cliente.nombre = val; }

    window.pagarSplit = async function() {
        const selTotal = _splitSelTotal();
        if (selTotal <= 0) return;
        if (splitPayState.method === 'efectivo' && splitPayState.received < selTotal) {
            alert('Monto insuficiente'); return;
        }
        const items = Object.entries(splitSel).flatMap(([id, qty]) => {
            const item = splitPending.find(i => i.id == id);
            return item ? [{...item, cantidad: qty}] : [];
        });
        if (!items.length) return;

        const liberarMesa = !splitPending.some(item => {
            const selQty = splitSel[item.id] || 0;
            return item.cantidad - selQty > 0;
        });

        const btn = document.getElementById('btn-pagar-split');
        if (btn) { btn.disabled = true; btn.innerHTML = '⏳ Procesando...'; }

        const cliente = (splitPayState.cliente.doc || splitPayState.cliente.nombre) ? splitPayState.cliente : null;
        try {
            const res = await fetch('<?= base_url('api/cobrar_pedido') ?>', {
                method: 'POST', headers: {'Content-Type':'application/json'},
                body: JSON.stringify({
                    id_mesa: currentMesa.id, metodo: splitPayState.method,
                    total: selTotal,
                    recibido: splitPayState.method === 'efectivo' ? splitPayState.received : selTotal,
                    items, liberar_mesa: liberarMesa, cliente,
                })
            });
            const data = await res.json();
            if (data.success) {
                // Descontar ítems pagados de splitPending y carrito; registrar en historial
                for (const [id, qty] of Object.entries(splitSel)) {
                    const pidx = splitPending.findIndex(i => i.id == id);
                    if (pidx >= 0) {
                        splitPending[pidx].cantidad -= qty;
                        if (splitPending[pidx].cantidad <= 0) splitPending.splice(pidx, 1);
                    }
                    const cidx = carrito.findIndex(i => i.id == id);
                    if (cidx >= 0) {
                        // Mover al historial visible
                        const hidx = carritoHistorial.findIndex(h => h.id == id);
                        if (hidx >= 0) carritoHistorial[hidx].cantidad += parseInt(qty);
                        else carritoHistorial.push({...carrito[cidx], cantidad: parseInt(qty)});
                        // Reducir pendiente
                        carrito[cidx].cantidad -= qty;
                        if (carrito[cidx].cantidad <= 0) carrito.splice(cidx, 1);
                    }
                }
                splitSel = {};
                splitPayState.received = 0;
                splitPayState.cliente  = { tipo:'boleta', doc:'', nombre:'' };

                if (liberarMesa) {
                    carrito = []; carritoHistorial = []; renderizarTicket(); hideCheckoutModal();
                    if (currentMesa) { currentMesa.estado = 'libre'; updateMesaUI(); }
                    if (window.loadMesas) window.loadMesas();
                } else {
                    renderizarTicket();
                    renderSplitMode();
                }
            } else {
                alert('Error: ' + (data.messages?.error || data.message));
                if (btn) { btn.disabled = false; btn.innerHTML = `COBRAR S/${selTotal.toFixed(2)}`; }
            }
        } catch(e) {
            alert('Error de conexión');
            if (btn) { btn.disabled = false; btn.innerHTML = `COBRAR S/${selTotal.toFixed(2)}`; }
        }
    }

</script>

<?php if (!$_puedeAdmin): ?>
<!-- Modal Confirmar Comanda (solo vendedor/mozo) -->
<div id="comanda-modal" class="fixed inset-0 z-100 hidden" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900 bg-opacity-75 backdrop-blur-sm"></div>
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
            <!-- Header -->
            <div class="bg-emerald-600 p-5 text-white">
                <div class="flex items-center gap-3">
                    <span class="text-4xl">🍽️</span>
                    <div>
                        <h2 class="text-xl font-bold">Confirmar Comanda</h2>
                        <p class="text-emerald-100 text-sm mt-0.5">
                            Mesa: <span id="comanda-mesa-nombre" class="font-bold text-white"></span>
                        </p>
                    </div>
                </div>
            </div>
            <!-- Items -->
            <div class="p-4 max-h-60 overflow-y-auto custom-scrollbar" id="comanda-items-list"></div>
            <!-- Footer -->
            <div class="p-4 bg-gray-50 border-t border-gray-100 flex gap-3">
                <button onclick="hideComandaModal()"
                    class="flex-1 py-3 rounded-xl font-bold text-gray-600 bg-gray-200 hover:bg-gray-300 transition-colors active:scale-95">
                    Cancelar
                </button>
                <button id="btn-confirm-comanda" onclick="confirmarComanda()"
                    class="flex-1 py-3 rounded-xl font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-lg transition-all active:scale-95">
                    ✓ Enviar a Cocina
                </button>
            </div>
        </div>
    </div>
</div>
<!-- Toast confirmación -->
<div id="toast-comanda"
    class="hidden fixed bottom-8 left-1/2 -translate-x-1/2 z-200 bg-emerald-600 text-white px-6 py-3 rounded-full shadow-xl font-bold text-sm transition-opacity duration-300 pointer-events-none">
    ✓ Comanda enviada a cocina
</div>
<?php endif; ?>

<?php if ($_puedeAdmin): ?>
<!-- Checkout Modal -->
<div id="checkout-modal" class="fixed inset-0 hidden" style="z-index:100">
    <div class="fixed inset-0 bg-gray-900 bg-opacity-90 backdrop-blur-sm"></div>
    <div class="fixed inset-0 flex items-center justify-center p-2" style="z-index:101">
        <div class="bg-gray-100 w-full h-full max-w-5xl rounded-2xl shadow-2xl overflow-hidden flex flex-col" style="max-height:96vh">

            <!-- Header -->
            <div class="bg-white px-4 py-3 border-b border-gray-200 flex items-center gap-3 shrink-0">
                <div class="flex-1 min-w-0">
                    <h2 class="text-base font-bold text-gray-800">Cobrar Pedido</h2>
                    <p class="text-xs text-gray-400" id="checkout-mesa-name"></p>
                </div>
                <button onclick="toggleSplitMode()" id="btn-split-toggle"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold border-2 border-gray-300 text-gray-600 active:scale-95 transition-all">
                    ✂️ Dividir cuenta
                </button>
                <button onclick="hideCheckoutModal()" class="w-9 h-9 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-500 text-xl shrink-0">✕</button>
            </div>

            <!-- Body -->
            <div class="flex-1 flex overflow-hidden">

                <!-- ═══ MODO ÚNICO ═══ -->
                <div id="checkout-single" class="flex w-full h-full">

                    <!-- Izquierda: resumen + cliente -->
                    <div class="w-5/12 bg-white border-r border-gray-200 flex flex-col">
                        <div class="flex-1 overflow-y-auto p-4" id="checkout-items-list"></div>
                        <div class="p-4 border-t border-gray-100 space-y-2 shrink-0">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Datos del cliente <span class="text-gray-300 font-normal">(opcional)</span></p>
                            <div class="flex gap-1">
                                <button onclick="setDocTipo('boleta')"  id="btn-doc-boleta"  class="flex-1 py-1.5 text-xs font-bold rounded-lg bg-blue-600 text-white">Boleta</button>
                                <button onclick="setDocTipo('factura')" id="btn-doc-factura" class="flex-1 py-1.5 text-xs font-bold rounded-lg bg-white text-gray-500 border border-gray-200">Factura</button>
                            </div>
                            <input id="cliente-doc" type="text" inputmode="numeric" placeholder="DNI (8 dígitos)" maxlength="11"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                                oninput="checkoutState.cliente.doc=this.value">
                            <input id="cliente-nombre" type="text" placeholder="Nombre / Razón social"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                                oninput="checkoutState.cliente.nombre=this.value">
                            <div class="flex justify-between items-center pt-2 border-t border-gray-100 text-xl font-black text-gray-800">
                                <span>Total</span>
                                <span id="checkout-total-display">S/ 0.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Derecha: método de pago -->
                    <div class="flex-1 flex flex-col">
                        <!-- Tabs método -->
                        <div class="flex gap-2 px-4 pt-4 pb-3 bg-gray-100 shrink-0">
                            <button id="tab-efectivo" onclick="setPaymentMethod('efectivo')" class="flex-1 py-3 rounded-xl font-bold active:scale-95 flex flex-col items-center gap-1 text-sm bg-blue-600 text-white shadow-md">
                                <span class="text-xl">💵</span>Efectivo</button>
                            <button id="tab-tarjeta" onclick="setPaymentMethod('tarjeta')" class="flex-1 py-3 rounded-xl font-bold active:scale-95 flex flex-col items-center gap-1 text-sm bg-white text-gray-600">
                                <span class="text-xl">💳</span>Tarjeta</button>
                            <button id="tab-monedero" onclick="setPaymentMethod('monedero')" class="flex-1 py-3 rounded-xl font-bold active:scale-95 flex flex-col items-center gap-1 text-sm bg-white text-gray-600">
                                <span class="text-xl">📱</span><span>Monedero</span><span class="text-gray-400 font-normal" style="font-size:10px">Yape/Plin/Tunki</span></button>
                        </div>
                        <!-- Efectivo controls -->
                        <div id="cash-controls" class="flex-1 px-4 overflow-y-auto space-y-3 py-2">
                            <div class="bg-white rounded-xl p-4 shadow-sm flex justify-between items-center">
                                <div>
                                    <p class="text-xs text-gray-400 font-bold uppercase">Recibido</p>
                                    <p class="text-3xl font-bold text-gray-800" id="amount-received-display">S/ 0.00</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-400 font-bold uppercase">Vuelto</p>
                                    <p class="text-3xl font-bold text-green-600" id="change-display">S/ 0.00</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-4 gap-2">
                                <button onclick="addCash(10)"      class="bg-white border border-gray-200 text-gray-700 font-bold text-lg py-4 rounded-xl shadow-sm active:scale-95">S/10</button>
                                <button onclick="addCash(20)"      class="bg-white border border-gray-200 text-gray-700 font-bold text-lg py-4 rounded-xl shadow-sm active:scale-95">S/20</button>
                                <button onclick="addCash(50)"      class="bg-white border border-gray-200 text-gray-700 font-bold text-lg py-4 rounded-xl shadow-sm active:scale-95">S/50</button>
                                <button onclick="addCash(100)"     class="bg-white border border-gray-200 text-gray-700 font-bold text-lg py-4 rounded-xl shadow-sm active:scale-95">S/100</button>
                                <button onclick="addCash(200)"     class="bg-white border border-gray-200 text-gray-700 font-bold text-lg py-4 rounded-xl shadow-sm active:scale-95">S/200</button>
                                <button onclick="addCash('exact')" class="col-span-2 bg-blue-100 text-blue-700 font-bold text-lg py-4 rounded-xl active:scale-95">Exacto</button>
                                <button onclick="clearCash()"      class="bg-red-100 text-red-600 font-bold text-xl py-4 rounded-xl active:scale-95">⌫</button>
                            </div>
                        </div>
                        <!-- Footer -->
                        <div class="p-4 bg-white border-t border-gray-200 flex gap-3 shrink-0">
                            <button onclick="hideCheckoutModal()" class="px-5 py-4 rounded-xl font-bold text-gray-500 bg-gray-100 active:scale-95">Cancelar</button>
                            <button id="btn-confirm-checkout" onclick="processPayment()"
                                class="flex-1 py-4 rounded-xl font-bold text-white bg-blue-600 shadow-lg active:scale-95 text-lg">
                                CONFIRMAR PAGO
                            </button>
                        </div>
                    </div>

                </div>

                <!-- ═══ MODO DIVIDIR ═══ -->
                <div id="checkout-split" style="display:none" class="flex w-full h-full">

                    <!-- Izquierda: asignar items -->
                    <div class="w-5/12 bg-white border-r border-gray-200 flex flex-col">
                        <div class="px-4 py-2 bg-gray-50 border-b border-gray-100 shrink-0">
                            <p class="text-xs font-bold text-gray-500 uppercase">Ítems pendientes de pago</p>
                            <p class="text-xs text-gray-400 mt-0.5">Selecciona lo que paga este cliente</p>
                        </div>
                        <div class="flex-1 overflow-y-auto" id="split-items-list"></div>
                    </div>

                    <!-- Derecha: cuentas dinámicas -->
                    <div class="flex-1 overflow-y-auto" id="split-cuentas-panel"></div>

                </div>

            </div>
        </div>
    </div>
</div>
<div id="cancel-modal" class="fixed inset-0 z-100 hidden" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity backdrop-blur-sm"></div>

    <div class="fixed inset-0 z-50 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0 sm:pb-[20vh]">
            <div
                class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <div class="bg-white px-4 pb-4 pt-5  sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                            <h3 class="text-xl font-bold leading-6 text-gray-900" id="modal-title">¿Cancelar Pedido?
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">                                    
                                    <br><br>                                    
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-3">
                    <button type="button" id="btn-confirm-cancel" onclick="confirmCancel()"
                        class="inline-flex w-full justify-center rounded-md bg-red-600 px-6 py-5 text-sm font-bold text-white shadow-sm hover:bg-red-500 sm:flex-1 active:scale-95 transition-transform">
                        SI, CANCELAR PEDIDO
                    </button>
                    <button type="button" onclick="hideCancelModal()"
                        class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-8 py-5 text-sm font-bold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:flex-1 active:scale-95 transition-transform">
                        NO
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal: Acción de mesa con comanda activa (visible para todos los roles) -->
<div id="mesa-action-modal" class="fixed inset-0 hidden" style="z-index:200">
    <div class="fixed inset-0 bg-gray-900/70 backdrop-blur-sm"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4" style="z-index:201">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xs overflow-hidden">
            <div class="bg-indigo-600 p-5 text-white text-center">
                <span class="text-4xl">🍽️</span>
                <h2 class="text-xl font-bold mt-2">Mesa <span id="action-mesa-nombre"></span></h2>
                <p class="text-indigo-200 text-sm mt-0.5">Tiene una comanda en curso</p>
            </div>
            <div class="p-4 space-y-3">
                <button onclick="mesaActionAgregar()"
                    class="w-full py-4 px-4 rounded-xl font-bold text-blue-700 bg-blue-50 border-2 border-blue-200 hover:bg-blue-100 active:scale-95 transition-all text-left flex items-center gap-3">
                    <span class="text-2xl">➕</span>
                    <div>
                        <div class="font-bold">Agregar más productos</div>
                        <div class="text-xs text-blue-400 font-normal">Continuar pidiendo</div>
                    </div>
                </button>
                <button id="btn-action-pre-cuenta" onclick="mesaActionPreCuenta()"
                    class="w-full py-4 px-4 rounded-xl font-bold text-amber-700 bg-amber-50 border-2 border-amber-200 hover:bg-amber-100 active:scale-95 transition-all text-left flex items-center gap-3">
                    <span class="text-2xl">🧾</span>
                    <div>
                        <div class="font-bold">Solicitar Pre-Cuenta</div>
                        <div class="text-xs text-amber-500 font-normal">El cliente está listo para pagar</div>
                    </div>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Pre-Cuenta (visible para todos los roles) -->
<div id="pre-cuenta-modal" class="fixed inset-0 hidden" style="z-index:200">
    <div class="fixed inset-0 bg-gray-900/70 backdrop-blur-sm"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4" style="z-index:201">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
            <div class="p-5 text-white text-center" style="background:#d97706">
                <span class="text-3xl">🧾</span>
                <h2 class="text-lg font-bold mt-1">Pre-Cuenta</h2>
                <p class="text-amber-100 text-sm" id="pre-cuenta-mesa"></p>
            </div>
            <div class="p-4 max-h-64 overflow-y-auto custom-scrollbar" id="pre-cuenta-items"></div>
            <div class="px-4 py-3 border-t border-gray-100 flex justify-between items-center font-bold text-lg">
                <span class="text-gray-700">Total</span>
                <span id="pre-cuenta-total" class="text-gray-900"></span>
            </div>
            <div class="p-4 bg-gray-50 border-t border-gray-100">
                <button onclick="hidePreCuentaModal()"
                    class="w-full py-3 rounded-xl font-bold text-gray-600 bg-gray-200 hover:bg-gray-300 active:scale-95 transition-all">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>