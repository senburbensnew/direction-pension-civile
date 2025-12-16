@extends('layouts.admin')

@section('content')
<div class="container mx-auto p-6">
    <!-- Recherche -->
    <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <!-- Search form -->
        <form method="GET" class="flex space-x-2 flex-1">
            <input
                type="text"
                name="q"
                value="{{ $searchQuery ?? '' }}"
                placeholder="Rechercher par titre ou catégorie"
                class="border rounded px-3 py-2 flex-1"
            >
            <button class="px-4 py-2 bg-blue-600 text-white rounded">
                Rechercher
            </button>
        </form>

        <!-- Create button -->
        <a
            href="{{ route('actualites.create') }}"
            class="inline-flex items-center justify-center text-green-600 whitespace-nowrap"
        >
            + Ajouter
        </a>
    </div>

    <!-- Tableau -->
<div class="overflow-x-auto">
    <table class="w-full border border-gray-200 rounded-lg">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-4 py-2 border">Titre</th>
                <th class="px-4 py-2 border">Catégorie</th>
                <th class="px-4 py-2 border">Description</th>
                <th class="px-4 py-2 border">Statut</th>
                <th class="px-4 py-2 border">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($actualites as $actualite)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 border">{{ $actualite->title }}</td>
                    <td class="px-4 py-2 border">{{ $actualite->category }}</td>
                    <td class="px-4 py-2 border">
                        {{ Str::limit($actualite->description, 60) }}
                    </td>
                    <td class="px-4 py-2 border">
                        <span class="px-2 py-1 rounded text-sm
                            {{ $actualite->published_at ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
                            {{ $actualite->published_at ? 'Publié' : 'Brouillon' }}
                        </span>
                    </td>
                    <td class="px-4 py-2 border gap-3">
                        <a
                            href="{{ route('actualites.edit', $actualite->id) }}"
                            class="text-yellow-600 hover:underline"
                        >
                            Éditer
                        </a>

                        <form
                            action="{{ route('actualites.destroy', $actualite->id) }}"
                            method="POST"
                            onsubmit="return confirm('Supprimer cette actualité ?')"
                        >
                            @csrf
                            @method('DELETE')
                            <button class="text-red-600 hover:underline">
                                Supprimer
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                        Aucune actualité trouvée.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

    <div class="mt-4">
        {{ $actualites->withQueryString()->links() }}
    </div>
</div>
@endsection
