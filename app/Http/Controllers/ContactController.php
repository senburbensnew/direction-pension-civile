<?php

namespace App\Http\Controllers;

use App\Mail\ContactMail;
use App\Models\Contact;
use App\Models\DirectionDepartementale;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function index()
    {
        $params = DB::table('parameters')
            ->whereIn('name', ['contact_address','contact_phone','contact_hours','contact_email','contact_map_url','social_facebook','social_twitter','social_linkedin','social_youtube'])
            ->pluck('value', 'name');

        return view('contact.index', [
            'contact'    => $params,
            'directions' => DirectionDepartementale::ordered()->get(),
            'services'   => Service::whereNotIn('code', ['direction'])->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email|max:255',
            'subject'    => 'required|in:pension,documents,rendezvous,autre',
            'message'    => 'required|string|max:3000',
        ]);

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
                  ->orWhere('email',      'like', '%' . $request->q . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('read', $request->status === 'read');
        }

        $contacts  = $query->latest()->paginate(20);
        $unreadCount = Contact::where('read', false)->count();

        return view('admin.contacts.index', compact('contacts', 'unreadCount'));
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
}
