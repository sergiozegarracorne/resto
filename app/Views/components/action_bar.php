<?php
$_rol = session('usuario_turno')['rol'] ?? 'mozo';

$_attrsEspeciales = [
    'corregir' => ['id' => 'btn-corregir', 'attrs' => 'style="opacity:0.4" disabled'],
];

// ── Rutas bloqueadas para este rol ────────────────────────────────────────────
$_bloqueadas = [];
try {
    $_db     = db_connect();
    $_rolReg = $_db->table('roles')->where('nombre', $_rol)->get()->getRowArray();
    $_idRol  = $_rolReg ? (int)$_rolReg['id'] : 0;
    if ($_idRol) {
        $_bRows = $_db->table('rutas r')
            ->join('rutas_permisos rp', 'rp.id_ruta = r.id')
            ->where('r.activo', 1)
            ->where('rp.id_rol', $_idRol)
            ->where('rp.puede_ver', 0)
            ->get()->getResultArray();
        foreach ($_bRows as $_bRow) {
            $_bloqueadas[] = ltrim($_bRow['ruta'], '/');
        }
    }
} catch (\Throwable $_e) { /* sin permisos configurados → no bloquear */ }

// ── Botones desde la BD ───────────────────────────────────────────────────────
$_visibles = [];
try {
    $_botones = db_connect()->table('botones_catalogo')->orderBy('orden')->get()->getResultArray();
    foreach ($_botones as $_b) {
        $href = ltrim($_b['href'] ?? '', '/');
        if (in_array($href, $_bloqueadas, true)) continue;
        $key = $_b['boton_key'];
        $_visibles[$key] = [
            'label'   => $_b['label'],
            'icon'    => $_b['icon'],
            'color'   => $_b['color'],
            'type'    => $_b['tipo'],
            'onclick' => $_b['onclick'] ?? '',
            'href'    => $_b['href']    ?? '/',
        ] + ($_attrsEspeciales[$key] ?? []);
    }
} catch (\Throwable $_e) { /* tabla no existe aún */ }

// ── Botones fijos (siempre disponibles para todos los roles) ─────────────────
// Se insertan al frente; botones_catalogo puede reemplazarlos si define la misma clave.
$_fijos = [];

if (!isset($_visibles['mesas']) && !in_array('mesas', $_bloqueadas, true)) {
    $_fijos['mesas'] = [
        'label'   => 'Mesas',
        'icon'    => '🍽️',
        'color'   => 'indigo',
        'type'    => 'button',
        'onclick' => 'toggleMesasOverlay()',
    ];
}

if (!isset($_visibles['corregir'])) {
    $_fijos['corregir'] = [
        'label'   => 'Corregir',
        'icon'    => '↩️',
        'color'   => 'amber',
        'type'    => 'button',
        'onclick' => 'undoUltimaAccion()',
        'id'      => 'btn-corregir',
        'attrs'   => 'style="opacity:0.4" disabled',
    ];
}

if (!isset($_visibles['vendedores'])) {
    $_fijos['vendedores'] = [
        'label' => 'Vendedores',
        'icon'  => '👤',
        'color' => 'slate',
        'type'  => 'link',
        'href'  => '/',
    ];
}

if ($_rol !== 'mozo' && !isset($_visibles['panel'])) {
    $_fijos['panel'] = [
        'label' => 'Panel',
        'icon'  => '🏠',
        'color' => 'slate',
        'type'  => 'link',
        'href'  => '/panel',
    ];
}

$_visibles = $_fijos + $_visibles;

$_cols = max(1, count($_visibles));
?>
<!-- SECCION ACCIONES -->
<section class="h-[22vh] bg-orange-100 border-t-8 border-orange-300 p-6 z-1">
    <div class="grid gap-3 h-full px-2" style="grid-template-columns: repeat(<?= $_cols ?>, minmax(0, 1fr))">
        <?php foreach ($_visibles as $_btn): ?>
            <?= accion_btn($_btn) ?>
        <?php endforeach; ?>
    </div>
</section>
