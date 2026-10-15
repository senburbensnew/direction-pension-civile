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

        <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between border-b border-gray-100 pb-4 mb-6">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="text-2xl text-navy shrink-0" aria-hidden="true"><i class="fa-solid fa-link"></i></span>
                    <h2 class="text-xl font-bold text-navy">Accès actifs</h2>
                </div>
                <div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-2 sm:gap-3 w-full sm:w-auto sm:justify-end">
                    <a href="{{ route('compte.relations.create') }}" class="inline-flex w-full sm:w-auto items-center justify-center px-5 py-2.5 bg-navy hover:bg-orange-500 text-white text-sm font-semibold transition-colors">
                        Demander une relation
                    </a>
                    <a href="{{ route('compte.mandats.index') }}" class="inline-flex w-full sm:w-auto items-center justify-center px-5 py-2.5 border border-navy text-navy hover:bg-navy hover:text-white text-sm font-semibold transition-colors">
                        Mes mandats
                    </a>
                </div>
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
                                {{ $mandat->typeLabel() }}
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

        @php
            $dccEnCours = $demandesCompteRepresentant->filter(
                fn ($d) => $d->status === \App\Models\DemandeCreationCompte::STATUS_EN_ATTENTE
            );
        @endphp
        @if($dccEnCours->isNotEmpty())
            <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-6 border-b border-gray-100 pb-4">
                    <span class="text-2xl text-navy" aria-hidden="true"><i class="fa-solid fa-user-plus"></i></span>
                    <h2 class="text-xl font-bold text-navy">Demandes de compte (représentant)</h2>
                </div>
                <ul class="divide-y divide-gray-100">
                    @foreach($dccEnCours as $dcc)
                        <li class="py-4 flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-semibold text-gray-900">Dossier {{ $dcc->code }}</p>
                                <p class="text-sm text-gray-600 mt-1">
                                    Pensionné mineur
                                    @if($dcc->pension_code)
                                        · <span class="font-mono">{{ $dcc->pension_code }}</span>
                                    @endif
                                    @if($dcc->representant_lien)
                                        · {{ \App\Enums\BeneficiaireRelationEnum::tryFrom($dcc->representant_lien)?->label() ?? $dcc->representant_lien }}
                                    @endif
                                </p>
                                <p class="text-xs text-gray-500 mt-2 leading-relaxed">
                                    La relation parentale est enregistrée avec ce dossier. Prenez rendez-vous aux formalités si ce n’est pas déjà fait ; l’accès au dossier du mineur sera activé lors de l’acceptation du compte.
                                </p>
                            </div>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full shrink-0 bg-amber-100 text-amber-900">
                                {{ $dcc->statusLabel() }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if($relationDemandes->isNotEmpty())
            <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-6 border-b border-gray-100 pb-4">
                    <span class="text-2xl text-navy" aria-hidden="true"><i class="fa-solid fa-clock"></i></span>
                    <h2 class="text-xl font-bold text-navy">Mes demandes de relation</h2>
                </div>
                <ul class="divide-y divide-gray-100">
                    @foreach($relationDemandes as $demande)
                        @php
                            $demandeBadge = match ($demande->status?->value ?? '') {
                                'en_attente' => 'bg-amber-100 text-amber-900',
                                'acceptee' => 'bg-green-100 text-green-900',
                                'refusee' => 'bg-red-100 text-red-900',
                                default => 'bg-gray-100 text-gray-700',
                            };
                        @endphp
                        <li class="py-4 flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-semibold text-gray-900">{{ $demande->beneficiaire?->displayName() }}</p>
                                <p class="text-sm text-gray-600 mt-1">
                                    {{ $demande->type_relation?->label() }}
                                    @if($demande->beneficiaire?->primaryAffiliation()?->code_pension)
                                        · <span class="font-mono">{{ $demande->beneficiaire->primaryAffiliation()->code_pension }}</span>
                                    @endif
                                </p>
                                @if($demande->isFromDemandeCreationCompte() && $demande->demandeCreationCompte)
                                    <p class="text-xs text-sky-800 mt-2">
                                        Via demande de compte <span class="font-mono">{{ $demande->demandeCreationCompte->code }}</span>
                                        — traitée avec le dossier formalités (RDV puis décision).
                                    </p>
                                @endif
                                @if($demande->status?->value === 'refusee' && filled($demande->refusal_reason))
                                    <p class="text-sm text-red-700 mt-2">Motif : {{ $demande->refusal_reason }}</p>
                                @endif
                                @if($demande->status?->value === 'acceptee' && $demande->beneficiaire)
                                    <a href="{{ route('compte.beneficiaires.context', $demande->beneficiaire) }}"
                                       class="inline-block mt-2 text-sm font-semibold text-navy hover:underline">
                                        Utiliser ce contexte →
                                    </a>
                                @endif
                            </div>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full shrink-0 {{ $demandeBadge }}">
                                {{ $demande->status?->label() }}
                            </span>
                        </li>
                    @endforeach
                </ul>
                @if($pendingRelations->isNotEmpty())
                    <p class="text-xs text-gray-500 mt-4">
                        Les demandes « en attente » sont examinées par le service des formalités. Vous recevrez une notification à la décision.
                    </p>
                @endif
            </section>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-4 text-sm text-gray-600 pt-2">
            <a href="{{ route('compte.affiliations.index') }}" class="text-navy font-medium hover:underline">← Mes codes pension</a>
            <a href="{{ route('personal.dashboard') }}" class="text-indigo-700 font-semibold hover:underline">Mes demandes</a>
        </div>
</x-portal-page>
</x-app-layout>
