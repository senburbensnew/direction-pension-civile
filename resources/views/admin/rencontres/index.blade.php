@extends('layouts.admin')

@section('title', 'Demandes de rencontre')

@section('content')
<div class="space-y-6 max-w-5xl">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Demandes de rencontre</h1>
            <p class="text-sm text-gray-500 mt-0.5">Visioconférences et rendez-vous demandés par des tiers.</p>
        </div>
        <span class="text-xs bg-indigo-100 text-indigo-700 font-semibold px-3 py-1 rounded-full">
            {{ $demandes->total() }} demande(s)
        </span>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Référence</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Demandeur</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Objet</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Date souhaitée</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Plateforme</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Statut</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($demandes as $d)
                    @php
                        $data   = $d->data ?? [];
                        $code   = $d->status?->code;
                        $isPending = in_array($code, ['SOUMISE', 'EN_ATTENTE', 'EN_COURS']);
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors" x-data="{ open: false }">
                        <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $d->code }}</td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-800">{{ ($data['prenom'] ?? '') . ' ' . ($data['nom'] ?? '') }}</p>
                            <p class="text-xs text-gray-400">{{ $data['email'] ?? '—' }}</p>
                            @if(!empty($data['organisation']))
                                <p class="text-xs text-gray-400 italic">{{ $data['organisation'] }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-700 max-w-xs truncate">{{ $data['objet'] ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600 text-xs">
                            {{ isset($data['date_souhaitee']) ? \Carbon\Carbon::parse($data['date_souhaitee'])->format('d/m/Y') : '—' }}
                            @if(!empty($data['heure_souhaitee']))
                                <span class="text-gray-400">à {{ $data['heure_souhaitee'] }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium
                                {{ match($data['plateforme'] ?? '') {
                                    'zoom'  => 'bg-blue-100 text-blue-700',
                                    'teams' => 'bg-purple-100 text-purple-700',
                                    'meet'  => 'bg-green-100 text-green-700',
                                    default => 'bg-gray-100 text-gray-600',
                                } }}">
                                {{ ucfirst($data['plateforme'] ?? '—') }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if($d->status)
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ \App\Models\WorkflowStep::getStatusStyle($code) }}">
                                    {{ $d->currentStep?->nom }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button @click="open = !open"
                                        class="text-xs text-indigo-600 hover:underline font-medium">
                                    Détails
                                </button>
                                @if($isPending)
                                    <form method="POST" action="{{ route('admin.rencontres.accepter', $d) }}"
                                          onsubmit="return confirm('Confirmer l\'acceptation ?')">
                                        @csrf
                                        <button type="submit"
                                                class="text-xs px-2 py-1 bg-green-600 hover:bg-green-700 text-white rounded font-medium transition">
                                            Accepter
                                        </button>
                                    </form>
                                    <button @click="open = true"
                                            class="text-xs px-2 py-1 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 rounded font-medium transition">
                                        Refuser
                                    </button>
                                @endif
                            </div>

                            {{-- Panneau détails + refus --}}
                            <div x-show="open" x-cloak x-transition
                                 class="mt-3 text-left bg-gray-50 border border-gray-200 rounded-lg p-4 space-y-3">

                                @if(!empty($data['message']))
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500 mb-1">Message</p>
                                        <p class="text-sm text-gray-700">{{ $data['message'] }}</p>
                                    </div>
                                @endif

                                @if($d->annotation)
                                    <div class="bg-yellow-50 border border-yellow-200 rounded p-2">
                                        <p class="text-xs font-semibold text-yellow-700 mb-0.5">Annotation</p>
                                        <p class="text-sm text-yellow-800">{{ $d->annotation }}</p>
                                    </div>
                                @endif

                                @if($isPending)
                                    <form method="POST" action="{{ route('admin.rencontres.refuser', $d) }}" class="border-t border-gray-200 pt-3">
                                        @csrf
                                        <p class="text-xs font-semibold text-gray-500 mb-1">Motif du refus</p>
                                        <textarea name="motif" rows="2" required maxlength="500"
                                                  class="w-full text-sm border border-gray-300 rounded px-3 py-2 focus:ring-red-300 focus:outline-none"
                                                  placeholder="Expliquer pourquoi la demande est refusée…"></textarea>
                                        <div class="flex gap-2 mt-2">
                                            <button type="submit"
                                                    class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded transition">
                                                Confirmer le refus
                                            </button>
                                            <button type="button" @click="open = false"
                                                    class="px-3 py-1.5 text-gray-500 text-xs hover:underline">
                                                Annuler
                                            </button>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-gray-400">
                            Aucune demande de rencontre.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($demandes->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $demandes->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
