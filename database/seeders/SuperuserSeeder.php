<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperuserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Superuser',
            'email' => 'superuser@example.com',
            'password' => Hash::make('password'), // Securely hash the password
            'role' => 'superuser',
            'permissions' => json_encode([]), // Or specify permissions if needed
            'profile_picture' => null, // Set a default or leave null
            'company_id' => null, // Adjust based on your application logic
            'is_admin' => false,
            'is_superuser' => true,
        ]);
    }
}