<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Wip;
use App\Models\Ticket;
use Illuminate\Support\Facades\Storage;

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

        $user = User::create([
            'name' => $request->name,
            'division' => $request->division,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $image = $request->file('avatar');
        $filename = time() . '_' . str_replace(' ', '_', $request->id . '.' . $image->getClientOriginalExtension());
        $path = $image->storeAs('avatar', $filename, 'public');
        $user->update([
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
        $token = $user->createTokenWithExpiry('auth_token')->accessToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token
        ]);
    }

    public function updatePhotoProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'photo' => 'required|image'
        ]);
        $photo = $request->file('photo');
        $timestamp = time();
        $extension = $photo->getClientOriginalExtension();
        $fileName = $timestamp . '_' . str_replace(' ', '_', $user->name) . '.' . $extension;
        $folderPath = 'avatar';
        $path = $photo->storeAs($folderPath, $fileName, 'public');
        Storage::disk('public')->delete($user->avatar);
        $user->update([
            'avatar' => $path
        ]);

        return response()->json([
            'success' => true,
            'message' => "Update photo success",
            'photo' => $request->file('photo')
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required',
            'email' => 'required',
        ]);
        $bank = null;
        $bankAcc = null;
        if ($request->bank) {
            $bank = $request->bank;
            if (!$request->bank_acc) {
                return response()->json([
                    'message' => 'Bank account is required',
                    'bank' => $request->bank_acc
                ]);
            }
            $bankAcc = $request->bank_acc;
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'bank' => $bank,
            'bank_account' => $bankAcc
        ]);
    }

    public function getUser($company)
    {
        $user = Auth::user();
        $task = Ticket::where('assign', $user->id)->where('ticket_id', 'LIKE', '%/' . $company . '/%')->where('status', '!=', 'Canceled')->where('status', '!=', 'Closed')->count('assign');
        $wip = Wip::whereHas('ticket', function ($query) use ($user) {
            $query->where('assign', $user->id);
        })->where('status', '=', 'On Going')->count();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized']);
        }
        $task += $wip;
        return response()->json([
            'message' => 'User retrieved sucessfully',
            'user' => $user,
            'task' => $task,
        ]);
    }

    public function getAllUser()
    {
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
