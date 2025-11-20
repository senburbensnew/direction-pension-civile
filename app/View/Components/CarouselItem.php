<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CarouselItem extends Component
{
    public array $imageUrls;

    /**
     * Create a new component instance.
     */
    public function __construct($imageUrls = [])
    {
        $this->imageUrls = $imageUrls;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.carousel-item');
    }
}
