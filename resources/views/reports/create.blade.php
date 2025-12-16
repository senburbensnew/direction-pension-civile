@extends('layouts.admin')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">
        {{ isset($reportEdit) ? 'Éditer le rapport' : 'Ajouter un rapport' }}
    </h1>
    <form action="{{ isset($reportEdit) ? route('reports.update', $reportEdit->id) : route('reports.store') }}"
          method="POST" enctype="multipart/form-data" class="space-y-4 mb-8">
        @csrf
        @if(isset($reportEdit))
            @method('PUT')
        @endif

        <div>
            <label class="block text-sm">Titre</label>
            <input name="title" value="{{ old('title', $reportEdit->title ?? '') }}" class="w-full border rounded px-3 py-2" required>
        </div>

        <div>
            <label class="block text-sm">Année</label>
            <input name="year" type="number" value="{{ old('year', $reportEdit->year ?? '') }}" class="w-40 border rounded px-3 py-2">
        </div>

        <div>
            <label class="block text-sm">Description</label>
            <textarea name="description" class="w-full border rounded px-3 py-2">{{ old('description', $reportEdit->description ?? '') }}</textarea>
        </div>

        <div>
            <label class="block text-sm">Fichier (PDF uniquement)</label>
            <input type="file" name="file" class="block" accept=".pdf" {{ isset($reportEdit) ? '' : 'required' }}>
            @if(isset($reportEdit) && $reportEdit->file_path)
                <p class="text-sm text-gray-500 mt-1">Fichier actuel :
                    <a href="{{ Storage::url($reportEdit->file_path) }}" target="_blank" class="text-blue-600 underline">Voir PDF</a>
                </p>
            @endif
        </div>

<div>
            <label class="inline-flex items-center">
                <input type="checkbox" name="status" value="published" class="mr-2"
                       {{ (old('status', $reportEdit->status ?? '') === 'published') ? 'checked' : '' }}>
                Publier immédiatement
            </label>
        </div>

        <div>
            <button class="px-4 py-2 bg-green-600 text-white rounded">
                {{ isset($reportEdit) ? 'Mettre à jour' : 'Enregistrer' }}
            </button>
        </div>
    </form>

    <div class="mb-4">
        <form method="GET" class="flex space-x-2">
            <input type="text" name="q" value="{{ $searchQuery ?? '' }}" placeholder="Rechercher par titre ou année" class="border rounded px-3 py-2 flex-1">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Rechercher</button>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border border-gray-200 rounded-lg">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 border">Titre</th>
                    <th class="px-4 py-2 border">Année</th>
                    <th class="px-4 py-2 border">Description</th>
                    <th class="px-4 py-2 border">Fichier</th>
                    <th class="px-4 py-2 border">Statut</th>
                    <th class="px-4 py-2 border">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 border">{{ $report->title }}</td>
                        <td class="px-4 py-2 border">{{ $report->year }}</td>
                        <td class="px-4 py-2 border">{{ Str::limit($report->description, 50) }}</td>
                        <td class="px-4 py-2 border">
                            @if($report->file_path)
                                <a href="{{ Storage::url($report->file_path) }}" target="_blank" class="text-blue-600 underline">Voir PDF</a>
                            @endif
                        </td>
                        <td class="px-4 py-2 border">{{ $report->status === 'published' ? 'Publié' : 'Brouillon' }}</td>
                        <td class="px-4 py-2 border space-x-2">
                            <a href="{{ route('reports.edit', $report->id) }}" class="px-2 py-1 bg-yellow-500 text-white rounded">Éditer</a>
                            <form action="{{ route('reports.destroy', $report->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce rapport ?')" class="px-2 py-1 bg-red-600 text-white rounded">Supprimer</button>
                            </form>
                            <form action="{{ route('reports.toggle', $report->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-2 py-1 bg-blue-600 text-white rounded">
                                    {{ $report->status === 'published' ? 'Mettre en brouillon' : 'Publier' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-2 text-center text-gray-500">Aucun rapport trouvé.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $reports->withQueryString()->links() }}
    </div>
</div>
@endsection
