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
        class="w-full bg-emerald-600 text-white font-bold py-3 rounded-lg hover:bg-emerald-700 shadow-lg hover:shadow-emerald-500/30 transition-all active:scale-95 text-base">
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

            html += `
            <div class="flex justify-between items-start p-3 hover:bg-gray-50 rounded-md cursor-pointer group transition-colors border-b border-gray-100 last:border-0 animation-fade-in">
                <div class="flex-1">
                    <p class="font-normal text-xs text-gray-800">
                        <span class="font-bold text-indigo-600">${item.cantidad} x</span> ${item.nombre}
                    </p>
                    ${item.detalle ? `<p class="text-[10px] text-gray-400">${item.detalle}</p>` : ''}
                </div>
                <div class="flex flex-col items-end gap-1">
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
        document.getElementById('comanda-items-list').innerHTML = carrito.map(item => `
            <div class="flex justify-between py-2 border-b border-gray-100 last:border-0 text-sm">
                <span><span class="font-bold text-emerald-700">${item.cantidad}×</span> ${item.nombre}</span>
                <span class="text-gray-500">S/ ${(item.precio * item.cantidad).toFixed(2)}</span>
            </div>
        `).join('');
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

    // Variables de estado
    let checkoutState = {
        method: 'efectivo', // efectivo, tarjeta, monedero
        received: 0,
        total: 0
    };

    window.showCheckoutModal = function() {
        if (!currentMesa || carrito.length === 0) return;

        checkoutState.total = carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
        checkoutState.received = 0;
        checkoutState.method = 'efectivo';
        
        renderCheckoutItems();
        updateCheckoutUI();
        document.getElementById('checkout-modal').classList.remove('hidden');
    }

    window.hideCheckoutModal = function() {
        document.getElementById('checkout-modal').classList.add('hidden');
    }

    function renderCheckoutItems() {
        const container = document.getElementById('checkout-items-list');
        container.innerHTML = carrito.map(item => `
            <div class="flex justify-between py-2 border-b border-gray-100 last:border-0">
                <div>
                    <span class="font-bold text-gray-700">${item.cantidad}x</span> ${item.nombre}
                </div>
                <span class="font-bold">S/ ${(item.precio * item.cantidad).toFixed(2)}</span>
            </div>
        `).join('');
        
        document.getElementById('checkout-total-display').innerText = 'S/ ' + checkoutState.total.toFixed(2);
    }

    window.setPaymentMethod = function(method) {
        checkoutState.method = method;
        updateCheckoutUI();
    }

    window.addCash = function(amount) {
        if (amount === 'exact') {
            checkoutState.received = checkoutState.total;
        } else {
            checkoutState.received += amount;
        }
        updateCheckoutUI();
    }

    window.clearCash = function() {
        checkoutState.received = 0;
        updateCheckoutUI();
    }

    function updateCheckoutUI() {
        // Update Tabs
        ['efectivo', 'tarjeta', 'monedero'].forEach(m => {
            const el = document.getElementById(`tab-${m}`);
            if (m === checkoutState.method) {
                el.classList.add('bg-indigo-600', 'text-white', 'shadow-md');
                el.classList.remove('bg-white', 'text-gray-600', 'hover:bg-gray-50');
            } else {
                el.classList.remove('bg-indigo-600', 'text-white', 'shadow-md');
                el.classList.add('bg-white', 'text-gray-600', 'hover:bg-gray-50');
            }
        });

        // Show/Hide Cash Controls
        const cashControls = document.getElementById('cash-controls');
        if (checkoutState.method === 'efectivo') {
            cashControls.classList.remove('hidden');
        } else {
            cashControls.classList.add('hidden');
        }

        // Update calculations
        const receivedEl = document.getElementById('amount-received-display');
        const changeEl = document.getElementById('change-display');
        const confirmBtn = document.getElementById('btn-confirm-checkout');

        if (checkoutState.method === 'efectivo') {
            receivedEl.innerText = 'S/ ' + checkoutState.received.toFixed(2);
            const change = checkoutState.received - checkoutState.total;
            
            if (change >= 0) {
                changeEl.innerText = 'S/ ' + change.toFixed(2);
                changeEl.classList.remove('text-red-500');
                changeEl.classList.add('text-green-600');
                confirmBtn.disabled = false;
                confirmBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                changeEl.innerText = 'Faltan S/ ' + Math.abs(change).toFixed(2);
                changeEl.classList.add('text-red-500');
                changeEl.classList.remove('text-green-600');
                confirmBtn.disabled = true;
                confirmBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        } else {
            // For card/wallet, always enable
            confirmBtn.disabled = false;
            confirmBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }

    window.processPayment = async function() {
        const btn = document.getElementById('btn-confirm-checkout');
        btn.innerHTML = '<span class="animate-spin inline-block mr-2">↻</span> Procesando...';
        btn.disabled = true;

        try {
            const res = await fetch('<?= base_url('api/cobrar_pedido') ?>', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    id_mesa: currentMesa.id,
                    metodo: checkoutState.method,
                    total: checkoutState.total,
                    recibido: checkoutState.method === 'efectivo' ? checkoutState.received : checkoutState.total,
                    items: carrito
                })
            });
            const data = await res.json();

            if (data.success) {
                // Success feedback
                alert('Venta Completada!'); // Replace with better UI later
                
                // Clear everything
                carrito = [];
                renderizarTicket();
                hideCheckoutModal();
                if (currentMesa) {
                    currentMesa.estado = 'libre';
                    updateMesaUI();
                }
                if (window.loadMesas) window.loadMesas();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (e) {
            console.error(e);
            alert('Error de conexión');
        }
        
        btn.innerHTML = 'CONFIRMAR PAGO';
        btn.disabled = false;
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
                    class="flex-1 py-3 rounded-xl font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-lg transition-all active:scale-95">
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
<div id="checkout-modal" class="fixed inset-0 z-100 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900 bg-opacity-90 backdrop-blur-sm transition-opacity"></div>

    <div class="fixed inset-0 z-50 w-screen overflow-hidden flex items-center justify-center p-4">
        <div class="bg-gray-100 w-full h-full max-w-6xl rounded-2xl shadow-2xl overflow-hidden flex flex-col md:flex-row">
            
            <!-- Left Side: Order Summary -->
            <div class="w-full md:w-1/3 bg-white border-r border-gray-200 flex flex-col h-full">
                <div class="p-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="font-bold text-gray-800 text-lg">Resumen de Orden</h3>
                    <p class="text-sm text-gray-500" id="checkout-mesa-name">Mesa Actual</p>
                </div>
                <div class="flex-1 overflow-y-auto p-4 space-y-2" id="checkout-items-list">
                    <!-- Items go here -->
                </div>
                <div class="p-6 bg-gray-50 border-t border-gray-200">
                    <div class="flex justify-between items-center text-2xl font-black text-gray-800">
                        <span>Total</span>
                        <span id="checkout-total-display">S/ 0.00</span>
                    </div>
                </div>
            </div>

            <!-- Right Side: Payment Controls -->
            <div class="w-full md:w-2/3 bg-gray-100 flex flex-col h-full">
                <!-- Header -->
                <div class="p-4 flex justify-between items-center">
                    <h2 class="text-xl font-bold text-gray-700">Método de Pago</h2>
                    <button onclick="hideCheckoutModal()" class="text-gray-400 hover:text-gray-600 p-2">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Tabs -->
                <div class="flex px-6 gap-4 mb-6">
                    <button id="tab-efectivo" onclick="setPaymentMethod('efectivo')" class="flex-1 py-4 rounded-xl font-bold text-lg transition-all transform active:scale-95 flex flex-col items-center gap-2 bg-indigo-600 text-white shadow-md">
                        <span>💵</span> Efectivo
                    </button>
                    <button id="tab-tarjeta" onclick="setPaymentMethod('tarjeta')" class="flex-1 py-4 rounded-xl font-bold text-lg transition-all transform active:scale-95 flex flex-col items-center gap-2 bg-white text-gray-600 hover:bg-gray-50">
                        <span>💳</span> Tarjeta
                    </button>
                    <button id="tab-monedero" onclick="setPaymentMethod('monedero')" class="flex-1 py-4 rounded-xl font-bold text-lg transition-all transform active:scale-95 flex flex-col items-center gap-2 bg-white text-gray-600 hover:bg-gray-50">
                        <span>📱</span> Digital
                    </button>
                </div>

                <!-- Main Content Area -->
                <div class="flex-1 px-6 overflow-y-auto">
                    
                    <!-- Cash Controls -->
                    <div id="cash-controls" class="space-y-6">
                        
                        <!-- Display Ranges -->
                        <div class="bg-white rounded-xl p-4 shadow-sm flex justify-between items-center">
                            <div>
                                <p class="text-sm text-gray-500 font-bold uppercase">Monto Recibido</p>
                                <p class="text-3xl font-bold text-gray-800" id="amount-received-display">S/ 0.00</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500 font-bold uppercase">Vuelto</p>
                                <p class="text-3xl font-bold text-green-600" id="change-display">S/ 0.00</p>
                            </div>
                        </div>

                        <!-- Quick Money Buttons -->
                        <div class="grid grid-cols-4 gap-3">
                            <button onclick="addCash(10)" class="bg-white border border-gray-200 text-gray-700 font-bold text-xl py-4 rounded-lg shadow-sm hover:bg-indigo-50 hover:border-indigo-200 active:scale-95 transition-all">S/ 10</button>
                            <button onclick="addCash(20)" class="bg-white border border-gray-200 text-gray-700 font-bold text-xl py-4 rounded-lg shadow-sm hover:bg-indigo-50 hover:border-indigo-200 active:scale-95 transition-all">S/ 20</button>
                            <button onclick="addCash(50)" class="bg-white border border-gray-200 text-gray-700 font-bold text-xl py-4 rounded-lg shadow-sm hover:bg-indigo-50 hover:border-indigo-200 active:scale-95 transition-all">S/ 50</button>
                            <button onclick="addCash(100)" class="bg-white border border-gray-200 text-gray-700 font-bold text-xl py-4 rounded-lg shadow-sm hover:bg-indigo-50 hover:border-indigo-200 active:scale-95 transition-all">S/ 100</button>
                            <button onclick="addCash(200)" class="bg-white border border-gray-200 text-gray-700 font-bold text-xl py-4 rounded-lg shadow-sm hover:bg-indigo-50 hover:border-indigo-200 active:scale-95 transition-all">S/ 200</button>
                            
                            <button onclick="addCash('exact')" class="col-span-2 bg-indigo-100 text-indigo-700 font-bold text-xl py-4 rounded-lg shadow-sm hover:bg-indigo-200 active:scale-95 transition-all">Exacto</button>
                            
                            <button onclick="clearCash()" class="bg-red-100 text-red-600 font-bold text-xl py-4 rounded-lg shadow-sm hover:bg-red-200 active:scale-95 transition-all">
                                ⌫
                            </button>
                        </div>
                    </div>

                    <!-- Placeholder for other methods -->
                    <div id="other-method-msg" class="hidden text-center text-gray-500 mt-20">
                        <p>Procesar pago directamente con POS o App</p>
                    </div>

                </div>

                <!-- Footer Actions -->
                <div class="p-6 bg-white border-t border-gray-200 flex gap-4">
                    <button onclick="hideCheckoutModal()" class="w-1/3 py-4 rounded-xl font-bold text-gray-500 bg-gray-100 hover:bg-gray-200 transition-colors">
                        Cancelar
                    </button>
                    <button id="btn-confirm-checkout" onclick="processPayment()" class="w-2/3 py-4 rounded-xl font-bold text-white bg-indigo-600 shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:shadow-indigo-300 transition-all active:scale-95">
                        CONFIRMAR PAGO
                    </button>
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