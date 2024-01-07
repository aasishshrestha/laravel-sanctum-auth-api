<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    //
    public function register(Request $request)
    {
        $request->validate([
            "name" => 'required',
            "email" => "required|email",
            "password" => "required|confirmed",
        ]);

        if (User::where('email', $request->email)->exists()) {
            return response([
                'message' => 'Email already exists',
                'status' => 'failed',
            ]);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        $token = $user->createToken($request->email)->plainTextToken;
        return response([
            'token' => $token,
            'message' => 'Successfully Registered',
            'status' => 'success',
            'user' => $user
        ], 201);
    }
    public function login(Request $request)
    {
        $request->validate([
            "email" => "required|email",
            "password" => "required",
        ]);
        $user = User::where('email', $request->email)->first();
        if ($user && Hash::check($request->password, $user->password)) {
            $token = $user->createToken($request->email)->plainTextToken;
            return response([
                'token' => $token,
                'message' => 'Logged in Successfully',
                'status' => 'success',
                'user' => $user
            ], 200);
        }

        return response([

            'message' => 'The provided Credintials was invalid',
            'status' => 'failed',
        ], 401);
    }
    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();
        return response([
            'message' => 'Logged out Successfully',
            'status' => 'success',
        ], 200);
    }

    public function logged_user(Request $request)
    {
        $logged_user = auth()->user();
        return response([
            'user' => $logged_user,
            'message' => 'Logged user data',
            'status' => 'success',
        ], 200);
    }

    public function change_password(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'password' => 'required|confirmed',
        ]);
        $logged_user = auth()->user();
        if (Hash::check($request->old_password, $logged_user->password)) {
            $logged_user->password = Hash::make($request->password);
            $logged_user->save();
            return response([
                'message' => 'Passwords changed successfully',
                'status' => 'success',
            ], 200);
        }else{
            return response([
                'message' => 'Old Password donot match',
                'status' => 'failed',
            ], 401);
        }


        
    }
}
