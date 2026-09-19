@extends('layouts.main')

@section('title', 'Mon profil')

@section('content')
@php
    $user = auth()->user();
    $isInstitution = $user->hasRole('institution');
    $displayName = $isInstitution
        ? ($user->name ?: 'Institution')
        : (trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? '')) ?: ($user->name ?: 'Utilisateur'));
    $inputClass = 'w-full rounded-none border-gray-300 shadow-sm focus:border-navy focus:ring-navy';
    $lockedClass = 'w-full rounded-none border-gray-300 bg-gray-100 text-gray-600 cursor-not-allowed shadow-sm';
    $btnClass = 'px-5 py-2.5 bg-navy hover:bg-orange-500 text-white text-sm font-semibold transition-colors';
@endphp

<style>
    .card-shadow { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
</style>

<div class="py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-8">

        <div class="text-center">
            <span class="text-xs font-bold text-orange-500 uppercase tracking-widest">Compte</span>
            <h1 class="text-4xl font-bold text-navy mt-2 mb-3">Mon profil</h1>
            <p class="text-gray-600">{{ $displayName }} · {{ $user->email }}</p>
        </div>

        <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-2">
                <span class="text-3xl text-navy flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-camera" aria-hidden="true"></i>
                </span>
                <h2 class="text-xl font-bold text-navy">Photo de profil</h2>
            </div>
            <p class="text-sm text-gray-500 mb-4">JPEG ou PNG · max. 5 Mo</p>

            @if (session('status') === 'profile-photo-updated')
                <p class="mb-4 text-sm text-green-700">Photo mise à jour avec succès.</p>
            @endif

            <form method="POST" action="{{ route('profile.profile-photo.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <div class="flex flex-col sm:flex-row sm:items-center gap-5">
                    <div class="h-24 w-24 rounded-full bg-gray-100 border border-gray-200 overflow-hidden flex items-center justify-center shrink-0">
                        @if ($user->profile_photo)
                            <img src="{{ asset('storage/' . $user->profile_photo) }}"
                                 alt="Photo actuelle"
                                 class="h-full w-full object-cover"
                                 id="photo-preview">
                        @else
                            <div class="h-full w-full flex items-center justify-center text-gray-400" id="photo-placeholder">
                                <i class="fas fa-user text-2xl"></i>
                            </div>
                            <img src="" alt="Aperçu" class="h-full w-full object-cover hidden" id="photo-preview">
                        @endif
                    </div>

                    <div class="flex-1 space-y-2">
                        <input type="file"
                               name="profile_photo"
                               id="profile_photo"
                               accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                               class="block w-full text-sm text-gray-600
                                      file:mr-3 file:py-2 file:px-4 file:border-0
                                      file:text-sm file:font-semibold file:bg-navy file:text-white
                                      hover:file:bg-orange-500
                                      {{ $errors->has('profile_photo') ? 'text-red-600' : '' }}">
                        @error('profile_photo')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="{{ $btnClass }}">
                        Mettre à jour
                    </button>
                </div>
            </form>
        </section>

        <script>
            document.getElementById('profile_photo')?.addEventListener('change', function (e) {
                const file = e.target.files?.[0];
                const preview = document.getElementById('photo-preview');
                const placeholder = document.getElementById('photo-placeholder');
                if (!file || !preview) return;
                if (file.size > 5 * 1024 * 1024) {
                    alert('Le fichier dépasse 5 Mo. Choisissez une image plus légère.');
                    e.target.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = (ev) => {
                    preview.src = ev.target.result;
                    preview.classList.remove('hidden');
                    placeholder?.classList.add('hidden');
                };
                reader.readAsDataURL(file);
            });
        </script>

        <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-2">
                <span class="text-3xl text-navy flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-id-card" aria-hidden="true"></i>
                </span>
                <h2 class="text-xl font-bold text-navy">Informations personnelles</h2>
            </div>
            <p class="text-sm text-gray-500 mb-4">Les champs verrouillés ne peuvent pas être modifiés ici.</p>

            @if (session('status') === 'profile-updated')
                <p class="mb-4 text-sm text-green-700">Informations mises à jour avec succès.</p>
            @endif

            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PATCH')

                <input type="hidden" name="name" value="{{ old('name', $user->name) }}">
                <input type="hidden" name="email" value="{{ old('email', $user->email) }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if ($isInstitution)
                        <div>
                            <label for="name_display" class="block text-sm font-medium text-gray-700 mb-1">
                                Nom <span class="text-gray-400 text-xs">(non modifiable)</span>
                            </label>
                            <input type="text" id="name_display"
                                   value="{{ old('name', $user->name) }}"
                                   disabled
                                   class="{{ $lockedClass }}">
                        </div>
                    @else
                        <div>
                            <label for="firstname" class="block text-sm font-medium text-gray-700 mb-1">
                                Prénom <span class="text-gray-400 text-xs">(non modifiable)</span>
                            </label>
                            <input type="text" id="firstname"
                                   value="{{ old('firstname', $user->firstname) }}"
                                   disabled
                                   class="{{ $lockedClass }}">
                        </div>
                        <div>
                            <label for="lastname" class="block text-sm font-medium text-gray-700 mb-1">
                                Nom de famille <span class="text-gray-400 text-xs">(non modifiable)</span>
                            </label>
                            <input type="text" id="lastname"
                                   value="{{ old('lastname', $user->lastname) }}"
                                   disabled
                                   class="{{ $lockedClass }}">
                        </div>
                    @endif

                    <div>
                        <label for="email_display" class="block text-sm font-medium text-gray-700 mb-1">
                            Email <span class="text-gray-400 text-xs">(non modifiable)</span>
                        </label>
                        <input type="email" id="email_display"
                               value="{{ old('email', $user->email) }}"
                               disabled
                               class="{{ $lockedClass }}">
                    </div>

                    <div>
                        <label for="nif" class="block text-sm font-medium text-gray-700 mb-1">NIF</label>
                        <input type="text" name="nif" id="nif"
                               value="{{ old('nif', $user->nif) }}"
                               placeholder="ex: 809-062-525-6"
                               class="{{ $inputClass }} @error('nif') border-red-500 @enderror">
                        @error('nif')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    @unless ($isInstitution)
                        <div>
                            <label for="ninu" class="block text-sm font-medium text-gray-700 mb-1">NINU</label>
                            <input type="text" name="ninu" id="ninu"
                                   value="{{ old('ninu', $user->ninu) }}"
                                   class="{{ $inputClass }} @error('ninu') border-red-500 @enderror">
                            @error('ninu')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endunless

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                        <input type="text" name="phone" id="phone"
                               value="{{ old('phone', $user->phone) }}"
                               placeholder="+509XXXXXXXX"
                               class="{{ $inputClass }} @error('phone') border-red-500 @enderror">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="sexe" class="block text-sm font-medium text-gray-700 mb-1">
                            Sexe <span class="text-gray-400 text-xs">(non modifiable)</span>
                        </label>
                        <input type="text" id="sexe"
                               value="{{ old('sexe', optional($user->gender)->name) }}"
                               disabled
                               class="{{ $lockedClass }}">
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="{{ $btnClass }}">
                        Mettre à jour
                    </button>
                </div>
            </form>
        </section>

        <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-2">
                <span class="text-3xl text-navy flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-lock" aria-hidden="true"></i>
                </span>
                <h2 class="text-xl font-bold text-navy">Mot de passe</h2>
            </div>
            <p class="text-sm text-gray-500 mb-4">Utilisez un mot de passe long pour sécuriser votre compte.</p>

            @if (session('status') === 'password-updated')
                <p class="mb-4 text-sm text-green-700">Mot de passe mis à jour avec succès.</p>
            @endif

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2 max-w-xl">
                        <label for="update_password_current_password" class="block text-sm font-medium text-gray-700 mb-1">
                            Mot de passe actuel
                        </label>
                        <input type="password"
                               name="current_password"
                               id="update_password_current_password"
                               autocomplete="current-password"
                               class="{{ $inputClass }} @error('current_password', 'updatePassword') border-red-500 @enderror">
                        @error('current_password', 'updatePassword')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="update_password_password" class="block text-sm font-medium text-gray-700 mb-1">
                            Nouveau mot de passe
                        </label>
                        <input type="password"
                               name="password"
                               id="update_password_password"
                               autocomplete="new-password"
                               class="{{ $inputClass }} @error('password', 'updatePassword') border-red-500 @enderror">
                        @error('password', 'updatePassword')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="update_password_password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                            Confirmer le mot de passe
                        </label>
                        <input type="password"
                               name="password_confirmation"
                               id="update_password_password_confirmation"
                               autocomplete="new-password"
                               class="{{ $inputClass }}">
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="{{ $btnClass }}">
                        Mettre à jour
                    </button>
                </div>
            </form>
        </section>

    </div>
</div>
@endsection
