    <div class="relative text-white z-[1001] bg-[#173052] bg-motif-dots">
        <div class="site-gutter py-4 relative z-10">
            <div class="grid w-full grid-cols-[minmax(0,1fr)_auto] items-center gap-x-4 gap-y-3 lg:flex lg:items-center lg:justify-between lg:gap-6">
                <!-- Logo -->
                <div class="col-start-1 row-start-1 flex min-w-0 items-center lg:shrink-0">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                        <x-optimized-image
                            src="images/setting-logo-1-M13oPLiYoM.png"
                            alt="Logo"
                            width="64"
                            height="64"
                            class="w-10 h-10 md:w-16 md:h-16 transition-transform group-hover:scale-105"
                            loading="eager"
                        />
                        <div class="flex flex-col min-w-0">
                            <span class="text-base md:text-lg font-semibold tracking-tight leading-tight group-hover:text-orange-500 transition-colors">
                                {{ __('messages.direction') }}
                            </span>
                            <span class="text-base font-normal tracking-wide text-white/80 leading-tight">
                                {{ __('messages.republic') }}
                            </span>
                        </div>
                    </a>
                </div>

                <div class="contents lg:flex lg:items-center lg:gap-6 lg:shrink-0">
                <nav class="col-span-2 row-start-2 flex w-full flex-wrap justify-center gap-x-4 gap-y-2 border-t border-white/20 pt-3 text-base font-medium lg:w-auto lg:border-0 lg:pt-0 lg:justify-end">
                    <a href="{{ route('home') }}"
                       class="hover:text-orange-500 transition-colors py-1 border-b-2 border-transparent hover:border-orange-500">
                        {{ __('messages.home') }}
                    </a>
                    <a href="{{ route('faq.index') }}"
                       class="hover:text-orange-500 transition-colors py-1 border-b-2 border-transparent hover:border-orange-500">
                        {{ __('messages.faq') }}
                    </a>
                    <a href="{{ route('glossaire') }}"
                       class="hover:text-orange-500 transition-colors py-1 border-b-2 border-transparent hover:border-orange-500">
                        {{ __('messages.glossaire') }}
                    </a>
                    <a href="{{ route('contact') }}"
                       class="hover:text-orange-500 transition-colors py-1 border-b-2 border-transparent hover:border-orange-500">
                        {{ __('messages.contact') }}
                    </a>
                </nav>

                <div class="col-start-2 row-start-1 flex shrink-0 items-center justify-end gap-3 sm:gap-4">
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
                                          class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center leading-none">
                                    </span>
                                </button>

                                <div x-show="open" x-cloak x-transition
                                     class="absolute right-0 mt-2 w-80 max-w-[calc(100vw-2rem)] bg-white rounded-lg shadow-xl z-50 overflow-hidden">
                                    <div class="flex items-center justify-between px-4 py-3 bg-gray-50 border-b">
                                        <span class="font-semibold text-gray-800 text-base">{{ __('messages.notifications') }}</span>
                                        <form x-show="unreadCount > 0" method="POST" action="{{ route('notifications.markAllAsRead') }}">
                                            @csrf
                                            <button type="submit" class="text-base text-navy hover:underline">
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
                                                            <p class="text-base text-gray-800 font-medium leading-snug">
                                                                {{ $data['message'] ?? 'Notification' }}
                                                            </p>
                                                            <p class="text-base text-gray-600 mt-1">
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

                                        <div @item-removed.window="visible = Math.max(0, visible - 1)"
                                             x-show="visible === 0"
                                             x-cloak
                                             class="py-8 px-4 flex flex-col items-center text-center gap-2">
                                            <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center mb-1">
                                                <svg class="w-5 h-5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                                </svg>
                                            </div>
                                            <p class="text-base font-medium text-gray-700">{{ __('messages.no_notification') }}</p>
                                            <p class="text-base text-gray-600">Vous êtes à jour !</p>
                                        </div>
                                        @if ($bellNotifications->count() > 0)
                                            <div x-show="visible > 0" class="border-t"></div>
                                        @endif
                                    </div>
                                    <div class="px-4 py-2 bg-gray-50 border-t text-center">
                                        <a href="{{ route('notifications.index') }}" class="text-base text-navy hover:underline">
                                            {{ __('messages.see_all_notifications') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endauth

                        @guest
                            <a href="{{ route('login') }}" class="inline-block">
                                <button class="bg-orange-500 hover:bg-orange-600 text-white px-5 py-2.5 rounded transition-colors text-base shadow-md hover:shadow-lg">
                                    {{ __('messages.login') }}
                                </button>
                            </a>
                        @else
                            <x-user-account-dropdown theme="dark" />
                        @endguest

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
    </div>
