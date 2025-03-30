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
        Schema::create('existence_proof_requests', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('id_number')->unique();
            $table->string('pensioner_picture')->nullable();
            $table->string('fiscal_year');
            $table->string('nif');
            $table->string('lastname');
            $table->string('firstname');
            $table->string('address');
            $table->string('location');
            $table->date('birth_date');
            $table->string('civil_status');
            $table->string('gender');
            $table->string('postal_address');
            $table->string('phone');
            $table->string('pension_amount');
            $table->integer('monitor_number');
            $table->date('monitor_date');
            $table->date('pension_start_date');
            $table->date('pension_end_date');
            $table->string('mandataire_name');
            $table->string('mandataire_nif')->nullable();
            $table->string('mandataire_cin')->nullable();
            $table->string('civil_pension_employee_signature')->nullable();
            $table->string('pensioner_signature')->nullable();
            $table->string('mandataire_signature')->nullable();
            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete()
                ->nullable(false);
            $table->foreignId('status_id')->constrained('statuses')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
