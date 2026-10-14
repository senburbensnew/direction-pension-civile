<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mes bénéficiaires</h2>
    </x-slot>

@php
    $statusBadge = fn ($status) => match ($status?->value ?? '') {
        'actif' => 'bg-green-100 text-green-900',
        'en_attente' => 'bg-amber-100 text-amber-900',
        'refuse' => 'bg-red-100 text-red-900',
        'revoque', 'expire' => 'bg-gray-100 text-gray-700',
        default => 'bg-gray-100 text-gray-700',
    };
@endphp

<style>
    .card-shadow { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
</style>

<x-portal-page bottom="pb-10" class="max-w-4xl space-y-8">
        <x-compte-page-intro
            title="Mes bénéficiaires"
            description="Consultez les personnes pour lesquelles vous avez un accès (propre dossier, représentant légal ou mandataire) et basculez le contexte de vos demandes."
        />

        @if(session('success'))
            <div class="rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif

        <div class="flex flex-wrap justify-center gap-3">
            <a href="{{ route('compte.relations.create') }}" class="px-5 py-2.5 bg-navy hover:bg-orange-500 text-white text-sm font-semibold transition-colors">
                Demander une relation
            </a>
            <a href="{{ route('compte.mandats.index') }}" class="px-5 py-2.5 border border-navy text-navy hover:bg-navy hover:text-white text-sm font-semibold transition-colors">
                Mes mandats
            </a>
        </div>

        <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-6 border-b border-gray-100 pb-4">
                <span class="text-2xl text-navy" aria-hidden="true"><i class="fa-solid fa-link"></i></span>
                <h2 class="text-xl font-bold text-navy">Accès actifs</h2>
            </div>
            <ul class="divide-y divide-gray-100">
                @forelse($links as $link)
                    <li class="py-4 flex flex-wrap justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-900">{{ $link->beneficiaire?->displayName() }}</p>
                            <p class="text-sm text-gray-600 mt-1">
                                {{ $link->relation?->label() }} · {{ $link->resolvedStatus()->label() }}
                            </p>
                            @if($link->relation?->isRepresentative())
                                <p class="text-xs text-gray-500 mt-2">
                                    Actions autorisées : {{ implode(' · ', $link->grantedPermissionLabels()) }}
                                </p>
                            @endif
                            @if($link->beneficiaire?->primaryAffiliation()?->code_pension)
                                <p class="text-sm font-mono text-gray-700 mt-1">{{ $link->beneficiaire->primaryAffiliation()->code_pension }}</p>
                            @endif
                        </div>
                        <a href="{{ route('compte.beneficiaires.context', $link->beneficiaire) }}"
                           class="text-navy text-sm font-semibold hover:underline shrink-0 self-start">
                            Utiliser ce contexte
                        </a>
                    </li>
                @empty
                    <li class="py-6 text-center text-gray-500 text-sm">Aucun lien actif (hors mandats).</li>
                @endforelse
            </ul>
        </section>

        <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-6 border-b border-gray-100 pb-4">
                <span class="text-2xl text-navy" aria-hidden="true"><i class="fa-solid fa-file-signature"></i></span>
                <h2 class="text-xl font-bold text-navy">Mandats</h2>
            </div>
            <ul class="divide-y divide-gray-100">
                @forelse($mandats as $mandat)
                    <li class="py-4 flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-900">{{ $mandat->beneficiaire?->displayName() }}</p>
                            <p class="text-sm text-gray-600 mt-1">
                                {{ str_replace('_', ' ', $mandat->type) }}
                                @if($mandat->beneficiaire?->primaryAffiliation()?->code_pension)
                                    · <span class="font-mono">{{ $mandat->beneficiaire->primaryAffiliation()->code_pension }}</span>
                                @endif
                            </p>
                            @if($mandat->date_debut || $mandat->date_fin)
                                <p class="text-xs text-gray-500 mt-1">
                                    @if($mandat->date_debut) Du {{ $mandat->date_debut->format('d/m/Y') }} @endif
                                    @if($mandat->date_fin) au {{ $mandat->date_fin->format('d/m/Y') }} @endif
                                </p>
                            @endif
                            @if($mandat->isCurrentlyActive() || $mandat->status?->value === 'actif')
                                <p class="text-xs text-gray-600 mt-2">
                                    <span class="font-medium text-gray-700">Périmètre — demandes :</span>
                                    {{ implode(' · ', $mandat->allowedDemandeTypeLabels()) }}
                                </p>
                                <p class="text-xs text-gray-600 mt-1">
                                    <span class="font-medium text-gray-700">Périmètre — codes :</span>
                                    {{ implode(' · ', $mandat->allowedPensionCodeLabels()) }}
                                </p>
                            @endif
                        </div>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full shrink-0 {{ $statusBadge($mandat->status) }}">
                            {{ $mandat->status?->label() ?? '—' }}
                        </span>
                    </li>
                @empty
                    <li class="py-6 text-center text-gray-500 text-sm">Aucun mandat enregistré.</li>
                @endforelse
            </ul>
            <p class="text-sm text-gray-500 mt-4">
                <a href="{{ route('compte.mandats.index') }}" class="text-navy font-medium hover:underline">Déposer une nouvelle demande de mandat →</a>
            </p>
        </section>

        @if($pendingRelations->isNotEmpty())
            <section class="bg-amber-50 border border-amber-200 card-shadow p-6 sm:p-8">
                <h3 class="text-lg font-bold text-amber-900 mb-3">Demandes de relation en attente</h3>
                <ul class="space-y-2">
                    @foreach($pendingRelations as $demande)
                        <li class="text-sm text-amber-900">
                            {{ $demande->beneficiaire?->displayName() }} — {{ $demande->type_relation?->label() }}
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-4 text-sm text-gray-600 pt-2">
            <a href="{{ route('compte.affiliations.index') }}" class="text-navy font-medium hover:underline">← Mes codes pension</a>
            <a href="{{ route('personal.dashboard') }}" class="text-indigo-700 font-semibold hover:underline">Mes demandes</a>
        </div>
</x-portal-page>
</x-app-layout>
