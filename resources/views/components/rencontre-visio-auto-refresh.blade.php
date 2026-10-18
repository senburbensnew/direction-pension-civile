@props([
    'token',
    'while' => 'en_attente',
])

@if(filled($token) && filled($while))
    <span
        class="hidden"
        data-visio-poll="{{ route('demandes.rencontre.visio.status', $token) }}"
        data-visio-while="{{ $while }}"
        aria-hidden="true"></span>
@endif
