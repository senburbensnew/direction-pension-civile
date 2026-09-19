<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([User::ROLE_DIRECTEUR, User::ROLE_ASSISTANT_DIRECTEUR] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }

    public function down(): void
    {
        Role::whereIn('name', [User::ROLE_DIRECTEUR, User::ROLE_ASSISTANT_DIRECTEUR])->delete();
    }
};
