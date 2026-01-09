<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/componentes', 'Home::componentes');

$routes->get('/venta', 'Venta::index');
$routes->get('/venta/(:num)', 'Venta::index/$1');

$routes->get('/panel', 'Panel::index');
$routes->get('/api/time', 'Api::time');
$routes->post('/api/verificar_vendedor', 'Api::verificar_vendedor');
$routes->get('/api/get_pisos_mesas', 'Api::get_pisos_mesas');
$routes->get('/api/get_mesa_pedido/(:num)', 'Api::get_mesa_pedido/$1');
$routes->post('/api/save_pedido', 'Api::save_pedido');
$routes->post('/api/unir_mesas', 'Api::unir_mesas');
$routes->post('/api/separar_mesas', 'Api::separar_mesas');
$routes->get('/ingress/(:num)', 'Home::ingress/$1');
