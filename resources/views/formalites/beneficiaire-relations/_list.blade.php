@php
    use App\Enums\BeneficiaireLinkPermissionEnum;
    use App\Enums\BeneficiaireRelationDemandeStatusEnum;

    $approveRoute = $approveRoute ?? 'formalites.beneficiaire-relations.approve';
    $refuseRoute = $refuseRoute ?? 'formalites.beneficiaire-relations.refuse';
    $pieceRoute = $pieceRoute ?? 'formalites.beneficiaire-relations.piece';
    $listTitle = $listTitle ?? 'Demandes de relation';
    $permissionChoices = BeneficiaireLinkPermissionEnum::cases();

    $relationStatusBadge = fn (?BeneficiaireRelationDemandeStatusEnum $status) => match ($status) {
        BeneficiaireRelationDemandeStatusEnum::EN_ATTENTE => 'bg-amber-50 text-amber-800 ring-amber-200',
        BeneficiaireRelationDemandeStatusEnum::ACCEPTEE => 'bg-emerald-50 text-emerald-800 ring-emerald-200',
        BeneficiaireRelationDemandeStatusEnum::REFUSEE => 'bg-red-50 text-red-800 ring-red-200',
        BeneficiaireRelationDemandeStatusEnum::REVOQUEE => 'bg-slate-100 text-slate-700 ring-slate-200',
        default => 'bg-gray-50 text-gray-700 ring-gray-200',
    };
@endphp

<section class="dossier-card overflow-hidden">
    <div class="dossier-card-head">
        <h3>{{ $listTitle }}</h3>
    </div>

    @forelse($demandes as $demande)
        <article class="px-5 py-5 border-b border-gray-100 last:border-b-0">
            <div class="flex flex-wrap justify-between gap-3 mb-3">
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-[#173052]">{{ $demande->beneficiaire?->displayName() }}</p>
                    <p class="text-sm text-gray-600 mt-0.5">
                        Demandeur : {{ $demande->user?->displayName() }} · {{ $demande->type_relation?->label() }}
                    </p>
                    @if($demande->motif)
                        <p class="text-sm text-gray-500 mt-1">{{ $demande->motif }}</p>
                    @endif
                    @if($demande->isFromDemandeCreationCompte() && $demande->demandeCreationCompte)
                        <p class="text-sm text-gray-700 mt-2 dossier-notice dossier-notice--indigo px-3 py-2 bg-indigo-50/50">
                            <span class="font-medium">Via création de compte</span>
                            — dossier
                            <a href="{{ route('formalites.comptes-demandes.show', $demande->demandeCreationCompte) }}"
                               class="font-mono dossier-link">
                                {{ $demande->demandeCreationCompte->code }}
                            </a>.
                            Relation finalisée à l’acceptation du dossier compte (sauf approbation explicite ci-dessous).
                        </p>
                    @endif
                    @if($demande->getFirstMedia('justificatif'))
                        <a href="{{ route($pieceRoute, ['demande' => $demande, 'media' => $demande->getFirstMedia('justificatif')]) }}?inline=1"
                           class="dossier-link inline-block mt-2" target="_blank" rel="noopener">
                            Voir le justificatif
                        </a>
                    @endif
                </div>
                <span class="inline-flex px-2.5 py-1 text-xs font-medium ring-1 ring-inset shrink-0 h-fit {{ $relationStatusBadge($demande->status) }}">
                    {{ $demande->status?->label() }}
                </span>
            </div>

            @if($demande->status?->isPending())
                <div class="flex flex-col lg:flex-row gap-4 border-t border-gray-100 pt-4 mt-2">
                    <form method="POST" action="{{ route($approveRoute, $demande) }}" class="space-y-3 flex-1 min-w-0 lg:max-w-md">
                        @csrf
                        <fieldset class="space-y-2">
                            <legend class="text-xs font-semibold uppercase tracking-wide text-gray-600">Permissions accordées</legend>
                            @foreach($permissionChoices as $permission)
                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->value }}" checked
                                           class="rounded-none border-gray-300 text-[#173052] focus:ring-[#173052]/30">
                                    {{ $permission->label() }}
                                </label>
                            @endforeach
                        </fieldset>
                        <button type="submit" class="dossier-btn-success text-xs px-3 py-1.5">Approuver</button>
                    </form>
                    <form method="POST" action="{{ route($refuseRoute, $demande) }}" class="flex flex-wrap gap-2 items-end flex-1 min-w-0">
                        @csrf
                        <div class="flex-1 min-w-[12rem]">
                            <label class="block text-xs font-medium uppercase tracking-wide text-gray-500 mb-1">Motif de refus</label>
                            <input type="text" name="refusal_reason" required minlength="5" class="dossier-field">
                        </div>
                        <button type="submit" class="dossier-btn-danger-outline text-xs px-3 py-1.5">Refuser</button>
                    </form>
                </div>
            @endif
        </article>
    @empty
        <div class="px-5 py-16 text-center">
            <div class="max-w-sm">
                <div class="w-14 h-14 mb-3 bg-gray-100 flex items-center justify-center border border-gray-200">
                    <i class="fas fa-inbox text-xl text-gray-300" aria-hidden="true"></i>
                </div>
                <p class="font-semibold text-gray-700">Aucune demande</p>
                <p class="text-sm text-gray-500 mt-1">Aucune relation représentant en attente ou récente.</p>
            </div>
        </div>
    @endforelse
</section>

@if($demandes->hasPages())
    <div class="mt-6">
        {{ $demandes->withQueryString()->links() }}
    </div>
@endif
