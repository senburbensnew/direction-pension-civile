@extends('layouts.admin')

@section('title', 'Actualités')

@section('breadcrumb')
    <span class="text-gray-700 text-sm">Actualités</span>
@endsection

@section('content')
<div class="space-y-4">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Actualités</h1>
            <p class="text-sm text-gray-500 mt-0.5">Gérez les articles et publications du site.</p>
        </div>
        <div class="flex items-center gap-2">
            <form method="GET" class="flex gap-2">
                <input type="text" name="q" value="{{ request('q') }}"
                    placeholder="Rechercher…"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-52 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <button type="submit"
                    class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg transition-colors">
                    <i class="fas fa-search"></i>
                </button>
                @if(request('q'))
                    <a href="{{ route('admin.actualites.admin.index') }}"
                        class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </form>
            <a href="{{ route('admin.actualites.create') }}"
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors whitespace-nowrap">
                <i class="fas fa-plus"></i> Ajouter
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="flex items-center gap-2 bg-green-50 border border-green-300 text-green-800 rounded-lg px-4 py-3 text-sm">
            <i class="fas fa-check-circle text-green-500"></i> {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Titre</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Catégorie</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center">Images</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Date</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center">Statut</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($actualites as $actualite)
                    <tr class="border-b border-gray-100 last:border-0 hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">
                            {{ Str::limit($actualite->title, 60) }}
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">
                            {{ $actualite->category ?: '—' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center gap-1 text-gray-500 text-xs">
                                <i class="fas fa-image"></i>
                                {{ $actualite->images_count }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-400 whitespace-nowrap text-xs">
                            {{ $actualite->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full
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
                                        class="px-2 py-1 rounded text-xs font-medium transition-colors
                                            {{ $actualite->published
                                                ? 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200'
                                                : 'bg-green-100 text-green-700 hover:bg-green-200' }}">
                                        <i class="fas {{ $actualite->published ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                        {{ $actualite->published ? 'Dépublier' : 'Publier' }}
                                    </button>
                                </form>

                                {{-- Edit --}}
                                <a href="{{ route('admin.actualites.edit', $actualite->id) }}"
                                    class="px-2 py-1 bg-blue-100 text-blue-700 hover:bg-blue-200 rounded text-xs font-medium transition-colors">
                                    <i class="fas fa-pencil-alt"></i> Éditer
                                </a>

                                {{-- View public --}}
                                <a href="{{ route('actualites.show', $actualite->id) }}" target="_blank"
                                    class="px-2 py-1 bg-gray-100 text-gray-600 hover:bg-gray-200 rounded text-xs font-medium transition-colors"
                                    title="Voir sur le site">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>

                                {{-- Delete --}}
                                <form action="{{ route('admin.actualites.destroy', $actualite->id) }}" method="POST"
                                    onsubmit="return confirm('Supprimer cette actualité et toutes ses images ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="px-2 py-1 bg-red-100 text-red-700 hover:bg-red-200 rounded text-xs font-medium transition-colors">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-gray-400">
                            <i class="fas fa-newspaper text-3xl mb-2 block"></i>
                            Aucune actualité trouvée.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($actualites->hasPages())
        <div>{{ $actualites->withQueryString()->links() }}</div>
    @endif

</div>
@endsection
