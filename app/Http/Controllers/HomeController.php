<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PohonkuController;
use App\Models\Pohonku;
use App\Models\Artikel;

class HomeController extends Controller
{
    public function __construct()
    {
         $this->middleware('auth:api')->only('index');
    }

    public function index()
    {
        $artikel = Artikel::all();
        $pohonku = pohonku::all(); // Mengambil pohonku dari user yang sedang login
        return response()->json([
            'status' => 'success',
            'data' => [
                'artikel' => $artikel,
                'pohonku' => $pohonku,
            ],
        ]);
    }

    public function landing()
    {

        $artikel = Artikel::all();  

        return response()->json([
            'status' => 'success',
            'data' => [
                'artikel' => $artikel,
            ],
        ]);
    }
}

