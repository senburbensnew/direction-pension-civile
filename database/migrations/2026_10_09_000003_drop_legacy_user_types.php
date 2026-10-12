<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'user_type_id')) {
            if (DB::getDriverName() === 'sqlite') {
                Schema::disableForeignKeyConstraints();
                Schema::table('users', function (Blueprint $table) {
                    $table->dropColumn('user_type_id');
                });
                Schema::enableForeignKeyConstraints();
            } else {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropForeign(['user_type_id']);
                    $table->dropColumn('user_type_id');
                });
            }
        }

        Schema::dropIfExists('user_types');
    }

    public function down(): void
    {
        Schema::create('user_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('user_type_id')->nullable()->after('password');
            $table->foreign('user_type_id')->references('id')->on('user_types')->restrictOnDelete();
        });
    }
};
