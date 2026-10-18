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
    $profileFormId = 'profile-update-form';
    $canComplete = fn (string $attribute) => $profileCompletion->isCompletable($user, $attribute);
    $canEdit = fn (string $attribute) => $profileCompletion->canEdit($user, $attribute);
    $personalCompletableAttributes = $isInstitution
        ? ['name', 'birth_date', 'gender_id', 'civil_status_id', 'address', 'username']
        : ['firstname', 'lastname', 'birth_date', 'gender_id', 'civil_status_id', 'address', 'username'];
    $hasPersonalCompletable = collect($personalCompletableAttributes)->contains(
        fn (string $attribute) => $canEdit($attribute)
    );
    $profileFormError = $errors->getBag('default')->first();
@endphp

<style>
    .card-shadow { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
</style>

<x-portal-page bottom="pb-10" class="space-y-8">
        @if ($profileFormError)
            <x-portal-toast type="error" :message="$profileFormError" />
        @endif

        <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-2">
                <span class="text-3xl text-navy flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-camera" aria-hidden="true"></i>
                </span>
                <h2 class="text-xl font-bold text-navy">Photo de profil</h2>
            </div>
            <p class="text-sm text-gray-500 mb-4">JPEG ou PNG · max. 5 Mo</p>

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

        <x-push-vite :js="['resources/js/pages/auth-profile-photo.js']" />

        <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-2">
                <span class="text-3xl text-navy flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-id-card" aria-hidden="true"></i>
                </span>
                <h2 class="text-xl font-bold text-navy">Informations personnelles</h2>
            </div>
            <p class="text-sm text-gray-500 mb-6">
                État civil et adresse restent modifiables. Les autres champs déjà renseignés sont en lecture seule.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if ($isInstitution)
                    <div class="md:col-span-2">
                        <label for="profile_name" class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                        @if ($canComplete('name'))
                            <input type="text" name="name" id="profile_name" form="{{ $profileFormId }}"
                                   value="{{ old('name', $user->name) }}"
                                   autocomplete="organization"
                                   class="{{ $inputClass }} {{ $errors->has('name') ? 'border-red-500' : '' }}">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        @else
                            <input type="text" value="{{ $user->name ?: $empty }}" disabled class="{{ $lockedClass }}">
                        @endif
                    </div>
                @else
                    <div>
                        <label for="profile_firstname" class="block text-sm font-medium text-gray-700 mb-1">Prénom</label>
                        @if ($canComplete('firstname'))
                            <input type="text" name="firstname" id="profile_firstname" form="{{ $profileFormId }}"
                                   value="{{ old('firstname', $user->firstname) }}"
                                   autocomplete="given-name"
                                   class="{{ $inputClass }} {{ $errors->has('firstname') ? 'border-red-500' : '' }}">
                            @error('firstname')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        @else
                            <input type="text" value="{{ $user->firstname ?: $empty }}" disabled class="{{ $lockedClass }}">
                        @endif
                    </div>
                    <div>
                        <label for="profile_lastname" class="block text-sm font-medium text-gray-700 mb-1">Nom de famille</label>
                        @if ($canComplete('lastname'))
                            <input type="text" name="lastname" id="profile_lastname" form="{{ $profileFormId }}"
                                   value="{{ old('lastname', $user->lastname) }}"
                                   autocomplete="family-name"
                                   class="{{ $inputClass }} {{ $errors->has('lastname') ? 'border-red-500' : '' }}">
                            @error('lastname')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        @else
                            <input type="text" value="{{ $user->lastname ?: $empty }}" disabled class="{{ $lockedClass }}">
                        @endif
                    </div>
                @endif

                <div>
                    <label for="profile_birth_date" class="block text-sm font-medium text-gray-700 mb-1">Date de naissance</label>
                    @if ($canComplete('birth_date'))
                        <input type="date" name="birth_date" id="profile_birth_date" form="{{ $profileFormId }}"
                               value="{{ old('birth_date', $user->birth_date?->format('Y-m-d')) }}"
                               class="{{ $inputClass }} {{ $errors->has('birth_date') ? 'border-red-500' : '' }}">
                        @error('birth_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    @else
                        <input type="text" value="{{ $fmtDate($user->birth_date) }}" disabled class="{{ $lockedClass }}">
                    @endif
                </div>
                <div>
                    <label for="profile_gender_id" class="block text-sm font-medium text-gray-700 mb-1">Sexe</label>
                    @if ($canComplete('gender_id'))
                        <select name="gender_id" id="profile_gender_id" form="{{ $profileFormId }}"
                                class="{{ $inputClass }} {{ $errors->has('gender_id') ? 'border-red-500' : '' }}">
                            <option value="">— Choisir —</option>
                            @foreach ($genders as $gender)
                                <option value="{{ $gender->id }}" @selected((string) old('gender_id', $user->gender_id) === (string) $gender->id)>
                                    {{ $gender->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('gender_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    @else
                        <input type="text" value="{{ $user->gender?->name ?: $empty }}" disabled class="{{ $lockedClass }}">
                    @endif
                </div>
                <div>
                    <label for="profile_civil_status_id" class="block text-sm font-medium text-gray-700 mb-1">État civil</label>
                    <select name="civil_status_id" id="profile_civil_status_id" form="{{ $profileFormId }}"
                            class="{{ $inputClass }} {{ $errors->has('civil_status_id') ? 'border-red-500' : '' }}">
                        <option value="">— Choisir —</option>
                        @foreach ($civilStatuses as $status)
                            <option value="{{ $status->id }}" @selected((string) old('civil_status_id', $user->civil_status_id) === (string) $status->id)>
                                {{ $status->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('civil_status_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="profile_username" class="block text-sm font-medium text-gray-700 mb-1">Nom d’utilisateur</label>
                    @if ($canComplete('username'))
                        <input type="text" name="username" id="profile_username" form="{{ $profileFormId }}"
                               value="{{ old('username', $user->username) }}"
                               autocomplete="username"
                               class="{{ $inputClass }} {{ $errors->has('username') ? 'border-red-500' : '' }}">
                        @error('username')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    @else
                        <input type="text" value="{{ $user->username ?: $empty }}" disabled class="{{ $lockedClass }}">
                    @endif
                </div>
                <div class="md:col-span-2">
                    <label for="profile_address" class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                    <input type="text" name="address" id="profile_address" form="{{ $profileFormId }}"
                           value="{{ old('address', $user->address) }}"
                           autocomplete="street-address"
                           class="{{ $inputClass }} {{ $errors->has('address') ? 'border-red-500' : '' }}">
                    @error('address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
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

            @if ($hasPersonalCompletable)
                <div class="mt-6 flex justify-end">
                    <button type="submit" form="{{ $profileFormId }}" class="{{ $btnClass }}">
                        Mettre à jour
                    </button>
                </div>
            @endif
        </section>

        <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-2">
                <span class="text-3xl text-navy flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                </span>
                <h2 class="text-xl font-bold text-navy">Coordonnées modifiables</h2>
            </div>
            <p class="text-sm text-gray-500 mb-4">
                Le téléphone reste modifiable. Les autres champs déjà renseignés (e-mail, NIF, NINU) sont en lecture seule.
            </p>

            <form method="POST" action="{{ route('profile.update') }}" id="{{ $profileFormId }}">
                @csrf
                @method('PATCH')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Adresse e-mail</label>
                        @if ($canComplete('email'))
                            <input type="email" name="email" id="email"
                                   value="{{ old('email', $user->email) }}"
                                   autocomplete="email"
                                   placeholder="exemple@gmail.com"
                                   class="{{ $inputClass }} {{ $errors->has('email') ? 'border-red-500' : '' }}">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        @else
                            <input type="text" value="{{ $user->email ?: $empty }}" disabled class="{{ $lockedClass }}">
                        @endif
                    </div>

                    <div>
                        <label for="nif" class="block text-sm font-medium text-gray-700 mb-1">NIF</label>
                        @if ($canComplete('nif'))
                            <input type="text" name="nif" id="nif"
                                   value="{{ old('nif', $user->nif) }}"
                                   placeholder="ex: 809-062-525-6"
                                   class="{{ $inputClass }} {{ $errors->has('nif') ? 'border-red-500' : '' }}">
                            @error('nif')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        @else
                            <input type="text" value="{{ $user->nif ?: $empty }}" disabled class="{{ $lockedClass }}">
                        @endif
                    </div>

                    @unless ($isInstitution)
                        <div>
                            <label for="ninu" class="block text-sm font-medium text-gray-700 mb-1">NINU</label>
                            @if ($canComplete('ninu'))
                                <input type="text" name="ninu" id="ninu"
                                       value="{{ old('ninu', $user->ninu) }}"
                                       class="{{ $inputClass }} {{ $errors->has('ninu') ? 'border-red-500' : '' }}">
                                @error('ninu')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            @else
                                <input type="text" value="{{ $user->ninu ?: $empty }}" disabled class="{{ $lockedClass }}">
                            @endif
                        </div>
                    @endunless

                    <div>
                        <x-telephone-input
                            name="phone"
                            id="phone"
                            :value="$user->phone"
                            :input-class="$inputClass.($errors->has('phone') ? ' border-red-500' : '')"
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

        <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-2">
                <span class="text-3xl text-navy flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-lock" aria-hidden="true"></i>
                </span>
                <h2 class="text-xl font-bold text-navy">Mot de passe</h2>
            </div>
            <p class="text-sm text-gray-500 mb-4">Utilisez un mot de passe long pour sécuriser votre compte.</p>

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
