<?php

namespace App\View\Components;

use Illuminate\View\Component;

class VideoCard extends Component
{
    public $title;
    public $videoUrl;

    public function __construct($title = 'Mediatheque/Video', $videoUrl = null)
    {
        $this->title = $title;
        $this->videoUrl = $videoUrl;
    }

    public function render()
    {
        return view('components.video-card');
    }
}