<?php

namespace App\Http\Controllers;

use App\Mail\ContactMail;
use App\Models\Contact;
use App\Models\ContactSubject;
use App\Models\DirectionDepartementale;
use App\Models\Service;
use App\Rules\Telephone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{
    public function index()
    {
        $params = DB::table('parameters')
            ->whereIn('name', ['contact_address','contact_phone','contact_hours','contact_email','contact_map_url','social_facebook','social_twitter','social_linkedin','social_youtube'])
            ->pluck('value', 'name');

        $subjects = ContactSubject::active()->ordered()->get();
        $directions = DirectionDepartementale::ordered()->get();
        $services = Service::publicOrdered();

        return view('contact.index', [
            'contact'    => $params,
            'directions' => $directions,
            'services'   => $services,
            'subjects'   => $subjects,
        ]);
    }

    public function store(Request $request)
    {
        $subjects = ContactSubject::active()->ordered()->get();
        $customSlugs = $subjects->where('allows_custom', true)->pluck('slug')->values()->all();
        $destinataireKeys = $this->destinataireKeys();

        $validated = $request->validate([
            'first_name'     => 'required|string|max:100',
            'last_name'      => 'required|string|max:100',
            'email'          => 'required|email|max:255',
            'telephone'      => ['required', 'string', 'max:20', new Telephone()],
            'subject'        => ['required', Rule::in($subjects->pluck('slug'))],
            'custom_subject' => ['nullable', 'string', 'max:150', Rule::requiredIf(in_array($request->input('subject'), $customSlugs, true))],
            'destinataire'   => ['required', 'string', Rule::in($destinataireKeys)],
            'message'        => 'required|string|max:' . Contact::MESSAGE_MAX_LENGTH,
        ], [
            'destinataire.required' => 'Veuillez sélectionner un destinataire.',
            'destinataire.in'       => 'Le destinataire sélectionné n’est pas valide.',
            'message.max'           => 'Le message ne peut pas dépasser :max caractères.',
        ]);

        if (in_array($validated['subject'], $customSlugs, true)) {
            $validated['subject'] = trim($validated['custom_subject']);
        }
        unset($validated['custom_subject']);

        $contact = Contact::create($validated);

        try {
            Mail::to(config('mail.from.address'))->send(new ContactMail($contact));
        } catch (\Throwable $e) {
            Log::error('ContactMail failed', ['error' => $e->getMessage()]);
        }

        return redirect()->route('contact')
            ->with('success', 'Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.');
    }

    // ── Admin ──────────────────────────────────────────────────────────────────

    public function adminIndex(Request $request)
    {
        $query = Contact::query();

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->q . '%')
                  ->orWhere('last_name',  'like', '%' . $request->q . '%')
                  ->orWhere('email',      'like', '%' . $request->q . '%')
                  ->orWhere('telephone',  'like', '%' . $request->q . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('read', $request->status === 'read');
        }

        if ($request->filled('destinataire')) {
            $query->where('destinataire', $request->destinataire);
        }

        $contacts  = $query->latest()->paginate(20);
        $unreadCount = Contact::where('read', false)->count();
        $subjectLabels = ContactSubject::query()->pluck('label', 'slug');

        return view('admin.contacts.index', compact('contacts', 'unreadCount', 'subjectLabels'));
    }

    public function adminShow(Contact $contact)
    {
        if (!$contact->read) {
            $contact->update(['read' => true]);
        }

        return view('admin.contacts.show', compact('contact'));
    }

    public function markRead(Contact $contact)
    {
        $contact->update(['read' => true]);
        return back()->with('success', 'Message marqué comme lu.');
    }

    public function markUnread(Contact $contact)
    {
        $contact->update(['read' => false]);
        return back();
    }

    public function markAllRead()
    {
        Contact::where('read', false)->update(['read' => true]);
        return back()->with('success', 'Tous les messages marqués comme lus.');
    }

    public function adminDestroy(Contact $contact)
    {
        $contact->delete();
        return back()->with('success', 'Message supprimé.');
    }

    /**
     * @return list<string>
     */
    private function destinataireKeys(): array
    {
        $serviceKeys = collect(Service::publicCodes())
            ->map(fn (string $code) => Contact::destinataireKey('service', $code));

        $directionKeys = DirectionDepartementale::query()
            ->pluck('abbr')
            ->map(fn (string $abbr) => Contact::destinataireKey('direction', $abbr));

        return $serviceKeys->merge($directionKeys)->values()->all();
    }
}
