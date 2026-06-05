<?php

namespace App\Http\Controllers;

use App\Mail\NewsletterMail;
use App\Models\Newsletter;
use App\Models\NewsletterCampaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NewsletterController extends Controller
{
    public function souscription(Request $request)
    {
        $request->validate(['email' => 'required|email|max:255']);

        if (Newsletter::where('email', $request->email)->exists()) {
            return back()->with('error', 'Cette adresse email est déjà inscrite.');
        }

        Newsletter::create([
            'email'             => $request->email,
            'unsubscribe_token' => Newsletter::generateToken(),
        ]);

        return back()->with('success', 'Merci ! Vous êtes maintenant abonné à la newsletter.');
    }

    public function unsubscribe(string $token)
    {
        $subscriber = Newsletter::where('unsubscribe_token', $token)->first();

        if (!$subscriber) {
            return view('newsletter.unsubscribe', ['status' => 'invalid']);
        }

        $subscriber->delete();

        return view('newsletter.unsubscribe', ['status' => 'success']);
    }

    // ── Admin ─────────────────────────────────────────────────────────────────

    public function adminIndex(Request $request)
    {
        $query = Newsletter::query();

        if ($request->filled('q')) {
            $query->where('email', 'like', '%' . $request->q . '%');
        }

        $subscribers = $query->latest()->paginate(25);
        $total       = Newsletter::count();
        $campaigns   = NewsletterCampaign::with('sender')->latest('sent_at')->take(10)->get();

        return view('admin.newsletter.index', compact('subscribers', 'total', 'campaigns'));
    }

    public function compose()
    {
        $total = Newsletter::count();
        return view('admin.newsletter.compose', compact('total'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'body'    => 'required|string',
        ]);

        $subscribers = Newsletter::all();

        if ($subscribers->isEmpty()) {
            return back()->with('error', 'Aucun abonné à qui envoyer la newsletter.');
        }

        $campaign = NewsletterCampaign::create([
            'subject'          => $request->subject,
            'body'             => $request->body,
            'recipients_count' => $subscribers->count(),
            'sent_by'          => auth()->id(),
            'sent_at'          => now(),
        ]);

        $sent = 0;
        foreach ($subscribers as $subscriber) {
            try {
                Mail::to($subscriber->email)->send(new NewsletterMail($campaign, $subscriber));
                $sent++;
            } catch (\Throwable $e) {
                Log::error('NewsletterMail failed', [
                    'email' => $subscriber->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return redirect()->route('admin.newsletter.admin.index')
            ->with('success', "Newsletter envoyée à {$sent} abonné(s) sur {$subscribers->count()}.");
    }

    public function destroyCampaign(NewsletterCampaign $campaign)
    {
        $campaign->delete();
        return back()->with('success', 'Campagne supprimée de l\'historique.');
    }

    public function destroy(Newsletter $newsletter)
    {
        $newsletter->delete();
        return back()->with('success', 'Abonné supprimé.');
    }

    public function export(): StreamedResponse
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="abonnes-newsletter-' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, ['Email', 'Date d\'inscription'], ';');

            Newsletter::orderBy('created_at')->chunk(200, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [$row->email, $row->created_at->format('d/m/Y H:i')], ';');
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
