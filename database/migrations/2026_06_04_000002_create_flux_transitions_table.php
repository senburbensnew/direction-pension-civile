<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flux_transitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_source_id')->nullable()->constrained('services')->nullOnDelete();
            $table->foreignId('service_destination_id')->constrained('services')->cascadeOnDelete();
            $table->string('action');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flux_transitions');
    }
};
