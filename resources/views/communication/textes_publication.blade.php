@extends('layouts.main')

@section('title', 'Textes & Publications légales')

@section('content')
<style>
    .document-card { transition: all 0.3s ease; position: relative; overflow: hidden; }
    .document-card::before { content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: linear-gradient(to bottom, #3b82f6, #1e40af); transform: scaleY(0); transition: transform 0.3s ease; }
    .document-card:hover::before { transform: scaleY(1); }
    .document-card:hover { transform: translateY(-4px); box-shadow: 0 15px 30px -10px rgba(0,0,0,.15); }
    .gradient-text { background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .card-shadow { box-shadow: 0 10px 25px -5px rgba(0,0,0,.1); }
    .fade-in { animation: fadeIn 0.6s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 fade-in">

    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold gradient-text mb-3">Textes & Publications légales</h1>
        <p class="text-gray-600 text-lg max-w-2xl mx-auto">
            Lois, décrets, circulaires et documents officiels publiés par la Direction de la Pension Civile.
        </p>
    </div>

    {{-- Search / filter bar --}}
    <form method="GET" action="{{ route('textes_documents_legaux') }}"
          class="bg-white rounded-xl border border-gray-200 card-shadow p-4 mb-8 flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
                <i class="fas fa-search text-sm"></i>
            </span>
            <input type="text" name="q" value="{{ request('q') }}"
                   placeholder="Rechercher un document…"
                   class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <select name="type" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 min-w-[160px]">
            <option value="">Tous les types</option>
            @foreach($types as $key => $label)
                <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit"
                class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg whitespace-nowrap">
            Rechercher
        </button>
        @if(request('q') || request('type'))
            <a href="{{ route('textes_documents_legaux') }}"
               class="px-4 py-2 border border-gray-300 text-gray-600 hover:bg-gray-50 text-sm rounded-lg whitespace-nowrap flex items-center gap-1.5">
                <i class="fas fa-times text-xs"></i> Effacer
            </a>
        @endif
    </form>

    @if($publications->isEmpty())
        <div class="bg-white rounded-2xl card-shadow p-16 text-center text-gray-400">
            <i class="fas fa-file-alt text-5xl mb-4 block"></i>
            <p>Aucune publication trouvée{{ request('q') || request('type') ? ' pour ces critères' : '' }}.</p>
        </div>
    @else
        @php
            $typeIcons = [
                'loi'        => ['fa-gavel',         'bg-red-100 text-red-700'],
                'decret'     => ['fa-scroll',        'bg-orange-100 text-orange-700'],
                'circulaire' => ['fa-envelope-open', 'bg-blue-100 text-blue-700'],
                'document'   => ['fa-file-alt',      'bg-green-100 text-green-700'],
                'texte'      => ['fa-file-contract', 'bg-purple-100 text-purple-700'],
                'autre'      => ['fa-paperclip',     'bg-gray-100 text-gray-700'],
            ];
        @endphp

        @foreach($publications as $type => $items)
            <div class="mb-10">
                @php [$icon, $iconClass] = $typeIcons[$type] ?? ['fa-file', 'bg-gray-100 text-gray-700']; @endphp
                <h2 class="text-2xl font-bold text-gray-800 mb-5 flex items-center gap-3">
                    <span class="w-10 h-10 {{ $iconClass }} rounded-lg flex items-center justify-center">
                        <i class="fas {{ $icon }}"></i>
                    </span>
                    {{ \App\Models\Publication::$types[$type] ?? $type }}
                    <span class="text-sm font-normal text-gray-400">({{ count($items) }})</span>
                </h2>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($items as $pub)
                        <div class="document-card bg-white border border-gray-200 rounded-xl p-5 card-shadow flex flex-col">
                            <div class="flex items-start gap-3 mb-3">
                                <div class="w-11 h-11 {{ $iconClass }} rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas {{ $icon }}"></i>
                                </div>
                                <h3 class="text-base font-semibold text-gray-800 leading-snug">{{ $pub->title }}</h3>
                            </div>

                            @if($pub->description)
                                <p class="text-gray-500 text-sm leading-relaxed mb-4">{{ Str::limit($pub->description, 120) }}</p>
                            @endif

                            <div class="flex items-center gap-2 mt-auto flex-wrap">
                                @if($pub->file_path)
                                    {{-- View inline --}}
                                    <a href="{{ $pub->fileUrl() }}" target="_blank"
                                       class="inline-flex items-center gap-1.5 text-sm px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg font-medium">
                                        <i class="fas fa-eye text-xs"></i> Voir
                                    </a>
                                    {{-- Force download --}}
                                    <a href="{{ route('publications.download', $pub) }}"
                                       class="inline-flex items-center gap-1.5 text-sm px-3 py-1.5 bg-green-50 text-green-700 hover:bg-green-100 rounded-lg font-medium">
                                        <i class="fas fa-download text-xs"></i> Télécharger
                                    </a>
                                @elseif($pub->url)
                                    <a href="{{ $pub->url }}" target="_blank"
                                       class="inline-flex items-center gap-1.5 text-sm px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg font-medium">
                                        <i class="fas fa-external-link-alt text-xs"></i> Consulter
                                    </a>
                                @else
                                    <span class="text-xs text-gray-400 italic">Fichier non disponible</span>
                                @endif

                                <span class="text-xs text-gray-400 ml-auto">{{ $pub->created_at->format('d/m/Y') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
