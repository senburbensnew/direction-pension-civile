<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FonctionnaireController extends Controller
{
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

