<?php

namespace Database\Seeders;

use App\Models\Carousel;
use Illuminate\Database\Seeder;

class CarouselSeeder extends Seeder
{
    public function run(): void
    {
        $slides = [
            [
                'title'       => 'Direction de la Pension Civile',
                'description' => null,
                'image'       => 'images/carousel/KEV_6804.jpg',
                'link'        => null,
                'status'      => true,
                'order'       => 1,
            ],
            [
                'title'       => 'Direction de la Pension Civile',
                'description' => null,
                'image'       => 'images/carousel/KEV_7157.jpg',
                'link'        => null,
                'status'      => true,
                'order'       => 2,
            ],
            [
                'title'       => 'Direction de la Pension Civile',
                'description' => null,
                'image'       => 'images/carousel/KEV_7055.jpg',
                'link'        => null,
                'status'      => true,
                'order'       => 3,
            ],
            [
                'title'       => 'Direction de la Pension Civile',
                'description' => null,
                'image'       => 'images/carousel/KEV_7043.jpg',
                'link'        => null,
                'status'      => true,
                'order'       => 4,
            ],
        ];

        foreach ($slides as $slide) {
            Carousel::updateOrCreate(
                ['image' => $slide['image']],
                $slide
            );
        }
    }
}
