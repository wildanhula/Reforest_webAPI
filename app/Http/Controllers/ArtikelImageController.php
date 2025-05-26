<?php

namespace App\Http\Controllers;

use App\Models\ArtikelImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArtikelImageController extends Controller
{
    public function index()
    {
        try {
            $images = ArtikelImage::with('artikel')->latest()->get();
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

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'artikel_id' => 'required|exists:artikel,id',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5048'
            ]);

            $image = $request->file('image');
            $filename = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/images', $filename);

            $imageRecord = ArtikelImage::create([
                'artikel_id' => $request->artikel_id,
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

    public function show($id)
    {
        try {
            $image = ArtikelImage::with('artikel')->findOrFail($id);
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

    public function update(Request $request, $id)
    {
        try {
            $image = ArtikelImage::findOrFail($id);

            $this->validate($request, [
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'artikel_id' => 'nullable|exists:artikel,id'
            ]);

            if ($request->has('artikel_id')) {
                $image->artikel_id = $request->artikel_id;
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

    public function destroy($id)
    {
        try {
            $image = ArtikelImage::findOrFail($id);
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