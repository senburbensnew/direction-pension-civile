@extends('layouts.main')

@section('title', 'Textes & Publications légales')

@section('content')
<style>
    .card-shadow { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
    [x-cloak] { display: none !important; }
</style>

<div class="py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-8">

        <div class="text-center">
            <span class="text-xs font-bold text-orange-500 uppercase tracking-widest">Communications</span>
            <h1 class="text-4xl font-bold text-navy mt-2 mb-3">Textes & Publications légales</h1>
            <p class="text-gray-600 max-w-2xl mx-auto">
                Lois, décrets, circulaires et documents officiels publiés par la Direction de la Pension Civile.
            </p>
        </div>

        <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-5">
                <span class="text-3xl text-navy flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                </span>
                <h2 class="text-xl font-bold text-navy">Rechercher un document</h2>
            </div>
            <form method="GET" action="{{ route('textes_documents_legaux') }}"
                  class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-sm"></i>
                    </span>
                    <input type="text" name="q" value="{{ request('q') }}"
                           placeholder="Rechercher un document…"
                           class="w-full pl-9 pr-4 py-2.5 border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                </div>
                <select name="type"
                        class="border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy min-w-[160px]">
                    <option value="">Tous les types</option>
                    @foreach($types as $key => $label)
                        <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit"
                        class="px-5 py-2.5 bg-navy text-white text-sm font-semibold hover:opacity-90 whitespace-nowrap">
                    Rechercher
                </button>
                @if(request('q') || request('type'))
                    <a href="{{ route('textes_documents_legaux') }}"
                       class="px-4 py-2.5 border border-gray-200 text-gray-700 hover:bg-gray-50 text-sm whitespace-nowrap flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-xmark text-xs"></i> Effacer
                    </a>
                @endif
            </form>
        </section>

        @if($publications->isEmpty())
            <section class="bg-white border border-gray-200 card-shadow p-12 text-center text-gray-500">
                <i class="fa-solid fa-file-lines text-3xl text-navy mb-4 block"></i>
                <p>Aucune publication trouvée{{ request('q') || request('type') ? ' pour ces critères' : '' }}.</p>
            </section>
        @else
            @foreach($publications as $type => $items)
                @php
                    [$icon] = $typeVisuals[$type] ?? ['fa-file'];
                    $iconName = str_starts_with($icon, 'fa-') ? $icon : 'fa-file';
                @endphp
                <section class="bg-white border border-gray-200 card-shadow overflow-hidden"
                         x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }">
                    <button type="button"
                            class="w-full flex items-center gap-3 px-6 sm:px-8 py-4 bg-navy text-white text-left"
                            @click="open = !open"
                            :aria-expanded="open.toString()">
                        <span class="text-2xl flex items-center justify-center shrink-0" aria-hidden="true">
                            <i class="fa-solid {{ $iconName }}"></i>
                        </span>
                        <h2 class="text-xl font-bold">{{ $types[$type] ?? $type }}</h2>
                        <span class="ml-auto text-xs font-semibold uppercase tracking-widest bg-white/15 px-2.5 py-1">
                            {{ count($items) }} {{ count($items) > 1 ? 'documents' : 'document' }}
                        </span>
                        <i class="fa-solid fa-chevron-down text-sm transition-transform duration-200"
                           :class="{ 'rotate-180': open }" aria-hidden="true"></i>
                    </button>
                    <div x-show="open" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="p-6 sm:p-8 grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($items as $pub)
                            <article class="border border-gray-200 p-5 flex flex-col bg-white">
                                <div class="flex items-start gap-3 mb-3">
                                    <span class="text-xl text-navy mt-0.5 shrink-0">
                                        <i class="fa-solid {{ str_starts_with($icon, 'fa-') ? $icon : 'fa-file' }}" aria-hidden="true"></i>
                                    </span>
                                    <h3 class="text-lg font-bold text-navy leading-snug">{{ $pub->title }}</h3>
                                </div>
                                @if($pub->description)
                                    <p class="text-gray-700 text-sm leading-relaxed mb-4">{{ Str::limit($pub->description, 120) }}</p>
                                @endif
                                <div class="flex items-center gap-2 mt-auto flex-wrap">
                                    @if($pub->file_path)
                                        <a href="{{ $pub->fileUrl() }}" target="_blank"
                                           class="inline-flex items-center gap-1.5 text-sm px-3 py-1.5 bg-orange-500 hover:bg-orange-600 text-white font-medium transition">
                                            <i class="fa-solid fa-eye text-xs"></i> Voir
                                        </a>
                                        <a href="{{ route('publications.download', $pub) }}"
                                           class="inline-flex items-center gap-1.5 text-sm px-3 py-1.5 border border-gray-200 text-navy font-medium hover:bg-gray-50">
                                            <i class="fa-solid fa-download text-xs"></i> Télécharger
                                        </a>
                                    @elseif($pub->url)
                                        <a href="{{ $pub->url }}" target="_blank"
                                           class="inline-flex items-center gap-1.5 text-sm px-3 py-1.5 bg-navy text-white font-medium hover:opacity-90">
                                            <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i> Consulter
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Fichier non disponible</span>
                                    @endif
                                    <span class="text-xs text-gray-400 ml-auto">{{ $pub->created_at->format('d/m/Y') }}</span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach
        @endif

    </div>
</div>
@endsection
