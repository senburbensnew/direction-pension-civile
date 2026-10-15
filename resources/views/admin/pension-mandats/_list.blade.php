@php
    use App\Enums\PensionMandatStatusEnum;

    $approveRoute = $approveRoute ?? 'formalites.pension-mandats.approve';
    $refuseRoute = $refuseRoute ?? 'formalites.pension-mandats.refuse';
    $revokeRoute = $revokeRoute ?? 'formalites.pension-mandats.revoke';
    $pieceRoute = $pieceRoute ?? 'formalites.pension-mandats.piece';
    $listTitle = $listTitle ?? 'Mandats pension';

    $mandatStatusBadge = fn (?PensionMandatStatusEnum $status) => match ($status) {
        PensionMandatStatusEnum::EN_ATTENTE => 'bg-amber-50 text-amber-800 ring-amber-200',
        PensionMandatStatusEnum::ACTIF => 'bg-emerald-50 text-emerald-800 ring-emerald-200',
        PensionMandatStatusEnum::REFUSE => 'bg-red-50 text-red-800 ring-red-200',
        PensionMandatStatusEnum::REVOQUE => 'bg-slate-100 text-slate-700 ring-slate-200',
        PensionMandatStatusEnum::EXPIRE => 'bg-gray-100 text-gray-600 ring-gray-200',
        default => 'bg-gray-50 text-gray-700 ring-gray-200',
    };
@endphp

<section class="dossier-card overflow-hidden">
    <div class="dossier-card-head">
        <h3>{{ $listTitle }}</h3>
    </div>

    @forelse($mandats as $mandat)
        <article class="px-5 py-5 border-b border-gray-100 last:border-b-0">
            <div class="flex flex-wrap justify-between gap-3 mb-3">
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-[#173052]">{{ $mandat->beneficiaire?->displayName() }}</p>
                    <p class="text-sm text-gray-600 mt-0.5">Mandataire : {{ $mandat->mandataire?->displayName() }} · {{ $mandat->type }}</p>
                    @if($mandat->isFromDemandeCreationCompte() && $mandat->demandeCreationCompte)
                        <p class="text-sm text-gray-700 mt-2 dossier-notice dossier-notice--indigo px-3 py-2 bg-indigo-50/50">
                            <span class="font-medium">Via création de compte</span>
                            — dossier
                            <a href="{{ route('formalites.comptes-demandes.show', $mandat->demandeCreationCompte) }}"
                               class="font-mono dossier-link">
                                {{ $mandat->demandeCreationCompte->code }}
                            </a>
                        </p>
                    @endif
                    @if($mandat->getFirstMedia('mandat_document'))
                        <a href="{{ route($pieceRoute, ['mandat' => $mandat, 'media' => $mandat->getFirstMedia('mandat_document')]) }}?inline=1"
                           class="dossier-link inline-block mt-2" target="_blank" rel="noopener">
                            Voir le document de mandat
                        </a>
                    @endif
                </div>
                <span class="inline-flex px-2.5 py-1 text-xs font-medium ring-1 ring-inset shrink-0 h-fit {{ $mandatStatusBadge($mandat->status) }}">
                    {{ $mandat->status?->label() }}
                </span>
            </div>

            @if($mandat->status === PensionMandatStatusEnum::EN_ATTENTE)
                <form method="POST" action="{{ route($approveRoute, $mandat) }}" class="space-y-4 border-t border-gray-100 pt-4">
                    @csrf
                    <div>
                        <p class="text-sm font-semibold text-[#173052] mb-2">Types de demandes autorisés</p>
                        <p class="text-xs text-gray-500 mb-2">Laisser vide = tous les types ci-dessous.</p>
                        <div class="grid sm:grid-cols-2 gap-2">
                            @foreach($demandeTypeChoices as $type)
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="allowed_demande_types[]" value="{{ $type->value }}" class="rounded-none border-gray-300">
                                    {{ $type->label() }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-[#173052] mb-2">Codes pension autorisés</p>
                        <p class="text-xs text-gray-500 mb-2">Laisser vide = tous les codes du bénéficiaire.</p>
                        <div class="flex flex-wrap gap-3">
                            @forelse($mandat->beneficiaire?->affiliations ?? [] as $affiliation)
                                <label class="inline-flex items-center gap-2 text-sm font-mono text-gray-700">
                                    <input type="checkbox" name="allowed_pension_codes[]" value="{{ $affiliation->code_pension }}" class="rounded-none border-gray-300">
                                    {{ $affiliation->code_pension }}
                                </label>
                            @empty
                                <span class="text-sm text-gray-500">Aucune affiliation enregistrée.</span>
                            @endforelse
                        </div>
                    </div>
                    <button type="submit" class="dossier-btn-success text-xs px-3 py-1.5">Approuver le mandat</button>
                </form>
                <form method="POST" action="{{ route($refuseRoute, $mandat) }}" class="mt-4 flex flex-wrap gap-2 items-end border-t border-gray-100 pt-4">
                    @csrf
                    <div class="flex-1 min-w-[12rem]">
                        <label class="block text-xs font-medium uppercase tracking-wide text-gray-500 mb-1">Motif de refus</label>
                        <input type="text" name="refusal_reason" required minlength="5" class="dossier-field">
                    </div>
                    <button type="submit" class="dossier-btn-danger-outline text-xs px-3 py-1.5">Refuser</button>
                </form>
            @elseif($mandat->status === PensionMandatStatusEnum::ACTIF)
                <div class="text-sm text-gray-600 border-t border-gray-100 pt-3">
                    @if($mandat->allowed_demande_types)
                        <p>Types : {{ implode(', ', $mandat->allowed_demande_types) }}</p>
                    @endif
                    @if($mandat->allowed_pension_codes)
                        <p>Codes : {{ implode(', ', $mandat->allowed_pension_codes) }}</p>
                    @endif
                </div>
                <form method="POST" action="{{ route($revokeRoute, $mandat) }}" class="mt-3">
                    @csrf
                    <button type="submit" class="dossier-btn-danger-outline text-xs px-3 py-1.5">Révoquer</button>
                </form>
            @endif
        </article>
    @empty
        <div class="px-5 py-16 text-center">
            <div class="max-w-sm mx-auto">
                <div class="w-14 h-14 mx-auto mb-3 bg-gray-100 flex items-center justify-center border border-gray-200">
                    <i class="fas fa-inbox text-xl text-gray-300" aria-hidden="true"></i>
                </div>
                <p class="font-semibold text-gray-700">Aucun mandat</p>
                <p class="text-sm text-gray-500 mt-1">Aucun mandat pension à afficher.</p>
            </div>
        </div>
    @endforelse
</section>

@if($mandats->hasPages())
    <div class="mt-6">
        {{ $mandats->withQueryString()->links() }}
    </div>
@endif
