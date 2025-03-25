<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserType;

class DefaultUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if the user type exists
        $userType = UserType::firstOrCreate(
            ['name' => 'fonctionnaire'], // Look for the 'fonctionnaire' user type
            ['name' => 'fonctionnaire']  // If it doesn't exist, create it with this value
        );
        
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password123'),
                'nif' => '1234567890',
                'user_type_id' => $userType->id, // Use the user_type_id from the found or created user type
            ]
        );          
    }
}
