<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pohonku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PohonkuController extends Controller
{
    /**
     * Menambahkan data pohon baru
     */
    public function PostPohonku(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'namaPohon' => 'required|string',
            'jenis_pohon' => 'required|string',
            'tanggal_tanam' => 'required|date',
            'lat' => 'required|numeric',
            'long' => 'required|numeric',
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $pohon = Pohonku::create($request->only([
            'namaPohon', 'jenis_pohon', 'tanggal_tanam', 'lat', 'long', 'user_id'
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Data pohon berhasil ditambahkan',
            'data' => $pohon,
        ], 201);
    }

    /**
     * Mengambil semua data pohon
     */
    public function GetAllPohonku()
    {
        $pohonku = Pohonku::with('user')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Data semua pohon berhasil diambil',
            'data' => [
                'pohonku' => $pohonku,
            ],
        ], 200);
    }

    /**
     * Mengambil data pohon berdasarkan ID
     */
    public function GetPohonku($id)
    {
        $pohon = Pohonku::with('user')->find($id);

        if (!$pohon) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data pohon tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data pohon ditemukan',
            'data' => [
                'pohon' => $pohon,
            ],
        ], 200);
    }

    /**
     * Mengupdate data pohon berdasarkan ID
     */
    public function UpdatePohonku(Request $request, $id)
    {
        $pohon = Pohonku::find($id);

        if (!$pohon) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data pohon tidak ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'namaPohon' => 'sometimes|string',
            'jenis_pohon' => 'sometimes|string',
            'tanggal_tanam' => 'sometimes|date',
            'lat' => 'sometimes|numeric',
            'long' => 'sometimes|numeric',
            'user_id' => 'sometimes|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $pohon->update($request->only([
            'namaPohon', 'jenis_pohon', 'tanggal_tanam', 'lat', 'long', 'user_id'
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Data pohon berhasil diperbarui',
            'data' => $pohon,
        ], 200);
    }

    /**
     * Menghapus data pohon berdasarkan ID
     */
    public function DeletePohonku($id)
    {
        $pohon = Pohonku::find($id);

        if (!$pohon) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data pohon tidak ditemukan',
            ], 404);
        }

        $pohon->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Data pohon berhasil dihapus',
        ], 200);
    }
}
