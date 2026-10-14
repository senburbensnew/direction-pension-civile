<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Validation des relations représentant</h2>
    </x-slot>

    <x-portal-page class="max-w-6xl space-y-4">
        @if(session('success'))
            <div class="rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif

        @include('formalites.beneficiaire-relations._active_links', ['activeLinks' => $activeLinks])
        @include('formalites.beneficiaire-relations._list', ['demandes' => $demandes])
    </x-portal-page>
</x-app-layout>
