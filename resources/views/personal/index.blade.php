<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Suivi de mes demandes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Pensionnaire Section --}}
            @can('viewPensionnaireSection')
                <div class="mb-12">
                    <h3 class="text-lg font-semibold mb-4 text-gray-700">Pensionnaire</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        @foreach (['pending', 'in_progress', 'rejected', 'approved', 'completed'] as $status)
                            @if ($stats[$status] > 0)
                                <a href="#"
                                    class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer hover:bg-gray-50 group block">
                                @else
                                    <div class="bg-white p-4 rounded-lg shadow-sm">
                            @endif
                            <div class="text-gray-500 text-sm">Demande de {{ str_replace('_', ' ', ucfirst($status)) }}
                            </div>
                            <div
                                class="text-2xl font-bold {{ $status == 'pending' ? 'text-yellow-600' : ($status == 'in_progress' ? 'text-purple-600' : ($status == 'rejected' ? 'text-red-600' : ($status == 'approved' ? 'text-blue-600' : 'text-green-600'))) }} mt-1">
                                {{ $stats[$status] }}</div>
                            @if ($stats[$status] > 0)
                                <div
                                    class="mt-2 text-sm text-blue-600 opacity-0 group-hover:opacity-100 transition-opacity">
                                    Voir la liste →</div>
                            @endif
                            @if ($stats[$status] > 0)
                                </a>
                            @else
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
        @endcan

        {{-- Fonctionnaire Section --}}
        @can('viewFonctionnaireSection')
            <div class="mb-12">
                <h3 class="text-lg font-semibold mb-4 text-gray-700">Fonctionnaire</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach (['rejected', 'approved', 'completed'] as $status)
                        @if ($stats[$status] > 0)
                            <a href="#"
                                class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer hover:bg-gray-50 group block">
                            @else
                                <div class="bg-white p-4 rounded-lg shadow-sm">
                        @endif
                        <div class="text-gray-500 text-sm">Demande de {{ str_replace('_', ' ', ucfirst($status)) }}</div>
                        <div
                            class="text-2xl font-bold {{ $status == 'rejected' ? 'text-red-600' : ($status == 'approved' ? 'text-blue-600' : 'text-green-600') }} mt-1">
                            {{ $stats[$status] }}</div>
                        @if ($stats[$status] > 0)
                            <div class="mt-2 text-sm text-blue-600 opacity-0 group-hover:opacity-100 transition-opacity">
                                Voir la liste →</div>
                        @endif
                        @if ($stats[$status] > 0)
                            </a>
                        @else
                </div>
                @endif
                @endforeach
            </div>
        </div>
    @endcan

    {{-- Institution Section --}}
    @can('viewInstitutionSection')
        <div class="mb-12">
            <h3 class="text-lg font-semibold mb-4 text-gray-700">Institution</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach (['rejected', 'approved', 'completed'] as $status)
                    @if ($stats[$status] > 0)
                        <a href="#"
                            class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer hover:bg-gray-50 group block">
                        @else
                            <div class="bg-white p-4 rounded-lg shadow-sm">
                    @endif
                    <div class="text-gray-500 text-sm">Demande de {{ str_replace('_', ' ', ucfirst($status)) }}</div>
                    <div
                        class="text-2xl font-bold {{ $status == 'rejected' ? 'text-red-600' : ($status == 'approved' ? 'text-blue-600' : 'text-green-600') }} mt-1">
                        {{ $stats[$status] }}</div>
                    @if ($stats[$status] > 0)
                        <div class="mt-2 text-sm text-blue-600 opacity-0 group-hover:opacity-100 transition-opacity">Voir la
                            liste →</div>
                    @endif
                    @if ($stats[$status] > 0)
                        </a>
                    @else
            </div>
            @endif
            @endforeach
        </div>
        </div>
    @endcan

    </div>
    </div>
</x-app-layout>
