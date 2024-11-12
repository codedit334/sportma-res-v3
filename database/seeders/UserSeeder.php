<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create users and an admin for each company
        Company::all()->each(function ($company) {
            // Create 5 regular users for each company
            User::factory()->count(5)->create(['company_id' => $company->id]);
    
            // Create an admin user for each company
            User::factory()->create([
                'company_id' => $company->id,
                'role' => 'admin',  // Assuming you have a role attribute
                'is_admin' => true, // Assuming you have an is_admin attribute
                'name' => 'Admin User',  // Optionally, you can set specific details
                'email' => 'admin@' . $company->name . '.com',  // Customize email per company
                'password' => bcrypt('password'),  // Set a default password for admins
            ]);
        });
    }
    


}