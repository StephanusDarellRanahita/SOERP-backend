<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'division' => 'required|string|max:50',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'avatar' => 'required|image|max:2048'
        ]);

        $image = $request->file('avatar');
        $filename = time() . '_' . str_replace(' ', '_', $request->name . '.' . $image->getClientOriginalExtension());
        $path = $image->storeAs('avatar', $filename, 'public');

        $user = User::create([
            'name' => $request->name,
            'division' => $request->division,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'avatar' => $path
        ]);

        $token = $user->createToken('auth_token')->accessToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            return response()->json(['message' => 'Email not found'], 401);
        }
        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Wrong password'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->accessToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token
        ]);
    }

    public function getUser() {
        $user = Auth::user();

        if(!$user) {
            return response()->json(['message' => 'Unauthorized']);
        }

        return response()->json([
            'message' => 'User retrieved sucessfully',
            'user' => $user
        ]);
    }

    public function getAllUser() {
        $user = User::all();

        return response()->json([
            'message' => 'Retrieve user data success',
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logout successful'
        ]);
    }
}
