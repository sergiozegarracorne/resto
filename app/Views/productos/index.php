<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Productos<?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="flex flex-col h-full">

    <!-- Encabezado -->
    <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between shrink-0">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Productos</h1>
            <p class="text-sm text-gray-500" id="contador">Cargando...</p>
        </div>
        <div class="flex gap-2">
            <button onclick="abrirCategorias()"
                class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200 transition">
                Categorías
            </button>
            <a href="<?= base_url('panel') ?>"
                class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200 transition">
                Panel
            </a>
            <button onclick="abrirModalProducto()"
                class="px-5 py-2 rounded-lg bg-blue-600 text-white text-sm font-bold hover:bg-blue-700 transition shadow">
                + Nuevo Producto
            </button>
        </div>
    </header>

    <!-- Filtro por categoría -->
    <div class="bg-white border-b border-gray-200 px-6 py-3 flex gap-2 overflow-x-auto shrink-0" id="tabs-categorias"></div>

    <!-- Grid de productos -->
    <main class="flex-1 overflow-y-auto p-6">
        <div id="grid-productos" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4"></div>
        <p id="msg-vacio" class="hidden text-center text-gray-400 mt-16 text-lg">No hay productos aquí aún</p>
    </main>
</div>

<!-- ══════════════════════════════════════════════════════════
     MODAL PRODUCTO
