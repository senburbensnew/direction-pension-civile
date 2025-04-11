<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('joining_requests', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('institution')->nullable();
            $table->string('lastname')->nullable();
            $table->string('firstname')->nullable();
            $table->string('mother_lastname')->nullable();
            $table->string('mother_firstname')->nullable();
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('nif')->nullable();
            $table->string('ninu')->nullable();
            $table->string('spouse_lastname')->nullable();
            $table->string('spouse_firstname')->nullable();
            $table->date('entry_date')->nullable();
            $table->decimal('current_salary', 12, 2)->nullable();
            $table->string('cotisant_signature')->nullable();
            $table->string('drh_signature')->nullable();
            $table->decimal('augmentation_amount', 12, 2)->nullable();
            $table->date('augmentation_date')->nullable();
            $table->date('cessation_date')->nullable();
            $table->date('reintegration_date')->nullable();
            $table->decimal('new_salary', 12, 2)->nullable();
            $table->date('end_of_service_date')->nullable();

            // ✅ Add foreign key columns before constraints
            $table->unsignedBigInteger('gender_id')->nullable();
            $table->unsignedBigInteger('civil_status_id')->nullable();

            // Foreign keys
            $table->foreign('gender_id')->references('id')->on('genders');
            $table->foreign('civil_status_id')->references('id')->on('civil_statuses');

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete()
                ->nullable(false);

            $table->foreignId('status_id')
                ->constrained('statuses')
                ->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('joining_requests');
    }
};
