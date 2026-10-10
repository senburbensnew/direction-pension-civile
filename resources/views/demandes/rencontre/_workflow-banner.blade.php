@php
    /** @var \App\Models\Demande $demande */
    $rdvStatut = $rdvStatut ?? $demande->rencontreStatut();
    $data = $demande->data ?? [];
    $motifKey = (string) ($data['motif_key'] ?? '');
    $isFinalisationCompte = in_array(
        $motifKey,
        \App\Models\DemandeCreationCompte::rdvFinalisationMotifKeys(),
        true
    );
    $isFormaliteMotif = str_starts_with($motifKey, 'demande_formalites') || str_starts_with($motifKey, 'formalites');
    $owner = $demande->user ?? \App\Models\User::find($demande->created_by);
    $compteCreation = $compteCreation ?? null;
    if ($isFinalisationCompte && ! $compteCreation && $owner?->isProvisionnel()) {
        $compteCreation = \App\Models\DemandeCreationCompte::query()
            ->where('user_id', $owner->id)
            ->where('status', \App\Models\DemandeCreationCompte::STATUS_EN_ATTENTE)
            ->latest()
            ->first();
    }
    $rdvRealise = $rdvStatut === \App\Enums\RencontreStatutEnum::REALISE;
    $rdvTraitable = $demande->peutEtreTraiteApresRendezVous();
    $rdvTerminal = $rdvStatut->isTerminal();
    $postTreatmentLabel = $isFinalisationCompte
        ? 'Décision création de compte'
        : ($isFormaliteMotif ? 'Enregistrement formalité' : 'Suite du dossier');
    $postTreatmentDone = false;
    if ($isFinalisationCompte && $compteCreation && ! $compteCreation->isPending()) {
        $postTreatmentDone = true;
    }
    if ($isFormaliteMotif && $rdvRealise && $owner && ! $owner->isProvisionnel()) {
        $anneeActive = \App\Models\AnneeFiscale::active();
        if ($anneeActive && \App\Models\Formalite::forYear($owner->id, $anneeActive->id)) {
            $postTreatmentDone = true;
        }
    }
    $steps = [
        ['key' => 'soumis', 'label' => 'Demande soumise', 'done' => true],
        ['key' => 'planifie', 'label' => 'Planifié / accepté', 'done' => ! in_array($rdvStatut, [\App\Enums\RencontreStatutEnum::DEMANDE], true)],
        ['key' => 'rdv', 'label' => 'Rendez-vous', 'done' => $rdvRealise || in_array($rdvStatut, [\App\Enums\RencontreStatutEnum::NON_HONORE], true)],
        ['key' => 'post', 'label' => $postTreatmentLabel, 'done' => $postTreatmentDone],
    ];
    $compteShowRoute = null;
    if ($compteCreation && auth()->user()?->hasAnyRole(['admin', \App\Models\User::ROLE_AGENT_FORMALITES])) {
        $compteShowRoute = auth()->user()->hasRole('admin')
            ? route('admin.comptes-demandes.show', $compteCreation)
            : route('formalites.comptes-demandes.show', $compteCreation);
    }
    $dossierRoute = auth()->user()?->hasPensionPortalAccess() && (int) auth()->id() === (int) $demande->created_by
        ? route('personal.request.authenticated-user-request.show', $demande->id)
        : route('personal.request.show', $demande->id);
@endphp

<div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 space-y-3">
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
            <a href="{{ route('demandes.rencontre.formalite', $demande) }}"
               @class([
                   'font-semibold hover:underline',
                   'text-emerald-700' => $rdvTraitable,
                   'text-amber-700' => ! $rdvTraitable,
               ])>
                {{ $rdvTraitable ? 'Traiter la formalité' : 'Formalité (après l’heure du RDV)' }}
            </a>
        @endif
        <a href="{{ $dossierRoute }}" class="text-navy font-semibold hover:underline">
            Dossier rendez-vous
        </a>
    </div>
</div>
