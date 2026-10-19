@props([
    'theme' => 'light',
])

@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Storage;

    $triggerClass = $theme === 'dark'
        ? 'text-white hover:text-orange-500 focus:ring-orange-500'
        : 'text-gray-800 hover:text-orange-600 focus:ring-orange-500/40 border border-transparent hover:border-orange-200';
@endphp

@auth
    <x-dropdown align="right" width="48">
        <x-slot name="trigger">
            <button type="button"
                class="{{ $triggerClass }} cursor-pointer text-sm sm:text-base flex items-center transition-colors focus:outline-none focus:ring-2 focus:ring-opacity-50 rounded-md p-1 max-w-[14rem] sm:max-w-xs">
                @if (Auth::user()->profile_photo)
                    <div class="h-8 w-8 rounded-full overflow-hidden mr-2 border-2 border-orange-500 flex-shrink-0">
                        <img src="{{ Storage::url(Auth::user()->profile_photo) }}"
                             class="h-8 w-8 object-cover" alt="">
                    </div>
                @else
                    <div class="h-8 w-8 rounded-full overflow-hidden mr-2 bg-gray-200 flex items-center justify-center border-2 border-orange-500 flex-shrink-0">
                        <svg class="h-5 w-5 text-gray-500" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z" clip-rule="evenodd" />
                        </svg>
                    </div>
                @endif
                <span class="hidden md:inline truncate font-medium uppercase tracking-wide">{{ Auth::user()->name }}</span>
                <div class="ms-1 flex-shrink-0">
                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
            </button>
        </x-slot>
        <x-slot name="content">
            @if(! auth()->user()?->isProvisionnel())
                <x-dropdown-link :href="route('profile.edit')">
                    <i class="fas fa-user-circle mr-2 text-gray-400"></i>
                    {{ __('Profile') }}
                </x-dropdown-link>
                @if(auth()->user()?->hasCategory('pensionne') || auth()->user()?->hasRole('pensionne'))
                    <x-dropdown-link :href="route('compte.affiliations.index')">
                        <i class="fas fa-id-card mr-2 text-gray-400"></i>
                        Mes codes pension
                    </x-dropdown-link>
                @endif
                @if(auth()->user()?->canSeePortalNavBeneficiairesMandats())
                    <x-dropdown-link :href="route('compte.beneficiaires.index')">
                        <i class="fas fa-users mr-2 text-gray-400"></i>
                        Mes bénéficiaires
                    </x-dropdown-link>
                    <x-dropdown-link :href="route('compte.mandats.index')">
                        <i class="fas fa-file-signature mr-2 text-gray-400"></i>
                        Mes mandats
                    </x-dropdown-link>
                @endif
            @endif
            @role('admin')
                <x-dropdown-link :href="route('admin.dashboard.index')">
                    <i class="fas fa-cog mr-2 text-gray-400"></i>
                    {{ __('messages.admin_panel') }}
                </x-dropdown-link>
            @endrole
            @if(auth()->user()?->canSeePortalNavMesDemandes())
                <x-dropdown-link :href="route('personal.dashboard')">
                    <i class="fas fa-folder-open mr-2 text-gray-400"></i>
                    {{ __('messages.my_requests') }}
                </x-dropdown-link>
            @endif
            @if(auth()->user()?->canSeePortalNavCorbeille())
                <x-dropdown-link :href="route('personal.cart')">
                    <i class="fas fa-shopping-cart mr-2 text-gray-400"></i>
                    {{ __('messages.basket') }}
                </x-dropdown-link>
            @endif

            <hr class="my-1 border-gray-200">

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-dropdown-link :href="route('logout')"
                    onclick="event.preventDefault(); this.closest('form').submit();">
                    <i class="fas fa-sign-out-alt mr-2 text-gray-400"></i>
                    {{ __('Log Out') }}
                </x-dropdown-link>
            </form>
        </x-slot>
    </x-dropdown>
@endauth
