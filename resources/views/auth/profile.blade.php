<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Mon profil
        </h2>
    </x-slot>

@php
    $user = $user ?? auth()->user();
    $isInstitution = $user->hasRole('institution');
    $inputClass = 'w-full rounded-none border-gray-300 shadow-sm focus:border-navy focus:ring-navy';
    $lockedClass = 'w-full rounded-none border-gray-300 bg-gray-100 text-gray-600 cursor-not-allowed shadow-sm';
    $btnClass = 'px-5 py-2.5 bg-navy hover:bg-orange-500 text-white text-sm font-semibold transition-colors';
    $empty = '—';
    $fmtDate = function ($d) use ($empty) {
        if (blank($d)) {
            return $empty;
        }
        return $d instanceof \DateTimeInterface
            ? $d->format('d/m/Y')
            : \Illuminate\Support\Carbon::parse($d)->format('d/m/Y');
    };
    $fmtDateTime = function ($d) use ($empty) {
        if (blank($d)) {
            return $empty;
        }
        return $d instanceof \DateTimeInterface
            ? $d->format('d/m/Y H:i')
            : \Illuminate\Support\Carbon::parse($d)->format('d/m/Y H:i');
    };
    $accountStatus = $user->resolvedAccountStatus();
@endphp

<style>
    .card-shadow { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
</style>

<x-portal-page bottom="pb-10" class="space-y-8">
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
                        <x-file-input
                            name="profile_photo"
                            id="profile_photo"
                            accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                        />
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
            <p class="text-sm text-gray-500 mb-6">Données enregistrées sur votre compte (lecture seule).</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if ($isInstitution)
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                        <input type="text" value="{{ $user->name ?: $empty }}" disabled class="{{ $lockedClass }}">
                    </div>
                @else
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prénom</label>
                        <input type="text" value="{{ $user->firstname ?: $empty }}" disabled class="{{ $lockedClass }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom de famille</label>
                        <input type="text" value="{{ $user->lastname ?: $empty }}" disabled class="{{ $lockedClass }}">
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date de naissance</label>
                    <input type="text" value="{{ $fmtDate($user->birth_date) }}" disabled class="{{ $lockedClass }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sexe</label>
                    <input type="text" value="{{ $user->gender?->name ?: $empty }}" disabled class="{{ $lockedClass }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">État civil</label>
                    <input type="text" value="{{ $user->civilStatus?->name ?: $empty }}" disabled class="{{ $lockedClass }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Statut du compte</label>
                    <input type="text" value="{{ $accountStatus->label() }}" disabled class="{{ $lockedClass }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="text" value="{{ $user->email ?: $empty }}" disabled class="{{ $lockedClass }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email vérifié le</label>
                    <input type="text"
                           value="{{ $user->email_verified_at ? $fmtDateTime($user->email_verified_at) : 'Non vérifié' }}"
                           disabled
                           class="{{ $lockedClass }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom d’utilisateur</label>
                    <input type="text" value="{{ $user->username ?: $empty }}" disabled class="{{ $lockedClass }}">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                    <input type="text" value="{{ $user->address ?: $empty }}" disabled class="{{ $lockedClass }}">
                </div>

                @if ($user->service_id)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Service</label>
                        <input type="text" value="{{ $user->service?->nom ?: $empty }}" disabled class="{{ $lockedClass }}">
                    </div>
                @endif

                @if ($user->institution_id)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Institution</label>
                        <input type="text" value="{{ $user->institution?->displayName() ?: $empty }}" disabled class="{{ $lockedClass }}">
                    </div>
                @endif

                @if ($user->signature)
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Signature</label>
                        <div class="p-3 border border-gray-200 bg-gray-50 inline-block">
                            <img src="{{ asset('storage/' . $user->signature) }}"
                                 alt="Signature enregistrée"
                                 class="max-h-24 max-w-xs object-contain">
                        </div>
                    </div>
                @endif
            </div>
        </section>

        <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-2">
                <span class="text-3xl text-navy flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                </span>
                <h2 class="text-xl font-bold text-navy">Coordonnées modifiables</h2>
            </div>
            <p class="text-sm text-gray-500 mb-4">Vous pouvez compléter ou mettre à jour votre adresse e-mail, NIF, NINU et téléphone ci-dessous.</p>

            @if (session('status') === 'profile-updated')
                <p class="mb-4 text-sm text-green-700">Informations mises à jour avec succès.</p>
            @endif

            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PATCH')

                <input type="hidden" name="name" value="{{ old('name', $user->name) }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Adresse e-mail</label>
                        <input type="email" name="email" id="email"
                               value="{{ old('email', $user->email) }}"
                               autocomplete="email"
                               placeholder="exemple@gmail.com"
                               class="{{ $inputClass }} {{ $errors->has('email') ? 'border-red-500' : '' }}">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="nif" class="block text-sm font-medium text-gray-700 mb-1">NIF</label>
                        <input type="text" name="nif" id="nif"
                               value="{{ old('nif', $user->nif) }}"
                               placeholder="ex: 809-062-525-6"
                               class="{{ $inputClass }} {{ $errors->has('nif') ? 'border-red-500' : '' }}">
                        @error('nif')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    @unless ($isInstitution)
                        <div>
                            <label for="ninu" class="block text-sm font-medium text-gray-700 mb-1">NINU</label>
                            <input type="text" name="ninu" id="ninu"
                                   value="{{ old('ninu', $user->ninu) }}"
                                   class="{{ $inputClass }} {{ $errors->has('ninu') ? 'border-red-500' : '' }}">
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
                               class="{{ $inputClass }} {{ $errors->has('phone') ? 'border-red-500' : '' }}">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
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
                               class="{{ $inputClass }} {{ $errors->updatePassword->has('current_password') ? 'border-red-500' : '' }}">
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
                               class="{{ $inputClass }} {{ $errors->updatePassword->has('password') ? 'border-red-500' : '' }}">
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

</x-portal-page>
</x-app-layout>
