<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Recover from a partial db:wipe (table/view left behind) during migrate:fresh.
        if (DB::getDriverName() === 'mysql') {
            DB::statement('DROP VIEW IF EXISTS `users`');
        }
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('name');
            $table->string('username')->nullable()->unique();
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->timestamp('password_changed_at')->nullable();
            $table->boolean('must_change_password')->default(false);
            $table->rememberToken();
            $table->string('account_status')->default('actif');
            $table->date('birth_date')->nullable();
            $table->string('nif')->nullable()->unique();
            $table->string('ninu')->nullable()->unique();
            $table->string('phone')->unique()->nullable();
            $table->string('address')->nullable();
            $table->string('profile_photo')->nullable();
            $table->string('signature')->nullable();
            $table->timestamps();

            $table->index('account_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
