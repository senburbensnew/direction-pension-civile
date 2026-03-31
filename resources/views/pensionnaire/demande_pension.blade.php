@extends('layouts.main')

@section('content')
<style>
    .input {
        @apply w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500;
    }
    .input-error {
        @apply border-red-500 focus:border-red-500 focus:ring-red-500;
    }
    .error-message {
        @apply mt-1 text-sm text-red-600;
    }
    .gradient-bg {
        background: linear-gradient(90deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 0.5rem;
        padding: 1rem;
        margin: 1rem 0;
    }
</style>

<div class="max-w-6xl mx-auto p-6 m-2 ">

    <!-- ✅ Main Card -->
    <main class="max-w-7xl mx-auto bg-white p-6 shadow-md rounded-lg relative m-2">

        <!-- ✅ Header -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-12 gap-8">
            <div class="text-center mb-6 flex flex-col md:flex-row items-center justify-center w-full gap-4 md:gap-8">

                <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}"
                    alt="Logo"
                    class="w-16 h-16 md:w-24 md:h-24 object-cover shrink-0" loading="lazy">

                <div class="px-4">
                    <h1 class="text-lg md:text-xl font-bold mb-1">Formalité administrative</h1>
                    <p class="text-base md:text-lg font-semibold mb-2">Formulaire d'enregistrement</p>
                    <p class="text-sm text-gray-600">Remplissez soigneusement les informations demandées</p>
                </div>

                <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}"
                    class="w-16 h-16 md:w-24 md:h-24 object-cover shrink-0 hidden md:block"
                    loading="lazy">
            </div>
        </div>

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded bg-red-100 p-4 text-red-700">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($demande)
            <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded text-sm">
                Brouillon en cours — dernière sauvegarde {{ $demande->updated_at->diffForHumans() }}
            </div>
        @endif

        <!-- ✅ Form -->
        <form action="{{ route('demandes.pension-pensionnaire.store') }}" method="POST" class="space-y-8">
            @csrf

            {{-- Title + hidden demande_id --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">
                        Titre personnalisé <span class="text-gray-400 font-normal">(optionnel)</span>
                    </label>
                    <input
                        id="title"
                        type="text"
                        name="title"
                        value="{{ old('title', $demande?->title ?? '') }}"
                        placeholder="ex : Demande de pension — 2026"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                </div>
                @if ($demande)
                    <input type="hidden" name="demande_id" value="{{ $demande->id }}">
                @endif
            </div>

            {{-- ✅ Identité --}}
            <section class="bg-white shadow-md rounded-xl p-6 border border-gray-200 mb-8">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Identité</h2>

                <div class="gradient-bg">
                    <p class="text-sm text-gray-700">Veuillez fournir les informations d'identification du demandeur.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <div>
                        <label class="block mb-1 font-medium text-gray-700">NIF</label>
                        <input type="text" name="nif"
                            value="{{ old('nif', data_get($demande, 'data.nif', '')) }}"
                            class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('nif') <p class="error-message">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Nom</label>
                        <input type="text" name="nom"
                            value="{{ old('nom', data_get($demande, 'data.nom', '')) }}"
                            class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('nom') <p class="error-message">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Prénom</label>
                        <input type="text" name="prenom"
                            value="{{ old('prenom', data_get($demande, 'data.prenom', '')) }}"
                            class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('prenom') <p class="error-message">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Nom complet</label>
                        <input type="text" name="nom_complet"
                            value="{{ old('nom_complet', data_get($demande, 'data.nom_complet', '')) }}"
                            class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('nom_complet') <p class="error-message">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Téléphone</label>
                        <input type="text" name="telephone"
                            value="{{ old('telephone', data_get($demande, 'data.telephone', '')) }}"
                            class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('telephone') <p class="error-message">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Email</label>
                        <input type="email" name="email"
                            value="{{ old('email', data_get($demande, 'data.email', '')) }}"
                            class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('email') <p class="error-message">{{ $message }}</p> @enderror
                    </div>

                </div>
            </section>


            {{-- ✅ Naissance --}}
            <section class="bg-white shadow-md rounded-xl p-6 border border-gray-200 mb-8">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Naissance</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Date de naissance</label>
                        <input type="date" name="date_naissance"
                            value="{{ old('date_naissance', data_get($demande, 'data.date_naissance', '')) }}"
                            class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('date_naissance') <p class="error-message">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Lieu de naissance</label>
                        <input type="text" name="lieu_naissance"
                            value="{{ old('lieu_naissance', data_get($demande, 'data.lieu_naissance', '')) }}"
                            class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('lieu_naissance') <p class="error-message">{{ $message }}</p> @enderror
                    </div>

                </div>
            </section>


            {{-- ✅ Adresse --}}
            <section class="bg-white shadow-md rounded-xl p-6 border border-gray-200 mb-8">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Adresse</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Département</label>
                        <input type="text" name="departement"
                            value="{{ old('departement', data_get($demande, 'data.departement', '')) }}"
                            class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('departement') <p class="error-message">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Commune</label>
                        <input type="text" name="commune"
                            value="{{ old('commune', data_get($demande, 'data.commune', '')) }}"
                            class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('commune') <p class="error-message">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Adresse</label>
                        <input type="text" name="adresse"
                            value="{{ old('adresse', data_get($demande, 'data.adresse', '')) }}"
                            class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('adresse') <p class="error-message">{{ $message }}</p> @enderror
                    </div>

                </div>
            </section>


            {{-- ✅ CIN --}}
            <section class="bg-white shadow-md rounded-xl p-6 border border-gray-200 mb-8">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Carte d'identité (CIN)</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <div>
                        <label class="block mb-1 font-medium text-gray-700">CIN</label>
                        <input type="text" name="cin"
                            value="{{ old('cin', data_get($demande, 'data.cin', '')) }}"
                            class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('cin') <p class="error-message">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Date délivrance</label>
                        <input type="date" name="date_delivrance_cin"
                            value="{{ old('date_delivrance_cin', data_get($demande, 'data.date_delivrance_cin', '')) }}"
                            class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('date_delivrance_cin') <p class="error-message">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Lieu délivrance</label>
                        <input type="text" name="lieu_delivrance_cin"
                            value="{{ old('lieu_delivrance_cin', data_get($demande, 'data.lieu_delivrance_cin', '')) }}"
                            class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('lieu_delivrance_cin') <p class="error-message">{{ $message }}</p> @enderror
                    </div>

                </div>
            </section>


            {{-- ✅ Passeport --}}
            <section class="bg-white shadow-md rounded-xl p-6 border border-gray-200 mb-8">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Passeport</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Passeport</label>
                        <input type="text" name="passeport"
                            value="{{ old('passeport', data_get($demande, 'data.passeport', '')) }}"
                            class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('passeport') <p class="error-message">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Date délivrance</label>
                        <input type="date" name="date_delivrance_passeport"
                            value="{{ old('date_delivrance_passeport', data_get($demande, 'data.date_delivrance_passeport', '')) }}"
                            class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('date_delivrance_passeport') <p class="error-message">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Lieu délivrance</label>
                        <input type="text" name="lieu_delivrance_passeport"
                            value="{{ old('lieu_delivrance_passeport', data_get($demande, 'data.lieu_delivrance_passeport', '')) }}"
                            class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('lieu_delivrance_passeport') <p class="error-message">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Date expiration</label>
                        <input type="date" name="date_expiration_passeport"
                            value="{{ old('date_expiration_passeport', data_get($demande, 'data.date_expiration_passeport', '')) }}"
                            class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('date_expiration_passeport') <p class="error-message">{{ $message }}</p> @enderror
                    </div>

                </div>
            </section>

            <!-- ✅ Submit -->
            <x-demande-actions :demande="$demande" />

        </form>

    </main>
</div>
@endsection
