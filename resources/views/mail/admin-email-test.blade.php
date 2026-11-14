<x-mail::message>
# Test d’envoi e-mail

{{ $bodyText }}

Cordialement,<br>
{{ config('app.name') }}
</x-mail::message>