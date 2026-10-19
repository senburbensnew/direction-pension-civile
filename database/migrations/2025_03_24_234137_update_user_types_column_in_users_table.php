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
        if (! Schema::hasColumn('users', 'user_type')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('user_type');

            $table->unsignedBigInteger('user_type_id')->nullable();

            $table->foreign('user_type_id')->references('id')->on('user_types')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (! Schema::hasColumn('users', 'user_type_id')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['user_type_id']);
            $table->dropColumn('user_type_id');
        });
    }
};
