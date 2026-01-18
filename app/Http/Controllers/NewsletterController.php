<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Newsletter;

class NewsletterController extends Controller
{
    public function souscription(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        // Check if email already exists
        if (Newsletter::where('email', $request->email)->exists()) {
            return back()->with('error', 'Cette adresse email est déjà inscrite.');
        }

        Newsletter::create([
            'email' => $request->email
        ]);

        return back()->with('success', 'Merci ! Vous êtes maintenant abonné à la newsletter.');
    }
}
