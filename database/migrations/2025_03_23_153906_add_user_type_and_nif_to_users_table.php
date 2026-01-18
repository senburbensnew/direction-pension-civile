<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserTypeAndNifToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Add user_type as an enum with the new values
            $table->enum('user_type', ['institution', 'fonctionnaire', 'pensionnaire'])->default('pensionnaire');
            // Add NIF as non-nullable
            $table->string('nif')->nullable()->unique();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the columns if rolling back
            $table->dropColumn(['user_type', 'nif']);
        });
    }
}