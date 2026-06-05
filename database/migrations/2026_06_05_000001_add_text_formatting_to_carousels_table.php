<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carousels', function (Blueprint $table) {
            $table->string('text_color', 20)->default('#ffffff')->after('text_size');
            $table->json('text_styles')->nullable()->after('text_color');
            $table->string('font_family', 50)->default('sans')->after('text_styles');
        });
    }

    public function down(): void
    {
        Schema::table('carousels', function (Blueprint $table) {
            $table->dropColumn(['text_color', 'text_styles', 'font_family']);
        });
    }
};