══════════════════════════════════════════════════════════ -->
<div id="modal-producto" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg flex flex-col max-h-[92vh]">

        <!-- Preview en vivo -->
        <div class="bg-gradient-to-br from-slate-700 to-slate-900 rounded-t-2xl px-6 py-5 flex items-center gap-4 shrink-0">
            <div id="mp-icon" class="w-16 h-16 bg-white/10 rounded-2xl flex items-center justify-center text-4xl shrink-0">
                🍽️
            </div>
            <div class="flex-1 min-w-0">
                <p id="mp-preview-nombre" class="text-white/50 font-semibold text-base truncate italic">Nombre del producto...</p>
                <p id="mp-preview-cat"    class="text-white/40 text-xs mt-0.5">Sin categoría</p>
                <p id="mp-preview-precio" class="text-emerald-400 font-bold text-2xl mt-1">S/ —</p>
            </div>
            <button onclick="cerrarModalProducto()" class="text-white/40 hover:text-white text-3xl leading-none self-start mt-[-4px]">×</button>
        </div>

        <!-- Formulario scrollable -->
        <form id="form-producto" class="overflow-y-auto flex-1 p-5 space-y-5" onsubmit="guardarProducto(event)">
            <input type="hidden" id="p-id">

            <!-- Nombre -->
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">
                    Nombre <span class="text-red-400">*</span>
                </label>
                <input id="p-nombre" type="text" required autocomplete="off"
                    placeholder="Ej: Pizza Americana"
                    oninput="actualizarPreview()"
                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-base font-medium text-gray-800 placeholder-gray-300 focus:outline-none focus:border-blue-500 transition">
            </div>

            <!-- Categoría como chips -->
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">
                    Categoría
                </label>
                <div id="cat-chips" class="flex flex-wrap gap-2">
                    <!-- renderizado por JS -->
                </div>
                <input type="hidden" id="p-categoria">
            </div>

            <!-- Precio -->
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">
                    Precio <span class="text-red-400">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 font-bold text-xl select-none">S/</span>
                    <input id="p-precio" type="number" step="0.01" min="0" required
                        placeholder="0.00"
                        oninput="actualizarPreview()"
                        class="w-full border-2 border-gray-200 rounded-xl pl-12 pr-4 py-3 text-3xl font-bold text-blue-600 placeholder-gray-200 focus:outline-none focus:border-blue-500 transition">
                </div>
            </div>

            <!-- Precio costo -->
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Precio de costo</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 font-bold text-base select-none">S/</span>
                    <input id="p-precio-costo" type="number" step="0.01" min="0" placeholder="0.00"
                        class="w-full border-2 border-gray-200 rounded-xl pl-10 pr-4 py-2.5 text-base text-gray-600 placeholder-gray-200 focus:outline-none focus:border-blue-500 transition">
                </div>
                <p class="text-xs text-gray-400 mt-1">Solo para calcular margen de ganancia, no se muestra al cliente</p>
            </div>

            <!-- Separador -->
            <div class="border-t border-gray-100 pt-1">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Descripción e imagen</p>
            </div>

            <!-- Descripción -->
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">
                    Descripción corta
                </label>
                <input id="p-descripcion" type="text" maxlength="150" autocomplete="off"
                    placeholder="Ej: Masa crujiente, doble queso, salsa de tomate..."
                    oninput="document.getElementById('desc-chars').textContent=this.value.length"
                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:border-blue-500 transition">
                <p class="text-xs text-gray-400 mt-1"><span id="desc-chars">0</span>/150</p>
            </div>

            <!-- Imagen -->
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">
                    Miniatura / Imagen
                </label>
                <div class="flex gap-3 items-center">
                    <div onclick="abrirSelectorImagenes()"
                        class="flex-1 border-2 border-gray-200 rounded-xl px-4 py-2.5 bg-gray-50 flex items-center min-h-[44px] overflow-hidden cursor-pointer hover:border-blue-300 transition">
                        <span id="p-imagen-nombre" class="text-sm text-gray-400 italic truncate">Sin imagen — click para elegir</span>
                    </div>
                    <input type="hidden" id="p-imagen">
                    <div id="img-preview-wrap" onclick="abrirSelectorImagenes()"
                        style="width:56px;height:56px;border-radius:0.75rem;border:2px dashed #93c5fd;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;background:#eff6ff;cursor:pointer"
                        title="Elegir imagen">
                        <span id="img-preview-placeholder" class="text-2xl">🖼️</span>
                        <img id="img-preview" src="" alt="" style="display:none;width:100%;height:100%;object-fit:cover"
                            onerror="this.style.display='none';document.getElementById('img-preview-placeholder').style.display=''">
                    </div>
                </div>
            </div>

            <!-- Ingredientes -->
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">
                    Ingredientes
                </label>
                <textarea id="p-ingredientes" rows="3"
                    placeholder="Un ingrediente por línea:&#10;Queso mozzarella&#10;Tomate fresco&#10;Aceitunas negras"
                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:border-blue-500 transition resize-none"></textarea>
            </div>

            <!-- Separador -->
            <div class="border-t border-gray-100 pt-1">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Impuestos</p>
            </div>

            <!-- IGV -->
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Afectación IGV</label>
                <div class="grid grid-cols-3 gap-2" id="igv-opciones">
                    <button type="button" onclick="seleccionarIgv('gravado')"    data-igv="gravado"    class="igv-btn py-2.5 rounded-xl border-2 border-blue-500 bg-blue-50 text-blue-700 text-xs font-semibold transition">Gravado</button>
                    <button type="button" onclick="seleccionarIgv('exonerado')"  data-igv="exonerado"  class="igv-btn py-2.5 rounded-xl border-2 border-gray-200 text-gray-500 text-xs font-semibold transition">Exonerado</button>
                    <button type="button" onclick="seleccionarIgv('inafecto')"   data-igv="inafecto"   class="igv-btn py-2.5 rounded-xl border-2 border-gray-200 text-gray-500 text-xs font-semibold transition">Inafecto</button>
                </div>
                <input type="hidden" id="p-tipo-igv" value="gravado">
                <p class="text-xs text-gray-400 mt-1">
                    <span id="igv-desc">Aplica IGV según la tasa global vigente</span>
                </p>
            </div>

            <!-- ICBR -->
            <div class="flex items-center justify-between bg-amber-50 border border-amber-100 rounded-xl px-4 py-3.5 cursor-pointer" onclick="toggleIcbr()">
                <div>
                    <p class="text-sm font-semibold text-gray-700">Cobra impuesto ICBR</p>
                    <p class="text-xs text-gray-400 mt-0.5">Impuesto a la bolsa plástica (monto fijo por unidad)</p>
                </div>
                <div id="toggle-icbr" class="w-12 h-6 rounded-full bg-gray-300 relative transition-colors duration-200 shrink-0 ml-3">
                    <span id="toggle-icbr-thumb" class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-200 block"></span>
                </div>
                <input type="hidden" id="p-icbr" value="0">
            </div>

            <!-- Separador -->
            <div class="border-t border-gray-100 pt-1">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Stock y vencimiento</p>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Stock mínimo</label>
                    <input id="p-stock-min" type="number" step="1" min="0" placeholder="0"
                        class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Stock máximo</label>
                    <input id="p-stock-max" type="number" step="1" min="0" placeholder="Sin límite"
                        class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Fecha de vencimiento</label>
                <input id="p-vencimiento" type="date"
                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition">
                <p class="text-xs text-gray-400 mt-1">Opcional — el sistema alertará cuando se acerque la fecha</p>
            </div>

            <!-- Separador -->
            <div class="border-t border-gray-100 pt-1">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Opciones</p>
            </div>

            <!-- Combo toggle -->
            <div class="flex items-center justify-between bg-gray-50 rounded-xl px-4 py-3.5 cursor-pointer" onclick="toggleCombo()">
                <div>
                    <p class="text-sm font-semibold text-gray-700">Producto combo</p>
                    <p class="text-xs text-gray-400 mt-0.5">Agrupa varios items en un solo precio</p>
                </div>
                <div id="toggle-combo" class="w-12 h-6 rounded-full bg-gray-300 relative transition-colors duration-200 shrink-0">
                    <span id="toggle-thumb" class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-200 block"></span>
                </div>
                <input type="hidden" id="p-combo" value="0">
            </div>

            <!-- Activo toggle -->
            <div class="flex items-center justify-between bg-gray-50 rounded-xl px-4 py-3.5 cursor-pointer" onclick="toggleActivo()">
                <div>
                    <p class="text-sm font-semibold text-gray-700">Visible en el POS</p>
                    <p class="text-xs text-gray-400 mt-0.5">Desactívalo para ocultarlo sin eliminarlo</p>
                </div>
                <div id="toggle-activo" class="w-12 h-6 rounded-full bg-blue-500 relative transition-colors duration-200 shrink-0">
                    <span id="toggle-activo-thumb" class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-200 block" style="transform:translateX(24px)"></span>
                </div>
                <input type="hidden" id="p-activo" value="1">
            </div>

            <div id="mp-error" class="hidden bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3"></div>
        </form>

        <!-- Botones fijos -->
        <div class="px-5 py-4 border-t border-gray-100 flex gap-3 shrink-0">
            <button type="button" onclick="cerrarModalProducto()"
                class="flex-1 py-3 rounded-xl border-2 border-gray-200 text-gray-600 font-semibold hover:bg-gray-50 transition text-sm">
                Cancelar
            </button>
            <button type="submit" form="form-producto" id="btn-guardar-p"
                class="flex-1 py-3 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700 active:bg-blue-800 transition shadow-lg shadow-blue-200 text-sm">
                Guardar producto
            </button>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     MODAL SELECTOR DE IMÁGENES
