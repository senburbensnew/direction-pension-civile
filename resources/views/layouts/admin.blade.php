<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Administration') — DPC</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root { --sidebar-w: 15rem; }

        /* Sidebar slide */
        #sidebar {
            width: var(--sidebar-w);
            transform: translateX(calc(-1 * var(--sidebar-w)));
            transition: transform .25s ease;
        }
        @media (min-width: 1024px) {
            #sidebar { transform: translateX(0); }
            #main     { margin-left: var(--sidebar-w); }
            #overlay  { display: none !important; }
            #hamburger { display: none; }
        }
        body.sidebar-open #sidebar  { transform: translateX(0); }
        body.sidebar-open #overlay  { display: block; }

        /* Nav links */
        .nav-link {
            display: flex; align-items: center; gap: .625rem;
            padding: .45rem .9rem; border-radius: .4rem;
            color: #94a3b8; font-size: .825rem; font-weight: 500;
            transition: background .15s, color .15s;
            white-space: nowrap;
        }
        .nav-link:hover { background: rgba(255,255,255,.07); color: #e2e8f0; }
        .nav-link.active { background: rgba(255,255,255,.12); color: #fff; }
        .nav-link i { width: 1rem; text-align: center; font-size: .85rem; }

        .nav-section {
            padding: .5rem .75rem .25rem;
            font-size: .65rem; font-weight: 700; letter-spacing: .07em;
            color: #64748b; text-transform: uppercase;
        }
    </style>
</head>

<body class="bg-gray-100 antialiased">

    <!-- Overlay (mobile) -->
    <div id="overlay" class="hidden fixed inset-0 bg-black/50 z-40" onclick="closeSidebar()"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 flex flex-col"
        style="background: #0f2340;">

        {{-- Brand --}}
        <div class="flex items-center gap-3 px-4 py-4 border-b border-white/10">
            <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}" alt="Logo" class="w-8 h-8 rounded">
            <div class="leading-tight">
                <p class="text-white text-sm font-semibold leading-none">DPC</p>
                <p class="text-slate-400 text-xs">Administration</p>
            </div>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 overflow-y-auto py-3 px-2 space-y-0.5">

            {{-- Overview --}}
            <p class="nav-section">Vue d'ensemble</p>
            <a href="{{ route('admin.dashboard.index') }}"
               class="nav-link {{ request()->routeIs('admin.dashboard.*') ? 'active' : '' }}">
               <i class="fas fa-chart-pie"></i> Tableau de bord
            </a>

            {{-- Utilisateurs & Accès --}}
            <p class="nav-section mt-2">Utilisateurs & Accès</p>
            <a href="{{ route('admin.users.index') }}"
               class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
               <i class="fas fa-users"></i> Utilisateurs
            </a>
            <a href="{{ route('admin.roles.index') }}"
               class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
               <i class="fas fa-shield-alt"></i> Rôles
            </a>
            <a href="{{ route('admin.permissions.index') }}"
               class="nav-link {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
               <i class="fas fa-key"></i> Permissions
            </a>
            <a href="{{ route('admin.services.index') }}"
               class="nav-link {{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
               <i class="fas fa-sitemap"></i> Services
            </a>

            {{-- Contenu --}}
            <p class="nav-section mt-2">Contenu</p>
            <a href="{{ route('admin.actualites.admin.index') }}"
               class="nav-link {{ request()->routeIs('admin.actualites.*') ? 'active' : '' }}">
               <i class="fas fa-newspaper"></i> Actualités
            </a>
            <a href="{{ route('admin.reports.admin.index') }}"
               class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
               <i class="fas fa-file-pdf"></i> Rapports
            </a>
            <a href="{{ route('admin.carousels.index') }}"
               class="nav-link {{ request()->routeIs('admin.carousels.*') ? 'active' : '' }}">
               <i class="fas fa-images"></i> Carrousel
            </a>

            {{-- Communications --}}
            <p class="nav-section mt-2">Communications</p>
            <a href="{{ route('admin.contacts.index') }}"
               class="nav-link {{ request()->routeIs('admin.contacts.*') ? 'active' : '' }}">
               <i class="fas fa-envelope"></i> Messages
               @php $unread = \App\Models\Contact::where('read', false)->count(); @endphp
               @if($unread > 0)
                   <span class="ml-auto bg-red-500 text-white text-xs rounded-full px-1.5 py-0.5 leading-none">{{ $unread }}</span>
               @endif
            </a>
            <a href="{{ route('admin.newsletter.admin.index') }}"
               class="nav-link {{ request()->routeIs('admin.newsletter.*') ? 'active' : '' }}">
               <i class="fas fa-paper-plane"></i> Newsletter
            </a>

        </nav>

        {{-- Footer: user info + profile + logout --}}
        <div class="border-t border-white/10 px-3 py-3 space-y-2">
            <a href="{{ route('profile.edit') }}"
                class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }} text-xs">
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
                    <p class="text-white text-xs font-medium truncate">{{ Auth::user()->name }}</p>
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
    <div id="main" class="flex flex-col min-h-screen">

        {{-- Top bar --}}
        <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
            <div class="flex items-center justify-between px-4 py-3">
                <div class="flex items-center gap-3">
                    <button id="hamburger" onclick="toggleSidebar()"
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

                    {{-- Notifications badge --}}
                    @if(isset($unread) && $unread > 0)
                        <a href="{{ route('admin.contacts.index') }}"
                            class="relative text-gray-500 hover:text-gray-700">
                            <i class="fas fa-bell text-lg"></i>
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center leading-none">
                                {{ $unread > 9 ? '9+' : $unread }}
                            </span>
                        </a>
                    @endif
                </div>
            </div>
        </header>

        {{-- Flash messages --}}
        @if(session('success') || session('error'))
            <div class="px-4 pt-3">
                @if(session('success'))
                    <div class="flex items-center gap-2 bg-green-50 border border-green-300 text-green-800 rounded-lg px-4 py-2.5 text-sm">
                        <i class="fas fa-check-circle text-green-500 flex-shrink-0"></i>
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="flex items-center gap-2 bg-red-50 border border-red-300 text-red-800 rounded-lg px-4 py-2.5 text-sm">
                        <i class="fas fa-exclamation-circle text-red-500 flex-shrink-0"></i>
                        {{ session('error') }}
                    </div>
                @endif
            </div>
        @endif

        {{-- Page content --}}
        <main class="flex-1 p-4 sm:p-6">
            @yield('content')
        </main>

        <footer class="px-6 py-3 text-xs text-gray-400 border-t border-gray-200 bg-white">
            DPC Administration &mdash; {{ date('Y') }}
        </footer>
    </div>

    <script>
        function toggleSidebar() {
            document.body.classList.toggle('sidebar-open');
        }
        function closeSidebar() {
            document.body.classList.remove('sidebar-open');
        }
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) closeSidebar();
        });
    </script>
</body>
</html>
