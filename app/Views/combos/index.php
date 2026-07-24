<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Combos<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="flex flex-col h-full">

    <!-- HEADER -->
    <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Combos</h1>
            <p class="text-sm text-gray-500" id="contador-combos">0 combos</p>
        </div>

        <button onclick="abrirModalCombo()"
            class="px-5 py-2 rounded-lg bg-blue-600 text-white text-sm font-bold">
            + Nuevo Combo
        </button>
    </header>

    <!-- GRID -->
    <main class="flex-1 overflow-y-auto p-6">
        <div id="grid-combos" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4"></div>
        <p id="msg-vacio" class="hidden text-center text-gray-400 mt-10">No hay combos</p>
    </main>
</div>
<!-- =========================
MODAL COMBO
========================= -->
    <div id="modal-combo" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">

     <div class="bg-white w-full max-w-lg rounded-2xl shadow-xl flex flex-col h-[450px] overflow-hidden">

        <!-- HEADER -->
        <div class="p-4 border-b flex justify-between items-center">
            <h2 class="font-bold text-lg">Nuevo Combo</h2>
            <button onclick="cerrarModalCombo()" class="text-2xl">&times;</button>
        </div>

        <!-- FORM -->
        <form id="form-combo" class="p-5 space-y-4 overflow-y-auto flex-1">

            <input type="hidden" id="combo-id" />

            <!-- Nombre -->
            <div>
                <label class="text-sm font-bold">Nombre</label>
                <input type="text" id="combo-nombre"
                    class="w-full border rounded-lg px-3 py-2" required>
            </div>

            <!-- Precio -->
            <div>
                <label class="text-sm font-bold">Precio del combo</label>
                <input type="number" id="combo-precio"
                    class="w-full border rounded-lg px-3 py-2" required>
            </div>

            <!-- PRODUCTOS -->
            <div class="border-t pt-3">
                <div class="flex justify-between items-center mb-2">
                    <p class="text-xs font-bold text-gray-500 uppercase">
                        Productos del combo
                    </p>

                    <button type="button"
                        onclick="abrirSelectorProductos()"
                        class="text-xs bg-blue-600 text-white px-3 py-1 rounded">
                        + Agregar
                    </button>
                </div>

                <div id="lista-productos-combo" class="space-y-2 h-56 overflow-y-auto border rounded-lg p-2 bg-gray-50">
                    
                </div>
            </div>

        </form>

        <!-- FOOTER -->
        <div class="p-4 border-t flex gap-2">
        
            <button onclick="cerrarModalCombo()"
                class="flex-1 bg-gray-200 py-2 rounded-lg">
                Cancelar
            </button>
        
            <button id="btn-eliminar"
                onclick="eliminarCombo()"
                class="hidden flex-1 bg-red-600 text-white py-2 rounded-lg">
                Eliminar
            </button>
        
            <button id="btn-guardar"
                onclick="guardarCombo()"
                class="flex-1 bg-blue-600 text-white py-2 rounded-lg">
                Guardar
            </button>
        
        </div>

    </div>

</div>

<!-- =========================
MODAL SELECTOR PRODUCTOS
========================= -->
<div id="modal-selector-productos"
     class="hidden fixed inset-0 bg-black/60 z-50">

    <div style="
        position:absolute;
        top:40px;
        left:50%;
        transform:translateX(-50%);
        width:550px;
        height:650px;
        background:white;
        border-radius:16px;
        box-shadow:0 10px 30px rgba(0,0,0,.25);
        display:flex;
        flex-direction:column;
    ">

        <div style="padding:15px;border-bottom:1px solid #ddd;">
            <b>Seleccionar productos</b>
        </div>

        <div style="padding:10px;">
            <input
                id="buscar-producto"
                oninput="renderSelectorProductos()"
                style="width:100%;padding:8px;">
        </div>

        <div id="lista-selector-productos"
             style="
                flex:1;
                overflow:auto;
                padding:10px;
                border:1px solid #ddd;
            ">
        </div>

        <div style="padding:10px;border-top:1px solid #ddd;">
            <button onclick="cerrarSelectorProductos()">Cerrar</button>
        </div>

    </div>

</div>

<!-- =========================
SCRIPT
========================= -->
<script>
const BASE = '<?= base_url() ?>';

let combos = [];
let productos = [];
let productosCombo = [];

// =========================
// INIT
// =========================
async function cargar() {

    const [r1, r2] = await Promise.all([
        fetch(BASE + 'api/combos/get_all'),
        fetch(BASE + 'api/productos/get_all')
    ]);

    const d1 = await r1.json();
    const d2 = await r2.json();

    combos = d1.combos || [];
    productos = d2.productos || [];

    renderCombos();
}

// =========================
// GRID COMBOS
// =========================
function renderCombos() {
    const grid = document.getElementById('grid-combos');
    const vacio = document.getElementById('msg-vacio');

    if (!combos.length) {
        grid.innerHTML = '';
        vacio.classList.remove('hidden');
        return;
    }

    vacio.classList.add('hidden');

    grid.innerHTML = combos.map(c => `
        <div class="bg-white border rounded-xl p-4 shadow-sm">
            <h3 class="font-bold">${c.nombre}</h3>
            <p class="text-sm text-gray-500">S/ ${c.precio}</p>

            <button onclick="editarCombo(${c.id})"
                class="mt-2 text-xs text-blue-600">
                Editar
            </button>
        </div>
    `).join('');
}

