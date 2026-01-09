<!-- SECCION ACCIONES (20% altura) -->
<section class="h-[22vh] bg-orange-100  border-t-8 border-orange-300 p-6 z-20">
    <div class="grid grid-cols-5 gap-3 h-full px-2">
        <button
            class="bg-white border border-red-200 text-red-500 font-medium rounded-2xl shadow-sm hover:shadow-md hover:border-red-400 hover:bg-red-50 active:scale-95 transition-all flex flex-col items-center justify-center gap-1 group">
            <span
                class="text-2xl group-hover:scale-110 transition-transform filter grayscale group-hover:grayscale-0">❌</span>
            <span class="text-xs font-bold tracking-wide">Corregir</span>
        </button>
        <button onclick="toggleMesasOverlay()"
            class="bg-white border border-indigo-200 text-indigo-500 font-medium rounded-2xl shadow-sm hover:shadow-md hover:border-indigo-400 hover:bg-indigo-50 active:scale-95 transition-all flex flex-col items-center justify-center gap-1 group">
            <span
                class="text-2xl group-hover:scale-110 transition-transform filter grayscale group-hover:grayscale-0">🍽️</span>
            <span class="text-xs font-bold tracking-wide">Mesas</span>
        </button>

        <button
            class="bg-white border border-emerald-200 text-emerald-600 font-medium rounded-2xl shadow-sm hover:shadow-md hover:border-emerald-400 hover:bg-emerald-50 active:scale-95 transition-all flex flex-col items-center justify-center gap-1 group">
            <span
                class="text-2xl group-hover:scale-110 transition-transform filter grayscale group-hover:grayscale-0">🎁</span>
            <span class="text-xs font-bold tracking-wide">Cajon</span>
        </button>

        <a href="<?= base_url('/') ?>"
            class="bg-white border border-orange-200 text-orange-500 font-medium rounded-2xl shadow-sm hover:shadow-md hover:border-orange-400 hover:bg-orange-50 active:scale-95 transition-all flex flex-col items-center justify-center gap-1 group">
            <span
                class="text-2xl group-hover:scale-110 transition-transform filter grayscale group-hover:grayscale-0">👥</span>
            <span class="text-xs font-bold tracking-wide">Vendedores</span>
        </a>

        <a href="<?= base_url('panel') ?>"
            class="bg-slate-800 border border-slate-700 text-slate-200 font-medium rounded-2xl shadow-lg hover:shadow-xl hover:bg-slate-700 hover:text-white active:scale-95 transition-all flex flex-col items-center justify-center gap-1 group">
            <span class="text-2xl group-hover:scale-110 group-hover:rotate-90 transition-transform">⚙️</span>
            <span class="text-xs font-bold tracking-wide">Opciones</span>
        </a>
    </div>
</section>