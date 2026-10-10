<?php

namespace Database\Seeders;

use App\Models\UserCategory;
use Illuminate\Database\Seeder;

class UserCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('account_categories.categories', []) as $code => $definition) {
            UserCategory::query()->updateOrCreate(
                ['code' => $code],
                [
                    'label' => $definition['label'] ?? $code,
                    'description' => $definition['description'] ?? null,
                    'is_system' => $definition['is_system'] ?? true,
                    'capabilities' => $definition['capabilities'] ?? null,
                ]
            );
        }
    }
}
