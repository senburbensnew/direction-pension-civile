<?php

namespace Database\Seeders;

use App\Models\InstitutionImage;
use Illuminate\Database\Seeder;

class InstitutionImageSeeder extends Seeder
{
    public function run(): void
    {
        if (InstitutionImage::exists()) {
            return;
        }

        $files = [
            'KEV_6728.jpg', 'KEV_6750.jpg', 'KEV_6792.jpg', 'KEV_6804.jpg',
            'KEV_6888.jpg', 'KEV_6916.jpg', 'KEV_6984.jpg', 'KEV_7043.jpg',
            'KEV_7055.jpg', 'KEV_7080.jpg', 'KEV_7117.jpg', 'KEV_7157.jpg',
        ];

        foreach ($files as $i => $file) {
            InstitutionImage::create([
                'title'  => null,
                'image'  => 'images/carousel/' . $file,
                'order'  => $i + 1,
                'active' => true,
            ]);
        }
    }
}
