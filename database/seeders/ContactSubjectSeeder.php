<?php

namespace Database\Seeders;

use App\Models\ContactSubject;
use Illuminate\Database\Seeder;

class ContactSubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            ['slug' => 'pension', 'label' => 'Question sur les pensions', 'position' => 1, 'allows_custom' => false],
            ['slug' => 'documents', 'label' => 'Demande de documents', 'position' => 2, 'allows_custom' => false],
            ['slug' => 'rendezvous', 'label' => 'Prise de rendez-vous', 'position' => 3, 'allows_custom' => false],
            ['slug' => 'autre', 'label' => 'Autre (préciser le sujet)', 'position' => 4, 'allows_custom' => true],
        ];

        foreach ($subjects as $subject) {
            ContactSubject::updateOrCreate(
                ['slug' => $subject['slug']],
                [
                    'label' => $subject['label'],
                    'position' => $subject['position'],
                    'is_active' => true,
                    'allows_custom' => $subject['allows_custom'],
                ]
            );
        }
    }
}
