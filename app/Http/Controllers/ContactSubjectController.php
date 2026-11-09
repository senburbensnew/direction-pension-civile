<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\StoreContactSubjectRequest;
use App\Models\ContactSubject;
use Illuminate\Http\Request;

class ContactSubjectController extends Controller
{
    public function store(StoreContactSubjectRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = ContactSubject::uniqueSlug($data['label']);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['allows_custom'] = $request->boolean('allows_custom');
        $data['position'] = $data['position'] ?? ((int) ContactSubject::max('position') + 1);

        ContactSubject::create($data);

        return back()->with('success', 'Sujet ajouté à la liste.');
    }

    public function update(StoreContactSubjectRequest $request, ContactSubject $contactSubject)
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);
        $data['allows_custom'] = $request->boolean('allows_custom');
        $data['position'] = $data['position'] ?? $contactSubject->position;

        $contactSubject->update($data);

        return back()->with('success', 'Sujet mis à jour.');
    }

    public function destroy(Request $request, ContactSubject $contactSubject)
    {
        return back()->with('success', $this->adminDelete($contactSubject, $request));
    }

    public function toggle(ContactSubject $contactSubject)
    {
        $contactSubject->update(['is_active' => ! $contactSubject->is_active]);

        return back()->with('success', $contactSubject->is_active
            ? 'Sujet visible sur le formulaire.'
            : 'Sujet masqué du formulaire.');
    }

}
