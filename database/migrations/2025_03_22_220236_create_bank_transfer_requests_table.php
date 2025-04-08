<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('bank_transfer_requests', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('Unique request code');
            $table->string('pensioner_code');
            $table->enum('pension_type', ['carriere', 'reversibilite']);
            $table->string('nif');
            $table->string('full_name');
            $table->string('address');
            $table->string('city');
            $table->date('birth_date');
            $table->enum('civil_status', ['célibataire', 'marié(e)', 'divorcé(e)', 'veuf(ve)']);
            $table->enum('gender', ['M', 'F', 'A']);
            $table->decimal('allocation_amount', 10, 2);
            $table->string('mother_name');
            $table->string('phone');
            $table->enum('pension_category', ['civile', 'militaire', 'bndai', 'minoterie', 'sélection nationale']);
            $table->string('bank_name');
            $table->string('account_number');
            $table->string('account_name');
            $table->string('status')
            ->default('en_attente')
            ->checkIn(['en attente', 'approuvé(e)', 'en cours', 'rejeté(e)', 'traité(e)', 'annulé(e)']);
            $table->string('profile_photo')->nullable();
            $table->foreignId('created_by')
                    ->constrained('users')
                    ->restrictOnDelete()
                    ->nullable(false);
            $table->string('pensioner_signature')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_transfer_requests');
    }
};
