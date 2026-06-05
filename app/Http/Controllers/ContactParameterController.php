<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContactParameterController extends Controller
{
    private const KEYS = [
        'contact_address', 'contact_phone', 'contact_hours', 'contact_email',
        'contact_map_url', 'social_facebook', 'social_twitter', 'social_linkedin', 'social_youtube',
    ];

    public function index()
    {
        $params = DB::table('parameters')
            ->whereIn('name', self::KEYS)
            ->pluck('value', 'name');

        return view('admin.contact-parameters.index', compact('params'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'contact_address'  => 'required|string|max:255',
            'contact_phone'    => 'required|string|max:50',
            'contact_hours'    => 'required|string|max:100',
            'contact_email'    => 'required|email|max:255',
            'contact_map_url'  => 'nullable|string|max:2000',
            'social_facebook'  => 'nullable|url|max:255',
            'social_twitter'   => 'nullable|url|max:255',
            'social_linkedin'  => 'nullable|url|max:255',
            'social_youtube'   => 'nullable|url|max:255',
        ]);

        foreach ($data as $name => $value) {
            DB::table('parameters')->updateOrInsert(
                ['name' => $name],
                ['name' => $name, 'description' => $name, 'value' => $value ?? '']
            );
        }

        return back()->with('success', 'Informations de contact mises à jour.');
    }
}
