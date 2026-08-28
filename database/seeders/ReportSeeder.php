<?php

namespace Database\Seeders;

use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ReportSeeder extends Seeder
{
    public function run(): void
    {
        $authorId = User::where('email', 'admin@example.com')->value('id')
            ?? User::query()->value('id');

        if (! $authorId) {
            return;
        }

        $reports = [
            [
                'title'       => 'Avis de liquidation de pension — 27 février 2026',
                'year'        => 2026,
                'description' => 'Avis de liquidation de pension pris en faveur de certains agents publics, publié le 27 février 2026.',
                'source'      => 'avis-liquidation-pension-en-faveur-certains-agents-publics.pdf',
                'cover_path'  => 'images/carousel/KEV_6728.jpg',
                'published_at'=> now()->subDays(12),
            ],
            [
                'title'       => 'Rapport d’activités de la Direction de la Pension Civile — 2025',
                'year'        => 2025,
                'description' => 'Bilan annuel des activités, des liquidations traitées et des services rendus aux pensionnaires et fonctionnaires.',
                'source'      => 'decret-su-09-octobre-2015-sur-la-pension-civile-de-retraite.pdf',
                'cover_path'  => 'images/carousel/KEV_6750.jpg',
                'published_at'=> now()->subDays(40),
            ],
            [
                'title'       => 'Note officielle sur le statut général de la Fonction Publique',
                'year'        => 2024,
                'description' => 'Document de référence rappelant le cadre statutaire applicable aux agents de l’État et à la pension civile de retraite.',
                'source'      => 'statut-general-fonction-publique-2005.pdf',
                'cover_path'  => 'images/carousel/KEV_6792.jpg',
                'published_at'=> now()->subDays(95),
            ],
        ];

        foreach ($reports as $data) {
            $source = public_path('documents/' . $data['source']);
            if (! File::exists($source)) {
                continue;
            }

            $storedName = $data['source'];
            $storedPath = 'reports/' . $data['year'] . '/' . $storedName;
            $destination = storage_path('app/public/' . $storedPath);

            File::ensureDirectoryExists(dirname($destination));
            File::copy($source, $destination);

            Report::updateOrCreate(
                ['title' => $data['title']],
                [
                    'year'         => $data['year'],
                    'description'  => $data['description'],
                    'file_name'    => $storedName,
                    'file_path'    => $storedPath,
                    'cover_path'   => $data['cover_path'],
                    'mime_type'    => 'application/pdf',
                    'file_size'    => File::size($source),
                    'status'       => 'published',
                    'published_at' => $data['published_at'],
                    'created_by'   => $authorId,
                ]
            );
        }
    }
}
