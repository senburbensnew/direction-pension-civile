@php
    use App\Enums\BeneficiaireLinkPermissionEnum;

    $approveRoute = $approveRoute ?? 'formalites.beneficiaire-relations.approve';
    $refuseRoute = $refuseRoute ?? 'formalites.beneficiaire-relations.refuse';
    $pieceRoute = $pieceRoute ?? 'formalites.beneficiaire-relations.piece';
    $permissionChoices = BeneficiaireLinkPermissionEnum::cases();
@endphp

@foreach($demandes as $demande)
    <article class="bg-white border rounded-xl shadow-sm p-5">
        <div class="flex flex-wrap justify-between gap-2 mb-3">
            <div>
                <p class="font-semibold text-gray-900">{{ $demande->beneficiaire?->displayName() }}</p>
                <p class="text-sm text-gray-600">
                    Demandeur : {{ $demande->user?->displayName() }} · {{ $demande->type_relation?->label() }}
                </p>
                @if($demande->motif)
                    <p class="text-sm text-gray-500 mt-1">{{ $demande->motif }}</p>
                @endif
                @if($demande->getFirstMedia('justificatif'))
                    <a href="{{ route($pieceRoute, ['demande' => $demande, 'media' => $demande->getFirstMedia('justificatif')]) }}?inline=1"
                       class="text-sm text-indigo-700 hover:underline" target="_blank" rel="noopener">
                        Voir le justificatif
                    </a>
                @endif
            </div>
            <span class="text-sm font-medium">{{ $demande->status?->label() }}</span>
        </div>

        @if($demande->status?->isPending())
            <div class="flex flex-wrap gap-3 border-t pt-4">
                <form method="POST" action="{{ route($approveRoute, $demande) }}" class="space-y-3 flex-1 min-w-[16rem]">
                    @csrf
                    <fieldset class="space-y-2">
                        <legend class="text-xs font-semibold text-gray-700 uppercase">Permissions accordées</legend>
                        @foreach($permissionChoices as $permission)
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="permissions[]" value="{{ $permission->value }}" checked
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                {{ $permission->label() }}
                            </label>
                        @endforeach
                    </fieldset>
                    <button type="submit" class="btn btn-sm btn-primary">Approuver</button>
                </form>
                <form method="POST" action="{{ route($refuseRoute, $demande) }}" class="flex flex-wrap gap-2 items-end flex-1">
                    @csrf
                    <div class="flex-1 min-w-[12rem]">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Motif de refus</label>
                        <input type="text" name="refusal_reason" required minlength="5" class="w-full rounded-md border-gray-300 text-sm">
                    </div>
                    <button type="submit" class="btn btn-sm btn-outline btn-error">Refuser</button>
                </form>
            </div>
        @endif
    </article>
@endforeach

<div>{{ $demandes->links() }}</div>
