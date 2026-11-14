@php
    $tracking = app(\App\Services\Newsletter\NewsletterTrackingService::class);
    $openUrl = $delivery ? $tracking->openPixelUrl($delivery) : null;
    $unsubscribeUrl = $subscriber->unsubscribe_token
        ? route('newsletter.unsubscribe', $subscriber->unsubscribe_token)
        : url('/');
@endphp
<x-mail::message>
# {{ $campaign->subject }}

{!! $campaign->renderHtmlBody() !!}

---

Vous recevez cet e-mail car vous êtes abonné à la newsletter de la Direction de la Pension Civile.

<x-mail::button :url="$unsubscribeUrl">
Se désabonner
</x-mail::button>
</x-mail::message>
@if($openUrl)
<img src="{{ $openUrl }}" width="1" height="1" alt="" style="display:none;">
@endif