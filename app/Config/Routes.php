<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

/**
 * API Routes for Product Management
 * Direct routes without api/v1 prefix
 */

// OPTIONS routes for CORS preflight
$routes->options('(:any)', function() {
    return service('response')
        ->setHeader('Access-Control-Allow-Origin', 'http://localhost:9000')
        ->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS, PATCH')
        ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin')
        ->setHeader('Access-Control-Allow-Credentials', 'true')
        ->setStatusCode(200)
        ->setBody('');
});

// Auth routes (no authentication required)
$routes->post('register', 'AuthController::register');
$routes->post('login', 'AuthController::login');
$routes->post('logout', 'AuthController::logout');

// Protected routes (JWT required)
$routes->get('me', 'AuthController::me', ['filter' => 'auth']);

// User routes (admin only)
$routes->get('users', 'UserController::index', ['filter' => 'auth']);
$routes->delete('users/(:alphanum)', 'UserController::delete/$1', ['filter' => 'auth']);

// Product routes
$routes->group('products', function($routes) {
    $routes->get('/', 'ProductController::index');           // GET /products
    $routes->get('(:num)', 'ProductController::show/$1');    // GET /products/123
    $routes->post('/', 'ProductController::create');         // POST /products (Admin only)
    $routes->put('(:num)', 'ProductController::update/$1');  // PUT /products/123 (Admin only) 
    $routes->patch('(:num)', 'ProductController::update/$1'); // PATCH /products/123 (Admin only)
    $routes->delete('(:num)', 'ProductController::delete/$1'); // DELETE /products/123 (Admin only)
});

// Cart routes (authenticated users only)
$routes->group('cart', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'CartController::index');              // GET /cart - Lihat isi keranjang
    $routes->post('/', 'CartController::create');            // POST /cart - Add item to cart
    $routes->put('(:num)', 'CartController::update/$1');     // PUT /cart/1 - Update cart item
    $routes->delete('(:num)', 'CartController::delete/$1');  // DELETE /cart/1 - Remove cart item
    $routes->delete('clear', 'CartController::clear');       // DELETE /cart/clear - Clear cart
});

// Order routes (authenticated users)
$routes->group('orders', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'OrderController::index');             // GET /orders - History transaksi
    $routes->get('stats', 'OrderController::getStats');      // GET /orders/stats (Admin only) - must be before (:alphanum)
    $routes->get('(:alphanum)', 'OrderController::show/$1');  // GET /orders/ABC123DEF456 - Detail order hash
    $routes->patch('(:alphanum)', 'OrderController::updateStatus/$1'); // PATCH /orders/ABC123DEF456 - Update status (Admin)
});

// Checkout route (authenticated users)
$routes->post('checkout', 'OrderController::checkout', ['filter' => 'auth']); // POST /checkout

// Admin routes (admin only) - dengan AdminFilter
$routes->group('admin', ['filter' => 'adminauth'], function($routes) {
    $routes->get('users', 'AdminController::getUsers');           // GET /admin/users
    $routes->get('users/(:num)', 'AdminController::getUser/$1');  // GET /admin/users/1
    $routes->put('users/(:num)', 'AdminController::updateUser/$1'); // PUT /admin/users/1
    $routes->delete('users/(:num)', 'AdminController::deleteUser/$1'); // DELETE /admin/users/1
    $routes->get('dashboard', 'AdminController::getDashboardStats'); // GET /admin/dashboard
});

// Future: Category routes can be added here
// $routes->group('categories', function($routes) {
//     $routes->get('/', 'CategoryController::index');
//     $routes->post('/', 'CategoryController::create');
//     // ... other category routes
// });
