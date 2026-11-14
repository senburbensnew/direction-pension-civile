<x-mail::message>
# Réinitialisation de votre mot de passe

Bonjour {{ $displayName }},

Un administrateur a réinitialisé votre mot de passe pour le portail de la Direction de la Pension Civile.

@if(filled($email))
**Adresse e-mail :** {{ $email }}  
@endif
@if(filled($phone))
**Téléphone :** {{ $phone }}  
@endif

<x-mail::panel>
**Mot de passe temporaire :** {{ $temporaryPassword }}
</x-mail::panel>

<x-mail::button :url="$loginUrl">
Se connecter au portail
</x-mail::button>

Lors de votre prochaine connexion, le système vous demandera de choisir un nouveau mot de passe personnel.

Ne communiquez ce mot de passe que par un canal sécurisé. Si vous n’êtes pas à l’origine de cette demande, contactez immédiatement la Direction de la Pension Civile.
</x-mail::message>