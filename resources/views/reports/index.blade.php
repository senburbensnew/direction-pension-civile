@extends('layouts.main')

@section('content')
<div class="container mx-auto p-6">
    <!-- Formulaire de recherche -->
    <form method="GET" class="mb-8 flex flex-col md:flex-row items-center justify-center gap-3">
        <input
            name="q"
            value="{{ request('q') }}"
            class="w-full md:w-1/3 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Recherche par titre ou année">
        <button class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-lg shadow hover:bg-blue-700 transition">
            Rechercher
        </button>
    </form>

    @if($reports->count() === 0)
        <p class="text-center text-gray-500">Aucun rapport trouvé.</p>
    @else
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($reports as $report)
                <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition transform hover:-translate-y-1 p-6 flex flex-col justify-between">
                    <!-- Header -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-1">{{ $report->title }}</h3>
                        <p class="text-sm text-gray-500">Année : {{ $report->year ?? '—' }}</p>
                    </div>

                    <!-- Description -->
                    <p class="text-gray-600 mt-4 flex-1 line-clamp-4">{{ Str::limit($report->description, 150) }}</p>

                    <!-- Actions -->
                    <div class="mt-6 flex gap-3">
                        <a href="{{ route('reports.show', $report) }}" class="flex-1 text-center px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
                            Voir
                        </a>
                        <a href="{{ route('reports.download', $report) }}" class="flex-1 text-center px-4 py-2 border border-gray-300 rounded-lg shadow hover:bg-gray-100 transition">
                            Télécharger
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8 flex justify-center">
            {{ $reports->links() }}
        </div>
    @endif

</div>
@endsection
