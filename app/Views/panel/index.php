<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
Panel Central - Resta
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Encabezado -->
<header class="bg-slate-900 text-white p-4 shadow-md flex justify-between items-center h-16 shrink-0 z-10">
    <h1 class="text-xl font-bold flex items-center gap-2">
        <span class="text-2xl">⚙️</span> Panel de Control
    </h1>
    <div class="text-sm opacity-75 capitalize">
        <?= esc($rol) ?>
    </div>
</header>

<!-- Grid Principal de Opciones -->
<main class="flex-1 p-6 overflow-y-auto">
    <div class="grid grid-cols-4  lg:grid-cols-6 gap-6 h-full content-start">

        <!-- TARJETAS DE GESTIÓN (Mantenimientos) -->
        <a href="<?= base_url('productos') ?>"
            class="bg-white border-b-4 border-blue-500 rounded-2xl shadow-md active:scale-95 active:bg-blue-50 transition-all p-6 flex flex-col items-center justify-center gap-4 h-36 group">
            <div
                class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-3xl group-hover:scale-110 transition-transform">
                🍎
            </div>
            <span class="text-lg font-bold text-gray-700">Productos</span>
        </a>

        <button
            class="bg-white border-b-4 border-amber-500 rounded-2xl shadow-md active:scale-95 active:bg-amber-50 transition-all p-6 flex flex-col items-center justify-center gap-4 h-36 group">
            <div
                class="w-16 h-16 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center text-3xl group-hover:scale-110 transition-transform">
                📜
            </div>
            <span class="text-lg font-bold text-gray-700">Menú / Carta</span>
        </button>

        <a href="<?= base_url('combos') ?>"
            class="bg-white border-b-4 border-orange-500 rounded-2xl shadow-md active:scale-95 active:bg-orange-50 transition-all p-6 flex flex-col items-center justify-center gap-4 h-36 group">
            <div
                class="w-16 h-16 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center text-3xl group-hover:scale-110 transition-transform">
                🍔
            </div>
            <span class="text-lg font-bold text-gray-700">Combos</span>
        </a>

        <!-- TARJETAS DE OPERACIÓN (Diario) -->
        <?php if (in_array($rol, ['admin', 'sudo'], true)): ?>
        <a href="<?= base_url('caja') ?>"
            class="bg-white border-b-4 border-emerald-500 rounded-2xl shadow-md active:scale-95 active:bg-emerald-50 transition-all p-6 flex flex-col items-center justify-center gap-4 h-36 group">
            <div
                class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center text-3xl group-hover:scale-110 transition-transform">
                💵
            </div>
            <span class="text-lg font-bold text-gray-700">Caja / Cuadre</span>
        </a>
        <?php endif; ?>

        <button
            class="bg-white border-b-4 border-indigo-500 rounded-2xl shadow-md active:scale-95 active:bg-indigo-50 transition-all p-6 flex flex-col items-center justify-center gap-4 h-36 group">
            <div
                class="w-16 h-16 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-3xl group-hover:scale-110 transition-transform">
                📈
            </div>
            <span class="text-lg font-bold text-gray-700">Ventas</span>
        </button>

        <?php if (in_array($rol, ['admin', 'sudo'], true)): ?>
        <a href="<?= base_url('usuarios') ?>"
            class="bg-white border-b-4 border-purple-500 rounded-2xl shadow-md active:scale-95 active:bg-purple-50 transition-all p-6 flex flex-col items-center justify-center gap-4 h-36 group">
            <div
                class="w-16 h-16 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center text-3xl group-hover:scale-110 transition-transform">
                👥
            </div>
            <span class="text-lg font-bold text-gray-700">Usuarios</span>
        </a>

        <a href="<?= base_url('impresoras') ?>"
            class="bg-white border-b-4 border-gray-500 rounded-2xl shadow-md active:scale-95 active:bg-gray-50 transition-all p-6 flex flex-col items-center justify-center gap-4 h-36 group">
            <div
                class="w-16 h-16 bg-gray-100 text-gray-600 rounded-full flex items-center justify-center text-3xl group-hover:scale-110 transition-transform">
                🖨️
            </div>
            <span class="text-lg font-bold text-gray-700">Impresoras</span>
        </a>
        <?php endif; ?>


        <!-- BOTÓN VOLVER + badge mesas abiertas -->
        <a href="<?= base_url('venta') ?>" id="card-volver"
            class="relative bg-slate-700 border-b-4 border-slate-900 rounded-2xl shadow-md active:scale-95 active:bg-slate-600 transition-all p-6 flex flex-col items-center justify-center gap-4 h-36 group">
            <!-- badge: se inyecta por JS si hay mesas ocupadas -->
            <span id="badge-mesas" style="display:none"
                class="absolute -top-2.5 -right-2.5 min-w-[28px] h-7 px-1.5
                bg-red-500 text-white text-xs font-black rounded-full items-center justify-center
                shadow-lg shadow-red-500/40 border-2 border-white z-10 leading-none"></span>
            <div
                class="w-16 h-16 bg-slate-600 text-white rounded-full flex items-center justify-center text-3xl group-hover:-translate-x-1 transition-transform">
                🔙
            </div>
            <span class="text-lg font-bold text-white">Volver a Ventas</span>
        </a>

    </div>
</main>

<script>
(async function() {
    try {
        const res  = await fetch('<?= base_url('api/mesas/resumen_activo') ?>');
        const data = await res.json();
        const n    = data.mesas_abiertas || 0;
        if (n > 0) {
            const badge = document.getElementById('badge-mesas');
            badge.textContent = n;
            badge.style.display = 'flex';
        }
    } catch (e) { /* silencioso */ }
})();
</script>

<?= $this->endSection() ?>