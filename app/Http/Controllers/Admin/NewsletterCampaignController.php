<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreNewsletterCampaignRequest;
use App\Models\Newsletter;
use App\Models\NewsletterCampaign;
use App\Notifications\NewsletterCampaignNotification;
use App\Services\Newsletter\NewsletterAudienceService;
use App\Services\Newsletter\NewsletterCampaignService;
use App\Services\Newsletter\NewsletterDispatchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class NewsletterCampaignController extends Controller
{
    public function create(NewsletterAudienceService $audience)
    {
        $recipientCount = $audience->count();

        return view('admin.newsletter.campaign-form', [
            'campaign' => new NewsletterCampaign([
                'from_name' => config('newsletter.from_name'),
                'reply_to' => config('newsletter.reply_to'),
                'open_tracking_enabled' => config('newsletter.open_tracking'),
                'click_tracking_enabled' => config('newsletter.click_tracking'),
            ]),
            'recipientCount' => $recipientCount,
        ]);
    }

    public function store(
        StoreNewsletterCampaignRequest $request,
        NewsletterCampaignService $campaigns,
        NewsletterDispatchService $dispatch,
    ) {
        $campaign = $campaigns->createDraft($request->validated(), auth()->id());

        return $this->afterSave($request, $campaign, $campaigns, $dispatch);
    }

    public function edit(NewsletterCampaign $campaign, NewsletterAudienceService $audience)
    {
        $recipientCount = $audience->count($campaign->audience_filter);

        return view('admin.newsletter.campaign-form', compact('campaign', 'recipientCount'));
    }

    public function update(
        StoreNewsletterCampaignRequest $request,
        NewsletterCampaign $campaign,
        NewsletterCampaignService $campaigns,
        NewsletterDispatchService $dispatch,
    ) {
        $campaigns->updateDraft($campaign, $request->validated());

        return $this->afterSave($request, $campaign->fresh(), $campaigns, $dispatch);
    }

    public function show(NewsletterCampaign $campaign)
    {
        $campaign->load(['sender', 'deliveries' => fn ($q) => $q->latest()->limit(50)]);

        return view('admin.newsletter.campaign-show', compact('campaign'));
    }

    public function duplicate(NewsletterCampaign $campaign, NewsletterCampaignService $campaigns)
    {
        $copy = $campaigns->duplicate($campaign, auth()->id());

        return redirect()
            ->route('admin.newsletter.campaigns.edit', $copy)
            ->with('success', 'Brouillon dupliqué.');
    }

    public function test(Request $request, NewsletterCampaign $campaign)
    {
        $request->validate(['test_email' => ['required', 'email']]);

        $stub = new Newsletter([
            'email' => $request->test_email,
            'unsubscribe_token' => Newsletter::generateToken(),
        ]);

        Notification::route('mail', $request->test_email)
            ->notify(new NewsletterCampaignNotification($campaign, $stub));

        return back()->with('success', 'Email de test envoyé à '.$request->test_email);
    }

    public function cancel(NewsletterCampaign $campaign, NewsletterCampaignService $campaigns)
    {
        try {
            $campaigns->cancel($campaign);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Campagne annulée.');
    }

    public function destroy(NewsletterCampaign $campaign)
    {
        if ($campaign->status?->value === 'sending') {
            return back()->with('error', 'Impossible de supprimer une campagne en cours d’envoi.');
        }

        $campaign->delete();

        return back()->with('success', 'Campagne supprimée.');
    }

    private function afterSave(
        Request $request,
        NewsletterCampaign $campaign,
        NewsletterCampaignService $campaigns,
        NewsletterDispatchService $dispatch,
    ) {
        $action = $request->input('action', 'save');

        if ($action === 'schedule') {
            $request->validate(['scheduled_at' => ['required', 'date', 'after:now']]);
            $campaigns->schedule($campaign, $request->date('scheduled_at'), auth()->id());

            return redirect()
                ->route('admin.newsletter.campaigns.edit', $campaign)
                ->with('success', 'Campagne planifiée.');
        }

        if ($action === 'send') {
            $dispatch->dispatchNow($campaign, auth()->id());

            return redirect()
                ->route('admin.newsletter.campaigns.show', $campaign)
                ->with('success', 'Envoi démarré — les emails sont mis en file d’attente.');
        }

        return redirect()
            ->route('admin.newsletter.campaigns.edit', $campaign)
            ->with('success', 'Brouillon enregistré.');
    }
}
