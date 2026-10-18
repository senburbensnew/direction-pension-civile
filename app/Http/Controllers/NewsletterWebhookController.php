<?php

namespace App\Http\Controllers;

use App\Services\Newsletter\NewsletterWebhookService;
use Illuminate\Http\Request;

class NewsletterWebhookController extends Controller
{
    public function handle(Request $request, NewsletterWebhookService $webhooks)
    {
        $secret = config('newsletter.webhook_secret');
        if ($secret && $request->header('X-Newsletter-Webhook-Secret') !== $secret) {
            abort(403);
        }

        $event = $request->input('event');
        $email = $request->input('email');
        $messageId = $request->input('message_id');

        if ($event === 'bounce' && $email) {
            $webhooks->handleBounce($email, $messageId, $request->all());
        }

        if ($event === 'delivered') {
            $webhooks->handleDelivered($messageId);
        }

        return response()->json(['ok' => true]);
    }
}
