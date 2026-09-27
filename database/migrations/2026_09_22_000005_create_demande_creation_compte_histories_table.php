<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demande_creation_compte_histories', function (Blueprint $table) {
            $table->id();

            // Column declared plainly — we control the index and FK names below.
            $table->unsignedBigInteger('demande_creation_compte_id');

            // Explicit short index (default would be 67 chars → fails)
            $table->index('demande_creation_compte_id', 'dcc_histories_dcc_id_index');

            // Explicit short FK (default would be 69 chars → fails)
            $table->foreign('demande_creation_compte_id', 'dcc_histories_dcc_id_foreign')
                  ->references('id')
                  ->on('demande_creation_comptes')
                  ->cascadeOnDelete();

            $table->string('event');
            $table->string('statut')->nullable();
            $table->text('commentaire')->nullable();

            // This one is 53 chars by default → fine, but keep it consistent.
            $table->foreignId('changed_by')
                  ->nullable()
                  ->constrained('users', indexName: 'dcc_histories_changed_by_index')
                  ->nullOnDelete();

            $table->json('champs')->nullable();
            $table->timestamps();

            // Composite index also needs an explicit short name
            // (default would be ~78 chars → fails)
            $table->index(
                ['demande_creation_compte_id', 'created_at'],
                'dcc_histories_dcc_id_created_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demande_creation_compte_histories');
    }
};