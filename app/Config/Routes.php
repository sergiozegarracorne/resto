<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/componentes', 'Home::componentes');
$routes->get('/venta', 'Venta::index');
