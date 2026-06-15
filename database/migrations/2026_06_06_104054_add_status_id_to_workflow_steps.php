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
            // FK non contrainte — la table sera renommée en 'etats' dans la migration suivante
            $table->unsignedBigInteger('status_id')->nullable()->after('service_id');
        });

        // Peupler status_id pour les étapes existantes
        $map = [
            'BROUILLON'                  => 'BROUILLON',
            'SOUMISE'                    => 'SOUMISE',
            'ACCUEIL_RECEPTION'          => 'EN_ATTENTE',
            'ENREGISTREMENT_SECRETARIAT' => 'EN_COURS',
            'VERIFICATION_FORMALITES'    => 'EN_COURS',
            'INSTRUCTION_LIQUIDATION'    => 'EN_COURS',
            'CONTROLE_PLACEMENT'         => 'EN_COURS',
            'ORDONNANCEMENT_COMPTABILITE'=> 'EN_COURS',
            'TRAITEMENT_ASSURANCE'       => 'EN_COURS',
            'ADMINISTRATION_INTERNE'     => 'EN_COURS',
            'DECISION_FINALE'            => 'EN_ATTENTE',
            'APPROUVEE'                  => 'APPROUVEE',
            'FINALISEE'                  => 'FINALISEE',
            'REJETEE'                    => 'REJETEE',
            'ANNULEE'                    => 'ANNULEE',
        ];

        foreach ($map as $stepCode => $statusCode) {
            $statusId = \DB::table('statuses')->where('code', $statusCode)->value('id');
            if ($statusId) {
                \DB::table('workflow_steps')
                    ->where('code', $stepCode)
                    ->update(['status_id' => $statusId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->dropForeign(['status_id']);
            $table->dropColumn('status_id');
        });
    }
};
