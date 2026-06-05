@extends('layouts.admin')

@section('title', 'Créer un utilisateur')

@section('breadcrumb')
    <a href="{{ route('admin.users.index') }}" class="text-gray-500 hover:text-gray-800">Utilisateurs</a>
    <i class="fas fa-chevron-right text-xs text-gray-300 mx-1"></i>
    <span class="text-gray-700">Créer</span>
@endsection

@php
    $field = fn(string $name) => 'w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400 ' . ($errors->has($name) ? 'border-red-400' : 'border-gray-200');
@endphp

@section('content')
<div class="max-w-3xl mx-auto space-y-5">

    {{-- Page header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Créer un utilisateur</h1>
            <p class="text-sm text-gray-500 mt-0.5">Remplissez les informations pour ajouter un nouveau compte.</p>
        </div>
        <a href="{{ route('admin.users.index') }}"
            class="inline-flex items-center gap-1.5 px-3 py-2 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
            <i class="fas fa-arrow-left text-xs"></i> Retour
        </a>
    </div>

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

    <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-5">
        @csrf

        {{-- Identité --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 flex items-center gap-2 bg-gray-50">
                <i class="fas fa-user text-indigo-500 text-sm w-4 text-center"></i>
                <span class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Identité</span>
            </div>
            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Prénom</label>
                    <input type="text" name="firstname" value="{{ old('firstname') }}"
                        class="{{ $field('firstname') }}" placeholder="Jean">
                    @error('firstname')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nom de famille</label>
                    <input type="text" name="lastname" value="{{ old('lastname') }}"
                        class="{{ $field('lastname') }}" placeholder="Dupont">
                    @error('lastname')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Nom d'affichage <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="{{ $field('name') }}" placeholder="Jean Dupont">
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
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
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Adresse e-mail <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="{{ $field('email') }}" placeholder="jean.dupont@example.com">
                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Téléphone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                        class="{{ $field('phone') }}" placeholder="+509 3600-0000">
                    @error('phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div></div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Mot de passe <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password" required minlength="8"
                        class="{{ $field('password') }}" placeholder="Min. 8 caractères">
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Confirmer le mot de passe <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password_confirmation" required
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
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Type d'utilisateur</label>
                    <select name="user_type" class="{{ $field('user_type') }}">
                        <option value="">— Sélectionner —</option>
                        <option value="fonctionnaire" {{ old('user_type') === 'fonctionnaire' ? 'selected' : '' }}>Fonctionnaire</option>
                        <option value="pensionnaire"  {{ old('user_type') === 'pensionnaire'  ? 'selected' : '' }}>Pensionnaire</option>
                        <option value="institution"   {{ old('user_type') === 'institution'   ? 'selected' : '' }}>Institution</option>
                    </select>
                    @error('user_type')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Service</label>
                    <select name="service_id" class="{{ $field('service_id') }}">
                        <option value="">— Aucun service —</option>
                        @foreach(\App\Models\Service::orderBy('nom')->get() as $service)
                            <option value="{{ $service->id }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>
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
                            <option value="{{ $role->name }}" {{ old('role') === $role->name ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Code pension</label>
                    <input type="text" name="pension_code" value="{{ old('pension_code') }}"
                        class="{{ $field('pension_code') }}" placeholder="Ex : PEN-00001">
                    @error('pension_code')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">NIF</label>
                    <input type="text" name="nif" value="{{ old('nif') }}"
                        class="{{ $field('nif') }}" placeholder="Numéro d'identification fiscale">
                    @error('nif')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">NINU</label>
                    <input type="text" name="ninu" value="{{ old('ninu') }}"
                        class="{{ $field('ninu') }}" placeholder="Numéro d'identification nationale unique">
                    @error('ninu')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 pb-2">
            <a href="{{ route('admin.users.index') }}"
                class="px-4 py-2 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                Annuler
            </a>
            <button type="submit"
                class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                <i class="fas fa-user-plus text-xs"></i> Créer l'utilisateur
            </button>
        </div>

    </form>
</div>
@endsection
