<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SuperAdminController extends Controller
{
    public function createCompanyAndUser(Request $request)
    {
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'company_email' => 'required|email|unique:companies,email',
            'company_phone' => 'nullable|string|max:20', // Make phone nullable
            'company_bio' => 'nullable|string', // Make bio nullable
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'user_name' => 'required|string|max:255',
            'user_email' => 'required|email|unique:users,email',
            'user_password' => 'required|string|min:8|confirmed',
            'sportma_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        // Handle Logo upload if exists
        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        // Step 1: Create the company using the request data
        $company = Company::create([
            'name' => $request->company_name,
            'email' => $request->company_email,
            'phone' => $request->company_phone, // Nullable field
            'bio' => $request->company_bio, // Nullable field
            'logo' => $logoPath, // Store logo file path
            'sportma_id' => $request->sportma_id, // Assuming the sportma_id is coming from the request
        ]);

        // Step 2: Create the user associated with the new company
        $user = User::create([
            'name' => $request->user_name,
            'email' => $request->user_email,
            'password' => Hash::make($request->user_password),
            'is_admin' => 1, // The user is an admin
            'role' => "Partner", // The user is an admin
            'company_id' => $company->id, // Associate the user with the company created above
            'logo' => $logoPath, // Optionally store user logo (if applicable)
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

}