@extends('layouts.main')

@section('title', 'Mes notifications')

@section('content')
<div x-data="{ open: false, deleteUrl: '', deleteId: '', pageVisible: {{ $notifications->count() }} }"
     @notification-deleted.window="pageVisible = Math.max(0, pageVisible - 1)"
     class="max-w-3xl mx-auto py-10 px-4">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Notifications</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                {{ $notifications->total() }} notification{{ $notifications->total() > 1 ? 's' : '' }}
                @php $unreadTotal = auth()->user()->unreadNotifications()->count(); @endphp
                @if ($unreadTotal > 0)
                    &mdash; <span class="text-blue-600 font-medium">{{ $unreadTotal }} non lue{{ $unreadTotal > 1 ? 's' : '' }}</span>
                @endif
            </p>
        </div>
        @if ($unreadTotal > 0)
            <form method="POST" action="{{ route('notifications.markAllAsRead') }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-1.5 text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-sm transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Tout marquer comme lu
                </button>
            </form>
        @endif
    </div>

    @if (session('success'))
        <div class="mb-6 flex items-center gap-2 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Notification list --}}
    <div class="space-y-2">
        @forelse ($notifications as $notification)
            @php
                $data     = $notification->data;
                $isUnread = is_null($notification->read_at);
                $icon     = $data['icon'] ?? 'info';
            @endphp

            <div x-data="{ read: {{ $isUnread ? 'false' : 'true' }}, gone: false }"
                 x-show="!gone"
                 x-init="$watch('read', v => { if (v) window.dispatchEvent(new Event('notification-read')) })"
                 @notification-deleted.window="if ($event.detail.id === '{{ $notification->id }}') gone = true"
                 :class="read
                    ? 'bg-white border-gray-200'
                    : 'bg-blue-50 border-blue-200'"
                 class="relative flex items-start gap-4 px-5 py-4 rounded-xl border shadow-sm transition-colors group cursor-pointer"
                 @click="if (!read) {
                     fetch('{{ route('notifications.markAsRead', $notification->id) }}', {
                         method: 'POST',
                         headers: {
                             'X-CSRF-TOKEN': '{{ csrf_token() }}',
                             'Accept': 'application/json',
                             'Content-Type': 'application/json'
                         }
                     }).then(r => r.json()).then(d => { if (d.ok) read = true; })
                 }">

                {{-- Icon badge --}}
                @php
                    $iconColor = match($icon) {
                        'check-circle'       => 'bg-green-100 text-green-600',
                        'x-circle'           => 'bg-red-100 text-red-500',
                        'alert-circle'       => 'bg-yellow-100 text-yellow-600',
                        'arrow-right-circle' => 'bg-indigo-100 text-indigo-600',
                        'file-plus'          => 'bg-purple-100 text-purple-600',
                        default              => 'bg-blue-100 text-blue-600',
                    };
                @endphp
                <div class="flex-shrink-0 mt-0.5 w-10 h-10 rounded-full flex items-center justify-center {{ $iconColor }}">
                    @switch($icon)
                        @case('check-circle')
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @break
                        @case('x-circle')
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @break
                        @case('alert-circle')
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            @break
                        @case('arrow-right-circle')
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @break
                        @case('file-plus')
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            @break
                        @default
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @endswitch
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    <p :class="read ? 'text-gray-700' : 'text-gray-900 font-semibold'"
                       class="text-sm leading-snug">
                        {{ $data['message'] ?? 'Notification' }}
                    </p>
                    @if (!empty($data['commentaire']))
                        <p class="text-xs text-gray-500 mt-1 italic leading-relaxed">
                            {{ $data['commentaire'] }}
                        </p>
                    @endif
                    <div class="flex items-center gap-3 mt-2">
                        <span class="text-xs text-gray-400">
                            <i class="far fa-clock mr-1"></i>{{ $notification->created_at->diffForHumans() }}
                        </span>
                        <span x-show="!read" class="inline-flex items-center gap-1 text-xs text-blue-600 font-medium">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500 inline-block"></span>
                            Non lue — cliquez pour marquer comme lue
                        </span>
                    </div>
                </div>

                {{-- Delete button: only visible once the notification is read --}}
                <button type="button"
                        x-show="read"
                        class="flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity text-gray-300 hover:text-red-500 p-1 rounded"
                        title="Supprimer"
                        @click.stop="deleteUrl = '{{ route('notifications.destroy', $notification->id) }}'; deleteId = '{{ $notification->id }}'; open = true">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>

                {{-- Unread dot --}}
                @if ($isUnread)
                    <span class="absolute top-4 right-4 w-2 h-2 rounded-full bg-blue-500"
                          x-show="!read" x-cloak></span>
                @endif
            </div>
        @empty
        @endforelse

        {{-- Empty state: DB empty OR all items deleted in-session --}}
        <div x-show="pageVisible === 0"
             x-cloak
             class="py-20 text-center">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </div>
            <p class="text-gray-500 font-medium">Aucune notification</p>
            <p class="text-gray-400 text-sm mt-1">Vous êtes à jour !</p>
        </div>
    </div>

    {{-- Pagination --}}
    @if ($notifications->hasPages())
        <div class="mt-8">
            {{ $notifications->links() }}
        </div>
    @endif

    {{-- Delete confirmation modal --}}
    <div x-show="open"
         x-cloak
         class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl p-6 mx-4" @click.stop>
            <div class="flex items-center gap-3 mb-4">
                <div class="flex-shrink-0 w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Supprimer la notification</h3>
                    <p class="text-xs text-gray-500">Cette action est irréversible.</p>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" @click="open = false"
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition-colors">
                    Annuler
                </button>
                <button type="button"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm"
                        @click="fetch(deleteUrl, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        }).then(r => r.json()).then(d => {
                            if (d.ok) {
                                open = false;
                                window.dispatchEvent(new CustomEvent('notification-deleted', { detail: { id: deleteId } }));
                            }
                        })">
                    Supprimer
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
