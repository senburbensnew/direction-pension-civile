<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('flux_transitions', function (Blueprint $table) {
            $table->string('type_demande')->nullable()->after('action');
            $table->unsignedInteger('ordre')->default(0)->after('type_demande');
        });

        // Set initial order based on current insertion order
        $i = 1;
        foreach (DB::table('flux_transitions')->orderBy('id')->get() as $row) {
            DB::table('flux_transitions')->where('id', $row->id)->update(['ordre' => $i++]);
        }
    }

    public function down(): void
    {
        Schema::table('flux_transitions', function (Blueprint $table) {
            $table->dropColumn(['type_demande', 'ordre']);
        });
    }
};
