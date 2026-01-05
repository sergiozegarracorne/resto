<!-- FILA SUPERIOR: CATEGORIAS (20% aprox, fijo) -->
<header class="h-[20vh] bg-gray-100 border-b border-orange-400 border-dashed  p-2   relative z-0 flex items-center  ">


    <!-- Contenedor Scrollable -->
    <div class="flex-1 h-full min-w-0 relative">
        <div class="absolute inset-0 overflow-x-auto no-scrollbar scroll-smooth grid grid-rows-2 grid-flow-col gap-1 " id="scroll-categorias">
            <?php foreach ($categorias as $cat): ?>
                <button onclick="filtrarProductos(<?= $cat['id'] ?? $cat['id_categoria'] ?>, this)"
                    class="categoria-btn h-full w-36 bg-white border border-orange-300 hover:bg-orange-50 hover:border-orange-400 active:bg-orange-200 transition-all flex flex-row items-center justify-center space-x-1 px-1 group cursor-pointer focus:outline-none">
                    <span class="text-xl group-hover:scale-110 transition-transform"><?= $cat['icono'] ?></span>
                    <span
                        class="font-bold text-gray-600 text-sm group-hover:text-orange-600 group-active:text-black text-left leading-tight line-clamp-2"><?= $cat['nombre'] ?></span>
                </button>
            <?php endforeach; ?>
        </div>
    </div> 

    <!-- Botón Scroll (Loop) -->
    <button onclick="scrollCategorias()"
        class="h-full w-18 bg-orange-100  active:bg-orange-400 rounded-br-lg rounded-tr-lg    border  border-orange-400  flex items-center justify-center text-gray-500 hover:text-indigo-600 transition-colors shadow-sm flex-shrink-0 z-10">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
    </button>

    <script>
        function scrollCategorias() {
            const container = document.getElementById('scroll-categorias');
            const scrollAmount = 310; //container.clientWidth * 0.7; // Scroll 80% of width
            const maxScrollLeft = container.scrollWidth - container.clientWidth;

            // Si ya llegamos al final (con un margen de error de 10px), volver al inicio
            if (container.scrollLeft >= maxScrollLeft - 10) {
                container.scrollTo({ left: 0, behavior: 'auto' });
            } else {
                // Si no, avanzar
                container.scrollBy({ left: scrollAmount, behavior: 'auto' });
            }
        }
        function filtrarProductos(idCategoria, btn) {
            // 1. Manejo visual de botones
            document.querySelectorAll('.categoria-btn').forEach(b => {
                b.classList.remove('ring-orange-500', 'bg-orange-100');
                b.classList.add('bg-white');
            });

            if (btn) {
                btn.classList.remove('bg-white');
                btn.classList.add('ring-orange-500', 'bg-orange-100');
            }

            // 2. Filtrado de productos
            const productos = document.querySelectorAll('.producto-card');
            productos.forEach(prod => {
                if (idCategoria === 'todos' || prod.dataset.categoria == idCategoria) {
                    prod.classList.remove('hidden');
                } else {
                    prod.classList.add('hidden');
                }
            });
        }
    </script>
</header>

<!-- FILA INFERIOR: PRODUCTOS (Resto de altura) -->
<section class="flex-1 overflow-y-auto p-2  custom-scrollbar bg-gray-300">
    <div class="grid grid-cols-3 md:grid-cols-3 lg:grid-cols-6 gap-2 p-1">
        <?php foreach ($productos as $prod): ?>
            <button onclick="agregarProducto(<?= $prod['id'] ?>, '<?= esc($prod['nombre']) ?>', <?= $prod['precio'] ?>)"
                data-categoria="<?= $prod['categoria_id'] ?? '' ?>"
                class="producto-card relative bg-white border border-gray-200 rounded-md p-2 px-1 pb-0 shadow-sm hover:shadow-md hover:border-indigo-400 hover:-translate-y-1 transition-all flex flex-col items-center justify-between text-center h-26 active:scale-95 group">
                <!-- Increased height back to h-48 for layout -->

                <!-- Precio flotante -->
                <span
                    class="absolute top-0 right-0 bg-indigo-100 text-indigo-700 text-xs font-bold px-2 py-1 rounded-bl-lg rounded-tr-md shadow-sm">
                    $<?= number_format($prod['precio'], 2) ?>
                </span>

                <div class="hidden sm:block w-24 h-10 mb-1 mt-3">
                    <img src="<?= $prod['imagen'] ?>" alt="<?= $prod['nombre'] ?>"
                        class="w-full h-full object-contain text-xs text-gray-300">
                </div>
                <div class="w-full flex-1 flex items-center justify-center p-0">
                    <h4 class="font-bold text-gray-700 text-xs leading-tight line-clamp-2"><?= $prod['nombre'] ?>
                    </h4>
                </div>
            </button>
        <?php endforeach; ?>

        <!-- Placeholders para llenar espacio visual -->
        <?php for ($i = 0; $i < 10; $i++): ?>
            <div
                class="bg-gray-50 border border-dashed border-gray-200 rounded-xl h-26 flex items-center justify-center opacity-50">
                <span class="text-gray-300 text-sm">Espacio Libre</span>
            </div>
        <?php endfor; ?>
    </div>
</section>
<?= view('components/action_bar') ?>