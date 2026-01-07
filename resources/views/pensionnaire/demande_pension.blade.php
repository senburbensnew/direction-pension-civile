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

    <!-- ✅ Breadcrumb -->
{{--     <nav aria-label="Breadcrumb" class="mb-4">
        <ol class="flex items-center space-x-2 text-sm text-gray-600">
            <li><span class="text-gray-800">Formalité</span></li>
            <li class="flex items-center">
                <span class="mx-2">›</span>
            </li>
            <li aria-current="page">
                <span class="text-gray-800 font-medium">Formulaire</span>
            </li>
        </ol>
    </nav> --}}

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
                    <p class="text-base md:text-lg font-semibold mb-2">Formulaire d’enregistrement</p>
                    <p class="text-sm text-gray-600">Remplissez soigneusement les informations demandées</p>
                </div>

                <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}"
                    class="w-16 h-16 md:w-24 md:h-24 object-cover shrink-0 hidden md:block"
                    loading="lazy">
            </div>
        </div>

        <!-- ✅ Form -->
        <form action="{{ route('demandes.demande-pension-reversion.store') }}" method="POST" class="space-y-8">
            @csrf

            {{-- ✅ Identité --}}
            <section class="bg-white shadow-md rounded-xl p-6 border border-gray-200 mb-8">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Identité</h2>

                <div class="gradient-bg">
                    <p class="text-sm text-gray-700">Veuillez fournir les informations d'identification du demandeur.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <div>
                        <label class="block mb-1 font-medium text-gray-700">NIF <span class="text-red-500">*</span></label>
                        <input type="text" name="nif" class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" >
                    </div>

                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Nom <span class="text-red-500">*</span></label>
                        <input type="text" name="nom" class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" >
                    </div>

                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Prénom <span class="text-red-500">*</span></label>
                        <input type="text" name="prenom" class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" >
                    </div>

                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Nom complet</label>
                        <input type="text" name="nom_complet" class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Téléphone</label>
                        <input type="text" name="telephone" class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Email</label>
                        <input type="email" name="email" class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                </div>
            </section>


            {{-- ✅ Naissance --}}
            <section class="bg-white shadow-md rounded-xl p-6 border border-gray-200 mb-8">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Naissance</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Date de naissance</label>
                        <input type="date" name="date_naissance" class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Lieu de naissance</label>
                        <input type="text" name="lieu_naissance" class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                </div>
            </section>


            {{-- ✅ Adresse --}}
            <section class="bg-white shadow-md rounded-xl p-6 border border-gray-200 mb-8">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Adresse</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Département</label>
                        <input type="text" name="departement" class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Commune</label>
                        <input type="text" name="commune" class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Adresse</label>
                        <input type="text" name="adresse" class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                </div>
            </section>


            {{-- ✅ CIN --}}
            <section class="bg-white shadow-md rounded-xl p-6 border border-gray-200 mb-8">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Carte d'identité (CIN)</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <div>
                        <label class="block mb-1 font-medium text-gray-700">CIN</label>
                        <input type="text" name="cin" class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Date délivrance</label>
                        <input type="date" name="date_delivrance_cin" class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Lieu délivrance</label>
                        <input type="text" name="lieu_delivrance_cin" class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                </div>
            </section>


            {{-- ✅ Passeport --}}
            <section class="bg-white shadow-md rounded-xl p-6 border border-gray-200 mb-8">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Passeport</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Passeport</label>
                        <input type="text" name="passeport" class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Date délivrance</label>
                        <input type="date" name="date_delivrance_passeport" class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Lieu délivrance</label>
                        <input type="text" name="lieu_delivrance_passeport" class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Date expiration</label>
                        <input type="date" name="date_expiration_passeport" class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                </div>
            </section>


            <!-- ✅ Submit -->
            <div class="text-right">
                <button type="submit"
                    class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow">
                    Enregistrer
                </button>
            </div>

        </form>

    </main>
</div>
@endsection
