<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demandes', function (Blueprint $table) {
            $table->text('annotation')->nullable()->after('expires_at');
            $table->foreignId('annotated_by')->nullable()->after('annotation')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('annotated_at')->nullable()->after('annotated_by');
            $table->string('folder', 50)->nullable()->after('annotated_at');
        });
    }

    public function down(): void
    {
        Schema::table('demandes', function (Blueprint $table) {
            $table->dropForeign(['annotated_by']);
            $table->dropColumn(['annotation', 'annotated_by', 'annotated_at', 'folder']);
        });
    }
};