══════════════════════════════════════════════════════════ -->
<div id="modal-imagenes" style="display:none;z-index:60" class="fixed inset-0 bg-black/70 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl flex flex-col" style="max-height:90vh">

        <!-- Header -->
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
            <h2 class="text-base font-bold text-gray-800">📷 Seleccionar imagen</h2>
            <button onclick="cerrarSelectorImagenes()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>

        <!-- Tabs -->
        <div class="border-b border-gray-100 px-5 flex shrink-0">
            <button id="tab-biblioteca-btn" onclick="cambiarTabImg('biblioteca')"
                class="px-4 py-3 text-sm font-semibold border-b-2 border-blue-500 text-blue-600 transition">
                🗂 Biblioteca
            </button>
            <button id="tab-subir-btn" onclick="cambiarTabImg('subir')"
                class="px-4 py-3 text-sm font-semibold border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition">
                ⬆️ Subir imagen
            </button>
        </div>

        <!-- TAB BIBLIOTECA -->
        <div id="tab-biblioteca" class="flex flex-1 min-h-0 overflow-hidden">
            <!-- Lista de nombres izquierda -->
            <div class="w-60 shrink-0 border-r border-gray-100 overflow-y-auto bg-white">
                <div id="galeria-grid" class="flex flex-col divide-y divide-gray-100">
                    <div class="text-center py-10 text-gray-400 text-sm">Cargando...</div>
                </div>
            </div>
            <!-- Preview derecha -->
            <div class="flex-1 flex flex-col items-center justify-center p-6 gap-3 min-h-0">
                <div class="flex items-center justify-center bg-gray-50 rounded-xl overflow-hidden" style="width:400px;height:400px;max-width:100%">
                    <div id="galeria-no-sel" class="text-center text-gray-300">
                        <div class="text-6xl mb-2">🖼️</div>
                        <p class="text-sm">Selecciona una imagen</p>
                    </div>
                    <img id="galeria-preview-img" src="" alt="" style="display:none;max-width:400px;max-height:400px;object-fit:contain;border-radius:0.5rem">
                </div>
                <p id="galeria-preview-nombre" class="text-xs text-gray-400 font-mono text-center truncate w-full max-w-sm"></p>
            </div>
        </div>

        <!-- TAB SUBIR -->
        <div id="tab-subir" style="display:none" class="flex-1 flex flex-col items-center justify-center p-8 gap-5 overflow-y-auto">
            <label for="file-upload-input" id="drop-zone"
                class="w-full max-w-md border-2 border-dashed border-gray-300 rounded-2xl p-8 flex flex-col items-center gap-3 cursor-pointer hover:border-blue-400 hover:bg-blue-50/50 transition"
                ondragover="event.preventDefault();this.classList.add('border-blue-400','bg-blue-50')"
                ondragleave="this.classList.remove('border-blue-400','bg-blue-50')"
                ondrop="manejarDropImagen(event)">
                <div class="text-5xl">📁</div>
                <p class="text-sm font-semibold text-gray-600">Arrastra una imagen aquí</p>
                <p class="text-xs text-gray-400">o haz click para seleccionar</p>
                <p class="text-xs text-gray-300">JPG, PNG, WEBP, GIF — máx. 5 MB</p>
            </label>
            <input type="file" id="file-upload-input" accept="image/*" class="sr-only" onchange="previsualizarSubida(this)">

            <div id="subida-preview-wrap" style="display:none" class="flex flex-col items-center gap-3 w-full max-w-md">
                <img id="subida-preview-img" src="" alt="" class="max-h-40 rounded-xl shadow object-contain">
                <p id="subida-preview-nombre" class="text-xs text-gray-500 font-mono text-center"></p>
                <div id="subida-error" style="display:none" class="text-xs text-red-600 bg-red-50 border border-red-200 rounded-lg px-4 py-2 w-full text-center"></div>
                <button onclick="ejecutarSubida()" id="btn-subir-imagen"
                    class="px-6 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-200 w-full max-w-xs">
                    ⬆️ Subir imagen
                </button>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-5 py-4 border-t border-gray-100 flex gap-3 shrink-0">
            <button onclick="cerrarSelectorImagenes()"
                class="flex-1 py-2.5 rounded-xl border-2 border-gray-200 text-gray-600 text-sm font-semibold hover:bg-gray-50 transition">
                Cancelar
            </button>
            <button onclick="confirmarSeleccionImg()" id="btn-confirmar-img" disabled
                class="flex-1 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-bold hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed transition shadow-lg shadow-blue-200">
                ✔ Seleccionar imagen
            </button>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     MODAL CATEGORÍAS
