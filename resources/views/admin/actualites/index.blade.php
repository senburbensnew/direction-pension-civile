@extends('layouts.admin')

@section('content')
<div class="container mx-auto p-6">

    @if(session('success'))
        <div class="mb-4 flex items-center gap-2 bg-green-50 border border-green-300 text-green-800 rounded-lg px-4 py-3 text-sm">
            <i class="fas fa-check-circle text-green-500"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <h1 class="text-2xl font-bold text-gray-800">Actualités</h1>

        <div class="flex items-center gap-3">
            <form method="GET" class="flex gap-2">
                <input type="text" name="q" value="{{ request('q') }}"
                    placeholder="Rechercher par titre ou catégorie…"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-64 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg">
                    <i class="fas fa-search mr-1"></i> Rechercher
                </button>
            </form>
            <a href="{{ route('admin.actualites.create') }}"
                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg whitespace-nowrap">
                <i class="fas fa-plus mr-1"></i> Ajouter
            </a>
        </div>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow-sm border border-gray-200">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Titre</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Catégorie</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600">Images</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Date</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600">Statut</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($actualites as $actualite)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">
                            {{ Str::limit($actualite->title, 60) }}
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $actualite->category ?: '—' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center gap-1 text-gray-500">
                                <i class="fas fa-image text-xs"></i>
                                {{ $actualite->images_count }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                            {{ $actualite->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                {{ $actualite->published ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $actualite->published ? 'Publié' : 'Brouillon' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                {{-- Toggle publish --}}
                                <form action="{{ route('admin.actualites.toggle', $actualite->id) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        title="{{ $actualite->published ? 'Dépublier' : 'Publier' }}"
                                        class="px-2 py-1 rounded text-xs font-medium
                                            {{ $actualite->published
                                                ? 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200'
                                                : 'bg-green-100 text-green-700 hover:bg-green-200' }}">
                                        <i class="fas {{ $actualite->published ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                        {{ $actualite->published ? 'Dépublier' : 'Publier' }}
                                    </button>
                                </form>

                                {{-- Edit --}}
                                <a href="{{ route('admin.actualites.edit', $actualite->id) }}"
                                    class="px-2 py-1 bg-blue-100 text-blue-700 hover:bg-blue-200 rounded text-xs font-medium">
                                    <i class="fas fa-pencil-alt"></i> Éditer
                                </a>

                                {{-- View (public) --}}
                                <a href="{{ route('actualites.show', $actualite->id) }}" target="_blank"
                                    class="px-2 py-1 bg-gray-100 text-gray-600 hover:bg-gray-200 rounded text-xs font-medium">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>

                                {{-- Delete --}}
                                <form action="{{ route('admin.actualites.destroy', $actualite->id) }}" method="POST"
                                    onsubmit="return confirm('Supprimer cette actualité et toutes ses images ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="px-2 py-1 bg-red-100 text-red-700 hover:bg-red-200 rounded text-xs font-medium">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                            <i class="fas fa-newspaper text-3xl mb-2 block"></i>
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
