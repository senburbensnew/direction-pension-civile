@extends('layouts.admin')

@section('title', 'Profil & Discours')

@section('breadcrumb')
    <span class="text-gray-700 text-sm">Profil &amp; Discours</span>
@endsection

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Profil &amp; Discours</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                Gérez les pages publiques <strong>Profil</strong> et <strong>Discours</strong> des responsables (Ministre, Directeur, etc.).
            </p>
        </div>
        <a href="{{ route('admin.officials.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
            <i class="fas fa-plus text-xs"></i> Ajouter un officiel
        </a>
    </div>

    @if(session('success'))
        <div class="flex items-center gap-2 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if($officials->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 py-16 flex flex-col items-center text-center gap-3">
            <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center">
                <i class="fas fa-user-tie text-2xl text-gray-300"></i>
            </div>
            <p class="text-gray-500 font-medium">Aucun officiel configuré.</p>
            <a href="{{ route('admin.officials.create') }}" class="text-sm text-blue-600 hover:underline">
                Ajouter le premier officiel
            </a>
        </div>
    @else
        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @foreach($officials as $official)
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="flex items-center gap-4 p-5 border-b border-gray-100">
                        <img src="{{ $official->photoUrl() }}"
                             alt="{{ $official->nom }}"
                             class="w-16 h-16 rounded-xl object-cover shrink-0 bg-gray-100"
                             onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($official->nom) }}&background=cbd5e1&color=475569&size=64';">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-0.5">
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full
                                    {{ $official->active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $official->active ? 'Actif' : 'Inactif' }}
                                </span>
                                <span class="text-xs text-gray-400 font-mono">{{ $official->slug }}</span>
                            </div>
                            <p class="text-xs font-semibold text-blue-600 mb-0.5">{{ $official->role }}</p>
                            <p class="text-sm font-bold text-gray-900 truncate">{{ $official->nom }}</p>
                        </div>
                    </div>

                    <div class="px-5 py-3 flex flex-wrap items-center gap-3 bg-gray-50 border-b border-gray-100">
                        <span class="flex items-center gap-1.5 text-xs {{ $official->hasBiographie() ? 'text-green-600' : 'text-amber-600' }}">
                            <i class="fas {{ $official->hasBiographie() ? 'fa-check-circle' : 'fa-exclamation-circle' }} text-[10px]"></i>
                            Profil {{ $official->hasBiographie() ? 'renseigné' : 'à compléter' }}
                        </span>
                        <span class="flex items-center gap-1.5 text-xs {{ $official->hasDiscours() ? 'text-green-600' : 'text-amber-600' }}">
                            <i class="fas {{ $official->hasDiscours() ? 'fa-check-circle' : 'fa-exclamation-circle' }} text-[10px]"></i>
                            Discours {{ $official->hasDiscours() ? 'renseigné' : 'à compléter' }}
                        </span>
                    </div>

                    <div class="px-5 py-3 space-y-2">
                        <div class="grid grid-cols-2 gap-2">
                            <a href="{{ route('admin.officials.edit', $official) }}#profil"
                               class="inline-flex items-center justify-center gap-1.5 text-xs px-2.5 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg font-medium transition-colors">
                                <i class="fas fa-id-card"></i> Éditer Profil
                            </a>
                            <a href="{{ route('admin.officials.edit', $official) }}#discours"
                               class="inline-flex items-center justify-center gap-1.5 text-xs px-2.5 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg font-medium transition-colors">
                                <i class="fas fa-microphone"></i> Éditer Discours
                            </a>
                        </div>
                        <div class="flex items-center justify-between gap-2 pt-1">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('quisommesnous.profil', ['role' => $official->slug]) }}"
                                   target="_blank"
                                   class="text-xs px-2.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg transition-colors">
                                    <i class="fas fa-external-link-alt mr-1"></i> Voir Profil
                                </a>
                                <a href="{{ route('quisommesnous.mots', ['role' => $official->slug]) }}"
                                   target="_blank"
                                   class="text-xs px-2.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg transition-colors">
                                    <i class="fas fa-external-link-alt mr-1"></i> Voir Discours
                                </a>
                            </div>
                            <div class="flex items-center gap-1">
                                <a href="{{ route('admin.officials.edit', $official) }}"
                                   title="Modifier tout"
                                   class="text-xs px-2.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.officials.destroy', $official) }}"
                                      onsubmit="return confirm('Supprimer cet officiel ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" title="Supprimer"
                                            class="text-xs px-2.5 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg transition-colors">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
