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
        Role::where('name', User::ROLE_AGENT_FORMALITES)->delete();
    }
};