══════════════════════════════════════════════════════════ -->
<div id="modal-categorias" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md flex flex-col max-h-[90vh]">

        <div class="p-5 border-b border-gray-100 flex items-center justify-between shrink-0">
            <h2 class="text-lg font-bold text-gray-800">Categorías</h2>
            <button onclick="cerrarCategorias()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>

        <!-- Lista de categorías -->
        <div id="lista-cats" class="flex-1 overflow-y-auto divide-y divide-gray-100"></div>

        <!-- Formulario nueva/editar categoría -->
        <div class="p-5 border-t border-gray-100 shrink-0">
            <p class="text-sm font-semibold text-gray-700 mb-3" id="cat-form-titulo">Nueva Categoría</p>
            <div class="flex gap-2 mb-2">
                <input id="cat-icono" type="text" placeholder="🍕" maxlength="4"
                    class="w-16 border border-gray-300 rounded-lg px-3 py-2 text-center text-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <input id="cat-nombre" type="text" placeholder="Nombre de la categoría"
                    class="flex-1 border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <input type="hidden" id="cat-id">
            <div id="cat-error" class="hidden bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-2 mb-2"></div>
            <div class="flex gap-2">
                <button id="cat-cancelar-btn" onclick="resetFormCat()" class="hidden px-4 py-2 rounded-lg border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50">
                    Cancelar
                </button>
                <button onclick="guardarCategoria()"
                    class="flex-1 py-2 rounded-lg bg-blue-600 text-white text-sm font-bold hover:bg-blue-700 transition">
                    Guardar categoría
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     MODAL CONFIRMAR ELIMINAR
══════════════════════════════════════════════════════════ -->
<div id="modal-delete" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xs p-6 text-center">
        <div class="text-5xl mb-3">🗑️</div>
        <h3 class="text-base font-bold text-gray-800 mb-1">¿Eliminar?</h3>
        <p id="delete-nombre" class="text-gray-500 text-sm mb-5"></p>
        <div class="flex gap-3">
            <button onclick="cerrarDelete()"
                class="flex-1 py-2.5 rounded-lg border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50">
                Cancelar
            </button>
            <button id="btn-confirmar-delete"
                class="flex-1 py-2.5 rounded-lg bg-red-600 text-white text-sm font-bold hover:bg-red-700">
                Eliminar
            </button>
        </div>
    </div>
</div>

<script>
const BASE = '<?= base_url() ?>';
let productos = [], categorias = [], catActiva = null;

// ─── Carga inicial ────────────────────────────────────────
async function cargar() {
    const [rProd, rCat] = await Promise.all([
        fetch(BASE + 'api/productos/get_all'),
        fetch(BASE + 'api/categorias/get_all'),
    ]);
    const dProd = await rProd.json();
    categorias  = await rCat.json();
    productos   = dProd.productos || [];

    renderTabs();
    renderGrid();
    poblarSelectCategorias();

    const total = productos.length;
    document.getElementById('contador').textContent = total + ' producto' + (total !== 1 ? 's' : '');
}

// ─── Tabs ─────────────────────────────────────────────────
function renderTabs() {
    const cont = document.getElementById('tabs-categorias');
    cont.innerHTML = btnTab(null, 'Todos', true);
    categorias.forEach(c => {
        cont.insertAdjacentHTML('beforeend', btnTab(c.id, (c.icono ? c.icono + ' ' : '') + c.nombre, false));
    });
}

function btnTab(id, label, activo) {
    const cls = activo
        ? 'bg-blue-600 text-white'
        : 'bg-gray-100 text-gray-700 hover:bg-gray-200';
    return `<button onclick="filtrar(${id ?? 'null'})" data-cat="${id ?? 'null'}"
        class="tab-btn px-4 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition ${cls}">${label}</button>`;
}

function filtrar(catId) {
    catActiva = catId === 'null' ? null : catId;
    document.querySelectorAll('.tab-btn').forEach(b => {
        const activo = String(b.dataset.cat) === String(catId);
        b.className = b.className
            .replace('bg-blue-600 text-white', '')
            .replace('bg-gray-100 text-gray-700', '')
            .trim();
        b.classList.add(...(activo ? ['bg-blue-600','text-white'] : ['bg-gray-100','text-gray-700']));
    });
    renderGrid();
}

