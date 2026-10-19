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
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'firstname')) {
                $table->string('firstname')->nullable()->after('id');
            }

            if (! Schema::hasColumn('users', 'lastname')) {
                $after = Schema::hasColumn('users', 'firstname') ? 'firstname' : 'id';
                $table->string('lastname')->nullable()->after($after);
            }

            if (! Schema::hasColumn('users', 'birth_date')) {
                $table->date('birth_date')->nullable()->after('remember_token');
            }

            if (! Schema::hasColumn('users', 'gender_id')) {
                $table->foreignId('gender_id')
                    ->nullable()
                    ->after('birth_date')
                    ->constrained('genders')
                    ->restrictOnDelete();
            }

            if (! Schema::hasColumn('users', 'civil_status_id')) {
                $table->foreignId('civil_status_id')
                    ->nullable()
                    ->after('gender_id')
                    ->constrained('civil_statuses')
                    ->restrictOnDelete();
            }

            if (! Schema::hasColumn('users', 'nif')) {
                $table->string('nif')->nullable()->unique()->after('civil_status_id');
            }

            if (! Schema::hasColumn('users', 'ninu')) {
                $table->string('ninu')->nullable()->unique()->after('nif');
            }

            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->unique()->nullable()->after('ninu');
            }

            if (! Schema::hasColumn('users', 'address')) {
                $table->string('address')->nullable()->after('phone');
            }

            if (! Schema::hasColumn('users', 'profile_photo')) {
                $table->string('profile_photo')->nullable()->after('address');
            }

            if (! Schema::hasColumn('users', 'signature')) {
                $table->string('signature')->nullable()->after('profile_photo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'signature',
                'profile_photo',
                'address',
                'phone',
                'ninu',
                'nif',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }

            if (Schema::hasColumn('users', 'civil_status_id')) {
                $table->dropForeign(['civil_status_id']);
                $table->dropColumn('civil_status_id');
            }

            if (Schema::hasColumn('users', 'gender_id')) {
                $table->dropForeign(['gender_id']);
                $table->dropColumn('gender_id');
            }

            if (Schema::hasColumn('users', 'birth_date')) {
                $table->dropColumn('birth_date');
            }

            if (Schema::hasColumn('users', 'lastname')) {
                $table->dropColumn('lastname');
            }

            if (Schema::hasColumn('users', 'firstname')) {
                $table->dropColumn('firstname');
            }
        });
    }
};
