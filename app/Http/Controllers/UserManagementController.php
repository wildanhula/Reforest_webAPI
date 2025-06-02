<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserImage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class UserManagementController extends Controller
{
    // Tampilkan semua user
    public function index()
    {
        // Gunakan with() seperti di UserController untuk eager loading
        $users = User::with('userImage')->get();
        return response()->json(['status' => 'success', 'data' => $users], 200);
    }

    // Tampilkan detail user by id
    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $user->load('userImage')
        ], 200);
    }

    // Tambah user baru
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'sometimes|string|max:255', // Tambahkan validasi role
            'profile_photo' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $user = new User();
        $user->username = $request->input('username');
        $user->email = $request->input('email');
        $user->password = Hash::make($request->input('password'));
        $user->role = $request->input('role', 'User'); // Set default role
        $user->save();

        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/images', $filename);

            $userImage = new UserImage([
                'filename' => $filename,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
            ]);
            $user->userImage()->save($userImage);
        }

        return response()->json([
            'message' => 'User created successfully',
            'data' => $user->load('userImage')
        ], 201);
    }

    // Update user by id
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) return response()->json(['message' => 'User not found'], 404);

        $validator = Validator::make($request->all(), [
            'username' => 'sometimes|string|max:255|unique:users,username,' . $user->id,
            'email' => 'sometimes|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:6',
            'role' => 'sometimes|string|max:255', // Tambahkan validasi role
            'profile_photo' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        if ($request->has('username')) $user->username = $request->input('username');
        if ($request->has('email')) $user->email = $request->input('email');
        if ($request->has('password')) $user->password = Hash::make($request->input('password'));
        if ($request->has('role')) $user->role = $request->input('role'); // Update role
        $user->save();

        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/images', $filename);

            // Hapus gambar lama jika ada
            if ($user->userImage) {
                Storage::disk('public')->delete('images/' . $user->userImage->filename);
                $user->userImage->delete();
            }

            $userImage = new UserImage([
                'filename' => $filename,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
            ]);
            $user->userImage()->save($userImage);
        }

        return response()->json([
            'message' => 'User updated successfully',
            'data' => $user->load('userImage')
        ]);
    }

    // Delete user by id
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) return response()->json(['message' => 'User not found'], 404);

        // Hapus gambar jika ada
        if ($user->userImage) {
            Storage::disk('public')->delete('images/' . $user->userImage->filename);
            $user->userImage->delete();
        }

        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }
}