<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ProfilePicture extends Component
{
    /**
     * Create a new component instance.
     */
    public $showLabel;

    public function __construct($showLabel = true)
    {
        $this->showLabel = $showLabel;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.profile-picture');
    }
}
