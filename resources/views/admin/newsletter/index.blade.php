@extends('layouts.admin')

@section('title', 'Newsletter')

@section('content')
<div class="space-y-6">

    @if(session('import_errors'))
        <div class="bg-amber-50 border border-amber-200 text-amber-900 rounded-lg px-4 py-3 text-xs max-h-40 overflow-y-auto">
            @foreach(session('import_errors') as $err)
                <p>{{ $err }}</p>
            @endforeach
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Newsletter</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                <span class="font-semibold">{{ $stats['confirmed'] ?? $total }}</span> abonnés confirmés
                · {{ $stats['pending'] ?? 0 }} en attente
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.newsletter.import') }}" class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">
                <i class="fas fa-file-upload"></i> Importer
            </a>
            <a href="{{ route('admin.newsletter.export') }}" class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">
                <i class="fas fa-file-csv text-green-600"></i> Exporter
            </a>
            <a href="{{ route('admin.newsletter.campaigns.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">
                <i class="fas fa-paper-plane"></i> Nouvelle campagne
            </a>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        @foreach([
            ['label' => 'Total', 'value' => $stats['total'] ?? $total],
            ['label' => 'Confirmés', 'value' => $stats['confirmed'] ?? 0],
            ['label' => 'En attente', 'value' => $stats['pending'] ?? 0],
            ['label' => 'Désabonnés', 'value' => $stats['unsubscribed'] ?? 0],
            ['label' => 'Liste suppression', 'value' => $stats['suppressions'] ?? 0],
        ] as $card)
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs text-gray-500 uppercase">{{ $card['label'] }}</p>
                <p class="text-2xl font-bold text-gray-800">{{ $card['value'] }}</p>
            </div>
        @endforeach
    </div>

    @if($campaigns->isNotEmpty())
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 flex items-center gap-2">
            <i class="fas fa-history text-gray-400 text-sm"></i>
            <h2 class="text-sm font-semibold text-gray-700">Campagnes</h2>
        </div>
        <div class="divide-y divide-gray-100">
            @foreach($campaigns as $campaign)
                <div class="px-5 py-3 flex items-center justify-between gap-4 hover:bg-gray-50">
                    <div class="flex-1 min-w-0">
                        <a href="{{ route('admin.newsletter.campaigns.show', $campaign) }}" class="text-sm font-medium text-gray-800 hover:text-blue-600 truncate block">
                            {{ $campaign->subject ?: '(sans sujet)' }}
                        </a>
                        <p class="text-xs text-gray-400 mt-0.5">
                            <span class="inline-flex px-1.5 py-0.5 rounded bg-gray-100 text-gray-600">{{ $campaign->statusEnum()->label() }}</span>
                            · {{ $campaign->recipients_count }} destinataire(s)
                            @if($campaign->sent_at)
                                · {{ $campaign->sent_at->format('d/m/Y H:i') }}
                            @elseif($campaign->scheduled_at)
                                · planifiée {{ $campaign->scheduled_at->format('d/m/Y H:i') }}
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-1">
                        @if($campaign->isEditable())
                            <a href="{{ route('admin.newsletter.campaigns.edit', $campaign) }}" class="p-2 text-gray-400 hover:text-blue-600" title="Modifier"><i class="fas fa-edit text-xs"></i></a>
                        @endif
                        <form action="{{ route('admin.newsletter.campaigns.destroy', $campaign) }}" method="POST" onsubmit="return confirm('Supprimer cette campagne ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 text-gray-300 hover:text-red-500"><i class="fas fa-trash text-xs"></i></button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h2 class="text-sm font-semibold text-gray-700">Abonnés</h2>
            <form method="GET" class="flex flex-wrap gap-2">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Email…" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm w-44">
                <select name="status" class="border border-gray-300 rounded-lg px-2 py-1.5 text-sm">
                    <option value="">Tous statuts</option>
                    @foreach(\App\Enums\NewsletterSubscriberStatusEnum::cases() as $st)
                        <option value="{{ $st->value }}" @selected(request('status') === $st->value)>{{ $st->label() }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-3 py-1.5 bg-gray-100 rounded-lg text-sm"><i class="fas fa-search"></i></button>
            </form>
        </div>

        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Email</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Statut</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Inscrit</th>
                    <th class="px-5 py-3 text-center w-20">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subscribers as $subscriber)
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="px-5 py-3">{{ $subscriber->email }}</td>
                        <td class="px-5 py-3">
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $subscriber->status?->badgeClass() }}">
                                {{ $subscriber->statusLabel() }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400">{{ $subscriber->created_at->format('d/m/Y') }}</td>
                        <td class="px-5 py-3 text-center">
                            <form action="{{ route('admin.newsletter.destroy', $subscriber) }}" method="POST" onsubmit="return confirm('Supprimer ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-gray-300 hover:text-red-500"><i class="fas fa-trash text-xs"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-10 text-center text-gray-400">Aucun abonné.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($subscribers->hasPages())
            <div class="px-5 py-3 border-t">{{ $subscribers->links() }}</div>
        @endif
    </div>
</div>
@endsection
