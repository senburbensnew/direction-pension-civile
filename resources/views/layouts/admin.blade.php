<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Panel - {{ config('app.name', 'Laravel') }}</title>
    <style>
        .sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
        }

        .sidebar-open .sidebar {
            transform: translateX(0);
        }

        .overlay {
            display: none;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .sidebar-open .overlay {
            display: block;
        }

        @media (min-width: 1024px) {
            .sidebar {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 16rem;
            }

            .hamburger {
                display: none;
            }
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- <link href="{{ asset('build/assets/app-CFGfTGFn.css') }}" rel="stylesheet"> --}}
</head>

<body class="bg-gray-100">
    <noscript>
        <div class="fixed inset-0 z-[10001] flex items-center justify-center bg-black bg-opacity-80">
            <div class="bg-white max-w-md w-full mx-4 p-6 rounded-xl shadow-xl text-center">
                <div class="text-red-500 text-5xl mb-4">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>

                <h2 class="text-2xl font-bold text-gray-800 mb-2">
                    JavaScript désactivé
                </h2>

                <p class="text-gray-600 mb-4">
                    Ce site nécessite JavaScript pour fonctionner correctement.
                    Veuillez activer JavaScript dans votre navigateur et recharger la page.
                </p>

                <p class="text-sm text-gray-500">
                    Sans JavaScript, certaines fonctionnalités ne seront pas disponibles.
                </p>
            </div>
        </div>
    </noscript>

    <div class="min-h-screen" id="wrapper">
        <!-- Mobile overlay -->
        <div class="overlay fixed inset-0 z-40 lg:hidden" onclick="toggleSidebar()"></div>

        <!-- Sidebar -->
        <aside class="sidebar fixed inset-y-0 left-0 z-50 w-64 bg-[#173152] overflow-y-auto">
            <div class="p-4">
                <h2 class="text-white text-xl font-semibold">Administration</h2>
            </div>
            <nav class="mt-4">

                <!-- Dashboard -->
                 <a href="{{ route('admin.dashboard.index') }}"
                    class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 {{ request()->routeIs('admin.dashboard.*') ? 'bg-gray-700' : '' }}">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7m-9 9V9m4 12V9m5 3l2 2"/>
                    </svg>
                    Dashboard
                </a> 
                <!-- Utilisateurs -->
                <a href="{{ route('admin.users.index') }}"
                    class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 {{ request()->routeIs('admin.users.*') ? 'bg-gray-700' : '' }}">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m4-4a4 4 0 100-8 4 4 0 000 8zm6 0a3 3 0 100-6 3 3 0 000 6z"/>
                    </svg>
                    Utilisateurs
                </a>
                <!-- Services -->
                <a href="{{ route('admin.services.index') }}"
                    class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 {{ request()->routeIs('admin.services.*') ? 'bg-gray-700' : '' }}">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m4-4a4 4 0 100-8 4 4 0 000 8zm6 0a3 3 0 100-6 3 3 0 000 6z"/>
                    </svg>
                    Services
                </a>
                <!-- Permissions -->
                <a href="{{ route('admin.permissions.index') }}"
                    class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 {{ request()->routeIs('admin.permissions.*') ? 'bg-gray-700' : '' }}">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 11c1.657 0 3-1.343 3-3S13.657 5 12 5 9 6.343 9 8s1.343 3 3 3z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 11v6a2 2 0 01-2 2H7a2 2 0 01-2-2v-6"/>
                    </svg>
                    Permissions
                </a>
                <!-- Rôles -->
                <a href="{{ route('admin.roles.index') }}"
                    class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 {{ request()->routeIs('admin.roles.*') ? 'bg-gray-700' : '' }}">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4M12 3l7 4v5c0 5-3.5 9-7 9s-7-4-7-9V7l7-4z"/>
                    </svg>
                    Rôles
                </a>
                <!-- Carrousel -->
                <a href="{{ route('admin.carousels.index') }}"
                    class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 {{ request()->routeIs('admin.carousels.*') ? 'bg-gray-700' : '' }}">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <rect x="3" y="5" width="18" height="14" rx="2" ry="2" stroke-width="2"/>
                        <circle cx="8" cy="10" r="2"/>
                        <path stroke-width="2" d="M21 15l-5-5-4 4-2-2-5 5"/>
                    </svg>
                    Carrousel
                </a>
                <!-- Ministre -->
                <a href="#"
                    class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 {{ request()->routeIs('admin.services.*') ? 'bg-gray-700' : '' }}">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 14a4 4 0 100-8 4 4 0 000 8z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 20a8 8 0 0116 0"/>
                    </svg>
                    Ministre
                </a>
                <!-- Rapports -->
                <a href="{{ route('reports.create') }}"
                    class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 {{ request()->routeIs('reports.*') ? 'bg-gray-700' : '' }}">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17v-4m3 4v-6m3 6v-2M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H9L5 7v12a2 2 0 002 2z"/>
                    </svg>
                    Rapports
                </a>
                <!-- Newsletter -->
                <a href="#"
                    class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 {{ request()->routeIs('admin.services.*') ? 'bg-gray-700' : '' }}">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Newsletter
                </a>
                <!-- Actualités -->
                <a href="{{ route('actualites.admin.index') }}"
                    class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 {{ request()->routeIs('actualites.admin.*') ? 'bg-gray-700' : '' }}">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7h-1a2 2 0 00-2 2v10H6a2 2 0 01-2-2V5a2 2 0 012-2h9l4 4z"/>
                        <path stroke-width="2" d="M8 13h8M8 17h6"/>
                    </svg>
                    Actualités
                </a>

            </nav>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navigation -->
            <header class="bg-white shadow">
                <div class="flex items-center justify-between px-4 py-3 sm:px-6 sm:py-4">
                    <div class="flex items-center space-x-4">
                        <!-- Hamburger Menu -->
                        <button class="hamburger text-gray-500 hover:text-gray-600 lg:hidden" onclick="toggleSidebar()"
                            aria-label="Toggle navigation">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <!-- Home Link -->
                        <a href="{{ route('home') }}"
                            class="flex items-center px-2 py-1 text-gray-700 hover:text-gray-900 transition-colors duration-200">
                            <svg class="w-5 h-5 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <span class="hidden sm:inline">Accueil</span>
                        </a>
                    </div>

                    <!-- User Dropdown -->
                    <div class="flex items-center">
                        <x-dropdown align="right" width="48" class="z-50">
                            <x-slot name="trigger">
                                <button
                                    class="flex items-center space-x-1 text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors duration-200">
                                    <span
                                        class="truncate max-w-[120px] sm:max-w-[160px]">{{ Auth::user()->name }}</span>
                                    <svg class="fill-current h-4 w-4 shrink-0" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault(); this.closest('form').submit();"
                                        class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100">
                                        {{ __('Log Out') }}
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-4">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const wrapper = document.getElementById('wrapper');
            wrapper.classList.toggle('sidebar-open');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (event) => {
            const sidebar = document.querySelector('.sidebar');
            const hamburger = document.querySelector('.hamburger');

            if (window.innerWidth < 1024 &&
                !sidebar.contains(event.target) &&
                !hamburger.contains(event.target)) {
                document.getElementById('wrapper').classList.remove('sidebar-open');
            }
        });

        // Handle window resize
        window.addEventListener('resize', () => {
            const wrapper = document.getElementById('wrapper');
            if (window.innerWidth >= 1024) {
                wrapper.classList.remove('sidebar-open');
            }
        });
    </script>
    {{-- <script src="{{ asset('build/assets/app-CbEvcXly.js') }}"></script> --}}
</body>

</html>
