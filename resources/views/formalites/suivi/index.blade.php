@php
    $anneeLabel = $annees->firstWhere('id', $anneeFiscaleId)?->code ?? '—';
@endphp

<x-app-layout>
    <x-portal-page bottom="pb-6" class="space-y-6">
        <header class="dossier-header px-5 py-5 sm:px-6 border-l-4 border-l-indigo-600">
            <p class="dossier-kicker">Formalités</p>
            <h1 class="mt-1 text-xl font-semibold text-[#173052] tracking-tight">Suivi des formalités fiscales</h1>
            <p class="mt-2 text-sm text-gray-600">
                Vue d’ensemble pour le responsable Formalités : exercice en cours, formalités enregistrées après rendez-vous,
                dossiers en attente et rendez-vous à clôturer.
            </p>
        </header>

        <form method="GET" class="dossier-form-bar">
            <input type="hidden" name="filtre" value="{{ $filtre }}">
            <div class="flex-1 min-w-[10rem]">
                <label for="annee_fiscale_id" class="block text-xs font-medium uppercase tracking-wide text-gray-500 mb-1">Exercice fiscal</label>
                <select id="annee_fiscale_id" name="annee_fiscale_id" class="dossier-field">
                    @foreach($annees as $annee)
                        <option value="{{ $annee->id }}" @selected((int) $anneeFiscaleId === (int) $annee->id)>
                            {{ $annee->code }} — {{ $annee->displayName() }}
                        </option>
                    @endforeach
                </select>
            </div>
            @if($filtre === 'remplies')
                <div class="flex-1 min-w-[12rem]">
                    <label for="suivi-q" class="block text-xs font-medium uppercase tracking-wide text-gray-500 mb-1">Recherche</label>
                    <input id="suivi-q" type="search" name="q" value="{{ $search }}" placeholder="Pensionné, courriel, exercice…" class="dossier-field">
                </div>
            @endif
            <button type="submit" class="dossier-btn-primary">
                <i class="fas fa-filter text-xs" aria-hidden="true"></i>
                Appliquer
            </button>
        </form>

        @php
            $suiviStatCards = [
                ['key' => 'remplies', 'label' => 'remplies ('.$anneeLabel.')', 'value' => $stats['remplies'], 'border' => 'border-l-emerald-600 hover:border-emerald-700', 'ring' => 'ring-emerald-600/30'],
                ['key' => 'en_attente', 'label' => 'en attente d’enregistrement', 'value' => $stats['en_attente'], 'border' => 'border-l-amber-500 hover:border-amber-600', 'ring' => 'ring-amber-500/30'],
                ['key' => 'a_cloturer', 'label' => 'rdv à clôturer', 'value' => $stats['a_cloturer'], 'border' => 'border-l-red-500 hover:border-red-600', 'ring' => 'ring-red-500/30'],
                ['key' => 'rdv_planifies', 'label' => 'rdv planifiés', 'value' => $stats['rdv_planifies'], 'border' => 'border-l-[#173052] hover:border-[#1e3d6b]', 'ring' => 'ring-[#173052]/20'],
                ['key' => 'rdv_realises', 'label' => 'rdv réalisés (honorés)', 'value' => $stats['rdv_realises'] ?? 0, 'border' => 'border-l-green-600 hover:border-green-700', 'ring' => 'ring-green-600/30'],
                ['key' => 'rdv_non_honores', 'label' => 'rdv non honorés', 'value' => $stats['rdv_non_honores'] ?? 0, 'border' => 'border-l-slate-500 hover:border-slate-600', 'ring' => 'ring-slate-500/30'],
                ['key' => 'rdv_annules', 'label' => 'annulés / refusés', 'value' => $stats['rdv_annules'] ?? 0, 'border' => 'border-l-red-600 hover:border-red-700', 'ring' => 'ring-red-600/30'],
            ];
        @endphp

        <div class="grid gap-3 sm:gap-4 grid-cols-[repeat(auto-fill,minmax(11.25rem,1fr))]">
            @foreach($suiviStatCards as $card)
                <a href="{{ route('formalites.suivi.index', ['filtre' => $card['key'], 'annee_fiscale_id' => $anneeFiscaleId]) }}"
                   @class([
                       'dossier-stat border-l-4 flex flex-col justify-center min-h-[5.25rem] transition-colors',
                       $card['border'],
                       'ring-2 '.$card['ring'] => $filtre === $card['key'],
                   ])>
                    <p class="dossier-stat__label normal-case tracking-normal leading-snug">{{ $card['label'] }}</p>
                    <p class="dossier-stat__value text-2xl">{{ $card['value'] }}</p>
                </a>
            @endforeach
        </div>

        <div class="flex flex-wrap gap-2">
            @foreach($filtreLabels as $key => $label)
                <a href="{{ route('formalites.suivi.index', array_filter([
                    'filtre' => $key,
                    'annee_fiscale_id' => $anneeFiscaleId,
                    'q' => $key === 'remplies' ? $search : null,
                ])) }}"
                   @class([
                       'dossier-filter',
                       'dossier-filter--active' => $filtre === $key,
                       'dossier-filter--idle' => $filtre !== $key,
                   ])>
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @if($filtre === 'remplies')
            <section class="dossier-card overflow-hidden">
                <div class="dossier-card-head">
                    <h3>Formalités enregistrées</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="dossier-table w-full min-w-[880px] text-sm">
                        <thead>
                            <tr>
                                <th>Pensionné</th>
                                <th>Exercice</th>
                                <th>Enregistrée le</th>
                                <th>Agent</th>
                                <th>Dossier RDV</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($formalites as $f)
                                <tr class="hover:bg-gray-50/80">
                                    <td class="font-medium text-gray-900">{{ $f->user?->displayName() ?? '—' }}</td>
                                    <td>{{ $f->anneeFiscale?->code ?? '—' }}</td>
                                    <td class="whitespace-nowrap text-gray-600">{{ $f->realisee_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                    <td class="text-gray-700">{{ $f->agent?->displayName() ?? '—' }}</td>
                                    <td>
                                        @if($f->demande_id)
                                            <a href="{{ route('personal.request.show', $f->demande_id) }}" class="dossier-link">Ouvrir</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-12 text-center text-gray-500">Aucune formalité enregistrée pour cet exercice.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @include('formalites.suivi._pagination', ['paginator' => $formalites])
            </section>
        @elseif($filtre === 'en_attente')
            <section class="dossier-card overflow-hidden">
                <div class="dossier-card-head">
                    <div>
                        <h3>En attente d’enregistrement</h3>
                        <p class="text-xs font-normal text-gray-500 mt-0.5">RDV marqué réalisé, formalité non enregistrée pour l’exercice {{ $anneeLabel }}.</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="dossier-table w-full min-w-[880px] text-sm">
                        <thead>
                            <tr>
                                <th>Pensionné</th>
                                <th>Créneau RDV</th>
                                <th>Statut RDV</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($demandesAttente as $d)
                                @php
                                    $at = $d->rencontreScheduledAt();
                                    $owner = $d->user ?? \App\Models\User::find($d->created_by);
                                @endphp
                                <tr class="hover:bg-gray-50/80">
                                    <td class="font-medium text-gray-900">{{ $owner?->displayName() ?? '—' }}</td>
                                    <td class="text-gray-600 whitespace-nowrap">
                                        {{ $at?->format('d/m/Y H:i') ?? '—' }}
                                    </td>
                                    @php $statutAttente = $d->rencontreStatutMetier(); @endphp
                                    <td>
                                        <span class="inline-flex px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $statutAttente->badgeClass() }}">
                                            {{ $statutAttente->label() }}
                                        </span>
                                    </td>
                                    <td class="text-right space-x-2 whitespace-nowrap">
                                        <a href="{{ route('personal.request.show', $d->id) }}" class="dossier-btn-secondary text-xs px-2.5 py-1.5">Dossier</a>
                                        @if($statutAttente->permetEnregistrementFormalite())
                                            <a href="{{ route('demandes.rencontre.formalite', $d) }}" class="dossier-btn-primary text-xs px-2.5 py-1.5">Enregistrer</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-12 text-center text-gray-500">Aucun dossier en attente pour cet exercice.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @include('formalites.suivi._pagination', ['paginator' => $demandesAttente])
            </section>
        @elseif($filtre === 'a_cloturer')
            <section class="dossier-card overflow-hidden">
                <div class="dossier-card-head">
                    <h3>Rendez-vous formalités à clôturer</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="dossier-table w-full min-w-[720px] text-sm">
                        <thead>
                            <tr>
                                <th>Pensionné</th>
                                <th>Créneau</th>
                                <th>Statut</th>
                                <th class="text-right">Dossier</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($demandesCloture as $d)
                                @php $owner = $d->user ?? \App\Models\User::find($d->created_by); @endphp
                                <tr class="hover:bg-gray-50/80">
                                    <td>{{ $owner?->displayName() ?? '—' }}</td>
                                    <td class="whitespace-nowrap">{{ $d->rencontreScheduledAt()?->format('d/m/Y H:i') ?? '—' }}</td>
                                    <td>
                                        <span class="inline-flex px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $d->rencontreStatut()->badgeClass() }}">
                                            {{ $d->rencontreStatut()->label() }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('personal.request.show', $d->id) }}" class="dossier-btn-primary text-xs px-2.5 py-1.5">Traiter</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-12 text-center text-gray-500">Aucun rendez-vous en attente de clôture.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @include('formalites.suivi._pagination', ['paginator' => $demandesCloture])
            </section>
        @elseif(isset($demandesCloturesStatut))
            <section class="dossier-card overflow-hidden">
                <div class="dossier-card-head">
                    <h3>Rendez-vous — {{ $clotureStatutLabel ?? 'clôturés' }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="dossier-table w-full min-w-[720px] text-sm">
                        <thead>
                            <tr>
                                <th>Pensionné</th>
                                <th>Créneau</th>
                                <th>Statut RDV</th>
                                <th class="text-right">Dossier</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($demandesCloturesStatut as $d)
                                @php
                                    $owner = $d->user ?? \App\Models\User::find($d->created_by);
                                    $statutCloture = $d->rencontreStatutMetier();
                                @endphp
                                <tr class="hover:bg-gray-50/80">
                                    <td>{{ $owner?->displayName() ?? '—' }}</td>
                                    <td class="whitespace-nowrap">{{ $d->rencontreScheduledAt()?->format('d/m/Y H:i') ?? '—' }}</td>
                                    <td>
                                        <span class="inline-flex px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $statutCloture->badgeClass() }}">
                                            {{ $statutCloture->label() }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('personal.request.show', $d->id) }}" class="dossier-btn-secondary text-xs px-2.5 py-1.5">Ouvrir</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-12 text-center text-gray-500">Aucun rendez-vous dans cette catégorie.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @include('formalites.suivi._pagination', ['paginator' => $demandesCloturesStatut])
            </section>
        @else
            <section class="dossier-card overflow-hidden">
                <div class="dossier-card-head">
                    <h3>Rendez-vous formalités à venir</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="dossier-table w-full min-w-[720px] text-sm">
                        <thead>
                            <tr>
                                <th>Pensionné</th>
                                <th>Créneau</th>
                                <th>Statut</th>
                                <th class="text-right">Dossier</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($demandesPlanifies as $d)
                                @php $owner = $d->user ?? \App\Models\User::find($d->created_by); @endphp
                                <tr class="hover:bg-gray-50/80">
                                    <td>{{ $owner?->displayName() ?? '—' }}</td>
                                    <td class="whitespace-nowrap">{{ $d->rencontreScheduledAt()?->format('d/m/Y H:i') ?? '—' }}</td>
                                    <td>
                                        <span class="inline-flex px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $d->rencontreStatut()->badgeClass() }}">
                                            {{ $d->rencontreStatut()->label() }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('personal.request.show', $d->id) }}" class="dossier-btn-secondary text-xs px-2.5 py-1.5">Voir</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-12 text-center text-gray-500">Aucun rendez-vous planifié.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @include('formalites.suivi._pagination', ['paginator' => $demandesPlanifies])
            </section>
        @endif
    </x-portal-page>
</x-app-layout>
