<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('newsletters', function (Blueprint $table) {
            $table->string('unsubscribe_token', 64)->unique()->nullable()->after('email');
        });

        // Backfill tokens for existing subscribers
        DB::table('newsletters')->whereNull('unsubscribe_token')->get()->each(function ($row) {
            DB::table('newsletters')->where('id', $row->id)->update([
                'unsubscribe_token' => bin2hex(random_bytes(32)),
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('newsletters', function (Blueprint $table) {
            $table->dropColumn('unsubscribe_token');
        });
    }
};
