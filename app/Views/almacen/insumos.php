<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Stock del Almacén<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="flex flex-col w-full h-full overflow-hidden">

    <!-- Header -->
    <div class="bg-green-600 text-white px-5 py-4 flex items-center justify-between shrink-0">
        <div class="flex items-center gap-3">
            <a href="<?= base_url('/') ?>" class="text-white opacity-70 hover:opacity-100 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <p class="text-xs opacity-80">Almacén</p>
                <h1 class="font-bold text-xl">Stock de Ingredientes</h1>
            </div>
        </div>
        <a href="<?= base_url('/almacen/compras') ?>"
            class="bg-white text-green-700 font-bold px-4 py-2 rounded-xl text-sm hover:bg-green-50 transition shadow">
            + Registrar Compra
        </a>
    </div>

    <!-- Resumen -->
    <?php
        $sinStock    = array_filter($insumos, fn($i) => $i['stock_actual'] <= 0);
        $stockBajo   = array_filter($insumos, fn($i) => $i['stock_actual'] > 0 && $i['stock_minimo'] > 0 && $i['stock_actual'] <= $i['stock_minimo']);
        $conStock    = array_filter($insumos, fn($i) => $i['stock_actual'] > $i['stock_minimo']);
    ?>
    <div class="grid grid-cols-3 gap-3 px-4 pt-4 shrink-0">
        <div class="bg-white rounded-xl p-3 text-center shadow-sm border border-gray-100">
            <p class="text-2xl font-bold text-green-600"><?= count($conStock) ?></p>
            <p class="text-xs text-gray-500 mt-0.5">Con stock</p>
        </div>
        <div class="bg-white rounded-xl p-3 text-center shadow-sm border border-orange-100">
            <p class="text-2xl font-bold text-orange-500"><?= count($stockBajo) ?></p>
            <p class="text-xs text-gray-500 mt-0.5">Stock bajo</p>
        </div>
        <div class="bg-white rounded-xl p-3 text-center shadow-sm border border-red-100">
            <p class="text-2xl font-bold text-red-500"><?= count($sinStock) ?></p>
            <p class="text-xs text-gray-500 mt-0.5">Sin stock</p>
        </div>
    </div>

    <!-- Buscador -->
    <div class="px-4 py-3 shrink-0">
        <input type="text" placeholder="🔍 Buscar ingrediente..." oninput="buscar(this.value)"
            class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-green-500 bg-white">
    </div>

    <!-- Lista -->
    <div class="flex-1 overflow-y-auto px-4 pb-4">
        <?php if (empty($insumos)): ?>
            <div class="text-center text-gray-400 mt-16">
                <p class="text-5xl mb-3">📦</p>
                <p>No hay ingredientes registrados.</p>
                <a href="<?= base_url('/almacen/compras') ?>" class="inline-block mt-4 bg-green-600 text-white px-6 py-2 rounded-xl font-semibold">
                    Registrar primera compra
                </a>
            </div>
        <?php else: ?>
            <div class="space-y-2" id="lista-insumos">
                <?php foreach ($insumos as $insumo):
                    $pct   = $insumo['stock_minimo'] > 0 ? min(100, ($insumo['stock_actual'] / $insumo['stock_minimo']) * 100) : 100;
                    $color = $insumo['stock_actual'] <= 0
                        ? 'bg-red-500'
                        : ($insumo['stock_actual'] <= $insumo['stock_minimo'] && $insumo['stock_minimo'] > 0
                            ? 'bg-orange-400'
                            : 'bg-green-500');
                    $badge = $insumo['stock_actual'] <= 0
                        ? '<span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full font-semibold">Sin stock</span>'
                        : ($insumo['stock_actual'] <= $insumo['stock_minimo'] && $insumo['stock_minimo'] > 0
                            ? '<span class="text-xs bg-orange-100 text-orange-600 px-2 py-0.5 rounded-full font-semibold">Stock bajo</span>'
                            : '<span class="text-xs bg-green-100 text-green-600 px-2 py-0.5 rounded-full font-semibold">OK</span>');
                ?>
                <div class="insumo-row bg-white rounded-xl px-4 py-3 shadow-sm border border-gray-100 flex items-center gap-4"
                    data-nombre="<?= strtolower(esc($insumo['nombre'])) ?>">
                    <span class="text-2xl shrink-0">📦</span>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-semibold text-gray-800"><?= esc($insumo['nombre']) ?></span>
                            <?= $badge ?>
                        </div>
                        <div class="flex items-center gap-3 mt-1">
                            <div class="flex-1 bg-gray-100 rounded-full h-2 max-w-[140px]">
                                <div class="<?= $color ?> h-2 rounded-full transition-all" style="width: <?= $pct ?>%"></div>
                            </div>
                            <span class="text-sm font-bold text-gray-700">
                                <?= number_format($insumo['stock_actual'], 2) ?> <?= esc($insumo['unidad']) ?>
                            </span>
                            <?php if ($insumo['stock_minimo'] > 0): ?>
                                <span class="text-xs text-gray-400">mín. <?= number_format($insumo['stock_minimo'], 1) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <button onclick="editarInsumo(<?= $insumo['id'] ?>, '<?= esc($insumo['nombre']) ?>', '<?= esc($insumo['unidad']) ?>', <?= $insumo['stock_minimo'] ?>)"
                        class="shrink-0 text-gray-400 hover:text-green-600 transition p-2 rounded-lg hover:bg-green-50">
                        ✏️
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL: Editar insumo -->
<div id="modal-editar" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-5">Editar ingrediente</h2>
        <input id="edit-id" type="hidden">

        <div class="mb-4">
            <label class="text-sm font-semibold text-gray-600 block mb-1">Nombre</label>
            <input id="edit-nombre" type="text"
                class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:border-green-500">
        </div>

        <div class="mb-4">
            <label class="text-sm font-semibold text-gray-600 block mb-1">Unidad de medida</label>
            <input id="edit-unidad" type="text"
                class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:border-green-500">
        </div>

        <div class="mb-6">
            <label class="text-sm font-semibold text-gray-600 block mb-1">Avisar cuando quede menos de...</label>
            <div class="flex items-center gap-2">
                <input id="edit-stock-min" type="number" min="0" step="1"
                    class="flex-1 border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:border-green-500">
                <span id="edit-unidad-label" class="text-sm text-gray-500 w-16">unidades</span>
            </div>
        </div>

        <div class="flex gap-3">
            <button onclick="cerrarEditar()"
                class="flex-1 py-3 border border-gray-300 rounded-xl text-gray-600 hover:bg-gray-50 font-semibold transition">
                Cancelar
            </button>
            <button onclick="guardarEdicion()"
                class="flex-1 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl font-bold transition shadow">
                Guardar ✓
            </button>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function buscar(q) {
        const texto = q.toLowerCase();
        document.querySelectorAll('.insumo-row').forEach(row => {
            row.style.display = row.dataset.nombre.includes(texto) ? '' : 'none';
        });
    }

    function editarInsumo(id, nombre, unidad, stockMin) {
        document.getElementById('edit-id').value       = id;
        document.getElementById('edit-nombre').value   = nombre;
        document.getElementById('edit-unidad').value   = unidad;
        document.getElementById('edit-stock-min').value = stockMin;
        document.getElementById('edit-unidad-label').textContent = unidad;
        document.getElementById('modal-editar').classList.remove('hidden');
    }

    function cerrarEditar() {
        document.getElementById('modal-editar').classList.add('hidden');
    }

    async function guardarEdicion() {
        const payload = {
            id:           parseInt(document.getElementById('edit-id').value),
            nombre:       document.getElementById('edit-nombre').value.trim(),
            unidad:       document.getElementById('edit-unidad').value.trim(),
            stock_minimo: parseFloat(document.getElementById('edit-stock-min').value) || 0,
        };

        if (!payload.nombre || !payload.unidad) {
            alert('Completa nombre y unidad');
            return;
        }

        const res  = await fetch('<?= base_url('/api/save_insumo') ?>', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(payload),
        });
        const data = await res.json();

        if (data.success) {
            cerrarEditar();
            location.reload();
        } else {
            alert('Error: ' + (data.message || ''));
        }
    }

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') cerrarEditar();
    });
</script>
<?= $this->endSection() ?>