// ─── Grid productos ───────────────────────────────────────
function renderGrid() {
    const lista = catActiva
        ? productos.filter(p => String(p.categoria_id) === String(catActiva))
        : productos;

    const grid  = document.getElementById('grid-productos');
    const vacio = document.getElementById('msg-vacio');

    if (!lista.length) { grid.innerHTML = ''; vacio.classList.remove('hidden'); return; }
    vacio.classList.add('hidden');

    grid.innerHTML = lista.map(p => {
        const cat = categorias.find(c => c.id == p.categoria_id);
        return `<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            <div class="bg-gradient-to-br from-blue-50 to-indigo-100 h-20 flex items-center justify-center text-4xl">
                ${cat?.icono || '🍽️'}
            </div>
            <div class="p-3 flex-1 flex flex-col gap-0.5">
                <p class="font-semibold text-gray-800 text-sm leading-tight">${p.nombre}</p>
                <p class="text-xs text-gray-400">${cat?.nombre || 'Sin categoría'}</p>
                <p class="text-blue-600 font-bold text-base mt-1">S/ ${parseFloat(p.precio).toFixed(2)}</p>
                ${p.es_combo ? '<span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full w-fit mt-1">Combo</span>' : ''}
            </div>
            <div class="border-t border-gray-100 flex">
                <button onclick="editarProducto(${p.id})" class="flex-1 py-2 text-xs text-blue-600 font-medium hover:bg-blue-50">Editar</button>
                <div class="w-px bg-gray-100"></div>
                <button onclick="confirmarDelete(${p.id}, '${p.nombre.replace(/'/g,"\\'")}', 'producto')" class="flex-1 py-2 text-xs text-red-500 font-medium hover:bg-red-50">Eliminar</button>
            </div>
        </div>`;
    }).join('');
}

function poblarSelectCategorias() {
    // ya no usa <select>, los chips se renderizan en abrirModalProducto
}

// ─── Preview en vivo ──────────────────────────────────────
function actualizarPreview() {
    const nombre = document.getElementById('p-nombre').value.trim();
    const precio = parseFloat(document.getElementById('p-precio').value);
    const catId  = document.getElementById('p-categoria').value;
    const cat    = categorias.find(c => String(c.id) === String(catId));

    document.getElementById('mp-preview-nombre').textContent = nombre || 'Nombre del producto...';
    document.getElementById('mp-preview-nombre').classList.toggle('italic', !nombre);
    document.getElementById('mp-preview-nombre').classList.toggle('text-white/50', !nombre);
    document.getElementById('mp-preview-nombre').classList.toggle('text-white', !!nombre);

    document.getElementById('mp-preview-cat').textContent = cat ? (cat.icono ? cat.icono + ' ' : '') + cat.nombre : 'Sin categoría';
    document.getElementById('mp-preview-precio').textContent = isNaN(precio) ? 'S/ —' : 'S/ ' + precio.toFixed(2);
    if (!document.getElementById('p-imagen').value.trim()) {
        document.getElementById('mp-icon').textContent = cat?.icono || '🍽️';
    }
}

function actualizarImagenPreview() {
    setImagenFormulario(document.getElementById('p-imagen').value.trim());
}

function setImagenFormulario(url) {
    const nombre      = url ? url.split('/').pop() : '';
    const labelEl     = document.getElementById('p-imagen-nombre');
    const img         = document.getElementById('img-preview');
    const placeholder = document.getElementById('img-preview-placeholder');
    const mpIcon      = document.getElementById('mp-icon');

    labelEl.textContent    = nombre || 'Sin imagen — click para elegir';
    labelEl.style.fontStyle = nombre ? 'normal' : 'italic';
    labelEl.style.color     = nombre ? '#374151' : '#9ca3af';

    if (url) {
        img.src = url;
        img.style.display = 'block';
        placeholder.style.display = 'none';
        mpIcon.innerHTML = '<img src="' + url + '" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:1rem" onerror="this.parentElement.textContent=\'🍽️\'">';
    } else {
        img.style.display = 'none';
        placeholder.style.display = '';
        const catId = document.getElementById('p-categoria').value;
        const cat   = categorias.find(c => String(c.id) === String(catId));
        mpIcon.textContent = cat?.icono || '🍽️';
    }
}

// ─── Chips de categoría ───────────────────────────────────
function renderChipsCategorias(catSeleccionada) {
    const cont = document.getElementById('cat-chips');
    const ninguna = catSeleccionada == null || catSeleccionada === '';

    let html = `<button type="button" onclick="seleccionarCat('')"
        class="chip-cat px-3 py-1.5 rounded-full text-sm font-medium border-2 transition ${ninguna ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 text-gray-500 hover:border-gray-300'}">
        Sin categoría
    </button>`;

    categorias.forEach(c => {
        const activo = String(c.id) === String(catSeleccionada);
        html += `<button type="button" onclick="seleccionarCat(${c.id})"
            class="chip-cat px-3 py-1.5 rounded-full text-sm font-medium border-2 transition ${activo ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 text-gray-500 hover:border-gray-300'}">
            ${c.icono ? c.icono + ' ' : ''}${c.nombre}
        </button>`;
    });

    cont.innerHTML = html;
}

function seleccionarCat(id) {
    document.getElementById('p-categoria').value = id;
    renderChipsCategorias(id);
    actualizarPreview();
}

// ─── Toggle combo ─────────────────────────────────────────
function toggleCombo() {
    const val   = document.getElementById('p-combo').value === '1';
    const nuevo = !val;
    document.getElementById('p-combo').value = nuevo ? '1' : '0';
    document.getElementById('toggle-combo').classList.toggle('bg-blue-500', nuevo);
    document.getElementById('toggle-combo').classList.toggle('bg-gray-300', !nuevo);
    document.getElementById('toggle-thumb').style.transform = nuevo ? 'translateX(24px)' : 'translateX(0)';
}

