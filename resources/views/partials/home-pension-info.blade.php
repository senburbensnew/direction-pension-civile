@php
    $guideTabs = [
        ['id' => 'accueil', 'icon' => 'fa-calendar-week', 'label' => 'home.hub_tab_accueil'],
        ['id' => 'mandats', 'icon' => 'fa-file-signature', 'label' => 'home.hub_tab_mandats'],
        ['id' => 'formalites', 'icon' => 'fa-id-card', 'label' => 'home.hub_tab_formalites'],
        ['id' => 'paiements', 'icon' => 'fa-money-check-dollar', 'label' => 'home.hub_tab_paiements'],
        ['id' => 'nouveau', 'icon' => 'fa-user-plus', 'label' => 'home.hub_tab_nouveau'],
        ['id' => 'proximite', 'icon' => 'fa-map-location-dot', 'label' => 'home.hub_tab_proximite'],
        ['id' => 'pratique', 'icon' => 'fa-phone', 'label' => 'home.hub_tab_pratique'],
    ];

    $avisNotices = [
        [
            'id' => 'pension',
            'icon' => 'fa-id-card',
            'title' => 'home.avis_pension_title',
            'intro' => 'home.avis_pension_intro',
            'docs' => 'home.avis_pension_docs',
            'items' => ['home.avis_pension_1', 'home.avis_pension_2', 'home.avis_pension_3', 'home.avis_pension_4', 'home.avis_pension_5', 'home.avis_pension_6', 'home.avis_pension_7'],
        ],
        [
            'id' => 'mineur',
            'icon' => 'fa-child',
            'title' => 'home.avis_mineur_title',
            'intro' => 'home.avis_mineur_intro',
            'docs' => 'home.avis_mineur_docs',
            'items' => ['home.avis_mineur_1', 'home.avis_mineur_2', 'home.avis_mineur_3', 'home.avis_mineur_4', 'home.avis_mineur_5'],
        ],
        [
            'id' => 'tuteur',
            'icon' => 'fa-people-roof',
            'title' => 'home.avis_tuteur_title',
            'intro' => 'home.avis_tuteur_intro',
            'docs' => 'home.avis_tuteur_docs',
            'items' => ['home.avis_tuteur_1', 'home.avis_tuteur_2', 'home.avis_tuteur_3', 'home.avis_tuteur_4', 'home.avis_tuteur_5', 'home.avis_tuteur_6'],
        ],
        [
            'id' => 'jeune',
            'icon' => 'fa-user-graduate',
            'title' => 'home.avis_jeune_title',
            'intro' => 'home.avis_jeune_intro',
            'docs' => 'home.avis_jeune_docs',
            'items' => ['home.avis_jeune_1', 'home.avis_jeune_2', 'home.avis_jeune_3', 'home.avis_jeune_4', 'home.avis_jeune_5', 'home.avis_jeune_6'],
            'note' => 'home.avis_jeune_nb',
        ],
    ];

    $dpcPhones = [
        ['label' => 'home.tel_cheques', 'tel' => '+50947912007', 'display' => '+(509) 4791-2007'],
        ['label' => 'home.tel_avals', 'tel' => '+50929405829', 'display' => '+(509) 2940-5829'],
        ['label' => 'home.tel_demande', 'tel' => '+50929407129', 'display' => '+(509) 2940-7129'],
        ['label' => 'home.tel_attestation', 'tel' => '+50929405829', 'display' => '+(509) 2940-5829'],
        ['label' => 'home.tel_assurance', 'tel' => '+50947912006', 'display' => '+(509) 4791-2006'],
    ];

    $homeDirections = \App\Models\DirectionDepartementale::ordered()->get();
@endphp

<div x-data="{
        tab: 'accueil',
        openAvis: 'pension',
        tabs: ['accueil','mandats','formalites','paiements','nouveau','proximite','pratique'],
        setTab(id) {
            if (!this.tabs.includes(id)) return;
            this.tab = id;
            history.replaceState(null, '', '#' + id);
            this.$nextTick(() => this.$refs.panel?.scrollIntoView({ behavior: 'smooth', block: 'start' }));
        }
     }"
     x-init="
        const hash = location.hash.replace('#','');
        if (this.tabs.includes(hash)) this.tab = hash;
     ">
