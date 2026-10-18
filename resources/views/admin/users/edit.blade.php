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

    {{-- Flash success --}}
    @include('admin.sessions._user-panel', [
        'user' => $user,
        'sessions' => $userActiveSessions,
        'manageable' => $sessionsManageable,
    ])

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
            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Prénom</label>
                    <input type="text" name="firstname" value="{{ old('firstname', $user->firstname) }}"
                        class="{{ $field('firstname') }}" placeholder="Jean">
                    @error('firstname')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nom de famille</label>
                    <input type="text" name="lastname" value="{{ old('lastname', $user->lastname) }}"
                        class="{{ $field('lastname') }}" placeholder="Dupont">
                    @error('lastname')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Nom d'affichage <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                        class="{{ $field('name') }}">
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Sexe</label>
                    <select name="gender_id" class="{{ $field('gender_id') }}">
                        <option value="">— Sélectionner —</option>
                        @foreach($genders as $gender)
                            <option value="{{ $gender->id }}"
                                {{ (string) old('gender_id', $user->gender_id) === (string) $gender->id ? 'selected' : '' }}>
                                {{ $gender->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('gender_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
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
            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                @php
                    $assignedCategories = old('category_codes', $user->categoryAssignments->pluck('user_category_code')->all());
                    $primaryCategory = old('primary_category', $user->categoryAssignments->firstWhere('is_primary', true)?->user_category_code);
                @endphp
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-2">Catégories de compte</label>
                    <div class="flex flex-wrap gap-3">
                        @foreach($categories as $category)
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="category_codes[]" value="{{ $category->code }}"
                                    {{ in_array($category->code, $assignedCategories, true) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-400">
                                {{ $category->label }}
                            </label>
                        @endforeach
                    </div>
                    @error('category_codes')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Catégorie principale</label>
                    <select name="primary_category" class="{{ $field('primary_category') }}">
                        <option value="">— Automatique —</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->code }}" {{ $primaryCategory === $category->code ? 'selected' : '' }}>
                                {{ $category->label }}
                            </option>
                        @endforeach
                    </select>
                    @error('primary_category')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Institution (compte unique)</label>
                    <select name="institution_id" class="{{ $field('institution_id') }}">
                        <option value="">— Aucune —</option>
                        @foreach($institutions as $institution)
                            <option value="{{ $institution->id }}" {{ (string) old('institution_id', $user->institution_id) === (string) $institution->id ? 'selected' : '' }}>
                                {{ $institution->raison_sociale }}
                            </option>
                        @endforeach
                    </select>
                    @error('institution_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Service</label>
                    <select name="service_id" class="{{ $field('service_id') }}">
                        <option value="">— Aucun service —</option>
                        @foreach(\App\Models\Service::orderBy('nom')->get() as $service)
                            <option value="{{ $service->id }}" {{ old('service_id', $user->service_id) == $service->id ? 'selected' : '' }}>
                                {{ $service->nom }}
                            </option>
                        @endforeach
                    </select>
                    @error('service_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Rôle</label>
                    <select name="role" class="{{ $field('role') }}">
                        <option value="">— Aucun rôle —</option>
                        @foreach(\Spatie\Permission\Models\Role::orderBy('name')->get() as $role)
                            <option value="{{ $role->name }}"
                                {{ old('role', $user->roles->first()?->name) === $role->name ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Code pension</label>
                    <input type="text" name="pension_code" value="{{ old('pension_code', $user->displayPensionCode()) }}"
                        class="{{ $field('pension_code') }}" placeholder="Ex : PEN-00001">
                    @error('pension_code')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">NIF</label>
                    <input type="text" name="nif" value="{{ old('nif', $user->nif) }}"
                        class="{{ $field('nif') }}" placeholder="Numéro d'identification fiscale">
                    @error('nif')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">NINU</label>
                    <input type="text" name="ninu" value="{{ old('ninu', $user->ninu) }}"
                        class="{{ $field('ninu') }}" placeholder="Numéro d'identification nationale unique">
                    @error('ninu')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between">
            {{-- Delete button --}}
            @if($user->id !== auth()->id())
                <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                    onsubmit="return confirm('Supprimer {{ addslashes($user->name) }} ? Cette action est irréversible.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors">
                        <i class="fas fa-trash text-xs"></i> Supprimer ce compte
                    </button>
                </form>
            @else
                <div></div>
            @endif

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

    {{-- Liens bénéficiaire (représentant / mandataire) --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 flex items-center gap-2 bg-gray-50">
            <i class="fas fa-link text-indigo-500 text-sm w-4 text-center"></i>
            <span class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Liens pension (bénéficiaires)</span>
        </div>
        <div class="p-5 space-y-4">
            <p class="text-xs text-gray-500">
                Accès au portail pension sans catégorie « pensionné » : représentant légal ou mandataire rattaché à un dossier.
            </p>

            @if($user->beneficiaireLinks->isNotEmpty())
                <ul class="divide-y divide-gray-100 border border-gray-100 rounded-lg">
                    @foreach($user->beneficiaireLinks as $link)
                        @php
                            $codes = $link->beneficiaire?->affiliations?->pluck('code_pension')->filter()->unique()->join(', ') ?: '—';
                        @endphp
                        <li class="flex items-center justify-between gap-3 px-4 py-3 text-sm">
                            <div>
                                <span class="font-medium text-gray-800">
                                    {{ $link->relation?->label() ?? '—' }}
                                </span>
                                <span class="text-gray-500"> — Bénéficiaire #{{ $link->beneficiaire_id }}</span>
                                <span class="block text-xs text-gray-400">Codes : {{ $codes }}</span>
                            </div>
                            <form action="{{ route('admin.users.beneficiaire-links.destroy', [$user, $link]) }}" method="POST"
                                onsubmit="return confirm('Retirer ce lien ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-600 hover:text-red-800">Retirer</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-sm text-gray-400 italic">Aucun lien enregistré.</p>
            @endif

            <form action="{{ route('admin.users.beneficiaire-links.store', $user) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-gray-100">
                @csrf
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Bénéficiaire</label>
                    <select name="beneficiaire_id" class="{{ $field('beneficiaire_id') }}" required>
                        <option value="">— Choisir —</option>
                        @foreach($beneficiaires as $b)
                            @php
                                $labelCodes = $b->affiliations->pluck('code_pension')->filter()->unique()->join(', ') ?: 'sans code';
                                $labelName = $b->displayName() ?: 'Sans nom';
                            @endphp
                            <option value="{{ $b->id }}" @selected(old('beneficiaire_id') == $b->id)>
                                #{{ $b->id }} — {{ $labelName }} ({{ $labelCodes }})
                            </option>
                        @endforeach
                    </select>
                    @error('beneficiaire_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Relation</label>
                    <select name="relation" class="{{ $field('relation') }}" required>
                        @foreach(\App\Enums\BeneficiaireRelationEnum::cases() as $rel)
                            @if($rel !== \App\Enums\BeneficiaireRelationEnum::SELF)
                                <option value="{{ $rel->value }}" @selected(old('relation') === $rel->value)>{{ $rel->label() }}</option>
                            @endif
                        @endforeach
                    </select>
                    @error('relation')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-end">
                    <button type="submit"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <i class="fas fa-plus text-xs"></i> Ajouter le lien
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
