<!-- Encabezado Ticket -->
<div class="p-3 border-b border-gray-200 bg-gray-50 space-y-2">
    <!-- Fila 1: Título y Reloj (Neon Style) -->
    <div class="flex justify-center items-center">
        <?= view('components/clock') ?>
    </div>

    <!-- Fila 2: Mesero y Estado -->
    <div class="flex justify-between items-center text-xs">
        <div class="flex items-center gap-1 text-gray-600">
            <span class="text-lg">💁</span>
            <span class="font-bold"><?= session('usuario_turno')['nombre'] ?? 'Sin Asignar' ?></span>
        </div>
        <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium text-[10px]">En Proceso</span>
    </div>

    <!-- Fila 2.5: Mesa Activa -->
    <div id="mesa-info-display"
        class="hidden flex justify-between items-center bg-indigo-50 px-3 py-2 rounded-lg border border-indigo-100 animate-pulse-once">
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
<div class="p-4 bg-gray-50 border-t border-gray-200">
    <div class="flex justify-between items-center mb-4 text-xl font-bold text-gray-800">
        <span>Total</span>
        <span id="total-ticket">S/ 0.00</span>
    </div>

    <div class="grid grid-cols-2 gap-2">
        <button class="bg-red-50 text-red-600 font-bold py-3 rounded-lg hover:bg-red-100 transition-colors">
            Cancelar
        </button>
        <button
            class="bg-indigo-600 text-white font-bold py-3 rounded-lg hover:bg-indigo-700 shadow-lg hover:shadow-indigo-500/30 transition-all active:scale-95">
            Cobrar
        </button>
    </div>
</div>

<script>
    // Estado del carrito y mesa
    let carrito = [];
    let currentMesa = null;
    let saveTimeout = null;

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
    };

    function autoSave() {
        if (!currentMesa) return;

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

    // Estilo para animación simple
    const style = document.createElement('style');
    style.innerHTML = `
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
        .animation-fade-in { animation: fadeIn 0.2s ease-out; }
    `;
    document.head.appendChild(style);
</script>