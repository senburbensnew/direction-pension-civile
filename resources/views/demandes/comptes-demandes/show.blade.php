@php
    $routePrefix = $routePrefix ?? 'formalites';
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight truncate">Création de compte</h2>
    </x-slot>

    <x-portal-page bottom="pb-6" class="space-y-6">
        @include('comptes-demandes._detail')
    </x-portal-page>
</x-app-layout>
