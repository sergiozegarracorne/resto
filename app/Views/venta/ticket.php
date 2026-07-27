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
        <span>Total</span>
        <span id="total-ticket">S/ 0.00</span>
    </div>

    <?php if ($_puedeAdmin): ?>
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
    <button onclick="showComandaModal()"
        class="w-full bg-blue-600 text-white font-bold py-3 rounded-lg hover:bg-blue-700 shadow-lg transition-all active:scale-95 text-base">
        🍽️ Confirmar Comanda
    </button>
    <?php endif; ?>
</div>

<script>
    // Estado del carrito y mesa
    let carrito = [];
    let currentMesa = null;
    let saveTimeout = null;
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
        const container = document.getElementById('lista-ticket');
        // Show loading indicator usually, but keep it simple

        try {
            const res = await fetch('<?= base_url('api/get_mesa_pedido') ?>/' + idMesa + '?t=' + Date.now());
            const data = await res.json();

            if (data.success) {
                carrito = data.items.map(i => ({
                    id: i.id,
                    nombre: i.nombre,
                    precio: parseFloat(i.precio),
                    cantidad: parseInt(i.cantidad),
                    commanded_qty: parseInt(i.cantidad),
                    detalle: ''
                }));
                if (currentMesa) currentMesa.estado = 'ocupada'; // Optimistic update
            } else {
                carrito = [];
                if (currentMesa) currentMesa.estado = 'libre';
            }
            updateMesaUI(); // Refresh status
        } catch (e) {
            console.error('Error loading order', e);
            carrito = [];
        }
        renderizarTicket();
    }

    // Función Global: Agregar Producto (Modificada con AutoSave)
    window.agregarProducto = function (id, nombre, precio) {
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
        const contenedor = document.getElementById('lista-ticket');
        const displayTotal = document.getElementById('total-ticket');

        if (carrito.length === 0) {
            contenedor.innerHTML = '<p class="text-center text-gray-400 text-sm mt-10">Orden vacía</p>';
            displayTotal.innerText = 'S/ 0.00';
            return;
        }

        let html = '';
        let total = 0;

        carrito.forEach((item, index) => {
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

            html += `
            <div class="flex justify-between items-start p-3 hover:bg-gray-50 rounded-md cursor-pointer group transition-colors border-b border-gray-100 last:border-0 animation-fade-in">
                <div class="flex-1 min-w-0">
                    <p class="font-normal text-xs text-gray-800">
                        <span class="font-bold text-indigo-600">${item.cantidad} x</span> ${item.nombre}
                    </p>
                    <div class="flex flex-wrap gap-1 mt-1">${badge}</div>
                    ${item.detalle ? `<p class="text-[10px] text-gray-400 mt-0.5">${item.detalle}</p>` : ''}
                </div>
                <div class="flex flex-col items-end gap-1 shrink-0 ml-1">
                    <span class="font-bold text-gray-700 text-xs">S/ ${subtotal.toFixed(2)}</span>
                    <button onclick="eliminarProducto(${item.id}); event.stopPropagation();" class="text-xs text-red-300 hover:text-red-500 p-1 hover:bg-red-50 rounded">
                        🗑️
                    </button>
                </div>
            </div>
            `;
        });

        contenedor.innerHTML = html;
        displayTotal.innerText = 'S/ ' + total.toFixed(2);
    }

    // Función Global: Eliminar Producto
    window.eliminarProducto = function (id) {
        const idx = carrito.findIndex(i => i.id == id);
        if (idx > -1) {
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
                renderizarTicket();
                hideCancelModal();

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

    // --- Logic de Cobro ---

    // ── Estado del checkout ────────────────────────────────────────────────────
    let checkoutState = {
        method: 'efectivo', received: 0, total: 0,
        cliente: { tipo: 'boleta', doc: '', nombre: '' },
    };

    const CUENTA_PALETTE = [
        { bg:'#EFF6FF', border:'#3B82F6', text:'#1D4ED8', btn:'#2563EB' },
        { bg:'#FFF7ED', border:'#F97316', text:'#C2410C', btn:'#EA580C' },
        { bg:'#F5F3FF', border:'#8B5CF6', text:'#6D28D9', btn:'#7C3AED' },
        { bg:'#F0FDF4', border:'#22C55E', text:'#15803D', btn:'#16A34A' },
        { bg:'#FFF1F2', border:'#F43F5E', text:'#BE123C', btn:'#E11D48' },
        { bg:'#F0FDFA', border:'#14B8A6', text:'#0F766E', btn:'#0D9488' },
    ];
    let splitCuentas    = [];
    let splitNextId     = 1;
    let splitAssignment = {};
    let splitIsActive   = false;

    window.showCheckoutModal = function() {
        if (!currentMesa || carrito.length === 0) return;
        checkoutState.method   = 'efectivo';
        checkoutState.received = 0;
        checkoutState.total    = carrito.reduce((s, i) => s + i.precio * i.cantidad, 0);
        checkoutState.cliente  = { tipo: 'boleta', doc: '', nombre: '' };
        splitNextId    = 1;
        splitCuentas   = [
            { id: splitNextId++, method:'efectivo', received:0, cliente:{tipo:'boleta',doc:'',nombre:''}, paid:false },
            { id: splitNextId++, method:'efectivo', received:0, cliente:{tipo:'boleta',doc:'',nombre:''}, paid:false },
        ];
        splitAssignment = {};
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
        document.getElementById('checkout-single').style.display = 'flex';
        document.getElementById('checkout-split').style.display  = 'none';
        const btn = document.getElementById('btn-split-toggle');
        btn.className = 'flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold border-2 border-gray-300 text-gray-600 active:scale-95 transition-all';
        btn.innerHTML = '✂️ Dividir cuenta';
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
                carrito = []; renderizarTicket(); hideCheckoutModal();
                if (currentMesa) { currentMesa.estado = 'libre'; updateMesaUI(); }
                if (window.loadMesas) window.loadMesas();
            } else { alert('Error: ' + (data.messages?.error || data.message)); }
        } catch(e) { alert('Error de conexión'); }
        btn.innerHTML = 'CONFIRMAR PAGO'; btn.disabled = false;
    }

    // ── Split mode — N cuentas dinámicas ─────────────────────────────────────

    function _cuentaById(id)   { return splitCuentas.find(c => c.id === id); }
    function _cuentaIdx(id)    { return splitCuentas.findIndex(c => c.id === id); }

    function _unassignedQty(item) {
        const assigned = Object.values(splitAssignment[item.id] || {}).reduce((s,q) => s+q, 0);
        return item.cantidad - assigned;
    }

    function _cuentaItems(cid) {
        return carrito.flatMap(item => {
            const q = (splitAssignment[item.id] || {})[cid] || 0;
            return q > 0 ? [{...item, cantidad: q}] : [];
        });
    }

    function _cuentaTotal(cid) {
        return _cuentaItems(cid).reduce((s,i) => s + i.precio * i.cantidad, 0);
    }

    function renderSplitMode() {
        renderSplitItems();
        renderCuentasPanel();
    }

    function renderSplitItems() {
        const list = document.getElementById('split-items-list');
        list.innerHTML = carrito.map(item => {
            const unassigned = _unassignedQty(item);
            let ctrl = '';
            if (item.cantidad === 1) {
                const btns = splitCuentas.map((c, idx) => {
                    const pal   = CUENTA_PALETTE[idx % CUENTA_PALETTE.length];
                    const owned = ((splitAssignment[item.id] || {})[c.id] || 0) === 1;
                    return `<button onclick="toggleItemCuenta(${item.id},${c.id})"
                        style="${owned ? 'background:'+pal.btn+';color:white;border-color:'+pal.btn : 'background:white;color:#6B7280;border-color:#D1D5DB'}"
                        class="px-3 py-2 text-xs font-bold rounded-xl border-2 active:scale-95 transition-all">C${idx+1}</button>`;
                }).join('');
                ctrl = `<div class="flex gap-1.5 flex-wrap mt-2">${btns}</div>`;
            } else {
                const rows = splitCuentas.map((c, idx) => {
                    const pal = CUENTA_PALETTE[idx % CUENTA_PALETTE.length];
                    const qty = (splitAssignment[item.id] || {})[c.id] || 0;
                    return `<div class="flex items-center gap-1.5 mt-1">
                        <span style="background:${pal.btn};color:white" class="text-xs font-bold rounded-md px-2 py-0.5 w-7 text-center shrink-0">C${idx+1}</span>
                        <button onclick="adjustItemCuenta(${item.id},${c.id},-1)" class="w-7 h-7 bg-gray-100 rounded-lg font-bold text-gray-700 active:scale-95 text-lg leading-none">−</button>
                        <span class="w-6 text-center font-black text-sm" style="color:${pal.text}">${qty}</span>
                        <button onclick="adjustItemCuenta(${item.id},${c.id},+1)" class="w-7 h-7 bg-gray-100 rounded-lg font-bold text-gray-700 active:scale-95 text-lg leading-none">+</button>
                    </div>`;
                }).join('');
                ctrl = `<div class="mt-1">${rows}</div>`;
            }
            const status = unassigned > 0
                ? `<p class="text-xs text-red-400 mt-1.5 font-semibold">⚠ Sin asignar: ${unassigned}</p>`
                : `<p class="text-xs text-green-600 mt-1.5">✓ Listo</p>`;
            return `<div class="p-3 border-b border-gray-100 last:border-0">
                <div class="flex justify-between text-sm font-semibold text-gray-800">
                    <span>${item.cantidad}× ${item.nombre}</span>
                    <span class="text-gray-400 text-xs ml-2 shrink-0">S/${(item.precio*item.cantidad).toFixed(2)}</span>
                </div>${ctrl}${status}</div>`;
        }).join('');
    }

    function renderCuentasPanel() {
        const panel = document.getElementById('split-cuentas-panel');
        const canRemove = splitCuentas.length > 2;
        panel.innerHTML = splitCuentas.map((c, idx) => {
            const pal   = CUENTA_PALETTE[idx % CUENTA_PALETTE.length];
            const items = _cuentaItems(c.id);
            const total = items.reduce((s,i) => s + i.precio*i.cantidad, 0);
            c.total     = total;
            const isEf  = c.method === 'efectivo';
            const canPay = items.length > 0 && !c.paid;
            const ok    = !isEf || c.received >= total;

            const itemsSummary = items.length
                ? items.map(i => `<span>${i.cantidad}× ${i.nombre} <b>S/${(i.precio*i.cantidad).toFixed(2)}</b></span>`).join('<br>')
                : '<span style="color:#9CA3AF">Sin ítems asignados</span>';

            const methods = ['efectivo','tarjeta','monedero'].map(m => {
                const icons  = { efectivo:'💵', tarjeta:'💳', monedero:'📱' };
                const labels = { efectivo:'Efectivo', tarjeta:'Tarjeta', monedero:'Monedero' };
                return `<button onclick="setCuentaMethod(${c.id},'${m}')"
                    style="${c.method===m ? 'background:'+pal.btn+';color:white' : 'background:white;color:#6B7280;border:1px solid #D1D5DB'}"
                    class="flex-1 py-1.5 text-xs font-bold rounded-xl active:scale-95 flex flex-col items-center">${icons[m]} ${labels[m]}</button>`;
            }).join('');

            const cashCtrl = isEf ? `
                <div class="flex flex-wrap gap-1">
                    ${[10,20,50,100].map(a => `<button onclick="addCuentaCash(${c.id},${a})" class="px-2 py-1.5 text-xs font-bold bg-white border border-gray-200 rounded-lg active:scale-95">S/${a}</button>`).join('')}
                    <button onclick="addCuentaCash(${c.id},'exact')" style="color:${pal.text};background:${pal.bg}" class="px-2 py-1.5 text-xs font-bold rounded-lg border active:scale-95">Exacto</button>
                    <button onclick="clearCuentaCash(${c.id})" class="px-2 py-1.5 text-xs font-bold bg-red-50 text-red-500 rounded-lg active:scale-95">⌫</button>
                </div>
                <p class="text-xs font-semibold" style="color:${pal.text}">
                    Recibido: <b>S/${c.received.toFixed(2)}</b> | Vuelto:
                    <b style="color:${c.received>=total?'#15803D':'#DC2626'}">S/${Math.max(0,c.received-total).toFixed(2)}</b>
                </p>` : `<p class="text-xs font-semibold" style="color:${pal.text}">Total: <b>S/${total.toFixed(2)}</b></p>`;

            const removeBtn = (canRemove && !c.paid)
                ? `<button onclick="removeCuenta(${c.id})" class="text-gray-300 hover:text-red-400 text-lg leading-none shrink-0 ml-1">✕</button>`
                : '';

            const payBtn = c.paid
                ? `<div style="background:${pal.btn};opacity:0.7" class="w-full py-2.5 rounded-xl text-white text-sm font-bold text-center">✅ Cuenta ${idx+1} cobrada</div>`
                : `<button id="btn-pagar-c-${c.id}" onclick="pagarCuentaN(${c.id})"
                    style="background:${pal.btn};${(!canPay||!ok)?'opacity:0.4;pointer-events:none':''}"
                    class="w-full py-2.5 rounded-xl text-white text-sm font-bold active:scale-95">
                    COBRAR CUENTA ${idx+1}
                </button>`;

            return `<div style="background:${pal.bg};border-bottom:2px solid ${pal.border}50" class="p-3 flex flex-col gap-2">
                <div class="flex items-center justify-between gap-2">
                    <h3 style="color:${pal.text}" class="font-black flex items-center gap-2 text-sm">
                        <span style="background:${pal.btn}" class="w-6 h-6 text-white rounded-full flex items-center justify-center text-xs font-black shrink-0">${idx+1}</span>
                        Cuenta ${idx+1}${removeBtn}
                    </h3>
                    <span style="color:${pal.text}" class="text-base font-black shrink-0">S/${total.toFixed(2)}</span>
                </div>
                <div class="text-xs bg-white rounded-lg px-2 py-1.5 leading-5" style="color:#374151;border:1px solid ${pal.border}40">${itemsSummary}</div>
                <div class="flex gap-1">
                    <button onclick="setCuentaDocTipo(${c.id},'boleta')"
                        style="${c.cliente.tipo==='boleta' ? 'background:'+pal.btn+';color:white' : 'background:white;color:#6B7280;border:1px solid #D1D5DB'}"
                        class="flex-1 py-1 text-xs font-bold rounded-lg">Boleta</button>
                    <button onclick="setCuentaDocTipo(${c.id},'factura')"
                        style="${c.cliente.tipo==='factura' ? 'background:'+pal.btn+';color:white' : 'background:white;color:#6B7280;border:1px solid #D1D5DB'}"
                        class="flex-1 py-1 text-xs font-bold rounded-lg">Factura</button>
                </div>
                <input type="text" inputmode="numeric" placeholder="${c.cliente.tipo==='factura'?'RUC (11 dígitos)':'DNI (8 dígitos)'}" maxlength="11"
                    style="border:1px solid ${pal.border}60" value="${c.cliente.doc}"
                    class="w-full rounded-lg px-2 py-1.5 text-sm bg-white focus:outline-none"
                    oninput="setCuentaDoc(${c.id},this.value)">
                <input type="text" placeholder="Nombre (opcional)" value="${c.cliente.nombre}"
                    style="border:1px solid ${pal.border}60"
                    class="w-full rounded-lg px-2 py-1.5 text-sm bg-white focus:outline-none"
                    oninput="setCuentaNombre(${c.id},this.value)">
                <div class="flex gap-1">${methods}</div>
                ${cashCtrl}
                ${payBtn}
            </div>`;
        }).join('') + (splitCuentas.length < 6 ? `
            <div class="p-3">
                <button onclick="addCuenta()" class="w-full py-3 border-2 border-dashed border-gray-300 rounded-xl text-gray-500 font-bold text-sm active:scale-95">
                    ➕ Agregar cuenta
                </button>
            </div>` : '');
    }

    window.addCuenta = function() {
        if (splitCuentas.length >= 6) return;
        splitCuentas.push({ id: splitNextId++, method:'efectivo', received:0,
                            cliente:{tipo:'boleta',doc:'',nombre:''}, paid:false });
        renderSplitMode();
    }

    window.removeCuenta = function(id) {
        const hasItems = carrito.some(item => ((splitAssignment[item.id] || {})[id] || 0) > 0);
        if (hasItems) { alert('Retira los ítems de esta cuenta antes de eliminarla'); return; }
        splitCuentas = splitCuentas.filter(c => c.id !== id);
        renderSplitMode();
    }

    window.toggleItemCuenta = function(itemId, cuentaId) {
        if (!splitAssignment[itemId]) splitAssignment[itemId] = {};
        const curr = splitAssignment[itemId][cuentaId] || 0;
        splitAssignment[itemId] = {};
        if (!curr) splitAssignment[itemId][cuentaId] = 1;
        renderSplitMode();
    }

    window.adjustItemCuenta = function(itemId, cuentaId, delta) {
        const item = carrito.find(i => i.id == itemId); if (!item) return;
        if (!splitAssignment[itemId]) splitAssignment[itemId] = {};
        const curr = splitAssignment[itemId][cuentaId] || 0;
        if (delta > 0 && _unassignedQty(item) <= 0) return;
        const newQty = Math.max(0, curr + delta);
        if (newQty === 0) delete splitAssignment[itemId][cuentaId];
        else splitAssignment[itemId][cuentaId] = newQty;
        renderSplitMode();
    }

    window.setCuentaMethod = function(id, m) {
        const c = _cuentaById(id); if (!c) return;
        c.method = m; renderSplitMode();
    }

    window.addCuentaCash = function(id, amount) {
        const c = _cuentaById(id); if (!c) return;
        c.received = amount === 'exact' ? _cuentaTotal(id) : c.received + amount;
        renderSplitMode();
    }

    window.clearCuentaCash = function(id) {
        const c = _cuentaById(id); if (!c) return;
        c.received = 0; renderSplitMode();
    }

    window.setCuentaDocTipo = function(id, tipo) {
        const c = _cuentaById(id); if (!c) return;
        c.cliente.tipo = tipo; renderSplitMode();
    }

    window.setCuentaDoc    = function(id, val) { const c = _cuentaById(id); if (c) c.cliente.doc    = val; }
    window.setCuentaNombre = function(id, val) { const c = _cuentaById(id); if (c) c.cliente.nombre = val; }

    window.pagarCuentaN = async function(id) {
        const c = _cuentaById(id); if (!c || c.paid) return;
        const items = _cuentaItems(id);
        if (!items.length) return;
        const total = _cuentaTotal(id);
        if (c.method === 'efectivo' && c.received < total) { alert('Monto insuficiente'); return; }

        // Solo liberar si TODOS los ítems quedan cubiertos por cuentas pagadas (incluyendo la actual)
        const liberarMesa = carrito.every(item => {
            const assigned = splitAssignment[item.id] || {};
            let covered = 0;
            for (const [cid, qty] of Object.entries(assigned)) {
                const cuenta = _cuentaById(parseInt(cid));
                if (cuenta && (cuenta.paid || parseInt(cid) === id)) covered += qty;
            }
            return covered >= item.cantidad;
        });

        const btn = document.getElementById(`btn-pagar-c-${id}`);
        if (btn) { btn.disabled = true; btn.innerHTML = '⏳ Procesando...'; }

        const cliente = (c.cliente.doc || c.cliente.nombre) ? c.cliente : null;
        try {
            const res = await fetch('<?= base_url('api/cobrar_pedido') ?>', {
                method: 'POST', headers: {'Content-Type':'application/json'},
                body: JSON.stringify({
                    id_mesa: currentMesa.id, metodo: c.method,
                    total, recibido: c.method === 'efectivo' ? c.received : total,
                    items, liberar_mesa: liberarMesa, cliente,
                })
            });
            const data = await res.json();
            if (data.success) {
                // Eliminar del carrito los ítems que acaban de pagarse
                for (const paidItem of items) {
                    const cartItem = carrito.find(i => i.id === paidItem.id);
                    if (cartItem) {
                        cartItem.cantidad -= paidItem.cantidad;
                        if (cartItem.cantidad <= 0) {
                            carrito = carrito.filter(i => i.id !== paidItem.id);
                            delete splitAssignment[paidItem.id];
                        } else {
                            if (splitAssignment[paidItem.id]) delete splitAssignment[paidItem.id][id];
                        }
                    }
                }
                c.paid = true;
                if (liberarMesa) {
                    carrito = []; renderizarTicket(); hideCheckoutModal();
                    if (currentMesa) { currentMesa.estado = 'libre'; updateMesaUI(); }
                    if (window.loadMesas) window.loadMesas();
                } else {
                    renderizarTicket();
                    renderSplitMode();
                }
            } else {
                alert('Error: ' + (data.messages?.error || data.message));
                if (btn) { btn.disabled = false; btn.innerHTML = `COBRAR CUENTA ${_cuentaIdx(id)+1}`; }
            }
        } catch(e) {
            alert('Error de conexión');
            if (btn) { btn.disabled = false; btn.innerHTML = `COBRAR CUENTA ${_cuentaIdx(id)+1}`; }
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
                            <p class="text-xs font-bold text-gray-500 uppercase">Asignar ítems a cada cuenta</p>
                            <p class="text-xs text-gray-400 mt-0.5">Toca C1 / C2 / C3... para asignar</p>
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