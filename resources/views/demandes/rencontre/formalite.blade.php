@extends('layouts.main')

@section('title', 'Formalité — '.$demande->code)

@section('content')
    @php
        $data  = $demande->data ?? [];
        $owner = $demande->user;
    @endphp

    <div class="py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto space-y-5">

            {{-- Fil d'Ariane --}}
<nav class="flex items-center gap-1.5 text-xs text-gray-400 px-1">
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
    </svg>
    <a href="{{ route('personal.cart') }}" class="hover:text-gray-600 transition-colors">
        Corbeille
    </a>
    <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <a href="{{ url()->previous() }}" class="hover:text-gray-600 transition-colors">
        Dossier #{{ $demande->code }}
    </a>
    <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-gray-500 font-medium">Formalité</span>
</nav>

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- En-tête --}}
            <div class="bg-white rounded-2xl border-2 border-emerald-200 shadow-sm overflow-hidden">
                <div class="bg-emerald-600 px-6 py-3 flex items-center gap-3">
                    <svg class="w-5 h-5 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                    <div class="flex-1">
                        <h1 class="text-white font-bold text-sm tracking-wide">
                            TRAITEMENT DE LA FORMALITÉ
                        </h1>
                        <p class="text-white/80 text-xs mt-0.5">
                            Dossier #{{ $demande->code }}
                        </p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold {{ $rdvStatut->badgeClass() }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>
                        {{ $rdvStatut->label() }}
                    </span>
                </div>

                <div class="p-6 space-y-6">

                    {{-- Contexte --}}
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-gray-500">Pensionné</dt>
                            <dd class="font-medium text-gray-900 mt-0.5">
                                {{ $owner?->displayName() ?? '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Motif du rendez-vous</dt>
                            <dd class="font-medium text-gray-900 mt-0.5">{{ $motif }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Date du rendez-vous</dt>
                            <dd class="font-medium text-gray-900 mt-0.5">
                                {{ !empty($data['date_souhaitee'])
                                    ? \Carbon\Carbon::parse($data['date_souhaitee'])->format('d/m/Y')
                                    : '—' }}
                                @if(!empty($data['heure_souhaitee']))
                                    à {{ \App\Models\Demande::normalizeRencontreTime($data['heure_souhaitee']) }}
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Service responsable</dt>
                            <dd class="font-medium text-gray-900 mt-0.5">
                                {{ $data['service_responsable'] ?? '—' }}
                            </dd>
                        </div>
                    </dl>

                    {{-- Pièce fournie --}}
                    @if($cartePension)
                        <div class="border-t border-gray-100 pt-4">
                            <p class="text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Pièce fournie</p>
                            <a href="{{ Storage::url($cartePension) }}" target="_blank"
                               class="inline-flex items-center gap-2 text-sm bg-gray-50 border border-gray-200 text-gray-800 px-3 py-2 rounded-lg hover:bg-gray-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                Carte de pension
                            </a>
                        </div>
                    @endif

                    {{-- Historique --}}
                    @if($formalites->isNotEmpty())
                        <div class="border-t border-gray-100 pt-4">
                            <p class="text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">
                                Formalités déjà enregistrées
                            </p>
                            <div class="space-y-2">
                                @foreach($formalites as $f)
                                    <div class="flex items-start gap-3 bg-emerald-50 border border-emerald-200 rounded-xl p-3">
                                        <svg class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm text-emerald-900">
                                                <strong>Exercice {{ $f->anneeFiscale?->code ?? '—' }}</strong>
                                                @if($f->realisee_at)
                                                    · enregistrée le {{ $f->realisee_at->format('d/m/Y à H:i') }}
                                                @endif
                                            </p>
                                            @if($f->agent)
                                                <p class="text-xs text-emerald-700">
                                                    Par {{ $f->agent->displayName() }}
                                                </p>
                                            @endif
                                            @if($f->commentaire)
                                                <p class="text-sm text-emerald-800 italic mt-1">"{{ $f->commentaire }}"</p>
                                            @endif
                                            @if($f->demande_id)
                                                <p class="text-xs text-emerald-600 mt-1">
                                                    Issue du dossier
                                                    <a href="{{ route('personal.request.show', $f->demande_id) }}"
                                                       class="underline">{{ $f->demande->code ?? '#' . $f->demande_id }}</a>
                                                </p>
                                            @else
                                                <p class="text-xs text-emerald-600 mt-1 italic">
                                                    Enregistrée hors rendez-vous (walk-in)
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Alerte année active déjà traitée --}}
                    @if($formaliteAnneeEnCours)
                        <div class="border-t border-gray-100 pt-5">
                            <div class="flex items-start gap-3 bg-amber-50 border border-amber-200 rounded-xl p-4">
                                <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                </svg>
                                <div class="text-sm text-amber-900">
                                    La formalité pour l'exercice
                                    <strong>{{ $anneeActive?->code }}</strong>
                                    a déjà été enregistrée pour ce pensionné.
                                    Choisissez un autre exercice si nécessaire.
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Formulaire --}}
                    <form method="POST"
                          action="{{ route('demandes.rencontre.formalite.store', $demande) }}"
                          class="border-t border-gray-100 pt-5 space-y-4">
                        @csrf

                        <div>
                            <label for="annee_fiscale_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Exercice concerné <span class="text-red-500">*</span>
                            </label>
                            <select id="annee_fiscale_id" name="annee_fiscale_id" required
                                    class="w-full max-w-md border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-300">
                                <option value="">Choisir un exercice…</option>
                                @foreach($anneesDispo as $annee)
                                    <option value="{{ $annee->id }}"
                                        @selected(old('annee_fiscale_id', $anneeActive?->id) == $annee->id)>
                                        {{ $annee->code }}
                                        (du {{ $annee->date_debut->format('d/m/Y') }}
                                         au {{ $annee->date_fin->format('d/m/Y') }})
                                        @if($annee->active) — en cours @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('annee_fiscale_id')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="commentaire" class="block text-sm font-medium text-gray-700 mb-1">
                                Commentaire <span class="text-gray-400 font-normal">(optionnel)</span>
                            </label>
                            <textarea id="commentaire" name="commentaire" rows="3" maxlength="2000"
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-300 resize-none"
                                      placeholder="Observations, références, précisions…">{{ old('commentaire') }}</textarea>
                            @error('commentaire')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex flex-wrap items-center gap-3 pt-2">
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Enregistrer la formalité
                            </button>
                            <a href="{{ url()->previous() }}"
                               class="px-4 py-2 text-gray-600 hover:text-gray-800 text-sm">
                                Annuler
                            </a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection