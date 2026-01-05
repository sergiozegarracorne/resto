<div id="panel-overlay" class="hidden fixed inset-0 z-50 transition-opacity duration-200">
    <!-- Backdrop Blur -->
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="togglePanel()"></div>

    <!-- Panel Content Container -->
    <div class="absolute inset-4 md:inset-10 bg-gray-100 rounded-3xl shadow-2xl flex flex-col overflow-hidden transform transition-all scale-100 border border-white/20">
        
        <!-- Header del Panel Overlay -->
        <header class="bg-slate-900 text-white p-4 shadow-md flex justify-between items-center h-16 shrink-0 z-10">
            <h1 class="text-xl font-bold flex items-center gap-2">
                <span class="text-2xl">⚙️</span> Panel de Control
            </h1>
            <button onclick="togglePanel()" class="bg-slate-700 hover:bg-red-500 text-white p-2 rounded-full transition-colors w-10 h-10 flex items-center justify-center font-bold text-lg">
                ✕
            </button>
        </header>

        <!-- Grid Principal (Copiado de panel/index.php pero ajustado) -->
        <main class="flex-1 p-6 overflow-y-auto">
             <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 h-full content-start">
            
                <!-- TARJETAS DE GESTIÓN (Mantenimientos) -->
                <button class="bg-white border-b-4 border-blue-500 rounded-2xl shadow-md active:scale-95 active:bg-blue-50 transition-all p-6 flex flex-col items-center justify-center gap-4 h-36 group">
                    <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-3xl group-hover:scale-110 transition-transform">
                        🍎
                    </div>
                    <span class="text-lg font-bold text-gray-700">Productos</span>
                </button>

                <button class="bg-white border-b-4 border-amber-500 rounded-2xl shadow-md active:scale-95 active:bg-amber-50 transition-all p-6 flex flex-col items-center justify-center gap-4 h-36 group">
                    <div class="w-16 h-16 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center text-3xl group-hover:scale-110 transition-transform">
                        📜
                    </div>
                    <span class="text-lg font-bold text-gray-700">Menú / Carta</span>
                </button>

                <button class="bg-white border-b-4 border-orange-500 rounded-2xl shadow-md active:scale-95 active:bg-orange-50 transition-all p-6 flex flex-col items-center justify-center gap-4 h-36 group">
                    <div class="w-16 h-16 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center text-3xl group-hover:scale-110 transition-transform">
                        🍔
                    </div>
                    <span class="text-lg font-bold text-gray-700">Combos</span>
                </button>

                <!-- TARJETAS DE OPERACIÓN (Diario) -->
                <button class="bg-white border-b-4 border-emerald-500 rounded-2xl shadow-md active:scale-95 active:bg-emerald-50 transition-all p-6 flex flex-col items-center justify-center gap-4 h-36 group">
                    <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center text-3xl group-hover:scale-110 transition-transform">
                        💵
                    </div>
                    <span class="text-lg font-bold text-gray-700">Caja / Cuadre</span>
                </button>

                <button class="bg-white border-b-4 border-indigo-500 rounded-2xl shadow-md active:scale-95 active:bg-indigo-50 transition-all p-6 flex flex-col items-center justify-center gap-4 h-36 group">
                    <div class="w-16 h-16 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-3xl group-hover:scale-110 transition-transform">
                        📈
                    </div>
                    <span class="text-lg font-bold text-gray-700">Ventas</span>
                </button>
                
                <button class="bg-white border-b-4 border-purple-500 rounded-2xl shadow-md active:scale-95 active:bg-purple-50 transition-all p-6 flex flex-col items-center justify-center gap-4 h-36 group">
                    <div class="w-16 h-16 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center text-3xl group-hover:scale-110 transition-transform">
                        👥
                    </div>
                    <span class="text-lg font-bold text-gray-700">Usuarios</span>
                </button>

                <!-- BOTÓN CERRAR -->
                <button onclick="togglePanel()" class="bg-slate-700 border-b-4 border-slate-900 rounded-2xl shadow-md active:scale-95 active:bg-slate-600 transition-all p-6 flex flex-col items-center justify-center gap-4 h-36 group">
                    <div class="w-16 h-16 bg-slate-600 text-white rounded-full flex items-center justify-center text-3xl group-hover:-translate-x-1 transition-transform">
                        🔙
                    </div>
                    <span class="text-lg font-bold text-white">Volver</span>
                </button>

            </div>
        </main>
    </div>
</div>

<script>
    function togglePanel() {
        const panel = document.getElementById('panel-overlay');
        if (panel.classList.contains('hidden')) {
            panel.classList.remove('hidden');
        } else {
            panel.classList.add('hidden');
        }
    }
</script>
