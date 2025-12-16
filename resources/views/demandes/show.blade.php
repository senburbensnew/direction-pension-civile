@extends('layouts.main')

@section('content')
<div class="max-w-5xl mx-auto py-8 px-4">

    <h1 class="text-3xl font-bold mb-6">Suivi de la demande</h1>

    {{-- Carte informations principales --}}
    <div class="bg-white shadow-lg rounded-xl p-6 mb-8">

        <h2 class="text-xl font-semibold mb-4">
            Dossier #{{ $demande->numero_dossier }}
        </h2>

        <div class="space-y-2 text-gray-700">

            <p><span class="font-semibold">Catégorie :</span> {{ ucfirst($demande->categorie) }}</p>

            <p>
                <span class="font-semibold">Type :</span>
                {{ str_replace('_', ' ', ucfirst($demande->type)) }}
            </p>

            @php
                $color = match($demande->etat) {
                    'reçue' => 'bg-blue-100 text-blue-700',
                    'en analyse' => 'bg-yellow-100 text-yellow-700',
                    'en attente de documents' => 'bg-gray-200 text-gray-800',
                    'validée' => 'bg-green-100 text-green-700',
                    'refusée' => 'bg-red-100 text-red-700',
                    default => 'bg-gray-100 text-gray-700'
                };
            @endphp

            <p>
                <span class="font-semibold">État actuel :</span>
                <span class="px-3 py-1 rounded-full text-sm font-medium {{ $color }}">
                    {{ ucfirst($demande->etat) }}
                </span>
            </p>

            <p>
                <span class="font-semibold">Créée le :</span>
                {{ $demande->created_at->format('d/m/Y H:i') }}
            </p>

        </div>

    </div>

    {{-- Timeline --}}
    <h3 class="text-2xl font-bold mb-4">Historique des mises à jour</h3>

    @if($demande->histories->count() === 0)
        <div class="bg-blue-50 text-blue-700 px-4 py-3 rounded-lg">
            Aucun historique pour cette demande.
        </div>
    @else

        <div class="relative border-l-2 border-blue-500">

            @foreach($demande->histories as $h)

                <div class="ml-6 mb-10 relative">

                    {{-- Badge rond --}}
                    <div class="absolute -left-3 w-6 h-6 rounded-full border-2 border-blue-500 bg-white"></div>

                    <div class="p-4 bg-gray-50 rounded-xl shadow-sm">
                        <h4 class="text-lg font-semibold text-gray-800">
                            {{ ucfirst($h->etat) }}
                        </h4>

                        <p class="text-sm text-gray-500">
                            {{ $h->created_at->format('d/m/Y H:i') }}
                        </p>

                        @if($h->commentaire)
                            <p class="mt-2 text-gray-700">
                                {{ $h->commentaire }}
                            </p>
                        @endif
                    </div>

                </div>

            @endforeach

        </div>

    @endif

</div>
@endsection
