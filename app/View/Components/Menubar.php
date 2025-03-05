<?php

namespace App\View\Components;

use Illuminate\View\Component;

class MenuBar extends Component
{
    public $menuItems;

    public function __construct()
    {
        // Fetch menu items from a database or config
        $this->menuItems = [
            ['name' => 'Home', 'url' => '/'],
            ['name' => 'About', 'url' => '/about', 'children' => [
                ['name' => 'Team', 'url' => '/team'],
                ['name' => 'History', 'url' => '/history'],
            ]],
        ];
    }

    public function render()
    {
        return view('components.menubar');
    }
}