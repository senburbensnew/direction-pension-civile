<?php

namespace App\View\Components;

use App\Models\InstitutionImage;
use Illuminate\View\Component;

class AutoSlideCarousel extends Component
{
    public array $images;

    public function __construct()
    {
        $db = InstitutionImage::active()->ordered()->get();

        if ($db->isNotEmpty()) {
            $this->images = $db->map(fn ($img) => [
                'src' => $img->imageUrl(),
                'alt' => $img->title ?? 'Institution en images',
            ])->toArray();
        } else {
            // Fallback to static assets until DB is populated
            $this->images = array_map(fn ($f) => [
                'src' => asset("images/carousel/$f"),
                'alt' => 'Institution en images',
            ], [
                'KEV_6728.jpg','KEV_6750.jpg','KEV_6792.jpg','KEV_6804.jpg',
                'KEV_6888.jpg','KEV_6916.jpg','KEV_6984.jpg','KEV_7043.jpg',
                'KEV_7055.jpg','KEV_7080.jpg','KEV_7117.jpg','KEV_7157.jpg',
            ]);
        }
    }

    public function render()
    {
        return view('components.auto-slide-carousel');
    }
}
