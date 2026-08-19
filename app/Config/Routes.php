<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// ─── Vistas ───────────────────────────────────────────────────────────────────
$routes->get('/',               'Home::index');
$routes->get('/componentes',    'Home::componentes');
$routes->get('/ingress/(:num)', 'Home::ingress/$1');

$routes->get('/venta',          'Venta::index');
$routes->get('/venta/(:num)',   'Venta::index/$1');

$routes->get('/panel',          'Panel::index');
$routes->get('/caja',           'Caja::index');

$routes->get('/almacen/compras', 'Almacen::compras');
$routes->get('/almacen/insumos', 'Almacen::insumos');

$routes->get('/productos', 'Productos::index');
$routes->get('/mesas',     'Mesas::index');
$routes->get('/combos',      'Combos::index');
$routes->get('/impresoras',  'Impresoras::index');
$routes->get('/usuarios',  'Usuarios::index');
$routes->get('/ventas',    'Ventas::index');

// ─── API ──────────────────────────────────────────────────────────────────────
$routes->group('api', function ($routes) {

    // Auth
    $routes->get('time',                  'Api\AuthApi::time');
    $routes->post('verificar_vendedor',   'Api\AuthApi::verificar_vendedor');

    // Mesas
    $routes->get('get_pisos_mesas',            'Api\MesasApi::get_pisos_mesas');
    $routes->get('mesas/resumen_activo',       'Api\MesasApi::resumen_activo');
    $routes->post('unir_mesas',                'Api\MesasApi::unir_mesas');
    $routes->post('separar_mesas',             'Api\MesasApi::separar_mesas');
    $routes->post('update_mesas_positions',    'Api\MesasApi::update_mesas_positions');
    $routes->post('mesas/set_pre_cuenta',      'Api\MesasApi::set_pre_cuenta');
    $routes->post('pisos/save',                'Api\MesasApi::piso_save');
    $routes->post('pisos/delete',              'Api\MesasApi::piso_delete');
    $routes->post('mesas/save',                'Api\MesasApi::mesa_save');
    $routes->post('mesas/delete',              'Api\MesasApi::mesa_delete');

    // Pedidos
    $routes->get('get_mesa_pedido/(:num)',  'Api\PedidosApi::get_mesa_pedido/$1');
    $routes->post('save_pedido',            'Api\PedidosApi::save_pedido');
    $routes->post('cancelar_pedido',        'Api\PedidosApi::cancelar_pedido');

    // Ventas
    $routes->post('cobrar_pedido',          'Api\VentasApi::cobrar_pedido');

    // Caja
    $routes->get('caja/get_data',           'Api\CajaApi::get_data');

    // Almacén
    $routes->get('get_insumos',             'Api\AlmacenApi::get_insumos');
    $routes->post('save_insumo',            'Api\AlmacenApi::save_insumo');
    $routes->post('save_compra',            'Api\AlmacenApi::save_compra');

    // Productos
    $routes->get('productos/get_all',       'Api\ProductosApi::get_all');
    $routes->post('productos/save',         'Api\ProductosApi::save');
    $routes->post('productos/delete',       'Api\ProductosApi::delete');

    // Categorías
    $routes->get('categorias/get_all',      'Api\CategoriasApi::get_all');
    $routes->post('categorias/save',        'Api\CategoriasApi::save');
    $routes->post('categorias/delete',      'Api\CategoriasApi::delete');

    // Usuarios
    $routes->get('usuarios/get_all',        'Api\UsuariosApi::get_all');
    $routes->post('usuarios/save',          'Api\UsuariosApi::save');
    $routes->post('usuarios/delete',        'Api\UsuariosApi::delete');
    
    // Combos
    $routes->get('combos/get_all',          'Api\CombosApi::get_all');
    $routes->post('combos/guardarCombo',    'Api\CombosApi::guardarCombo');
    $routes->post('combos/updateCombo',     'Api\CombosApi::updateCombo');
    $routes->post('combos/delete_combos/(:num)', 'Api\CombosApi::delete_combos/$1');

    // Imágenes
    $routes->get('imagenes/get_all',        'Api\ImagenesApi::get_all');
    $routes->post('imagenes/upload',         'Api\ImagenesApi::upload');

    // Ventas reporte
    $routes->get('ventas/get_lista',          'Api\VentasReporteApi::get_lista');
    $routes->get('ventas/get_detalle/(:num)', 'Api\VentasReporteApi::get_detalle/$1');
    $routes->post('ventas/imprimir',          'Api\VentasReporteApi::imprimir');

    // Impresoras
    $routes->get('impresoras/get_all',       'Api\ImpresorasApi::get_all');
    $routes->post('impresoras/save',         'Api\ImpresorasApi::save');
    $routes->post('impresoras/delete/(:num)','Api\ImpresorasApi::delete/$1');
    $routes->post('impresoras/test/(:num)',  'Api\ImpresorasApi::test_print/$1');
    $routes->get('impresoras/scan_red',      'Api\ImpresorasApi::scan_red');
    $routes->get('impresoras/get_routing',   'Api\ImpresorasApi::get_routing');
    $routes->post('impresoras/save_routing', 'Api\ImpresorasApi::save_routing');

    
});



