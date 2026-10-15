@php
    $revokeRoute = $revokeRoute ?? 'formalites.beneficiaire-relations.revoke-link';
@endphp

@if(isset($activeLinks) && $activeLinks->isNotEmpty())
    <section class="dossier-card overflow-hidden">
        <div class="dossier-card-head">
            <div>
                <h3>Liens représentant actifs</h3>
                <p class="text-xs font-normal text-gray-500 mt-0.5">Révoquer un lien coupe immédiatement l’accès au dossier pour le représentant.</p>
            </div>
            <span class="text-xs font-medium text-gray-500 tabular-nums">{{ $activeLinks->count() }}</span>
        </div>
        <div class="divide-y divide-gray-100">
            @foreach($activeLinks as $link)
                <article class="px-5 py-4 flex flex-wrap justify-between gap-3 items-start">
                    <div class="min-w-0">
                        <p class="font-semibold text-[#173052]">{{ $link->beneficiaire?->displayName() }}</p>
                        <p class="text-sm text-gray-600 mt-0.5">
                            {{ $link->user?->displayName() }} · {{ $link->relation?->label() }}
                        </p>
                        @if($link->beneficiaire?->primaryAffiliation()?->code_pension)
                            <p class="text-sm font-mono text-gray-700 mt-1">{{ $link->beneficiaire->primaryAffiliation()->code_pension }}</p>
                        @endif
                        <p class="text-xs text-gray-500 mt-1">
                            Actions : {{ implode(' · ', $link->grantedPermissionLabels()) }}
                        </p>
                    </div>
                    @can('beneficiaire_relations.revoquer')
                        <form method="POST" action="{{ route($revokeRoute, $link) }}" onsubmit="return confirm('Révoquer ce lien ?');">
                            @csrf
                            <button type="submit" class="dossier-btn-danger-outline text-xs px-3 py-1.5">Révoquer le lien</button>
                        </form>
                    @endcan
                </article>
            @endforeach
        </div>
    </section>
@endif
