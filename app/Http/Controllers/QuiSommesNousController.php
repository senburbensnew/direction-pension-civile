<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class QuiSommesNousController extends Controller
{
    public function mots(Request $request)
    {
        $role = $request->query('role');

        // Allowed roles
        $allowed = ['ministre', 'directeur-general', 'directeur'];

        // If role NOT allowed → redirect to home
        if (!in_array($role, $allowed)) {
            return redirect()->route('home');
        }

        return view('quisommesnous.mots', compact('role'));
    }

   public function profil(Request $request)
    {
        $role = $request->query('role');

        // Allowed roles
        $allowed = ['ministre', 'directeur-general', 'directeur'];

        // If role NOT allowed → redirect to home
        if (!in_array($role, $allowed)) {
            return redirect()->route('home');
        }

        return view('quisommesnous.profil', compact('role'));
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
