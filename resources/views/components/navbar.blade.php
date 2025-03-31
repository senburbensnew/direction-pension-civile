<div class="flex justify-between items-center mx-auto py-4 md:py-6 px-4 sm:px-6 lg:px-8 bg-[#173152]">
    <div class="flex items-center">
        <!-- Logo -->
        <a href="{{ route('home') }}" class="mr-3">
            <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}" alt="Logo"
                class="w-12 h-12 md:w-16 md:h-16">
        </a>
        <!-- Navigation Links -->
        <a href="{{ route('home') }}" class="font-semibold text-white hover:text-white flex flex-col">
            <span class="text-sm md:text-lg">{{ __('messages.direction') }}</span>
            <span class="text-xs md:text-sm">{{ __('messages.republic') }}</span>
        </a>
    </div>

    <!-- Navigation and Icons -->
    <div class="flex items-center space-x-4">
        <!-- Main Navigation Links -->
        <div class="hidden md:flex space-x-2 md:space-x-4">
            <a href="{{ route('liens-utiles') }}"
                class="text-white hover:text-orange-500 cursor-pointer text-sm md:text-base">
                {{ __('messages.useful_links') }}
            </a>
            <a href="{{ route('contact') }}"
                class="text-white hover:text-orange-500 cursor-pointer text-sm md:text-base">
                {{ __('messages.contact') }}
            </a>
            @guest
                <a href="{{ route('login') }}" class="text-white hover:text-orange-500 cursor-pointer text-sm md:text-base">
                    {{ __('messages.login') }}
                </a>
            @endguest
            @auth
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="text-white hover:text-orange-500 cursor-pointer text-sm md:text-base inline-flex items-center">
                            @if (Auth::user()->profile_photo)
                                <div class="h-8 w-8 rounded-full overflow-hidden">
                                    <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}"
                                        class="h-8 w-8 object-cover" alt="{{ __('Profile Photo') }}">
                                </div>
                            @else
                                {{--                                     <svg class="h-8 w-8 text-gray-300" viewBox="0 0 32 32" fill="currentColor">
                                <path
                                    d="M16 16c4.418 0 8-3.582 8-8s-3.582-8-8-8-8 3.582-8 8 3.582 8 8 8zm0 4c-5.523 0-16 2.477-16 8v4h32v-4c0-5.523-10.477-8-16-8z" />
                            </svg> --}}
                                <span>{{ Auth::user()->name }}</span>
                            @endif
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <!-- Rest of dropdown content remains the same -->
                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>
                            @role('admin')
                                <x-dropdown-link :href="route('admin.carousels.index')">
                                    {{ __('messages.admin_panel') }}
                                </x-dropdown-link>
                            @endrole

                            @auth
                                @can('viewDashboard')
                                    <x-dropdown-link :href="route('personal.index')">
                                        Dashboard
                                    </x-dropdown-link>
                                @endcan
                            @endauth

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                        this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                </x-dropdown>
            @endauth
            {{--             @auth
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="text-white hover:text-orange-500 cursor-pointer text-sm md:text-base inline-flex items-center">
                            <div class="underline">{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                                                <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

            @role('admin')
                <x-dropdown-link :href="route('admin.carousels.index')">
                    {{ __('messages.admin_panel') }}
                </x-dropdown-link>
            @endrole
            @auth
                @can('viewDashboard')
                    <x-dropdown-link :href="route('personal.index')">
                        Dashboard
                    </x-dropdown-link>
                @endcan
            @endauth

            <!-- Authentication -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-dropdown-link :href="route('logout')"
                    onclick="event.preventDefault();
                                    this.closest('form').submit();">
                    {{ __('Log Out') }}
                </x-dropdown-link>
            </form>
            </x-slot>
            </x-dropdown>
        @endauth --}}
        </div>

        <!-- Search Icon -->
        <span class="text-white hover:text-gray-200 ml-2 hover:cursor-pointer" onclick="toggleSearch()">
            <svg class="w-6 h-6 search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <svg class="w-6 h-6 close-icon hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </span>

        <!-- Hamburger icon -->
        <span class="text-white hover:text-gray-200 ml-2 hover:cursor-pointer" onclick="toggleHamburgerMenu()">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                </path>
            </svg>
        </span>
        <!-- Language Switcher - Mobile -->
        <div class="flex items-center space-x-4 py-2">
            @if (App::getLocale() !== 'fr')
                <a href="{{ route('locale', 'fr') }}" class="hover:opacity-75 transition-opacity" title="Français">
                    <img src="{{ asset('images/fr-flag.svg') }}" alt="French Flag" class="w-4 h-4 rounded-sm">
                </a>
            @endif

            @if (App::getLocale() !== 'en')
                <a href="{{ route('locale', 'en') }}" class="hover:opacity-75 transition-opacity" title="English">
                    <img src="{{ asset('images/us-flag.svg') }}" alt="English Flag" class="w-4 h-4 rounded-sm">
                </a>
            @endif
        </div>
    </div>
</div>

<!-- Right Panel Overlay -->
<div class="fixed inset-0 bg-black bg-opacity-50 z-[1001] hidden" id="rightPanelOverlay" onclick="closeRightPanel()">
</div>

<!-- Right Panel -->
<div class="fixed top-0 right-0 w-full md:w-80 h-screen bg-white shadow-lg p-4 md:p-6 max-w-full z-[1001] hidden"
    id="rightPanel">
    <button onclick="closeRightPanel()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-800 text-2xl">
        &times;
    </button>

    <div class="flex items-center space-x-4">
        <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}" alt="Logo MEF"
            class="w-12 h-12 md:w-16 md:h-16">
        <div>
            <h2 class="text-xs md:text-sm font-bold text-gray-800">
                {{ __('messages.direction') }}
            </h2>
            <p class="text-gray-600 text-xs md:text-sm">{{ __('messages.republic') }}</p>
        </div>
    </div>

    <!-- Mobile navigation links -->
    <div class="mt-4 md:hidden space-y-2">
        <!-- Language Switcher - Mobile -->
        <div class="flex items-center space-x-4 py-2">
            @if (App::getLocale() !== 'fr')
                <a href="{{ route('locale', 'fr') }}" class="hover:opacity-75 transition-opacity" title="Français">
                    <img src="{{ asset('images/fr-flag.svg') }}" alt="French Flag" class="w-6 h-6 rounded-sm">
                </a>
            @endif

            @if (App::getLocale() !== 'en')
                <a href="{{ route('locale', 'en') }}" class="hover:opacity-75 transition-opacity" title="English">
                    <img src="{{ asset('images/us-flag.svg') }}" alt="English Flag" class="w-6 h-6 rounded-sm">
                </a>
            @endif
        </div>

        <a href="{{ route('liens-utiles') }}" class="block text-gray-700 hover:text-orange-500 p-2 pl-0">
            {{ __('messages.useful_links') }}
        </a>
        <a href="{{ route('contact') }}" class="block text-gray-700 hover:text-orange-500 p-2 pl-0">
            {{ __('messages.contact') }}
        </a>

        @guest
            <a href="{{ route('login') }}" class="block text-gray-700 hover:text-orange-500 p-2 pl-0">
                {{ __('messages.login') }}
            </a>
        @endguest
        @auth
            <!-- Mobile-friendly user menu -->
            <div class="space-y-2">
                <div class="px-2 py-3 pl-0 flex items-center gap-3">
                    <div class="h-8 w-8 rounded-full overflow-hidden shrink-0">
                        @if (Auth::user()->profile_photo)
                            <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}"
                                class="h-full w-full object-cover"
                                alt="{{ Auth::user()->name }} {{ __('Profile Photo') }}">
                        @else
                            <svg class="h-full w-full text-gray-300" viewBox="0 0 32 32" fill="currentColor"
                                aria-hidden="true">
                                <path
                                    d="M16 16c4.418 0 8-3.582 8-8s-3.582-8-8-8-8 3.582-8 8 3.582 8 8 8zm0 4c-5.523 0-16 2.477-16 8v4h32v-4c0-5.523-10.477-8-16-8z" />
                            </svg>
                        @endif
                    </div>
                    <span class="text-orange-500 font-medium truncate">
                        {{ Auth::user()->name }}
                    </span>
                </div>
                <a href="{{ route('profile.edit') }}" class="block text-gray-700 hover:text-orange-500 p-2">
                    {{ __('Profile') }}
                </a>
                @role('admin')
                    <a href="{{ route('admin.carousels.index') }}" class="block text-gray-700 hover:text-orange-500 p-2">
                        {{ __('messages.admin_panel') }}
                    </a>
                @endrole

                @auth
                    @can('viewDashboard')
                        <a href="{{ route('personal.index') }}" class="block text-gray-700 hover:text-orange-500 p-2">
                            {{ __('Dashboard') }}
                        </a>
                    @endcan
                @endauth

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <a href="{{ route('logout') }}" class="block text-gray-700 hover:text-orange-500 p-2"
                        onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </a>
                </form>
            </div>
        @endauth
        {{--         @auth
            <!-- Mobile-friendly user menu -->
            <div class="space-y-2">
                <div class="px-2 py-3 text-orange-500 font-medium pl-0 underline">
                    {{ Auth::user()->name }}
                </div>
                                <a href="{{ route('profile.edit') }}" class="block text-gray-700 hover:text-orange-500 p-2">
                    {{ __('Profile') }}
                </a> 
                @role('admin')
                    <a href="{{ route('admin.carousels.index') }}" class="block text-gray-700 hover:text-orange-500 p-2">
                        {{ __('messages.admin_panel') }}
                    </a>
                @endrole
                @auth
                    @can('viewDashboard')
                        <a href="{{ route('personal.index') }}" class="block text-gray-700 hover:text-orange-500 p-2">
                            {{ __('Dashboard') }}
                        </a>
                    @endcan
                @endauth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <a href="{{ route('logout') }}" class="block text-gray-700 hover:text-orange-500 p-2"
                        onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </a>
                </form>
            </div>
        @endauth --}}
    </div>

    <p class="text-gray-700 text-xs md:text-sm mt-4">
        {{ __('messages.description') }}
    </p>

    <div class="mt-4 space-y-2">
        <div class="flex items-center space-x-2 text-gray-700 text-xs md:text-sm">
            <span class="text-blue-600">📞</span>
            <span>+(509) 29 92 1007</span>
        </div>
        <div class="flex items-center space-x-2 text-gray-700 text-xs md:text-sm">
            <span class="text-blue-600">✉️</span>
            <a href="mailto:info@mef.gouv.ht" class="text-blue-500">info@mef.gouv.ht</a>
        </div>
        <div class="flex items-center space-x-2 text-gray-700 text-xs md:text-sm">
            <span class="text-blue-600">📍</span>
            <span>5, Avenue Charles Sumner Port-au-Prince, Haïti (W.I)</span>
        </div>
    </div>
</div>

{{-- @push('scripts') --}}
<script>
    function toggleSearch() {
        document.querySelectorAll('.search-icon, .close-icon').forEach(icon => {
            icon.classList.toggle('hidden');
        });
        // Add your search toggle logic here
    }

    function toggleHamburgerMenu() {
        document.getElementById('rightPanelOverlay').classList.remove('hidden');
        document.getElementById('rightPanel').classList.remove('hidden');
    }

    function closeRightPanel() {
        document.getElementById('rightPanelOverlay').classList.add('hidden');
        document.getElementById('rightPanel').classList.add('hidden');
    }
</script>
{{-- @endpush --}}
