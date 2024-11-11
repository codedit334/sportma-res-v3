<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create a new company
        $company = Company::create([
            'name' => 'Sports Management Ltd.',
            'address' => '123 Sport St, Cityville',
        ]);

        // Create the admin user and link them to the company
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'role' => 'Admin',
            'permissions' => null,
            'password' => Hash::make('password'),
            'isAdmin' => true,
            'company_id' => $company->id, // Link to the company
        ]);

        // Optionally, create additional users (staff, employees, etc.)
        User::create([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'role' => 'Staff',
            'permissions' => null,
            'password' => Hash::make('password'),
            'isAdmin' => false,
            'company_id' => $company->id, // Link to the same company
        ]);

        User::create([
            'name' => 'Jane Smith',
            'email' => 'jane.smith@example.com',
            'role' => 'Staff',
            'permissions' => null,
            'password' => Hash::make('password'),
            'isAdmin' => false,
            'company_id' => $company->id, // Link to the same company
        ]);
    }
}