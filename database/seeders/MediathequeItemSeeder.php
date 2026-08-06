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
                'title'        => 'Journée d\'information et d\'orientation sur votre pension / retraite',
                'description'  => 'Vidéo en vedette — Jounen enfòmasyon ak oryantasyon sou pansyon.',
                'type'         => 'video',
                'file_path'    => null,
                'url'          => 'https://www.youtube.com/watch?v=8sigu4fUheo',
                'order_column' => 0,
                'published'    => true,
                'is_featured'  => true,
            ],
            [
                'title'        => 'Direction Pension Civile',
                'description'  => 'Presentation audio de la Direction des Pensions Civiles.',
                'type'         => 'audio',
                'file_path'    => 'media/audios/Direction Pension Civile.mp3',
                'url'          => null,
                'order_column' => 1,
                'published'    => true,
                'is_featured'  => false,
            ],
            [
                'title'        => 'PRAP',
                'description'  => "Enregistrement audio relatif au Programme de Reforme de l'Administration Publique.",
                'type'         => 'audio',
                'file_path'    => 'media/audios/PRAP.mp3',
                'url'          => null,
                'order_column' => 2,
                'published'    => true,
                'is_featured'  => false,
            ],
            [
                'title'        => 'Retraite Anticipee',
                'description'  => 'Explication audio des conditions et procedures de la retraite anticipee.',
                'type'         => 'audio',
                'file_path'    => 'media/audios/Retraite Anticipee.mp3',
                'url'          => null,
                'order_column' => 3,
                'published'    => true,
                'is_featured'  => false,
            ],
            [
                'title'        => 'Comment calculer le montant de la pension',
                'description'  => 'Guide audio expliquant la methode de calcul du montant de la pension civile.',
                'type'         => 'audio',
                'file_path'    => 'media/audios/Comment calculer le montant de la pension.mp3',
                'url'          => null,
                'order_column' => 4,
                'published'    => true,
                'is_featured'  => false,
            ],
            [
                'title'        => 'Comment faire une demande de pension',
                'description'  => "Guide audio etape par etape pour soumettre une demande de pension aupres de la Direction.",
                'type'         => 'audio',
                'file_path'    => 'media/audios/Comment faire une demande de pension.mp3',
                'url'          => null,
                'order_column' => 5,
                'published'    => true,
                'is_featured'  => false,
            ],
        ];

        foreach ($items as $data) {
            MediathequeItem::updateOrCreate(['title' => $data['title']], $data);
        }

        // Remove previously seeded default images
        MediathequeItem::where('type', 'image')
            ->where('title', 'like', 'Photo institutionnelle%')
            ->delete();
    }
}
