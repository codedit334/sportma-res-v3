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

        // Get the refresh token
        $refreshToken = JWTAuth::getToken(); // Assuming you are using the same token for both purposes

        // Get the token expiration time (TTL) in seconds
        $expiresIn = config('jwt.ttl') * 60;  // Converting TTL from minutes to seconds
        
        $company = $user->company; 
        
        // Prepare the response data
        $responseData = [
            'token' => $token,
            'refreshToken' => $refreshToken,  // Add refresh token
            'name' => $user->name, 
            'role' => $user->role, 
            'is_admin' => $user->is_admin,
            'is_superuser' => $user->is_superuser,
            'email' => $user->email,
            'company_id' => $user->company_id,
            'profile_picture' => $user->profile_picture,
            'permissions' => json_decode($user->permissions), 
            'expiresIn' => $expiresIn,  // Add expiresIn
            'company' => $company
        ];

    } catch (JWTException $e) {
        return response()->json(['error' => 'Could not create token'], 500);
    }

    // Return the response with token, refreshToken, user data, and expiresIn
    return response()->json($responseData);
}



public function logout(Request $request)
{
    // Log out the user
    auth()->logout();
    
    // Return a response with success status and message
    return response()->json(['success' => true, 'message' => 'Successfully logged out']);
}


    // Optionally, you can create a method to refresh the token
    public function refresh()
    {
        return response()->json(['token' => JWTAuth::refresh()]);
    }
}