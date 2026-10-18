<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_login_events')) {
            return;
        }

        Schema::table('user_login_events', function (Blueprint $table) {
            if (! Schema::hasColumn('user_login_events', 'country_code')) {
                $table->string('country_code', 2)->nullable()->after('user_agent');
            }
            if (! Schema::hasColumn('user_login_events', 'country_name')) {
                $table->string('country_name', 120)->nullable()->after('country_code');
            }
            if (! Schema::hasColumn('user_login_events', 'region_name')) {
                $table->string('region_name', 120)->nullable()->after('country_name');
            }
            if (! Schema::hasColumn('user_login_events', 'city')) {
                $table->string('city', 120)->nullable()->after('region_name');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('user_login_events')) {
            return;
        }

        Schema::table('user_login_events', function (Blueprint $table) {
            $table->dropColumn(['country_code', 'country_name', 'region_name', 'city']);
        });
    }
};
