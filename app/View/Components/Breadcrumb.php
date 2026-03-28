<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use InvalidArgumentException;

class Breadcrumb extends Component
{
    public array $items;

    public function __construct(array $items)
    {
        if (empty($items)) {
            throw new InvalidArgumentException(
                'Le composant <x-breadcrumb> requiert obligatoirement la propriété :items.'
            );
        }

        $this->items = $items;
    }

    public function render(): View|Closure|string
    {
        return view('components.breadcrumb');
    }
}
