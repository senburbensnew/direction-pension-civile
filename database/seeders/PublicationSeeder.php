<?php

namespace Database\Seeders;

use App\Models\Publication;
use Illuminate\Database\Seeder;

class PublicationSeeder extends Seeder
{
    public function run(): void
    {
        $publications = [
            // Décrets / arrêtés
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

            // Avis de liquidation
            [
                'title'        => 'Avis de liquidation No 2 publié le 4 janvier 2024',
                'description'  => 'Avis de liquidation n° 2 publié le 4 janvier 2024.',
                'type'         => 'avis',
                'file_path'    => 'documents/avis-de-liquidation-no-2-publie-le-4-janvier-2024.pdf',
                'url'          => null,
                'order_column' => 7,
                'published'    => true,
            ],
            [
                'title'        => 'Avis de liquidation No 4 publié le 10 février 2026',
                'description'  => 'Avis de liquidation n° 4 publié le 10 février 2026.',
                'type'         => 'avis',
                'file_path'    => 'documents/avis-de-liquidation-no-4-publie-le-10-fevrier-2026.pdf',
                'url'          => null,
                'order_column' => 8,
                'published'    => true,
            ],
            [
                'title'        => 'Avis de liquidation No 56 publié le 28 août 2025',
                'description'  => 'Avis de liquidation n° 56 publié le 28 août 2025.',
                'type'         => 'avis',
                'file_path'    => 'documents/avis-de-liquidation-no-56-publie-le-28-aout-2025.pdf',
                'url'          => null,
                'order_column' => 9,
                'published'    => true,
            ],

            // Avis rectificatifs
            [
                'title'        => 'Avis rectificatif No 2 publié le 6 janvier 2026',
                'description'  => 'Avis rectificatif n° 2 publié le 6 janvier 2026.',
                'type'         => 'avis',
                'file_path'    => 'documents/avis-rectificatif-no-2-publie-le-6-jan-2026.pdf',
                'url'          => null,
                'order_column' => 10,
                'published'    => true,
            ],
            [
                'title'        => 'Avis Rectificatif No 22 publié le 24 mai 2024',
                'description'  => 'Avis rectificatif n° 22 publié le 24 mai 2024.',
                'type'         => 'avis',
                'file_path'    => 'documents/avis-rectificatif-no-22-publie-le-24-mai-2024.pdf',
                'url'          => null,
                'order_column' => 11,
                'published'    => true,
            ],

            // Documents d'information
            [
                'title'        => 'Conseils pour les Retraités et Futurs Retraités',
                'description'  => 'Guide pratique à l\'attention des pensionnaires et des fonctionnaires en fin de carrière.',
                'type'         => 'document',
                'file_path'    => 'documents/conseils-pour-les-retraites-et-futurs-retraites.docx',
                'url'          => null,
                'order_column' => 12,
                'published'    => true,
            ],
            [
                'title'        => 'Vos droits à la retraite',
                'description'  => 'Document d\'information synthétisant les droits des fonctionnaires à la pension de retraite.',
                'type'         => 'document',
                'file_path'    => 'documents/vos-droits-a-la-retraite.docx',
                'url'          => null,
                'order_column' => 13,
                'published'    => true,
            ],
            [
                'title'        => 'ACE Scanner — 8 juin 2026',
                'description'  => 'Document scanné du 8 juin 2026.',
                'type'         => 'document',
                'file_path'    => 'documents/ACE Scanner_2026_06_08.pdf',
                'url'          => null,
                'order_column' => 14,
                'published'    => true,
            ],
        ];

        foreach ($publications as $data) {
            Publication::updateOrCreate(['title' => $data['title']], $data);
        }
    }
}
