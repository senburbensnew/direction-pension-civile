@props([
    'theme' => 'light',
])

@auth
    @php
        $unreadCount = auth()->user()->unreadNotifications()->count();
        $bellNotifications = auth()->user()->notifications()->latest()->take(8)->get();
        $triggerClass = $theme === 'dark'
            ? 'relative text-white hover:text-orange-400 transition-colors focus:outline-none p-1'
            : 'relative text-gray-500 hover:text-gray-700 transition-colors focus:outline-none p-1';
    @endphp

    <div x-data="{ open: false, unreadCount: {{ $unreadCount }} }"
         @notification-read.window="unreadCount = Math.max(0, unreadCount - 1)"
         class="relative">
        <button type="button"
                @click="open = !open"
                @click.outside="open = false"
                class="{{ $triggerClass }}"
                aria-label="{{ __('messages.notifications') }}"
                :aria-expanded="open">
            @if ($theme === 'dark')
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            @else
                <i class="fas fa-bell text-lg" aria-hidden="true"></i>
            @endif
            <span x-show="unreadCount > 0"
                  x-text="unreadCount > 9 ? '9+' : unreadCount"
                  x-cloak
                  class="absolute -top-0.5 -right-0.5 bg-red-500 text-white text-[10px] rounded-full min-w-[1rem] h-4 px-1 flex items-center justify-center leading-none">
            </span>
        </button>

        <div x-show="open"
             x-cloak
             x-transition
             class="absolute right-0 mt-2 w-80 max-w-[calc(100vw-2rem)] bg-white rounded-lg shadow-xl z-[60] overflow-hidden text-left">
            <div class="flex items-center justify-between px-4 py-3 bg-gray-50 border-b border-gray-100">
                <span class="font-semibold text-gray-800 text-sm">{{ __('messages.notifications') }}</span>
                <form x-show="unreadCount > 0" method="POST" action="{{ route('notifications.markAllAsRead') }}">
                    @csrf
                    <button type="submit" class="text-xs text-[#173052] hover:underline font-medium">
                        {{ __('messages.mark_all_as_read') }}
                    </button>
                </form>
            </div>

            <div x-data="{ visible: {{ $bellNotifications->count() }} }">
                <ul class="divide-y divide-gray-100 max-h-72 overflow-y-auto">
                    @foreach ($bellNotifications as $notification)
                        @php $data = $notification->data; @endphp
                        <li x-data="{ read: {{ is_null($notification->read_at) ? 'false' : 'true' }}, gone: false }"
                            x-show="!gone"
                            x-init="
                                $watch('read', v => { if (v) window.dispatchEvent(new Event('notification-read')) });
                                $watch('gone', v => { if (v) $dispatch('item-removed') });
                            "
                            @notification-deleted.window="if ($event.detail.id === '{{ $notification->id }}') gone = true"
                            :class="read ? 'bg-white' : 'bg-sky-50/80'"
                            class="group relative hover:bg-gray-50 transition-colors">
                            <a href="{{ route('notifications.open', $notification->id) }}"
                               @click="read = true"
                               class="block px-4 py-3 pr-10">
                                <p class="text-sm text-gray-800 leading-snug"
                                   :class="read ? 'font-normal' : 'font-semibold'">
                                    {{ $data['message'] ?? 'Notification' }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $notification->created_at->locale(app()->getLocale())->diffForHumans() }}
                                </p>
                            </a>
                            <button type="button"
                                    x-show="read"
                                    x-cloak
                                    class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 focus:opacity-100 text-gray-300 hover:text-red-500 transition-colors p-1 rounded"
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
                                <i class="fas fa-times text-sm"></i>
                            </button>
                        </li>
                    @endforeach
                </ul>

                <div @item-removed.window="visible = Math.max(0, visible - 1)"
                     x-show="visible === 0"
                     x-cloak
                     class="py-8 px-4 flex flex-col items-center text-center gap-1">
                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center mb-1">
                        <i class="fas fa-bell-slash text-gray-300"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-700">{{ __('messages.no_notification') }}</p>
                    <p class="text-xs text-gray-500">Vous êtes à jour !</p>
                </div>
            </div>

            <div class="px-4 py-2.5 bg-gray-50 border-t border-gray-100 text-center">
                <a href="{{ route('notifications.index') }}"
                   class="text-sm font-medium text-[#173052] hover:underline">
                    {{ __('messages.see_all_notifications') }}
                </a>
            </div>
        </div>
    </div>
@endauth