<section class="relative overflow-hidden">
    <div class="absolute inset-y-0 left-0 w-1.5 bg-orange-500"></div>
    <div class="bg-navy bg-motif-dark py-5 md:py-6">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row md:items-center gap-4 md:gap-8 pl-2">
            <div class="flex items-center gap-3 shrink-0">
                <span class="w-11 h-11 rounded-full bg-orange-500 text-white flex items-center justify-center shadow-lg shadow-orange-500/30">
                    <i class="fas fa-triangle-exclamation"></i>
                </span>
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-orange-300">{{ __('home.hub_urgent_label') }}</p>
            </div>
            <div class="md:flex-1">
                <p class="text-white font-semibold leading-snug">{{ __('home.rappel_expiry') }}</p>
                <p class="text-blue-100 text-sm mt-1 leading-relaxed">{{ __('home.rappel_renewal') }}</p>
            </div>
            <button type="button"
                    @click="setTab('mandats')"
                    class="inline-flex items-center justify-center px-5 py-2.5 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold rounded-full transition-colors shrink-0">
                {{ __('home.hub_tab_mandats') }}
                <i class="fas fa-arrow-right text-[10px] ml-2"></i>
            </button>
        </div>
    </div>
    </div>
</section>

<section class="py-16 md:py-20 bg-[#eef2f7] bg-motif">
    <div class="container mx-auto px-4">
        <x-home-heading :kicker="__('home.comm_kicker')" :title="__('home.comm_title')" :intro="__('home.comm_audience')" />

        <div class="grid md:grid-cols-3 gap-4 items-stretch mb-8">
            @foreach([
                ['icon' => 'fa-file-lines', 'title' => 'home.comm_attestation_title', 'body' => 'home.comm_attestation_body'],
                ['icon' => 'fa-stethoscope', 'title' => 'home.comm_medical_title', 'body' => 'home.comm_medical_body'],
                ['icon' => 'fa-handshake', 'title' => 'home.comm_aval_title', 'body' => 'home.comm_aval_body'],
            ] as $offer)
                <article class="home-card home-card-hover p-6">
                    <span class="w-11 h-11 rounded-xl bg-navy text-white flex items-center justify-center mb-4">
                        <i class="fas {{ $offer['icon'] }}"></i>
                    </span>
                    <h3 class="text-lg font-bold text-navy mb-2">{{ __($offer['title']) }}</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">{{ __($offer['body']) }}</p>
                </article>
            @endforeach
        </div>

        <div class="home-card p-6 md:p-8">
            <div class="flex items-center gap-3 mb-4">
                <span class="w-10 h-10 rounded-xl bg-orange-500 text-white flex items-center justify-center">
                    <i class="fas fa-folder-open"></i>
                </span>
                <h3 class="text-xl font-bold text-navy">{{ __('home.comm_docs_title') }}</h3>
            </div>
            <ul class="space-y-3">
                @foreach(['home.comm_docs_1', 'home.comm_docs_2', 'home.comm_docs_3'] as $doc)
                    <li class="flex items-center gap-2 text-sm text-gray-700 bg-slate-50 rounded-xl px-4 py-3">
                        <i class="fas fa-check text-orange-500 text-xs"></i>
                        <span>{{ __($doc) }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</section>

<section id="guide-pensionne" class="py-16 md:py-20 bg-white bg-motif scroll-mt-24">
    <div class="container mx-auto px-4">
        <x-home-heading :kicker="__('home.hub_kicker')" :title="__('home.hub_title')" :intro="__('home.hub_intro')" />

        <div class="home-tabs bg-[#eef2f7] rounded-2xl p-2 mb-8 overflow-x-auto" role="tablist" aria-label="{{ __('home.hub_title') }}">
            <div class="flex gap-1 min-w-max">
            @foreach($guideTabs as $guideTab)
                <button type="button"
                        role="tab"
                        :aria-selected="tab === '{{ $guideTab['id'] }}'"
                        @click="setTab('{{ $guideTab['id'] }}')"
                        :class="tab === '{{ $guideTab['id'] }}' ? 'bg-navy text-white shadow-md' : 'text-navy hover:bg-white'"
                        class="inline-flex items-center gap-2 whitespace-nowrap px-4 py-2.5 rounded-xl text-sm font-semibold transition-colors">
                    <i class="fas {{ $guideTab['icon'] }} text-xs opacity-80"></i>
                    {{ __($guideTab['label']) }}
                </button>
            @endforeach
            </div>
        </div>

        <div x-ref="panel" class="max-w-5xl mx-auto scroll-mt-28">
            <div x-show="tab === 'accueil'" x-cloak role="tabpanel">
                <article class="home-card p-6 md:p-8 space-y-6">
                    <div class="flex items-start gap-4">
                        <span class="w-11 h-11 rounded-lg bg-navy text-white flex items-center justify-center shrink-0">
                            <i class="fas fa-calendar-week"></i>
                        </span>
                        <div>
                            <h3 class="text-xl md:text-2xl font-bold text-navy">{{ __('home.accueil_title') }}</h3>
                            <p class="text-gray-700 leading-relaxed mt-3">{{ __('home.accueil_body') }}</p>
                        </div>
                    </div>
                    <p class="text-gray-700 leading-relaxed bg-slate-50 border-l-4 border-orange-500 pl-4 py-3 rounded-r-lg">
                        {{ __('home.accueil_note') }}
                    </p>
                    <div class="border-t border-slate-100 pt-6">
                        <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                            <div class="shrink-0 w-16 h-16 rounded-2xl bg-orange-500 text-white flex items-center justify-center text-center px-2">
                                <span class="text-xs font-bold leading-tight">{{ __('home.mandat_day_label') }}</span>
                            </div>
                            <div>
                                <h4 class="text-lg font-bold text-navy">{{ __('home.mandat_day_title') }}</h4>
                                <p class="text-gray-700 leading-relaxed mt-2">{{ __('home.mandat_day_intro') }}</p>
                                <p class="text-gray-700 leading-relaxed mt-2">{{ __('home.mandat_day_when') }}</p>
                                <p class="text-gray-700 leading-relaxed bg-slate-50 border-l-4 border-orange-500 pl-4 py-3 rounded-r-lg mt-3">
                                    {{ __('home.mandat_day_goal') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </article>
            </div>

            <div x-show="tab === 'mandats'" x-cloak role="tabpanel">
                <article class="home-card p-6 md:p-8 shadow-[0_8px_28px_rgba(23,48,82,0.06)] space-y-5">
                    <div class="flex items-start gap-4">
                        <span class="w-11 h-11 rounded-lg bg-orange-500 text-white flex items-center justify-center shrink-0">
                            <i class="fas fa-file-signature"></i>
                        </span>
                        <div>
                            <h3 class="text-xl md:text-2xl font-bold text-navy">{{ __('home.mandat_exp_title') }}</h3>
                            <p class="text-gray-700 leading-relaxed mt-3">{{ __('home.rappel_title') }}</p>
                        </div>
                    </div>
                    <p class="text-gray-700 leading-relaxed">{{ __('home.rappel_payment') }}</p>
                    <p class="text-gray-700 leading-relaxed">{{ __('home.rappel_mandataries') }}</p>
                    <p class="font-semibold text-navy">{{ __('home.rappel_expiry') }}</p>
                    <p class="text-gray-700 leading-relaxed">{{ __('home.mandat_exp_intro') }}</p>
                    <p class="text-gray-700 leading-relaxed">{{ __('home.mandat_exp_from') }}</p>
                    <x-home-checks :items="['home.mandat_exp_1', 'home.mandat_exp_2', 'home.mandat_exp_3']" :columns="false" />
                    <p class="text-gray-700 leading-relaxed">{{ __('home.mandat_exp_close') }}</p>
                    <p class="text-sm text-navy leading-relaxed bg-orange-50 border-l-4 border-orange-500 pl-4 py-3 rounded-r-lg">
                        {{ __('home.mandat_exp_nb') }}
                    </p>
                </article>
            </div>

            <div x-show="tab === 'formalites'" x-cloak role="tabpanel">
                <div class="space-y-3">
                    @foreach($avisNotices as $notice)
                        <article class="home-card shadow-[0_8px_28px_rgba(23,48,82,0.06)] overflow-hidden">
                            <button type="button"
                                    class="w-full flex items-center gap-3 p-5 md:p-6 text-left"
                                    @click="openAvis = openAvis === '{{ $notice['id'] }}' ? null : '{{ $notice['id'] }}'">
                                <span class="w-10 h-10 rounded-lg bg-navy text-white flex items-center justify-center shrink-0">
                                    <i class="fas {{ $notice['icon'] }}"></i>
                                </span>
                                <h3 class="flex-1 text-lg md:text-xl font-bold text-navy">{{ __($notice['title']) }}</h3>
                                <i class="fas fa-chevron-down text-navy/50 text-sm transition-transform"
                                   :class="openAvis === '{{ $notice['id'] }}' && 'rotate-180'"></i>
                            </button>
                            <div x-show="openAvis === '{{ $notice['id'] }}'" x-cloak class="px-5 md:px-6 pb-6">
                                <p class="text-gray-700 leading-relaxed mb-3">{{ __($notice['intro']) }}</p>
                                <p class="text-sm font-semibold text-navy mb-3">{{ __($notice['docs']) }}</p>
                                <x-home-checks :items="$notice['items']" />
                                @if(!empty($notice['note']))
                                    <p class="text-sm text-navy leading-relaxed bg-orange-50 border-l-4 border-orange-500 pl-4 py-3 rounded-r-lg mt-4">
                                        {{ __($notice['note']) }}
                                    </p>
                                @endif
                            </div>
                        </article>
                    @endforeach

                    <article class="home-card p-6 shadow-[0_8px_28px_rgba(23,48,82,0.06)]">
                        <h3 class="text-lg font-bold text-navy mb-3">{{ __('home.avis_exterieur_title') }}</h3>
                        <x-home-checks :items="['home.avis_exterieur_1', 'home.avis_exterieur_2', 'home.avis_exterieur_3', 'home.avis_exterieur_4', 'home.avis_exterieur_5']" />
                    </article>
                    <article class="home-card p-6 shadow-[0_8px_28px_rgba(23,48,82,0.06)]">
                        <h3 class="text-lg font-bold text-navy mb-3">{{ __('home.avis_malade_title') }}</h3>
                        <x-home-checks :items="['home.avis_malade_1', 'home.avis_malade_2', 'home.avis_malade_3', 'home.avis_malade_4', 'home.avis_malade_5', 'home.avis_malade_6']" />
                    </article>
                </div>
            </div>

            <div x-show="tab === 'paiements'" x-cloak role="tabpanel">
                <div class="space-y-4">
                    <article class="bg-navy text-white rounded-2xl p-6 md:p-8 shadow-[0_8px_28px_rgba(23,48,82,0.12)]">
                        <h3 class="text-xl md:text-2xl font-bold">{{ __('home.avis_rente_title') }}</h3>
                        <p class="text-lg font-semibold text-orange-300 mt-3">{{ __('home.avis_rente_lead') }}</p>
                        <p class="text-blue-100 leading-relaxed mt-2">{{ __('home.avis_rente_body') }}</p>
                        <p class="font-bold text-white bg-white/10 border-l-4 border-orange-400 pl-4 py-3 rounded-r-lg mt-4">
                            {{ __('home.avis_rente_cta') }}
                        </p>
                    </article>

                    <article class="home-card p-6 md:p-8 shadow-[0_8px_28px_rgba(23,48,82,0.06)]">
                        <h3 class="text-xl font-bold text-navy mb-3">{{ __('home.paiement_title') }}</h3>
                        <div class="flex flex-wrap gap-2 mb-4">
                            <span class="px-3 py-1.5 rounded-full bg-navy text-white text-sm font-semibold">{{ __('home.paiement_cheque') }}</span>
                            <span class="px-3 py-1.5 rounded-full bg-orange-500 text-white text-sm font-semibold">{{ __('home.paiement_virement') }}</span>
                        </div>
                        <p class="text-gray-700 leading-relaxed mb-3">{{ __('home.paiement_virement_intro') }}</p>
                        <x-home-checks :items="['home.paiement_virement_1', 'home.paiement_virement_2', 'home.paiement_virement_3', 'home.paiement_virement_4', 'home.paiement_virement_5']" />
                    </article>

                    <div class="grid md:grid-cols-2 gap-4">
                        <article class="home-card p-6 shadow-[0_8px_28px_rgba(23,48,82,0.06)]">
                            <h3 class="text-lg font-bold text-navy mb-2">{{ __('home.perte_title') }}</h3>
                            <p class="text-sm text-gray-700 leading-relaxed">{{ __('home.perte_body') }}</p>
                        </article>
                        <article class="home-card p-6 shadow-[0_8px_28px_rgba(23,48,82,0.06)]">
                            <h3 class="text-lg font-bold text-navy mb-2">{{ __('home.transfert_title') }}</h3>
                            <p class="text-sm text-gray-700 leading-relaxed mb-3">{{ __('home.transfert_who') }}</p>
                            <x-home-checks :items="['home.transfert_1', 'home.transfert_2', 'home.transfert_3']" :columns="false" />
                        </article>
                    </div>

                    <article class="home-card p-6 md:p-8 shadow-[0_8px_28px_rgba(23,48,82,0.06)]">
                        <h3 class="text-lg font-bold text-navy mb-3">{{ __('home.reclamation_title') }}</h3>
                        <x-home-checks :items="['home.reclamation_1', 'home.reclamation_2', 'home.reclamation_3', 'home.reclamation_4', 'home.reclamation_5', 'home.reclamation_6']" />
                    </article>
                </div>
            </div>

            <div x-show="tab === 'nouveau'" x-cloak role="tabpanel">
                <div class="space-y-4">
                    <article class="home-card p-6 md:p-8 shadow-[0_8px_28px_rgba(23,48,82,0.06)]">
                        <h3 class="text-xl md:text-2xl font-bold text-navy">{{ __('home.nouveau_title') }}</h3>
                        <p class="text-gray-600 mt-1">{{ __('home.nouveau_intro') }}</p>
                        <div class="grid md:grid-cols-2 gap-4 mt-5">
                            <div class="bg-slate-50 rounded-xl p-4">
                                <h4 class="font-bold text-navy mb-3">{{ __('home.nouveau_actif_title') }}</h4>
                                <x-home-checks :items="['home.nouveau_actif_1', 'home.nouveau_actif_2', 'home.nouveau_actif_3', 'home.nouveau_actif_4', 'home.nouveau_actif_5']" :columns="false" />
                            </div>
                            <div class="bg-slate-50 rounded-xl p-4">
                                <h4 class="font-bold text-navy mb-3">{{ __('home.nouveau_reversion_title') }}</h4>
                                <x-home-checks :items="['home.nouveau_reversion_1', 'home.nouveau_reversion_2', 'home.nouveau_reversion_3', 'home.nouveau_reversion_4']" :columns="false" />
                            </div>
                        </div>
                    </article>

                    <article class="home-card p-6 md:p-8 shadow-[0_8px_28px_rgba(23,48,82,0.06)]">
                        <h3 class="text-xl font-bold text-navy mb-3">{{ __('home.formalites_title') }}</h3>
                        <p class="text-gray-700 leading-relaxed mb-4">{{ __('home.formalites_body') }}</p>
                        <p class="text-sm text-navy leading-relaxed bg-orange-50 border-l-4 border-orange-500 pl-4 py-3 rounded-r-lg mb-6">
                            {{ __('home.formalites_nb') }}
                        </p>
                        <h4 class="text-lg font-bold text-navy mb-2">{{ __('home.formalites_ass_title') }}</h4>
                        <p class="text-gray-700 leading-relaxed mb-5">{{ __('home.formalites_ass_body') }}</p>
                        <h4 class="text-lg font-bold text-navy mb-2">{{ __('home.formalites_cheque_title') }}</h4>
                        <p class="text-gray-700 leading-relaxed mb-3">{{ __('home.formalites_cheque_body') }}</p>
                        <p class="text-sm text-gray-700 leading-relaxed bg-slate-50 border-l-4 border-navy pl-4 py-3 rounded-r-lg">
                            {{ __('home.formalites_cheque_nb') }}
                        </p>
                    </article>

                    <article class="home-card p-6 md:p-8 shadow-[0_8px_28px_rgba(23,48,82,0.06)]">
                        <h3 class="text-xl font-bold text-navy mb-3">{{ __('home.incompat_title') }}</h3>
                        <p class="text-gray-700 leading-relaxed">{{ __('home.incompat_body') }}</p>
                    </article>

                    <article class="bg-white rounded-2xl border border-red-100 p-6 md:p-8 shadow-[0_8px_28px_rgba(23,48,82,0.06)]">
                        <div class="flex items-start gap-4 mb-3">
                            <span class="w-11 h-11 rounded-lg bg-red-600 text-white flex items-center justify-center shrink-0">
                                <i class="fas fa-heart-crack"></i>
                            </span>
                            <h3 class="text-xl font-bold text-navy">{{ __('home.deces_title') }}</h3>
                        </div>
                        <p class="text-gray-700 leading-relaxed">{{ __('home.deces_body') }}</p>
                    </article>
                </div>
            </div>

            <div x-show="tab === 'proximite'" x-cloak role="tabpanel">
                <article class="home-card p-6 md:p-8 shadow-[0_8px_28px_rgba(23,48,82,0.06)]">
                    <h3 class="text-xl md:text-2xl font-bold text-navy">{{ __('home.dd_services_title') }}</h3>
                    <p class="text-gray-700 leading-relaxed mt-3 mb-2">{{ __('home.dd_services_intro') }}</p>
                    <p class="text-gray-700 leading-relaxed mb-4">{{ __('home.dd_services_body') }}</p>
                    <p class="font-bold text-navy bg-orange-50 border-l-4 border-orange-500 pl-4 py-3 rounded-r-lg mb-6">
                        {{ __('home.dd_services_cta') }}
                    </p>
                    <h4 class="text-lg font-bold text-navy mb-4">{{ __('home.dd_list_title') }}</h4>
                    <ul class="grid sm:grid-cols-2 gap-3">
                        @foreach($homeDirections as $direction)
                            <li>
                                <a href="{{ route('directions.show', $direction) }}"
                                   class="flex items-start gap-3 bg-slate-50 hover:bg-orange-50 rounded-xl px-4 py-3 border border-transparent hover:border-orange-200 transition-colors">
                                    <i class="fas fa-location-dot text-orange-500 mt-1 text-sm shrink-0"></i>
                                    <span>
                                        <span class="block text-sm font-semibold text-navy">{{ $direction->nom }}</span>
                                        <span class="block text-xs text-gray-500 mt-0.5">{{ $direction->ville }}</span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </article>
            </div>

            <div x-show="tab === 'pratique'" x-cloak role="tabpanel">
                <div class="grid lg:grid-cols-2 gap-4">
                    <article class="home-card p-6 md:p-8 shadow-[0_8px_28px_rgba(23,48,82,0.06)]">
                        <h3 class="text-xl font-bold text-navy mb-3">{{ __('home.gratuite_title') }}</h3>
                        <p class="text-gray-700 leading-relaxed mb-3">{{ __('home.gratuite_intro') }}</p>
                        <p class="text-sm font-semibold text-navy mb-3">{{ __('home.gratuite_none') }}</p>
                        <x-home-checks :items="['home.gratuite_1', 'home.gratuite_2', 'home.gratuite_3', 'home.gratuite_4', 'home.gratuite_5']" :columns="false" />
                    </article>
                    <article class="home-card p-6 md:p-8 shadow-[0_8px_28px_rgba(23,48,82,0.06)]">
                        <h3 class="text-xl font-bold text-navy mb-5">{{ __('home.tel_title') }}</h3>
                        <ul class="space-y-3">
                            @foreach($dpcPhones as $phone)
                                <li class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 sm:gap-4 bg-slate-50 rounded-lg px-4 py-3">
                                    <span class="text-sm text-gray-700">{{ __($phone['label']) }}</span>
                                    <a href="tel:{{ $phone['tel'] }}" class="font-semibold text-navy hover:text-orange-500 whitespace-nowrap">
                                        {{ $phone['display'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </article>
                </div>
            </div>
        </div>
    </div>
</section>
</div>
