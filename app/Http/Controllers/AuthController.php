<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|unique:users|max:50',
            'email' => 'required|email|unique:users|max:100',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'sometimes|in:user,admin', // optional, default user bisa di-handle di model/controller
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'user',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully',
            'data' => $user,
        ], 201);
    }

    /**
     * Login user and return user data.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }
    
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User does not exist',
            ], 404);
        }
    
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials',
            ], 401);
        }

        // Jika pakai JWT, generate token di sini dan simpan ke $user->jwt_token (optional)
        // Contoh (gunakan package jwt-auth atau manual implementasi):
        // $token = JWT::fromUser($user);
        // $user->jwt_token = $token;
        // $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged in',
            'data' => [
                'user' => $user,
                // 'token' => $token, // uncomment jika menggunakan JWT
            ]
        ], 200);
    }

    /**
     * Logout user (remove token).
     */
    public function logout(Request $request)
    {
        // Jika menggunakan JWT dan menyimpan token di DB, hapus token di sini:
        // $user = $request->user();
        // $user->jwt_token = null;
        // $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    /**
     * Get authenticated user.
     */
    public function user(Request $request)
    {
        // Jika pakai middleware auth:api, bisa langsung return user
        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => $request->user(),
            ],
        ]);
    }
}
