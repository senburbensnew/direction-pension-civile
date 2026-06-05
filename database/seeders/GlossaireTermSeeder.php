<?php

namespace Database\Seeders;

use App\Models\GlossaireTerm;
use Illuminate\Database\Seeder;

class GlossaireTermSeeder extends Seeder
{
    public function run(): void
    {
        $terms = [
            // From original hardcoded glossaire view (commit e0abce6)
            [
                'term'         => 'Pension',
                'definition'   => 'Montant versé chaque mois à un fonctionnaire retraité, en compensation de ses années de service.',
                'category'     => 'retraite',
                'icon'         => 'fa-book',
                'order_column' => 1,
                'published'    => true,
            ],
            [
                'term'         => 'Cotisation',
                'definition'   => 'Prélèvement obligatoire effectué sur le salaire pour financer la pension civile et les droits futurs.',
                'category'     => 'finance',
                'icon'         => 'fa-book',
                'order_column' => 2,
                'published'    => true,
            ],
            [
                'term'         => 'Affilié',
                'definition'   => 'Fonctionnaire inscrit au régime de pension civile et cotisant régulièrement selon la loi.',
                'category'     => 'agent',
                'icon'         => 'fa-book',
                'order_column' => 3,
                'published'    => true,
            ],
            [
                'term'         => 'Salaire de référence',
                'definition'   => 'Salaire moyen calculé à partir des 60 meilleurs mois de rémunération, servant au calcul de la pension.',
                'category'     => 'calcul',
                'icon'         => 'fa-book',
                'order_column' => 4,
                'published'    => true,
            ],
            [
                'term'         => 'Retraite anticipée',
                'definition'   => 'Départ à la retraite avant l\'âge légal, généralement pour raisons de santé ou incapacité.',
                'category'     => 'invalidite',
                'icon'         => 'fa-book',
                'order_column' => 5,
                'published'    => true,
            ],
            [
                'term'         => 'Direction de la Pension Civile',
                'definition'   => 'Institution publique chargée de la gestion administrative, financière et réglementaire des pensions civiles.',
                'category'     => 'administration',
                'icon'         => 'fa-book',
                'order_column' => 6,
                'published'    => true,
            ],
            // Extended terms
            [
                'term'         => 'Pension de retraite',
                'definition'   => 'Allocation mensuelle versée à un fonctionnaire ayant atteint l\'âge légal de départ à la retraite et justifiant du nombre d\'années de service requis.',
                'category'     => 'pension',
                'icon'         => 'fa-book',
                'order_column' => 7,
                'published'    => true,
            ],
            [
                'term'         => 'Pension d\'invalidité',
                'definition'   => 'Allocation accordée à un fonctionnaire contraint de cesser son activité en raison d\'une incapacité permanente reconnue médicalement.',
                'category'     => 'pension',
                'icon'         => 'fa-book',
                'order_column' => 8,
                'published'    => true,
            ],
            [
                'term'         => 'Pension de réversion',
                'definition'   => 'Fraction de la pension du fonctionnaire décédé reversée à son conjoint survivant ou à ses ayants droit.',
                'category'     => 'pension',
                'icon'         => 'fa-book',
                'order_column' => 9,
                'published'    => true,
            ],
            [
                'term'         => 'Liquidation de dossier',
                'definition'   => 'Processus administratif de vérification et de calcul du montant de la pension d\'un fonctionnaire avant la mise en paiement.',
                'category'     => 'procedure',
                'icon'         => 'fa-book',
                'order_column' => 10,
                'published'    => true,
            ],
            [
                'term'         => 'Attestation de pension',
                'definition'   => 'Document officiel délivré par la Direction des Pensions certifiant qu\'une personne est bénéficiaire d\'une pension et en précisant le montant.',
                'category'     => 'document',
                'icon'         => 'fa-book',
                'order_column' => 11,
                'published'    => true,
            ],
            [
                'term'         => 'Preuve d\'existence',
                'definition'   => 'Certificat annuel que le pensionnaire doit fournir pour confirmer qu\'il est toujours en vie et continuer à percevoir sa pension.',
                'category'     => 'document',
                'icon'         => 'fa-book',
                'order_column' => 12,
                'published'    => true,
            ],
            [
                'term'         => 'Ayant droit',
                'definition'   => 'Personne (conjoint, enfant ou autre membre de la famille) qui bénéficie de droits liés à la pension d\'un fonctionnaire décédé.',
                'category'     => 'beneficiaire',
                'icon'         => 'fa-book',
                'order_column' => 13,
                'published'    => true,
            ],
            [
                'term'         => 'NIF (Numéro d\'Identification Fiscale)',
                'definition'   => 'Identifiant unique attribué à chaque contribuable par la Direction Générale des Impôts, requis dans la plupart des démarches administratives.',
                'category'     => 'identification',
                'icon'         => 'fa-book',
                'order_column' => 14,
                'published'    => true,
            ],
            [
                'term'         => 'NINU (Numéro d\'Identification Nationale Unique)',
                'definition'   => 'Numéro unique attribué à chaque citoyen haïtien par l\'Office National d\'Identification (ONI), servant de référence dans les actes administratifs.',
                'category'     => 'identification',
                'icon'         => 'fa-book',
                'order_column' => 15,
                'published'    => true,
            ],
            [
                'term'         => 'Code pension',
                'definition'   => 'Identifiant propre à chaque pensionnaire dans le système de la Direction des Pensions, utilisé pour le suivi des dossiers et des paiements.',
                'category'     => 'identification',
                'icon'         => 'fa-book',
                'order_column' => 16,
                'published'    => true,
            ],
            [
                'term'         => 'Relevé de carrière',
                'definition'   => 'Document récapitulatif de l\'ensemble des postes occupés, des indices salariaux et des années de service d\'un fonctionnaire.',
                'category'     => 'document',
                'icon'         => 'fa-book',
                'order_column' => 17,
                'published'    => true,
            ],
            [
                'term'         => 'Arrêt de paiement',
                'definition'   => 'Suspension du versement d\'une pension, décidée en cas de fraude, de décès non déclaré, ou sur demande du bénéficiaire.',
                'category'     => 'procedure',
                'icon'         => 'fa-book',
                'order_column' => 18,
                'published'    => true,
            ],
        ];

        foreach ($terms as $data) {
            GlossaireTerm::firstOrCreate(['term' => $data['term']], $data);
        }
    }
}
