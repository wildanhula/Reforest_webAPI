<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\artikel;
use Illuminate\Http\Request;

class ArtikelController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function PostArtikel(Request $request)
    {
        $artikel = artikel::create([
            'title' => $request->title,
            'isi' => $request->isi,
            'author' => $request->author,
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Artikel berhasil di post',
            'data' => $artikel,
        ], 201);
    }

    public function GetAllArtikel()
    {
        $artikel = artikel::all();

        return response()->json([
            'status' => 'Success',
            'message' => 'all artikel grabbed',
            'data' => [
                'artikel' => $artikel,
            ]
        ], 200);
    }


    public function getArtikel($id){
        $artikel = artikel::find($id);

         return response()->json([
            'status' => 'Success',
            'message' => 'artikel tersedia taraaa',
            'data' => [
                'artikel' => $artikel,
            ]
        ], 200);

    }


    /**
     * Update the specified artikel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function UpdateArtikel(Request $request, $id)
    {
        $artikel = artikel::find($id);
        
        if (!$artikel) {
            return response()->json([
                'status' => 'error',
                'message' => 'Artikel tidak ditemukan'
            ], 404);
        }

        $artikel->update([
            'title' => $request->title ?? $artikel->title,
            'isi' => $request->isi ?? $artikel->isi,
            'author' => $request->author ?? $artikel->author,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Artikel berhasil diupdate',
            'data' => $artikel
        ], 200);
    }

    /**
     * Remove the specified artikel.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function DeleteArtikel($id)
    {
        $artikel = artikel::find($id);
        
        if (!$artikel) {
            return response()->json([
                'status' => 'error',
                'message' => 'Artikel tidak ditemukan'
            ], 404);
        }

        $artikel->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Artikel berhasil dihapus'
        ], 200);
    }
}