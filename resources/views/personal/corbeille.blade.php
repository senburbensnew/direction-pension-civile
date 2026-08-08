<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Suivi des demandes') }}
        </h2>
    </x-slot>

    <div class="px-5 py-8">
        @if (session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl">
                {{ session('error') }}
            </div>
        @endif

        {{-- ===================== RÉCEPTIONS EN ATTENTE (circuit) ===================== --}}
        @if(isset($pendingReceptions) && $pendingReceptions->isNotEmpty())
            <div class="bg-white rounded-2xl border-2 border-amber-300 shadow-sm overflow-hidden mb-6">
                <div class="bg-amber-500 px-5 py-3 flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <span class="text-white font-bold text-sm tracking-wide">RÉCEPTIONS EN ATTENTE</span>
                        <span class="bg-white/30 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $pendingReceptions->count() }}</span>
                    </div>
                    <p class="text-amber-100 text-xs">Confirmez la réception pour débloquer le traitement selon le circuit.</p>
                </div>

                <div class="divide-y divide-gray-100">
                    @foreach($pendingReceptions as $wf)
                        <div class="px-5 py-4 flex flex-wrap items-center justify-between gap-4 hover:bg-amber-50 transition-colors">
                            <div class="flex items-start gap-3">
                                <div class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-800 font-mono text-sm">#{{ $wf->demande->code ?? $wf->demande_id }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ str_replace('_', ' ', $wf->demande->type ?? '—') }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        Depuis <span class="font-medium text-gray-600">{{ $wf->fromService?->nom ?? '—' }}</span>
                                        <span class="text-gray-300 mx-1">·</span>
                                        {{ $wf->created_at?->diffForHumans() }}
                                    </p>
                                    @if($wf->demande?->currentStep)
                                        <p class="text-xs text-amber-700 mt-1">
                                            Étape : {{ $wf->demande->currentStep->nom }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <a href="{{ route('personal.request.show', $wf->demande_id) }}"
                               class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-xl transition-all shadow-sm hover:shadow">
                                Confirmer la réception
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ===================== AVIS EN ATTENTE ===================== --}}
        @if(isset($pendingAffectations) && $pendingAffectations->isNotEmpty())
            <div class="bg-white rounded-2xl border-2 border-orange-300 shadow-sm overflow-hidden mb-6">
                <div class="bg-orange-400 px-5 py-3 flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        <span class="text-white font-bold text-sm tracking-wide">AVIS EN ATTENTE</span>
                        <span class="bg-white/30 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $pendingAffectations->count() }}</span>
                    </div>
                    <p class="text-orange-100 text-xs">Dossiers affectés pour consultation — votre avis est requis.</p>
                </div>

                <div class="divide-y divide-gray-100">
                    @foreach($pendingAffectations as $aff)
                        <div class="px-5 py-4 flex flex-wrap items-center justify-between gap-4 hover:bg-orange-50 transition-colors">
                            <div class="flex items-start gap-3">
                                <div class="w-9 h-9 rounded-xl bg-orange-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-800 font-mono text-sm">#{{ $aff->demande->code ?? $aff->demande_id }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ str_replace('_', ' ', $aff->demande->type ?? '—') }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        Affecté {{ $aff->created_at?->diffForHumans() }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                @if($aff->demande?->currentStep)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium ring-1
                                        {{ \App\Models\WorkflowStep::getStatusStyle($aff->demande->currentStep->code) }}">
                                        {{ $aff->demande->currentStep->nom }}
                                    </span>
                                @endif

                                <a href="{{ route('personal.request.show', $aff->demande_id) }}"
                                   class="inline-flex items-center gap-2 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold rounded-xl transition-all shadow-sm hover:shadow">
                                    Donner mon avis
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ===================== RÉPERTOIRE DE DOSSIERS ===================== --}}
        <fieldset class="border-2 border-blue-200 rounded-lg mb-6 pl-3 ml-1 mr-1">
            <legend class="text-lg font-semibold ml-4 px-4 text-blue-700 bg-white rounded-full shadow-sm">
                <i class="fas fa-folder-open mr-2 text-blue-400"></i> Répertoire de dossiers
            </legend>
            <div class="py-6 px-5">
                <p class="text-xs text-gray-500 mb-4">
                    Dossiers actuellement dans <strong>votre service</strong>, classés par catégorie. Ouvrez un dossier pour le faire avancer selon le circuit défini.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-7 gap-4">
                    @foreach($folderStats as $folder)
                        @php
                            $colorMap = [
                                'blue'   => ['bg' => 'bg-blue-50',   'border' => 'border-blue-200',   'text' => 'text-blue-700',   'badge' => 'bg-blue-600'],
                                'red'    => ['bg' => 'bg-red-50',    'border' => 'border-red-200',    'text' => 'text-red-700',    'badge' => 'bg-red-600'],
                                'yellow' => ['bg' => 'bg-yellow-50', 'border' => 'border-yellow-200', 'text' => 'text-yellow-700', 'badge' => 'bg-yellow-500'],
                                'indigo' => ['bg' => 'bg-indigo-50', 'border' => 'border-indigo-200', 'text' => 'text-indigo-700', 'badge' => 'bg-indigo-600'],
                                'purple' => ['bg' => 'bg-purple-50', 'border' => 'border-purple-200', 'text' => 'text-purple-700', 'badge' => 'bg-purple-600'],
                                'green'  => ['bg' => 'bg-green-50',  'border' => 'border-green-200',  'text' => 'text-green-700',  'badge' => 'bg-green-600'],
                                'gray'   => ['bg' => 'bg-gray-50',   'border' => 'border-gray-300',   'text' => 'text-gray-700',   'badge' => 'bg-gray-500'],
                                'teal'   => ['bg' => 'bg-teal-50',   'border' => 'border-teal-200',   'text' => 'text-teal-700',   'badge' => 'bg-teal-600'],
                            ];
                            $c = $colorMap[$folder['color']];
                        @endphp
                        @if($folder['count'] > 0)
                            <a href="{{ route('personal.cart.folder', ['folder' => $folder['key']]) }}"
                               class="{{ $c['bg'] }} border {{ $c['border'] }} p-4 rounded-lg hover:shadow-md transition-all group block">
                        @else
                            <div class="bg-gray-50 border border-gray-200 p-4 rounded-lg opacity-60">
                        @endif
                            <div class="flex items-center justify-between mb-2">
                                <i class="fas {{ $folder['icon'] }} {{ $folder['count'] > 0 ? $c['text'] : 'text-gray-400' }}"></i>
                                @if($folder['count'] > 0)
                                    <span class="text-white text-xs font-bold px-2 py-0.5 rounded-full {{ $c['badge'] }}">
                                        {{ $folder['count'] }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs">0</span>
                                @endif
                            </div>
                            <p class="text-sm font-medium {{ $folder['count'] > 0 ? $c['text'] : 'text-gray-500' }}">
                                {{ $folder['label'] }}
                            </p>
                            @if($folder['count'] > 0)
                                <p class="text-xs text-blue-500 mt-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    Voir les dossiers →
                                </p>
                            @endif
                        @if($folder['count'] > 0)
                            </a>
                        @else
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </fieldset>
    </div>
</x-app-layout>
