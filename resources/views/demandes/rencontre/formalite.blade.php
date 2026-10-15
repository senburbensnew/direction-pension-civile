<x-app-layout>
    @php
        $data = $demande->data ?? [];
        $owner = $owner ?? $demande->user ?? \App\Models\User::find($demande->created_by);
        $peutEnregistrer = ($peutEnregistrer ?? false) && ! ($formaliteAnneeEnCours ?? false);
        $rdvScheduledAt = $rdvScheduledAt ?? $demande->rencontreScheduledAt();
        $heureNorm = ! empty($data['heure_souhaitee'])
            ? \App\Models\Demande::normalizeRencontreTime($data['heure_souhaitee'])
            : null;
    @endphp

    <x-portal-page class="space-y-6">
        <header class="dossier-header px-5 py-5 sm:px-6 border-l-4 border-l-emerald-600">
            <p class="dossier-kicker">
                <a href="{{ route('personal.cart') }}" class="text-[#173052] hover:underline">Corbeille</a>
                · <a href="{{ route('personal.request.show', $demande->id) }}" class="text-[#173052] hover:underline">{{ $demande->libellePublic() }}</a>
            </p>
            <div class="mt-1 flex flex-wrap items-center gap-3">
                <h1 class="text-xl font-semibold text-[#173052] tracking-tight">Traitement de la formalité</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {{ $rdvStatut->badgeClass() }}">{{ $rdvStatut->label() }}</span>
            </div>
            <p class="text-sm text-gray-600 mt-2 max-w-3xl">
                Enregistrez la formalité fiscale liée à ce rendez-vous. Vérifiez l’identité du pensionné et l’exercice concerné avant validation.
            </p>
        </header>

        @if($errors->any())
            <div class="dossier-notice border-l-red-500 bg-red-50 text-red-800 px-4 py-3 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($rdvStatut === \App\Enums\RencontreStatutEnum::REALISE && ! ($peutEnregistrer ?? false) && $rdvScheduledAt && $rdvScheduledAt->isFuture())
            <div class="dossier-notice dossier-notice--amber bg-amber-50/80 px-4 py-3 text-sm text-amber-900">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p>
                    Le rendez-vous est marqué réalisé. L’enregistrement de la formalité sera disponible à partir du
                    <strong>{{ $rdvScheduledAt->format('d/m/Y à H:i') }}</strong>.
                </p>
            </div>
            <x-rencontre-scheduled-unlock-refresh :opens-at="$rdvScheduledAt" />
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
            {{-- Colonne principale : enregistrement --}}
            <div class="xl:col-span-7 space-y-6">
                <section class="dossier-card overflow-hidden">
                    <div class="dossier-card-head">
                        <div>
                            <h2>Nouvelle formalité</h2>
                            <p class="text-xs text-gray-500 mt-0.5 font-normal">Une seule formalité par exercice et par pensionné.</p>
                        </div>
                    </div>

                    <div class="p-6">
                        @if($formaliteAnneeEnCours ?? false)
                            <div class="mb-6 flex items-start gap-3 bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-900">
                                <svg class="w-5 h-5 flex-shrink-0 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                </svg>
                                <p>
                                    La formalité pour l’exercice <strong>{{ $anneeActive?->code }}</strong> est déjà enregistrée.
                                    Choisissez un autre exercice si la situation le justifie.
                                </p>
                            </div>
                        @endif

                        <form method="POST"
                              action="{{ route('demandes.rencontre.formalite.store', $demande) }}"
                              class="space-y-5">
                            @csrf

                            <div>
                                <label for="annee_fiscale_id" class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Exercice fiscal <span class="text-red-500">*</span>
                                </label>
                                <select id="annee_fiscale_id" name="annee_fiscale_id" required
                                        @disabled(! $peutEnregistrer)
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-300 disabled:bg-gray-50 disabled:text-gray-500">
                                    <option value="">Choisir un exercice…</option>
                                    @foreach($anneesDispo as $annee)
                                        <option value="{{ $annee->id }}"
                                            @selected(old('annee_fiscale_id', $anneeActive?->id) == $annee->id)>
                                            {{ $annee->code }}
                                            ({{ $annee->date_debut->format('d/m/Y') }} – {{ $annee->date_fin->format('d/m/Y') }})
                                            @if($annee->active) — en cours @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('annee_fiscale_id')
                                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="commentaire" class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Commentaire <span class="text-gray-400 font-normal">(optionnel)</span>
                                </label>
                                <textarea id="commentaire" name="commentaire" rows="4" maxlength="2000"
                                          @disabled(! $peutEnregistrer)
                                          class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-300 resize-y min-h-[6rem] disabled:bg-gray-50"
                                          placeholder="Observations, références internes, précisions sur la vérification…">{{ old('commentaire') }}</textarea>
                                @error('commentaire')
                                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex flex-wrap items-center gap-3 pt-2 border-t border-gray-100">
                                <button type="submit"
                                        @disabled(! $peutEnregistrer)
                                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:pointer-events-none text-white text-sm font-semibold rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Enregistrer la formalité
                                </button>
                                <a href="{{ route('personal.request.show', $demande->id) }}"
                                   class="px-4 py-2.5 text-sm font-medium text-gray-600 hover:text-navy">
                                    Retour au dossier
                                </a>
                            </div>
                        </form>
                    </div>
                </section>
            </div>

            {{-- Colonne latérale : contexte --}}
            <aside class="xl:col-span-5 space-y-6">
                <section class="dossier-card p-5 sm:p-6 space-y-4">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-gray-500">Rendez-vous</h2>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-1 gap-4 text-sm">
                        <div>
                            <dt class="text-gray-500">Pensionné</dt>
                            <dd class="font-semibold text-gray-900 mt-0.5">{{ $owner?->displayName() ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Motif</dt>
                            <dd class="font-medium text-gray-900 mt-0.5">{{ $motif }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Créneau</dt>
                            <dd class="font-medium text-gray-900 mt-0.5">
                                @if(! empty($data['date_souhaitee']))
                                    {{ \Carbon\Carbon::parse($data['date_souhaitee'])->format('d/m/Y') }}
                                    @if($heureNorm)
                                        <span class="text-gray-600">à {{ $heureNorm }}</span>
                                    @endif
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Service</dt>
                            <dd class="font-medium text-gray-900 mt-0.5">{{ $data['service_responsable'] ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Dossier</dt>
                            <dd class="font-medium text-gray-900 mt-0.5">{{ $demande->code ?? '#'.$demande->id }}</dd>
                        </div>
                    </dl>

                    @if($cartePension ?? null)
                        <div class="pt-4 border-t border-gray-100">
                            <p class="text-xs font-bold uppercase tracking-wide text-gray-500 mb-2">Pièce jointe</p>
                            <a href="{{ $demande->urlForStoredPath($cartePension) }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-2 text-sm bg-slate-50 border border-slate-200 text-slate-800 px-3 py-2 rounded-lg hover:bg-slate-100 w-full sm:w-auto">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                Carte de pension
                            </a>
                        </div>
                    @endif
                </section>

                @if($formalites->isNotEmpty())
                    <section class="dossier-card p-5 sm:p-6 space-y-3">
                        <h2 class="text-sm font-bold uppercase tracking-wide text-gray-500">
                            Historique ({{ $formalites->count() }})
                        </h2>
                        <ul class="space-y-3 max-h-[28rem] overflow-y-auto pr-1">
                            @foreach($formalites as $f)
                                <li class="bg-emerald-50/80 border border-emerald-100 rounded-xl p-3.5">
                                    <div class="flex gap-2">
                                        <svg class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-semibold text-emerald-900">
                                                Exercice {{ $f->anneeFiscale?->code ?? '—' }}
                                            </p>
                                            @if($f->realisee_at)
                                                <p class="text-xs text-emerald-700 mt-0.5">
                                                    {{ $f->realisee_at->format('d/m/Y à H:i') }}
                                                    @if($f->agent)
                                                        · {{ $f->agent->displayName() }}
                                                    @endif
                                                </p>
                                            @endif
                                            @if($f->commentaire)
                                                <p class="text-sm text-emerald-800/90 mt-2 italic">« {{ $f->commentaire }} »</p>
                                            @endif
                                            @if($f->demande_id)
                                                <a href="{{ route('personal.request.show', $f->demande_id) }}"
                                                   class="inline-block text-xs text-emerald-700 font-medium mt-2 hover:underline">
                                                    Voir le dossier source
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            </aside>
        </div>
    </x-portal-page>
</x-app-layout>
