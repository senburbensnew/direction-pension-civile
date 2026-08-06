<?php

namespace Database\Seeders;

use App\Models\PublicationType;
use Illuminate\Database\Seeder;

class PublicationTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'loi',         'label' => 'Loi',             'icon' => 'fa-gavel',         'badge_class' => 'bg-red-100 text-red-700',       'order_column' => 1],
            ['code' => 'decret',      'label' => 'Décret',          'icon' => 'fa-scroll',        'badge_class' => 'bg-orange-100 text-orange-700', 'order_column' => 2],
            ['code' => 'circulaire',  'label' => 'Circulaire',      'icon' => 'fa-envelope-open', 'badge_class' => 'bg-blue-100 text-blue-700',     'order_column' => 3],
            ['code' => 'avis',        'label' => 'Avis',            'icon' => 'fa-bullhorn',      'badge_class' => 'bg-amber-100 text-amber-700',   'order_column' => 4],
            ['code' => 'document',    'label' => 'Document',        'icon' => 'fa-file-alt',      'badge_class' => 'bg-green-100 text-green-700',   'order_column' => 5],
            ['code' => 'texte',       'label' => 'Texte officiel',  'icon' => 'fa-file-contract', 'badge_class' => 'bg-purple-100 text-purple-700', 'order_column' => 6],
            ['code' => 'autre',       'label' => 'Autre',           'icon' => 'fa-paperclip',     'badge_class' => 'bg-gray-100 text-gray-700',     'order_column' => 7],
        ];

        foreach ($types as $type) {
            PublicationType::updateOrCreate(['code' => $type['code']], $type);
        }
    }
}