function setToggleCombo(activo) {
    document.getElementById('p-combo').value = activo ? '1' : '0';
    document.getElementById('toggle-combo').classList.toggle('bg-blue-500', activo);
    document.getElementById('toggle-combo').classList.toggle('bg-gray-300', !activo);
    document.getElementById('toggle-thumb').style.transform = activo ? 'translateX(24px)' : 'translateX(0)';
}

function toggleActivo() {
    const val = document.getElementById('p-activo').value === '1';
    setToggleActivo(!val);
}
function setToggleActivo(activo) {
    document.getElementById('p-activo').value = activo ? '1' : '0';
    document.getElementById('toggle-activo').classList.toggle('bg-blue-500', activo);
    document.getElementById('toggle-activo').classList.toggle('bg-gray-300', !activo);
    document.getElementById('toggle-activo-thumb').style.transform = activo ? 'translateX(24px)' : 'translateX(0)';
}

function toggleIcbr() {
    const val = document.getElementById('p-icbr').value === '1';
    setToggleIcbr(!val);
}
function setToggleIcbr(activo) {
    document.getElementById('p-icbr').value = activo ? '1' : '0';
    document.getElementById('toggle-icbr').classList.toggle('bg-amber-500', activo);
    document.getElementById('toggle-icbr').classList.toggle('bg-gray-300', !activo);
    document.getElementById('toggle-icbr-thumb').style.transform = activo ? 'translateX(24px)' : 'translateX(0)';
}

const IGV_DESC = {
    gravado:   'Aplica IGV según la tasa global vigente',
    exonerado: 'Exonerado — no paga IGV (operación exonerada)',
    inafecto:  'Inafecto — fuera del alcance del IGV',
};
function seleccionarIgv(tipo) {
    document.getElementById('p-tipo-igv').value = tipo;
    document.getElementById('igv-desc').textContent = IGV_DESC[tipo];
    document.querySelectorAll('.igv-btn').forEach(b => {
        const activo = b.dataset.igv === tipo;
        b.className = b.className
            .replace('border-blue-500 bg-blue-50 text-blue-700', '')
            .replace('border-gray-200 text-gray-500', '')
            .trim();
        b.classList.add(...(activo
            ? ['border-blue-500', 'bg-blue-50', 'text-blue-700']
            : ['border-gray-200', 'text-gray-500']));
    });
}

// ─── Modal producto ───────────────────────────────────────
function abrirModalProducto(p = null) {
    document.getElementById('p-id').value          = p?.id            || '';
    document.getElementById('p-nombre').value      = p?.nombre        || '';
    document.getElementById('p-precio').value      = p?.precio        || '';
    document.getElementById('p-precio-costo').value = p?.precio_costo || '';
    document.getElementById('p-categoria').value   = p?.categoria_id  || '';
    document.getElementById('p-stock-min').value    = p?.stock_minimo       || '';
    document.getElementById('p-stock-max').value    = p?.stock_maximo       || '';
    document.getElementById('p-vencimiento').value  = p?.fecha_vencimiento  || '';
    document.getElementById('p-descripcion').value  = p?.descripcion        || '';
    document.getElementById('p-imagen').value        = p?.imagen             || '';
    document.getElementById('p-ingredientes').value  = p?.ingredientes       || '';
    document.getElementById('desc-chars').textContent = (p?.descripcion || '').length;

    setToggleCombo(p?.es_combo == 1);
    setToggleActivo(p ? p.activo == 1 : true);
    setToggleIcbr(p?.aplica_icbr == 1);
    seleccionarIgv(p?.tipo_igv || 'gravado');
    renderChipsCategorias(p?.categoria_id || '');
    actualizarPreview();
    actualizarImagenPreview();
    document.getElementById('mp-error').classList.add('hidden');
    document.getElementById('modal-producto').classList.remove('hidden');
    setTimeout(() => document.getElementById('p-nombre').focus(), 50);
}

function cerrarModalProducto() {
    document.getElementById('modal-producto').classList.add('hidden');
}

function editarProducto(id) {
    abrirModalProducto(productos.find(p => p.id == id));
}

async function guardarProducto(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-guardar-p');
    btn.disabled = true; btn.textContent = 'Guardando...';

    try {
        const res  = await fetch(BASE + 'api/productos/save', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                id:                document.getElementById('p-id').value || null,
                nombre:            document.getElementById('p-nombre').value,
                precio:            document.getElementById('p-precio').value,
                precio_costo:      document.getElementById('p-precio-costo').value || null,
                categoria_id:      document.getElementById('p-categoria').value || null,
                tipo_igv:          document.getElementById('p-tipo-igv').value,
                aplica_icbr:       document.getElementById('p-icbr').value === '1',
                es_combo:          document.getElementById('p-combo').value === '1',
                activo:            document.getElementById('p-activo').value === '1' ? 1 : 0,
                stock_minimo:      document.getElementById('p-stock-min').value || 0,
                stock_maximo:      document.getElementById('p-stock-max').value || null,
                fecha_vencimiento: document.getElementById('p-vencimiento').value || null,
                descripcion:       document.getElementById('p-descripcion').value.trim()  || null,
                imagen:            document.getElementById('p-imagen').value.trim()        || null,
                ingredientes:      document.getElementById('p-ingredientes').value.trim()  || null,
            })
        });
        const data = await res.json();
        if (data.success) { cerrarModalProducto(); await cargar(); }
        else { mostrarError('mp-error', data.message || 'Error al guardar'); }
    } catch { mostrarError('mp-error', 'Error de conexión'); }
    finally { btn.disabled = false; btn.textContent = 'Guardar producto'; }
}

