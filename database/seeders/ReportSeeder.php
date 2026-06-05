<?php

namespace Database\Seeders;

use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReportSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();
        if (! $admin) {
            return;
        }

        $reports = [
            [
                'title' => 'Rapport annuel d\'activité 2025',
                'year' => 2025,
                'description' => 'Bilan complet des activités de la Direction des Pensions Civiles pour l\'exercice 2025 : traitement des dossiers, statistiques des bénéficiaires et perspectives.',
                'file_name' => 'rapport_annuel_2025.pdf',
                'file_path' => 'reports/placeholder.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 2_456_320,
                'status' => 'published',
                'published_at' => now()->subDays(30),
                'created_by' => $admin->id,
            ],
            [
                'title' => 'Rapport semestriel — 1er semestre 2025',
                'year' => 2025,
                'description' => 'Rapport d\'activité couvrant la période janvier–juin 2025 avec les indicateurs de performance du service.',
                'file_name' => 'rapport_semestriel_s1_2025.pdf',
                'file_path' => 'reports/placeholder.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 1_234_560,
                'status' => 'published',
                'published_at' => now()->subDays(120),
                'created_by' => $admin->id,
            ],
            [
                'title' => 'Note de cadrage budgétaire 2026',
                'year' => 2026,
                'description' => 'Document de cadrage présentant les orientations budgétaires de la Direction des Pensions Civiles pour l\'année 2026.',
                'file_name' => 'note_cadrage_budgetaire_2026.pdf',
                'file_path' => 'reports/placeholder.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 876_240,
                'status' => 'published',
                'published_at' => now()->subDays(15),
                'created_by' => $admin->id,
            ],
            [
                'title' => 'Rapport annuel d\'activité 2024',
                'year' => 2024,
                'description' => 'Bilan des activités de l\'exercice 2024 avec l\'évolution du nombre de dossiers traités et les délais de traitement moyens.',
                'file_name' => 'rapport_annuel_2024.pdf',
                'file_path' => 'reports/placeholder.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 3_102_800,
                'status' => 'published',
                'published_at' => now()->subDays(200),
                'created_by' => $admin->id,
            ],
        ];

        foreach ($reports as $data) {
            Report::updateOrCreate(
                ['title' => $data['title'], 'year' => $data['year']],
                $data
            );
        }
    }
}
