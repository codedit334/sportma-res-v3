<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarConfigController;
use App\Http\Controllers\SportController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\SuperAdminController;

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

    Route::get('/sports/{id}', [SportController::class, 'index']);
    Route::post('/sports', [SportController::class, 'store']);
    Route::post('/sports/download', [SportController::class, 'storeArray']);
    Route::put('/sports/{id}', [SportController::class, 'update']);
    Route::delete('/sports/{id}', [SportController::class, 'destroy']);

        Route::get('/reservations', [ReservationController::class, 'index']); // Get all reservations
        Route::get('/reservations/{company_id}', [ReservationController::class, 'showByPartner']);
        Route::put('/reservations/{id}', [ReservationController::class, 'update']); // Update a reservation
        Route::delete('/reservations/{id}', [ReservationController::class, 'destroy']); // Delete a reservation
        Route::post('/reservations', [ReservationController::class, 'store']); // Create a new reservation
        Route::post('/reservations/batch', [ReservationController::class, 'batchStore']);
    
    Route::post('/partner', [SuperAdminController::class, 'createCompanyAndUser']);
    Route::post('/partner/edit', [SuperAdminController::class, 'updateCompanyAndUser']);
    Route::post('/partner/logo/{company_id}', [SuperAdminController::class, 'updateLogo']);
    Route::delete('/partner/{id}', [SuperAdminController::class, 'destroy']);
    Route::get('/partners', [SuperAdminController::class, 'index']);
});

// routes/web.php or routes/api.php
Route::get('/proxy-image/{url}', function ($url) {
    // Use finfo to detect the MIME type of the file
    $fullUrl = Storage::disk('public')->path("logos/{$url}");

    // Fetch the image content
    $imageContent = file_get_contents($fullUrl);

    if ($imageContent === false) {
        return response()->json(['error' => 'Image not found'], 404);
        abort(404, 'Image not found');
    }
    
    $imageData = base64_encode($imageContent);
    $mimeType = mime_content_type($fullUrl);

    // Prepare the base64 string response
    $base64String = "data:$mimeType;base64,$imageData";

    return response()->json(['image' => $base64String]);
});

Route::get('/user/profile', function () {
    // Retrieve the authenticated user with their associated company
    $user = auth()->user()->load('company');

    // Decode the permissions field if it's a JSON string
    if (is_string($user->permissions)) {
        $user->permissions = json_decode($user->permissions, true); // Decode as an array
    }

    return response()->json($user);
})->middleware('jwt.auth');

Route::post('/user/profile/update', function (\Illuminate\Http\Request $request) {
    $user = auth()->user();

    // Validate the incoming request
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'permissions' => 'nullable|array',
        'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        'new_password' => 'nullable|string|min:8|confirmed', // For new password
    ]);

    // Update the user's details
    $user->update($request->only(['name', 'email', 'permissions']));

    // Update the profile picture if it exists
    if ($request->hasFile('profile_picture')) {
        $path = $request->file('profile_picture')->store('profile_pictures', 'public');
        $user->profile_picture = $path;
    }

    // Update password if a new password is provided
    if ($request->filled('new_password')) {
        $user->password = bcrypt($request->new_password);
    }

    $user->save();

    return response()->json($user);
})->middleware('jwt.auth');


Route::post('/company/update/logo', function (Request $request) {
    $user = auth()->user();

    // Ensure the user has a company
    if (!$user->company) {
        return response()->json(['error' => 'User does not belong to a company.'], 403);
    }

    // Validate the incoming request
    $validated = $request->validate([
        'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ]);

    // Store the new logo
    if ($request->hasFile('logo')) {
        // Delete the old logo if it exists
        if ($user->company->logo && Storage::disk('public')->exists($user->company->logo)) {
            Storage::disk('public')->delete($user->company->logo);
        }

        // Save the new logo
        $path = $request->file('logo')->store('logos', 'public');
        $user->company->logo = $path;
        $user->company->save();
    }

    return response()->json([
        'message' => 'Company logo updated successfully!',
        'company' => $user->company,
    ]);
})->middleware('jwt.auth');



Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout']);
Route::post('refresh', [AuthController::class, 'refresh']);