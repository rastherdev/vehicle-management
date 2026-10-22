<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Autenticación
$routes->get('/', 'Auth::index');
$routes->post('/login', 'Auth::login', ['filter' => 'csrf']);
$routes->get('/logout', 'Auth::logout');

// Panel de administración (requiere sesión iniciada)
$routes->get('admin', 'Admin::index', ['filter' => 'auth']);

// Vehículos: cards + registro de combustible + gastos
$routes->group('admin/vehiculos', ['filter' => 'auth'], static function (RouteCollection $routes): void {
    $routes->get('', 'Vehiculos::index');
    $routes->get('cargas', 'Api\CargasCombustible::index');
    $routes->post('cargas', 'Api\CargasCombustible::store', ['filter' => 'csrf']);
    $routes->get('fotos/(:segment)', 'Api\CargasCombustible::foto/$1');
    $routes->get('gastos', 'Api\Gastos::index');
    $routes->post('gastos', 'Api\Gastos::store', ['filter' => 'csrf']);
    $routes->get('gastos/fotos/(:segment)', 'Api\Gastos::foto/$1');
});

// Mantenimientos: seguimiento programado
$routes->group('admin/mantenimientos', ['filter' => 'auth'], static function (RouteCollection $routes): void {
    $routes->get('', 'Mantenimientos::index');
    $routes->get('lista', 'Api\Mantenimientos::index');
    $routes->get('(:num)', 'Api\Mantenimientos::show/$1');
    $routes->post('crear', 'Api\Mantenimientos::store', ['filter' => 'csrf']);
    $routes->post('(:num)/iniciar', 'Api\Mantenimientos::iniciar/$1', ['filter' => 'csrf']);
    $routes->post('(:num)/completar', 'Api\Mantenimientos::completar/$1', ['filter' => 'csrf']);
    $routes->post('(:num)/cancelar', 'Api\Mantenimientos::cancelar/$1', ['filter' => 'csrf']);
});

// Reportes y gráficas
$routes->group('admin/reportes', ['filter' => 'auth'], static function (RouteCollection $routes): void {
    $routes->get('', 'Reportes::index');
    $routes->get('opciones', 'Api\Reportes::opciones');
    $routes->get('gastos', 'Api\Reportes::gastos');
    $routes->get('cargas', 'Api\Reportes::cargas');
    $routes->get('impacto', 'Api\Reportes::impacto');
    $routes->get('mantenimientos', 'Api\Reportes::mantenimientos');
});

// Dashboard (inicio)
$routes->get('admin/dashboard/resumen', 'Api\Dashboard::resumen', ['filter' => 'auth']);

// Configuración: página + CRUDs AJAX (Áreas, Conductores, Vehículos)
$routes->get('admin/configuracion', 'Configuracion::index', ['filter' => 'auth']);
$routes->group('admin/configuracion', ['filter' => 'auth'], static function (RouteCollection $routes): void {
    // Áreas
    $routes->get('areas', 'Api\Areas::index');
    $routes->get('areas/(:num)', 'Api\Areas::show/$1');
    $routes->post('areas', 'Api\Areas::create', ['filter' => 'csrf']);
    $routes->put('areas/(:num)', 'Api\Areas::update/$1', ['filter' => 'csrf']);
    $routes->delete('areas/(:num)', 'Api\Areas::delete/$1', ['filter' => 'csrf']);

    // Conductores
    $routes->get('conductores', 'Api\Conductores::index');
    $routes->get('conductores/(:num)', 'Api\Conductores::show/$1');
    $routes->post('conductores', 'Api\Conductores::create', ['filter' => 'csrf']);
    $routes->put('conductores/(:num)', 'Api\Conductores::update/$1', ['filter' => 'csrf']);
    $routes->delete('conductores/(:num)', 'Api\Conductores::delete/$1', ['filter' => 'csrf']);

    // Vehículos
    $routes->get('vehiculos', 'Api\Vehiculos::index');
    $routes->get('vehiculos/(:num)', 'Api\Vehiculos::show/$1');
    $routes->post('vehiculos', 'Api\Vehiculos::create', ['filter' => 'csrf']);
    $routes->put('vehiculos/(:num)', 'Api\Vehiculos::update/$1', ['filter' => 'csrf']);
    $routes->delete('vehiculos/(:num)', 'Api\Vehiculos::delete/$1', ['filter' => 'csrf']);
});

// Resto de secciones del panel (placeholders)
$routes->group('admin', ['filter' => 'auth'], static function (RouteCollection $routes): void {
    $routes->get('(:segment)', 'Admin::section/$1');
});
