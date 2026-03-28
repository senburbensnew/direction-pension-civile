<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demande_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('demande_id')
                ->constrained('demandes')
                ->cascadeOnDelete();

            $table->string('type', 50)->index(); // cin, contrat, justificatif…

            $table->string('disk', 20)
                ->default(config('filesystems.default')); // ✅ local, public, s3…

            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size'); // en octets

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demande_documents');
    }
};

