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
        // ── Ajouter service_id à etats ──────────────────────────────────
        Schema::table('etats', function (Blueprint $table) {
            $table->unsignedBigInteger('service_id')->nullable()->after('code');
        });

        // ── Peupler service_id pour les états existants ─────────────────
        $svc = fn(string $code) => \DB::table('services')->where('code', $code)->value('id');

        $serviceMap = [
            'SOUMISE'               => 'direction',
            'APPROUVEE'             => 'direction',
            'REJETEE'               => 'direction',
            'FINALISEE'             => 'direction',
            'ANNULEE'               => 'direction',
            'EN_ATTENTE'            => null,
            'EN_COURS'              => null,
            'BROUILLON'             => null,
            'COMPLEMENT_REQUIS'     => null,
            'TRANSFERT_EN_ATTENTE'  => null,
            'TRANSFERT_REFUSE'      => null,
        ];

        foreach ($serviceMap as $code => $serviceCde) {
            \DB::table('etats')->where('code', $code)->update([
                'service_id' => $serviceCde ? $svc($serviceCde) : null,
            ]);
        }

        // ── Nouveaux états service-spécifiques ──────────────────────────
        $nouveaux = [
            // États de traitement par service (localisation précise)
            ['code' => 'EN_RECEPTION',          'label' => 'En réception',                'description' => 'Dossier reçu et enregistré à la réception',                     'service' => 'reception'],
            ['code' => 'EN_INSTRUCTION_SECRETARIAT', 'label' => 'En instruction — Secrétariat', 'description' => 'Dossier en cours d\'instruction au Secrétariat',          'service' => 'secretariat'],
            ['code' => 'EN_LIQUIDATION',        'label' => 'En liquidation',              'description' => 'Dossier en cours de liquidation de pension',                     'service' => 'service_liquidation'],
            ['code' => 'EN_ORDONNANCEMENT',     'label' => 'En ordonnancement',           'description' => 'Dossier en cours d\'ordonnancement comptable',                   'service' => 'service_comptabilite'],
            ['code' => 'EN_TRAITEMENT_ASSURANCE','label' => 'En traitement assurance',   'description' => 'Dossier en cours de traitement par le service Assurance',        'service' => 'service_assurance'],
            ['code' => 'EN_CONTROLE',           'label' => 'En contrôle et placement',   'description' => 'Dossier en cours de contrôle et de placement pour signature',    'service' => 'service_controle_placement'],
            ['code' => 'EN_FORMALITE',          'label' => 'En vérification des formalités', 'description' => 'Dossier en cours de vérification des formalités',             'service' => 'service_formalite'],
            ['code' => 'EN_DECISION',           'label' => 'En attente de décision',     'description' => 'Dossier soumis à la Direction pour décision finale',             'service' => 'direction'],
            // États transversaux supplémentaires
            ['code' => 'SUSPENDU',              'label' => 'Suspendu',                    'description' => 'Traitement du dossier temporairement suspendu',                  'service' => null],
            ['code' => 'RETOURNE',              'label' => 'Retourné au demandeur',       'description' => 'Dossier retourné au demandeur pour corrections ou compléments',  'service' => null],
            ['code' => 'CLASSE',                'label' => 'Classé sans suite',           'description' => 'Dossier classé sans suite suite à une décision administrative',  'service' => null],
        ];

        foreach ($nouveaux as $etat) {
            $serviceId = $etat['service'] ? $svc($etat['service']) : null;
            \DB::table('etats')->updateOrInsert(
                ['code' => $etat['code']],
                ['label' => $etat['label'], 'description' => $etat['description'], 'service_id' => $serviceId, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        $nouveauxCodes = ['EN_RECEPTION','EN_INSTRUCTION_SECRETARIAT','EN_LIQUIDATION','EN_ORDONNANCEMENT',
                          'EN_TRAITEMENT_ASSURANCE','EN_CONTROLE','EN_FORMALITE','EN_DECISION',
                          'SUSPENDU','RETOURNE','CLASSE'];
        \DB::table('etats')->whereIn('code', $nouveauxCodes)->delete();

        Schema::table('etats', function (Blueprint $table) {
            $table->dropColumn('service_id');
        });
    }
};
