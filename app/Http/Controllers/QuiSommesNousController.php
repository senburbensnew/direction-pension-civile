<?php

namespace App\Http\Controllers;

use App\Models\Official;
use Illuminate\Http\Request;

class QuiSommesNousController extends Controller
{
    public function mots(Request $request)
    {
        $official = Official::findBySlug($request->query('role', ''));

        if (!$official || !$official->active) {
            return redirect()->route('home');
        }

        return view('quisommesnous.mots', compact('official'));
    }

    public function profil(Request $request)
    {
        $official = Official::findBySlug($request->query('role', ''));

        if (!$official || !$official->active) {
            return redirect()->route('home');
        }

        return view('quisommesnous.profil', compact('official'));
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
