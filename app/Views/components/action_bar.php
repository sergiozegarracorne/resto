<?php
$_rol = session('usuario_turno')['rol'] ?? 'mozo';

// Catálogo completo de botones de acción.
// 'roles' define quiénes lo ven por defecto.
// TODO: reemplazar este array con lectura de DB (tabla permisos_botones_accion)
//       cuando se construya el panel de configuración de niveles de acceso.
$_catalogo = [
    'corregir' => [
        'label'   => 'Corregir',
        'icon'    => '❌',
        'type'    => 'button',
        'onclick' => 'undoUltimaAccion()',
        'color'   => 'red',
        'id'      => 'btn-corregir',
        'attrs'   => 'style="opacity:0.4" disabled',
        'roles'   => ['mozo', 'caja', 'administrador', 'sudo'],
    ],
    'mesas' => [
        'label'   => 'Mesas',
        'icon'    => '🍽️',
        'type'    => 'button',
        'onclick' => 'toggleMesasOverlay()',
        'color'   => 'indigo',
        'roles'   => ['mozo', 'caja', 'administrador', 'sudo'],
    ],
    'comandas' => [
        'label'   => 'Comandas',
        'icon'    => '📋',
        'type'    => 'button',
        'onclick' => 'showComandaModal()',
        'color'   => 'amber',
        'roles'   => ['mozo', 'caja', 'administrador', 'sudo'],
    ],
    'gabeta' => [
        'label'   => 'GABETA',
        'icon'    => '🎁',
        'type'    => 'button',
        'onclick' => '',
        'color'   => 'emerald',
        'roles'   => ['caja', 'administrador', 'sudo'],
    ],
    'vendedores' => [
        'label'  => 'Vendedores',
        'icon'   => '👥',
        'type'   => 'link',
        'href'   => '/',
        'color'  => 'orange',
        'roles'  => ['mozo', 'caja', 'administrador', 'sudo'],
    ],
    'opciones' => [
        'label'  => 'Opciones',
        'icon'   => '⚙️',
        'type'   => 'link',
        'href'   => 'panel',
        'color'  => 'slate',
        'roles'  => ['caja', 'administrador', 'sudo'],
    ],
];

$_visibles = array_filter($_catalogo, fn($b) => in_array($_rol, $b['roles'], true));
$_cols     = max(1, count($_visibles));
?>
<!-- SECCION ACCIONES -->
<section class="h-[22vh] bg-orange-100 border-t-8 border-orange-300 p-6 z-1">
    <div class="grid gap-3 h-full px-2" style="grid-template-columns: repeat(<?= $_cols ?>, minmax(0, 1fr))">
        <?php foreach ($_visibles as $_btn): ?>
            <?= accion_btn($_btn) ?>
        <?php endforeach; ?>
    </div>
</section>
