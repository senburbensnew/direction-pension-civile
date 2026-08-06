@extends('layouts.main')

@section('title', 'Mon profil')

@section('content')
@php
    $user = auth()->user();
    $isInstitution = $user->hasRole('institution');
    $displayName = $isInstitution
        ? ($user->name ?: 'Institution')
        : (trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? '')) ?: ($user->name ?: 'Utilisateur'));
@endphp

<div class="py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Mon profil</h1>
            <p class="mt-1 text-sm text-gray-600">{{ $displayName }} · {{ $user->email }}</p>
        </div>

        {{-- Photo --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">Photo de profil</h2>
            <p class="text-sm text-gray-500 mb-4">JPEG ou PNG · max. 5 Mo</p>

            @if (session('status') === 'profile-photo-updated')
                <p class="mb-4 text-sm text-green-600">Photo mise à jour avec succès.</p>
            @endif

            <form method="POST" action="{{ route('profile.profile-photo.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <div class="flex flex-col sm:flex-row sm:items-center gap-5">
                    <div class="h-20 w-20 rounded-full bg-gray-100 border border-gray-200 overflow-hidden flex items-center justify-center shrink-0">
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
                                      file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0
                                      file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700
                                      hover:file:bg-blue-100
                                      {{ $errors->has('profile_photo') ? 'text-red-600' : '' }}">
                        @error('profile_photo')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-5 flex justify-end">
                    <button type="submit"
                            class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                        Mettre à jour
                    </button>
                </div>
            </form>
        </div>

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

        {{-- Informations --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">Informations personnelles</h2>
            <p class="text-sm text-gray-500 mb-4">Les champs verrouillés ne peuvent pas être modifiés ici.</p>

            @if (session('status') === 'profile-updated')
                <p class="mb-4 text-sm text-green-600">Informations mises à jour avec succès.</p>
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
                                   class="w-full rounded-lg border-gray-300 bg-gray-100 text-gray-600 cursor-not-allowed shadow-sm">
                        </div>
                    @else
                        <div>
                            <label for="firstname" class="block text-sm font-medium text-gray-700 mb-1">
                                Prénom <span class="text-gray-400 text-xs">(non modifiable)</span>
                            </label>
                            <input type="text" id="firstname"
                                   value="{{ old('firstname', $user->firstname) }}"
                                   disabled
                                   class="w-full rounded-lg border-gray-300 bg-gray-100 text-gray-600 cursor-not-allowed shadow-sm">
                        </div>
                        <div>
                            <label for="lastname" class="block text-sm font-medium text-gray-700 mb-1">
                                Nom de famille <span class="text-gray-400 text-xs">(non modifiable)</span>
                            </label>
                            <input type="text" id="lastname"
                                   value="{{ old('lastname', $user->lastname) }}"
                                   disabled
                                   class="w-full rounded-lg border-gray-300 bg-gray-100 text-gray-600 cursor-not-allowed shadow-sm">
                        </div>
                    @endif

                    <div>
                        <label for="email_display" class="block text-sm font-medium text-gray-700 mb-1">
                            Email <span class="text-gray-400 text-xs">(non modifiable)</span>
                        </label>
                        <input type="email" id="email_display"
                               value="{{ old('email', $user->email) }}"
                               disabled
                               class="w-full rounded-lg border-gray-300 bg-gray-100 text-gray-600 cursor-not-allowed shadow-sm">
                    </div>

                    <div>
                        <label for="nif" class="block text-sm font-medium text-gray-700 mb-1">NIF</label>
                        <input type="text" name="nif" id="nif"
                               value="{{ old('nif', $user->nif) }}"
                               placeholder="ex: 809-062-525-6"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('nif') border-red-500 @enderror">
                        @error('nif')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    @unless ($isInstitution)
                        <div>
                            <label for="ninu" class="block text-sm font-medium text-gray-700 mb-1">NINU</label>
                            <input type="text" name="ninu" id="ninu"
                                   value="{{ old('ninu', $user->ninu) }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('ninu') border-red-500 @enderror">
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
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('phone') border-red-500 @enderror">
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
                               class="w-full rounded-lg border-gray-300 bg-gray-100 text-gray-600 cursor-not-allowed shadow-sm">
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit"
                            class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                        Mettre à jour
                    </button>
                </div>
            </form>
        </div>

        {{-- Mot de passe --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">Mot de passe</h2>
            <p class="text-sm text-gray-500 mb-4">Utilisez un mot de passe long pour sécuriser votre compte.</p>

            @if (session('status') === 'password-updated')
                <p class="mb-4 text-sm text-green-600">Mot de passe mis à jour avec succès.</p>
            @endif

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                @method('PUT')

                <div class="space-y-4 max-w-xl">
                    <div>
                        <label for="update_password_current_password" class="block text-sm font-medium text-gray-700 mb-1">
                            Mot de passe actuel
                        </label>
                        <input type="password"
                               name="current_password"
                               id="update_password_current_password"
                               autocomplete="current-password"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('current_password', 'updatePassword') border-red-500 @enderror">
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
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('password', 'updatePassword') border-red-500 @enderror">
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
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit"
                            class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                        Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
