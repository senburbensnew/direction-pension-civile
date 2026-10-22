<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Mes notifications
        </h2>
    </x-slot>

    @php
        $unreadTotal = auth()->user()->unreadNotifications()->count();
        $readTotal = max(0, $notifications->total() - $unreadTotal);
    @endphp

    <x-portal-page bottom="pb-12" class="space-y-6"
         x-data="{ open: false, deleteUrl: '', deleteId: '', pageVisible: {{ $notifications->count() }} }"
         @notification-deleted.window="pageVisible = Math.max(0, pageVisible - 1)">

        <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
            <section>
                <header class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-medium text-gray-900">
                            Résumé
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">
                            Alertes sur vos dossiers et activités du portail.
                        </p>
                    </div>
                    @if ($unreadTotal > 0)
                        <form method="POST" action="{{ route('notifications.markAllAsRead') }}" class="flex-shrink-0">
                            @csrf
                            <x-primary-button class="normal-case tracking-normal text-sm w-full sm:w-auto justify-center">
                                <i class="fas fa-check-double text-xs mr-2"></i>
                                Tout marquer comme lu
                            </x-primary-button>
                        </form>
                    @endif
                </header>

                <dl class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
                    <div class="rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-3">
                        <dt class="text-sm font-medium text-gray-500">Total</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $notifications->total() }}</dd>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-3">
                        <dt class="text-sm font-medium text-gray-500">Non lues</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $unreadTotal }}</dd>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-3">
                        <dt class="text-sm font-medium text-gray-500">Lues</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $readTotal }}</dd>
                    </div>
                </dl>

                <p class="mt-6 text-sm text-gray-600">
                    @if ($unreadTotal > 0)
                        <span class="inline-flex items-center gap-1.5 font-medium text-gray-800">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                            {{ $unreadTotal }} notification{{ $unreadTotal > 1 ? 's' : '' }} à consulter
                        </span>
                    @else
                        <span class="text-green-700 font-medium">
                            <i class="fas fa-check-circle mr-1"></i> Tout est à jour
                        </span>
                    @endif
                </p>
            </section>
        </div>

        <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg overflow-hidden">
            <section>
                <header>
                    <h2 class="text-lg font-medium text-gray-900">
                        Fil d'activité
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        Historique de vos alertes les plus récentes.
                    </p>
                </header>

                <div class="mt-6 -mx-4 sm:-mx-8 border-t border-gray-100">
                    @foreach ($notifications as $notification)
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
                             class="group relative flex items-start gap-4 px-4 sm:px-8 py-5 border-b border-gray-100 transition-colors"
                             :class="read ? 'bg-white hover:bg-gray-50/80' : 'bg-indigo-50/40 hover:bg-indigo-50/60'">

                            <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center {{ $iconColor }}">
                                @switch($icon)
                                    @case('check-circle')
                                        <i class="fas fa-check-circle"></i>
                                        @break
                                    @case('x-circle')
                                        <i class="fas fa-times-circle"></i>
                                        @break
                                    @case('alert-circle')
                                        <i class="fas fa-exclamation-triangle"></i>
                                        @break
                                    @case('arrow-right-circle')
                                        <i class="fas fa-arrow-circle-right"></i>
                                        @break
                                    @case('file-plus')
                                        <i class="fas fa-file-medical"></i>
                                        @break
                                    @default
                                        <i class="fas fa-info-circle"></i>
                                @endswitch
                            </div>

                            <div class="flex-1 min-w-0 pt-0.5">
                                <p :class="read ? 'text-gray-700' : 'text-gray-900 font-medium'"
                                   class="text-sm leading-snug pr-8">
                                    {{ $data['message'] ?? 'Notification' }}
                                </p>
                                @if (!empty($data['commentaire']))
                                    <p class="text-sm text-gray-500 mt-2 leading-relaxed pl-3 border-l-2 border-gray-200">
                                        {{ $data['commentaire'] }}
                                    </p>
                                @endif
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-2 mt-3 text-xs text-gray-500">
                                    <span>
                                        <i class="far fa-clock mr-1"></i>
                                        {{ $notification->created_at->locale(app()->getLocale())->diffForHumans() }}
                                    </span>
                                    <span>{{ $notification->created_at->format('d/m/Y H:i') }}</span>
                                    <span x-show="!read" x-cloak class="font-medium text-indigo-700">
                                        Non lue
                                    </span>
                                </div>
                                <div class="flex flex-wrap items-center gap-3 mt-3">
                                    @if ($actionUrl)
                                        <a href="{{ route('notifications.open', $notification->id) }}"
                                           class="text-sm font-medium text-indigo-600 hover:text-indigo-800 hover:underline">
                                            Ouvrir
                                        </a>
                                    @endif
                                    <button type="button"
                                            x-show="!read"
                                            x-cloak
                                            class="text-sm text-gray-600 hover:text-gray-900 underline-offset-2 hover:underline"
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
                                    x-cloak
                                    class="absolute top-4 right-4 sm:right-8 opacity-0 group-hover:opacity-100 focus:opacity-100 transition-opacity text-gray-300 hover:text-red-600 p-2 rounded-md hover:bg-red-50"
                                    title="Supprimer"
                                    @click.stop="deleteUrl = '{{ route('notifications.destroy', $notification->id) }}'; deleteId = '{{ $notification->id }}'; open = true">
                                <i class="fas fa-trash-alt text-sm"></i>
                            </button>
                        </div>
                    @endforeach

                    <div x-show="pageVisible === 0"
                         x-cloak
                         class="py-16 px-4 sm:px-8 flex flex-col items-center text-center border-b border-gray-100">
                        <div class="w-12 h-12 mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                            <i class="fas fa-bell-slash text-xl text-gray-300"></i>
                        </div>
                        <p class="text-base font-medium text-gray-900">Aucune notification</p>
                        <p class="text-sm text-gray-500 mt-1 max-w-sm">
                            Lorsqu'un événement concerne vos dossiers, il apparaîtra ici.
                        </p>
                    </div>
                </div>

                @if ($notifications->hasPages())
                    <div class="mt-6 flex justify-center">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </section>
        </div>

        <div x-show="open"
             x-cloak
             class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white w-full max-w-md rounded-lg shadow-xl p-6 sm:p-8" @click.stop>
                <header>
                    <h3 class="text-lg font-medium text-gray-900">Supprimer la notification</h3>
                    <p class="mt-1 text-sm text-gray-600">Cette action est irréversible.</p>
                </header>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="open = false"
                            class="px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Annuler
                    </button>
                    <button type="button"
                            class="px-4 py-2 bg-red-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-red-700"
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
