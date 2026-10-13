<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        // Référentiel Spatie : RolesAndPermissionsSeeder
    }

    public function down(): void
    {
        Role::whereIn('name', [User::ROLE_DIRECTEUR, User::ROLE_ASSISTANT_DIRECTEUR])->delete();
    }
};
