<?php

namespace Database\Seeders;

use App\Models\Publication;
use Illuminate\Database\Seeder;

class PublicationSeeder extends Seeder
{
    public function run(): void
    {
        $publications = [
            // From original hardcoded textes_publication view (commit 4ae4dd1) — Documents légaux
            [
                'title'        => 'Arrêté sur la Comptabilité Publique',
                'description'  => 'Arrêté réglementant les règles de comptabilité publique applicables aux institutions de l\'État haïtien.',
                'type'         => 'decret',
                'file_path'    => 'documents/arrete-sur-la-comptabilite-pub.pdf',
                'url'          => null,
                'order_column' => 1,
                'published'    => true,
            ],
            [
                'title'        => 'Décret portant organisation de l\'administration centrale de l\'État',
                'description'  => 'Décret définissant la structure et le fonctionnement de l\'administration centrale en Haïti.',
                'type'         => 'decret',
                'file_path'    => 'documents/decret-portant-organisation-de-l-administration-centrale-de-l-etat.pdf',
                'url'          => null,
                'order_column' => 2,
                'published'    => true,
            ],
            [
                'title'        => 'Arrêté définissant les règles déontologiques applicables aux agents de la Fonction Publique',
                'description'  => 'Arrêté établissant le code de conduite et les obligations éthiques des fonctionnaires haïtiens.',
                'type'         => 'decret',
                'file_path'    => 'documents/arrete-definissant-la-regle-deontologique-applicable-aux-agents-de-la-fp.pdf',
                'url'          => null,
                'order_column' => 3,
                'published'    => true,
            ],
            [
                'title'        => 'Décret réorganisant le ministère de l\'Économie et des Finances',
                'description'  => 'Décret portant réorganisation structurelle du MEF et redéfinissant ses attributions.',
                'type'         => 'decret',
                'file_path'    => 'documents/decret-reorganisant-le-mef.pdf',
                'url'          => null,
                'order_column' => 4,
                'published'    => true,
            ],
            [
                'title'        => 'Décret du 09 octobre 2015 sur la pension civile de retraite',
                'description'  => 'Texte de référence fixant les modalités de calcul et d\'attribution de la pension civile de retraite en Haïti.',
                'type'         => 'decret',
                'file_path'    => 'documents/decret-su-09-octobre-2015-sur-la-pension-civile-de-retraite.pdf',
                'url'          => null,
                'order_column' => 5,
                'published'    => true,
            ],
            [
                'title'        => 'Décret portant révision du Statut Général de la Fonction Publique',
                'description'  => 'Révision du statut général encadrant les droits, obligations et carrières des agents de l\'État.',
                'type'         => 'decret',
                'file_path'    => 'documents/statut-general-fonction-publique-2005.pdf',
                'url'          => null,
                'order_column' => 6,
                'published'    => true,
            ],
            // Autres documents
            [
                'title'        => 'Conseils pour les Retraités et Futurs Retraités',
                'description'  => 'Guide pratique à l\'attention des pensionnaires et des fonctionnaires en fin de carrière.',
                'type'         => 'document',
                'file_path'    => null,
                'url'          => null,
                'order_column' => 7,
                'published'    => true,
            ],
            [
                'title'        => 'Vos droits à la retraite',
                'description'  => 'Document d\'information synthétisant les droits des fonctionnaires à la pension de retraite.',
                'type'         => 'document',
                'file_path'    => null,
                'url'          => null,
                'order_column' => 8,
                'published'    => true,
            ],
            // Additional entries
            [
                'title'        => 'Circulaire no. 12 — Procédures de liquidation des dossiers de pension',
                'description'  => 'Circulaire interne décrivant les étapes de traitement et de liquidation des dossiers.',
                'type'         => 'circulaire',
                'file_path'    => null,
                'url'          => null,
                'order_column' => 9,
                'published'    => true,
            ],
            [
                'title'        => 'Rapport annuel de la Direction des Pensions — 2024',
                'description'  => 'Bilan des activités, chiffres clés et perspectives de la Direction pour l\'année 2024.',
                'type'         => 'autre',
                'file_path'    => null,
                'url'          => null,
                'order_column' => 10,
                'published'    => false,
            ],
        ];

        foreach ($publications as $data) {
            Publication::firstOrCreate(['title' => $data['title']], $data);
        }
    }
}
