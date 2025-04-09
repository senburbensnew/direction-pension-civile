<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SignaturePad extends Component
{
    /**
     * Create a new component instance.
     */
    public $name;
    public $inputId;
    public $canvasId;

    public function __construct($name = 'signature')
    {
        $this->name = $name;
        $this->inputId = "$name-input";
        $this->canvasId = "$name-pad";
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.signature-pad');
    }
}
