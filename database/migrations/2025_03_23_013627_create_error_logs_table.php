<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
// database/migrations/xxxx_create_error_logs_table.php
public function up()
{
    Schema::create('error_logs', function (Blueprint $table) {
        $table->id();
        $table->text('message');
        $table->string('code')->nullable();
        $table->string('file')->nullable();
        $table->integer('line')->nullable();
        $table->text('trace')->nullable();
        $table->text('request_data')->nullable();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->timestamps();
        
        $table->foreign('user_id')->references('id')->on('users');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('error_logs');
    }
};
