<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Suivi de mes demandes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Pensionnaire Section -->
            <div class="mb-12">
                <h3 class="text-lg font-semibold mb-4 text-gray-700">Pensionnaire</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @if ($stats['pending'] > 0)
                        <a href="#"
                            class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer hover:bg-gray-50 group block">
                        @else
                            <div class="bg-white p-4 rounded-lg shadow-sm">
                    @endif
                    <div class="text-gray-500 text-sm">Demande de virement</div>
                    <div class="text-2xl font-bold text-yellow-600 mt-1">{{ $stats['pending'] }}</div>
                    @if ($stats['pending'] > 0)
                        <div class="mt-2 text-sm text-blue-600 opacity-0 group-hover:opacity-100 transition-opacity">
                            Voir la liste →
                        </div>
                    @endif
                    @if ($stats['pending'] > 0)
                        </a>
                    @else
                </div>
                @endif

                @if ($stats['in_progress'] > 0)
                    <a href="#"
                        class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer hover:bg-gray-50 group block">
                    @else
                        <div class="bg-white p-4 rounded-lg shadow-sm">
                @endif
                <div class="text-gray-500 text-sm">Demande d'attestation</div>
                <div class="text-2xl font-bold text-purple-600 mt-1">{{ $stats['in_progress'] }}</div>
                @if ($stats['in_progress'] > 0)
                    <div class="mt-2 text-sm text-blue-600 opacity-0 group-hover:opacity-100 transition-opacity">
                        Voir la liste →
                    </div>
                @endif
                @if ($stats['in_progress'] > 0)
                    </a>
                @else
            </div>
            @endif

            @if ($stats['rejected'] > 0)
                <a href="#"
                    class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer hover:bg-gray-50 group block">
                @else
                    <div class="bg-white p-4 rounded-lg shadow-sm">
            @endif
            <div class="text-gray-500 text-sm">Demande de transfert de cheques</div>
            <div class="text-2xl font-bold text-red-600 mt-1">{{ $stats['rejected'] }}</div>
            @if ($stats['rejected'] > 0)
                <div class="mt-2 text-sm text-blue-600 opacity-0 group-hover:opacity-100 transition-opacity">
                    Voir la liste →
                </div>
            @endif
            @if ($stats['rejected'] > 0)
                </a>
            @else
        </div>
        @endif

        @if ($stats['approved'] > 0)
            <a href="#"
                class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer hover:bg-gray-50 group block">
            @else
                <div class="bg-white p-4 rounded-lg shadow-sm">
        @endif
        <div class="text-gray-500 text-sm">Demande d'arret de paiement</div>
        <div class="text-2xl font-bold text-blue-600 mt-1">{{ $stats['approved'] }}</div>
        @if ($stats['approved'] > 0)
            <div class="mt-2 text-sm text-blue-600 opacity-0 group-hover:opacity-100 transition-opacity">
                Voir la liste →
            </div>
        @endif
        @if ($stats['approved'] > 0)
            </a>
        @else
    </div>
    @endif

    @if ($stats['completed'] > 0)
        <a href="#"
            class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer hover:bg-gray-50 group block">
        @else
            <div class="bg-white p-4 rounded-lg shadow-sm">
    @endif
    <div class="text-gray-500 text-sm">Demande de réinsertion</div>
    <div class="text-2xl font-bold text-green-600 mt-1">{{ $stats['completed'] }}</div>
    @if ($stats['completed'] > 0)
        <div class="mt-2 text-sm text-blue-600 opacity-0 group-hover:opacity-100 transition-opacity">
            Voir la liste →
        </div>
    @endif
    @if ($stats['completed'] > 0)
    </a>@else</div>
    @endif

    @if ($stats['rejected'] > 0)
        <a href="#"
            class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer hover:bg-gray-50 group block">
        @else
            <div class="bg-white p-4 rounded-lg shadow-sm">
    @endif
    <div class="text-gray-500 text-sm">Demande d'arret de virement</div>
    <div class="text-2xl font-bold text-orange-600 mt-1">{{ $stats['rejected'] }}</div>
    @if ($stats['rejected'] > 0)
        <div class="mt-2 text-sm text-blue-600 opacity-0 group-hover:opacity-100 transition-opacity">
            Voir la liste →
        </div>
    @endif
    @if ($stats['rejected'] > 0)
    </a>@else</div>
    @endif
    </div>
    </div>

    <!-- Fonctionnaire Section -->
    <div class="mb-12">
        <h3 class="text-lg font-semibold mb-4 text-gray-700">Fonctionnaire</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @if ($stats['rejected'] > 0)
                {{-- <a href="{{ route('demandes.index', ['type' => 'transfert-cheques-fonctionnaire']) }}" --}}
                <a href="#"
                    class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer hover:bg-gray-50 group block">
                @else
                    <div class="bg-white p-4 rounded-lg shadow-sm">
            @endif
            <div class="text-gray-500 text-sm">Demande de transfert de cheques</div>
            <div class="text-2xl font-bold text-red-600 mt-1">{{ $stats['rejected'] }}</div>
            @if ($stats['rejected'] > 0)
                <div class="mt-2 text-sm text-blue-600 opacity-0 group-hover:opacity-100 transition-opacity">
                    Voir la liste →
                </div>
            @endif
            @if ($stats['rejected'] > 0)
                </a>
            @else
        </div>
        @endif

        @if ($stats['approved'] > 0)
            <a href="#"
                class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer hover:bg-gray-50 group block">
            @else
                <div class="bg-white p-4 rounded-lg shadow-sm">
        @endif
        <div class="text-gray-500 text-sm">Demande d'arret de paiement</div>
        <div class="text-2xl font-bold text-blue-600 mt-1">{{ $stats['approved'] }}</div>
        @if ($stats['approved'] > 0)
            <div class="mt-2 text-sm text-blue-600 opacity-0 group-hover:opacity-100 transition-opacity">
                Voir la liste →
            </div>
        @endif
        @if ($stats['approved'] > 0)
            </a>
        @else
    </div>
    @endif

    @if ($stats['completed'] > 0)
        <a href="#"
            class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer hover:bg-gray-50 group block">
        @else
            <div class="bg-white p-4 rounded-lg shadow-sm">
    @endif
    <div class="text-gray-500 text-sm">Demande de réinsertion</div>
    <div class="text-2xl font-bold text-green-600 mt-1">{{ $stats['completed'] }}</div>
    @if ($stats['completed'] > 0)
        <div class="mt-2 text-sm text-blue-600 opacity-0 group-hover:opacity-100 transition-opacity">
            Voir la liste →
        </div>
    @endif
    @if ($stats['completed'] > 0)
    </a>@else</div>
    @endif
    </div>
    </div>
    </div>
    </div>
</x-app-layout>
