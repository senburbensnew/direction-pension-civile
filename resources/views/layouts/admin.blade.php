<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-telephone-validation-meta />
    <title>@yield('title', 'Administration') — Direction de la Pension Civile</title>
    <x-site-head-meta robots="noindex, nofollow" />
    <x-fonts />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    x-data="{ sidebarOpen: false }"
    x-on:keydown.escape.window="sidebarOpen = false"
    x-on:resize.window="if (window.innerWidth >= 1024) sidebarOpen = false"
    class="bg-gray-100 font-sans antialiased">

    <!-- Overlay (mobile) -->
    <div
        x-show="sidebarOpen"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 bg-black/50 z-40 lg:hidden"
        x-on:click="sidebarOpen = false"></div>

    <!-- Sidebar -->
    <aside
        id="sidebar"
        class="fixed inset-y-0 left-0 z-50 flex w-60 flex-col bg-[#0f2340] transition-transform duration-300 ease-out"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

        {{-- Brand --}}
        <div class="flex items-center gap-3 px-4 py-4 border-b border-white/10">
            <x-optimized-image src="images/setting-logo-1-M13oPLiYoM.png" alt="Logo" class="w-8 h-8 rounded" width="32" height="32" loading="eager"></x-optimized-image>
            <div class="leading-tight">
                <p class="text-white text-sm font-semibold leading-none">Direction de la Pension Civile</p>
                <p class="text-slate-400 text-xs">Administration</p>
            </div>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 overflow-y-auto py-3 px-2 space-y-0.5">
            @php
                $adminNavSection = 'px-3 pt-2 pb-1 text-[0.65rem] font-bold tracking-wider text-slate-500 uppercase';
                $adminNavLink = function (bool $active, string $extra = ''): string {
                    $size = str_contains($extra, 'text-xs') ? 'text-xs' : 'text-[0.825rem]';
                    $base = 'flex items-center gap-2.5 py-[0.45rem] px-3.5 rounded-md font-medium whitespace-nowrap transition-colors [&>i]:w-4 [&>i]:shrink-0 [&>i]:text-center [&>i]:text-[0.85rem] '.$size;
                    $state = $active ? 'bg-white/20 text-white' : 'text-slate-400 hover:bg-white/10 hover:text-slate-200';
                    return trim($base.' '.$state);
                };
            @endphp

            {{-- Vue d'ensemble — open by default --}}
            <div x-data="{ open: true }">
                <button @click="open = !open"
                    class="{{ $adminNavSection }} w-full flex items-center justify-between cursor-pointer hover:text-slate-300 transition-colors">
                    <span>Vue d'ensemble</span>
                    <i class="fas fa-chevron-down text-[9px] transition-transform duration-200"
                       :class="open ? '' : '-rotate-90'"></i>
                </button>
                <div x-show="open" x-transition.duration.150ms>
                    <x-layouts.admin-nav-link
                        :href="route('admin.dashboard.index')"
                        icon="fas fa-chart-pie"
                        :active="request()->routeIs('admin.dashboard.*')">
                        Tableau de bord
                    </x-layouts.admin-nav-link>
                </div>
            </div>

            {{-- Utilisateurs & Accès --}}
            <div x-data="{ open: {{ request()->routeIs('admin.users.*', 'admin.roles.*', 'admin.permissions.*', 'admin.sessions.*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                    class="{{ $adminNavSection }} mt-2 w-full flex items-center justify-between cursor-pointer hover:text-slate-300 transition-colors">
                    <span>Utilisateurs &amp; Accès</span>
                    <i class="fas fa-chevron-down text-[9px] transition-transform duration-200"
                       :class="open ? '' : '-rotate-90'"></i>
                </button>
                <div x-show="open" x-transition.duration.150ms>
                    <a href="{{ route('admin.users.index') }}"
                       class="{{ $adminNavLink(request()->routeIs('admin.users.*')) }}">
                       <i class="fas fa-users"></i> Utilisateurs
                    </a>
                    <a href="{{ route('admin.roles.index') }}"
                       class="{{ $adminNavLink(request()->routeIs('admin.roles.*')) }}">
                       <i class="fas fa-shield-alt"></i> Rôles
                    </a>
                    <a href="{{ route('admin.permissions.index') }}"
                       class="{{ $adminNavLink(request()->routeIs('admin.permissions.*')) }}">
                       <i class="fas fa-key"></i> Permissions
                    </a>
                    <a href="{{ route('admin.sessions.index') }}"
                       class="{{ $adminNavLink(request()->routeIs('admin.sessions.*')) }}">
                       <i class="fas fa-user-clock"></i> Sessions actives
                    </a>
                    <!-- @canany(['pension_mandats.view', 'beneficiaire_relations.view'])
                        <a href="{{ route('formalites.representations.index') }}"
                           class="{{ $adminNavLink(request()->routeIs('formalites.representations.*', 'formalites.pension-mandats.*', 'formalites.beneficiaire-relations.*')) }}">
                           <i class="fas fa-people-arrows"></i> Mandats &amp; relations (formalités)
                        </a>
                    @endcanany -->
                </div>
            </div>

            {{-- Organisation --}}
            <div x-data="{ open: {{ request()->routeIs('admin.services.*', 'admin.directions.*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                    class="{{ $adminNavSection }} mt-2 w-full flex items-center justify-between cursor-pointer hover:text-slate-300 transition-colors">
                    <span>Organisation</span>
                    <i class="fas fa-chevron-down text-[9px] transition-transform duration-200"
                       :class="open ? '' : '-rotate-90'"></i>
                </button>
                <div x-show="open" x-transition.duration.150ms>
                    <a href="{{ route('admin.services.index') }}"
                       class="{{ $adminNavLink(request()->routeIs('admin.services.*')) }}">
                       <i class="fas fa-sitemap"></i> Services
                    </a>
                    <a href="{{ route('admin.directions.index') }}"
                       class="{{ $adminNavLink(request()->routeIs('admin.directions.*')) }}">
                       <i class="fas fa-map-marker-alt"></i> Directions Départementales
                    </a>
                </div>
            </div>

            {{-- Publications --}}
            <div x-data="{ open: {{ request()->routeIs('admin.actualites.*', 'admin.reports.*', 'admin.publications.*', 'admin.documents.*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                    class="{{ $adminNavSection }} mt-2 w-full flex items-center justify-between cursor-pointer hover:text-slate-300 transition-colors">
                    <span>Publications</span>
                    <i class="fas fa-chevron-down text-[9px] transition-transform duration-200"
                       :class="open ? '' : '-rotate-90'"></i>
                </button>
                <div x-show="open" x-transition.duration.150ms>
                    <a href="{{ route('admin.actualites.admin.index') }}"
                       class="{{ $adminNavLink(request()->routeIs('admin.actualites.*')) }}">
                       <i class="fas fa-newspaper"></i> Actualités
                    </a>
                    <a href="{{ route('admin.reports.admin.index') }}"
                       class="{{ $adminNavLink(request()->routeIs('admin.reports.*')) }}">
                       <i class="fas fa-file-pdf"></i> Rapports
                    </a>
                    <a href="{{ route('admin.publications.index') }}"
                       class="{{ $adminNavLink(request()->routeIs('admin.publications.*')) }}">
                       <i class="fas fa-file-contract"></i> Publications légales
                    </a>
                    <a href="{{ route('admin.documents.index') }}"
                       class="{{ $adminNavLink(request()->routeIs('admin.documents.*')) }}">
                       <i class="fas fa-file-upload"></i> Téléversement documents
                    </a>
                </div>
            </div>

            {{-- Médias & présentation --}}
            <div x-data="{ open: {{ request()->routeIs('admin.carousels.*', 'admin.mediatheque.*', 'admin.institution-images.*', 'admin.officials.*', 'admin.partenaires.*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                    class="{{ $adminNavSection }} mt-2 w-full flex items-center justify-between cursor-pointer hover:text-slate-300 transition-colors">
                    <span>Médias &amp; présentation</span>
                    <i class="fas fa-chevron-down text-[9px] transition-transform duration-200"
                       :class="open ? '' : '-rotate-90'"></i>
                </button>
                <div x-show="open" x-transition.duration.150ms>
                    <a href="{{ route('admin.carousels.index') }}"
                       class="{{ $adminNavLink(request()->routeIs('admin.carousels.*')) }}">
                       <i class="fas fa-images"></i> Carrousel
                    </a>
                    <a href="{{ route('admin.mediatheque.index') }}"
                       class="{{ $adminNavLink(request()->routeIs('admin.mediatheque.*')) }}">
                       <i class="fas fa-photo-film"></i> Médiathèque
                    </a>
                    <a href="{{ route('admin.institution-images.index') }}"
                       class="{{ $adminNavLink(request()->routeIs('admin.institution-images.*')) }}">
                       <i class="fas fa-camera"></i> Notre Institution en Images
                    </a>
                    <a href="{{ route('admin.officials.index') }}"
                       class="{{ $adminNavLink(request()->routeIs('admin.officials.*')) }}">
                       <i class="fas fa-user-tie"></i> Profil &amp; Discours
                    </a>
                    <a href="{{ route('admin.partenaires.index') }}"
                       class="{{ $adminNavLink(request()->routeIs('admin.partenaires.*')) }}">
                       <i class="fas fa-handshake"></i> Nos Partenaires
                    </a>
                </div>
            </div>

            {{-- Informations --}}
            <div x-data="{ open: {{ request()->routeIs('admin.faq.*', 'admin.glossaire.*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                    class="{{ $adminNavSection }} mt-2 w-full flex items-center justify-between cursor-pointer hover:text-slate-300 transition-colors">
                    <span>Informations</span>
                    <i class="fas fa-chevron-down text-[9px] transition-transform duration-200"
                       :class="open ? '' : '-rotate-90'"></i>
                </button>
                <div x-show="open" x-transition.duration.150ms>
                    <a href="{{ route('admin.faq.index') }}"
                       class="{{ $adminNavLink(request()->routeIs('admin.faq.*')) }}">
                       <i class="fas fa-question-circle"></i> FAQ
                    </a>
                    <a href="{{ route('admin.glossaire.index') }}"
                       class="{{ $adminNavLink(request()->routeIs('admin.glossaire.*')) }}">
                       <i class="fas fa-book"></i> Glossaire
                    </a>
                </div>
            </div>

            {{-- Communications --}}
            <div x-data="{ open: {{ request()->routeIs('admin.contacts.*', 'admin.newsletter.*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                    class="{{ $adminNavSection }} mt-2 w-full flex items-center justify-between cursor-pointer hover:text-slate-300 transition-colors">
                    <span>Communications</span>
                    <i class="fas fa-chevron-down text-[9px] transition-transform duration-200"
                       :class="open ? '' : '-rotate-90'"></i>
                </button>
                <div x-show="open" x-transition.duration.150ms>
                    <a href="{{ route('admin.contacts.index') }}"
                       class="{{ $adminNavLink(request()->routeIs('admin.contacts.*')) }}">
                       <i class="fas fa-envelope"></i> Messages
                       @php $unread = \App\Models\Contact::where('read', false)->count(); @endphp
                       @if($unread > 0)
                           <span class="ml-auto bg-red-500 text-white text-xs rounded-full px-1.5 py-0.5 leading-none">{{ $unread }}</span>
                       @endif
                    </a>
                    <a href="{{ route('admin.newsletter.admin.index') }}"
                       class="{{ $adminNavLink(request()->routeIs('admin.newsletter.*')) }}">
                       <i class="fas fa-paper-plane"></i> Newsletter
                    </a>
                </div>
            </div>

            {{-- Paramètres --}}
            <div x-data="{ open: {{ request()->routeIs('admin.settings', 'admin.contact-parameters.*', 'admin.rdv-settings.*', 'admin.ocr.test*', 'admin.email.test*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                    class="{{ $adminNavSection }} mt-2 w-full flex items-center justify-between cursor-pointer hover:text-slate-300 transition-colors">
                    <span>Paramètres</span>
                    <i class="fas fa-chevron-down text-[9px] transition-transform duration-200"
                       :class="open ? '' : '-rotate-90'"></i>
                </button>
                <div x-show="open" x-transition.duration.150ms>
                    <a href="{{ route('admin.settings') }}"
                       class="{{ $adminNavLink(request()->routeIs('admin.settings')) }}">
                       <i class="fas fa-cog"></i> Paramètres
                    </a>
                    <a href="{{ route('admin.ocr.test') }}"
                       class="{{ $adminNavLink(request()->routeIs('admin.ocr.test*')) }}">
                       <i class="fas fa-eye"></i> Test OCR
                    </a>
                    <a href="{{ route('admin.email.test') }}"
                       class="{{ $adminNavLink(request()->routeIs('admin.email.test*')) }}">
                       <i class="fas fa-envelope"></i> Test e-mail
                    </a>
                    <a href="{{ route('admin.contact-parameters.index') }}"
                       class="{{ $adminNavLink(request()->routeIs('admin.contact-parameters.*')) }}">
                       <i class="fas fa-address-card"></i> Infos de contact
                    </a>
                    <a href="{{ route('admin.rdv-settings.index') }}"
                       class="{{ $adminNavLink(request()->routeIs('admin.rdv-settings.*')) }}">
                       <i class="fas fa-calendar-week"></i> Paramètres RDV
                    </a>
                </div>
            </div>

        </nav>

        {{-- Footer: maintenance toggle + user info + profile + logout --}}
        <div class="border-t border-white/10 px-3 py-3 space-y-2">
            @php
                $maintenanceOn = \Illuminate\Support\Facades\Cache::remember('is_maintenance_mode', 60, fn() =>
                    \Illuminate\Support\Facades\DB::table('parameters')
                        ->where('name', 'is_maintenance_mode')->value('value') === 'true'
                );
            @endphp
            <form method="POST" action="{{ route('admin.toggle.maintenance') }}">
                @csrf
                <button type="submit"
                    role="switch"
                    aria-checked="{{ $maintenanceOn ? 'true' : 'false' }}"
                    aria-label="Activer ou désactiver le mode maintenance"
                    class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-xs font-medium transition-colors {{ $maintenanceOn ? 'bg-yellow-500/20 text-yellow-300 hover:bg-yellow-500/30' : 'text-slate-400 hover:bg-white/10 hover:text-slate-200' }}">
                    <span class="flex items-center gap-2 min-w-0">
                        <i class="fas fa-tools w-4 text-center flex-shrink-0 {{ $maintenanceOn ? 'text-yellow-400' : '' }}"></i>
                        Mode maintenance
                    </span>
                    <span
                        aria-hidden="true"
                        @class([
                            'flex h-4 w-8 flex-shrink-0 rounded-full p-0.5 transition-colors duration-200',
                            'bg-yellow-400' => $maintenanceOn,
                            'bg-slate-600' => ! $maintenanceOn,
                        ])>
                        <span @class([
                            'block h-3 w-3 rounded-full bg-white shadow-sm transition-[margin] duration-200 ease-in-out',
                            'ml-auto' => $maintenanceOn,
                        ])></span>
                    </span>
                </button>
            </form>
            <a href="{{ route('profile.edit') }}"
                class="{{ $adminNavLink(request()->routeIs('profile.*'), 'text-xs') }}">
                <i class="fas fa-user-circle"></i> Mon profil
            </a>
            <div class="flex items-center gap-2 px-2">
                @if(Auth::user()->profile_photo)
                    <img src="{{ Storage::url(Auth::user()->profile_photo) }}"
                         class="w-7 h-7 rounded-full object-cover flex-shrink-0">
                @else
                    <div class="w-7 h-7 rounded-full bg-slate-600 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-user text-slate-300 text-xs"></i>
                    </div>
                @endif
                <div class="flex-1 min-w-0">
                    <p class="text-white text-xs font-medium truncate flex items-center gap-1.5">
                        {{ Auth::user()->name }}
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-blue-500/20 text-blue-300 leading-none flex-shrink-0">Moi</span>
                    </p>
                    <p class="text-slate-500 text-xs truncate">Administrateur</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Déconnexion"
                        class="text-slate-500 hover:text-red-400 transition-colors">
                        <i class="fas fa-sign-out-alt text-sm"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main -->
    <div id="main" class="flex min-h-screen flex-col lg:ml-60">

        {{-- Top bar --}}
        <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
            <div class="flex items-center justify-between px-4 py-3">
                <div class="flex items-center gap-3">
                    <button type="button" id="hamburger" x-on:click="sidebarOpen = !sidebarOpen"
                        class="text-gray-500 hover:text-gray-700 lg:hidden">
                        <i class="fas fa-bars text-lg"></i>
                    </button>

                    {{-- Breadcrumb --}}
                    <div class="text-sm text-gray-500 hidden sm:flex items-center gap-1.5">
                        <a href="{{ route('admin.dashboard.index') }}" class="hover:text-gray-800">Admin</a>
                        @hasSection('breadcrumb')
                            <i class="fas fa-chevron-right text-xs text-gray-300"></i>
                            @yield('breadcrumb')
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    {{-- Site link --}}
                    <a href="{{ route('home') }}" target="_blank"
                        class="text-xs text-gray-500 hover:text-gray-700 flex items-center gap-1">
                        <i class="fas fa-external-link-alt"></i>
                        <span class="hidden sm:inline">Voir le site</span>
                    </a>

                    {{-- Notifications bell --}}
                    @php
                        $bellCount = \App\Models\Contact::where('read', false)->count()
                                   + Auth::user()->unreadNotifications->count();
                    @endphp
                    <a href="{{ route('admin.contacts.index') }}"
                        class="relative text-gray-500 hover:text-gray-700">
                        <i class="fas fa-bell text-lg"></i>
                        @if($bellCount > 0)
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center leading-none">
                                {{ $bellCount > 9 ? '9+' : $bellCount }}
                            </span>
                        @endif
                    </a>
                </div>
            </div>
        </header>

        {{-- Page content --}}
        <main class="flex-1 p-4 sm:p-6">
            @yield('content')
        </main>
    </div>

    <x-flash-messages position="top-16" />

    @stack('scripts')
</body>
</html>
