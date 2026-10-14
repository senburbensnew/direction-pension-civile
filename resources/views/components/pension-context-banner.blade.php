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
            ])->values(),
        ])->values();
    @endphp

    @if($show)
        <div
            class="bg-sky-50 border-b border-sky-200 px-4 py-3"
            x-data="{
                beneficiaires: @js($payload),
                beneficiaireId: @js($activeBeneficiaire?->id),
                affiliationId: @js($activeAffiliation?->id),
                displayCode: @js($activeAffiliation?->code_pension),
                loading: false,
                get affiliations() {
                    const b = this.beneficiaires.find(x => x.id === this.beneficiaireId);
                    return b ? b.affiliations : [];
                },
                onBeneficiaireChange() {
                    const first = this.affiliations[0];
                    this.affiliationId = first ? first.id : null;
                    this.displayCode = first ? first.code : null;
                },
                async applyContext() {
                    this.loading = true;
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
                        const data = await res.json();
                        if (data.display_code) {
                            this.displayCode = data.display_code;
                        }
                        document.querySelectorAll('[data-active-pension-code]').forEach((el) => {
                            if (!el.dataset.userEdited) {
                                el.value = this.displayCode ?? '';
                            }
                        });
                    } finally {
                        this.loading = false;
                    }
                }
            }"
        >
            <div class="container mx-auto flex flex-wrap items-end gap-3 text-sm">
                <div>
                    <label class="block text-xs font-semibold text-sky-900 uppercase mb-1">Bénéficiaire</label>
                    <select x-model.number="beneficiaireId" @change="onBeneficiaireChange()" class="rounded-md border-sky-200 text-sm min-w-[12rem]">
                        <template x-for="b in beneficiaires" :key="b.id">
                            <option :value="b.id" x-text="b.name + (b.is_mineur ? ' (mineur)' : '')"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-sky-900 uppercase mb-1">Code pension</label>
                    <select x-model.number="affiliationId" class="rounded-md border-sky-200 text-sm min-w-[10rem]">
                        <template x-for="a in affiliations" :key="a.id">
                            <option :value="a.id" x-text="a.code"></option>
                        </template>
                    </select>
                </div>
                <button type="button" @click="applyContext()" :disabled="loading" class="btn btn-sm btn-primary">
                    <span x-show="!loading">Appliquer</span>
                    <span x-show="loading">…</span>
                </button>
                <p class="text-sky-900 ml-auto">
                    Contexte actif : <span class="font-mono font-semibold" x-text="displayCode ?? '—'"></span>
                </p>
                <div class="flex flex-wrap gap-4 w-full sm:w-auto sm:ml-auto justify-end text-right">
                    <a href="{{ route('compte.beneficiaires.index') }}" class="text-sky-800 font-medium hover:underline">
                        Mes bénéficiaires →
                    </a>
                    <a href="{{ route('compte.affiliations.index') }}" class="text-sky-800 font-medium hover:underline">
                        Gérer mes codes →
                    </a>
                </div>
            </div>
        </div>
    @endif
@endauth
