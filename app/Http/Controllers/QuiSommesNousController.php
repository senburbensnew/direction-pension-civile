<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class QuiSommesNousController extends Controller
{
    public function mots()
    {
        return view('quisommesnous.mots');
    }

    public function missions()
    {
        return view('quisommesnous.missions');
    }
    public function historique()
    {
        return view('quisommesnous.historique');
    }

    public function structureOrganique()
    {
        return view('quisommesnous.structure-organique');
    }

    public function financement()
    {
        return view('quisommesnous.financement');
    }
}
