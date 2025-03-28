<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FonctionnaireController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!$request->user() || !$request->user()->can('viewFonctionnaireMenu')) {
                abort(403, 'Unauthorized action.');
            }
            return $next($request);
        });
    }
    

    public function demandeEtatCarriere()
    {
        return view('fonctionnaire.career-state-form');
    }

    public function retirementSimulation(){
        return view('fonctionnaire.simulation-retraite-form'); 
    }

    public function demandePension(){
        return view('fonctionnaire.demande_pension'); 
    }
}

