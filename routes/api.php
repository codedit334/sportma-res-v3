<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/refresh-token', function () {
    try {
        // Get the current token
        $token = JWTAuth::getToken();
        
        // Refresh the token
        $newToken = JWTAuth::refresh($token);
        
        // Get the expiration time for the new token
        $expiresIn = JWTAuth::factory()->getTTL() * 60;  // Convert from minutes to seconds
        
        return response()->json([
            'access_token' => $newToken,
            'expiresIn' => $expiresIn, // Pass the expiration time to frontend
        ]);
    } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
        return response()->json(['error' => 'Could not refresh token'], 500);
    }
})->middleware('jwt.auth');

Route::middleware(['jwt.auth'])->group(function () {
    // Define routes that require authentication here
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    
    
});


Route::get('/user/profile', function () {
    $user = auth()->user();
    
    // Decode the permissions field if it's a JSON string
    if (is_string($user->permissions)) {
        $user->permissions = json_decode($user->permissions, true); // Decode as an array
    }

    return response()->json($user);
})->middleware('jwt.auth');

Route::post('/user/profile/update', function (\Illuminate\Http\Request $request) {
    $user = auth()->user();

    $user->update($request->only(['name', 'email', 'permissions']));

    if ($request->hasFile('profile_picture')) {
        $path = $request->file('profile_picture')->store('profile_pictures', 'public');
        $user->profile_picture = $path;
    }

    $user->save();

    return response()->json($user);
})->middleware('jwt.auth');

Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout']);
Route::post('refresh', [AuthController::class, 'refresh']);