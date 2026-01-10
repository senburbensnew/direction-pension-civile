<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(GendersSeeder::class);
        $this->call(UserTypesSeeder::class);
        $this->call(DefaultUserSeeder::class);
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(UsersSeeder::class);
        $this->call(StatusesSeeder::class);
        $this->call(CivilStatusesSeeder::class);
        $this->call(PensionTypesSeeder::class);
        $this->call(PensionCategoriesSeeder::class);
        $this->call(RequestTypeSeeder::class);
        $this->call(ParametersSeeder::class);
    }
}
