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
    public function __construct()
    {
        // Bisa tambahkan middleware auth jika perlu
        // $this->middleware('auth');
    }

    // Create pohon + image
    public function PostPohonku(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'namaPohon' => 'required|string',
            'jenis_pohon' => 'required|string',
            'tanggal_tanam' => 'required|date',
            'lat' => 'required|numeric',
            'long' => 'required|numeric',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = $request->user(); // user login
            $pohon = Pohonku::create([
                'namaPohon' => $request->namaPohon,
                'jenis_pohon' => $request->jenis_pohon,
                'tanggal_tanam' => $request->tanggal_tanam,
                'lat' => $request->lat,
                'long' => $request->long,
                'user_id' => $user->id,
            ]);

            // Upload image
            $image = $request->file('image');
            $filename = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/images', $filename);

            // Save image record
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

    // Get all pohon (optionally you can restrict to user login)
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

    // Get pohon by ID
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

    // Update pohon data + optional update gambar
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

            // Update pohon fields
            $pohon->update($request->only([
                'namaPohon', 'jenis_pohon', 'tanggal_tanam', 'lat', 'long'
            ]));

            // If new image uploaded, replace old image(s)
            if ($request->hasFile('image')) {
                // Delete old images from storage and DB
                $oldImages = PohonImage::where('pohon_id', $pohon->id)->get();
                foreach ($oldImages as $oldImage) {
                    if (Storage::exists('public/images/' . $oldImage->filename)) {
                        Storage::delete('public/images/' . $oldImage->filename);
                    }
                    $oldImage->delete();
                }

                // Upload new image
                $image = $request->file('image');
                $filename = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/images', $filename);

                // Save new image record
                PohonImage::create([
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

    // Delete pohon + all images
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

            $pohon->forceDelete();

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

    // Upload tambahan gambar ke pohon (post /api/pohonku/images)
    public function store(Request $request)
    {
        $this->validate($request, [
            'pohon_id' => 'required|exists:pohonku,id',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5048'
        ]);

        try {
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

    // Ambil pohon milik user yang login
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
                'message' => 'Error fetching pohon for user',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
