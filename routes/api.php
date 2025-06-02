<?php

use Laravel\Lumen\Routing\Router;

/** @var Router $router */

// Default Lumen version route
$router->get('/', function () use ($router) {
    return $router->app->version();
});

// -------------------- Auth Routes --------------------
$router->post('/auth/register', 'AuthController@register');
$router->post('/auth/login', 'AuthController@login');

$router->group(['middleware' => 'auth:api'], function () use ($router) {
    $router->get('/auth/me', 'AuthController@me');
    $router->post('/auth/logout', 'AuthController@logout');
});

// -------------------- User Image Routes --------------------
$router->group(['prefix' => 'api/user'], function () use ($router) {
    $router->get('/images/all', 'imageUserController@index');
    $router->post('/images/post', 'imageUserController@store');
    $router->get('/images/get/{id}', 'imageUserController@show');
    $router->post('/images/post/{id}', 'imageUserController@update');
    $router->delete('/images/delete/{id}', 'imageUserController@destroy');
});

// Serve uploaded user images
$router->get('storage/images/{filename}', function ($filename) {
    $path = storage_path('app/public/images/' . $filename);
    if (!file_exists($path)) {
        abort(404);
    }
    return response()->file($path);
});

// -------------------- Artikel Routes --------------------
$router->group(['prefix' => 'api/artikel'], function () use ($router) {
    $router->post('/post', 'CrudArtikel@store');
    $router->get('/all', 'CrudArtikel@index');
    $router->get('/get/{id}', 'CrudArtikel@show');
    $router->put('/update/{id}', 'CrudArtikel@update');
    $router->delete('/delete/{id}', 'CrudArtikel@destroy');
    $router->post('/artikels/{artikelId}/images', 'CrudArtikel@addImage');
});

// -------------------- Pohonku Routes --------------------
$router->group(['prefix' => 'api/pohonku', 'middleware' => 'auth:api'], function () use ($router) {
    $router->post('/post', 'CrudPohonku@PostPohonku');          // Tambah pohon + gambar
    $router->get('/all', 'CrudPohonku@index');                  // Lihat semua pohon (admin & user filtered)
    $router->get('/get/{id}', 'CrudPohonku@show');              // Detail pohon
    $router->put('/update/{id}', 'CrudPohonku@update');         // Update pohon
    $router->delete('/delete/{id}', 'CrudPohonku@destroy');     // Hapus pohon
    $router->post('/images', 'CrudPohonku@store');              // Tambah gambar ke pohon (pohon_id + image)
    $router->get('/my', 'CrudPohonku@getPohonByUser');          // Pohon milik user login
});
$router->get('/api/pohonku/map', 'CrudPohonku@getForMap');

// -------------------- Pohon Image Routes --------------------
$router->group(['prefix' => 'api/pohon-image'], function () use ($router) {
    $router->get('/', 'PohonImageController@index');
    $router->post('/', 'PohonImageController@store');
    $router->get('/{id}', 'PohonImageController@show');
    $router->put('/{id}', 'PohonImageController@update');
    $router->delete('/{id}', 'PohonImageController@destroy');
});

// -------------------- FAQ Routes --------------------
$router->group(['prefix' => 'api/faq'], function () use ($router) {
    $router->post('/post', 'FaQController@PostFaq');
    $router->get('/all', 'FaQController@GetAllFaq');
    $router->get('/get/{id}', 'FaQController@GetFaq');
    $router->put('/update/{id}', 'FaQController@UpdateFaq');
    $router->delete('/delete/{id}', 'FaQController@DeleteFaq');
});

// -------------------- Statistik Route --------------------
$router->get('/stats', 'StatistikController@getStats');

// -------------------- User Profile Routes --------------------
$router->group(['prefix' => 'api/user', 'middleware' => 'auth:api'], function () use ($router) {
    $router->get('/me', 'UserController@getUserData');
    $router->post('/update', 'UserController@updateProfile');
    $router->delete('/delete', 'UserController@deleteUser');
});

// -------------------- Admin User Management --------------------
$router->group(['prefix' => 'api/users', 'middleware' => 'auth:api'], function () use ($router) {
    $router->get('/', 'UserManagementController@index');
    $router->put('/update/{id}', 'UserManagementController@update');
    $router->delete('/delete/{id}', 'UserManagementController@destroy');
});

