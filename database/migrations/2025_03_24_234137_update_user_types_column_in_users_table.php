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
        Schema::table('users', function (Blueprint $table) {
            // Supprimer la colonne `user_type` existante
            $table->dropColumn('user_type');
            
            // Ajouter la colonne `user_type_id` comme clé étrangère
            $table->unsignedBigInteger('user_type_id')->nullable();

            // Ajouter la contrainte de clé étrangère pointant vers `user_types`
            $table->foreign('user_type_id')->references('id')->on('user_types')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert the changes if needed
            $table->dropForeign(['user_type_id']);
            $table->dropColumn('user_type_id');
        });
    }
};
