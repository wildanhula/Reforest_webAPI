<?php

namespace App\Http\Controllers;

use App\Models\PohonImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PohonImageController extends Controller
{
    /**
     * Menampilkan semua data gambar pohon
     */
    public function index()
    {
        try {
            $images = PohonImage::with('pohonku')->latest()->get();
            return response()->json([
                'success' => true,
                'data' => $images
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching images',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menambahkan data gambar pohon baru
     */
    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'pohon_id' => 'required|exists:pohonku,id',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5048' // Maks 5MB
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

    /**
     * Menampilkan data gambar pohon berdasarkan ID
     */
    public function show($id)
    {
        try {
            $image = PohonImage::with('pohonku')->findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $image
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Image not found'
            ], 404);
        }
    }

    /**
     * Mengupdate data gambar pohon berdasarkan ID
     */
    public function update(Request $request, $id)
    {
        try {
            $image = PohonImage::findOrFail($id);

            $this->validate($request, [
                'pohon_id' => 'nullable|exists:pohonku,id',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5048'
            ]);

            if ($request->has('pohon_id')) {
                $image->pohon_id = $request->pohon_id;
            }

            if ($request->hasFile('image')) {
                Storage::delete('public/images/' . $image->filename);

                $newImage = $request->file('image');
                $filename = time() . '_' . Str::random(10) . '.' . $newImage->getClientOriginalExtension();
                $newImage->storeAs('public/images', $filename);

                $image->filename = $filename;
                $image->original_name = $newImage->getClientOriginalName();
                $image->mime_type = $newImage->getMimeType();
                $image->file_size = $newImage->getSize();
            }

            $image->save();

            return response()->json([
                'success' => true,
                'message' => 'Image updated successfully',
                'data' => $image
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menghapus data gambar pohon berdasarkan ID
     */
    public function destroy($id)
    {
        try {
            $image = PohonImage::findOrFail($id);
            Storage::delete('public/images/' . $image->filename);
            $image->delete();

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting image',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}