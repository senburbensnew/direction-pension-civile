<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Suivi des demandes') }}
        </h2>
    </x-slot>

    <div>
        <fieldset class="border-2 border-gray-200 rounded-lg mt-8 pl-3 ml-1 mr-1">
            <legend class="text-lg font-semibold ml-4 px-4 text-gray-700 bg-white rounded-full shadow-sm">
                Mes demandes
            </legend>
            <div class="py-6 pl-5 pr-5">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    @can('viewPensionnaireSection')
                        <div class="mb-12">
                            <h3 class="text-lg font-semibold mb-4 text-gray-700">Pensionnaire</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                @foreach ($stats['pensionnaire'] as $request)
                                    @if ($request['count'] > 0)
                                        <a href="{{ route('personal.requests-dashboard', ['request_type' => $request['type']]) }}"
                                            class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer hover:bg-gray-50 group block">
                                        @else
                                            <div class="bg-white p-4 rounded-lg shadow-sm">
                                    @endif
                                    <div class="text-gray-500 text-sm">{{ $request['label'] }}</div>
                                    <div class="text-2xl font-bold mt-1">{{ $request['count'] }}</div>
                                    @if ($request['count'] > 0)
                                        <div
                                            class="mt-2 text-sm text-blue-600 opacity-0 group-hover:opacity-100 transition-opacity">
                                            Voir la liste →
                                        </div>
                                    @endif
                                    @if ($request['count'] > 0)
                                        </a>
                                    @else
                            </div>
                            @endif
                            @endforeach
                        </div>
                    </div>
                @endcan
                @can('viewFonctionnaireSection')
                    <div class="mb-12">
                        <h3 class="text-lg font-semibold mb-4 text-gray-700">Fonctionnaire</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                            @foreach ($stats['fonctionnaire'] as $request)
                                @if ($request['count'] > 0)
                                    <a href="{{ route('personal.requests-dashboard', ['request_type' => $request['type']]) }}"
                                        class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer hover:bg-gray-50 group block">
                                    @else
                                        <div class="bg-white p-4 rounded-lg shadow-sm">
                                @endif
                                <div class="text-gray-500 text-sm">{{ $request['label'] }}</div>
                                <div class="text-2xl font-bold mt-1">{{ $request['count'] }}</div>
                                @if ($request['count'] > 0)
                                    <div
                                        class="mt-2 text-sm text-blue-600 opacity-0 group-hover:opacity-100 transition-opacity">
                                        Voir la liste →
                                    </div>
                                @endif
                                @if ($request['count'] > 0)
                                    </a>
                                @else
                        </div>
                        @endif
                        @endforeach
                    </div>
                @endcan
                @can('viewInstitutionSection')
                    InstitutionSection
                @endcan
            </div>
    </div>
    </fieldset>
    {{--     <fieldset class="border-2 border-gray-200 rounded-lg mt-8 pl-3 ml-1 mr-1">
        <legend class="text-lg font-semibold ml-4 px-4 text-gray-700 bg-white rounded-full shadow-sm">
            Autres demandes
        </legend>
        <div class="py-6 pl-5 pr-5">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                @can('viewPensionnaireSection')
                    <div class="mb-12">
                        <h3 class="text-lg font-semibold mb-4 text-gray-700">Pensionnaire</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                            @foreach ($stats['pensionnaire'] as $request)
                                @if ($request['count'] > 0)
                                    <a href="{{ route('personal.requests-dashboard', ['request_type' => $request['type']]) }}"
                                        class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer hover:bg-gray-50 group block">
                                    @else
                                        <div class="bg-white p-4 rounded-lg shadow-sm">
                                @endif
                                <div class="text-gray-500 text-sm">{{ $request['label'] }}</div>
                                <div class="text-2xl font-bold mt-1">{{ $request['count'] }}</div>
                                @if ($request['count'] > 0)
                                    <div
                                        class="mt-2 text-sm text-blue-600 opacity-0 group-hover:opacity-100 transition-opacity">
                                        Voir la liste →
                                    </div>
                                @endif
                                @if ($request['count'] > 0)
                                    </a>
                                @else
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
            @endcan
            @can('viewFonctionnaireSection')
                FonctionnaireSection
            @endcan
            @can('viewInstitutionSection')
                InstitutionSection
            @endcan
        </div>
        </div>
    </fieldset> --}}
    </div>
</x-app-layout>
