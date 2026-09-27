@if(($mesRendezVous ?? collect())->isNotEmpty())
    <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
        <div class="flex items-center gap-3 mb-5">
            <span class="text-2xl text-navy flex items-center justify-center shrink-0">
                <i class="fa-solid fa-calendar-check" aria-hidden="true"></i>
            </span>
            <div>
                <h2 class="text-xl font-bold text-navy">Mes rendez-vous</h2>
                <p class="text-sm text-gray-500">Vous pouvez annuler un rendez-vous tant qu’il n’a pas eu lieu.</p>
            </div>
        </div>

        <ul class="divide-y divide-gray-100">
            @foreach ($mesRendezVous as $rdv)
                @php
                    $data = $rdv->data ?? [];
                    $date = $data['date_souhaitee'] ?? null;
                    $heure = \App\Models\Demande::normalizeRencontreTime($data['heure_souhaitee'] ?? null);
                    $canCancel = $rdv->canBeCancelledByUser();
                    $rdvStatut = $rdv->rencontreStatut();
                    $status = $rdvStatut->label();
                    $isPhysique = ($data['modalite'] ?? 'visio') === 'physique';
                    $visio = app(\App\Services\RencontreVisioService::class);
                    $visioStatus = (! $isPhysique && $rdv->visio_token) ? $visio->status($rdv) : null;
                @endphp
                <li class="py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-800">
                            {{ $data['objet'] ?? $rdv->code }}
                            <span class="ml-1 text-xs font-semibold px-2 py-0.5 rounded-full {{ $isPhysique ? 'bg-orange-100 text-orange-700' : 'bg-indigo-100 text-indigo-700' }}">
                                {{ $isPhysique ? 'Présentiel' : 'Visioconférence' }}
                            </span>
                        </p>
                        <p class="text-sm text-gray-500 mt-0.5">
                            Réf. {{ $rdv->code }}
                            @if($date)
                                · {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                            @endif
                            @if($heure)
                                à {{ $heure }}
                            @endif
                            @if(!empty($data['agent_nom']))
                                · {{ $data['agent_nom'] }} (Formalités)
                            @endif
                            @if($isPhysique && !empty($data['lieu_rdv']))
                                · {{ $data['lieu_rdv'] }}
                            @endif
                        </p>
                        <p class="text-xs mt-1">
                            <span class="px-2 py-0.5 rounded-full font-medium {{ $rdvStatut->badgeClass() }}">{{ $status }}</span>
                        </p>
                        @if($rdvStatut === \App\Enums\RencontreStatutEnum::VALIDE && !empty($data['confirmation']))
                            <p class="text-xs text-gray-500 mt-2">
                                Confirmé — {{ $data['confirmation']['service'] ?? 'Formalités' }}
                                @if(!empty($data['confirmation']['lieu']))
                                    · {{ $data['confirmation']['lieu'] }}
                                @endif
                            </p>
                        @endif
                        @if($visioStatus)
                            <p class="text-xs mt-2">
                                @if($visioStatus === 'actif')
                                    <a href="{{ route('demandes.rencontre.visio', $rdv->visio_token) }}"
                                       class="inline-flex items-center gap-1 text-navy font-semibold hover:underline">
                                        Rejoindre la visioconférence
                                    </a>
                                @elseif($visioStatus === 'en_attente')
                                    <span class="text-gray-500">Lien sécurisé prêt — actif 15 minutes avant le rendez-vous.</span>
                                @else
                                    <span class="text-gray-400">Lien de visioconférence expiré.</span>
                                @endif
                            </p>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                    @if(! $isPhysique && $rdv->visio_token && $visioStatus === 'en_attente')
                        <a href="{{ route('demandes.rencontre.visio', $rdv->visio_token) }}"
                           class="inline-flex items-center gap-2 px-4 py-2 border border-navy/20 text-navy text-sm font-semibold rounded-lg hover:bg-slate-50">
                            Voir le lien
                        </a>
                    @endif
                    @if($canCancel)
                        <form method="POST" action="{{ route('demandes.rencontre.annuler', $rdv) }}"
                              onsubmit="return confirm('Annuler ce rendez-vous ? Le créneau redeviendra disponible.')">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-4 py-2 border border-red-200 text-red-700 text-sm font-semibold rounded-lg hover:bg-red-50 transition-colors">
                                <i class="fa-solid fa-calendar-xmark"></i>
                                Annuler
                            </button>
                        </form>
                    @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </section>
@endif
