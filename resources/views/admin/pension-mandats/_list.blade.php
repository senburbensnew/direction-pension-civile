@php
    $approveRoute = $approveRoute ?? 'admin.pension-mandats.approve';
    $refuseRoute = $refuseRoute ?? 'admin.pension-mandats.refuse';
    $revokeRoute = $revokeRoute ?? 'admin.pension-mandats.revoke';
    $pieceRoute = $pieceRoute ?? 'admin.pension-mandats.piece';
@endphp

@foreach($mandats as $mandat)
    <article class="bg-white border rounded-xl shadow-sm p-5">
        <div class="flex flex-wrap justify-between gap-2 mb-3">
            <div>
                <p class="font-semibold text-gray-900">{{ $mandat->beneficiaire?->displayName() }}</p>
                <p class="text-sm text-gray-600">Mandataire : {{ $mandat->mandataire?->displayName() }} · {{ $mandat->type }}</p>
                @if($mandat->getFirstMedia('mandat_document'))
                    <a href="{{ route($pieceRoute, ['mandat' => $mandat, 'media' => $mandat->getFirstMedia('mandat_document')]) }}?inline=1"
                       class="text-sm text-indigo-700 hover:underline" target="_blank" rel="noopener">
                        Voir le document de mandat
                    </a>
                @endif
            </div>
            <span class="text-sm font-medium">{{ $mandat->status?->label() }}</span>
        </div>

        @if($mandat->status === \App\Enums\PensionMandatStatusEnum::EN_ATTENTE)
            <form method="POST" action="{{ route($approveRoute, $mandat) }}" class="space-y-4 border-t pt-4">
                @csrf
                <div>
                    <p class="text-sm font-semibold text-gray-800 mb-2">Types de demandes autorisés</p>
                    <p class="text-xs text-gray-500 mb-2">Laisser vide = tous les types ci-dessous.</p>
                    <div class="grid sm:grid-cols-2 gap-2">
                        @foreach($demandeTypeChoices as $type)
                            <label class="inline-flex items-center gap-2 text-sm">
                                <input type="checkbox" name="allowed_demande_types[]" value="{{ $type->value }}" class="rounded border-gray-300">
                                {{ $type->label() }}
                            </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-800 mb-2">Codes pension autorisés</p>
                    <p class="text-xs text-gray-500 mb-2">Laisser vide = tous les codes du bénéficiaire.</p>
                    <div class="flex flex-wrap gap-3">
                        @forelse($mandat->beneficiaire?->affiliations ?? [] as $affiliation)
                            <label class="inline-flex items-center gap-2 text-sm font-mono">
                                <input type="checkbox" name="allowed_pension_codes[]" value="{{ $affiliation->code_pension }}" class="rounded border-gray-300">
                                {{ $affiliation->code_pension }}
                            </label>
                        @empty
                            <span class="text-sm text-gray-500">Aucune affiliation enregistrée.</span>
                        @endforelse
                    </div>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="btn btn-sm btn-primary">Approuver le mandat</button>
                </div>
            </form>
            <form method="POST" action="{{ route($refuseRoute, $mandat) }}" class="mt-3 flex flex-wrap gap-2 items-end border-t pt-3">
                @csrf
                <div class="flex-1 min-w-[12rem]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Motif de refus</label>
                    <input type="text" name="refusal_reason" required minlength="5" class="w-full rounded-md border-gray-300 text-sm">
                </div>
                <button type="submit" class="btn btn-sm btn-outline btn-error">Refuser</button>
            </form>
        @elseif($mandat->status === \App\Enums\PensionMandatStatusEnum::ACTIF)
            <div class="text-sm text-gray-600 border-t pt-3">
                @if($mandat->allowed_demande_types)
                    <p>Types : {{ implode(', ', $mandat->allowed_demande_types) }}</p>
                @endif
                @if($mandat->allowed_pension_codes)
                    <p>Codes : {{ implode(', ', $mandat->allowed_pension_codes) }}</p>
                @endif
            </div>
            <form method="POST" action="{{ route($revokeRoute, $mandat) }}" class="mt-3">
                @csrf
                <button type="submit" class="text-amber-800 text-sm font-medium hover:underline">Révoquer</button>
            </form>
        @endif
    </article>
@endforeach

<div>{{ $mandats->links() }}</div>
