<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('raison_sociale');
            $table->string('sigle')->nullable();
            $table->string('nif')->nullable()->unique();
            $table->string('code')->nullable()->unique();
            $table->string('statut', 32)->default('actif');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('statut');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('institution_id')
                ->nullable()
                ->after('service_id')
                ->constrained('institutions')
                ->nullOnDelete();

            $table->unique('institution_id');
        });

        $this->backfillInstitutionsFromUsers();
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['institution_id']);
            $table->dropUnique(['institution_id']);
            $table->dropColumn('institution_id');
        });

        Schema::dropIfExists('institutions');
    }

    private function backfillInstitutionsFromUsers(): void
    {
        if (! Schema::hasTable('user_types') || ! Schema::hasColumn('users', 'user_type_id')) {
            return;
        }

        $institutionTypeId = DB::table('user_types')->where('name', 'institution')->value('id');

        if (! $institutionTypeId) {
            return;
        }

        $now = now();

        $institutionUsers = DB::table('users')
            ->where('user_type_id', $institutionTypeId)
            ->select('id', 'name', 'nif')
            ->get();

        foreach ($institutionUsers as $row) {
            $institutionId = DB::table('institutions')->insertGetId([
                'raison_sociale' => $row->name,
                'sigle' => null,
                'nif' => $row->nif,
                'code' => null,
                'statut' => 'actif',
                'metadata' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('users')->where('id', $row->id)->update([
                'institution_id' => $institutionId,
            ]);
        }
    }
};
