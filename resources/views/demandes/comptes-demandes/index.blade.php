<x-app-layout>

    <div class="max-w-6xl mx-auto px-4 py-8 space-y-6">

        <nav class="text-sm text-gray-500 flex items-center mb-5">
            <a href="{{ route('personal.cart') }}" class="hover:underline text-blue-600">Corbeille</a>
            <span class="mx-2">/</span>
            <span class="text-gray-700 font-semibold">Demande de création de compte</span>
        </nav>

        @include('comptes-demandes._list')
    </div>

</x-app-layout>