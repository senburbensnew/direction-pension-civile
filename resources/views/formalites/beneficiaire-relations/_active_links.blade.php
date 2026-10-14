@php
    $revokeRoute = $revokeRoute ?? 'formalites.beneficiaire-relations.revoke-link';
@endphp

@if(isset($activeLinks) && $activeLinks->isNotEmpty())
    <section class="bg-white border rounded-xl shadow-sm p-5 space-y-4">
        <div>
            <h3 class="font-semibold text-gray-900">Liens représentant actifs</h3>
            <p class="text-sm text-gray-500 mt-1">Révoquer un lien coupe immédiatement l’accès au dossier pour le représentant.</p>
        </div>
        @foreach($activeLinks as $link)
            <article class="flex flex-wrap justify-between gap-3 border-t first:border-t-0 pt-4 first:pt-0">
                <div>
                    <p class="font-medium text-gray-900">{{ $link->beneficiaire?->displayName() }}</p>
                    <p class="text-sm text-gray-600">
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
                        <button type="submit" class="btn btn-sm btn-outline btn-error">Révoquer le lien</button>
                    </form>
                @endcan
            </article>
        @endforeach
    </section>
@endif
