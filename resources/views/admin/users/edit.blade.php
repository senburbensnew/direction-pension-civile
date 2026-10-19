@extends('layouts.admin')

@section('title', 'Modifier ' . $user->name)

@section('breadcrumb')
    <a href="{{ route('admin.users.index') }}" class="text-gray-500 hover:text-gray-800">Utilisateurs</a>
    <i class="fas fa-chevron-right text-xs text-gray-300 mx-1"></i>
    <span class="text-gray-700">{{ $user->name }}</span>
@endsection

@php
    $field = fn(string $name) => 'w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400 ' . ($errors->has($name) ? 'border-red-400' : 'border-gray-200');
@endphp

@section('content')
<div class="site-gutter space-y-5">

    {{-- Page header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0 overflow-hidden">
                @if($user->profile_photo)
                    <img src="{{ asset('storage/' . $user->profile_photo) }}" class="w-11 h-11 object-cover">
                @else
                    <span class="text-indigo-600 font-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                @endif
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-800">{{ $user->name }}</h1>
                <p class="text-sm text-gray-500">{{ $user->email }}</p>
            </div>
        </div>
        <a href="{{ route('admin.users.index') }}"
            class="inline-flex items-center gap-1.5 px-3 py-2 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
            <i class="fas fa-arrow-left text-xs"></i> Retour
        </a>
    </div>

    @include('admin.sessions._user-panel', [
        'user' => $user,
        'sessions' => $userActiveSessions,
        'manageable' => $sessionsManageable,
    ])

    @include('admin.sessions._connection-trace', [
        'user' => $user,
        'timeline' => $connectionTimeline,
    ])

    @if(auth()->id() !== $user->id && filled($user->email))
        <div class="bg-white rounded-xl border border-amber-200 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-amber-100 flex items-center gap-2 bg-amber-50">
                <i class="fas fa-key text-amber-600 text-sm w-4 text-center"></i>
                <span class="text-xs font-semibold text-amber-900 uppercase tracking-wider">Mot de passe oublié</span>
            </div>
            <div class="p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <p class="text-sm text-gray-600 max-w-2xl">
                    Générer un mot de passe temporaire, l’envoyer à <span class="font-medium text-gray-800">{{ $user->email }}</span>
                    et obliger l’utilisateur à le changer à la prochaine connexion. Ses sessions actives seront fermées.
                </p>
                <form
                    action="{{ route('admin.users.reset-password', $user) }}"
                    method="POST"
                    onsubmit="return confirm('Confirmer la réinitialisation et l’envoi du mot de passe par e-mail ?');"
                    class="flex-shrink-0"
                >
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <i class="fas fa-envelope text-xs"></i>
                        Réinitialiser et envoyer par e-mail
                    </button>
                </form>
            </div>
        </div>
    @endif

    {{-- Validation errors --}}
    @if($errors->any())
        <div class="flex items-start gap-2 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">
            <i class="fas fa-exclamation-circle text-red-400 mt-0.5 flex-shrink-0"></i>
            <ul class="list-disc pl-3 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-5">
        @csrf
        @method('PUT')

        {{-- Identité --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 flex items-center gap-2 bg-gray-50">
                <i class="fas fa-user text-indigo-500 text-sm w-4 text-center"></i>
                <span class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Identité</span>
            </div>
            @php($identityUser = $user)
            @include('admin.users._identity-grid')
        </div>

        {{-- Contact & Accès --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 flex items-center gap-2 bg-gray-50">
                <i class="fas fa-envelope text-indigo-500 text-sm w-4 text-center"></i>
                <span class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Contact &amp; accès</span>
            </div>
            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Adresse e-mail <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                        class="{{ $field('email') }}">
                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nom d’utilisateur</label>
                    <input type="text" name="username" value="{{ old('username', $user->username) }}"
                        class="{{ $field('username') }}" placeholder="jean.dupont" autocomplete="username">
                    @error('username')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <x-telephone-input
                        name="phone"
                        :value="$user->phone"
                        label="Téléphone"
                        :required="true"
                        label-class="block text-xs font-medium text-gray-600 mb-1"
                        hint-class="text-xs text-gray-500 mt-1"
                        error-class="text-red-500 text-xs mt-1"
                        client-error-class="text-red-500 text-xs mt-1"
                        :input-class="$field('phone')"
                    />
                </div>
                <div></div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Nouveau mot de passe
                        <span class="text-gray-400 font-normal">(laisser vide pour ne pas changer)</span>
                    </label>
                    <input type="password" name="password" minlength="8"
                        class="{{ $field('password') }}" placeholder="Min. 8 caractères">
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Confirmer le mot de passe</label>
                    <input type="password" name="password_confirmation"
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400"
                        placeholder="Répétez le mot de passe">
                </div>
            </div>
        </div>

        {{-- Profil système --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 flex items-center gap-2 bg-gray-50">
                <i class="fas fa-sliders-h text-indigo-500 text-sm w-4 text-center"></i>
                <span class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Profil système</span>
            </div>
            <div
                class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4"
                x-data="{ accountType: @js($accountCategory ?? '') }"
            >
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Type de compte</label>
                    <select name="primary_category" x-model="accountType" class="{{ $field('primary_category') }}">
                        <option value="">— Déduire des rôles et du service —</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->code }}" {{ $accountCategory === $category->code ? 'selected' : '' }}>
                                {{ $category->label }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Un seul type parmi Pensionné, Fonctionnaire, etc. Les droits viennent des rôles (plusieurs possibles).</p>
                    @error('primary_category')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div x-show="accountType === 'institution'" x-cloak x-transition>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Institution rattachée <span class="text-red-500">*</span>
                    </label>
                    <select
                        name="institution_id"
                        x-ref="institutionSelect"
                        :disabled="accountType !== 'institution'"
                        :required="accountType === 'institution'"
                        class="{{ $field('institution_id') }}"
                    >
                        <option value="">— Choisir une institution —</option>
                        @foreach($institutions as $institution)
                            <option value="{{ $institution->id }}" {{ (string) old('institution_id', $user->institution_id) === (string) $institution->id ? 'selected' : '' }}>
                                {{ $institution->raison_sociale }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Un compte institution est lié à une seule institution.</p>
                    @error('institution_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div x-show="accountType === 'personnel_dpc' || accountType === ''" x-cloak x-transition>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Service</label>
                    <select
                        name="service_id"
                        :disabled="accountType !== 'personnel_dpc' && accountType !== ''"
                        class="{{ $field('service_id') }}"
                    >
                        <option value="">— Aucun service —</option>
                        @foreach(\App\Models\Service::orderBy('nom')->get() as $service)
                            <option value="{{ $service->id }}" {{ old('service_id', $user->service_id) == $service->id ? 'selected' : '' }}>
                                {{ $service->nom }}
                            </option>
                        @endforeach
                    </select>
                    @error('service_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <x-form.dual-listbox
                    name="roles[]"
                    label="Rôles"
                    available-title="Rôles disponibles"
                    selected-title="Rôles attribués à l'utilisateur"
                    hint="Sélectionnez un ou plusieurs rôles à gauche, puis utilisez les flèches pour les attribuer."
                    :options="$roleOptions"
                    :selected="$assignedRoles"
                />
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">NIF</label>
                    <input type="text" name="nif" value="{{ old('nif', $user->nif) }}"
                        class="{{ $field('nif') }}" placeholder="123-456-789-0"
                        pattern="\d{3}-\d{3}-\d{3}-\d{1}" title="Format 000-000-000-0">
                    <p class="text-xs text-gray-400 mt-1">Format : 000-000-000-0</p>
                    @error('nif')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">NINU</label>
                    <input type="text" name="ninu" value="{{ old('ninu', $user->ninu) }}"
                        class="{{ $field('ninu') }}" placeholder="1148519577"
                        pattern="\d{10}" inputmode="numeric" title="10 chiffres, sans tiret">
                    <p class="text-xs text-gray-400 mt-1">10 chiffres, sans tiret.</p>
                    @error('ninu')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.users.index') }}"
                    class="px-4 py-2 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    Annuler
                </a>
                <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                    <i class="fas fa-save text-xs"></i> Enregistrer
                </button>
            </div>
        </div>

    </form>
</div>
@endsection
