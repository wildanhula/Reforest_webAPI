<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use App\Models\UserImage;

class UserController extends Controller
{
    public function getUserData(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            return response()->json([
                'status' => 'success',
                'data' => $user->load('userImage'),
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getAllUsers()
    {
        $users = User::with('userImage')->get();

        return response()->json([
            'status' => 'success',
            'data' => $users,
        ], 200);
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $validator = Validator::make($request->all(), [
                'username' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|max:255',
                'profile_photo' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            if ($request->has('username')) $user->username = $request->input('username');
            if ($request->has('email')) $user->email = $request->input('email');
            $user->save();

            if ($request->hasFile('profile_photo')) {
                $file = $request->file('profile_photo');
                $filename = uniqid() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('public/images', $filename);

                if ($user->userImage) {
                    Storage::disk('public')->delete('images/' . $user->userImage->filename);
                    $user->userImage->delete();
                }

                $userImage = new UserImage([
                    'user_id' => $user->id,
                    'filename' => $filename,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);

                $user->userImage()->save($userImage);
            }

            return response()->json([
                'message' => 'Profile updated successfully',
                'data' => $user->load('userImage'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function deleteUser(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if ($user->userImage) {
                Storage::disk('public')->delete('images/' . $user->userImage->filename);
                $user->userImage->delete();
            }

            $user->delete();

            return response()->json(['message' => 'User deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unauthorized or deletion failed'], 401);
        }
    }
}
