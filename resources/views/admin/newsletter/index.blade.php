@extends('layouts.admin')

@section('title', 'Newsletter')

@section('breadcrumb')
    <span class="text-gray-700 text-sm">Newsletter</span>
@endsection

@section('content')
<div class="space-y-6">

    @if(session('success'))
        <div class="flex items-center gap-2 bg-green-50 border border-green-300 text-green-800 rounded-lg px-4 py-3 text-sm">
            <i class="fas fa-check-circle text-green-500"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-2 bg-red-50 border border-red-300 text-red-800 rounded-lg px-4 py-3 text-sm">
            <i class="fas fa-exclamation-circle text-red-500"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Newsletter</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                <span class="font-semibold text-gray-700">{{ $total }}</span>
                abonné{{ $total > 1 ? 's' : '' }} inscrit{{ $total > 1 ? 's' : '' }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.newsletter.export') }}"
                class="inline-flex items-center gap-1.5 px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-700 transition-colors">
                <i class="fas fa-file-csv text-green-600"></i> Exporter CSV
            </a>
            <a href="{{ route('admin.newsletter.compose') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                <i class="fas fa-paper-plane"></i> Rédiger une newsletter
            </a>
        </div>
    </div>

    {{-- Campagnes envoyées --}}
    @if($campaigns->isNotEmpty())
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 flex items-center gap-2">
            <i class="fas fa-history text-gray-400 text-sm"></i>
            <h2 class="text-sm font-semibold text-gray-700">Campagnes envoyées</h2>
        </div>
        <div class="divide-y divide-gray-100">
            @foreach($campaigns as $campaign)
                <div class="px-5 py-3 flex items-center justify-between gap-4 hover:bg-gray-50 transition-colors">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $campaign->subject }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            Envoyée à
                            <span class="font-medium text-gray-600">{{ $campaign->recipients_count }}</span>
                            abonné{{ $campaign->recipients_count > 1 ? 's' : '' }}
                            · {{ $campaign->sent_at->format('d/m/Y à H:i') }}
                            @if($campaign->sender)
                                · par <span class="text-gray-600">{{ $campaign->sender->name }}</span>
                            @endif
                        </p>
                    </div>
                    <form action="{{ route('admin.newsletter.campaigns.destroy', $campaign->id) }}" method="POST"
                          onsubmit="return confirm('Supprimer cette entrée de l\'historique ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="p-1.5 text-gray-300 hover:text-red-500 transition-colors rounded"
                            title="Supprimer">
                            <i class="fas fa-trash text-xs"></i>
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Liste des abonnés --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <i class="fas fa-users text-gray-400 text-sm"></i>
                <h2 class="text-sm font-semibold text-gray-700">Abonnés</h2>
            </div>
            <form method="GET" class="flex gap-2">
                <input type="text" name="q" value="{{ request('q') }}"
                    placeholder="Rechercher par email…"
                    class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm w-52 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <button type="submit"
                    class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg transition-colors">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Email</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Inscrit le</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center w-20">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subscribers as $subscriber)
                    <tr class="border-b border-gray-100 last:border-0 hover:bg-gray-50">
                        <td class="px-5 py-3 text-gray-800">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-user text-blue-400 text-xs"></i>
                                </div>
                                {{ $subscriber->email }}
                            </div>
                        </td>
                        <td class="px-5 py-3 text-gray-400 text-xs whitespace-nowrap">
                            {{ $subscriber->created_at->format('d/m/Y') }}
                            <span class="text-gray-300 mx-1">·</span>
                            {{ $subscriber->created_at->diffForHumans() }}
                        </td>
                        <td class="px-5 py-3 text-center">
                            <form action="{{ route('admin.newsletter.destroy', $subscriber->id) }}" method="POST"
                                  onsubmit="return confirm('Supprimer cet abonné ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="p-1.5 text-gray-300 hover:text-red-500 transition-colors rounded"
                                    title="Supprimer">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-5 py-10 text-center text-gray-400">
                            <i class="fas fa-inbox text-3xl mb-2 block text-gray-200"></i>
                            Aucun abonné trouvé.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($subscribers->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">
                {{ $subscribers->withQueryString()->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
