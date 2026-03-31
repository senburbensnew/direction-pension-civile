<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Newsletter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NewsletterController extends Controller
{
    public function souscription(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        if (Newsletter::where('email', $request->email)->exists()) {
            return back()->with('error', 'Cette adresse email est déjà inscrite.');
        }

        Newsletter::create([
            'email'               => $request->email,
            'unsubscribe_token'   => Newsletter::generateToken(),
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

        return view('admin.newsletter.index', compact('subscribers', 'total'));
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
            // BOM for Excel UTF-8
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, ['Email', 'Date d\'inscription'], ';');

            Newsletter::orderBy('created_at')->chunk(200, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->email,
                        $row->created_at->format('d/m/Y H:i'),
                    ], ';');
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
