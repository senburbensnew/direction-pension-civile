<?php

namespace App\Http\Controllers;

class StaticPageController extends Controller
{
    public function simulateurCalcul()
    {
        return view('fonctionnaire.simulateur-calcul');
    }

    public function privacy()
    {
        return view('privacy');
    }

    public function terms()
    {
        return view('terms');
    }
}
