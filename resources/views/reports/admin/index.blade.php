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
        <h1 class="text-2xl font-bold text-gray-800">Rapports</h1>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.reports.create') }}"
                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg whitespace-nowrap">
                <i class="fas fa-plus mr-1"></i> Ajouter un rapport
            </a>
        </div>
    </div>

    {{-- Stats bar --}}
    @php
        $total     = $reports->total();
        $published = $reports->getCollection()->where('status', 'published')->count();
        $drafts    = $reports->getCollection()->where('status', 'draft')->count();
    @endphp
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white border border-gray-200 rounded-lg px-4 py-3 text-center shadow-sm">
            <p class="text-2xl font-bold text-gray-800">{{ $total }}</p>
            <p class="text-xs text-gray-500 mt-1">Total</p>
        </div>
        <div class="bg-white border border-green-200 rounded-lg px-4 py-3 text-center shadow-sm">
            <p class="text-2xl font-bold text-green-700">{{ $published }}</p>
            <p class="text-xs text-gray-500 mt-1">Publiés</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg px-4 py-3 text-center shadow-sm">
            <p class="text-2xl font-bold text-gray-500">{{ $drafts }}</p>
            <p class="text-xs text-gray-500 mt-1">Brouillons</p>
        </div>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow-sm border border-gray-200">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Titre</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Année</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Description</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Taille</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600">Statut</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Ajouté le</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($reports as $report)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">
                            {{ Str::limit($report->title, 55) }}
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $report->year ?: '—' }}
                        </td>
                        <td class="px-4 py-3 text-gray-500">
                            {{ Str::limit($report->description, 50) ?: '—' }}
                        </td>
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                            @if($report->file_size)
                                {{ round($report->file_size / 1024) }} Ko
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                {{ $report->status === 'published' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $report->status === 'published' ? 'Publié' : 'Brouillon' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                            {{ $report->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                {{-- Toggle --}}
                                <form action="{{ route('admin.reports.toggle', $report->id) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        title="{{ $report->status === 'published' ? 'Dépublier' : 'Publier' }}"
                                        class="px-2 py-1 rounded text-xs font-medium
                                            {{ $report->status === 'published'
                                                ? 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200'
                                                : 'bg-green-100 text-green-700 hover:bg-green-200' }}">
                                        <i class="fas {{ $report->status === 'published' ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                        {{ $report->status === 'published' ? 'Dépublier' : 'Publier' }}
                                    </button>
                                </form>

                                {{-- Edit --}}
                                <a href="{{ route('admin.reports.edit', $report->id) }}"
                                    class="px-2 py-1 bg-blue-100 text-blue-700 hover:bg-blue-200 rounded text-xs font-medium">
                                    <i class="fas fa-pencil-alt"></i> Éditer
                                </a>

                                {{-- View PDF --}}
                                @if($report->file_path)
                                    <a href="{{ Storage::url($report->file_path) }}" target="_blank"
                                        class="px-2 py-1 bg-gray-100 text-gray-600 hover:bg-gray-200 rounded text-xs font-medium">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                @endif

                                {{-- Delete --}}
                                <form action="{{ route('admin.reports.destroy', $report->id) }}" method="POST"
                                    onsubmit="return confirm('Supprimer ce rapport ?')">
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
                        <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                            <i class="fas fa-file-alt text-3xl mb-2 block"></i>
                            Aucun rapport trouvé.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $reports->links() }}
    </div>
</div>
@endsection
