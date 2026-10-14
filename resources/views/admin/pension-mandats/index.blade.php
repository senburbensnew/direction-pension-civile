<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Validation des mandats pension</h2>
    </x-slot>

    <x-portal-page class="max-w-6xl space-y-4">
        @include('admin.pension-mandats._list', compact('mandats', 'demandeTypeChoices'))
    </x-portal-page>
</x-app-layout>
