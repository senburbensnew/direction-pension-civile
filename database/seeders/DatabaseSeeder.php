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
        $this->call(ServiceSeeder::class);
        $this->call(FluxTransitionSeeder::class);
        $this->call(UsersSeeder::class);
        $this->call(StatusesSeeder::class);
        $this->call(CivilStatusesSeeder::class);
        $this->call(PensionTypesSeeder::class);
        $this->call(PensionCategoriesSeeder::class);
        $this->call(ParametersSeeder::class);
        $this->call(TypeDemandeSeeder::class);
        $this->call(CarouselSeeder::class);
        $this->call(ActualiteSeeder::class);
        $this->call(ReportSeeder::class);
        $this->call(NewsletterSeeder::class);
        $this->call(InstitutionImageSeeder::class);
        $this->call(PartenaireSeeder::class);
    }
}
