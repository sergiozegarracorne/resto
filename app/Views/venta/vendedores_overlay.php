<div id="vendedores-overlay" class="hidden fixed inset-0 z-50 transition-opacity duration-200">
    <!-- Backdrop Blur -->
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="toggleVendedores()"></div>

    <!-- Panel Content -->
    <div class="absolute inset-x-4 bottom-4 top-20 md:inset-20 bg-white rounded-3xl shadow-2xl flex flex-col overflow-hidden transform transition-all scale-100 border border-white/20">
        
        <!-- Header -->
        <header class="bg-orange-500 text-white p-4 shadow-md flex justify-between items-center h-16 shrink-0 z-10">
            <h1 class="text-xl font-bold flex items-center gap-2">
                <span class="text-2xl">👥</span> Seleccionar Vendedor
            </h1>
            <button onclick="toggleVendedores()" class="bg-orange-600 hover:bg-orange-700 text-white p-2 rounded-full transition-colors w-10 h-10 flex items-center justify-center font-bold text-lg">
                ✕
            </button>
        </header>

        <!-- Grid de Usuarios -->
        <main class="flex-1 p-6 overflow-y-auto bg-gray-50">
             <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 h-full content-start">
                <?php if (!empty($usuarios)): ?>
                    <?php foreach ($usuarios as $usu): ?>
                        <button onclick="seleccionarVendedor('<?= $usu['usuario'] ?>')" 
                            class="bg-white border-b-4 border-gray-300 hover:border-orange-400 rounded-2xl shadow-sm hover:shadow-md active:scale-95 active:bg-orange-50 transition-all p-4 flex flex-col items-center justify-center gap-3 h-32 group">
                            
                            <!-- Avatar Initials -->
                            <div class="w-14 h-14 bg-gray-200 text-gray-600 rounded-full flex items-center justify-center text-xl font-bold group-hover:bg-orange-100 group-hover:text-orange-600 transition-colors">
                                <?= strtoupper(substr($usu['usuario'], 0, 2)) ?>
                            </div>
                            
                            <span class="text-base font-bold text-gray-700 group-hover:text-orange-700"><?= esc($usu['usuario']) ?></span>
                        </button>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="col-span-full text-center text-gray-400">No hay usuarios registrados.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<script>
    function toggleVendedores() {
        const panel = document.getElementById('vendedores-overlay');
        if (panel.classList.contains('hidden')) {
            panel.classList.remove('hidden');
        } else {
            panel.classList.add('hidden');
        }
    }

    function seleccionarVendedor(nombre) {
        // En un futuro esto actualizará un estado global o el ticket
        // Por ahora solo actualizamos el visual del ticket (Juan Perez hardcoded)
        // Buscamos el elemento en el ticket (necesitamos ponerle un ID primero)
        // Pero como el usuario no pidió persistencia aun, solo cerramos
        
        // Simulación visual rapida (Hack)
        const meseroElement = document.querySelector('.fa-mesero-name'); // Inventare una clase o ID 
        if(meseroElement) meseroElement.innerText = nombre;

        alert('Vendedor seleccionado: ' + nombre);
        toggleVendedores();
    }
</script>
