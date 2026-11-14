<x-mail::message>
# Nouveau message de contact

**Expéditeur :** {{ $contact->first_name }} {{ $contact->last_name }}

**Adresse e-mail :** {{ $contact->email }}

**Téléphone :** {{ $contact->telephone }}

**Sujet :** {{ $contact->subjectLabel() }}

**Message :**

<x-mail::panel>
{!! nl2br(e($contact->message)) !!}
</x-mail::panel>

Reçu le {{ $contact->created_at->format('d/m/Y à H:i') }}. Pour répondre, utilisez le bouton **Répondre** — l’adresse de l’expéditeur est déjà configurée.
</x-mail::message>