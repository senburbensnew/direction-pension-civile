@extends('layouts.admin')

@section('title', 'Messages de contact')

@section('breadcrumb')
    <span class="text-gray-700 text-sm">Messages de contact</span>
@endsection

@section('content')
@php
    $statusBadge = fn (string $status) => match ($status) {
        \App\Models\Contact::STATUS_NOUVEAU => 'bg-orange-100 text-orange-800',
        \App\Models\Contact::STATUS_EN_COURS => 'bg-blue-100 text-blue-800',
        \App\Models\Contact::STATUS_TRAITE => 'bg-gray-100 text-gray-600',
        default => 'bg-gray-100 text-gray-600',
    };
@endphp
<div class="space-y-4">

    {{-- ── Header ────────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Messages reçus</h1>
            @if($nouveauCount > 0)
                <p class="text-sm text-orange-600 mt-0.5">
                    <span class="inline-block w-2 h-2 rounded-full bg-orange-500 mr-1"></span>
                    {{ $nouveauCount }} nouveau{{ $nouveauCount > 1 ? 'x' : '' }}
                </p>
            @else
                <p class="text-sm text-gray-400 mt-0.5">Aucun message en attente de prise en charge</p>
            @endif
        </div>

        <div class="flex items-center gap-2 flex-wrap justify-end">
            @if($nouveauCount > 0 || \App\Models\Contact::where('status', \App\Models\Contact::STATUS_EN_COURS)->exists())
                <form method="POST" action="{{ route('admin.contacts.markAllTraite') }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-3 py-2 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors">
                        <i class="fas fa-check-double"></i> Tout marquer comme traité
                    </button>
                </form>
            @endif

            <form method="GET" class="flex gap-2">
                <input type="text" name="q" value="{{ request('q') }}"
                       placeholder="Rechercher…"
                       class="border border-gray-200 rounded-lg px-3 py-2 text-sm w-44 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="status" onchange="this.form.submit()"
                        class="border border-gray-200 rounded-lg px-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Tous les statuts</option>
                    <option value="{{ \App\Models\Contact::STATUS_NOUVEAU }}" {{ request('status') === \App\Models\Contact::STATUS_NOUVEAU ? 'selected' : '' }}>Nouveau</option>
                    <option value="{{ \App\Models\Contact::STATUS_EN_COURS }}" {{ request('status') === \App\Models\Contact::STATUS_EN_COURS ? 'selected' : '' }}>En cours</option>
                    <option value="{{ \App\Models\Contact::STATUS_TRAITE }}" {{ request('status') === \App\Models\Contact::STATUS_TRAITE ? 'selected' : '' }}>Traité</option>
                </select>
            </form>

            <a href="{{ route('admin.contact-parameters.index') }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 text-xs border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 rounded-lg font-medium">
                <i class="fas fa-address-card"></i> Infos de contact
            </a>
            <a href="{{ route('contact') }}" target="_blank"
               class="inline-flex items-center gap-1.5 px-3 py-2 text-xs border border-blue-200 text-blue-600 hover:bg-blue-50 rounded-lg font-medium">
                <i class="fas fa-external-link-alt"></i> Voir le site
            </a>
        </div>
    </div>

    {{-- ── Table ──────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Statut</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Expéditeur</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Sujet</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Message</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap">Date</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($contacts as $contact)
                    @php
                        $subjLabel = $subjectLabels[$contact->subject] ?? $contact->subjectLabel();
                        $isNouveau = $contact->status === \App\Models\Contact::STATUS_NOUVEAU;
                    @endphp
                    <tr class="{{ $isNouveau ? 'bg-orange-50/40' : '' }} hover:bg-gray-50 transition-colors">

                        <td class="px-4 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $statusBadge($contact->status) }}">
                                {{ $contact->statusLabel() }}
                            </span>
                        </td>

                        {{-- Sender --}}
                        <td class="px-4 py-3">
                            <p class="font-{{ $isNouveau ? 'semibold' : 'medium' }} text-gray-800 whitespace-nowrap">
                                {{ $contact->first_name }} {{ $contact->last_name }}
                            </p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $contact->email }}</p>
                            @if($contact->telephone)
                                <p class="text-xs text-gray-400">{{ $contact->telephone }}</p>
                            @endif
                        </td>

                        {{-- Subject badge --}}
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ isset($subjectLabels[$contact->subject]) ? 'bg-blue-100 text-blue-700' : 'bg-teal-100 text-teal-700' }}">
                                {{ $subjLabel }}
                            </span>
                        </td>

                        {{-- Preview --}}
                        <td class="px-4 py-3 text-gray-500 max-w-xs hidden md:table-cell">
                            <span class="{{ $isNouveau ? 'font-medium text-gray-700' : '' }}">
                                {{ Str::limit($contact->message, 65) }}
                            </span>
                        </td>

                        {{-- Date --}}
                        <td class="px-4 py-3 text-gray-400 whitespace-nowrap text-xs">
                            <span title="{{ $contact->created_at->format('d/m/Y H:i') }}">
                                {{ $contact->created_at->diffForHumans() }}
                            </span>
                        </td>

                        {{-- Actions --}}
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                <a href="{{ route('admin.contacts.show', $contact->id) }}"
                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg text-xs font-medium transition-colors">
                                    <i class="fas fa-eye text-[10px]"></i> Ouvrir
                                </a>

                                @if($contact->status !== \App\Models\Contact::STATUS_EN_COURS)
                                    <form action="{{ route('admin.contacts.markEnCours', $contact->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" title="Marquer en cours"
                                                class="p-1.5 text-gray-400 hover:text-blue-600 transition-colors rounded">
                                            <i class="fas fa-spinner text-xs"></i>
                                        </button>
                                    </form>
                                @endif

                                @if($contact->status !== \App\Models\Contact::STATUS_TRAITE)
                                    <form action="{{ route('admin.contacts.markTraite', $contact->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" title="Marquer comme traité"
                                                class="p-1.5 text-gray-400 hover:text-green-600 transition-colors rounded">
                                            <i class="fas fa-check text-xs"></i>
                                        </button>
                                    </form>
                                @endif

                                <form action="{{ route('admin.contacts.destroy', $contact->id) }}" method="POST"
                                      onsubmit="return confirm('Supprimer ce message ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" title="Supprimer"
                                            class="p-1.5 text-gray-300 hover:text-red-500 transition-colors rounded">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center">
                            <i class="fas fa-inbox text-4xl text-gray-200 mb-3 block"></i>
                            <p class="text-gray-400 font-medium">Aucun message trouvé</p>
                            @if(request('q') || request('status'))
                                <a href="{{ route('admin.contacts.index') }}" class="text-xs text-blue-500 hover:underline mt-1 block">
                                    Effacer les filtres
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($contacts->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $contacts->withQueryString()->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