// =========================
// MODAL COMBO
// =========================
function abrirModalCombo(combo = null) {

    document.getElementById('combo-id').value = combo?.id || '';
    document.getElementById('combo-nombre').value = combo?.nombre || '';
    document.getElementById('combo-precio').value = combo?.precio || '';

    productosCombo = combo?.productos || [];

    renderProductosCombo();

    const btnGuardar = document.getElementById('btn-guardar');
    const btnEliminar = document.getElementById('btn-eliminar');

    if (combo) {
        btnGuardar.innerText = 'Actualizar';
        btnGuardar.onclick = actualizarCombo;
        btnEliminar.classList.remove('hidden');
    } else {
        btnGuardar.innerText = 'Guardar';
        btnGuardar.onclick = guardarCombo;
        btnEliminar.classList.add('hidden');
    }

    document.getElementById('modal-combo').classList.remove('hidden');
}

function cerrarModalCombo() {

    document.getElementById('modal-combo').classList.add('hidden');

    productosCombo = [];

    document.getElementById('combo-id').value = '';

    document.getElementById('btn-guardar').innerText = 'Guardar';
    document.getElementById('btn-eliminar').classList.add('hidden');
}

async function eliminarCombo() {

    const id = document.getElementById('combo-id').value;

    if (!id) return;

    if (!confirm('¿Desea eliminar este combo?'))
        return;

    const res = await fetch(BASE + 'api/combos/delete_combos/' + id, {
        method: 'POST'
    });

    const data = await res.json();

    if (data.success) {
        cerrarModalCombo();
        cargar();
    } else {
        alert('No se pudo eliminar.');
    }
}

// =========================
// SELECTOR PRODUCTOS
// =========================
function abrirSelectorProductos() {
    renderSelectorProductos();
    document.getElementById('modal-selector-productos').classList.remove('hidden');
}

function cerrarSelectorProductos() {
    document.getElementById('modal-selector-productos').classList.add('hidden');
}

function renderSelectorProductos() {

    const q = document.getElementById('buscar-producto').value?.toLowerCase() || '';

    const lista = productos.filter(p =>
        p.nombre.toLowerCase().includes(q)
    );

    const cont = document.getElementById('lista-selector-productos');

    cont.innerHTML = lista.map(p => `
        <div class="flex justify-between items-center border rounded-lg p-2">
            <div>
                <p class="font-semibold text-sm">${p.nombre}</p>
                <p class="text-xs text-gray-400">S/ ${p.precio}</p>
            </div>

            <button onclick="agregarProducto(${p.id})"
                class="text-xs bg-green-600 text-white px-3 py-1 rounded">
                Agregar
            </button>
        </div>
    `).join('');
}

// =========================
// AGREGAR PRODUCTO AL COMBO
// =========================
function agregarProducto(id) {

    const p = productos.find(x => x.id == id);
    if (!p) return;

    if (productosCombo.some(x => x.id == id)) {
        cerrarSelectorProductos();
        return;
    }

    productosCombo.push({
        id: p.id,
        nombre: p.nombre,
        precio: p.precio,
        cantidad: 1
    });

    renderProductosCombo();
    cerrarSelectorProductos();
}

document.getElementById('modal-combo').addEventListener('click', e => {
    if (e.target === e.currentTarget) {
        cerrarModalCombo();
    }
});

document.getElementById('modal-selector-productos').addEventListener('click', e => {
    if (e.target === e.currentTarget) {
        cerrarSelectorProductos();
    }
});
// =========================
// LISTA COMBO
// =========================
function renderProductosCombo() {

    const cont = document.getElementById('lista-productos-combo');

    cont.innerHTML = productosCombo.map((p, i) => `
        <div class="flex justify-between items-center border rounded-lg p-2 pb-4">

            <div>
                <p class="text-sm font-semibold">${p.nombre}</p>
                <p class="text-xs text-gray-400">S/ ${p.precio}</p>
            </div>

            <div class="flex items-center gap-2">

                <input type="number" min="1"
                    value="${p.cantidad}"
                    onchange="productosCombo[${i}].cantidad = this.value"
                    class="w-14 border rounded px-2 py-1 text-sm">

                <button onclick="quitarProducto(${i})"
                    class="text-red-500 text-xs">
                    X
                </button>

            </div>
        </div>
    `).join('');
}

function quitarProducto(i) {
    productosCombo.splice(i, 1);
    renderProductosCombo();
}

// =========================
// GUARDAR COMBO
// =========================
async function guardarCombo() {

    const res = await fetch(BASE + 'api/combos/guardarCombo', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            id: document.getElementById('combo-id').value || null,
            nombre: document.getElementById('combo-nombre').value,
            precio: document.getElementById('combo-precio').value,
            productos: productosCombo
        })
    });

    const data = await res.json();

    if (data.success) {
        cerrarModalCombo();
        cargar();
    } else {
        alert('Error al guardar combo');
    }
}

// =========================
// EDITAR
// =========================
function editarCombo(id) {
    const c = combos.find(x => x.id == id);
    abrirModalCombo(c);
}

// =========================
// UPDATE - MODAL
// =========================

async function actualizarCombo(){
  const res = await fetch(BASE + 'api/combos/updateCombo', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            id: document.getElementById('combo-id').value || null,
            nombre: document.getElementById('combo-nombre').value,
            precio: document.getElementById('combo-precio').value,
            productos: productosCombo
        })
    });

    const data = await res.json();

    if (data.success) {
        cerrarModalCombo();
        cargar();
    } else {
        alert('Error al Actualizar combo');
    }
}

// INIT
cargar();
</script>

<?= $this->endSection() ?>