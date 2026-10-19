<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Cosmetic column order for the users table (MySQL 64-char limit / readability).
     * Irreversible: down() is intentionally empty.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $columns = [
            'firstname' => 'VARCHAR(255) NULL',
            'lastname' => 'VARCHAR(255) NULL',
            'name' => 'VARCHAR(255) NOT NULL',
            'username' => 'VARCHAR(255) NULL',
            'email' => 'VARCHAR(255) NULL',
            'email_verified_at' => 'TIMESTAMP NULL DEFAULT NULL',
            'password' => 'VARCHAR(255) NOT NULL',
            'must_change_password' => "TINYINT(1) NOT NULL DEFAULT '0'",
            'remember_token' => 'VARCHAR(100) NULL',
            'account_status' => "VARCHAR(255) NOT NULL DEFAULT 'actif'",
            'birth_date' => 'DATE NULL',
            'gender_id' => 'BIGINT UNSIGNED NULL',
            'civil_status_id' => 'BIGINT UNSIGNED NULL',
            'nif' => 'VARCHAR(255) NULL',
            'ninu' => 'VARCHAR(255) NULL',
            'phone' => 'VARCHAR(255) NULL',
            'address' => 'VARCHAR(255) NULL',
            'service_id' => 'BIGINT UNSIGNED NULL',
            'institution_id' => 'BIGINT UNSIGNED NULL',
            'profile_photo' => 'VARCHAR(255) NULL',
            'signature' => 'VARCHAR(255) NULL',
            'created_at' => 'TIMESTAMP NULL DEFAULT NULL',
            'updated_at' => 'TIMESTAMP NULL DEFAULT NULL',
        ];

        $after = 'id';

        foreach ($columns as $column => $definition) {
            DB::statement("ALTER TABLE `users` MODIFY COLUMN `{$column}` {$definition} AFTER `{$after}`");
            $after = $column;
        }
    }

    public function down(): void
    {
        // Column reorder is cosmetic; no down migration.
    }
};
