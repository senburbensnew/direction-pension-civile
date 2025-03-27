<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call(GendersSeeder::class);
        $this->call(UserTypesSeeder::class);
        $this->call(DefaultUserSeeder::class);
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(StatusesSeeder::class);
        $this->call(CivilStatusesSeeder::class);
        $this->call(PensionTypesSeeder::class);
        $this->call(PensionCategoriesSeeder::class);
        $this->call(RequestTypeSeeder::class);
    }
}
