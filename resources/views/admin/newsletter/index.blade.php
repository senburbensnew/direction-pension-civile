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
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Newsletter</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $total }} abonné{{ $total > 1 ? 's' : '' }} au total</p>
        </div>

        <div class="flex items-center gap-3">
            <form method="GET" class="flex gap-2">
                <input type="text" name="q" value="{{ request('q') }}"
                    placeholder="Rechercher par email…"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-56 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg">
                    <i class="fas fa-search"></i>
                </button>
            </form>

            <a href="{{ route('admin.newsletter.export') }}"
                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg whitespace-nowrap">
                <i class="fas fa-file-csv mr-1"></i> Exporter CSV
            </a>
        </div>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow-sm border border-gray-200">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Email</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Date d'inscription</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($subscribers as $subscriber)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-800">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-envelope text-gray-300"></i>
                                {{ $subscriber->email }}
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                            {{ $subscriber->created_at->format('d/m/Y à H:i') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <form action="{{ route('admin.newsletter.destroy', $subscriber->id) }}" method="POST"
                                onsubmit="return confirm('Supprimer cet abonné ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="px-2 py-1 bg-red-100 text-red-700 hover:bg-red-200 rounded text-xs font-medium">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-gray-400">
                            <i class="fas fa-envelope-open text-3xl mb-2 block"></i>
                            Aucun abonné trouvé.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $subscribers->withQueryString()->links() }}
    </div>
</div>
@endsection
