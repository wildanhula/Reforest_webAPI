<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class UserManagementController extends Controller
{
    /**
     * Tampilkan semua user dengan relasi userImage
     */
    public function index()
    {
        $users = User::with('userImage')->get();

        return response()->json([
            'status' => 'success',
            'data' => $users,
        ], 200);
    }

    /**
     * Update user lain (bukan user login)
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'role' => 'sometimes|string',
            // Tambah validasi lain sesuai kebutuhan
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user->update($request->only(['name', 'email', 'role']));

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully',
            'data' => $user,
        ], 200);
    }

    /**
     * Hapus user
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->userImage) {
            Storage::disk('public')->delete('images/' . $user->userImage->filename);
            $user->userImage->delete();
        }

        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User deleted successfully',
        ], 200);
    }
}
