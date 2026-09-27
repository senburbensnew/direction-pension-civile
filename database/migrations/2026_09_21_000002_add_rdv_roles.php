<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([User::ROLE_AGENT_RDV, User::ROLE_VALIDATEUR_RDV] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }

    public function down(): void
    {
        Role::whereIn('name', [User::ROLE_AGENT_RDV, User::ROLE_VALIDATEUR_RDV])->delete();
    }
};
