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

// Mobile API Routes (JWT-based Authentication)
$routes->group('api', ['filter' => 'cors_custom'], function($routes) {
    // Public API routes (no auth required)
    $routes->post('auth/register', 'Api\AuthController::register');
    $routes->post('auth/login', 'Api\AuthController::login');
    $routes->post('auth/forgot-password', 'Api\AuthController::forgotPassword');
    $routes->post('auth/reset-password', 'Api\AuthController::resetPassword');
    
    // Master data (public access for dropdown data)
    $routes->get('instansi', 'Api\MasterDataController::instansi');
    $routes->get('kategori', 'Api\MasterDataController::kategori');
    
    // Protected API routes (JWT auth required)
    $routes->group('', ['filter' => 'jwt_auth'], function($routes) {
        // Auth management
        $routes->post('auth/refresh', 'Api\AuthController::refresh');
        $routes->post('auth/logout', 'Api\AuthController::logout');
        $routes->get('auth/profile', 'Api\AuthController::profile');
        $routes->post('auth/profile', 'Api\AuthController::updateProfile');
        $routes->post('auth/change-password', 'Api\AuthController::changePassword');
        
        // Pengaduan management
        $routes->get('pengaduan', 'Api\PengaduanController::index');
        $routes->post('pengaduan', 'Api\PengaduanController::store');
        $routes->get('pengaduan/(:segment)', 'Api\PengaduanController::show/$1');
        $routes->put('pengaduan/(:segment)', 'Api\PengaduanController::update/$1');
        $routes->delete('pengaduan/(:segment)', 'Api\PengaduanController::delete/$1');
        $routes->post('pengaduan/(:segment)/comment', 'Api\PengaduanController::addComment/$1');
        $routes->post('pengaduan/(:segment)/cancel', 'Api\PengaduanController::cancelPengaduan/$1');
        
        // File upload
        $routes->post('upload', 'Api\FileController::upload');
        
        // Notifications
        $routes->get('notifications', 'Api\NotificationController::index');
        $routes->post('notifications/(:num)/read', 'Api\NotificationController::markAsRead/$1');
        $routes->post('notifications/read-all', 'Api\NotificationController::markAllAsRead');
    });
});

// CSP Violation Report
$routes->post('csp-violation-report', 'SecurityController::cspViolationReport');
