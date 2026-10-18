<?php

namespace App\Http\Controllers;

use App\Http\Requests\NewsletterResubscribeRequest;
use App\Http\Requests\NewsletterSubscribeRequest;
use App\Models\Newsletter;
use App\Models\NewsletterCampaign;
use App\Services\Newsletter\NewsletterSubscribeResultType;
use App\Services\Newsletter\NewsletterSubscriptionService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NewsletterController extends Controller
{
    public function souscription(
        NewsletterSubscribeRequest $request,
        NewsletterSubscriptionService $subscriptions,
    ) {
        $preferences = array_values(array_filter($request->input('preferences', [])));

        $result = $subscriptions->subscribe(
            $request->validated('email'),
            $request,
            locale: $request->input('locale', app()->getLocale()),
            audienceGroup: $request->input('audience_group', 'externe'),
            preferences: $preferences,
            userId: $request->user()?->id,
        );

        return back()->with($result->flashKey(), $result->message);
    }

    public function confirm(string $token, NewsletterSubscriptionService $subscriptions)
    {
        $result = $subscriptions->confirm($token);

        return view('newsletter.confirm', [
            'status' => $result->status,
            'message' => $result->message,
        ]);
    }

    public function unsubscribe(string $token, NewsletterSubscriptionService $subscriptions)
    {
        if (! $subscriptions->unsubscribe($token)) {
            return view('newsletter.unsubscribe', ['status' => 'invalid']);
        }

        return view('newsletter.unsubscribe', ['status' => 'success']);
    }

    public function showResubscribe()
    {
        return view('newsletter.resubscribe');
    }

    public function resubscribe(
        NewsletterResubscribeRequest $request,
        NewsletterSubscriptionService $subscriptions,
    ) {
        $result = $subscriptions->resubscribe($request->validated('email'), $request);

        if ($result->type === NewsletterSubscribeResultType::ResubscribeRequired) {
            return back()->with('error', $result->message);
        }

        return back()->with($result->flashKey(), $result->message);
    }

    // ── Admin (legacy entry points — see Admin\Newsletter* controllers) ───────

    public function adminIndex(Request $request)
    {
        return app(Admin\NewsletterSubscriberController::class)->index($request);
    }

    public function compose()
    {
        return redirect()->route('admin.newsletter.campaigns.create');
    }

    public function send(Request $request)
    {
        return redirect()->route('admin.newsletter.campaigns.create')
            ->with('info', 'Utilisez l’éditeur de campagne pour enregistrer un brouillon ou envoyer.');
    }

    public function destroy(Newsletter $newsletter)
    {
        return app(Admin\NewsletterSubscriberController::class)->destroy($newsletter);
    }

    public function export(): StreamedResponse
    {
        return app(Admin\NewsletterSubscriberController::class)->export();
    }

    public function destroyCampaign(NewsletterCampaign $campaign)
    {
        return app(Admin\NewsletterCampaignController::class)->destroy($campaign);
    }
}
