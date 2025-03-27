<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bank_transfer_requests', function (Blueprint $table) {
            // Drop old enum columns
            $table->dropColumn(['pension_type', 'civil_status', 'gender', 'pension_category', 'status']);

            // Add foreign keys
            $table->foreignId('pension_type_id')->constrained('pension_types')->onDelete('cascade');
            $table->foreignId('civil_status_id')->constrained('civil_statuses')->onDelete('cascade');
            $table->foreignId('gender_id')->constrained('genders')->onDelete('cascade');
            $table->foreignId('pension_category_id')->constrained('pension_categories')->onDelete('cascade');
            $table->foreignId('status_id')->constrained('statuses')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('bank_transfer_requests', function (Blueprint $table) {
            // Restore enum columns
            $table->enum('pension_type', ['carriere', 'reversibilite']);
            $table->enum('civil_status', ['célibataire', 'marié(e)', 'divorcé(e)', 'veuf(ve)']);
            $table->enum('gender', ['M', 'F', 'A']);
            $table->enum('pension_category', ['civile', 'militaire', 'bndai', 'minoterie', 'sélection nationale']);
            $table->string('status')->default('en_attente')->checkIn(['en attente', 'approuvé(e)', 'en cours', 'rejeté(e)', 'traité(e)', 'annulé(e)']);

            // Drop foreign keys
            $table->dropForeign(['pension_type_id']);
            $table->dropForeign(['civil_status_id']);
            $table->dropForeign(['gender_id']);
            $table->dropForeign(['pension_category_id']);
            $table->dropForeign(['status_id']);

            // Drop new columns
            $table->dropColumn(['pension_type_id', 'civil_status_id', 'gender_id', 'pension_category_id', 'status_id']);
        });
    }
};
