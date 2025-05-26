<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pohonku;
use App\Models\PohonImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Lumen\Routing\Controller;
use Illuminate\Support\Facades\DB;
class CrudPohonku extends Controller
{
    public function __construct()
    {
        // Constructor can be used for middleware if needed
    }

    public function PostPohonku(Request $request)
    {
        try {
            // Validate tree data and image
            $validator = Validator::make($request->all(), [
                'namaPohon' => 'required|string',
                'jenis_pohon' => 'required|string',
                'tanggal_tanam' => 'required|date',
                'lat' => 'required|numeric',
                'long' => 'required|numeric',
                'user_id' => 'required|exists:users,id',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5048' // Max 5MB
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Create pohonku
            $pohon = Pohonku::create($request->only([
                'namaPohon', 'jenis_pohon', 'tanggal_tanam', 'lat', 'long', 'user_id'
            ]));

            // Handle image upload
            $image = $request->file('image');
            $filename = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/images', $filename);

            // Create image record
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

    public function index()
    {
        try {
            $pohons = Pohonku::with('images')->latest()->get();
            return response()->json([
                'success' => true,
                'data' => $pohons
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching pohons',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $pohon = Pohonku::with('images')->findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $pohon
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pohon not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching pohon',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'namaPohon' => 'sometimes|string',
                'jenis_pohon' => 'sometimes|string',
                'tanggal_tanam' => 'sometimes|date',
                'lat' => 'sometimes|numeric',
                'long' => 'sometimes|numeric',
                'user_id' => 'sometimes|exists:users,id',
                'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:5048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $pohon = Pohonku::findOrFail($id);
            $pohon->update($request->only([
                'namaPohon', 'jenis_pohon', 'tanggal_tanam', 'lat', 'long', 'user_id'
            ]));

            if ($request->hasFile('image')) {
                // Delete old images
                $oldImages = PohonImage::where('pohon_id', $pohon->id)->get();
                foreach ($oldImages as $oldImage) {
                    Storage::delete('public/images/' . $oldImage->filename);
                    $oldImage->delete();
                }

                // Upload new image
                $image = $request->file('image');
                $filename = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/images', $filename);

                // Create new image record
                $imageRecord = PohonImage::create([
                    'pohon_id' => $pohon->id,
                    'filename' => $filename,
                    'original_name' => $image->getClientOriginalName(),
                    'mime_type' => $image->getMimeType(),
                    'file_size' => $image->getSize()
                ]);
            }

            $pohon->refresh();
            $pohon->load('images');

            return response()->json([
                'success' => true,
                'message' => 'Pohon updated successfully',
                'data' => $pohon
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pohon not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating pohon',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // public function destroy($id)
    // {
    //     try {
    //         $pohon = Pohonku::findOrFail($id);

    //         // Delete associated images
    //         $images = PohonImage::where('pohon_id', $pohon->id)->get();
    //         foreach ($images as $image) {
    //             Storage::delete('public/images/' . $image->filename);
    //             $image->delete();
    //             $pohon->delete();
    //         }

    //         $pohon->delete();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Pohon and associated images deleted successfully'
    //         ]);
    //     } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Pohon not found'
    //         ], 404);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Error deleting pohon',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

  public function destroy($id)
{
    try {
        DB::beginTransaction();

        $pohon = Pohonku::findOrFail($id);

        $images = PohonImage::where('pohon_id', $pohon->id)->get();
        foreach ($images as $image) {
            if (Storage::exists('public/images/' . $image->filename)) {
                Storage::delete('public/images/' . $image->filename);
            }
            $image->delete();
        }

        $pohon->forceDelete(); // Ganti delete() dengan forceDelete()

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Pohon dan semua gambar berhasil dihapus'
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
            'message' => 'Terjadi kesalahan saat menghapus pohon',
            'error' => $e->getMessage()
        ], 500);
    }
}
    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'pohon_id' => 'required|exists:pohonku,id',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5048'
            ]);

            $image = $request->file('image');
            $filename = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/images', $filename);

            $imageRecord = PohonImage::create([
                'pohon_id' => $request->pohon_id,
                'filename' => $filename,
                'original_name' => $image->getClientOriginalName(),
                'mime_type' => $image->getMimeType(),
                'file_size' => $image->getSize()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'data' => $imageRecord
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error uploading image',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}