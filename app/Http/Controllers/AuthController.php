<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Enums\UserRole;

class AuthController extends Controller
{
    public function register_user(Request $request)
    {
        $fields = $request->validate([
            'nama' => 'required|string',
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        try {
            if (User::where('email', $fields['email'])->exists()) {
                return response()->json([
                    'message' => 'Email already exists',
                ], 409);
            }

            $fields['password'] = bcrypt($fields['password']);
            $fields['role'] = 'user';

            // buat user
            $user = User::create($fields);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user->only(['id', 'nama', 'email', 'role']),
        ], 201);
    }

    public function login_user(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        try {
            $user = User::where('email', $fields['email'])->first();

            if (!$user) {
                return response()->json([
                    'message' => 'Email not registered',
                ], 404);
            }

            if ($user->role ===  UserRole::Pengelola) {
                return response()->json([
                    'message' => 'unauthorized',
                ], 401);
            }

            if (!Hash::check($fields['password'], $user->password)) {
                return response()->json([
                    'message' => 'Wrong credentials',
                ], 401);
            }

            $token = $user->createToken('myapptoken')->plainTextToken;
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'User Login Successful',
            'user' => $user->only(['id', 'nama', 'email', 'role']),
            'token' => $token
        ], 201);
    }

    public function register_pengelola(Request $request)
    {
        $fields = $request->validate([
            'nama' => 'required|string',
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        try {
            if (User::where('email', $fields['email'])->exists()) {
                return response()->json([
                    'message' => 'Email already exists',
                ], 409);
            }

            $fields['password'] = bcrypt($fields['password']);
            $fields['role'] = 'pengelola';

            // buat pengelola
            $user = User::create($fields);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Pengelola created successfully',
            'user' => $user->only(['id', 'nama', 'email', 'role']),
        ], 201);
    }

    public function login_pengelola(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        try {
            $user = User::where('email', $fields['email'])->first();

            if (!$user) {
                return response()->json([
                    'message' => 'Email not registered',
                ], 404);
            }

            if ($user->role !== UserRole::Pengelola) {
                return response()->json([
                    'message' => 'unauthorized',
                ], 401);
            }

            if (!Hash::check($fields['password'], $user->password)) {
                return response()->json([
                    'message' => 'Wrong credentials',
                ], 401);
            }

            $token = $user->createToken('myapptoken')->plainTextToken;
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Pengelola Login Successful',
            'user' => $user->only(['id', 'nama', 'email', 'role']),
            'token' => $token
        ], 201);
    }

    public function logout(Request $request)
    {
        $user = $request->user('sanctum');

        if ($user) {
            $user->tokens()->delete();
            return response()->json([
                'message' => 'Logout Successful',
            ], 200);
        } else {
            return response()->json([
                'message' => 'Logout Failed',
            ], 404);
        }
    }
}
