<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
                'role' => 'in:ADMIN,USER'
            ]);


            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role ?? 'USER',
            ]);

            $token = JWTAuth::fromUser($user);

            return response()->json([
                'user' => new UserResource($user),
                'token' => $token,
            ], 201);


        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage() ], 500);
        }
    }


    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);

            $user = User::where('email', $request->email)->firstOrFail();

            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'Invalid credentials.', ], 401);
            }

            $token = JWTAuth::fromUser($user);

            return response()->json([
                'user' => new UserResource($user),
                'token' => $token,
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage() ], 500);
        }
    }
}
