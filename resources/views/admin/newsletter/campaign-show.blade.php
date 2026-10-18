@extends('layouts.admin')

@section('title', 'Campagne — ' . $campaign->subject)

@section('content')
<div class="space-y-6 max-w-5xl">
    <div class="flex items-center justify-between gap-3">
        <div>
            <a href="{{ route('admin.newsletter.admin.index') }}" class="text-sm text-blue-600 hover:underline">&larr; Newsletter</a>
            <h1 class="text-xl font-bold text-gray-800 mt-1">{{ $campaign->subject }}</h1>
            <p class="text-sm text-gray-500">
                {{ $campaign->statusEnum()->label() }}
                @if($campaign->sent_at)
                    · envoyée {{ $campaign->sent_at->format('d/m/Y H:i') }}
                @elseif($campaign->scheduled_at)
                    · planifiée {{ $campaign->scheduled_at->format('d/m/Y H:i') }}
                @endif
            </p>
        </div>
        <div class="flex gap-2">
            @if($campaign->isEditable())
                <a href="{{ route('admin.newsletter.campaigns.edit', $campaign) }}" class="px-3 py-2 text-sm border rounded-lg">Modifier</a>
            @endif
            @if($campaign->statusEnum()->value === 'scheduled')
                <form method="POST" action="{{ route('admin.newsletter.campaigns.cancel', $campaign) }}">
                    @csrf
                    <button type="submit" class="px-3 py-2 text-sm bg-red-50 text-red-700 border border-red-200 rounded-lg">Annuler l’envoi</button>
                </form>
            @endif
            <form method="POST" action="{{ route('admin.newsletter.campaigns.duplicate', $campaign) }}">
                @csrf
                <button type="submit" class="px-3 py-2 text-sm border rounded-lg">Dupliquer</button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        @foreach([
            ['label' => 'File', 'value' => $campaign->count_queued],
            ['label' => 'Envoyés', 'value' => $campaign->count_sent],
            ['label' => 'Échecs', 'value' => $campaign->count_failed],
            ['label' => 'Ouvertures', 'value' => $campaign->count_opened],
        ] as $stat)
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-500 uppercase">{{ $stat['label'] }}</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stat['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-5 py-3 border-b text-sm font-semibold text-gray-700">Derniers envois</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-2 text-left">Email</th>
                    <th class="px-4 py-2 text-left">Statut</th>
                    <th class="px-4 py-2 text-left">Envoyé</th>
                </tr>
            </thead>
            <tbody>
                @forelse($campaign->deliveries as $delivery)
                    <tr class="border-t border-gray-100">
                        <td class="px-4 py-2">{{ $delivery->email }}</td>
                        <td class="px-4 py-2">{{ $delivery->status?->label() }}</td>
                        <td class="px-4 py-2 text-gray-500">{{ optional($delivery->sent_at)->format('d/m/Y H:i') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-8 text-center text-gray-400">Aucun envoi enregistré.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
