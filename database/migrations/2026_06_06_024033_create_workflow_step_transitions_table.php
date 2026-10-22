<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('workflow_step_transitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_step_id')->nullable()->constrained('workflow_steps')->cascadeOnDelete();
            $table->foreignId('to_step_id')->constrained('workflow_steps')->cascadeOnDelete();
            $table->string('action', 100);
            $table->boolean('is_urgent_only')->default(false);
            $table->string('required_permission', 100)->nullable();
            $table->json('guard_conditions')->nullable();
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->timestamps();

            $table->unique(['from_step_id', 'to_step_id'], 'wst_from_to_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_step_transitions');
    }
};
