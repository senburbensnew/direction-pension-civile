<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Panel - {{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
</head>

<body class="bg-gray-100">
    <div class="min-h-screen" id="wrapper">
        <!-- Mobile overlay -->
        <div class="overlay fixed inset-0 z-40 lg:hidden" onclick="toggleSidebar()"></div>

        <!-- Sidebar -->
        <aside class="sidebar fixed inset-y-0 left-0 z-50 w-64 bg-[#173152] overflow-y-auto">
            <div class="p-4">
                <h2 class="text-white text-xl font-semibold">Admin Panel</h2>
            </div>
            <nav class="mt-4">
                <a href="{{ route('admin.users.index') }}"
                    class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 {{ request()->routeIs('admin.users.index') ? 'bg-gray-700' : '' }}">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Users
                </a>
                <a href="{{ route('admin.carousels.index') }}"
                    class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 {{ request()->routeIs('admin.carousels.index') ? 'bg-gray-700' : '' }}">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <rect x="2" y="2" width="20" height="20" rx="3" stroke="currentColor"
                            stroke-width="2" fill="none" />
                        <circle cx="8" cy="8" r="2" stroke="currentColor" stroke-width="2"
                            fill="none" />
                        <path d="M2 18L8 12L12 16L18 10L22 14" stroke="currentColor" stroke-width="2" fill="none" />
                    </svg>
                    Carousel
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
                            <span class="hidden sm:inline">Home</span>
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
</body>

</html>
