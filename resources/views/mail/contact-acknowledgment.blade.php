<x-mail::message>
# Votre message a bien été enregistré

Bonjour {{ $contact->first_name }},

Nous avons bien reçu votre message concernant **{{ $contact->subjectLabel() }}**.

Notre équipe vous répondra dans les meilleurs délais à l’adresse **{{ $contact->email }}**.

Pour le suivi d’un dossier de pension ou toute démarche nécessitant des pièces officielles, connectez-vous à votre espace sécurisé sur le portail DPC.

<x-mail::button :url="route('login')">
Accéder à l’espace sécurisé
</x-mail::button>

<x-mail::panel>
**Important :** ne transmettez jamais par ce formulaire public votre NIF, des pièces d’identité, des relevés bancaires ou tout autre document sensible.
</x-mail::panel>
</x-mail::message>