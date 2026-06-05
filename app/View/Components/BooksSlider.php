<?php

namespace App\View\Components;

use App\Models\MediathequeItem;
use Illuminate\View\Component;

class BooksSlider extends Component
{
    public $items;

    public function __construct()
    {
        $this->items = MediathequeItem::published()
            ->where('type', 'image')
            ->ordered()
            ->get();
    }

    public function render()
    {
        return view('components.books-slider');
    }
}
