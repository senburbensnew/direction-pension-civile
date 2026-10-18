<?php

namespace App\Http\Controllers\Admin;

use App\Enums\NewsletterSubscriberStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Newsletter;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSuppression;
use App\Services\Newsletter\NewsletterImportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NewsletterSubscriberController extends Controller
{
    public function index(Request $request)
    {
        $query = Newsletter::query()->with('user');

        if ($request->filled('q')) {
            $query->where('email', 'like', '%'.$request->q.'%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('locale')) {
            $query->where('locale', $request->locale);
        }

        if ($request->filled('audience_group')) {
            $query->where('audience_group', $request->audience_group);
        }

        $subscribers = $query->latest()->paginate(25)->withQueryString();
        $total = Newsletter::count();
        $confirmed = Newsletter::query()->mailable()->count();
        $pending = Newsletter::query()->where('status', NewsletterSubscriberStatusEnum::Pending)->count();
        $campaigns = NewsletterCampaign::with('sender')->latest()->take(15)->get();
        $suppressionCount = NewsletterSuppression::count();

        $stats = [
            'total' => $total,
            'confirmed' => $confirmed,
            'pending' => $pending,
            'unsubscribed' => Newsletter::query()->where('status', NewsletterSubscriberStatusEnum::Unsubscribed)->count(),
            'bounced' => Newsletter::query()->where('status', NewsletterSubscriberStatusEnum::Bounced)->count(),
            'suppressions' => $suppressionCount,
        ];

        return view('admin.newsletter.index', compact('subscribers', 'total', 'campaigns', 'stats'));
    }

    public function importForm()
    {
        return view('admin.newsletter.import');
    }

    public function import(Request $request, NewsletterImportService $importService)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
            'require_consent' => ['nullable', 'boolean'],
        ]);

        $result = $importService->importCsv(
            $request->file('file'),
            requireConsentColumn: $request->boolean('require_consent', true),
        );

        $message = "Import terminé : {$result['imported']} ajouté(s), {$result['skipped']} ignoré(s).";
        if ($result['errors'] !== []) {
            $message .= ' '.count($result['errors']).' alerte(s).';
        }

        return redirect()
            ->route('admin.newsletter.admin.index')
            ->with('success', $message)
            ->with('import_errors', $result['errors']);
    }

    public function destroy(Newsletter $newsletter)
    {
        $newsletter->delete();

        return back()->with('success', 'Abonné supprimé.');
    }

    public function export(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="abonnes-newsletter-'.date('Y-m-d').'.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, [
                'Email', 'Statut', 'Locale', 'Audience', 'Confirmé le', 'Inscrit le', 'Consentement',
            ], ';');

            Newsletter::orderBy('created_at')->chunk(200, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->email,
                        $row->status?->value,
                        $row->locale,
                        $row->audience_group,
                        $row->confirmed_at?->format('d/m/Y H:i'),
                        $row->created_at->format('d/m/Y H:i'),
                        $row->consent_source,
                    ], ';');
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
