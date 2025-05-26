<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pohonku; // Sudah diubah menjadi App\Models\Pohonku
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PohonkuController extends Controller // Sudah diubah menjadi PohonkuController
{
    /**
     * Konstruktor untuk menerapkan middleware autentikasi ke semua metode di controller ini,
     * KECUALI metode 'index', 'getAllPohon', dan 'getPohonById'.
     * Ini membuat index, getAllPohon, dan getPohonById bisa diakses tanpa token.
     */
    public function __construct()
    {
        $this->middleware('auth:api')->except(['index', 'getAllPohon', 'getPohonById']);
    }

    /**
     * Mengambil daftar pohon.
     * - Jika diakses dengan token valid: Mengembalikan pohon milik pengguna yang login.
     * - Jika diakses tanpa token: Mengembalikan SEMUA pohon.
     * Ini adalah metode yang menangani GET /api/pohonku.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::guard('api')->user();
        Log::info('Accessing /api/pohonku. Authenticated user ID: ' . ($user ? $user->id : 'NULL (Public Access)'));

        $pohonku = collect(); // Inisialisasi koleksi kosong
        $message = 'Data pohon berhasil diambil.';

        if ($user) {
            // Jika pengguna terautentikasi, ambil hanya pohon milik pengguna tersebut
            $pohonku = Pohonku::where('user_id', $user->id)->get(); // Sudah diubah menjadi Pohonku
            $message = 'Data pohon berhasil diambil untuk pengguna yang sedang login.';
        } else {
            // Jika tidak ada pengguna terautentikasi, ambil semua pohon
            $pohonku = Pohonku::all(); // Sudah diubah menjadi Pohonku
            $message = 'Akses publik: Semua data pohon ditampilkan.';
        }

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => [
                'user' => $user, // Akan null jika tidak terautentikasi
                'pohonku' => $pohonku, // Sudah diubah menjadi 'pohonku' agar konsisten dengan penamaan
            ],
        ], 200);
    }

    /**
     * Mengambil semua data pohon yang ada di database.
     * Endpoint ini dibuat PUBLIK.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllPohon() // Metode baru: getAllPohon
    {
        $pohonku = Pohonku::with('user')->get(); // Sudah diubah menjadi Pohonku

        return response()->json([
            'status' => 'success',
            'message' => 'Semua data pohon berhasil diambil.',
            'data' => ['pohonku' => $pohonku], // Sudah diubah menjadi 'pohonku'
        ], 200);
    }

    /**
     * Mengambil data pohon berdasarkan ID spesifik.
     * Endpoint ini dibuat PUBLIK.
     *
     * @param int $id ID pohon yang ingin diambil.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPohonById($id) // Metode baru: getPohonById
    {
        $pohonku = Pohonku::with('user')->find($id); // Sudah diubah menjadi Pohonku

        if (!$pohonku) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data pohon tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data pohon ditemukan.',
            'data' => ['pohonku' => $pohonku], // Sudah diubah menjadi 'pohonku'
        ], 200);
    }

    /**
     * Memperbarui data pohon berdasarkan ID.
     * Metode ini dilindungi oleh middleware 'auth:api' dan memiliki otorisasi internal.
     *
     * @param Request $request Data request.
     * @param int $id ID pohon yang akan diperbarui.
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePohon(Request $request, $id) // Diubah ke updatePohon (konsisten)
    {
        $pohonku = Pohonku::find($id); // Sudah diubah menjadi Pohonku

        if (!$pohonku) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data pohon tidak ditemukan.',
            ], 404);
        }

        // Cek Otorisasi: Pastikan pengguna yang login memiliki pohon ini
        $user = Auth::guard('api')->user();
        if (!$user || $pohonku->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki izin untuk mengedit pohon ini.',
            ], 403); // Forbidden
        }

        $validator = Validator::make($request->all(), [
            'namaPohon' => 'sometimes|string',
            'jenis_pohon' => 'sometimes|string',
            'tanggal_tanam' => 'sometimes|date',
            'lat' => 'sometimes|numeric',
            'long' => 'sometimes|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $pohonku->update($request->only([
            'namaPohon', 'jenis_pohon', 'tanggal_tanam', 'lat', 'long'
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Data pohon berhasil diperbarui.',
            'data' => $pohonku,
        ], 200);
    }

    /**
     * Menghapus data pohon berdasarkan ID.
     * Metode ini dilindungi oleh middleware 'auth:api' dan memiliki otorisasi internal.
     *
     * @param int $id ID pohon yang akan dihapus.
     * @return \Illuminate\Http\JsonResponse
     */
    public function deletePohon($id) // Diubah ke deletePohon (konsisten)
    {
        $pohonku = Pohonku::find($id); // Sudah diubah menjadi Pohonku

        if (!$pohonku) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data pohon tidak ditemukan.',
            ], 404);
        }

        // Cek Otorisasi: Pastikan pengguna yang login memiliki pohon ini
        $user = Auth::guard('api')->user();
        if (!$user || $pohonku->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki izin untuk menghapus pohon ini.',
            ], 403); // Forbidden
        }

        $pohonku->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Data pohon berhasil dihapus.',
        ], 200);
    }
}
