@php
    /** @var \App\Models\Demande $demande */
    $rdvStatut = $rdvStatut ?? $demande->rencontreStatut();
    $data = $demande->data ?? [];
    $motifKey = (string) ($data['motif_key'] ?? '');
    $motifsService = app(\App\Services\RencontreMotifService::class);
    $isFormaliteMotif = str_starts_with($motifKey, 'demande_formalites') || str_starts_with($motifKey, 'formalites');
    $owner = $demande->user ?? \App\Models\User::find($demande->created_by);
    $compteCreation = $compteCreation ?? null;
    $decisionComptePossible = $owner?->isProvisionnel()
        && $motifsService->countsTowardAccountDecision($motifKey);
    if ($decisionComptePossible && ! $compteCreation) {
        $compteCreation = \App\Models\DemandeCreationCompte::query()
            ->where('user_id', $owner->id)
            ->where('status', \App\Models\DemandeCreationCompte::STATUS_EN_ATTENTE)
            ->latest()
            ->first();
    }
    $rdvRealise = $rdvStatut === \App\Enums\RencontreStatutEnum::REALISE;
    $rdvTraitable = $demande->peutProposerTraitementFormaliteApresRdv();
    $formaliteRdvFaite = $demande->formaliteRdvEnregistree();
    $rdvTerminal = $rdvStatut->isTerminal();
    $postTreatmentLabel = $decisionComptePossible
        ? 'Décision création de compte (si applicable)'
        : ($isFormaliteMotif ? 'Enregistrement formalité' : 'Suite du dossier');
    $postTreatmentDone = false;
    if ($decisionComptePossible && $compteCreation && ! $compteCreation->isPending()) {
        $postTreatmentDone = true;
    }
    if ($isFormaliteMotif && $rdvRealise && $owner && ! $owner->isProvisionnel() && $formaliteRdvFaite) {
        $postTreatmentDone = true;
    }
    $steps = [
        ['key' => 'soumis', 'label' => 'Demande soumise', 'done' => true],
        ['key' => 'planifie', 'label' => 'Planifié / accepté', 'done' => ! in_array($rdvStatut, [\App\Enums\RencontreStatutEnum::DEMANDE], true)],
        ['key' => 'rdv', 'label' => 'Rendez-vous', 'done' => $rdvRealise || in_array($rdvStatut, [\App\Enums\RencontreStatutEnum::NON_HONORE], true)],
        ['key' => 'post', 'label' => $postTreatmentLabel, 'done' => $postTreatmentDone],
    ];
    $compteShowRoute = null;
    if ($compteCreation && auth()->user()?->hasRole(\App\Models\User::ROLE_AGENT_FORMALITES)) {
        $compteShowRoute = route('formalites.comptes-demandes.show', $compteCreation);
    }
    $dossierRoute = auth()->user()?->hasPensionPortalAccess() && (int) auth()->id() === (int) $demande->created_by
        ? route('personal.request.authenticated-user-request.show', $demande->id)
        : route('personal.request.show', $demande->id);
@endphp

<div class="border border-slate-200 bg-slate-50 px-4 py-3 space-y-3">
    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Parcours du rendez-vous</p>
    <ol class="flex flex-wrap gap-2 text-xs">
        @foreach($steps as $index => $step)
            <li class="inline-flex items-center gap-1.5">
                <span @class([
                    'inline-flex h-6 w-6 items-center justify-center rounded-full font-semibold',
                    'bg-emerald-600 text-white' => $step['done'],
                    'bg-white border border-slate-300 text-slate-500' => ! $step['done'],
                ])>{{ $index + 1 }}</span>
                <span @class(['font-medium', 'text-emerald-800' => $step['done'], 'text-slate-600' => ! $step['done']])>
                    {{ $step['label'] }}
                </span>
                @if(! $loop->last)
                    <span class="text-slate-300 mx-1">→</span>
                @endif
            </li>
        @endforeach
    </ol>
    <div class="flex flex-wrap gap-3 text-sm">
        @if($compteShowRoute)
            <a href="{{ $compteShowRoute }}" class="text-indigo-700 font-semibold hover:underline">
                {{ $compteCreation->libellePublic() }}
            </a>
        @endif
        @if($isFormaliteMotif && $rdvRealise && ! $owner?->isProvisionnel())
            @if($formaliteRdvFaite)
                <span class="text-emerald-700 font-semibold">Formalité enregistrée</span>
            @else
                <a href="{{ route('demandes.rencontre.formalite', $demande) }}"
                   @class([
                       'font-semibold hover:underline',
                       'text-emerald-700' => $rdvTraitable,
                       'text-amber-700' => ! $rdvTraitable,
                   ])>
                    {{ $rdvTraitable ? 'Traiter la formalité' : 'Formalité (après l’heure du RDV)' }}
                </a>
            @endif
        @endif
        <a href="{{ $dossierRoute }}" class="text-navy font-semibold hover:underline">
            Dossier rendez-vous
        </a>
    </div>
</div>
