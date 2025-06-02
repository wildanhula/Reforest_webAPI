<?php

namespace App\Http\Controllers;

use App\Models\Pohonku;
use App\Models\PohonImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CrudPohonku extends Controller
{
    // Create pohon + image
public function PostPohonku(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'namaPohon' => 'required|string',
            'jenis_pohon' => 'required|string',
            'tanggal_tanam' => 'required|date',
            'lat' => 'required|numeric',
            'long' => 'required|numeric',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5048',
            'target_user_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = $request->user();
            $assignedUserId = $user->id;

            // Jika admin, bisa set target_user_id
            if ($user->role === 'admin' && $request->filled('target_user_id')) {
                $assignedUserId = $request->target_user_id;
            }

            $pohon = Pohonku::create([
                'namaPohon' => $request->namaPohon,
                'jenis_pohon' => $request->jenis_pohon,
                'tanggal_tanam' => $request->tanggal_tanam,
                'lat' => $request->lat,
                'long' => $request->long,
                'user_id' => $assignedUserId,
                'status' => 'aktif',
            ]);

            $image = $request->file('image');
            $filename = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/images', $filename);

            $imageRecord = PohonImage::create([
                'pohon_id' => $pohon->id,
                'filename' => $filename,
                'original_name' => $image->getClientOriginalName(),
                'mime_type' => $image->getMimeType(),
                'file_size' => $image->getSize()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Data pohon dan gambar berhasil ditambahkan',
                'data' => [
                    'pohon' => $pohon,
                    'image' => $imageRecord
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error creating pohon or uploading image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get all pohon
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $query = Pohonku::with('images')->latest();

            if ($user->role === 'user') {
                $query->where('user_id', $user->id);
            }

            return response()->json([
                'success' => true,
                'data' => $query->get()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching pohons',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // New method: Get pohon data for map (minimal data, accessible for all logged-in users)
public function getForMap(Request $request)
{
    try {
        $pohons = Pohonku::with('user:id,username') // Ambil hanya kolom id dan username dari user
            ->select('id', 'namaPohon', 'lat', 'long', 'user_id')
            ->get()
            ->map(function ($pohon) {
                return [
                    'id' => $pohon->id,
                    'namaPohon' => $pohon->namaPohon,
                    'lat' => $pohon->lat,
                    'long' => $pohon->long,
                    'username' => optional($pohon->user)->username,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $pohons
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Gagal mengambil data pohon untuk peta',
            'error' => $e->getMessage()
        ], 500);
    }
}


    // Get pohon by ID
    public function show(Request $request, $id)
    {
        try {
            $pohon = Pohonku::with('images')->findOrFail($id);
            $user = $request->user();

            if ($user->role === 'user' && $pohon->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $pohon
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pohon tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching pohon',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Update pohon
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'namaPohon' => 'sometimes|string',
            'jenis_pohon' => 'sometimes|string',
            'tanggal_tanam' => 'sometimes|date',
            'lat' => 'sometimes|numeric',
            'long' => 'sometimes|numeric',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:5048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $pohon = Pohonku::findOrFail($id);
            $user = $request->user();

            if ($user->role === 'user' && $pohon->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak'
                ], 403);
            }

            $pohon->update($request->only([
                'namaPohon', 'jenis_pohon', 'tanggal_tanam', 'lat', 'long'
            ]));

            if ($request->hasFile('image')) {
                $oldImages = PohonImage::where('pohon_id', $pohon->id)->get();
                foreach ($oldImages as $oldImage) {
                    Storage::delete('public/images/' . $oldImage->filename);
                    $oldImage->delete();
                }

                $image = $request->file('image');
                $filename = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/images', $filename);

                PohonImage::create([
                    'pohon_id' => $pohon->id,
                    'filename' => $filename,
                    'original_name' => $image->getClientOriginalName(),
                    'mime_type' => $image->getMimeType(),
                    'file_size' => $image->getSize()
                ]);
            }

            $pohon->refresh()->load('images');

            return response()->json([
                'success' => true,
                'message' => 'Pohon berhasil diupdate',
                'data' => $pohon
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pohon tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating pohon',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Delete pohon + gambar
    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();
            $pohon = Pohonku::findOrFail($id);

            if ($user->role === 'user' && $pohon->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak'
                ], 403);
            }

            DB::beginTransaction();

            $images = PohonImage::where('pohon_id', $pohon->id)->get();
            foreach ($images as $image) {
                Storage::delete('public/images/' . $image->filename);
                $image->delete();
            }

            $pohon->forceDelete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pohon dan gambar berhasil dihapus'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Pohon tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus pohon',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Tambah gambar ke pohon
    public function store(Request $request)
    {
        $this->validate($request, [
            'pohon_id' => 'required|exists:pohonku,id',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5048'
        ]);

        try {
            $pohon = Pohonku::findOrFail($request->pohon_id);
            $user = $request->user();

            if ($user->role === 'user' && $pohon->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak'
                ], 403);
            }

            $image = $request->file('image');
            $filename = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/images', $filename);

            $imageRecord = PohonImage::create([
                'pohon_id' => $pohon->id,
                'filename' => $filename,
                'original_name' => $image->getClientOriginalName(),
                'mime_type' => $image->getMimeType(),
                'file_size' => $image->getSize()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Gambar berhasil ditambahkan',
                'data' => $imageRecord
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan gambar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get pohon milik user yang login
    public function getPohonByUser(Request $request)
    {
        try {
            $user = $request->user();

            $pohons = Pohonku::with('images')
                ->where('user_id', $user->id)
                ->latest()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $pohons
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pohon user',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
