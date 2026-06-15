<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $isSqlite = DB::getDriverName() === 'sqlite';

        // SQLite cannot drop columns involved in FK constraints without recreating the table.
        // Since these columns are already removed from all fillable arrays and no longer
        // referenced in code, we skip the column drops for SQLite (dev/test) and only
        // perform them on MySQL/PostgreSQL (production).
        if (!$isSqlite) {
            if (Schema::hasColumn('demandes', 'status_id')) {
                Schema::table('demandes', function (Blueprint $table) {
                    try { $table->dropForeign(['status_id']); } catch (\Throwable) {}
                    $table->dropColumn('status_id');
                });
            }

            if (Schema::hasColumn('demande_workflows', 'status_id')) {
                Schema::table('demande_workflows', function (Blueprint $table) {
                    try { $table->dropForeign(['status_id']); } catch (\Throwable) {}
                    $table->dropColumn('status_id');
                });
            }

            if (Schema::hasColumn('workflow_steps', 'status_id')) {
                Schema::table('workflow_steps', function (Blueprint $table) {
                    $table->dropColumn('status_id');
                });
            }
        }

        // Drop etats table — works on all databases.
        // Disable FK checks on MySQL before dropping to handle FK references.
        if (!$isSqlite) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }
        Schema::dropIfExists('etats');
        if (!$isSqlite) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    public function down(): void
    {
        Schema::create('etats', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('label');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::table('demandes', function (Blueprint $table) {
            $table->foreignId('status_id')->nullable()->constrained('etats');
        });

        Schema::table('demande_workflows', function (Blueprint $table) {
            $table->foreignId('status_id')->nullable()->constrained('etats');
        });

        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->unsignedBigInteger('status_id')->nullable();
        });
    }
};
