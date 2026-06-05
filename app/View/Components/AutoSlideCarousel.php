<?php

namespace App\View\Components;

use App\Models\InstitutionImage;
use Illuminate\View\Component;

class AutoSlideCarousel extends Component
{
    public array $images;

    public function __construct()
    {
        $this->images = InstitutionImage::active()->ordered()->get()
            ->map(fn ($img) => [
                'src' => $img->imageUrl(),
                'alt' => $img->title ?? 'Institution en images',
            ])->toArray();
    }

    public function render()
    {
        return view('components.auto-slide-carousel');
    }
}
