<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Models\Contact;
use App\Models\ContactSubject;
use App\Models\DirectionDepartementale;
use App\Models\Service;
use App\Notifications\ContactFormAcknowledgmentNotification;
use App\Notifications\ContactFormReceivedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ContactController extends Controller
{
    public function index()
    {
        $params = DB::table('parameters')
            ->whereIn('name', ['contact_address', 'contact_phone', 'contact_hours', 'contact_email', 'contact_map_url', 'social_facebook', 'social_twitter', 'social_linkedin', 'social_youtube'])
            ->pluck('value', 'name');

        $subjects = ContactSubject::active()->ordered()->get();
        $directions = DirectionDepartementale::ordered()->get();
        $services = Service::publicOrdered();

        return view('contact.index', [
            'contact' => $params,
            'directions' => $directions,
            'services' => $services,
            'subjects' => $subjects,
            'phoneLines' => config('contact.service_phones', []),
        ]);
    }

    public function store(StoreContactRequest $request)
    {
        $contact = Contact::create([
            ...$request->validatedForStorage(),
            'status' => Contact::STATUS_NOUVEAU,
            'read' => false,
        ]);

        try {
            Notification::route('mail', config('mail.contact_address'))
                ->notify(new ContactFormReceivedNotification($contact));
        } catch (\Throwable $e) {
            Log::error('ContactMail failed', ['error' => $e->getMessage()]);
        }

        try {
            Notification::route('mail', $contact->email)
                ->notify(new ContactFormAcknowledgmentNotification($contact));
        } catch (\Throwable $e) {
            Log::error('ContactAcknowledgmentMail failed', [
                'contact_id' => $contact->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->route('contact')
            ->with('success', 'Votre message a été enregistré. Merci d’avoir contacté la DPC.');
    }

    // ── Admin ──────────────────────────────────────────────────────────────────

    public function adminIndex(Request $request)
    {
        $query = Contact::query();

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', '%'.$request->q.'%')
                    ->orWhere('last_name', 'like', '%'.$request->q.'%')
                    ->orWhere('email', 'like', '%'.$request->q.'%')
                    ->orWhere('telephone', 'like', '%'.$request->q.'%');
            });
        }

        if ($request->filled('status') && in_array($request->status, Contact::STATUSES, true)) {
            $query->where('status', $request->status);
        }

        $contacts = $query->latest()->paginate(20);
        $nouveauCount = Contact::where('status', Contact::STATUS_NOUVEAU)->count();
        $subjectLabels = ContactSubject::query()->pluck('label', 'slug');

        return view('admin.contacts.index', compact('contacts', 'nouveauCount', 'subjectLabels'));
    }

    public function adminShow(Contact $contact)
    {
        if ($contact->status === Contact::STATUS_NOUVEAU) {
            $contact->transitionTo(Contact::STATUS_EN_COURS);
        }

        return view('admin.contacts.show', compact('contact'));
    }

    public function markEnCours(Contact $contact)
    {
        $contact->transitionTo(Contact::STATUS_EN_COURS);

        return back()->with('success', 'Message marqué comme en cours.');
    }

    public function markTraite(Contact $contact)
    {
        $contact->transitionTo(Contact::STATUS_TRAITE);

        return back()->with('success', 'Message marqué comme traité.');
    }

    public function markAllTraite()
    {
        Contact::query()
            ->whereIn('status', [Contact::STATUS_NOUVEAU, Contact::STATUS_EN_COURS])
            ->update([
                'status' => Contact::STATUS_TRAITE,
                'read' => true,
            ]);

        return back()->with('success', 'Tous les messages ont été marqués comme traités.');
    }

    public function adminDestroy(Contact $contact)
    {
        $contact->delete();

        return back()->with('success', 'Message supprimé.');
    }
}
