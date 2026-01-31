<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

/**
 * API Routes for Product Management
 * Menggunakan route group untuk mengorganisir API routes
 */
$routes->group('api/v1', ['namespace' => 'App\Controllers'], function($routes) {
    
    // Auth routes (no authentication required)
    $routes->post('register', 'AuthController::register');
    $routes->post('login', 'AuthController::login');

    // Protected routes (JWT required)
    $routes->get('me', 'Me::index', ['filter' => 'auth']);

    // User routes (admin only)
    $routes->get('users', 'UserController::index', ['filter' => 'auth']);

    // Product routes
    $routes->group('products', function($routes) {
        $routes->get('/', 'ProductController::index');           // GET /api/v1/products
        $routes->get('(:num)', 'ProductController::show/$1');    // GET /api/v1/products/123
        $routes->post('/', 'ProductController::create');         // POST /api/v1/products (Admin only)
        $routes->put('(:num)', 'ProductController::update/$1');  // PUT /api/v1/products/123 (Admin only) 
        $routes->patch('(:num)', 'ProductController::update/$1'); // PATCH /api/v1/products/123 (Admin only)
        $routes->delete('(:num)', 'ProductController::delete/$1'); // DELETE /api/v1/products/123 (Admin only)
    });

    // Admin routes (admin only) - dengan AdminFilter
    $routes->group('admin', ['filter' => 'adminauth'], function($routes) {
        $routes->get('users', 'AdminController::getUsers');           // GET /admin/users
        $routes->get('users/(:num)', 'AdminController::getUser/$1');  // GET /admin/users/1
        $routes->put('users/(:num)', 'AdminController::updateUser/$1'); // PUT /admin/users/1
        $routes->delete('users/(:num)', 'AdminController::deleteUser/$1'); // DELETE /admin/users/1
        $routes->get('dashboard', 'AdminController::getDashboardStats'); // GET /admin/dashboard
    });

    $routes->get('users/usernames', 'AuthController::getUsernames', ['filter' => 'auth']);

    // Future: Category routes can be added here
    // $routes->group('categories', function($routes) {
    //     $routes->get('/', 'CategoryController::index');
    //     $routes->post('/', 'CategoryController::create');
    //     // ... other category routes
    // });
});
