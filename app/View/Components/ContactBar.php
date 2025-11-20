<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ContactBar extends Component
{
    public $borderType;

    public function __construct($borderType = 'top')
    {
        $this->borderType = $borderType; // 'top' or 'bottom'
    }

    public function render()
    {
        return view('components.contact-bar');
    }
}
