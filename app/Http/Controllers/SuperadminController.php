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

        // load company into user
        $user->load('company');
    
        return response()->json([
            'message' => 'Company and User created successfully!',
            'user' => $user,
        ]);
    }

    public function updateCompanyAndUser(Request $request)
{
    // Validate incoming request
    $validator = Validator::make($request->all(), [
        'company_id' => 'required|exists:companies,id', // Ensure company exists
        'user_id' => 'required|exists:users,id',       // Ensure user exists
        'company_name' => 'required|string|max:255',
        'company_email' => 'required|email|unique:companies,email,' . $request->company_id,
        'company_phone' => 'nullable|string|max:20',
        'company_bio' => 'nullable|string',
        'logo_url' => 'nullable|url', // Add validation for external logo URL
        'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'user_name' => 'required|string|max:255',
        'user_email' => 'required|email|unique:users,email,' . $request->user_id,
        'user_password' => 'nullable|string|min:8|confirmed', // Password is optional for updates
        'sportma_id' => 'nullable|integer',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'errors' => $validator->errors(),
        ], 422);
    }

    // Fetch the existing company and user
    $company = Company::find($request->company_id);
    $user = User::find($request->user_id);

    // Handle Logo upload or external URL
    $logoPath = $company->logo; // Keep the current logo by default

    if ($request->hasFile('logo')) {
        // Upload new logo from the file
        $logoPath = $request->file('logo')->store('logos', 'public');
    } elseif ($request->filled('logo_url')) {
        // Download and store the new logo from URL
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

    // Step 1: Update the company
    $company->update([
        'name' => $request->company_name,
        'email' => $request->company_email,
        'phone' => $request->company_phone,
        'bio' => $request->company_bio,
        'logo' => $logoPath,
        'sportma_id' => $request->sportma_id ?? $company->sportma_id, // Keep the current sportma_id if not provided
    ]);

    // Step 2: Update the user associated with the company
    $userData = [
        'name' => $request->user_name,
        'email' => $request->user_email,
        'company_id' => $company->id,
    ];

    if ($request->filled('user_password')) {
        $userData['password'] = Hash::make($request->user_password); // Update password if provided
    }

    $user->update($userData);

    // return user with company
    $user->load('company');
    
    return response()->json([
        'message' => 'Company and User updated successfully!',
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

    // Fetch all users except those with is_superadmin = 1 with comany
    $users = User::where('is_superuser', '!=', 1)
             ->where('is_admin', 1)
             ->with('company')
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

    public function updateLogo(Request $request, $company_id)
{
    // Validate the incoming request
    $validated = $request->validate([
        'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ]);

    // Retrieve the company by ID
    $company = Company::findOrFail($company_id);

    // Check if a new logo file is provided
    if ($request->hasFile('logo')) {
        // Delete the old logo if it exists
        if ($company->logo && Storage::disk('public')->exists($company->logo)) {
            Storage::disk('public')->delete($company->logo);
        }

        // Store the new logo and update the database
        $path = $request->file('logo')->store('logos', 'public');
        $company->logo = $path;
        $company->save();
    }

    return response()->json([
        'message' => 'Company logo updated successfully!',
        'company' => $company,
    ]);
}

}