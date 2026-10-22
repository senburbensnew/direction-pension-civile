<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
                ->constrained('institutions')
                ->nullOnDelete();

            $table->unique('institution_id');
        });
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
};
