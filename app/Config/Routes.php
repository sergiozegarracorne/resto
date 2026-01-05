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
$routes->get('/ingress/(:num)', 'Home::ingress/$1');
