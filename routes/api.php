<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\imageUserController;
use App\Http\Controllers\CrudArtikel;
use App\Http\Controllers\CrudPohonku;
use App\Http\Controllers\PohonImageController;
use App\Http\Controllers\FaQController;
use App\Http\Controllers\StatistikController;

// Default Laravel version route
$router->get('/', function () use ($router) {
    return $router->app->version();
});

// -------------------- Auth Routes --------------------
$router->post('/register', 'AuthController@register');
$router->post('/login', 'AuthController@login');

$router->group(['middleware' => 'auth:api'], function () use ($router) {
    $router->post('/logout', 'AuthController@logout');
    $router->get('/user', 'AuthController@user');
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
$router->group(['prefix' => 'api/pohonku'], function () use ($router) {
    $router->post('/post', 'CrudPohonku@PostPohonku');
    $router->get('/all', 'CrudPohonku@index');
    $router->get('/get/{id}', 'CrudPohonku@show');
    $router->put('/update/{id}', 'CrudPohonku@update');
    $router->delete('/delete/{id}', 'CrudPohonku@destroy');
    $router->post('/pohonku_id/images', 'CrudPohonku@addImage');
});

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
    $router->post('/post', ['uses' => 'FaQController@PostFaq']);
    $router->get('/all', ['uses' => 'FaQController@GetAllFaq']);
    $router->get('/get/{id}', ['uses' => 'FaQController@GetFaq']);
    $router->put('/update/{id}', ['uses' => 'FaQController@UpdateFaq']);
    $router->delete('/delete/{id}', ['uses' => 'FaQController@DeleteFaq']);
});

// -------------------- Statistik Route --------------------
$router->get('/stats', 'StatistikController@getStats');
