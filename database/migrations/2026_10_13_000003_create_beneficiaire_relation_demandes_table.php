<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beneficiaire_relation_demandes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('beneficiaire_id')->constrained()->cascadeOnDelete();
            $table->string('type_relation');
            $table->string('status')->default('en_attente');
            $table->text('motif')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->json('permissions')->nullable();
            $table->text('refusal_reason')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->foreignId('user_beneficiaire_link_id')->nullable()->constrained('user_beneficiaire_links')->nullOnDelete();
            $table->foreignId('demande_creation_compte_id')
                ->nullable()
                ->constrained('demande_creation_comptes', 'id', 'brd_dcc_id_foreign')
                ->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['beneficiaire_id', 'status']);
            $table->unique('demande_creation_compte_id', 'brd_dcc_id_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beneficiaire_relation_demandes');
    }
};
