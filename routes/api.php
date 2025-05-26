<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\imageUserController;
use App\Http\Controllers\CrudArtikel;
use App\Http\Controllers\CrudPohonku;

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
$router->group(['prefix' => 'api/user'], function () use ($router) {
    $router->get('/images/all', 'imageUserController@index');
    $router->post('/images/post', 'imageUserController@store');
    $router->get('/images/get/{id}', 'imageUserController@show');
    $router->post('/images/post/{id}', 'imageUserController@update'); // Using POST for file upload
    $router->delete('/images/delete/{id}', 'imageUserController@destroy');
});

// Serve uploaded images
$router->get('storage/images/{filename}', function ($filename) {
    $path = storage_path('app/public/images/' . $filename);
    
    if (!file_exists($path)) {
        abort(404);
    }
    
    return response()->file($path);
});
//artikel yang baru
$router->group(['prefix' => 'api/artikel'], function () use ($router) {
    // Create a new artikel with image
    $router->post('/post', 'CrudArtikel@store');

    // Get all artikels with their images
    $router->get('/all', 'CrudArtikel@index');

    // Get a single artikel with its images
    $router->get('/get/{id}', 'CrudArtikel@show');

    // Update an artikel
    $router->put('/update/{id}', 'CrudArtikel@update');

    // Delete an artikel and its images
    $router->delete('/delete/{id}', 'CrudArtikel@destroy');

    // Add a new image to an existing artikel
    $router->post('/artikels/{artikelId}/images', 'CrudArtikel@addImage');
});
    
    
    

//artikel-img
$router->group(['prefix' => 'api/artikel-image'], function () use ($router) {
    $router->get('/all', 'ArtikelImageController@index');       // Menampilkan semua gambar
    $router->post('/post', 'ArtikelImageController@store');      // Menyimpan gambar baru
    $router->get('/get/{id}', 'ArtikelImageController@show');     // Menampilkan detail gambar
    $router->put('/update/{id}', 'ArtikelImageController@update');   // Update gambar
    $router->delete('/delete/{id}', 'ArtikelImageController@destroy'); // Hapus gambar
});


































//pohonku API DONE
$router->group(['prefix' => 'api/pohonku'], function () use ($router) {
    $router->post('/post', 'CrudPohonku@PostPohonku');
    $router->get('/all', 'CrudPohonku@index');
    $router->get('/get/{id}', 'CrudPohonku@show');
    $router->put('/update/{id}', 'CrudPohonku@update');
    $router->delete('/delete/{id}', 'CrudPohonku@destroy');
    $router->post('/pohonku_id/images', 'CrudPohonku@addImage');
});







//pohon img
$router->group(['prefix' => 'api/pohon-image'], function () use ($router) {
    $router->get('/post', 'PohonImageController@index');
    $router->post('/all', 'PohonImageController@store');
    $router->get('/get/{id}', 'PohonImageController@show');
    $router->put('/update/{id}', 'PohonImageController@update');
    $router->delete('/delete{id}', 'PohonImageController@destroy');
});
//FAQ
$router->group(['prefix' => 'api/faq'], function () use ($router) {
    $router->post('/post', ['uses' => 'FaQController@PostFaq']);
    $router->get('/all', ['uses' => 'FaQController@GetAllFaq']);
    $router->get('/get/{id}', ['uses' => 'FaQController@GetFaq']);
    $router->put('/update/{id}', ['uses' => 'FaQController@UpdateFaq']);
    $router->delete('/delete/{id}', ['uses' => 'FaQController@DeleteFaq']);
});