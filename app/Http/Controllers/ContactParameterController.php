<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\UpdateContactParametersRequest;
use App\Models\ContactSubject;
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

        $subjects = ContactSubject::ordered()->get();

        return view('admin.contact-parameters.index', compact('params', 'subjects'));
    }

    public function update(UpdateContactParametersRequest $request)
    {
        $data = $request->validated();

        foreach ($data as $name => $value) {
            DB::table('parameters')->updateOrInsert(
                ['name' => $name],
                ['name' => $name, 'description' => $name, 'value' => $value ?? '']
            );
        }

        return back()->with('success', 'Informations de contact mises à jour.');
    }
}
