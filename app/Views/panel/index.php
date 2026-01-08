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
    <div class="text-sm opacity-75">
        Admin
    </div>
</header>

<!-- Grid Principal de Opciones -->
<main class="flex-1 p-6 overflow-y-auto">
    <div class="grid grid-cols-4  lg:grid-cols-6 gap-6 h-full content-start">

        <!-- TARJETAS DE GESTIÓN (Mantenimientos) -->
        <button
            class="bg-white border-b-4 border-blue-500 rounded-2xl shadow-md active:scale-95 active:bg-blue-50 transition-all p-6 flex flex-col items-center justify-center gap-4 h-36 group">
            <div
                class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-3xl group-hover:scale-110 transition-transform">
                🍎
            </div>
            <span class="text-lg font-bold text-gray-700">Productos</span>
        </button>

        <button
            class="bg-white border-b-4 border-amber-500 rounded-2xl shadow-md active:scale-95 active:bg-amber-50 transition-all p-6 flex flex-col items-center justify-center gap-4 h-36 group">
            <div
                class="w-16 h-16 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center text-3xl group-hover:scale-110 transition-transform">
                📜
            </div>
            <span class="text-lg font-bold text-gray-700">Menú / Carta</span>
        </button>

        <button
            class="bg-white border-b-4 border-orange-500 rounded-2xl shadow-md active:scale-95 active:bg-orange-50 transition-all p-6 flex flex-col items-center justify-center gap-4 h-36 group">
            <div
                class="w-16 h-16 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center text-3xl group-hover:scale-110 transition-transform">
                🍔
            </div>
            <span class="text-lg font-bold text-gray-700">Combos</span>
        </button>

        <!-- TARJETAS DE OPERACIÓN (Diario) -->
        <button
            class="bg-white border-b-4 border-emerald-500 rounded-2xl shadow-md active:scale-95 active:bg-emerald-50 transition-all p-6 flex flex-col items-center justify-center gap-4 h-36 group">
            <div
                class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center text-3xl group-hover:scale-110 transition-transform">
                💵
            </div>
            <span class="text-lg font-bold text-gray-700">Caja / Cuadre</span>
        </button>

        <button
            class="bg-white border-b-4 border-indigo-500 rounded-2xl shadow-md active:scale-95 active:bg-indigo-50 transition-all p-6 flex flex-col items-center justify-center gap-4 h-36 group">
            <div
                class="w-16 h-16 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-3xl group-hover:scale-110 transition-transform">
                📈
            </div>
            <span class="text-lg font-bold text-gray-700">Ventas</span>
        </button>

        <button
            class="bg-white border-b-4 border-purple-500 rounded-2xl shadow-md active:scale-95 active:bg-purple-50 transition-all p-6 flex flex-col items-center justify-center gap-4 h-36 group">
            <div
                class="w-16 h-16 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center text-3xl group-hover:scale-110 transition-transform">
                👥
            </div>
            <span class="text-lg font-bold text-gray-700">Usuarios</span>
        </button>


        <!-- BOTÓN VOLVER -->
        <a href="<?= base_url('venta') ?>"
            class="bg-slate-700 border-b-4 border-slate-900 rounded-2xl shadow-md active:scale-95 active:bg-slate-600 transition-all p-6 flex flex-col items-center justify-center gap-4 h-36 group">
            <div
                class="w-16 h-16 bg-slate-600 text-white rounded-full flex items-center justify-center text-3xl group-hover:-translate-x-1 transition-transform">
                🔙
            </div>
            <span class="text-lg font-bold text-white">Volver a Ventas</span>
        </a>

    </div>
</main>
<?= $this->endSection() ?>