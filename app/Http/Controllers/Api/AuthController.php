<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();
        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $validated = $request->validated();
        if (!Auth::attempt($validated)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user = User::where('email', $validated['email'])->firstOrFail();
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'User logged in successfully',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'User logged out successfully',
        ], 200);
    }

    public function user(Request $request)
    {
        return response()->json([
            'message' => 'User retrieved successfully',
            'data' => $request->user(),
        ], 200);
    }
}
