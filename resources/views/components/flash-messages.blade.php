@props([
    'position' => 'top-20',
])

@php
    $skipGlobalFlash = request()->routeIs('demandes.rencontre.create');

    $statusMessages = [
        'profile-updated' => 'Informations mises à jour avec succès.',
        'profile-photo-updated' => 'Photo mise à jour avec succès.',
        'password-updated' => 'Mot de passe mis à jour avec succès.',
    ];
    $statusMessage = $statusMessages[session('status')] ?? null;
@endphp

@if(! $skipGlobalFlash)
    @if(session('success'))
        <x-portal-toast type="success" :message="session('success')" :position="$position">
            @if(session('created_password'))
                <p class="font-mono text-sm mt-1.5">
                    Mot de passe provisoire :
                    <strong>{{ session('created_password') }}</strong>
                </p>
                <p class="text-xs opacity-90 mt-1">
                    Communiquez-le au demandeur (affichage unique sur cette page).
                </p>
            @endif
        </x-portal-toast>
    @elseif($statusMessage)
        <x-portal-toast type="success" :message="$statusMessage" :position="$position" />
    @endif

    @if(session('error'))
        <x-portal-toast type="error" :message="session('error')" :position="$position" />
    @endif

    @if(session('warning'))
        <x-portal-toast type="warning" :message="session('warning')" :position="$position" />
    @endif

    @if(session('info'))
        <x-portal-toast type="success" :message="session('info')" :position="$position" />
    @endif
@endif
