<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_visit_ips', function (Blueprint $table) {
            $table->id();
            $table->date('visited_on');
            $table->string('ip_hash', 64);
            $table->timestamps();

            $table->unique(['visited_on', 'ip_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_visit_ips');
    }
};