// ─── Modal categorías ─────────────────────────────────────
function abrirCategorias() {
    renderListaCats();
    resetFormCat();
    document.getElementById('modal-categorias').classList.remove('hidden');
}

function cerrarCategorias() {
    document.getElementById('modal-categorias').classList.add('hidden');
    cargar();
}

function renderListaCats() {
    const cont = document.getElementById('lista-cats');
    if (!categorias.length) {
        cont.innerHTML = '<p class="text-center text-gray-400 text-sm py-6">No hay categorías aún</p>';
        return;
    }
    cont.innerHTML = categorias.map(c => `
        <div class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50">
            <span class="text-2xl w-8 text-center">${c.icono || '📁'}</span>
            <span class="flex-1 text-sm font-medium text-gray-700">${c.nombre}</span>
            <button onclick="editarCategoria(${c.id})" class="text-xs text-blue-600 hover:underline px-2">Editar</button>
            <button onclick="confirmarDelete(${c.id}, '${c.nombre.replace(/'/g,"\\'")}', 'categoria')" class="text-xs text-red-500 hover:underline px-2">Quitar</button>
        </div>`).join('');
}

function editarCategoria(id) {
    const c = categorias.find(x => x.id == id);
    if (!c) return;
    document.getElementById('cat-id').value    = c.id;
    document.getElementById('cat-nombre').value = c.nombre;
    document.getElementById('cat-icono').value  = c.icono || '';
    document.getElementById('cat-form-titulo').textContent = 'Editando: ' + c.nombre;
    document.getElementById('cat-cancelar-btn').classList.remove('hidden');
    document.getElementById('cat-nombre').focus();
}

function resetFormCat() {
    document.getElementById('cat-id').value    = '';
    document.getElementById('cat-nombre').value = '';
    document.getElementById('cat-icono').value  = '';
    document.getElementById('cat-form-titulo').textContent = 'Nueva Categoría';
    document.getElementById('cat-cancelar-btn').classList.add('hidden');
    document.getElementById('cat-error').classList.add('hidden');
}

async function guardarCategoria() {
    const nombre = document.getElementById('cat-nombre').value.trim();
    if (!nombre) { mostrarError('cat-error', 'El nombre es obligatorio'); return; }

    const res  = await fetch(BASE + 'api/categorias/save', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            id:     document.getElementById('cat-id').value || null,
            nombre,
            icono:  document.getElementById('cat-icono').value.trim(),
            estado: 1,
        })
    });
    const data = await res.json();
    if (data.success) {
        const rCat = await fetch(BASE + 'api/categorias/get_all');
        categorias = await rCat.json();
        renderListaCats();
        resetFormCat();
    } else {
        mostrarError('cat-error', data.message || 'Error');
    }
}

// ─── Modal eliminar ───────────────────────────────────────
let deleteId = null, deleteTipo = null;

function confirmarDelete(id, nombre, tipo) {
    deleteId = id; deleteTipo = tipo;
    document.getElementById('delete-nombre').textContent = nombre;
    document.getElementById('modal-delete').classList.remove('hidden');
}

function cerrarDelete() {
    document.getElementById('modal-delete').classList.add('hidden');
    deleteId = null; deleteTipo = null;
}

document.getElementById('btn-confirmar-delete').onclick = async () => {
    if (!deleteId) return;
    const url = deleteTipo === 'categoria'
        ? BASE + 'api/categorias/delete'
        : BASE + 'api/productos/delete';

    const res  = await fetch(url, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id: deleteId})
    });
    const data = await res.json();
    if (data.success) {
        cerrarDelete();
        if (deleteTipo === 'categoria') {
            const rCat = await fetch(BASE + 'api/categorias/get_all');
            categorias = await rCat.json();
            renderListaCats();
        } else {
            await cargar();
        }
    }
};

function mostrarError(elId, msg) {
    const el = document.getElementById(elId);
    el.textContent = msg; el.classList.remove('hidden');
}

// Cerrar al hacer click fuera
['modal-producto','modal-categorias','modal-delete'].forEach(id => {
    document.getElementById(id).addEventListener('click', e => {
        if (e.target === e.currentTarget) e.currentTarget.classList.add('hidden');
    });
});

// ─── Selector de imágenes ─────────────────────────────────
let imgSeleccionada  = null;
let archivoPendiente = null;

async function abrirSelectorImagenes() {
    imgSeleccionada  = null;
    archivoPendiente = null;
    document.getElementById('btn-confirmar-img').disabled = true;
    document.getElementById('galeria-preview-img').style.display = 'none';
    document.getElementById('galeria-no-sel').style.display      = '';
    document.getElementById('galeria-preview-nombre').textContent = '';
    document.getElementById('subida-preview-wrap').style.display  = 'none';
    document.getElementById('file-upload-input').value            = '';
    document.getElementById('subida-error').style.display         = 'none';
    document.getElementById('modal-imagenes').style.display       = 'flex';
    cambiarTabImg('biblioteca');
    await cargarGaleria();
}

