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
            // Homepage "Informations utiles" illustrated cards
            [
                'title'        => 'A ki laj mwen ka pwan pansyon mwen?',
                'description'  => 'Kart enfòmasyon sou laj retrèt pou ajan piblik yo.',
                'type'         => 'image',
                'file_path'    => 'mediatheque/images/photo_2025-11-18_23-36-39.jpg',
                'url'          => null,
                'order_column' => 10,
                'published'    => true,
                'is_featured'  => false,
            ],
            [
                'title'        => "Pandan konbyen tan ou ap peye pou pansyon'w",
                'description'  => 'Kart enfòmasyon sou 60 mwa sèvis obligatwa.',
                'type'         => 'image',
                'file_path'    => 'mediatheque/images/photo_2025-11-18_23-36-43.jpg',
                'url'          => null,
                'order_column' => 11,
                'published'    => true,
                'is_featured'  => false,
            ],
            [
                'title'        => "Enfòmasyon ou dwe konnen sou pansyon'w",
                'description'  => 'Kart enfòmasyon sou dwa ajan piblik yo.',
                'type'         => 'image',
                'file_path'    => 'mediatheque/images/photo_2025-11-18_23-36-46.jpg',
                'url'          => null,
                'order_column' => 12,
                'published'    => true,
                'is_featured'  => false,
            ],
            [
                'title'        => 'Kisa ki pansyon pa revèsibilite an?',
                'description'  => 'Kart enfòmasyon sou pansyon sivivan.',
                'type'         => 'image',
                'file_path'    => 'mediatheque/images/photo_2025-11-18_23-36-49.jpg',
                'url'          => null,
                'order_column' => 13,
                'published'    => true,
                'is_featured'  => false,
            ],
            [
                'title'        => "Dokiman pou mande pansyon'w",
                'description'  => 'Kart enfòmasyon sou dokiman obligatwa.',
                'type'         => 'image',
                'file_path'    => 'mediatheque/images/photo_2025-11-18_23-36-52.jpg',
                'url'          => null,
                'order_column' => 14,
                'published'    => true,
                'is_featured'  => false,
            ],
            [
                'title'        => 'Se kisa PRAP la ye?',
                'description'  => 'Kart enfòmasyon sou pwogram PRAP.',
                'type'         => 'image',
                'file_path'    => 'mediatheque/images/photo_2025-11-18_23-36-55.jpg',
                'url'          => null,
                'order_column' => 15,
                'published'    => true,
                'is_featured'  => false,
            ],
        ];

        foreach ($items as $data) {
            $key = ! empty($data['file_path'])
                ? ['file_path' => $data['file_path']]
                : ['title' => $data['title']];

            MediathequeItem::updateOrCreate($key, $data);
        }
    }
}
