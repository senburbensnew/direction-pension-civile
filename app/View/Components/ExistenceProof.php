<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ExistenceProof extends Component
{
    /**
     * Create a new component instance.
     */
    public $genders;
    public $civilStatuses;
    public $pensionCategories;

    public function __construct($genders, $civilStatuses, $pensionCategories)
    {
        $this->genders = $genders;
        $this->civilStatuses = $civilStatuses;
        $this->pensionCategories = $pensionCategories;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.existence-proof');
    }
}
