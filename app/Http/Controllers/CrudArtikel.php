<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use App\Models\ArtikelImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Lumen\Routing\Controller;
use Illuminate\Support\Facades\DB;

class CrudArtikel extends Controller
{
    public function __construct()
    {
        // Constructor can be used for middleware if needed
    }

    // Create a new artikel with image
    public function store(Request $request)
    {
        try {
            // Validate article data and image
            $this->validate($request, [
                'title' => 'required|string|max:255',
                'isi' => 'required|string',
                'author' => 'required|string',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5048'
            ]);

            // Create artikel
            $artikel = Artikel::create([
                'title' => $request->title,
                'isi' => $request->isi,
                'author' => $request->author,
            ]);

            // Handle image upload
            $image = $request->file('image');
            $filename = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/images', $filename);

            // Create image record
            $imageRecord = ArtikelImage::create([
                'artikel_id' => $artikel->id,
                'filename' => $filename,
                'original_name' => $image->getClientOriginalName(),
                'mime_type' => $image->getMimeType(),
                'file_size' => $image->getSize()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Artikel dan gambar berhasil diunggah',
                'data' => [
                    'artikel' => $artikel,
                    'image' => $imageRecord
                ]
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
                'message' => 'Error creating artikel or uploading image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Read all artikels with their images
    public function index()
    {
        try {
            $artikels = Artikel::with('images')->latest()->get();
            return response()->json([
                'success' => true,
                'data' => $artikels
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching artikels',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Read a single artikel with its images
    public function show($id)
    {
        try {
            $artikel = Artikel::with('images')->findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $artikel
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Artikel not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching artikel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Update an artikel
public function update(Request $request, $id)
{
    try {
        $this->validate($request, [
            'title' => 'sometimes|string|max:255',
            'isi' => 'sometimes|string',
            'author' => 'sometimes|string',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:5048'
        ]);

        $artikel = Artikel::findOrFail($id);

        // Manual update field agar bisa bypass mass-assignment
        if ($request->has('title')) {
            $artikel->title = $request->title;
        }
        if ($request->has('isi')) {
            $artikel->isi = $request->isi;
        }
        if ($request->has('author')) {
            $artikel->author = $request->author;
        }

        $artikel->save();

        if ($request->hasFile('image')) {
            // Delete old images
            $oldImages = ArtikelImage::where('artikel_id', $artikel->id)->get();
            foreach ($oldImages as $oldImage) {
                Storage::delete('public/images/' . $oldImage->filename);
                $oldImage->delete();
            }

            // Upload new image
            $image = $request->file('image');
            $filename = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/images', $filename);

            // Create new image record
            $imageRecord = ArtikelImage::create([
                'artikel_id' => $artikel->id,
                'filename' => $filename,
                'original_name' => $image->getClientOriginalName(),
                'mime_type' => $image->getMimeType(),
                'file_size' => $image->getSize()
            ]);
        }

        $artikel->refresh();
        $artikel->load('images');

        return response()->json([
            'success' => true,
            'message' => 'Artikel updated successfully',
            'data' => $artikel
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validation error',
            'errors' => $e->errors()
        ], 422);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Artikel not found'
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error updating artikel',
            'error' => $e->getMessage()
        ], 500);
    }
}


    // Delete an artikel and its images
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $artikel = Artikel::findOrFail($id);

            $images = ArtikelImage::where('artikel_id', $artikel->id)->get();
            foreach ($images as $image) {
                if (Storage::exists('public/images/' . $image->filename)) {
                    Storage::delete('public/images/' . $image->filename);
                }
                $image->delete();
            }

            $artikel->forceDelete(); // Gunakan forceDelete jika menggunakan SoftDeletes

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Artikel dan semua gambar berhasil dihapus'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Artikel tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus artikel',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
