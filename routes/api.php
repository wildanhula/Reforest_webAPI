<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\imageUserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
//authcontroller
$router->post('/register', 'AuthController@register');
$router->post('/login', 'AuthController@login');

$router->group(['middleware' => 'auth:api'], function () use ($router) {
    $router->post('/logout', 'AuthController@logout');
    $router->get('/user', 'AuthController@user');
});
//image User controller<?php

$router->get('/', function () use ($router) {
    return $router->app->version();
});

// API Routes
$router->group(['prefix' => 'api'], function () use ($router) {
    $router->get('images', 'imageUserController@index');
    $router->post('images', 'imageUserController@store');
    $router->get('images/{id}', 'imageUserController@show');
    $router->post('images/{id}', 'imageUserController@update'); // Using POST for file upload
    $router->delete('images/{id}', 'imageUserController@destroy');
});

// Serve uploaded images
$router->get('storage/images/{filename}', function ($filename) {
    $path = storage_path('app/public/images/' . $filename);
    
    if (!file_exists($path)) {
        abort(404);
    }
    
    return response()->file($path);
});
//artikel
    $router->group(['prefix' => 'artikel'], function () use ($router) {
        $router->post('/post', ['uses' => 'ArtikelController@PostArtikel']);
        $router->get('/all', ['uses' => 'ArtikelController@GetAllArtikel']);
        $router->get('/get/{id}', ['uses' => 'ArtikelController@GetArtikel']);
        $router->put('/update/{id}', ['uses' => 'ArtikelController@UpdateArtikel']);
        $router->delete('/delete/{id}', ['uses' => 'ArtikelController@DeleteArtikel']);
    });

//artikel-img
$router->group(['prefix' => 'artikel-images'], function () use ($router) {
    $router->get('/', 'ArtikelImageController@index');       // Menampilkan semua gambar
    $router->post('/', 'ArtikelImageController@store');      // Menyimpan gambar baru
    $router->get('{id}', 'ArtikelImageController@show');     // Menampilkan detail gambar
    $router->put('{id}', 'ArtikelImageController@update');   // Update gambar
    $router->delete('{id}', 'ArtikelImageController@destroy'); // Hapus gambar
});

//pohonku api
$router->group(['prefix' => 'api/pohonku'], function () use ($router) {
    $router->post('/', 'PohonkuController@PostPohonku');
    $router->get('/', 'PohonkuController@GetAllPohonku');
    $router->get('/{id}', 'PohonkuController@GetPohonku');
    $router->put('/{id}', 'PohonkuController@UpdatePohonku');
    $router->delete('/{id}', 'PohonkuController@DeletePohonku');
});
//pohon img
$router->group(['prefix' => 'api/pohon-image'], function () use ($router) {
    $router->get('/', 'PohonImageController@index');
    $router->post('/', 'PohonImageController@store');
    $router->get('/{id}', 'PohonImageController@show');
    $router->put('/{id}', 'PohonImageController@update');
    $router->delete('/{id}', 'PohonImageController@destroy');
});
//FAQ
$router->group(['prefix' => 'api/faq'], function () use ($router) {
    $router->post('/', ['uses' => 'FaQController@PostFaq']);
    $router->get('/', ['uses' => 'FaQController@GetAllFaq']);
    $router->get('/{id}', ['uses' => 'FaQController@GetFaq']);
    $router->put('/{id}', ['uses' => 'FaQController@UpdateFaq']);
    $router->delete('/{id}', ['uses' => 'FaQController@DeleteFaq']);
});
