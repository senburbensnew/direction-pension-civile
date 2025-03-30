<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class BankTransferRequests extends Component
{
    /**
     * Create a new component instance.
     */
    public $pensionTypes;
    public $civilStatuses;
    public $genders;
    public $pensionCategories;

    public function __construct($pensionTypes, $civilStatuses, $genders, $pensionCategories)
    {
        $this->pensionTypes = $pensionTypes;
        $this->civilStatuses = $civilStatuses;
        $this->genders = $genders;
        $this->pensionCategories = $pensionCategories;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.bank-transfer-requests');
    }
}
