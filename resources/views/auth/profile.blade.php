@extends('layouts.main')

@section('title', 'Modifier votre profil')

@section('content')
        <div class="max-w-6xl mx-auto bg-white p-6 my-8 shadow-md rounded-lg relative m-2">
            <h2 class="text-lg font-medium text-gray-900">Modifier votre photo de profil</h2>

            <form method="POST" action="{{ route('profile.profile-photo.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                @if (session('status') === 'profile-photo-updated')
                    <p class="text-green-600">Photo profil mise à jour avec succès.</p>
                @endif

                <div class="flex gap-5 items-center mb-8 pt-5">
                    <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Photo actuelle</label>
                            <div
                                class="h-20 w-20 rounded-full bg-gray-100 flex items-center justify-center overflow-hidden mb-4 relative group">
                                @if (auth()->user()->profile_photo)
                                    <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" alt="Profile Picture"
                                        class="h-full w-full object-cover">
                                    <div
                                        class="absolute inset-0 bg-black bg-opacity-40 hidden group-hover:flex items-center justify-center">
                                        <span class="text-white text-sm">Changer</span>
                                    </div>
                                @else
                                    <svg class="h-12 w-12 text-gray-400 group-hover:text-gray-500 transition-colors"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                @endif
                            </div>
                    </div>

                    <div class="space-y-2">
                        <div class="relative">
                            <input type="file" name="profile_photo" id="profile_photo"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                onchange="document.getElementById('file-name').textContent = this.files[0]?.name || 'Aucun fichier sélectionné'">

                            <div
                                class="flex items-center justify-center px-4 py-2 border-2 border-dashed border-gray-300 rounded-lg 
                                    hover:border-blue-500 hover:bg-blue-50 transition-colors duration-200
                                    {{ $errors->has('profile_photo') ? 'border-red-500' : '' }}">
                                <svg class="w-5 h-5 mr-2 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                <span class="text-sm font-medium text-gray-600">
                                    <span id="file-name">Choisir un fichier</span>
                                </span>
                            </div>
                        </div>

                        @error('profile_photo')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Formats supportés: JPEG, PNG - Max 2MB</p>
                    </div>
                </div>

                <div class="mt-6 text-right">
                    <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition-colors
                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Mettre à jour
                    </button>
                </div>
            </form>
        </div>

        <div class="max-w-6xl mx-auto bg-white p-6 my-8 shadow-md rounded-lg relative m-2">
            <h2 class="text-lg font-medium text-gray-900">Modifier vos informations</h2>

            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PATCH')

                @if (session('status') === 'profile-updated')
                    <p class="text-green-600">Informations mises à jour avec succès.</p>
                @endif

                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- NAME (Institution only) --}}
                    @if(auth()->user()->hasRole('institution'))
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                Nom *
                                <span class="text-gray-500 text-xs">(non modifiable)</span>
                            </label>
                            <input type="text" name="name" id="name"
                                value="{{ old('name', auth()->user()->name) }}"
                                disabled
                                class="w-full rounded-md bg-gray-100 cursor-not-allowed
                                @error('name') border-red-500 @else border-gray-300 @enderror">
                            @error('name')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    {{-- FIRSTNAME (not institution) --}}
                    @unless(auth()->user()->hasRole('institution'))
                        <div>
                            <label for="firstname" class="block text-sm font-medium text-gray-700 mb-1">
                                Prénom *
                                <span class="text-gray-500 text-xs">(non modifiable)</span>
                            </label>
                            <input type="text" name="firstname" id="firstname"
                                value="{{ old('firstname', auth()->user()->firstname) }}"
                                disabled
                                class="w-full rounded-md bg-gray-100 cursor-not-allowed
                                @error('firstname') border-red-500 @else border-gray-300 @enderror">
                            @error('firstname')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                    @endunless

                    {{-- LASTNAME (not institution) --}}
                    @unless(auth()->user()->hasRole('institution'))
                        <div>
                            <label for="lastname" class="block text-sm font-medium text-gray-700 mb-1">
                                Nom de famille *
                                <span class="text-gray-500 text-xs">(non modifiable)</span>
                            </label>
                            <input type="text" name="lastname" id="lastname"
                                value="{{ old('lastname', auth()->user()->lastname) }}"
                                disabled
                                class="w-full rounded-md bg-gray-100 cursor-not-allowed
                                @error('lastname') border-red-500 @else border-gray-300 @enderror">
                            @error('lastname')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                    @endunless

                    {{-- EMAIL --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            Email *
                            <span class="text-gray-500 text-xs">(non modifiable)</span>
                        </label>
                        <input type="email" name="email" id="email"
                            value="{{ old('email', auth()->user()->email) }}"
                            readonly
                            class="w-full rounded-md bg-gray-100 cursor-not-allowed
                            @error('email') border-red-500 @else border-gray-300 @enderror">
                        @error('email')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- NIF --}}
                    <div>
                        <label for="nif" class="block text-sm font-medium text-gray-700 mb-1">
                            NIF
                        </label>
                        <input type="text" name="nif" id="nif"
                            value="{{ old('nif', auth()->user()->nif) }}"
                            placeholder="ex:809-062-525-6"
                            class="w-full rounded-md
                            @error('nif') border-red-500 @else border-gray-300 @enderror">
                        @error('nif')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- NINU (not institution) --}}
                    @unless(auth()->user()->hasRole('institution'))
                        <div>
                            <label for="ninu" class="block text-sm font-medium text-gray-700 mb-1">
                                NINU
                            </label>
                            <input type="text" name="ninu" id="ninu"
                                value="{{ old('ninu', auth()->user()->ninu) }}"
                                class="w-full rounded-md
                                @error('ninu') border-red-500 @else border-gray-300 @enderror">
                            @error('ninu')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                    @endunless

                    {{-- PHONE --}}
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                            Téléphone
                        </label>
                        <input type="text" name="phone" id="phone"
                        placeholder="+509XXXXXXXX"
                            value="{{ old('phone', auth()->user()->phone) }}"
                            class="w-full rounded-md
                            @error('phone') border-red-500 @else border-gray-300 @enderror">
                        @error('phone')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- SEXE --}}
                    <div>
                        <label for="sexe" class="block text-sm font-medium text-gray-700 mb-1">
                            Sexe *
                            <span class="text-gray-500 text-xs">(non modifiable)</span>
                        </label>
                        <input type="text" name="sexe" id="sexe"
                            value="{{ old('sexe', auth()->user()->gender?->name) }}"
                            disabled
                            class="w-full rounded-md bg-gray-100 cursor-not-allowed
                            @error('sexe') border-red-500 @else border-gray-300 @enderror">

                        @error('sexe')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 text-right">
                    <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition-colors
                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Mettre à jour
                    </button>
                </div>
            </form>
        </div>

        <div class="max-w-6xl mx-auto bg-white p-6 my-8 shadow-md rounded-lg relative m-2">
            <h2 class="text-lg font-medium text-gray-900">Mettre à jour le mot de passe</h2>
            <p class="mt-1 text-sm text-gray-600">Assurez-vous d'utiliser un mot de passe long et aléatoire pour sécuriser votre compte.</p>
            
            <form method="POST" action="{{ route('password.update') }}" class="mt-5">
                @csrf
                @method('PUT')

                @if (session('status') === 'password-updated')
                    <p class="mt-4 text-sm text-green-600">
                        Mot de passe mis à jour avec succès.
                    </p>
                @endif

                <div class="mt-4 space-y-4">
                    <div>
                        <label for="update_password_current_password"
                            class="block text-sm font-medium text-gray-700 mb-1">
                            Mot de passe actuel
                        </label>

                        <input type="password"
                            name="current_password"
                            id="update_password_current_password"
                            class="w-full rounded-md 
                                @error('current_password', 'updatePassword') border-red-500 
                                @else border-gray-300 
                                @enderror"
                            placeholder="••••••••">

                        @error('current_password', 'updatePassword')
                            <p class="error-message text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="update_password_password"
                            class="block text-sm font-medium text-gray-700 mb-1">
                            Nouveau mot de passe
                        </label>

                        <input type="password"
                            name="password"
                            id="update_password_password"
                            class="w-full rounded-md 
                                @error('password', 'updatePassword') border-red-500 
                                @else border-gray-300 
                                @enderror"
                            placeholder="••••••••">

                        @error('password', 'updatePassword')
                            <p class="error-message text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="update_password_password_confirmation"
                            class="block text-sm font-medium text-gray-700 mb-1">
                            Confirmer le mot de passe
                        </label>

                        <input type="password"
                            name="password_confirmation"
                            id="update_password_password_confirmation"
                            class="w-full rounded-md border-gray-300"
                            placeholder="••••••••">
                    </div>
                </div>

                <div class="mt-6 text-right">
                    <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition-colors
                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Mettre à jour
                    </button>
                </div>
            </form>
        </div>

    <script>
        // Update file name display
        document.getElementById('avatar').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || 'Aucun fichier sélectionné';
            document.getElementById('file-name').textContent = fileName;
        });
    </script>
@endsection
