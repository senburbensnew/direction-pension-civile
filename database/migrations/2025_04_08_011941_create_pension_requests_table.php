<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pension_requests', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('nif');
            $table->string('career_certificate');
            $table->string('monitor_copy');
            $table->string('marriage_certificate');
            $table->string('birth_certificate');
            $table->string('divorce_certificate')->nullable();
            $table->string('tax_id_number');
            $table->json('photos');
            $table->string('medical_certificate');
            $table->string('check_stub');
            // Foreign key constraints
            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete()
                ->nullable(false);
            $table->foreignId('status_id')->constrained('statuses')->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pension_requests');
    }
};