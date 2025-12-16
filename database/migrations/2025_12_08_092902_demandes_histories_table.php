<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up()
    {
        Schema::create('demande_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('demande_id');
            $table->string('etat');
            $table->text('commentaire')->nullable();
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->timestamps();


            $table->foreign('demande_id')->references('id')->on('demandes')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('demande_histories');
    }
};
