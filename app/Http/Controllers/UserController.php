<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index()
{
    $users = User::all()->map(function ($user) {
        // Decode permissions from JSON to array
        $user->permissions = json_decode($user->permissions, true);
        return $user;
    });

    return response()->json($users);
}

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string',
            'permissions' => 'nullable|array',
            'profile_picture' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $request->full_name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
            'permissions' => json_encode($request->permissions), // Convert to JSON
            'profile_picture' => $request->profile_picture,
        ]);
        
        return response()->json($user, 201);
    }

    // app/Http/Controllers/UserController.php


public function update(Request $request, $id)
{
    try {
        // Find the user by ID or return a 404 error if not found
        $user = User::findOrFail($id);

        // Validate the incoming data
        $validatedData = $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $id,
            'password' => 'sometimes|string|min:8', // Optional for updating
            'role' => 'sometimes|string',
            'permissions' => 'nullable|array',
            'profile_picture' => 'nullable|string',
        ]);

        // Update user attributes conditionally
        if (isset($validatedData['full_name'])) {
            $user->name = $validatedData['full_name'];
        }

        if (isset($validatedData['email'])) {
            $user->email = $validatedData['email'];
        }

        if (isset($validatedData['password'])) {
            $user->password = bcrypt($validatedData['password']);
        }

        if (isset($validatedData['role'])) {
            $user->role = $validatedData['role'];
        }

        if (isset($validatedData['permissions'])) {
            $user->permissions = json_encode($validatedData['permissions']); // Store as JSON
        }

        if (isset($validatedData['profile_picture'])) {
            $user->profile_picture = $validatedData['profile_picture'];
        }

        // Save the updated user
        $user->save();

        // Return the updated user as JSON
        return response()->json($user, 200);

    } catch (ValidationException $e) {
        return response()->json(['errors' => $e->errors()], 422);
    } catch (\Exception $e) {
        return response()->json(['error' => 'An error occurred while updating the user'], 500);
    }
}


}