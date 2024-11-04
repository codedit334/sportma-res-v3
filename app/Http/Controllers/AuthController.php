<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function login(Request $request)
{
    $credentials = $request->only('email', 'password');

    try {
        // Attempt to create a token
        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Fetch the user associated with the credentials
        $user = User::where('email', $credentials['email'])->first();

        // Prepare the response data
        $responseData = [
            'token' => $token,
            'name' => $user->name, 
            'role' => $user->role, 
            'permissions' => $user->permissions, 
        ];

    } catch (JWTException $e) {
        return response()->json(['error' => 'Could not create token'], 500);
    }

    // Return the response with token and user data
    return response()->json($responseData);
}


    public function logout(Request $request)
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    // Optionally, you can create a method to refresh the token
    public function refresh()
    {
        return response()->json(['token' => JWTAuth::refresh()]);
    }
}