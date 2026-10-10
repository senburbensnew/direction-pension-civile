<?php

use App\Models\DemandeCreationCompte;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demande_creation_comptes', function (Blueprint $table) {
            if (! Schema::hasColumn('demande_creation_comptes', 'soumise_at')) {
                $table->timestamp('soumise_at')->nullable()->after('status');
            }
            if (! Schema::hasColumn('demande_creation_comptes', 'approuvee_at')) {
                $table->timestamp('approuvee_at')->nullable()->after('soumise_at');
            }
            if (! Schema::hasColumn('demande_creation_comptes', 'rejetee_at')) {
                $table->timestamp('rejetee_at')->nullable()->after('approuvee_at');
            }
            if (! Schema::hasColumn('demande_creation_comptes', 'traitee_par')) {
                $table->foreignId('traitee_par')
                    ->nullable()
                    ->after('rejetee_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });

        if (Schema::hasColumn('demande_creation_comptes', 'reviewed_by')) {
            DemandeCreationCompte::query()->each(function (DemandeCreationCompte $demande) {
                $updates = [];

                if (Schema::hasColumn('demande_creation_comptes', 'reviewed_by') && $demande->getAttributes()['reviewed_by'] ?? null) {
                    $updates['traitee_par'] = $demande->getAttributes()['reviewed_by'];
                }

                $reviewedAt = $demande->getAttributes()['reviewed_at'] ?? null;
                if ($reviewedAt) {
                    if ($demande->status === DemandeCreationCompte::STATUS_ACCEPTEE) {
                        $updates['approuvee_at'] = $reviewedAt;
                    } elseif ($demande->status === DemandeCreationCompte::STATUS_REFUSEE) {
                        $updates['rejetee_at'] = $reviewedAt;
                    }
                }

                if (empty($demande->soumise_at) && $demande->created_at) {
                    $updates['soumise_at'] = $demande->created_at;
                }

                if ($updates !== []) {
                    $demande->forceFill($updates)->saveQuietly();
                }
            });

            Schema::table('demande_creation_comptes', function (Blueprint $table) {
                $table->dropForeign(['reviewed_by']);
                $table->dropColumn(['reviewed_by', 'reviewed_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('demande_creation_comptes', function (Blueprint $table) {
            if (! Schema::hasColumn('demande_creation_comptes', 'reviewed_by')) {
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
            }
        });

        DemandeCreationCompte::query()->each(function (DemandeCreationCompte $demande) {
            $updates = [];

            if ($demande->traitee_par) {
                $updates['reviewed_by'] = $demande->traitee_par;
            }

            $reviewedAt = $demande->approuvee_at ?? $demande->rejetee_at;
            if ($reviewedAt) {
                $updates['reviewed_at'] = $reviewedAt;
            }

            if ($updates !== []) {
                $demande->forceFill($updates)->saveQuietly();
            }
        });

        Schema::table('demande_creation_comptes', function (Blueprint $table) {
            if (Schema::hasColumn('demande_creation_comptes', 'traitee_par')) {
                $table->dropForeign(['traitee_par']);
                $table->dropColumn(['soumise_at', 'approuvee_at', 'rejetee_at', 'traitee_par']);
            }
        });
    }
};
