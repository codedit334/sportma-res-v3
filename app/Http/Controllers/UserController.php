<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index()
    {
        $authUser = auth()->user();
    
        // Retrieve users with the same company_id as the authenticated user and where is_admin is false
        $users = User::where('company_id', $authUser->company_id)
        ->where('is_admin', false)
        ->where('is_superuser', false) // Add this condition
        ->get()
        ->map(function ($user) {
            // Decode permissions from JSON to array
            $user->permissions = json_decode($user->permissions, true);
            return $user;
        });

    
        return response()->json($users);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string',
            'permissions' => 'nullable|array',
            'profile_picture' => 'nullable|string',
            'is_superuser' => 'nullable|boolean',
        ]);
        
        if ($validator->fails()) {
            // Throw a validation exception with the errors
            throw new \Illuminate\Validation\ValidationException($validator, response()->json([
                'message' => 'Validation Failed',
                'errors' => $validator->errors(),
            ], 422));
        }
        
        // Get auth user's company_id
        $company_id = auth()->user()->company_id;

        $user = User::create([
            'company_id' => $company_id,
            'name' => $request->full_name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
            'permissions' => json_encode($request->permissions), // Convert to JSON
            'profile_picture' => $request->profile_picture,
            'is_superuser' => false,
        ]);
        
        return response()->json($user, 201);
    }



    public function update(Request $request, $id)
    {
        try {
            // Find the user by ID or return a 404 error if not found
            $user = User::findOrFail($id);
    
            // Validate the incoming request data
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $id,
                'password' => 'nullable|string|min:8', // Password is optional for updating
                'role' => 'sometimes|string',
                'permissions' => 'nullable|array',
                'profile_picture' => 'nullable|string',
            ]);
    
            if ($validator->fails()) {
                // Throw a validation exception with a custom response
                throw new \Illuminate\Validation\ValidationException($validator, response()->json([
                    'message' => 'Validation Failed',
                    'errors' => $validator->errors(),
                ], 422));
            }
    
            // Update user attributes conditionally
            $validatedData = $validator->validated(); // Get validated data
    
            if (isset($validatedData['name'])) {
                $user->name = $validatedData['name'];
            }
    
            if (isset($validatedData['email'])) {
                $user->email = $validatedData['email'];
            }
    
            // Only update the password if a new one is provided
            if (!empty($validatedData['password'])) {
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
    
public function destroy($id)
    {
        try {
            // Find the user by ID
            $user = User::findOrFail($id);
            
            // Delete the user
            $user->delete();

            // Return a success response
            return response()->json([
                'message' => 'User deleted successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Handle any errors
            return response()->json([
                'error' => 'User deletion failed.',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}