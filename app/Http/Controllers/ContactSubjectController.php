<?php

namespace App\Http\Controllers;

use App\Models\ContactSubject;
use Illuminate\Http\Request;

class ContactSubjectController extends Controller
{
    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = ContactSubject::uniqueSlug($data['label']);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['allows_custom'] = $request->boolean('allows_custom');
        $data['position'] = $data['position'] ?? ((int) ContactSubject::max('position') + 1);

        ContactSubject::create($data);

        return back()->with('success', 'Sujet ajouté à la liste.');
    }

    public function update(Request $request, ContactSubject $contactSubject)
    {
        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['allows_custom'] = $request->boolean('allows_custom');
        $data['position'] = $data['position'] ?? $contactSubject->position;

        $contactSubject->update($data);

        return back()->with('success', 'Sujet mis à jour.');
    }

    public function destroy(ContactSubject $contactSubject)
    {
        $contactSubject->delete();

        return back()->with('success', 'Sujet retiré de la liste.');
    }

    public function toggle(ContactSubject $contactSubject)
    {
        $contactSubject->update(['is_active' => ! $contactSubject->is_active]);

        return back()->with('success', $contactSubject->is_active
            ? 'Sujet visible sur le formulaire.'
            : 'Sujet masqué du formulaire.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'label' => 'required|string|max:150',
            'position' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'allows_custom' => 'nullable|boolean',
        ]);
    }
}
