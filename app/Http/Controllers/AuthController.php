<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

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
            'role' => 'sometimes|in:user,admin',
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
     * Login and generate JWT token
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid credentials'
                ], 401);
            }
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Could not create token'
            ], 500);
        }

        $user = auth()->user();

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user
        ], 200);
    }

    /**
     * Get the authenticated user
     */
    public function me(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'user' => auth()->user()
        ]);
    }
}
