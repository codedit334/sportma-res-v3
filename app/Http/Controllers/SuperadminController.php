<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;



class SuperAdminController extends Controller
{
    public function createCompanyAndUser(Request $request)
    {
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'company_email' => 'required|email|unique:companies,email',
            'company_phone' => 'nullable|string|max:20',
            'company_bio' => 'nullable|string',
            'logo_url' => 'nullable|url', // Add validation for external logo URL
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'user_name' => 'required|string|max:255',
            'user_email' => 'required|email|unique:users,email',
            'user_password' => 'required|string|min:8|confirmed',
            'sportma_id' => 'nullable|integer',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }
    
        // Handle Logo upload or external URL
        $logoPath = null;
    
        if ($request->hasFile('logo')) {
            // Upload logo from the file
            $logoPath = $request->file('logo')->store('logos', 'public');
        } elseif ($request->filled('logo_url')) {
            // Download and store the logo from URL
            try {
                $imageContents = file_get_contents($request->logo_url);
                $fileName = 'logos/' . uniqid() . '.jpg'; // Generate a unique file name
                Storage::disk('public')->put($fileName, $imageContents);
                $logoPath = $fileName;
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Failed to download logo from the provided URL.',
                ], 400);
            }
        }
    
        // Step 1: Create the company
        $company = Company::create([
            'name' => $request->company_name,
            'email' => $request->company_email,
            'phone' => $request->company_phone,
            'bio' => $request->company_bio,
            'logo' => $logoPath,
            'sportma_id' => $request->sportma_id ?? null,
        ]);
    
        // Step 2: Create the user associated with the company
        $user = User::create([
            'name' => $request->user_name,
            'email' => $request->user_email,
            'password' => Hash::make($request->user_password),
            'is_admin' => 1,
            'role' => 'Partner',
            'company_id' => $company->id,
        ]);
    
        return response()->json([
            'message' => 'Company and User created successfully!',
            'company' => $company,
            'user' => $user,
        ]);
    }
    
    public function index()
{
    $authUser = auth()->user();

    // Ensure the authenticated user is a superadmin
    if (!$authUser || $authUser->is_superuser !== 1) {
        return response()->json([
            'error' => 'Unauthorized access. Only superadmins can access this resource.',
        ], 403);
    }

    // Fetch all users except those with is_superadmin = 1
    $users = User::where('is_superuser', '!=', 1)
             ->where('is_admin', 1)
             ->get();

    return response()->json($users);
}

public function destroy($id)
    {
        try {
            // Find the user by ID
            $partner = Company::findOrFail($id);
            
            // Delete the user
            $partner->delete();

            // Return a success response
            return response()->json([
                'message' => 'Partner deleted successfully.'
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