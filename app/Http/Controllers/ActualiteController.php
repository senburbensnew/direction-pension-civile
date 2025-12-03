<?php

namespace App\Http\Controllers;

use App\Models\Actualite;
use Illuminate\Http\Request;

class ActualiteController extends Controller
{
    public function index()
    {
        $actualites = Actualite::latest()->paginate(6); // pagination
        return view('actualites.index', compact('actualites'));
    }

    public function show($id)
    {
        $actu = Actualite::findOrFail($id);
        return view('actualites.show', compact('actu'));
    }

    public function latestForHome($count = 3)
{
    return Actualite::latest()->take($count)->get();
}

}
