<!-- Encabezado Ticket -->
<div class="p-4 border-b border-gray-200 bg-gray-50">
    <h2 class="text-lg font-bold text-gray-700 flex items-center justify-between">
        <span>Orden #001</span>
        <span class="text-xs font-normal bg-green-100 text-green-700 px-2 py-1 rounded-full">En Proceso</span>
    </h2>
    <p class="text-sm text-gray-500 mt-1">Mesero: <span class="font-medium text-gray-700">Juan Pérez</span></p>
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
    // Estado del carrito
    let carrito = [];

    // Función Global: Agregar Producto
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
                detalle: '' // Futuro: opciones
            });
        }

        renderizarTicket();
    };

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
            <div class="flex justify-between items-start p-1 hover:bg-gray-50 rounded-md cursor-pointer group transition-colors border-b border-gray-100 last:border-0 animation-fade-in">
                <div class="flex-1">
                    <p class="font-normal text-xs text-gray-800">
                        <span class="font-bold text-indigo-600">${item.cantidad}x</span> ${item.nombre}
                    </p>
                    ${item.detalle ? `<p class="text-[10px] text-gray-400">${item.detalle}</p>` : ''}
                </div>
                <span class="font-bold text-gray-700 text-xs">S/ ${subtotal.toFixed(2)}</span>
            </div>
            `;
        });

        contenedor.innerHTML = html;
        displayTotal.innerText = 'S/ ' + total.toFixed(2);

        // Auto scroll al final si es nuevo item
        // contenedor.scrollTop = contenedor.scrollHeight;
    }

    // Estilo para animación simple
    const style = document.createElement('style');
    style.innerHTML = `
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
        .animation-fade-in { animation: fadeIn 0.2s ease-out; }
    `;
    document.head.appendChild(style);
</script>