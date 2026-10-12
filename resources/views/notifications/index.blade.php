<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 w-full">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Mes notifications
                </h2>
                <p class="text-sm text-gray-500 mt-0.5 hidden sm:block">
                    Alertes sur vos dossiers et activités du portail
                </p>
            </div>
        </div>
    </x-slot>

    @php
        $unreadTotal = auth()->user()->unreadNotifications()->count();
        $readTotal = max(0, $notifications->total() - $unreadTotal);
    @endphp

    <x-portal-page bottom="pb-6" class="space-y-6"
         x-data="{ open: false, deleteUrl: '', deleteId: '', pageVisible: {{ $notifications->count() }} }"
         @notification-deleted.window="pageVisible = Math.max(0, pageVisible - 1)">

        <header class="dossier-header px-5 py-5 sm:px-6 border-l-4 border-l-[#173052]">
            <p class="dossier-kicker">Mon espace</p>
            <h1 class="mt-1 text-xl font-semibold text-[#173052] tracking-tight">Mes notifications</h1>
            <p class="mt-2 text-sm text-gray-600">Alertes sur vos dossiers et activités du portail.</p>
        </header>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="dossier-stat">
                <p class="dossier-stat__label">Total</p>
                <p class="dossier-stat__value text-2xl">{{ $notifications->total() }}</p>
            </div>
            <div class="dossier-stat border-l-4 border-l-[#173052]">
                <p class="dossier-stat__label">Non lues</p>
                <p class="dossier-stat__value text-2xl">{{ $unreadTotal }}</p>
            </div>
            <div class="dossier-stat bg-gray-50/80">
                <p class="dossier-stat__label">Lues</p>
                <p class="dossier-stat__value text-2xl">{{ $readTotal }}</p>
            </div>
        </div>

        {{-- Toolbar --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <p class="text-sm text-gray-600">
                @if ($unreadTotal > 0)
                    <span class="inline-flex items-center gap-1.5 text-[#173052] font-medium">
                        <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                        {{ $unreadTotal }} notification{{ $unreadTotal > 1 ? 's' : '' }} à consulter
                    </span>
                @else
                    <span class="text-green-700 font-medium"><i class="fas fa-check-circle mr-1"></i> Tout est à jour</span>
                @endif
            </p>
            @if ($unreadTotal > 0)
                <form method="POST" action="{{ route('notifications.markAllAsRead') }}">
                    @csrf
                    <button type="submit" class="dossier-btn-primary w-full sm:w-auto justify-center">
                        <i class="fas fa-check-double text-xs"></i>
                        Tout marquer comme lu
                    </button>
                </form>
            @endif
        </div>

        {{-- List --}}
        <section class="dossier-card overflow-hidden">
            <div class="dossier-card-head">
                <h3>Fil d'activité</h3>
            </div>

            <div class="divide-y divide-gray-100">
                @forelse ($notifications as $notification)
                    @php
                        $data = $notification->data;
                        $isUnread = is_null($notification->read_at);
                        $icon = $data['icon'] ?? 'info';
                        $actionUrl = $data['url'] ?? null;
                        $iconColor = match ($icon) {
                            'check-circle' => 'bg-emerald-100 text-emerald-600',
                            'x-circle' => 'bg-red-100 text-red-600',
                            'alert-circle' => 'bg-amber-100 text-amber-600',
                            'arrow-right-circle' => 'bg-indigo-100 text-indigo-600',
                            'file-plus' => 'bg-violet-100 text-violet-600',
                            default => 'bg-sky-100 text-sky-600',
                        };
                    @endphp

                    <div x-data="{ read: {{ $isUnread ? 'false' : 'true' }}, gone: false }"
                         x-show="!gone"
                         x-init="$watch('read', v => { if (v) window.dispatchEvent(new Event('notification-read')) })"
                         @notification-deleted.window="if ($event.detail.id === '{{ $notification->id }}') gone = true"
                         class="group relative flex items-start gap-4 px-5 sm:px-6 py-5 transition-colors border-l-4"
                         :class="read
                            ? 'bg-white hover:bg-gray-50/80 border-l-transparent'
                            : 'bg-sky-50/60 hover:bg-sky-50 border-l-[#173052]'">

                        <div class="flex-shrink-0 w-11 h-11 rounded-xl flex items-center justify-center {{ $iconColor }}">
                            @switch($icon)
                                @case('check-circle')
                                    <i class="fas fa-check-circle text-lg"></i>
                                    @break
                                @case('x-circle')
                                    <i class="fas fa-times-circle text-lg"></i>
                                    @break
                                @case('alert-circle')
                                    <i class="fas fa-exclamation-triangle text-lg"></i>
                                    @break
                                @case('arrow-right-circle')
                                    <i class="fas fa-arrow-circle-right text-lg"></i>
                                    @break
                                @case('file-plus')
                                    <i class="fas fa-file-medical text-lg"></i>
                                    @break
                                @default
                                    <i class="fas fa-info-circle text-lg"></i>
                            @endswitch
                        </div>

                        <div class="flex-1 min-w-0 pt-0.5">
                            <p :class="read ? 'text-gray-700' : 'text-gray-900 font-semibold'"
                               class="text-sm sm:text-base leading-snug pr-8">
                                {{ $data['message'] ?? 'Notification' }}
                            </p>
                            @if (!empty($data['commentaire']))
                                <p class="text-sm text-gray-500 mt-2 leading-relaxed border-l-2 border-gray-200 pl-3">
                                    {{ $data['commentaire'] }}
                                </p>
                            @endif
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 mt-3">
                                <span class="text-xs text-gray-400">
                                    <i class="far fa-clock mr-1"></i>
                                    {{ $notification->created_at->locale(app()->getLocale())->diffForHumans() }}
                                </span>
                                <span class="text-xs text-gray-400">
                                    {{ $notification->created_at->format('d/m/Y H:i') }}
                                </span>
                                <span x-show="!read" x-cloak
                                      class="inline-flex items-center gap-1 text-xs font-medium text-[#173052]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                    Non lue
                                </span>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 mt-3">
                                @if ($actionUrl)
                                    <a href="{{ route('notifications.open', $notification->id) }}"
                                       class="inline-flex items-center gap-1.5 text-xs font-semibold text-white bg-[#173052] hover:bg-[#1e3d6b] px-3 py-1.5 rounded-md transition-colors">
                                        Ouvrir
                                        <i class="fas fa-arrow-right text-[10px]"></i>
                                    </a>
                                @endif
                                <button type="button"
                                        x-show="!read"
                                        class="text-xs font-medium text-blue-700 hover:text-blue-900 underline-offset-2 hover:underline"
                                        @click="fetch('{{ route('notifications.markAsRead', $notification->id) }}', {
                                            method: 'POST',
                                            headers: {
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                'Accept': 'application/json',
                                                'Content-Type': 'application/json'
                                            }
                                        }).then(r => r.json()).then(d => { if (d.ok) read = true; })">
                                    Marquer comme lue
                                </button>
                            </div>
                        </div>

                        <button type="button"
                                x-show="read"
                                class="absolute top-4 right-4 sm:top-5 sm:right-5 opacity-0 group-hover:opacity-100 focus:opacity-100 transition-opacity text-gray-300 hover:text-red-600 p-2 rounded-lg hover:bg-red-50"
                                title="Supprimer"
                                @click.stop="deleteUrl = '{{ route('notifications.destroy', $notification->id) }}'; deleteId = '{{ $notification->id }}'; open = true">
                            <i class="fas fa-trash-alt text-sm"></i>
                        </button>
                    </div>
                @empty
                @endforelse
            </div>

            <div x-show="pageVisible === 0"
                 x-cloak
                 class="py-16 sm:py-20 px-6 text-center">
                <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 border border-gray-200 flex items-center justify-center">
                    <i class="fas fa-bell-slash text-2xl text-gray-300"></i>
                </div>
                <p class="text-gray-700 font-semibold text-lg">Aucune notification</p>
                <p class="text-gray-500 text-sm mt-2 max-w-sm mx-auto">
                    Lorsqu'un événement concerne vos dossiers, il apparaîtra ici.
                </p>
            </div>
        </section>

        @if ($notifications->hasPages())
            <div class="mt-8 flex justify-center">
                {{ $notifications->links() }}
            </div>
        @endif

        {{-- Delete modal --}}
        <div x-show="open"
             x-cloak
             class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl p-6" @click.stop>
                <div class="flex items-start gap-4 mb-6">
                    <div class="flex-shrink-0 w-11 h-11 bg-red-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-trash-alt text-red-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Supprimer la notification</h3>
                        <p class="text-sm text-gray-500 mt-1">Cette action est irréversible.</p>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" @click="open = false"
                            class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition-colors">
                        Annuler
                    </button>
                    <button type="button"
                            class="px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition-colors"
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
    </x-portal-page>
</x-app-layout>
