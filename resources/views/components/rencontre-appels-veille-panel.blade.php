@props([
    'appelsVeille',
    'collapsedLimit' => 5,
])

@php
    use App\Models\Demande;
    use App\Services\RencontreReminderService;
    use Carbon\Carbon;

    $count = $appelsVeille->count();
    $veilleDate = Carbon::parse(app(RencontreReminderService::class)->reminderDate())->format('d/m/Y');
    $visioCount = $appelsVeille->filter(fn ($d) => ($d->data['modalite'] ?? '') === 'visio')->count();
    $physiqueCount = $count - $visioCount;
@endphp

<section
    class="dpc-square-ui border border-sky-200 bg-white shadow-sm overflow-hidden"
    x-data="{ showAll: {{ $count <= $collapsedLimit ? 'true' : 'false' }} }"
>
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-sky-100 bg-sky-50/80 px-5 py-4">
        <div class="flex items-center gap-3 min-w-0">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-600 text-white">
                <i class="fa-solid fa-phone" aria-hidden="true"></i>
            </span>
            <div>
                <h2 class="text-sm font-bold text-sky-950">Appels de rappel — veille des rencontres</h2>
                <p class="text-xs text-sky-800/70 mt-0.5">
                    RDV du <strong class="font-semibold">{{ $veilleDate }}</strong>
                    · contacter l’usager la veille du rendez-vous planifié.
                </p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if($visioCount > 0)
                <span class="inline-flex items-center gap-1 rounded-full bg-violet-100 px-2.5 py-0.5 text-[11px] font-semibold text-violet-900">
                    <i class="fa-solid fa-video text-[10px]" aria-hidden="true"></i>{{ $visioCount }} visio
                </span>
            @endif
            @if($physiqueCount > 0)
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-900">
                    <i class="fa-solid fa-building text-[10px]" aria-hidden="true"></i>{{ $physiqueCount }} présentiel
                </span>
            @endif
            <span class="inline-flex items-center rounded-full bg-sky-600 px-3 py-1 text-xs font-bold text-white tabular-nums">
                {{ $count }}
            </span>
        </div>
    </div>

    {{-- En-têtes colonnes (desktop) --}}
    <div class="hidden lg:grid lg:grid-cols-12 gap-3 px-5 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-gray-500 bg-gray-50/90 border-b border-gray-100">
        <div class="col-span-2">Heure</div>
        <div class="col-span-4">Usager / demande</div>
        <div class="col-span-2">Téléphone</div>
        <div class="col-span-2">Modalité</div>
        <div class="col-span-2 text-right">Action</div>
    </div>

    <ul
        class="divide-y divide-gray-100 {{ $count > $collapsedLimit ? 'max-h-[28rem] overflow-y-auto' : '' }}"
        :class="{ 'max-h-none overflow-visible': showAll && {{ $count }} > {{ $collapsedLimit }} }"
    >
        @foreach($appelsVeille as $rdv)
            @php
                $data = $rdv->data ?? [];
                $usager = $rdv->user;
                $nomUsager = $usager?->displayName()
                    ?: trim(($data['prenom'] ?? '').' '.($data['nom'] ?? ''))
                    ?: '—';
                $telephone = $data['telephone'] ?? $usager?->telephone ?? $usager?->phone ?? '';
                $telephoneDisplay = $telephone !== '' ? $telephone : '—';
                $telHref = $telephone !== '' ? 'tel:'.preg_replace('/[^\d+]/', '', $telephone) : null;
                $heure = ! empty($data['heure_souhaitee'])
                    ? Demande::normalizeRencontreTime($data['heure_souhaitee'])
                    : '—';
                $modalite = $data['modalite'] ?? 'physique';
                $isVisio = $modalite === 'visio';
                $dejaRappele = ! empty($data['rappel_veille_envoye_at']);
            @endphp
            <li
                class="px-5 py-4 hover:bg-sky-50/40 transition-colors lg:grid lg:grid-cols-12 lg:gap-3 lg:items-center"
                @if($loop->index >= $collapsedLimit) x-show="showAll" x-cloak @endif
            >
                {{-- Mobile + colonne heure --}}
                <div class="lg:col-span-2 flex items-start gap-3 lg:block">
                    <span class="inline-flex shrink-0 items-center justify-center min-w-[4.25rem] rounded-lg bg-sky-100 px-2.5 py-1.5 text-sm font-bold tabular-nums text-sky-950">
                        {{ $heure }}
                    </span>
                    @if($dejaRappele)
                        <span class="lg:hidden text-[10px] font-medium text-gray-500 mt-1">Notif. envoyée</span>
                    @endif
                </div>

                <div class="lg:col-span-4 mt-3 lg:mt-0 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $nomUsager }}</p>
                    <p class="text-xs text-gray-600 mt-0.5 truncate">{{ $rdv->libellePublic() }}</p>
                    @if($dejaRappele)
                        <p class="hidden lg:block text-[10px] text-gray-400 mt-1">Rappel système déjà émis</p>
                    @endif
                </div>

                <div class="lg:col-span-2 mt-2 lg:mt-0">
                    @if($telHref)
                        <a href="{{ $telHref }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-sky-800 hover:text-orange-600 hover:underline">
                            <i class="fas fa-phone-alt text-sky-500 text-xs" aria-hidden="true"></i>
                            {{ $telephoneDisplay }}
                        </a>
                    @else
                        <span class="text-sm text-gray-400">{{ $telephoneDisplay }}</span>
                    @endif
                </div>

                <div class="lg:col-span-2 mt-2 lg:mt-0">
                    @if($isVisio)
                        <span class="inline-flex items-center gap-1 rounded-full bg-violet-100 px-2 py-0.5 text-[11px] font-semibold text-violet-900">
                            <i class="fa-solid fa-video text-[10px]" aria-hidden="true"></i> Visio
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-900">
                            <i class="fa-solid fa-building text-[10px]" aria-hidden="true"></i>
                            <span class="truncate max-w-[8rem]" title="{{ $data['lieu_rdv'] ?? 'Présentiel' }}">{{ $data['lieu_rdv'] ?? 'Présentiel' }}</span>
                        </span>
                    @endif
                </div>

                <div class="lg:col-span-2 mt-3 lg:mt-0 flex lg:justify-end">
                    <a href="{{ route('personal.request.show', $rdv->id) }}"
                       class="inline-flex w-full lg:w-auto items-center justify-center gap-2 rounded-lg bg-[#173052] px-4 py-2 text-xs font-semibold text-white hover:bg-orange-600 transition-colors">
                        Dossier
                        <i class="fas fa-arrow-right text-[10px]" aria-hidden="true"></i>
                    </a>
                </div>
            </li>
        @endforeach
    </ul>

    @if($count > $collapsedLimit)
        <div class="border-t border-gray-100 bg-gray-50/80 px-5 py-3 flex flex-wrap items-center justify-between gap-2">
            <p class="text-xs text-gray-600">
                <span x-show="!showAll">{{ $collapsedLimit }} sur {{ $count }} affichés</span>
                <span x-show="showAll" x-cloak>{{ $count }} rappels · triés par heure</span>
            </p>
            <button
                type="button"
                class="text-xs font-semibold text-sky-800 hover:text-orange-600 underline-offset-2 hover:underline"
                @click="showAll = !showAll"
                x-text="showAll ? 'Réduire la liste' : 'Afficher les {{ $count - $collapsedLimit }} autres'"
            ></button>
        </div>
    @elseif($count > 1)
        <div class="border-t border-gray-100 bg-gray-50/50 px-5 py-2">
            <p class="text-xs text-gray-500">{{ $count }} rappels · triés par heure de rendez-vous</p>
        </div>
    @endif
</section>
