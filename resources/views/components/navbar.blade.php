    <div class="container mx-auto relative bg-blue-900 text-white bg-cover bg-center z-[1001]"
            style="background-image: url({{ asset('images/carousel/slide2.jpg') }});">
        <div class="absolute inset-0 bg-blue-900 bg-opacity-70"></div>

        <div class="container mx-auto px-4 py-4 relative z-10">
            <div class="flex flex-wrap lg:flex-nowrap justify-between lg:justify-start items-center gap-x-3 gap-y-0">
                <!-- Logo — lg:mr-auto pushes nav+user to the right on large screens -->
                <div class="flex items-center flex-shrink-0 lg:mr-auto">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                        <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}"
                             alt="Logo"
                             class="w-10 h-10 md:w-16 md:h-16 transition-transform group-hover:scale-105">
                        <div class="flex flex-col">
                            <span class="font-semibold text-base md:text-lg leading-tight group-hover:text-orange-500 transition-colors">
                                {{ __('messages.direction') }}
                            </span>
                            <span class="text-sm leading-tight">
                                {{ __('messages.republic') }}
                            </span>
                        </div>
                    </a>
                </div>

                <!-- Main Navigation
                     Mobile: order-3 + w-full → drops to its own full-width row below logo & user section
                     Desktop: order-2 + w-auto → sits inline, to the left of the user section -->
                <nav class="order-3 lg:order-2 w-full lg:w-auto flex flex-wrap lg:flex-nowrap justify-center gap-3 lg:gap-5 text-base
                            mt-3 pt-3 lg:mt-0 lg:pt-0 border-t border-white/20 lg:border-0">
                    <a href="{{ route('home') }}"
                       class="hover:text-orange-500 transition-colors py-1 border-b-2 border-transparent hover:border-orange-500">
                        {{ __('messages.home') }}
                    </a>
                    <a href="{{ route('liens-utiles') }}"
                       class="hover:text-orange-500 transition-colors py-1 border-b-2 border-transparent hover:border-orange-500">
                        {{ __('messages.links') }}
                    </a>
                    <a href="{{ route('contact') }}"
                       class="hover:text-orange-500 transition-colors py-1 border-b-2 border-transparent hover:border-orange-500">
                        {{ __('messages.contact') }}
                    </a>
                    <a href="{{ route('faq.index') }}"
                       class="hover:text-orange-500 transition-colors py-1 border-b-2 border-transparent hover:border-orange-500">
                        {{ __('messages.faq') }}
                    </a>
                    <a href="{{ route('glossaire') }}"
                       class="hover:text-orange-500 transition-colors py-1 border-b-2 border-transparent hover:border-orange-500">
                        {{ __('messages.glossaire') }}
                    </a>
                </nav>

                <!-- User Section: order-2 on sm/md (beside logo), order-3 on lg+ (rightmost) -->
                <div class="order-2 lg:order-3 flex-shrink-0 flex items-center gap-4">
                        <!-- Notification Bell -->
                        @auth
                            @php $unreadCount = auth()->user()->unreadNotifications()->count(); @endphp
                            <div x-data="{ open: false, unreadCount: {{ $unreadCount }} }"
                                 @notification-read.window="unreadCount = Math.max(0, unreadCount - 1)"
                                 class="relative">
                                <button @click="open = !open" @click.outside="open = false"
                                        class="relative text-white hover:text-orange-400 transition-colors focus:outline-none"
                                        aria-label="Notifications">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                    <span x-show="unreadCount > 0"
                                          x-text="unreadCount > 9 ? '9+' : unreadCount"
                                          x-cloak
                                          class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center font-bold leading-none">
                                    </span>
                                </button>

                                <!-- Dropdown panel -->
                                <div x-show="open" x-cloak x-transition
                                     class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl z-50 overflow-hidden">
                                    <div class="flex items-center justify-between px-4 py-3 bg-gray-50 border-b">
                                        <span class="font-semibold text-gray-700 text-sm">{{ __('messages.notifications') }}</span>
                                        <form x-show="unreadCount > 0" method="POST" action="{{ route('notifications.markAllAsRead') }}">
                                            @csrf
                                            <button type="submit" class="text-xs text-blue-600 hover:underline">
                                                {{ __('messages.mark_all_as_read') }}
                                            </button>
                                        </form>
                                    </div>
                                    @php $bellNotifications = auth()->user()->notifications()->latest()->take(8)->get(); @endphp
                                    <div x-data="{ visible: {{ $bellNotifications->count() }} }">
                                        <ul class="divide-y divide-gray-100 max-h-72 overflow-y-auto">
                                            @forelse ($bellNotifications as $notification)
                                                @php $data = $notification->data; @endphp
                                                <li x-data="{ read: {{ is_null($notification->read_at) ? 'false' : 'true' }}, gone: false }"
                                                    x-show="!gone"
                                                    x-init="
                                                        $watch('read', v => { if (v) window.dispatchEvent(new Event('notification-read')) });
                                                        $watch('gone', v => { if (v) $dispatch('item-removed') });
                                                    "
                                                    @notification-deleted.window="if ($event.detail.id === '{{ $notification->id }}') gone = true"
                                                    :class="read ? 'bg-white' : 'bg-blue-50'"
                                                    class="cursor-pointer px-4 py-3 hover:bg-gray-50 transition-colors"
                                                    @click="fetch('{{ route('notifications.markAsRead', $notification->id) }}', {
                                                        method: 'POST',
                                                        headers: {
                                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                            'Accept': 'application/json',
                                                            'Content-Type': 'application/json'
                                                        }
                                                    }).then(r => r.json()).then(d => { if (d.ok) read = true; })">
                                                    <div class="flex items-start justify-between gap-2">
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-sm text-gray-800 font-medium leading-snug">
                                                                {{ $data['message'] ?? 'Notification' }}
                                                            </p>
                                                            <p class="text-xs text-gray-400 mt-1">
                                                                {{ $notification->created_at->diffForHumans() }}
                                                            </p>
                                                        </div>
                                                        <button type="button"
                                                                x-show="read"
                                                                class="flex-shrink-0 text-gray-300 hover:text-red-500 transition-colors text-lg leading-none"
                                                                title="Supprimer"
                                                                @click.stop="fetch('{{ route('notifications.destroy', $notification->id) }}', {
                                                                    method: 'DELETE',
                                                                    headers: {
                                                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                                        'Accept': 'application/json'
                                                                    }
                                                                }).then(r => r.json()).then(d => {
                                                                    if (d.ok) window.dispatchEvent(new CustomEvent('notification-deleted', { detail: { id: '{{ $notification->id }}' } }))
                                                                })">
                                                            &times;
                                                        </button>
                                                    </div>
                                                </li>
                                            @empty
                                            @endforelse
                                        </ul>

                                        {{-- Empty state: shown when DB had no items, or all were deleted in-session --}}
                                        <div @item-removed.window="visible = Math.max(0, visible - 1)"
                                             x-show="visible === 0"
                                             x-cloak
                                             class="py-8 px-4 flex flex-col items-center text-center gap-2">
                                            <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center mb-1">
                                                <svg class="w-5 h-5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                                </svg>
                                            </div>
                                            <p class="text-sm font-medium text-gray-500">{{ __('messages.no_notification') }}</p>
                                            <p class="text-xs text-gray-400">Vous êtes à jour !</p>
                                        </div>
                                        @if ($bellNotifications->count() > 0)
                                            <div x-show="visible > 0" class="border-t"></div>
                                        @endif
                                    </div>
                                    <div class="px-4 py-2 bg-gray-50 border-t text-center">
                                        <a href="{{ route('notifications.index') }}" class="text-xs text-blue-600 hover:underline">
                                            {{ __('messages.see_all_notifications') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endauth

                        <!-- Login/User Info -->
                        @guest
                            <a href="{{ route('login') }}" class="inline-block">
                                <button class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded transition-colors text-sm md:text-base shadow-md hover:shadow-lg">
                                    {{ __('messages.login') }}
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
                                        <x-dropdown-link :href="route('admin.dashboard.index')">
                                            <i class="fas fa-cog mr-2 text-gray-400"></i>
                                            {{ __('messages.admin_panel') }}
                                        </x-dropdown-link>
                                    @endrole

                                    @auth
                                        @unlessrole('admin|direction|secretariat|service_liquidation|service_formalite|service_controle_placement|service_comptabilite|service_assurance')
                                            <x-dropdown-link :href="route('personal.index')">
                                                <i class="fas fa-tachometer-alt mr-2 text-gray-400"></i>
                                                {{ __('messages.my_requests') }}
                                            </x-dropdown-link>
                                        @endunlessrole
                                    @endauth

                                    @auth
                                        @role([ 'direction',
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
                                                {{ __('messages.basket') }}
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
                        <div class="flex items-center gap-2">
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
                        </div>
                </div>
            </div>
        </div>
    </div>

 