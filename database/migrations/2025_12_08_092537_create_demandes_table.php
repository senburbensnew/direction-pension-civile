<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('demandes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('visio_token', 64)->nullable()->unique();
            $table->string('title')->nullable();
            $table->string('type', 50)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('data')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('annotation')->nullable();
            $table->foreignId('annotated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('annotated_at')->nullable();
            $table->string('folder', 50)->nullable();
            $table->string('categorie', 50)->nullable();
            $table->boolean('is_urgent')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('demandes');
    }
};
