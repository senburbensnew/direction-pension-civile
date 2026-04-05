@extends('layouts.main')

@section('content')
<div class="m-5 max-w-4xl mx-auto p-6 bg-white rounded-lg shadow-md">

    <!-- HEADER -->
    <div class="flex flex-col md:flex-row justify-around items-center mb-12 gap-8">
        <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}" class="w-24 h-24 object-cover">
        <h1 class="text-xl md:text-2xl font-bold text-center">
            MINISTERE DE L'ECONOMIE ET DES FINANCES<br>
            <span class="underline">PENSION CIVILE</span><br>
            Demande de Visioconférence
        </h1>
        <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}" class="w-24 h-24 object-cover">
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded">
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('demandes.rencontre.store') }}" method="POST" id="main-form">
        @csrf

        {{-- Identité --}}
        <div class="mb-6">
            <h2 class="text-base font-semibold text-gray-800 border-b pb-2 mb-4">Informations personnelles</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Prénom <span class="text-red-500">*</span></label>
                    <input type="text" name="prenom" value="{{ old('prenom', auth()->user()?->firstname ?? '') }}"
                           placeholder="Jean"
                           class="mt-1 block w-full rounded-md border @error('prenom') border-red-500 @else border-gray-300 @enderror shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('prenom')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Nom <span class="text-red-500">*</span></label>
                    <input type="text" name="nom" value="{{ old('nom', auth()->user()?->lastname ?? '') }}"
                           placeholder="Dupont"
                           class="mt-1 block w-full rounded-md border @error('nom') border-red-500 @else border-gray-300 @enderror shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('nom')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Adresse e-mail <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', auth()->user()?->email ?? '') }}"
                           placeholder="vous@exemple.com"
                           class="mt-1 block w-full rounded-md border @error('email') border-red-500 @else border-gray-300 @enderror shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Téléphone</label>
                    <input type="text" name="telephone" value="{{ old('telephone') }}"
                           placeholder="+509 XX XX XXXX"
                           class="mt-1 block w-full rounded-md border @error('telephone') border-red-500 @else border-gray-300 @enderror shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('telephone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">
                    Organisation / Institution <span class="text-gray-400 font-normal">(optionnel)</span>
                </label>
                <input type="text" name="organisation" value="{{ old('organisation') }}"
                       placeholder="Ministère, ONG, entreprise..."
                       class="mt-1 block w-full rounded-md border @error('organisation') border-red-500 @else border-gray-300 @enderror shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('organisation')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Détails de la rencontre --}}
        <div class="mb-6">
            <h2 class="text-base font-semibold text-gray-800 border-b pb-2 mb-4">Détails de la visioconférence</h2>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Objet de la rencontre <span class="text-red-500">*</span></label>
                <input type="text" name="objet" value="{{ old('objet') }}"
                       placeholder="Ex : Discussion sur mon dossier de pension, partenariat institutionnel..."
                       class="mt-1 block w-full rounded-md border @error('objet') border-red-500 @else border-gray-300 @enderror shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('objet')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Date souhaitée <span class="text-red-500">*</span></label>
                    <input type="date" name="date_souhaitee" value="{{ old('date_souhaitee') }}"
                           min="{{ date('Y-m-d') }}"
                           class="mt-1 block w-full rounded-md border @error('date_souhaitee') border-red-500 @else border-gray-300 @enderror shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('date_souhaitee')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Heure souhaitée <span class="text-red-500">*</span></label>
                    <select name="heure_souhaitee"
                            class="mt-1 block w-full rounded-md border @error('heure_souhaitee') border-red-500 @else border-gray-300 @enderror shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">-- Choisir une heure --</option>
                        @foreach(['08:00','08:30','09:00','09:30','10:00','10:30','11:00','11:30','13:00','13:30','14:00','14:30','15:00','15:30','16:00','16:30'] as $h)
                            <option value="{{ $h }}" {{ old('heure_souhaitee') === $h ? 'selected' : '' }}>{{ $h }}</option>
                        @endforeach
                    </select>
                    @error('heure_souhaitee')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Plateforme souhaitée <span class="text-red-500">*</span></label>
                <div class="mt-2 grid grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach(['zoom' => 'Zoom', 'teams' => 'Microsoft Teams', 'meet' => 'Google Meet', 'autre' => 'Autre'] as $val => $label)
                        <label class="flex items-center gap-2 border rounded-md px-3 py-2 cursor-pointer hover:bg-gray-50 transition-colors
                                      {{ old('plateforme') === $val ? 'border-blue-500 bg-blue-50' : 'border-gray-300' }}">
                            <input type="radio" name="plateforme" value="{{ $val }}"
                                   {{ old('plateforme') === $val ? 'checked' : '' }}
                                   class="accent-blue-600">
                            <span class="text-sm text-gray-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('plateforme')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">
                    Message complémentaire <span class="text-gray-400 font-normal">(optionnel)</span>
                </label>
                <textarea name="message" rows="4"
                          placeholder="Précisions supplémentaires, documents à préparer..."
                          class="mt-1 block w-full rounded-md border @error('message') border-red-500 @else border-gray-300 @enderror shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('message') }}</textarea>
                @error('message')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Submit --}}
        <div class="mt-8 flex justify-end">
            <button type="submit"
                    class="bg-blue-600 text-white px-8 py-2 rounded hover:bg-blue-700">
                Envoyer la demande
            </button>
        </div>
    </form>
</div>
@endsection
