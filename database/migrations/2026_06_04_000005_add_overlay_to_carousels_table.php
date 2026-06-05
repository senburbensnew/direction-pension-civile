<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carousels', function (Blueprint $table) {
            $table->string('overlay_position')->default('bottom-left')->after('link');
            $table->string('cta_label')->nullable()->after('overlay_position');
        });
    }

    public function down(): void
    {
        Schema::table('carousels', function (Blueprint $table) {
            $table->dropColumn(['overlay_position', 'cta_label']);
        });
    }
};
