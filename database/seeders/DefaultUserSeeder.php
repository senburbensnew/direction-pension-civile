<?php

namespace Database\Seeders;

use App\Enums\UserTypeEnum;
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
            ['name' => UserTypeEnum::FONCTIONNAIRE->value], // Look for the 'fonctionnaire' user type
            ['name' => UserTypeEnum::FONCTIONNAIRE->value]  // If it doesn't exist, create it with this value
        );
        
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Pierre Rubens MILORME',
                'lastname' => 'MILORME',
                'firstname' => 'Pierre Rubens',
                'email' => 'admin@example.com',
                'password' => bcrypt('password123'),
                'nif' => '555-123-456-9',
                'user_type_id' => $userType->id, // Use the user_type_id from the found or created user type
            ]
        );          
    }
}
