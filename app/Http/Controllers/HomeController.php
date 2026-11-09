<?php

namespace App\Http\Controllers;

use App\Models\Actualite;
use App\Models\Carousel;
use App\Models\Report;

class HomeController extends Controller
{
    public function index()
    {
        $latestActualites = Actualite::with('images')
            ->where('published', true)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        $recentReports = Report::where('status', 'published')
            ->orderBy('published_at', 'desc')
            ->take(3)
            ->get();

        $carousels = Carousel::where('status', true)
            ->ordered()
            ->get();

        return view('home', compact('latestActualites', 'recentReports', 'carousels'));
    }
}
