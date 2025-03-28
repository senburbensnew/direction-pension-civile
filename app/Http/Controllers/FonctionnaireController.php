<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FonctionnaireController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // Ensure user is authenticated
        $this->middleware(function ($request, $next) {
            // Convert roles collection to an array of role names
            $userRoles = Auth::user()->roles->pluck('name')->toArray();
    
            // Check if 'pensionnaire' exists in the array
            if (!in_array('fonctionnaire', $userRoles)) {
                abort(403, 'Unauthorized access');
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

