<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Test routes
$routes->get('test/database', 'TestController::database');
$routes->get('test/password', 'TestController::testPassword');

// Admin Web Routes (Session-based Authentication)
$routes->group('admin', function($routes) {
    // Public admin routes (no auth required)
    $routes->get('login', 'Admin\AuthController::login');
    $routes->post('login', 'Admin\AuthController::doLogin', ['filter' => ['security', 'csrf']]);
    $routes->get('logout', 'Admin\AuthController::logout');
    
    // Protected admin routes (auth required)
    $routes->group('', ['filter' => 'auth'], function($routes) {
        $routes->get('dashboard', 'Admin\DashboardController::index');
        
        // Pengaduan management
        $routes->get('pengaduan', 'Admin\PengaduanController::index');
        $routes->get('pengaduan/(:num)', 'Admin\PengaduanController::show/$1');
        $routes->get('pengaduan/(:num)/edit', 'Admin\PengaduanController::edit/$1');
        $routes->put('pengaduan/update/(:num)', 'Admin\PengaduanController::update/$1');
        $routes->post('pengaduan/(:num)/status', 'Admin\PengaduanController::updateStatus/$1');
        $routes->post('pengaduan/(:num)/comment', 'Admin\PengaduanController::addComment/$1', ['filter' => 'csrf']);
        $routes->post('pengaduan/update-comment/(:num)', 'Admin\PengaduanController::updateComment/$1', ['filter' => 'csrf']);
        
        // User management
        $routes->get('users', 'Admin\UserController::index');
        $routes->get('users/create', 'Admin\UserController::create');
        $routes->post('users', 'Admin\UserController::store');
        $routes->get('users/(:num)/edit', 'Admin\UserController::edit/$1');
        $routes->post('users/(:num)', 'Admin\UserController::update/$1');
        $routes->delete('users/(:num)', 'Admin\UserController::delete/$1');
        
        // Instansi management
        $routes->get('instansi', 'Admin\InstansiController::index');
        $routes->get('instansi/create', 'Admin\InstansiController::create');
        $routes->post('instansi', 'Admin\InstansiController::store');
        $routes->get('instansi/(:num)/edit', 'Admin\InstansiController::edit/$1');
        $routes->post('instansi/(:num)', 'Admin\InstansiController::update/$1');
        $routes->delete('instansi/(:num)', 'Admin\InstansiController::delete/$1');
        
        // Kategori management
        $routes->get('kategori', 'Admin\KategoriController::index');
        $routes->get('kategori/create', 'Admin\KategoriController::create');
        $routes->post('kategori', 'Admin\KategoriController::store');
        $routes->get('kategori/(:num)/edit', 'Admin\KategoriController::edit/$1');
        $routes->post('kategori/(:num)', 'Admin\KategoriController::update/$1');
        $routes->delete('kategori/(:num)', 'Admin\KategoriController::delete/$1');

        // Master data (master role only)
        $routes->group('master', ['filter' => 'auth:master'], function($routes) {
            // Advanced settings or system configurations can go here
        });
        
        // Reports
        $routes->get('reports', 'Admin\ReportController::index');
        $routes->post('reports/export', 'Admin\ReportController::export');
        
        // Profile
        $routes->get('profile', 'Admin\ProfileController::index');
        $routes->post('profile', 'Admin\ProfileController::update');
    });
});

// Mobile API Routes (Token-based Authentication)
// API Routes (with CORS support)
$routes->group('api', ['filter' => 'cors'], function ($routes) {
    // Handle OPTIONS requests for CORS preflight - match all possible endpoints
    $routes->options('(:any)', 'Api\ApiController::options');
    $routes->options('(:any)/(:num)', 'Api\ApiController::options');
    $routes->options('(:any)/(:any)', 'Api\ApiController::options');
    $routes->options('(:any)/(:num)/(:any)', 'Api\ApiController::options');
    

    // Public API endpoints (no auth required)
    $routes->post('register', 'Api\AuthController::register');
    $routes->post('login', 'Api\AuthController::login');
    
    // Kategori list for dropdowns
    $routes->get('kategori', 'Api\KategoriController::index');
    
    // Protected API endpoints with simplified auth
    $routes->group('', ['filter' => 'apiauth'], function ($routes) {
        // User routes
        $routes->post('logout', 'Api\AuthController::logout');
        $routes->get('user', 'Api\UserController::index');
        $routes->put('user', 'Api\UserController::update');
        
        // Pengaduan routes
        $routes->get('pengaduan', 'Api\PengaduanController::index');
        $routes->get('pengaduan/(:num)', 'Api\PengaduanController::show/$1');
        $routes->post('pengaduan', 'Api\PengaduanController::create');
        $routes->put('pengaduan/(:num)', 'Api\PengaduanController::update/$1');
        $routes->delete('pengaduan/(:num)', 'Api\PengaduanController::delete/$1');
        $routes->post('pengaduan/(:num)/status', 'Api\PengaduanController::addStatus/$1');
        $routes->get('pengaduan/statistic', 'Api\PengaduanStatistic::index'); // <-- Tambahkan ini
    });
});

// CSP Violation Report
$routes->post('csp-violation-report', 'SecurityController::cspViolationReport');
