<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Suivi des demandes') }}
        </h2>
    </x-slot>

    <div class="px-5 py-8">
        @if (session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        {{-- ===================== RÉPERTOIRE DE DOSSIERS ===================== --}}
        <fieldset class="border-2 border-blue-200 rounded-lg mb-6 pl-3 ml-1 mr-1">
            <legend class="text-lg font-semibold ml-4 px-4 text-blue-700 bg-white rounded-full shadow-sm">
                <i class="fas fa-folder-open mr-2 text-blue-400"></i> Répertoire de dossiers
            </legend>
            <div class="py-6 px-5">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                    @foreach($folderStats as $folder)
                        @php
                            $colorMap = [
                                'blue'   => ['bg' => 'bg-blue-50',   'border' => 'border-blue-200',   'text' => 'text-blue-700',   'badge' => 'bg-blue-600'],
                                'red'    => ['bg' => 'bg-red-50',    'border' => 'border-red-200',    'text' => 'text-red-700',    'badge' => 'bg-red-600'],
                                'yellow' => ['bg' => 'bg-yellow-50', 'border' => 'border-yellow-200', 'text' => 'text-yellow-700', 'badge' => 'bg-yellow-500'],
                                'indigo' => ['bg' => 'bg-indigo-50', 'border' => 'border-indigo-200', 'text' => 'text-indigo-700', 'badge' => 'bg-indigo-600'],
                                'purple' => ['bg' => 'bg-purple-50', 'border' => 'border-purple-200', 'text' => 'text-purple-700', 'badge' => 'bg-purple-600'],
                                'green'  => ['bg' => 'bg-green-50',  'border' => 'border-green-200',  'text' => 'text-green-700',  'badge' => 'bg-green-600'],
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
