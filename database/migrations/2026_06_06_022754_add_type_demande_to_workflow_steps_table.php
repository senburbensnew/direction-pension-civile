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
        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->string('type_demande')->nullable()->after('service_id');

            // Remove old unique on code, replace with (service_id, type_demande)
            $table->dropUnique(['code']);
            $table->unique(['service_id', 'type_demande'], 'workflow_steps_service_type_unique');
        });
    }

    public function down(): void
    {
        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->dropUnique('workflow_steps_service_type_unique');
            $table->dropColumn('type_demande');
            $table->unique('code');
        });
    }
};