function cerrarSelectorImagenes() {
    document.getElementById('modal-imagenes').style.display = 'none';
}

function cambiarTabImg(tab) {
    const esBib = tab === 'biblioteca';
    document.getElementById('tab-biblioteca').style.display = esBib ? 'flex' : 'none';
    document.getElementById('tab-subir').style.display      = esBib ? 'none'  : 'flex';
    const actCls  = 'px-4 py-3 text-sm font-semibold border-b-2 transition border-blue-500 text-blue-600';
    const idleCls = 'px-4 py-3 text-sm font-semibold border-b-2 transition border-transparent text-gray-500 hover:text-gray-700';
    document.getElementById('tab-biblioteca-btn').className = esBib  ? actCls : idleCls;
    document.getElementById('tab-subir-btn').className      = !esBib ? actCls : idleCls;
}

async function cargarGaleria() {
    const grid = document.getElementById('galeria-grid');
    grid.innerHTML = '<div class="col-span-2 text-center py-10 text-gray-400 text-sm">Cargando...</div>';
    try {
        const res  = await fetch(BASE + 'api/imagenes/get_all');
        const data = await res.json();
        renderGaleriaImg(data.imagenes || []);
    } catch {
        grid.innerHTML = '<div class="col-span-2 text-center py-8 text-red-400 text-xs">Error al cargar</div>';
    }
}

function renderGaleriaImg(imagenes) {
    const grid = document.getElementById('galeria-grid');
    if (!imagenes.length) {
        grid.innerHTML = '<div class="text-center py-10 text-gray-400 text-xs leading-relaxed">Sin imágenes aún.<br>Usa ⬆️ para subir una.</div>';
        return;
    }
    grid.innerHTML = imagenes.map(img => `
        <button type="button"
            onclick="seleccionarImgGaleria('${img.url}','${img.nombre}')"
            data-img-url="${img.url}"
            class="img-thumb w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors flex items-center gap-2 truncate">
            <span class="text-base shrink-0">🖼️</span>
            <span class="truncate">${img.nombre}</span>
        </button>`).join('');
}

function seleccionarImgGaleria(url, nombre) {
    imgSeleccionada = {url, nombre};
    document.querySelectorAll('.img-thumb').forEach(b => {
        const activo = b.dataset.imgUrl === url;
        b.style.background = activo ? '#eff6ff' : '';
        b.style.color      = activo ? '#1d4ed8' : '';
        b.style.fontWeight = activo ? '600'     : '';
    });
    const previewImg = document.getElementById('galeria-preview-img');
    previewImg.src           = url;
    previewImg.style.display = 'block';
    document.getElementById('galeria-no-sel').style.display      = 'none';
    document.getElementById('galeria-preview-nombre').textContent = nombre;
    document.getElementById('btn-confirmar-img').disabled         = false;
}

function confirmarSeleccionImg() {
    if (!imgSeleccionada) return;
    document.getElementById('p-imagen').value = imgSeleccionada.url;
    setImagenFormulario(imgSeleccionada.url);
    cerrarSelectorImagenes();
}

// ─── Subida de archivos ───────────────────────────────────
function previsualizarSubida(input) {
    const file = input.files[0];
    if (!file) return;
    archivoPendiente = file;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('subida-preview-img').src         = e.target.result;
        document.getElementById('subida-preview-nombre').textContent = file.name;
        document.getElementById('subida-preview-wrap').style.display = 'flex';
        document.getElementById('subida-error').style.display        = 'none';
    };
    reader.readAsDataURL(file);
}

function manejarDropImagen(e) {
    e.preventDefault();
    document.getElementById('drop-zone').classList.remove('border-blue-400','bg-blue-50');
    const file = e.dataTransfer.files[0];
    if (!file) return;
    const dt = new DataTransfer();
    dt.items.add(file);
    const input = document.getElementById('file-upload-input');
    input.files = dt.files;
    previsualizarSubida(input);
}

async function ejecutarSubida() {
    if (!archivoPendiente) return;
    const btn = document.getElementById('btn-subir-imagen');
    btn.disabled = true; btn.textContent = 'Subiendo...';
    document.getElementById('subida-error').style.display = 'none';
    const form = new FormData();
    form.append('imagen', archivoPendiente);
    try {
        const res  = await fetch(BASE + 'api/imagenes/upload', {method:'POST', body: form});
        const data = await res.json();
        if (data.success) {
            archivoPendiente = null;
            document.getElementById('file-upload-input').value      = '';
            document.getElementById('subida-preview-wrap').style.display = 'none';
            cambiarTabImg('biblioteca');
            await cargarGaleria();
            seleccionarImgGaleria(data.url, data.nombre);
        } else {
            document.getElementById('subida-error').textContent    = data.message || 'Error al subir';
            document.getElementById('subida-error').style.display  = 'block';
        }
    } catch {
        document.getElementById('subida-error').textContent   = 'Error de conexión';
        document.getElementById('subida-error').style.display = 'block';
    } finally {
        btn.disabled = false; btn.textContent = '⬆️ Subir imagen';
    }
}

cargar();
</script>

<?= $this->endSection() ?>
