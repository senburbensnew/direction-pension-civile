<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class QuiSommesNousController extends Controller
{
    public function mots()
    {
        return view('quisommesnous.mots');
    }

}
