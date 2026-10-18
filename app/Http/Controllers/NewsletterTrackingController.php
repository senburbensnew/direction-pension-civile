<?php

namespace App\Http\Controllers;

use App\Services\Newsletter\NewsletterTrackingService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NewsletterTrackingController extends Controller
{
    public function open(string $token, NewsletterTrackingService $tracking)
    {
        $tracking->recordOpen($token);

        $pixel = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

        return response($pixel, Response::HTTP_OK, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    public function click(string $token, Request $request, NewsletterTrackingService $tracking)
    {
        $encoded = $request->query('url', '');
        $target = base64_decode((string) $encoded, true) ?: '';

        if ($target === '' || ! filter_var($target, FILTER_VALIDATE_URL)) {
            abort(404);
        }

        $tracking->recordClick($token);

        return redirect()->away($target);
    }
}
