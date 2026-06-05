@extends('layouts.admin')

@section('title', 'Tableau de bord')

@section('breadcrumb')
    <span class="text-gray-700 text-sm">Tableau de bord</span>
@endsection

@section('content')
<div class="space-y-6">

    <div>
        <h1 class="text-xl font-bold text-gray-800">Tableau de bord</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ now()->translatedFormat('l d F Y') }}</p>
    </div>

    {{-- ── Stat cards row 1 ─────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        <a href="{{ route('admin.users.index') }}"
            class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm hover:shadow-md transition-shadow group">
            <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center group-hover:bg-indigo-100 transition-colors">
                <i class="fas fa-users text-indigo-600"></i>
            </div>
            <p class="text-2xl font-bold text-gray-800 mt-3">{{ $stats['users'] }}</p>
            <p class="text-sm text-gray-500">Utilisateurs</p>
        </a>

        <a href="{{ route('admin.contacts.index') }}"
            class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 rounded-lg bg-orange-50 flex items-center justify-center group-hover:bg-orange-100 transition-colors">
                    <i class="fas fa-envelope text-orange-500"></i>
                </div>
                @if($stats['contacts_unread'] > 0)
                    <span class="text-xs bg-red-500 text-white px-2 py-0.5 rounded-full font-medium">
                        {{ $stats['contacts_unread'] }} non lus
                    </span>
                @endif
            </div>
            <p class="text-2xl font-bold text-gray-800 mt-3">{{ $stats['contacts'] }}</p>
            <p class="text-sm text-gray-500">Messages reçus</p>
        </a>

        <a href="{{ route('admin.newsletter.admin.index') }}"
            class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm hover:shadow-md transition-shadow group">
            <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center group-hover:bg-green-100 transition-colors">
                <i class="fas fa-paper-plane text-green-600"></i>
            </div>
            <p class="text-2xl font-bold text-gray-800 mt-3">{{ $stats['newsletter'] }}</p>
            <p class="text-sm text-gray-500">Abonnés newsletter</p>
        </a>

        <a href="{{ route('admin.actualites.admin.index') }}"
            class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm hover:shadow-md transition-shadow group">
            <div class="w-10 h-10 rounded-lg bg-yellow-50 flex items-center justify-center group-hover:bg-yellow-100 transition-colors">
                <i class="fas fa-newspaper text-yellow-600"></i>
            </div>
            <p class="text-2xl font-bold text-gray-800 mt-3">{{ $stats['actualites'] }}</p>
            <p class="text-sm text-gray-500">Actualités</p>
        </a>

    </div>

    {{-- ── Stat cards row 2 ─────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        <a href="{{ route('admin.reports.admin.index') }}"
            class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm hover:shadow-md transition-shadow group">
            <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center group-hover:bg-red-100 transition-colors">
                <i class="fas fa-file-pdf text-red-500"></i>
            </div>
            <p class="text-2xl font-bold text-gray-800 mt-3">{{ $stats['reports'] }}</p>
            <p class="text-sm text-gray-500">Rapports publiés</p>
        </a>

        <a href="{{ route('admin.roles.index') }}"
            class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm hover:shadow-md transition-shadow group">
            <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center group-hover:bg-purple-100 transition-colors">
                <i class="fas fa-shield-alt text-purple-600"></i>
            </div>
            <p class="text-2xl font-bold text-gray-800 mt-3">{{ $stats['roles'] }}</p>
            <p class="text-sm text-gray-500">Rôles</p>
        </a>

        <a href="{{ route('admin.permissions.index') }}"
            class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm hover:shadow-md transition-shadow group">
            <div class="w-10 h-10 rounded-lg bg-slate-50 flex items-center justify-center group-hover:bg-slate-100 transition-colors">
                <i class="fas fa-key text-slate-500"></i>
            </div>
            <p class="text-2xl font-bold text-gray-800 mt-3">{{ $stats['permissions'] }}</p>
            <p class="text-sm text-gray-500">Permissions</p>
        </a>

        <a href="{{ route('admin.services.index') }}"
            class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm hover:shadow-md transition-shadow group">
            <div class="w-10 h-10 rounded-lg bg-teal-50 flex items-center justify-center group-hover:bg-teal-100 transition-colors">
                <i class="fas fa-sitemap text-teal-600"></i>
            </div>
            <p class="text-2xl font-bold text-gray-800 mt-3">{{ $stats['services'] }}</p>
            <p class="text-sm text-gray-500">Services</p>
        </a>

    </div>

    {{-- ── Two columns ──────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Unread messages --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100">
                <h2 class="font-semibold text-gray-700 text-sm">Messages non lus</h2>
                <a href="{{ route('admin.contacts.index') }}"
                    class="text-xs text-blue-600 hover:underline">Tout voir →</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recentContacts as $contact)
                    <a href="{{ route('admin.contacts.show', $contact->id) }}"
                        class="flex items-start gap-3 px-5 py-3 hover:bg-gray-50 transition-colors">
                        <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-user text-orange-500 text-xs"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-800">
                                {{ $contact->first_name }} {{ $contact->last_name }}
                            </p>
                            <p class="text-xs text-gray-500 truncate">{{ Str::limit($contact->message, 60) }}</p>
                        </div>
                        <p class="text-xs text-gray-300 whitespace-nowrap mt-0.5">
                            {{ $contact->created_at->diffForHumans() }}
                        </p>
                    </a>
                @empty
                    <div class="px-5 py-8 text-center text-gray-400 text-sm">
                        <i class="fas fa-check-circle text-green-400 text-2xl mb-2 block"></i>
                        Aucun message non lu.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Recent actualites --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100">
                <h2 class="font-semibold text-gray-700 text-sm">Actualités récentes</h2>
                <a href="{{ route('admin.actualites.admin.index') }}"
                    class="text-xs text-blue-600 hover:underline">Gérer →</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recentActualites as $actu)
                    <div class="flex items-center gap-3 px-5 py-3">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $actu->title }}</p>
                            <p class="text-xs text-gray-400">{{ $actu->created_at->format('d/m/Y') }}</p>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded-full
                            {{ $actu->published ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $actu->published ? 'Publié' : 'Brouillon' }}
                        </span>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-gray-400 text-sm">Aucune actualité.</div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- ── Quick actions ────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
        <h2 class="font-semibold text-gray-700 text-sm mb-3">Actions rapides</h2>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.actualites.create') }}"
                class="inline-flex items-center gap-1.5 px-3 py-2 bg-yellow-50 hover:bg-yellow-100 text-yellow-700 text-xs font-medium rounded-lg transition-colors">
                <i class="fas fa-plus"></i> Nouvelle actualité
            </a>
            <a href="{{ route('admin.reports.create') }}"
                class="inline-flex items-center gap-1.5 px-3 py-2 bg-red-50 hover:bg-red-100 text-red-700 text-xs font-medium rounded-lg transition-colors">
                <i class="fas fa-upload"></i> Ajouter un rapport
            </a>
            <a href="{{ route('admin.users.create') }}"
                class="inline-flex items-center gap-1.5 px-3 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-medium rounded-lg transition-colors">
                <i class="fas fa-user-plus"></i> Créer un utilisateur
            </a>
            <a href="{{ route('admin.newsletter.export') }}"
                class="inline-flex items-center gap-1.5 px-3 py-2 bg-green-50 hover:bg-green-100 text-green-700 text-xs font-medium rounded-lg transition-colors">
                <i class="fas fa-download"></i> Exporter newsletter
            </a>
            <a href="{{ route('admin.carousels.create') }}"
                class="inline-flex items-center gap-1.5 px-3 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-medium rounded-lg transition-colors">
                <i class="fas fa-images"></i> Ajouter une slide
            </a>
        </div>
    </div>

</div>
@endsection
