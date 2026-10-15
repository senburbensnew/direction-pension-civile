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
        Schema::create('step_required_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_step_id')
                ->constrained('workflow_steps')
                ->cascadeOnDelete();
            $table->string('type_demande', 100)->nullable();
            $table->string('label');
            $table->string('document_type');
            $table->timestamps();

            $table->index(['workflow_step_id', 'type_demande']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('step_required_documents');
    }
};
