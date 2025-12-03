<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

class MediaController extends Controller
{
    public function index()
    {
        $images = File::files(public_path('media/images'));
        $videos = File::files(public_path('media/videos'));
        $audios = File::files(public_path('media/audios'));

        return view('communication.mediatheque', [
            'images' => $images,
            'videos' => $videos,
            'audios' => $audios
        ]);
    }
}
