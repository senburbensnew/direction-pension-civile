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
            <a href="#" class="text-white hover:text-orange-500 cursor-pointer text-sm md:text-base">
                {{ __('messages.contact') }}
            </a>
            <a href="{{ route('login') }}" class="text-white hover:text-orange-500 cursor-pointer text-sm md:text-base">
                {{ __('messages.login') }}
            </a>
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
                    <img src="{{ asset('images/fr-flag.svg') }}" alt="French Flag" class="w-6 h-6 rounded-sm">
                </a>
            @endif

            @if (App::getLocale() !== 'en')
                <a href="{{ route('locale', 'en') }}" class="hover:opacity-75 transition-opacity" title="English">
                    <img src="{{ asset('images/us-flag.svg') }}" alt="English Flag" class="w-6 h-6 rounded-sm">
                </a>
            @endif
        </div>
    </div>
</div>

<!-- Right Panel Overlay -->
<div class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden" id="rightPanelOverlay" onclick="closeRightPanel()"></div>

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

        <a href="{{ route('liens-utiles') }}" class="block text-gray-700 hover:text-orange-500 p-2">
            {{ __('messages.useful_links') }}
        </a>
        <a href="#" class="block text-gray-700 hover:text-orange-500 p-2">
            {{ __('messages.contact') }}
        </a>
        <a href="{{ route('login') }}" class="block text-gray-700 hover:text-orange-500 p-2">
            {{ __('messages.login') }}
        </a>
    </div>

    <p class="text-gray-700 text-xs md:text-sm mt-4">
        {{ __('messages.description') }}
    </p>

    <div class="mt-4 space-y-2">
        <div class="flex items-center space-x-2 text-gray-700 text-xs md:text-sm">
            <span class="text-blue-600">📞</span>
            <span>+ (509) 2992 1067 / 2992 1028</span>
        </div>
        <div class="flex items-center space-x-2 text-gray-700 text-xs md:text-sm">
            <span class="text-blue-600">✉️</span>
            <a href="mailto:info@mef.gouv.ht" class="text-blue-500">info@mef.gouv.ht</a>
        </div>
        <div class="flex items-center space-x-2 text-gray-700 text-xs md:text-sm">
            <span class="text-blue-600">📍</span>
            <span>5, Ave. Charles Summer, Port-au-Prince, Haïti</span>
        </div>
    </div>
</div>

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
