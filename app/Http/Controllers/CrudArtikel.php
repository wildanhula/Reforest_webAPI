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
    // Store new artikel with image
    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'title'  => 'required|string|max:255',
                'isi'    => 'required|string',
                'author' => 'required|string',
                'image'  => 'required|image|mimes:jpeg,png,jpg,gif|max:5048'
            ]);

            $artikel = Artikel::create($request->only(['title', 'isi', 'author']));

            $image = $request->file('image');
            $filename = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/images', $filename);

            $imageRecord = ArtikelImage::create([
                'artikel_id'     => $artikel->id,
                'filename'       => $filename,
                'original_name'  => $image->getClientOriginalName(),
                'mime_type'      => $image->getMimeType(),
                'file_size'      => $image->getSize()
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Artikel dan gambar berhasil diunggah',
                'data'    => [
                    'artikel' => $artikel,
                    'image'   => $imageRecord
                ]
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating artikel or uploading image',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // Get all artikels
    public function index()
    {
        try {
            $artikels = Artikel::with('images')->latest()->get();
            return response()->json([
                'success' => true,
                'data'    => $artikels
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching artikels',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // Show single artikel
    public function show($id)
    {
        try {
            $artikel = Artikel::with('images')->findOrFail($id);
            return response()->json([
                'success' => true,
                'data'    => $artikel
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
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // Update artikel
    public function update(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'title'  => 'sometimes|string|max:255',
                'isi'    => 'sometimes|string',
                'author' => 'sometimes',
                'image'  => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:5048'
            ]);

            $artikel = Artikel::findOrFail($id);
            $artikel->update($request->only(['title', 'isi', 'author']));

            if ($request->hasFile('image')) {
                $oldImages = ArtikelImage::where('artikel_id', $artikel->id)->get();
                foreach ($oldImages as $oldImage) {
                    Storage::delete('public/images/' . $oldImage->filename);
                    $oldImage->delete();
                }

                $image = $request->file('image');
                $filename = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/images', $filename);

                ArtikelImage::create([
                    'artikel_id'     => $artikel->id,
                    'filename'       => $filename,
                    'original_name'  => $image->getClientOriginalName(),
                    'mime_type'      => $image->getMimeType(),
                    'file_size'      => $image->getSize()
                ]);
            }

            $artikel->load('images');

            return response()->json([
                'success' => true,
                'message' => 'Artikel updated successfully',
                'data'    => $artikel
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $e->errors()
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
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // Delete artikel
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

            $artikel->forceDelete();

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
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // Add new image to artikel
    public function addImage(Request $request, $artikelId)
    {
        try {
            $this->validate($request, [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5048'
            ]);

            $artikel = Artikel::findOrFail($artikelId);

            $image = $request->file('image');
            $filename = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/images', $filename);

            $imageRecord = ArtikelImage::create([
                'artikel_id'     => $artikel->id,
                'filename'       => $filename,
                'original_name'  => $image->getClientOriginalName(),
                'mime_type'      => $image->getMimeType(),
                'file_size'      => $image->getSize()
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
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Artikel not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error uploading image',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}