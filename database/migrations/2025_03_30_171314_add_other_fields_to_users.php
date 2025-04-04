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
        Schema::table('users', function (Blueprint $table) {
            $table->string('ninu')->nullable();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->foreignId('civil_status_id')
            ->nullable()
            ->constrained('civil_statuses')
            ->onDelete('restrict');        
            $table->foreignId('gender_id')
                ->nullable()
                ->constrained('genders')
                ->onDelete('restrict');
            $table->string('profile_photo')->nullable();
            $table->string('signature')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
