    <div class="container mx-auto relative bg-blue-900 text-white bg-cover bg-center z-[1001]"
            style="background-image: url({{ asset('images/carousel/slide2.jpg') }});">
        <div class="absolute inset-0 bg-blue-900 bg-opacity-70"></div>

        <div class="container mx-auto px-4 py-4 relative z-10">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">

                <!-- Logo and Title -->
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                        <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}"
                             alt="Logo"
                             class="w-10 h-10 md:w-16 md:h-16 transition-transform group-hover:scale-105">
                        <div class="flex flex-col">
                            <span class="font-semibold text-sm md:text-lg leading-tight group-hover:text-orange-500 transition-colors">
                                {{ __('messages.direction') }}
                            </span>
                            <span class="text-xs md:text-sm leading-tight">
                                {{ __('messages.republic') }}
                            </span>
                        </div>
                    </a>
                </div>

                <!-- Navigation and User Menu -->
                <div class="flex flex-col md:flex-row items-start md:items-center gap-4 md:gap-6 w-full md:w-auto">

                    <!-- Main Navigation -->
                    <nav class="flex flex-wrap gap-4 md:gap-6 text-sm md:text-base">
                        <a href="{{ route('home') }}"
                           class="hover:text-orange-500 transition-colors py-1 border-b-2 border-transparent hover:border-orange-500">
                            Accueil
                        </a>
                        <a href="{{ route('liens-utiles') }}"
                           class="hover:text-orange-500 transition-colors py-1 border-b-2 border-transparent hover:border-orange-500">
                            Liens
                        </a>
                        <a href="{{ route('contact') }}"
                           class="hover:text-orange-500 transition-colors py-1 border-b-2 border-transparent hover:border-orange-500">
                            Contact
                        </a>
                        <a href="{{ route('faq.index') }}"
                           class="hover:text-orange-500 transition-colors py-1 border-b-2 border-transparent hover:border-orange-500">
                            FAQ
                        </a>
                        <a href="{{ route('glossaire') }}"
                           class="hover:text-orange-500 transition-colors py-1 border-b-2 border-transparent hover:border-orange-500">
                            Glossaire
                        </a>
                    </nav>

                    <!-- User Section -->
                    <div class="flex flex-col md:flex-row items-start md:items-center gap-4">

                        <!-- Login/User Info -->
                        @guest
                            <a href="{{ route('login') }}" class="inline-block">
                                <button class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded transition-colors text-sm md:text-base shadow-md hover:shadow-lg">
                                    Connexion
                                </button>
                            </a>
                        @else
                            <!-- User Dropdown -->
                            <x-dropdown align="right" width="48" >
                                <x-slot name="trigger">
                                    <button class="text-white hover:text-orange-500 cursor-pointer text-sm md:text-base flex items-center transition-colors focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-opacity-50 rounded-md p-1">
                                        @if (Auth::user()->profile_photo)
                                            <div class="h-8 w-8 rounded-full overflow-hidden mr-2 border-2 border-orange-500">
                                                <img src="{{ Storage::url(Auth::user()->profile_photo) }}"
                                                    class="h-8 w-8 object-cover" alt="{{ __('Profile Photo') }}">
                                            </div>
                                        @else
                                            <div class="h-8 w-8 rounded-full overflow-hidden mr-2 bg-gray-200 flex items-center justify-center border-2 border-orange-500">
                                                <svg class="h-5 w-5 text-gray-500" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        @endif
                                        <span class="hidden md:inline">{{ Auth::user()->name }}</span>
                                        <div class="ms-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </button>
                                </x-slot>
                                <x-slot name="content" style="">
                                    <x-dropdown-link :href="route('profile.edit')">
                                        <i class="fas fa-user-circle mr-2 text-gray-400"></i>
                                        {{ __('Profile') }}
                                    </x-dropdown-link>
                                    @role('admin')
                                        <x-dropdown-link :href="route('admin.carousels.index')">
                                            <i class="fas fa-cog mr-2 text-gray-400"></i>
                                            {{ __('messages.admin_panel') }}
                                        </x-dropdown-link>
                                    @endrole

                                    @auth
                                        @can('viewDashboard')
                                            <x-dropdown-link :href="route('personal.index')">
                                                <i class="fas fa-tachometer-alt mr-2 text-gray-400"></i>
                                                Mon Dashboard
                                            </x-dropdown-link>
                                        @endcan
                                    @endauth

                                    @auth
                                        @role([ 'admin',
                                                'direction',
                                                'secretariat',
                                                'service_liquidation',
                                                'service_formalite',
                                                'service_controle_placement',
                                                'service_comptabilite',
                                                'service_assurance',
                                                'administration',
                                        ])
                                            <x-dropdown-link :href="route('personal.cart')">
                                                <i class="fas fa-shopping-cart mr-2 text-gray-400"></i>
                                                Corbeille
                                            </x-dropdown-link>
                                        @endrole
                                    @endauth

                                    <hr class="my-1 border-gray-200">

                                    <!-- Authentication -->
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <x-dropdown-link :href="route('logout')"
                                            onclick="event.preventDefault();
                                            this.closest('form').submit();">
                                            <i class="fas fa-sign-out-alt mr-2 text-gray-400"></i>
                                            {{ __('Log Out') }}
                                        </x-dropdown-link>
                                    </form>
                                </x-slot>
                            </x-dropdown>
                        @endguest

                        <!-- Language Selector -->
 {{--                         <div class="flex items-center gap-2">
                            @if (App::getLocale() !== 'fr')
                                <a href="{{ route('locale', 'fr') }}" class="hover:opacity-75 transition-opacity" title="Français">
                                    <img src="{{ asset('images/france-flag-icon.svg') }}" alt="French Flag" class="w-4 h-4 rounded-sm">
                                </a>
                            @endif

                            @if (App::getLocale() !== 'ht')
                                <a href="{{ route('locale', 'ht') }}" class="hover:opacity-75 transition-opacity" title="Kreyòl">
                                    <img src="{{ asset('images/haiti-flag-icon.svg') }}" alt="Haitian Flag Flag" class="w-4 h-4 rounded-sm">
                                </a>
                            @endif
                        </div> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
