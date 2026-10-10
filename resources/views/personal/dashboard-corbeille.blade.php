<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $type }}
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        <nav class="flex flex-wrap items-center gap-2 text-sm text-gray-500" aria-label="Fil d'Ariane">
            <a href="{{ route('personal.cart') }}" class="inline-flex items-center gap-1.5 text-[#173052] hover:text-orange-600 font-medium transition-colors">
                <i class="fas fa-inbox text-xs" aria-hidden="true"></i>
                Corbeille
            </a>
            <i class="fas fa-chevron-right text-[10px] text-gray-300" aria-hidden="true"></i>
            <span class="text-gray-800 font-semibold">{{ $type }}</span>
        </nav>

        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900">{{ $type }}</h1>
                <p class="text-sm text-gray-600 mt-1">
                    Dossiers de votre service — ouvrez une ligne pour traiter la demande.
                </p>
            </div>
            @if($requests->total() > 0)
                <p class="text-sm text-gray-500 tabular-nums">
                    <span class="font-semibold text-gray-800">{{ $requests->total() }}</span> dossier(s)
                </p>
            @endif
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Dossier</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Étape</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Soumis le</th>
                            @if($folder === 'rencontre')
                                <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Rendez-vous</th>
                            @endif
                            <th class="px-5 py-3.5 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($requests as $req)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-5 py-4 font-semibold text-gray-900 max-w-[14rem]">
                                    <span class="line-clamp-2">{{ $req->libellePublic() }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    @if($req->currentStep)
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full {{ $req->statutBadgeClass() }}">
                                                {{ $req->statutAffiche() }}
                                            </span>
                                            @if($req->is_urgent)
                                                <span class="inline-flex px-2 py-0.5 text-[10px] font-bold uppercase rounded-full bg-red-100 text-red-700 ring-1 ring-red-200">
                                                    Urgent
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-gray-600 whitespace-nowrap tabular-nums">
                                    {{ $req->submitted_at?->format('d/m/Y H:i') ?? $req->created_at->format('d/m/Y H:i') }}
                                </td>
                                @if($folder === 'rencontre')
                                    @php
                                        $dateRdv = data_get($req->data, 'date_souhaitee');
                                        $heureRdv = data_get($req->data, 'heure_souhaitee');
                                    @endphp
                                    <td class="px-5 py-4 text-gray-700 whitespace-nowrap">
                                        @if($dateRdv)
                                            <span class="font-medium text-gray-900">
                                                {{ \Carbon\Carbon::parse($dateRdv)->locale('fr')->isoFormat('D MMM YYYY') }}
                                            </span>
                                            @if($heureRdv)
                                                <span class="text-gray-500">· {{ $heureRdv }}</span>
                                            @endif
                                        @else
                                            <span class="text-xs text-gray-400">Non planifié</span>
                                        @endif
                                    </td>
                                @endif
                                <td class="px-5 py-4 text-right whitespace-nowrap">
                                    <a href="{{ route('personal.request.show', $req->id) }}"
                                       class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-[#173052] hover:border-[#173052] hover:bg-[#173052] hover:text-white transition-colors">
                                        {{ ($folder ?? null) === 'clotures' ? 'Consulter' : 'Traiter' }}
                                        <i class="fas fa-arrow-right text-[10px]" aria-hidden="true"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $folder === 'rencontre' ? 6 : 5 }}" class="px-5 py-16 text-center">
                                    <div class="mx-auto max-w-sm">
                                        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-gray-400">
                                            <i class="fas fa-folder-open text-xl" aria-hidden="true"></i>
                                        </span>
                                        <h3 class="mt-4 text-base font-semibold text-gray-900">Répertoire vide</h3>
                                        <p class="mt-2 text-sm text-gray-500">Aucun dossier dans cette catégorie pour le moment.</p>
                                        <a href="{{ route('personal.cart') }}"
                                           class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-[#173052] hover:text-orange-600">
                                            <i class="fas fa-arrow-left text-xs" aria-hidden="true"></i>
                                            Retour à la corbeille
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($requests->hasPages())
                <div class="border-t border-gray-100 px-5 py-4 bg-gray-50/50">
                    {{ $requests->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
