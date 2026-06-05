<?php

namespace Database\Seeders;

use App\Models\MediathequeItem;
use Illuminate\Database\Seeder;

class MediathequeItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'title'        => 'Direction Pension Civile',
                'description'  => 'Presentation audio de la Direction des Pensions Civiles.',
                'type'         => 'audio',
                'file_path'    => 'media/audios/Direction Pension Civile.mp3',
                'url'          => null,
                'order_column' => 1,
                'published'    => true,
            ],
            [
                'title'        => 'PRAP',
                'description'  => "Enregistrement audio relatif au Programme de Reforme de l'Administration Publique.",
                'type'         => 'audio',
                'file_path'    => 'media/audios/PRAP.mp3',
                'url'          => null,
                'order_column' => 2,
                'published'    => true,
            ],
            [
                'title'        => 'Retraite Anticipee',
                'description'  => 'Explication audio des conditions et procedures de la retraite anticipee.',
                'type'         => 'audio',
                'file_path'    => 'media/audios/Retraite Anticipee.mp3',
                'url'          => null,
                'order_column' => 3,
                'published'    => true,
            ],
            [
                'title'        => 'Comment calculer le montant de la pension',
                'description'  => 'Guide audio expliquant la methode de calcul du montant de la pension civile.',
                'type'         => 'audio',
                'file_path'    => 'media/audios/Comment calculer le montant de la pension.mp3',
                'url'          => null,
                'order_column' => 4,
                'published'    => true,
            ],
            [
                'title'        => 'Comment faire une demande de pension',
                'description'  => "Guide audio etape par etape pour soumettre une demande de pension aupres de la Direction.",
                'type'         => 'audio',
                'file_path'    => 'media/audios/Comment faire une demande de pension.mp3',
                'url'          => null,
                'order_column' => 5,
                'published'    => true,
            ],
            // Informations utiles images — files must be in storage/app/public/mediatheque/images/
            ['title' => 'Photo institutionnelle 1', 'description' => null, 'type' => 'image', 'file_path' => 'mediatheque/images/photo_2025-11-18_23-36-39.jpg', 'url' => null, 'order_column' => 10, 'published' => true],
            ['title' => 'Photo institutionnelle 2', 'description' => null, 'type' => 'image', 'file_path' => 'mediatheque/images/photo_2025-11-18_23-36-43.jpg', 'url' => null, 'order_column' => 11, 'published' => true],
            ['title' => 'Photo institutionnelle 3', 'description' => null, 'type' => 'image', 'file_path' => 'mediatheque/images/photo_2025-11-18_23-36-46.jpg', 'url' => null, 'order_column' => 12, 'published' => true],
            ['title' => 'Photo institutionnelle 4', 'description' => null, 'type' => 'image', 'file_path' => 'mediatheque/images/photo_2025-11-18_23-36-49.jpg', 'url' => null, 'order_column' => 13, 'published' => true],
            ['title' => 'Photo institutionnelle 5', 'description' => null, 'type' => 'image', 'file_path' => 'mediatheque/images/photo_2025-11-18_23-36-52.jpg', 'url' => null, 'order_column' => 14, 'published' => true],
            ['title' => 'Photo institutionnelle 6', 'description' => null, 'type' => 'image', 'file_path' => 'mediatheque/images/photo_2025-11-18_23-36-55.jpg', 'url' => null, 'order_column' => 15, 'published' => true],
        ];

        foreach ($items as $data) {
            MediathequeItem::firstOrCreate(['title' => $data['title']], $data);
        }
    }
}
