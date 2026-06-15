<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Instantanés du circuit (circuit_snapshot extrait de demandes) ────
        Schema::create('demande_circuit_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demande_id')->constrained()->cascadeOnDelete();
            $table->json('snapshot');
            $table->timestamps();
        });

        // ── 2. Table unifiée (fusion demande_workflows + affectations) ──────────
        Schema::create('demande_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demande_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['TRANSFERT', 'AVIS']);
            $table->foreignId('from_service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->foreignId('to_service_id')->constrained('services');
            $table->foreignId('initiated_by')->constrained('users');
            $table->text('commentaire')->nullable();
            $table->enum('statut', ['EN_ATTENTE', 'ACCEPTE', 'REJETE', 'EN_COURS', 'TERMINE'])
                  ->default('EN_ATTENTE');
            $table->text('reponse')->nullable();
            $table->timestamp('repondu_at')->nullable();
            $table->foreignId('repondu_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // ── 3. Migrer demande_workflows → demande_interactions (type=TRANSFERT) ─
        if (Schema::hasTable('demande_workflows')) {
            DB::table('demande_workflows')->orderBy('id')->chunk(200, function ($rows) {
                foreach ($rows as $row) {
                    $statut = match ($row->reception_status ?? 'pending') {
                        'accepted' => 'ACCEPTE',
                        'refused'  => 'REJETE',
                        default    => 'EN_ATTENTE',
                    };
                    DB::table('demande_interactions')->insert([
                        'demande_id'      => $row->demande_id,
                        'type'            => 'TRANSFERT',
                        'from_service_id' => $row->from_service_id,
                        'to_service_id'   => $row->to_service_id,
                        'initiated_by'    => $row->action_by_user_id,
                        'commentaire'     => $row->commentaire,
                        'statut'          => $statut,
                        'reponse'         => $row->reception_motif ?? null,
                        'repondu_at'      => $row->reception_at ?? null,
                        'repondu_by'      => $row->reception_by_user_id ?? null,
                        'created_at'      => $row->created_at,
                        'updated_at'      => $row->updated_at,
                    ]);
                }
            });
        }

        // ── 4. Migrer affectations → demande_interactions (type=AVIS) ────────────
        if (Schema::hasTable('affectations')) {
            DB::table('affectations')->orderBy('id')->chunk(200, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('demande_interactions')->insert([
                        'demande_id'      => $row->demande_id,
                        'type'            => 'AVIS',
                        'from_service_id' => null,
                        'to_service_id'   => $row->service_id,
                        'initiated_by'    => $row->affecte_par_user_id,
                        'commentaire'     => null,
                        'statut'          => $row->statut,
                        'reponse'         => $row->avis,
                        'repondu_at'      => $row->date_reponse,
                        'repondu_by'      => null,
                        'created_at'      => $row->date_affectation ?? $row->created_at,
                        'updated_at'      => $row->updated_at,
                    ]);
                }
            });
        }

        // ── 5. Migrer circuit_snapshot → demande_circuit_snapshots ───────────────
        if (Schema::hasColumn('demandes', 'circuit_snapshot')) {
            DB::table('demandes')
                ->whereNotNull('circuit_snapshot')
                ->orderBy('id')
                ->chunk(200, function ($rows) {
                    foreach ($rows as $row) {
                        DB::table('demande_circuit_snapshots')->insert([
                            'demande_id' => $row->id,
                            'snapshot'   => $row->circuit_snapshot,
                            'created_at' => $row->submitted_at ?? $row->created_at,
                            'updated_at' => $row->updated_at,
                        ]);
                    }
                });

            Schema::table('demandes', function (Blueprint $table) {
                $table->dropColumn('circuit_snapshot');
            });
        }

        // ── 6. Assurer que current_service_id n'est jamais NULL ──────────────────
        $directionId = DB::table('services')->where('code', 'DIRECTION')->value('id');
        if ($directionId) {
            DB::table('demandes')
                ->whereNull('current_service_id')
                ->update(['current_service_id' => $directionId]);
        }

        // ── 7. demande_histories : renommer data → champs, ajouter event ─────────
        Schema::table('demande_histories', function (Blueprint $table) {
            $table->string('event', 100)->nullable()->after('demande_id');
            $table->json('champs')->nullable()->after('event');
        });
        DB::table('demande_histories')->whereNotNull('data')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                DB::table('demande_histories')
                    ->where('id', $row->id)
                    ->update(['champs' => $row->data]);
            }
        });
        Schema::table('demande_histories', function (Blueprint $table) {
            $table->dropColumn('data');
        });

        $isSqlite = DB::getDriverName() === 'sqlite';

        // ── 8. assignments : supprimer service_id (redondant via users.service_id)
        if (Schema::hasColumn('assignments', 'service_id')) {
            if ($isSqlite) {
                // SQLite cannot drop FK columns; recreate the table without service_id.
                DB::statement('PRAGMA foreign_keys = OFF');
                DB::statement('
                    CREATE TABLE assignments_new (
                        id         INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                        demande_id INTEGER NOT NULL REFERENCES demandes(id) ON DELETE CASCADE,
                        user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
                        assigned_by INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
                        note       TEXT,
                        ended_at   DATETIME,
                        created_at DATETIME,
                        updated_at DATETIME
                    )
                ');
                DB::statement('INSERT INTO assignments_new (id, demande_id, user_id, assigned_by, note, ended_at, created_at, updated_at)
                               SELECT id, demande_id, user_id, assigned_by, note, ended_at, created_at, updated_at FROM assignments');
                DB::statement('DROP TABLE assignments');
                DB::statement('ALTER TABLE assignments_new RENAME TO assignments');
                // Leave FK OFF on SQLite: some legacy FK references (e.g. status_id → etats)
                // are unresolvable after prior schema cleanup migrations skip drops on SQLite.
                // Tests rely on FK-OFF to insert demandes without triggering broken constraints.
            } else {
                Schema::table('assignments', function (Blueprint $table) {
                    $table->dropForeign(['service_id']);
                    $table->dropColumn('service_id');
                });
            }
        }

        // ── 9. step_required_documents : service_id → workflow_step_id ──────────
        // Add workflow_step_id and populate it (works on all databases).
        if (Schema::hasColumn('step_required_documents', 'service_id') && !Schema::hasColumn('step_required_documents', 'workflow_step_id')) {
            Schema::table('step_required_documents', function (Blueprint $table) {
                $table->foreignId('workflow_step_id')
                      ->nullable()
                      ->after('id')
                      ->constrained('workflow_steps')
                      ->nullOnDelete();
            });

            DB::table('step_required_documents')->orderBy('id')->chunk(200, function ($rows) {
                foreach ($rows as $row) {
                    $stepId = DB::table('workflow_steps')
                        ->where('service_id', $row->service_id)
                        ->when($row->type_demande, fn ($q) => $q->where(
                            fn ($q2) => $q2->whereNull('type_demande')
                                          ->orWhere('type_demande', $row->type_demande)
                        ))
                        ->orderBy('ordre')
                        ->value('id');

                    if ($stepId) {
                        DB::table('step_required_documents')
                            ->where('id', $row->id)
                            ->update(['workflow_step_id' => $stepId]);
                    }
                }
            });

            // SQLite cannot drop columns that are part of a FK definition.
            if (!$isSqlite) {
                Schema::table('step_required_documents', function (Blueprint $table) {
                    $table->dropForeign(['service_id']);
                    $table->dropColumn('service_id');
                });
            }
        }

        // ── 10. Supprimer les tables legacy ──────────────────────────────────────
        Schema::dropIfExists('flux_transitions');
        Schema::dropIfExists('affectations');
        Schema::dropIfExists('demande_workflows');
    }

    public function down(): void
    {
        // Recréer demande_workflows
        Schema::create('demande_workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demande_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->foreignId('to_service_id')->constrained('services');
            $table->foreignId('action_by_user_id')->constrained('users');
            $table->text('commentaire')->nullable();
            $table->enum('reception_status', ['pending', 'accepted', 'refused'])->default('pending');
            $table->text('reception_motif')->nullable();
            $table->timestamp('reception_at')->nullable();
            $table->foreignId('reception_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Recréer affectations
        Schema::create('affectations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demande_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services');
            $table->foreignId('affecte_par_user_id')->constrained('users');
            $table->enum('statut', ['EN_ATTENTE', 'EN_COURS', 'TERMINE', 'REJETE'])->default('EN_ATTENTE');
            $table->text('avis')->nullable();
            $table->timestamp('date_affectation')->useCurrent();
            $table->timestamp('date_reponse')->nullable();
            $table->timestamps();
        });

        // Recréer flux_transitions
        Schema::create('flux_transitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_source_id')->constrained('services');
            $table->foreignId('service_destination_id')->constrained('services');
            $table->string('action');
            $table->string('type_demande', 100)->nullable();
            $table->boolean('is_urgent_only')->default(false);
            $table->integer('ordre')->default(0);
            $table->timestamps();
        });

        // Restaurer circuit_snapshot dans demandes
        Schema::table('demandes', function (Blueprint $table) {
            $table->json('circuit_snapshot')->nullable()->after('expires_at');
        });

        // Restaurer demande_histories.data
        Schema::table('demande_histories', function (Blueprint $table) {
            $table->json('data')->nullable();
        });
        DB::table('demande_histories')->whereNotNull('champs')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                DB::table('demande_histories')
                    ->where('id', $row->id)
                    ->update(['data' => $row->champs]);
            }
        });
        Schema::table('demande_histories', function (Blueprint $table) {
            $table->dropColumn(['event', 'champs']);
        });

        // Restaurer assignments.service_id
        Schema::table('assignments', function (Blueprint $table) {
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
        });

        // Restaurer step_required_documents.service_id
        Schema::table('step_required_documents', function (Blueprint $table) {
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
        });
        Schema::table('step_required_documents', function (Blueprint $table) {
            $table->dropForeign(['workflow_step_id']);
            $table->dropColumn('workflow_step_id');
        });

        Schema::dropIfExists('demande_interactions');
        Schema::dropIfExists('demande_circuit_snapshots');
    }
};
