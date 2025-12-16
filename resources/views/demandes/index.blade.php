@extends('layouts.main')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">

    <!-- Title -->
    <h1 class="text-3xl font-bold text-gray-800 mb-6">
        Mes demandes
    </h1>

    <!-- No demandes -->
    @if($demandes->count() === 0)
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
            <p class="text-yellow-700">Aucune demande trouvée.</p>
        </div>
    @endif

    <!-- List -->
    <div class="space-y-4 mt-4">
        @foreach($demandes as $d)
            <div class="bg-white border border-gray-200 shadow-sm rounded-lg p-5 flex justify-between items-center hover:shadow-md transition">

                <!-- Left section: info -->
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">
                        {{ ucfirst(str_replace('_',' ', $d->type)) }}
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Soumis le : {{ $d->created_at->format('d/m/Y') }}
                    </p>

                    <p class="text-sm mt-2">
                        <span class="font-semibold text-gray-700">Statut :</span>
                        <span
                            class="
                                px-2 py-1 text-xs font-medium rounded
                                @if($d->status === 'soumis') bg-blue-100 text-blue-700
                                @elseif($d->status === 'en_cours') bg-yellow-100 text-yellow-700
                                @elseif($d->status === 'complet') bg-green-100 text-green-700
                                @elseif($d->status === 'rejete') bg-red-100 text-red-700
                                @endif
                            "
                        >
                            {{ ucfirst(str_replace('_',' ', $d->status)) }}
                        </span>
                    </p>
                </div>

                <!-- Right section: link -->
                <div>
                    <a href="{{ route('demandes.show', $d->id) }}"
                       class="inline-block bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                        Voir
                    </a>
                </div>

            </div>
        @endforeach
    </div>

</div>
@endsection
