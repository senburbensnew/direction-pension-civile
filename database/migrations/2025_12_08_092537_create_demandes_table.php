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
            $table->string('type', 50)->index();
            $table->foreignId('created_by')
                    ->constrained('users')
                    ->restrictOnDelete()
                    ->index();
            $table->foreignId('status_id')
                    ->constrained('statuses')
                    ->restrictOnDelete()
                    ->index();
            $table->json('data')->nullable();
            $table->timestamps();

            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('demandes');
    }
};
