@extends('layouts.main')

@section('title', 'Demande de visioconférence')

@section('content')
<style>
    .card-shadow { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
    .plateforme-option.is-active { border-color: #173052; background: #f4f6f9; }
</style>

@php
    $fieldClass = fn (string $field) => 'mt-1 block w-full py-2.5 px-4 border text-base focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy '
        . ($errors->has($field) ? 'border-red-400 bg-red-50' : 'border-gray-200');
@endphp

<div class="py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-8">

        <div class="text-center">
            <span class="text-xs font-bold text-orange-500 uppercase tracking-widest">Communications</span>
            <h1 class="text-4xl font-bold text-navy mt-2 mb-3">Demande de visioconférence</h1>
            <p class="text-gray-600 max-w-2xl mx-auto">
                Ministère de l’Économie et des Finances — Direction de la Pension Civile.
            </p>
        </div>

        @if(session('success'))
            <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
                <div class="flex items-start gap-3 text-green-800">
                    <i class="fa-solid fa-check-circle text-green-500 mt-0.5 shrink-0"></i>
                    <p class="text-base">{{ session('success') }}</p>
                </div>
            </section>
        @endif

        @if($errors->any())
            <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
                <div class="flex items-start gap-3 text-red-800">
                    <i class="fa-solid fa-exclamation-circle text-red-500 mt-0.5 shrink-0"></i>
                    <ul class="text-base list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </section>
        @endif

        <form action="{{ route('demandes.rencontre.store') }}" method="POST" id="main-form" class="space-y-8"
              x-data="{ plateforme: @js(old('plateforme', '')) }">
            @csrf

            <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-6">
                    <span class="text-3xl text-navy flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-user" aria-hidden="true"></i>
                    </span>
                    <h2 class="text-xl font-bold text-navy">Informations personnelles</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="prenom" class="block text-base font-medium text-gray-700">Prénom <span class="text-red-500">*</span></label>
                        <input type="text" id="prenom" name="prenom" value="{{ old('prenom', auth()->user()?->firstname ?? '') }}"
                               placeholder="Jean" class="{{ $fieldClass('prenom') }}">
                        @error('prenom')<p class="mt-1 text-base text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="nom" class="block text-base font-medium text-gray-700">Nom <span class="text-red-500">*</span></label>
                        <input type="text" id="nom" name="nom" value="{{ old('nom', auth()->user()?->lastname ?? '') }}"
                               placeholder="Dupont" class="{{ $fieldClass('nom') }}">
                        @error('nom')<p class="mt-1 text-base text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="email" class="block text-base font-medium text-gray-700">Adresse e-mail <span class="text-red-500">*</span></label>
                        <input type="email" id="email" name="email" value="{{ old('email', auth()->user()?->email ?? '') }}"
                               placeholder="vous@exemple.com" class="{{ $fieldClass('email') }}">
                        @error('email')<p class="mt-1 text-base text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="telephone" class="block text-base font-medium text-gray-700">Téléphone</label>
                        <input type="text" id="telephone" name="telephone" value="{{ old('telephone') }}"
                               placeholder="+509 XX XX XXXX" class="{{ $fieldClass('telephone') }}">
                        @error('telephone')<p class="mt-1 text-base text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="mt-5">
                    <label for="organisation" class="block text-base font-medium text-gray-700">
                        Organisation / Institution <span class="text-gray-400 font-normal">(optionnel)</span>
                    </label>
                    <input type="text" id="organisation" name="organisation" value="{{ old('organisation') }}"
                           placeholder="Ministère, ONG, entreprise..." class="{{ $fieldClass('organisation') }}">
                    @error('organisation')<p class="mt-1 text-base text-red-600">{{ $message }}</p>@enderror
                </div>
            </section>

            <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-6">
                    <span class="text-3xl text-navy flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-video" aria-hidden="true"></i>
                    </span>
                    <h2 class="text-xl font-bold text-navy">Détails de la visioconférence</h2>
                </div>

                <div class="mb-5">
                    <label for="objet" class="block text-base font-medium text-gray-700">Objet de la rencontre <span class="text-red-500">*</span></label>
                    <input type="text" id="objet" name="objet" value="{{ old('objet') }}"
                           placeholder="Ex : Discussion sur mon dossier de pension, partenariat institutionnel..."
                           class="{{ $fieldClass('objet') }}">
                    @error('objet')<p class="mt-1 text-base text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                    <div>
                        <label for="date_souhaitee" class="block text-base font-medium text-gray-700">Date souhaitée <span class="text-red-500">*</span></label>
                        <input type="date" id="date_souhaitee" name="date_souhaitee" value="{{ old('date_souhaitee') }}"
                               min="{{ date('Y-m-d') }}" class="{{ $fieldClass('date_souhaitee') }}">
                        @error('date_souhaitee')<p class="mt-1 text-base text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="heure_souhaitee" class="block text-base font-medium text-gray-700">Heure souhaitée <span class="text-red-500">*</span></label>
                        <select id="heure_souhaitee" name="heure_souhaitee" class="{{ $fieldClass('heure_souhaitee') }}">
                            <option value="">-- Choisir une heure --</option>
                            @foreach(['08:00','08:30','09:00','09:30','10:00','10:30','11:00','11:30','13:00','13:30','14:00','14:30','15:00','15:30','16:00','16:30'] as $h)
                                <option value="{{ $h }}" {{ old('heure_souhaitee') === $h ? 'selected' : '' }}>{{ $h }}</option>
                            @endforeach
                        </select>
                        @error('heure_souhaitee')<p class="mt-1 text-base text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="mb-5">
                    <p class="block text-base font-medium text-gray-700 mb-2">Plateforme souhaitée <span class="text-red-500">*</span></p>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @foreach(['zoom' => 'Zoom', 'teams' => 'Microsoft Teams', 'meet' => 'Google Meet', 'autre' => 'Autre'] as $val => $label)
                            <label class="plateforme-option flex items-center gap-2 border border-gray-200 px-3 py-2.5 cursor-pointer hover:bg-gray-50 transition"
                                   :class="{ 'is-active': plateforme === @js($val) }">
                                <input type="radio" name="plateforme" value="{{ $val }}" x-model="plateforme"
                                       {{ old('plateforme') === $val ? 'checked' : '' }}
                                       class="accent-[#173052]">
                                <span class="text-base text-gray-700">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('plateforme')<p class="mt-1 text-base text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="message" class="block text-base font-medium text-gray-700">
                        Message complémentaire <span class="text-gray-400 font-normal">(optionnel)</span>
                    </label>
                    <textarea id="message" name="message" rows="4"
                              placeholder="Précisions supplémentaires, documents à préparer..."
                              class="{{ $fieldClass('message') }} resize-none">{{ old('message') }}</textarea>
                    @error('message')<p class="mt-1 text-base text-red-600">{{ $message }}</p>@enderror
                </div>
            </section>

            <div class="flex justify-end">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-8 py-3 bg-navy text-white font-semibold hover:opacity-90 transition">
                    <i class="fa-solid fa-paper-plane text-sm" aria-hidden="true"></i>
                    Envoyer la demande
                </button>
            </div>
        </form>

    </div>
</div>
@endsection
