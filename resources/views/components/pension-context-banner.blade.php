@auth
    @php
        $user = auth()->user();
        $context = $user->pensionContext();
        $show = $user->canAccessFullServices() && $context->needsContextSwitcher();
        $beneficiaires = $show
            ? $context->accessibleBeneficiaires()->load('affiliations')
            : collect();
        $activeBeneficiaire = $context->resolveBeneficiaire();
        $activeAffiliation = $context->resolveAffiliation();
        $payload = $beneficiaires->map(fn ($b) => [
            'id' => $b->id,
            'name' => $b->displayName(),
            'is_mineur' => (bool) $b->is_mineur,
            'affiliations' => $b->affiliations->map(fn ($a) => [
                'id' => $a->id,
                'code' => $a->code_pension,
                'description' => $a->loc_description,
            ])->values(),
        ])->values();
    @endphp

    @if($show)
        <div class="space-y-3 text-left">
            <div class="rounded-lg border border-sky-100 bg-sky-50/70 px-4 py-3">
                <p class="text-sm font-semibold text-navy">
                    {{ __('messages.pension_context_title') }}
                </p>
                <p class="mt-1.5 text-xs text-gray-700 leading-relaxed max-w-4xl">
                    {{ __('messages.pension_context_help') }}
                </p>
            </div>

        <div
            class="dossier-form-bar text-sm"
            x-data="{
                beneficiaires: @js($payload),
                beneficiaireId: @js($activeBeneficiaire?->id),
                affiliationId: @js($activeAffiliation?->id),
                displayCode: @js($activeAffiliation?->code_pension),
                loading: false,
                applyFeedback: null,
                applyFeedbackError: false,
                messages: {
                    applied: @js(__('messages.pension_context_applied')),
                    failed: @js(__('messages.pension_context_apply_failed')),
                },
                get affiliations() {
                    const b = this.beneficiaires.find(x => x.id === this.beneficiaireId);
                    return b ? b.affiliations : [];
                },
                onBeneficiaireChange() {
                    const first = this.affiliations[0];
                    this.affiliationId = first ? first.id : null;
                    this.applyFeedback = null;
                },
                async applyContext() {
                    this.loading = true;
                    this.applyFeedback = null;
                    this.applyFeedbackError = false;
                    try {
                        const res = await fetch(@js(route('compte.pension-context.update')), {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
                            },
                            body: JSON.stringify({
                                beneficiaire_id: this.beneficiaireId,
                                affiliation_id: this.affiliationId,
                            }),
                        });
                        let data = {};
                        try {
                            data = await res.json();
                        } catch (e) {
                            data = {};
                        }
                        if (res.ok && data.display_code) {
                            this.displayCode = data.display_code;
                            this.applyFeedback = this.messages.applied;
                            document.querySelectorAll('[data-active-pension-code]').forEach((el) => {
                                if (!el.dataset.userEdited) {
                                    el.value = this.displayCode ?? '';
                                }
                            });
                            return;
                        }
                        this.applyFeedbackError = true;
                        this.applyFeedback = data.message ?? this.messages.failed;
                    } finally {
                        this.loading = false;
                    }
                },
            }"
        >
            <div class="flex flex-wrap items-end gap-3 flex-1 min-w-0">
                <div class="min-w-[12rem] flex-1 sm:flex-none">
                    <label class="block text-xs font-medium uppercase tracking-wide text-gray-500 mb-1">Bénéficiaire</label>
                    <select
                        x-model.number="beneficiaireId"
                        @change="onBeneficiaireChange()"
                        class="dossier-field min-w-[12rem]"
                    >
                        <template x-for="b in beneficiaires" :key="b.id">
                            <option :value="b.id" x-text="b.name + (b.is_mineur ? ' (mineur)' : '')"></option>
                        </template>
                    </select>
                </div>
                <div class="min-w-[10rem] flex-1 sm:flex-none">
                    <label class="block text-xs font-medium uppercase tracking-wide text-gray-500 mb-1">Code pension</label>
                    <select x-model.number="affiliationId" class="dossier-field min-w-[10rem]">
                        <template x-for="a in affiliations" :key="a.id">
                            <option
                                :value="a.id"
                                x-text="a.description ? (a.code + ' — ' + a.description) : a.code"
                            ></option>
                        </template>
                    </select>
                </div>
                <button
                    type="button"
                    @click="applyContext()"
                    :disabled="loading"
                    class="px-5 py-2.5 bg-navy hover:bg-orange-500 disabled:opacity-60 text-white text-sm font-semibold transition-colors shrink-0"
                >
                    <span x-show="!loading">Appliquer</span>
                    <span x-show="loading" x-cloak>…</span>
                </button>
                <p
                    x-show="applyFeedback"
                    x-cloak
                    class="w-full text-xs sm:w-auto sm:max-w-xs"
                    :class="applyFeedbackError ? 'text-red-700' : 'text-green-700'"
                    x-text="applyFeedback"
                    role="status"
                ></p>
            </div>

            <div class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-2 sm:gap-4 w-full lg:w-auto lg:ml-auto">
                <p class="text-gray-700 whitespace-nowrap">
                    {{ __('messages.pension_context_active') }}
                    <span class="font-mono font-semibold text-[#173052]" x-text="displayCode ?? '—'"></span>
                </p>
                <div class="flex flex-wrap gap-x-4 gap-y-1">
                    @if($user->canSeePortalNavBeneficiairesMandats())
                        <a href="{{ route('compte.beneficiaires.index') }}" class="dossier-link">
                            Mes bénéficiaires →
                        </a>
                    @endif
                    @if($user->hasCategory('pensionne') || $user->hasRole('pensionne'))
                        <a href="{{ route('compte.affiliations.index') }}" class="dossier-link">
                            Gérer mes codes →
                        </a>
                    @endif
                </div>
            </div>
        </div>
        </div>
    @endif
@endauth
