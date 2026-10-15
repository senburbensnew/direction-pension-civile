<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            Schema::table('pension_mandats', function (Blueprint $table) {
                $table->dropForeign(['beneficiaire_id']);
            });

            DB::statement('ALTER TABLE pension_mandats MODIFY beneficiaire_id BIGINT UNSIGNED NULL');

            Schema::table('pension_mandats', function (Blueprint $table) {
                $table->foreign('beneficiaire_id')->references('id')->on('beneficiaires')->cascadeOnDelete();
            });

            return;
        }

        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        Schema::disableForeignKeyConstraints();

        DB::statement('CREATE TABLE pension_mandats_new (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            user_id INTEGER NOT NULL,
            beneficiaire_id INTEGER NULL,
            type VARCHAR NOT NULL DEFAULT \'procuration\',
            status VARCHAR NOT NULL DEFAULT \'en_attente\',
            date_debut DATE NULL,
            date_fin DATE NULL,
            allowed_demande_types TEXT NULL,
            allowed_pension_codes TEXT NULL,
            notes TEXT NULL,
            validated_by INTEGER NULL,
            validated_at DATETIME NULL,
            refusal_reason TEXT NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL,
            demande_creation_compte_id INTEGER NULL,
            FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY(beneficiaire_id) REFERENCES beneficiaires(id) ON DELETE CASCADE,
            FOREIGN KEY(validated_by) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY(demande_creation_compte_id) REFERENCES demande_creation_comptes(id) ON DELETE SET NULL
        )');

        DB::statement('INSERT INTO pension_mandats_new (
            id, user_id, beneficiaire_id, type, status, date_debut, date_fin,
            allowed_demande_types, allowed_pension_codes, notes, validated_by, validated_at,
            refusal_reason, created_at, updated_at, demande_creation_compte_id
        ) SELECT
            id, user_id, beneficiaire_id, type, status, date_debut, date_fin,
            allowed_demande_types, allowed_pension_codes, notes, validated_by, validated_at,
            refusal_reason, created_at, updated_at, demande_creation_compte_id
        FROM pension_mandats');

        DB::statement('DROP TABLE pension_mandats');
        DB::statement('ALTER TABLE pension_mandats_new RENAME TO pension_mandats');

        DB::statement('CREATE INDEX pension_mandats_user_id_status_index ON pension_mandats (user_id, status)');
        DB::statement('CREATE INDEX pension_mandats_beneficiaire_id_status_index ON pension_mandats (beneficiaire_id, status)');
        DB::statement('CREATE UNIQUE INDEX pension_mandats_demande_creation_compte_id_unique ON pension_mandats (demande_creation_compte_id)');

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            Schema::table('pension_mandats', function (Blueprint $table) {
                $table->dropForeign(['beneficiaire_id']);
            });

            DB::statement('ALTER TABLE pension_mandats MODIFY beneficiaire_id BIGINT UNSIGNED NOT NULL');

            Schema::table('pension_mandats', function (Blueprint $table) {
                $table->foreign('beneficiaire_id')->references('id')->on('beneficiaires')->cascadeOnDelete();
            });
        }
    }
};
