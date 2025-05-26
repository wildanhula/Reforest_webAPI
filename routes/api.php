    <?php

    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\AuthController;
    use App\Http\Controllers\HomeController;
    use App\Http\Controllers\ArtikelController;
    use App\Http\Controllers\PohonkuController; // Sudah diubah menjadi PohonkuController

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

    // Route publik (tidak memerlukan autentikasi)
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    
    Route::get('/artikel/all', [ArtikelController::class, 'GetAllArtikel']);
    Route::get('/artikel/{id}', [ArtikelController::class, 'getArtikel']);



    Route::get('/home', [HomeController::class, 'index']);
    Route::get('/pohonku/all', [PohonkuController::class, 'getAllPohon']); 
    Route::get('/pohonku/{id}', [PohonkuController::class, 'getPohonById']); 
    Route::get('/pohonku', [PohonkuController::class, 'index']); 
    Route::put('/pohonku/{id}', [PohonkuController::class, 'updatePohon']); 
    Route::delete('/pohonku/{id}', [PohonkuController::class, 'deletePohon']); 

    Route::middleware('auth:api')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
    });
    
    
    Route::get('/test', function() {
        return response()->json([
            'message'=>'API jalan'
        ]);
    });
    