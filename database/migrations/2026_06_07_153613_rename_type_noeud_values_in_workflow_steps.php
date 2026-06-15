<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        \DB::table('workflow_steps')
            ->where('type_noeud', 'initiateur')
            ->update(['type_noeud' => 'initial']);

        \DB::table('workflow_steps')
            ->where('type_noeud', 'terminaison')
            ->update(['type_noeud' => 'terminal']);
    }

    public function down(): void
    {
        \DB::table('workflow_steps')
            ->where('type_noeud', 'initial')
            ->update(['type_noeud' => 'initiateur']);

        \DB::table('workflow_steps')
            ->where('type_noeud', 'terminal')
            ->update(['type_noeud' => 'terminaison']);
    }
};
