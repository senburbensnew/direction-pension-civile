@extends('layouts.main')

@section('title', 'Mes notifications')

@section('content')
<div x-data="{ open: false, deleteUrl: '' }"
     x-on:open-delete-modal.window="open = true; deleteUrl = $event.detail.url"
     class="max-w-3xl mx-auto py-8 px-4">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Mes notifications</h1>
        @if ($notifications->where('read_at', null)->count() > 0)
            <form method="POST" action="{{ route('notifications.markAllAsRead') }}">
                @csrf
                <button type="submit"
                        class="text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition-colors">
                    Tout marquer comme lu
                </button>
            </form>
        @endif
    </div>

    @if (session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow divide-y divide-gray-100">
        @forelse ($notifications as $notification)
            @php $data = $notification->data; $isUnread = is_null($notification->read_at); @endphp
            <div class="flex items-start gap-4 px-5 py-4 {{ $isUnread ? 'bg-blue-50' : '' }}">
                <!-- Icon -->
                <div class="mt-0.5 flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center
                    {{ $isUnread ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-400' }}">
                    @switch($data['icon'] ?? 'info')
                        @case('check-circle')
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @break
                        @case('x-circle')
                            <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @break
                        @case('alert-circle')
                            <svg class="w-5 h-5 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
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

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-800 {{ $isUnread ? 'font-semibold' : '' }}">
                        {{ $data['message'] ?? 'Notification' }}
                    </p>
                    @if (!empty($data['commentaire']))
                        <p class="text-xs text-gray-500 mt-0.5 italic">{{ $data['commentaire'] }}</p>
                    @endif
                    <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2 flex-shrink-0">
                    @if (!empty($data['url']))
                        <form method="POST" action="{{ route('notifications.markAsRead', $notification->id) }}">
                            @csrf
                            <button type="submit" class="text-xs text-blue-600 hover:underline whitespace-nowrap">
                                Voir
                            </button>
                        </form>
                    @elseif ($isUnread)
                        <form method="POST" action="{{ route('notifications.markAsRead', $notification->id) }}">
                            @csrf
                            <button type="submit" class="text-xs text-gray-500 hover:underline whitespace-nowrap">
                                Marquer lu
                            </button>
                        </form>
                    @endif

                    <button type="button"
                            onclick="window.dispatchEvent(new CustomEvent('open-delete-modal', { detail: { url: '{{ route('notifications.destroy', $notification->id) }}' } }))"
                            class="text-gray-300 hover:text-red-400 transition-colors" title="Supprimer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        @empty
            <div class="py-16 text-center text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <p class="text-sm">Aucune notification pour le moment.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $notifications->links() }}
    </div>

    {{-- Delete confirmation modal --}}
    <div x-show="open"
         x-cloak
         class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/50">
        <div class="bg-white w-full max-w-sm rounded-lg shadow-xl p-6" @click.stop>
            <div class="flex items-center gap-3 mb-4">
                <div class="flex-shrink-0 w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Supprimer la notification</h3>
            </div>
            <p class="text-sm text-gray-600 mb-6">
                Êtes-vous sûr de vouloir supprimer cette notification ? Cette action est <strong>irréversible</strong>.
            </p>
            <div class="flex justify-end gap-3">
                <button type="button" @click="open = false"
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md text-sm transition-colors">
                    Annuler
                </button>
                <form :action="deleteUrl" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md text-sm transition-colors">
                        Supprimer définitivement
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
